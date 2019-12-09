<?php

if(!class_exists('CarbonPost')) {
	class CarbonPost extends CarbonObject {

		public $id = null;
		public $slug = '';
		public $title = '';
		public $content = '';
		public $raw_content = '';
		public $excerpt = '';
		public $raw_excerpt = '';
		public $date_posted = '';
		public $date_posted_gmt = '';
		public $date_modified = '';
		public $date_modified_gmt = '';
		public $author = null;
		public $raw_url = '';
		public $url = '';
		public $link = '';
		public $type = '';
		public $parent_id = null;
		public $status = '';
		public $comment_status = '';
		public $ping_status = '';
		public $password = '';
		public $password_required = false;
		public $comment_count = 0;
		public $classes = array();
		public $class = '';
		// public $thumbnail = null;
		// public $terms = array();
		// public $meta = null;
		public $object = null;

		public function __construct($postId = null) {
			$theId = get_the_ID();
			if($postId === null && !empty($theId)) {
				$postId = $theId;
			}
			$this->init($postId);
		}

		protected function init($postId = 0) {

			// resolve post ID
			if(is_numeric($postId) && $postId === 0) {
				$postId = get_the_ID();
			} else if(!is_numeric($postId) && is_string($postId)) {
				$postId = self::get_post_id_by_name($postId);
			}
			if(!$postId) return;

			$postCacheId = 0;
			if(is_object($postId) && property_exists($postId, 'ID')) {
				$postCacheId = $postId->ID;
			} else {
				$postCacheId = $postId;
			}
			if(array_key_exists($postCacheId, Carbon::$objectCache['posts'])) {
				$this->importProps(Carbon::$objectCache['posts'][$postCacheId]);
				return;
			}

			// retrieve post data
			$postData = array();
			if(is_object($postId) && get_class($postId) == 'WP_Post') {
				$postData = $postId;
			} else {
				$postData = get_post($postId);
			}
			if(empty($postData)) return;

			$this->id = $postData->ID;
			$this->slug = $postData->post_name;
			$this->title = $postData->post_title;

			$this->raw_content = $postData->post_content;
			$this->raw_excerpt = $postData->post_excerpt;

			$this->content = $postData->post_content;
			$this->excerpt = $this->getExcerpt($postData);

			if(post_password_required($this->id)) {
				$this->content = get_the_password_form($this->id);
				$this->password_required = true;
			}
			
			$this->date_posted = $postData->post_date;
			$this->date_posted_gmt = $postData->post_date_gmt;
			$this->date_modified = $postData->post_modified;
			$this->date_modified_gmt = $postData->post_modified_gmt;
			$this->author = new CarbonUser($postData->post_author);
			$this->url = get_permalink($this->id);
			$this->link = $this->url;
			$this->type = $postData->post_type;
			$this->parent_id = $postData->post_parent;
			$this->status = $postData->post_status;
			$this->comment_status = $postData->comment_status;
			$this->ping_status = $postData->ping_status;
			$this->password = $postData->post_password;
			$this->comment_count = $postData->comment_count;
			$this->object = $postData;

			do_action('carbon_disable_permalink_filtering');
			$this->raw_url = get_permalink($this->id);
			do_action('carbon_enable_permalink_filtering');

			$this->addClasses(get_post_class('', $this->id));

			// $taxonomies = get_object_taxonomies($this->object);
			// foreach($taxonomies as $taxonomy) {
			// 	$termIds = wp_get_object_terms($this->id, $taxonomy, array('fields' => 'ids'));
			// 	$terms = array();
			// 	foreach($termIds as $termId) {
			// 		$terms[] = new CarbonTerm($termId, $taxonomy);
			// 	}
			// 	$this->terms[$taxonomy] = $terms;
			// }

			// $this->meta = new stdClass();
			// $this->importMeta();

			// if(property_exists($this->meta, '_thumbnail_id')) $this->thumbnail = $this->meta->_thumbnail_id;
			
			Carbon::$objectCache['posts'][$this->id] = clone $this;

		}

		public function addClasses($classes = array()) {
			$classes = is_array($classes) ? $classes : explode(' ', $classes);
			$this->classes = array_merge($this->classes, $classes);
			$this->classes = array_unique($this->classes);
			$this->class = trim(implode(' ', $this->classes));
		}

		protected function importMeta() {

			$meta = get_post_custom($this->id);
			if(!is_array($meta) || empty($meta)) $meta = array();
			foreach($meta as $key => $value) {
				if(is_array($value) && count($value) == 1) {
					$value = $value[0];
				}
				$meta[$key] = maybe_unserialize($value);
			}
			$meta = apply_filters('carbon_import_post_meta', $meta, $this->id, $this);
			
			if(is_array($meta)) {
				foreach($meta as $key => $value) {
					if(!empty($key)) {
						$this->meta->$key = $value;
					}
				}
			}

		}

		protected function getExcerpt($postData, $length = 40, $tail = '&hellip;') {

			if(post_password_required($postData->ID)) {
				return 'There is no excerpt because this is a protected post.';
			}
			
			$excerpt = trim($postData->post_excerpt);
			if(!empty($excerpt)) return strip_tags($excerpt);

			$excerpt = do_shortcode($postData->post_content);
			if(preg_match('/<!--more(.*?)?-->/', $excerpt, $matches)) {
				$teaser = trim(substr($excerpt, 0, strpos($excerpt, $matches[0])));
				$moreHash = md5($teaser);
				$moreLinkLabel = '(more&hellip;)';
				return wp_strip_all_tags($teaser).apply_filters('the_content_more_link', ' <a href="'.get_permalink($postData->ID).'#more-'.$moreHash.'" class="more-link">'.$moreLinkLabel.'</a>', $moreLinkLabel);
			}

			return wp_trim_words($excerpt, $length, $tail);

		}

		public function getComments($args = array()) {

			if(post_password_required($this->id)) return array();

			$currentCommenter = wp_get_current_commenter();

			$args = array_merge(array(
				'status' => 'approve',
				'post_id' => $this->id,
				'order' => 'ASC',
				'include_unapproved' => array($currentCommenter['comment_author_email'])
			), $args);
			$comments = get_comments($args);

			$index = array();
			foreach($comments as $comment) {
				if(isset($comment->comment_ID)) {
					$comment = new CarbonComment($comment);
					$index['_'.$comment->id] = $comment;
				}
			}

			$comments = array();
			foreach($index as $i => $comment) {
				if(isset($comment->parent_id) && $comment->parent_id && isset($index['_'.$comment->parent_id])) {
					$index['_'.$comment->parent_id]->addChild($index[$i]);
				} else {
					$comments[] = $index[$i];
				}
			}

			return $comments;

		}

		public function getCommentForm($args = array()) {

			if(post_password_required($this->id)) return '';

			$args = array_merge(array(), $args);
			$commentForm = Carbon::getOB('comment_form', $args, $this->id);
			return $commentForm;

		}
		public function comment_form($args = array()) { return $this->getCommentForm($args); }

		public function __get($field) {

			if(array_key_exists($this->id, Carbon::$objectCache['posts']) && isset(Carbon::$objectCache['posts'][$this->id]->$field)) {
				return Carbon::$objectCache['posts'][$this->id]->$field;
			}

			if(in_array($field, array('meta', 'thumbnail'))) {
				$this->meta = new stdClass();
				$this->importMeta();
				if(property_exists($this->meta, '_thumbnail_id')) $this->thumbnail = $this->meta->_thumbnail_id;
			} else if(in_array($field, array('terms')) && !isset($this->terms)) {
				$this->terms = array();
				$taxonomies = get_object_taxonomies($this->object);
				foreach($taxonomies as $taxonomy) {
					$termIds = wp_get_object_terms($this->id, $taxonomy, array('fields' => 'ids'));
					$terms = array();
					foreach($termIds as $termId) {
						$terms[] = new CarbonTerm($termId, $taxonomy);
					}
					$this->terms[$taxonomy] = $terms;
				}
			}

			Carbon::$objectCache['posts'][$this->id] = clone $this;
			return isset($this->$field) ? $this->$field : null;
		}

	}
}