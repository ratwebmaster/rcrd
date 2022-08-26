<?php

use Michelf\Markdown;


if(!class_exists('CarbonTwig')) {
	class CarbonTwig {

		protected $twig = null;

		public function __construct() {
			global $wp_embed;

			Twig_Autoloader::register();

			// define template file loader
			$templateDirectories = array(
				get_stylesheet_directory().'/views',
				get_template_directory().'/views'
			);
			$loader = new Twig_Loader_Filesystem($templateDirectories);
			
			// setup environment
			$environmentOptions = array(
				'autoescape' => false,
				'debug' => WP_DEBUG
			);
			if(!WP_DEBUG && defined('CARBON_CACHE') && CARBON_CACHE) {
				$environmentOptions['cache'] = __DIR__.'/../cache';
			}

			$this->twig = new Twig_Environment($loader, $environmentOptions);
			if(WP_DEBUG) $this->twig->addExtension(new Twig_Extension_Debug());

			// extend environment
			$this->addTwigFilters();
			$this->addTwigFunctions();


			// setup carbon content filter
			
			add_filter('carbon_content', 'wptexturize');
			add_filter('carbon_content', 'convert_smilies', 20);
			add_filter('carbon_content', 'wpautop');
			add_filter('carbon_content', 'shortcode_unautop');
			add_filter('carbon_content', 'prepend_attachment');
//			add_filter('carbon_content', 'wp_make_content_images_responsive');
			add_filter('carbon_content', 'do_shortcode', 11);
			add_filter('carbon_content', array($wp_embed, 'run_shortcode'), 8);
			add_filter('carbon_content', array($wp_embed, 'autoembed'), 8);

		}

		protected function addTwigFilters() {

			// build array of filters to add to environment
			$filters = array(
				'filter' => array('callback' => array(&$this, 'filterFilter')),
				'excerpt' => array('callback' => array(&$this, 'filterExcerpt')),
				'content' => array('callback' => array(&$this, 'filterContent')),
				'numbers_only' => array('callback' => array(&$this, 'filterNumbersOnly')),
				'color_mix' => array('callback' => array(&$this, 'filterColorMix')),
				'color_lighten' => array('callback' => array(&$this, 'filterColorLighten')),
				'markdown' => array('callback' => array(&$this, 'filterMarkdown'))
			);
			$filters = apply_filters('carbon_twig_filters', $filters);

			// register filters
			foreach($filters as $name => $settings) {
				if(!is_array($settings)) $settings = array('callback' => $settings);
				$settings = array_merge(array('callback' => null, 'options' => array()), $settings);
				if(is_callable($settings['callback'])) {
					$this->twig->addFilter(new Twig_SimpleFilter($name, $settings['callback'], $settings['options']));
				}
			}

		}

		protected function addTwigFunctions() {

			// build array of functions to add to environment
			$functions = array(
				'function' => array('callback' => array(&$this, 'functionFunction')),
				'action' => array('callback' => array(&$this, 'functionAction')),
				'get_url' => array('callback' => array(&$this, 'functionGetUrl')),
				'get_media_url' => array('callback' => array(&$this, 'functionGetMediaUrl')),
				'get_media_srcset' => array('callback' => array(&$this, 'functionGetMediaSrcset')),
				'get_media_filename' => array('callback' => array(&$this, 'functionGetMediaFilename')),
				'get_media_filetype' => array('callback' => array(&$this, 'functionGetMediaFiletype')),
				'get_media_alt_text' => array('callback' => array(&$this, 'functionGetMediaAltText')),
				'get_media_caption' => array('callback' => array(&$this, 'functionGetMediaCaption')),
				'get_media_mime_type' => array('callback' => array(&$this, 'functionGetMediaMimeType')),
				'get_media_mime_file_size' => array('callback' => array(&$this, 'functionGetMediaFileSize')),
				'file_exists' => array('callback' => array(&$this, 'functionFileExists')),
				'get_post' => array('callback' => array(&$this, 'functionGetPost')),
				'get_posts' => array('callback' => array(&$this, 'functionGetPosts')),
				'get_term' => array('callback' => array(&$this, 'functionGetTerm')),
				'get_terms' => array('callback' => array(&$this, 'functionGetTerms')),
				'get_post_meta' => array('callback' => array(&$this, 'functionGetPostMeta')),
				'use_white_foreground' => array('callback' => array(&$this, 'functionUseWhiteForeground')),
				'get_luminosity' => array('callback' => array(&$this, 'functionGetLuminosity')),
				'compare_colors' => array('callback' => array(&$this, 'functionCompareColors')),
                'get_custom_excerpt' => array('callback' => array(&$this, 'functionBuildExcerpt')),
                'get_title' => array('callback' => array(&$this, 'functionGetTitle'))
			);
			$functions = apply_filters('carbon_twig_functions', $functions);

			// register functions
			foreach($functions as $name => $settings) {
				if(!is_array($settings)) $settings = array('callback' => $settings);
				$settings = array_merge(array('callback' => null, 'options' => array()), $settings);
				if(is_callable($settings['callback'])) {
					$this->twig->addFunction(new Twig_SimpleFunction($name, $settings['callback'], $settings['options']));
				}
			}

		}

		public function render($templates = array(), $context = array()) {

			// retrieve valid template
			$template = $this->twig->resolveTemplate($templates);

			// return rendered template
			return $template->render($context);

		}

		public function filterFilter() {

			// retrieve arguments
			$args = func_get_args();
			if(empty($args)) return '';

			// reorder arguments
			$string = array_shift($args);
			if(empty($args)) return $string;
			array_splice($args, 1, 0, array($string));

			// call action
			return call_user_func_array('apply_filters', $args);

		}

		public function filterExcerpt($content, $length = 40, $tail = '&hellip;') {
			return wp_trim_words($content, $length, $tail);
		}

		public function filterContent($content, $moreLinkUrl = '', $moreLinkLabel = '(more&hellip;)') {
			if(preg_match('/<!--more(.*?)?-->/', $content, $matches)) {
				$teaser = trim(substr($content, 0, strpos($content, $matches[0])));
				$moreHash = md5($teaser);
				if(is_singular()) {
					$content = str_replace($matches[0], '<span id="more-'.$moreHash.'"></span>', $content);
				} else if(!empty($moreLinkUrl)) {
					if(!empty($matches[1]) && !empty($moreLinkLabel)) {
						$moreLinkLabel = strip_tags(wp_kses_no_null(trim($matches[1])));
					}
					$content = $teaser.apply_filters('the_content_more_link', ' <a href="'.$moreLinkUrl.'#more-'.$moreHash.'" class="more-link">'.$moreLinkLabel.'</a>', $moreLinkLabel);
					$content = force_balance_tags($content);
				}
			}
			return apply_filters('carbon_content', $content);
		}

		public function filterNumbersOnly($text) {
			return preg_replace('/[^0-9]/', '', $text);
		}

		public function filterColorLighten($color = array(0, 0, 0), $percent = 0.5) {
			$color = is_string($color) ? $color = $this->hex2rgb($color) : $color;
			return $this->rgb2hex(array_map(function($x) use ($percent) { return max(0, min(255, round($x * (100 + ($percent * 100 * 2)) / 100))); }, $color));
		}

		public function filterColorMix($color1 = array(0, 0, 0), $color2 = array(0, 0, 0), $weight = 0.5) {
			$color1 = is_string($color1) ? $color1 = $this->hex2rgb($color1) : $color1;
			$color2 = is_string($color2) ? $color2 = $this->hex2rgb($color2) : $color2;
			$f = function($x) use ($weight) { return $weight * $x; };
			$g = function($x) use ($weight) { return (1 - $weight) * $x; };
			$h = function($x, $y) { return round($x + $y); };
			return $this->rgb2hex(array_map($h, array_map($f, $color1), array_map($g, $color2)));
		}
		public function hex2rgb($hex = '#000000') {
			$f = function($x) { return hexdec($x); };
			return array_map($f, str_split(str_replace('#', '', $hex), 2));
		}
		public function rgb2hex($rgb = array(0, 0, 0)) {
			$f = function($x) { return str_pad(dechex($x), 2, '0', STR_PAD_LEFT); };
			return '#'.implode("", array_map($f, $rgb));
		}

		public static function filterMarkdown($content) {
			return Markdown::defaultTransform($content);
		}

		public function functionFunction() {

			// retrieve arguments
			$args = func_get_args();
			if(empty($args)) return '';

			// call function
			$callback = array_shift($args);
			if(!is_callable($callback)) return '';
			return call_user_func_array($callback, $args);

		}

		public function functionAction() {

			// retrieve arguments
			$args = func_get_args();
			if(empty($args)) return '';

			// call action
			return call_user_func_array('do_action', $args);

		}

		public function functionGetUrl($postId) {
		    return get_permalink($postId);
        }

		public function functionGetMediaUrl($mediaId, $size = '') {
			if(empty($size)) {
				return wp_get_attachment_url($mediaId);
			} else {
				$src = wp_get_attachment_image_src($mediaId, $size);
				if(is_array($src)) return $src[0];
			}
			return '';
		}

		public function functionGetMediaSrcset($mediaId, $size = 'medium') {
		    return wp_get_attachment_image_srcset($mediaId, $size);
        }

		public function functionGetMediaFilename($mediaId) {
			$fileUrl = get_attached_file($mediaId);
			return !empty($fileUrl) ? basename($fileUrl) : '';
		}

		public function functionGetMediaFiletype($mediaId) {
			$url = wp_get_attachment_url($mediaId);
			if($url) {
				$filetype = wp_check_filetype($url);
				return $filetype['ext'];
			}
			return '';
		}

		public function functionGetMediaAltText($mediaId) {
			$alt = get_post_meta($mediaId, '_wp_attachment_image_alt', true);
			return $alt;
		}

		public function functionGetMediaCaption($mediaId) {
			$media = get_post($mediaId);
			return $media ? $media->post_excerpt : '';
		}

		public function functionGetMediaMimeType($mediaId) {
			return get_post_mime_type($mediaId);
		}

		public function functionGetMediaFileSize($mediaId) {
			if(file_exists(get_attached_file($mediaId))) {
				$fileSize = filesize(get_attached_file($mediaId));
				if($fileSize >= 1073741824) {
					return number_format($fileSize / 1073741824, 2).' GB';
				} else if($fileSize >= 1048576) {
					return number_format($fileSize / 1048576, 2).' MB';
				} else {
					return number_format($fileSize / 1024, 2).' KB';
				}
			}
			return 0;
		}

		public function functionFileExists($file) {
			return file_exists($file);
		}

		public function functionGetPost($postId) {
			$post = new CarbonPost($postId);
			return $post;
		}

		public function functionGetPosts($args = array()) {
			return Carbon::getPosts((array)$args);
		}

		public function functionGetTerm($termId, $taxonomy) {
			$term = new CarbonTerm($termId, $taxonomy);
			return $term;
		}

		public function functionGetTerms($args = array()) {
			return Carbon::getTerms((array)$args);
		}

		public function functionGetPostMeta($post_id) {
		    return get_post_meta($post_id);
        }

		public function functionUseWhiteForeground($hex = '', $threshold = null) {
			if(empty($threshold)) $threshold = apply_filters('default_white_foreground_threshold', 0.38);
			$luminosity = $this->functionGetLuminosity($hex);
			return $luminosity <= $threshold;
		}

		public function functionGetLuminosity($hex) {
			$hex = preg_replace('/[^0-9a-f]/i', '', $hex);
			if(empty($hex) || strlen($hex) < 3) $hex = 'fff';
			if(strlen($hex) < 6) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
			$c = array();
			for($i = 0; $i < 3; $i++) $c[] = hexdec(substr($hex, $i * 2, 2)) / 255;
			for($i = 0; $i < 3; $i++) {
				if($c[$i] <= 0.03928) {
					$c[$i] = $c[$i] / 12.92;
				} else {
					$c[$i] = pow(($c[$i] + 0.055) / 1.055, 2.4);
				}
			}
			$luminosity = (0.2126 * $c[0]) + (0.7152 * $c[1]) + (0.0722 * $c[2]);
			return $luminosity;
		}

		public function functionCompareColors($hexA = '', $hexB = '') {
			$luminosityA = $this->functionGetLuminosity($hexA);
			$luminosityB = $this->functionGetLuminosity($hexB);
			return 1 - abs($luminosityA - $luminosityB);
		}

		public function functionBuildExcerpt($string, $wordcount = 20, $ellipsis = '...') {
		    $words = str_word_count($string, 2);
		    $shortened = array_slice($words, 0, $wordcount);

		    return implode(' ', $shortened) . $ellipsis;
        }

        public function functionGetTitle($postId, $savedLabel = null) {
            if ($savedLabel) {
                return $savedLabel;
            }

            $post_data = get_post($postId);
            $post_title = $post_data->post_title;

            return $post_title;
        }

	}
}