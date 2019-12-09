<?php

require_once(__DIR__.'/vendor/autoload.php');

if(!function_exists('carbonAutoload')) {
	function carbonAutoload($class) {
		$fileMap = array(
			'CarbonComment'				=> __DIR__.'/classes/carbon-comment.php',
			'CarbonExtensionCrown'		=> __DIR__.'/classes/extensions/carbon-extension-crown.php',
			'CarbonExtensionPolylang'	=> __DIR__.'/classes/extensions/carbon-extension-polylang.php',
			'CarbonMenu'				=> __DIR__.'/classes/carbon-menu.php',
			'CarbonMenuItem'			=> __DIR__.'/classes/carbon-menu-item.php',
			'CarbonObject'				=> __DIR__.'/classes/carbon-object.php',
			'CarbonPost'				=> __DIR__.'/classes/carbon-post.php',
			'CarbonSidebar'				=> __DIR__.'/classes/carbon-sidebar.php',
			'CarbonSite'				=> __DIR__.'/classes/carbon-site.php',
			'CarbonTaxonomy'			=> __DIR__.'/classes/carbon-taxonomy.php',
			'CarbonTerm'				=> __DIR__.'/classes/carbon-term.php',
			'CarbonTheme'				=> __DIR__.'/classes/carbon-theme.php',
			'CarbonTwig'				=> __DIR__.'/classes/carbon-twig.php',
			'CarbonUser'				=> __DIR__.'/classes/carbon-user.php',
			'CarbonView'				=> __DIR__.'/classes/carbon-view.php'
		);
		if(array_key_exists($class, $fileMap)) require_once($fileMap[$class]);
	}
	spl_autoload_register('carbonAutoload');
}

if(!class_exists('Carbon')) {
	class Carbon {

		public static $isInit = false;
		public static $extensions = array();

		public static $objectCache = array(
			'posts' => array(),
			'terms' => array(),
			'menu-items' => array(),
			'menus' => array(),
			'comments' => array(),
			'sidebars' => array(),
			'users' => array()
		);

		public static function init() {
			if(self::$isInit) return;
			self::$extensions['crown'] = new CarbonExtensionCrown();
			self::$extensions['polylang'] = new CarbonExtensionPolylang();
			self::$isInit = true;
		}

		public static function getGlobalContext() {
			global $wp_query, $post;
			$context = array();

			$context['site'] = new CarbonSite();
			$context['theme'] = $context['site']->theme;
			$context['template'] = $context['site']->template;

			$context['current_user'] = new CarbonUser();

			$context['body_class'] = implode(' ', get_body_class());

			if(is_singular()) {
				$context['post'] = new CarbonPost($post);
				if(is_preview()) {
					$revisions = wp_get_post_revisions($post->ID);
					if(!empty($revisions)) {
						$revision = new CarbonPost(current($revisions));
						$context['post']->meta = $revision->meta;
						$context['post']->thumbnail = $revision->thumbnail;
					}
				}
				$context['posts'] = array($context['post']);
			} else {
				$context['posts'] = self::getPosts($wp_query);
				$context['post'] = !empty($context['posts']) ? $context['posts'][0] : null;
			}
			$context['pagination'] = self::getPagination();

			return apply_filters('carbon_context', $context);
		}

		public static function getOB() {

			// retrieve arguments
			$args = func_get_args();
			if(empty($args)) return '';

			// get callback
			$callback = array_shift($args);
			if(!is_callable($callback)) return '';

			// get function output
			ob_start();
			call_user_func_array($callback, $args);
			return ob_get_clean();

		}

		public static function getPosts($args) {
			global $post;

			$posts = array();
			$postObjects = array();

			if(is_array($args)) {
				reset($args);
				if(!empty($args) && is_a(current($args), 'WP_Post')) {
					$postObjects = $args;
				} else {
					$postObjects = get_posts($args);
					if(array_key_exists('fields', $args) && $args['fields'] == 'ids') {
						return $postObjects;
					}
				}
			} else if(get_class($args) == 'WP_Query' && $args->post_count > 0) {
				while($args->have_posts()) {
					$args->the_post();
					$postObjects[] = $post;
				}
				wp_reset_postdata();
			}

			foreach($postObjects as $postObject) {
				$posts[] = new CarbonPost($postObject);
			}

			return $posts;

		}

		public static function getTerms($args) {

			$terms = array();
			$termObjects = array();

			if(is_array($args)) {
				reset($args);
				if(!empty($args) && is_a(current($args), 'WP_Term')) {
					$termObjects = $args;
				} else {
					$termObjects = get_terms($args);
				}
			}

			foreach($termObjects as $termObject) {
				$terms[] = new CarbonTerm($termObject->term_id, $termObject->taxonomy);
			}

			return $terms;

		}

		public static function getPagination($prefs = array()) {
			global $wp_query, $paged;

			$maxPage = $wp_query->max_num_pages;
			if($maxPage < 2) return false;
			if(!$paged) $paged = 1;

			$pagination = new stdClass();
			$pagination->current_page = $paged;
			$pagination->pages = array();
			for($i = 1; $i <= $maxPage; $i++) {
				$pagination->pages[] = get_pagenum_link($i);
			}

			$nextPageUrl = false;
			$prevPageUrl = false;

			$nextPage = intval($paged) + 1;
			if(!is_single() && ($nextPage <= $maxPage)) {
				$pagination->next = next_posts($maxPage, false);
			}

			if(!is_single() && $paged > 1) {
				$pagination->prev = previous_posts(false);
			}

			return $pagination;
		}

		public static function translate($string, $context = '') {
			return self::$extensions['polylang']->twigFilterTranslate($string, $context);
		}

	}
}

