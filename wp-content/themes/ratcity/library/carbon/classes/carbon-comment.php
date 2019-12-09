<?php

if(!class_exists('CarbonComment')) {
	class CarbonComment extends CarbonObject {

		public $id = null;
		public $post_id = null;
		public $content = '';
		public $date = '';
		public $date_gmt = '';
		public $author = null;
		public $author_user = null;
		public $url = '';
		public $edit_url = '';
		public $karma = 0;
		public $approved = false;
		public $type = '';
		public $parent_id = null;
		public $classes = array();
		public $class = '';
		public $meta = null;
		public $object = null;
		public $children = array();

		public function __construct($commentId = null) {
			$this->init($commentId);
		}

		protected function init($commentId = 0) {

			if(!$commentId) return;

			$commentCacheId = 0;
			if(is_object($commentId) && property_exists($commentId, 'comment_ID')) {
				$commentCacheId = $commentId->comment_ID;
			} else {
				$commentCacheId = $commentId;
			}
			if(array_key_exists($commentCacheId, Carbon::$objectCache['comments'])) {
				$this->importProps(Carbon::$objectCache['comments'][$commentCacheId]);
				return;
			}

			// retrieve post data
			$commentData = array();
			if(is_object($commentId) && property_exists($commentId, 'comment_ID')) {
				$commentData = $commentId;
			} else {
				$commentData = get_comment($commentId);
			}
			if(empty($commentData)) return;

			$this->id = $commentData->comment_ID;
			$this->post_id = $commentData->comment_post_ID;
			
			$this->raw_content = $commentData->comment_content;
			$this->content = apply_filters('comment_text', $commentData->comment_content);
			
			$this->date = $commentData->comment_date;
			$this->date_gmt = $commentData->comment_date_gmt;

			$this->author = new stdClass();
			$this->author->name = $commentData->comment_author;
			$this->author->email = $commentData->comment_author_email;
			$this->author->url = $commentData->comment_author_url;
			$this->author->ip = $commentData->comment_author_IP;
			$this->author->agent = $commentData->comment_agent;

			if(!empty($commentData->user_id)) {
				$this->author_user = new CarbonUser($commentData->user_id);
			}

			$this->url = get_comment_link($this->id);
			$this->edit_url = get_edit_comment_link($this->id);
			$this->karma = $commentData->comment_ID;
			$this->approved = (bool)$commentData->comment_approved;
			$this->type = $commentData->comment_type;
			$this->parent_id = $commentData->comment_parent;
			$this->object = $commentData;

			$this->addClasses(get_comment_class('', $this->id, $this->post_id));

			$this->meta = new stdClass();
			$this->importMeta();

			Carbon::$objectCache['comments'][$this->id] = clone $this;

		}

		public function addClasses($classes = array()) {
			$classes = is_array($classes) ? $classes : explode(' ', $classes);
			$this->classes = array_merge($this->classes, $classes);
			$this->classes = array_unique($this->classes);
			$this->class = trim(implode(' ', $this->classes));
		}

		protected function importMeta() {

			$meta = get_comment_meta($this->id);
			if(!is_array($meta) || empty($meta)) $meta = array();
			foreach($meta as $key => $value) {
				if(is_array($value) && count($value) == 1) {
					$value = $value[0];
				}
				$meta[$key] = maybe_unserialize($value);
			}
			$meta = apply_filters('carbon_import_comment_meta', $meta, $this->id, $this);
			
			if(is_array($meta)) {
				foreach($meta as $key => $value) {
					if(!empty($key)) {
						$this->meta->$key = $value;
					}
				}
			}

		}

		public function addChild($comment) {
			if(empty($this->children)) {
				$this->addClasses('parent');
			}
			$this->children[] = $comment;
		}

		public function getAvatar($size = 100) {
			return get_avatar($this->object, $size);
		}

		public function getReplyLink($args = array()) {
			$args = array_merge(array(), $args);
			return get_comment_reply_link($args, $this->id);
		}
		public function reply_link($args = array()) {
			return $this->getReplyLink($args);
		}

	}
}