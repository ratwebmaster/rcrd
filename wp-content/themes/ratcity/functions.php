<?php

// include carbon library
if(!class_exists('Carbon')) require_once(__DIR__.'/library/carbon/carbon.php');
Carbon::init();

// includes
require_once(__DIR__.'/includes/gf-multi-column.php');


if(!class_exists('Zero')) {

	/**
	 * Theme class with methods to perform basic theme functionality.
	 */
	class Zero {


		public static $version = null;


		/**
		 * Register action/filter hooks for execution.
		 */
		public static function init() {

			$theme = wp_get_theme(basename(dirname(__FILE__)));
			self::$version = $theme->version;

			add_action('init', array(__CLASS__, 'disableEmojis'));
			add_action('after_setup_theme', array(__CLASS__, 'setupTheme'));
			add_filter('image_size_names_choose', array(__CLASS__, 'filterImageSizeNamesChoose'));
			add_action('widgets_init', array(__CLASS__, 'registerSidebars'));

			add_action('pre_get_posts', array(__CLASS__, 'setMainQuery'));

			add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueueScripts'));
			add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueueStyles'));

			add_filter('body_class', array(__CLASS__, 'filterBodyClass'));
			add_filter('get_search_form', array(__CLASS__, 'filterSearchForm'));
			add_filter('the_password_form', array(__CLASS__, 'filterPasswordForm'));
			add_filter('nav_menu_css_class', array(__CLASS__, 'addNavMenuCssClasses'), 10, 2);
			add_filter('comment_form_defaults', array(__CLASS__, 'filterCommentFormDefaults'));
			add_filter('post_gallery', array(__CLASS__, 'filterGalleryShortcodeOutput'), 10, 3);

			// Crown
			add_filter('crown_theme_colors', array(__CLASS__, 'filterCrownThemeColors'), 10, 2);

			// TinyMCE
			add_filter('mce_buttons_2', array(__CLASS__, 'filterMceButtons2'));
			add_filter('tiny_mce_before_init', array(__CLASS__, 'filterTinyMceBeforeInit'));

			// Carbon
			add_filter('carbon_twig_functions', array(__CLASS__, 'addTwigFunctions'));
			add_filter('carbon_twig_filters', array(__CLASS__, 'addTwigFilters'));
			add_filter('carbon_context', array(__CLASS__, 'filterCarbonContext'));
			add_filter('carbon_import_site_meta', array(__CLASS__, 'filterCarbonSiteMeta'), 10, 3);
			add_filter('carbon_import_theme_meta', array(__CLASS__, 'filterCarbonThemeMeta'), 10, 3);
			add_filter('carbon_import_post_meta', array(__CLASS__, 'filterCarbonPostMeta'), 10, 3);
			add_filter('carbon_import_crown_repeater_entry_meta', array(__CLASS__, 'filterCarbonCrownRepeaterEntryMeta'), 10, 3);
			add_filter('carbon_menu_item_index', array(__CLASS__, 'filterCarbonMenuItemIndex'), 10, 2);

			// SEO Framework
			add_filter('the_seo_framework_metabox_priority', function() { return 'low'; });

			// Gravity Forms
			add_filter('gform_submit_button', array(__CLASS__, 'filterGformSubmitButton'), 10, 2);

			// WooCommerce
			remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
			remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);

		}


		/**
		 * Disable support for emojis
		 */
		public static function disableEmojis() {
			remove_action('wp_head', 'print_emoji_detection_script', 7);
			remove_action('admin_print_scripts', 'print_emoji_detection_script');
			remove_action('wp_print_styles', 'print_emoji_styles');
			remove_action('admin_print_styles', 'print_emoji_styles');	
			remove_filter('the_content_feed', 'wp_staticize_emoji');
			remove_filter('comment_text_rss', 'wp_staticize_emoji');	
			remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
		}


		/**
		 * Define the theme settings.
		 */
		public static function setupTheme() {

			// import theme's styles into admin content editor
			add_editor_style(get_template_directory_uri().'/css/editor-style.css');

			// define theme support
			// add_theme_support('post-formats', array('aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat'));
			add_theme_support('post-thumbnails');
			add_theme_support('automatic-feed-links');
			add_theme_support('title-tag');
			add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
			add_theme_support('woocommerce');

			// register navigation menu locations
			register_nav_menu('header', 'Header Navigation');
			register_nav_menu('footer', 'Footer Navigation');
			register_nav_menu('mobile', 'Mobile Navigation');

			// set image sizes
			set_post_thumbnail_size(200, 200, true);
			add_image_size('450w', 450, 10000000, false);
			add_image_size('660w', 660, 10000000, false);
			add_image_size('800w', 800, 10000000, false);
			add_image_size('1000w', 1000, 10000000, false);
			add_image_size('1200w', 1200, 10000000, false);
			add_image_size('1600w', 1600, 10000000, false);
			add_image_size('2000w', 2000, 10000000, false);

		}


		/**
		 * Adds custom image sizes to image size options.
		 *
		 * @param string[] $sizes Key-value pair of image sizes and labels.
		 *
		 * @return string[] Updated list of image size options.
		 */
		public static function filterImageSizeNamesChoose($sizes) {
			global $_wp_additional_image_sizes;
			
			$sizesConfig = array();
			foreach(get_intermediate_image_sizes() as $sizeName) {
				if(in_array($sizeName, array('thumbnail', 'medium', 'medium_large', 'large'))) {
					$sizesConfig[$sizeName]['width'] = get_option($sizeName.'_size_w');
					$sizesConfig[$sizeName]['height'] = get_option($sizeName.'_size_h');
					$sizesConfig[$sizeName]['crop'] = (bool)get_option($sizeName.'_crop');
				} else if(isset($_wp_additional_image_sizes[$sizeName])) {
					$sizesConfig[$sizeName] = array(
						'width' => $_wp_additional_image_sizes[$sizeName]['width'],
						'height' => $_wp_additional_image_sizes[$sizeName]['height'],
						'crop' => $_wp_additional_image_sizes[$sizeName]['crop']
					);
				}
			}
			uasort($sizesConfig, function($a, $b) {
				return $a['width'] - $b['width'];
			});

			$sizes = array();
			foreach($sizesConfig as $sizeName => $config) {
				$label = $config['width'].'px';
				if(in_array($sizeName, array('thumbnail', 'medium', 'medium_large', 'large'))) {
					$label = ucwords(str_replace('_', ' ', $sizeName));
				}
				$sizes[$sizeName] = $label;
			}
			$sizes['full'] = 'Full Size';

			return $sizes;
		}


		/**
		 * Register theme's dynamic sidebars available in admin.
		 */
		public static function registerSidebars() {

			// global sidebar settings
			$defaultSidebarSettings = array(
				'id' => '',
				'name' => '',
				'description' => '',
				'class' => '',
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget' => '</section>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>'
			);

			// define dynamic sidebars
			$sidebars = array(
				array('id' => 'blog', 'name' => 'Blog Sidebar')
			);

			// register sidebars
			foreach($sidebars as $sidebarSettings) {
				if(!empty($sidebarSettings['id'])) register_sidebar(array_merge($defaultSidebarSettings, $sidebarSettings));
			}

		}


		/**
		 * Modify global wp_query parameters before fetched.
		 *
		 * Additional parameters can be set using the query object's set() method.
		 *
		 * @param WP_Query &$query Gobal wp_query object.
		 */
		public static function setMainQuery($query) {
			if(is_admin() || !$query->is_main_query()) return false;

            if ($query->is_post_type_archive('event')) {
                $query->set('posts_per_page', -1);
                $query->set('sort_by', 'event_start_date');
            }

            if ($query->is_post_type_archive('member')) {
                $query->set('posts_per_page', -1);
                $query->set('sort_by', 'title');
            }

			// place modifications to $query here

		}


		/**
		 * Register and enqueue JS files.
		 *
		 * Localize theme scripts if necessary.
		 */
		public static function enqueueScripts() {

			if(class_exists('Crown\Api\GoogleMaps')) wp_register_script('google-maps-infobox', get_template_directory_uri().'/library/infobox_packed.js', array('google-maps-api'), '', true); // Google Maps infobox plugin

			wp_enqueue_script('jquery-mobile', get_template_directory_uri().'/library/jquery/jquery.mobile.custom.min.js', array('jquery'), '1.4.5', true); // jQuery Mobile touchscreen events
			if(is_singular() && comments_open() && get_option('thread_comments')) wp_enqueue_script('comment-reply'); // threaded comment interface
			wp_enqueue_script('blueimp-gallery', get_template_directory_uri().'/library/blueimp-gallery/js/blueimp-gallery.min.js', array(), '2.21.3'); // Gallery plugin
			wp_enqueue_script('slick', get_template_directory_uri().'/library/slick/slick.min.js', array('jquery'), '1.6.0', true); // Slick slider plugin

			// custom theme scripts
			wp_enqueue_script('theme-plugins', get_template_directory_uri().'/js/plugins.js', array(), self::$version, true);
			wp_enqueue_script('theme-main', get_template_directory_uri().'/js/main.js', array('jquery-effects-core', 'jquery-mobile', 'theme-plugins', 'blueimp-gallery', 'slick'), self::$version, true);
			wp_localize_script('theme-main', 'themeData', array(
				'baseUrl' => get_home_url(),
				'themeUrl' => get_template_directory_uri(),
				'ajaxUrl' => admin_url('admin-ajax.php')
			));

			// Full Height Banner Script
            wp_enqueue_script('banner-height', get_template_directory_uri().'/js/banner-height.min.js', array('jquery'), '1.0', true);



		}


		/**
		 * Register and enqueue CSS files.
		 */
		public static function enqueueStyles() {

			wp_enqueue_style('blueimp-gallery', get_template_directory_uri().'/library/blueimp-gallery/css/blueimp-gallery.min.css', array(), '2.21.3');
			wp_enqueue_style('slick', get_template_directory_uri().'/library/slick/slick.css', array(), '1.6.0');

			// custom theme stylesheet
			wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,400,300,700|Montserrat:300,400,500,600,700');
			wp_enqueue_style('theme-style', get_template_directory_uri().'/css/style.css', array(), self::$version, 'all');

		}


		/**
		 * Add or modify HTML body classes.
		 *
		 * @param array $classes Body classes.
		 *
		 * @return array Body classes.
		 */
		public static function filterBodyClass($classes) {
			
			// add, remove, modify body classes here

			return $classes;
		}


		/**
		 * Modify the default search form.
		 * 
		 * @param string $form Search form HTML.
		 *
		 * @return string Search form HTML.
		 */
		public static function filterSearchForm($form) {
			ob_start();
			?>
				<form role="search" method="get" class="search-form" action="<?php echo home_url('/'); ?>">
					<label>
						<span class="screen-reader-text">Search for:</span>
						<input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="Search&hellip;">
					</label>
					<span class="submit-wrap"><button type="submit">Search</button></span>
				</form>
			<?php
			return ob_get_clean();
		}


		/**
		 * Modify the default post password form.
		 * 
		 * @param string $form Post password form HTML.
		 *
		 * @return string Post password form HTML.
		 */
		public static function filterPasswordForm($form) {
			ob_start();
			?>
				<p>This content is password protected. To view it please enter your password below:</p>
				<form method="post" class="post-password-form" action="<?php echo esc_url(site_url('wp-login.php?action=postpass', 'login_post')); ?>">
					<label>
						<span class="screen-reader-text">Password:</span>
						<input type="password" name="post_password" placeholder="Password&hellip;">
					</label>
					<span class="submit-wrap"><button type="submit">Submit</button></span>
				</form>
			<?php
			return ob_get_clean();
		}


		/**
		 * Add classes to nav menu items.
		 *
		 * @param string[] $classes Existing classes.
		 * @param WP_Post $item Menu item post object.
		 *
		 * @return string[] Updated menu item class names.
		 */
		public static function addNavMenuCssClasses($classes, $item) {
			
			// add classes to menu item classes here

			return $classes;
		}


		/**
		 * Modify default comment form settings.
		 *
		 * @param array $defaults Default settings array.
		 *
		 * @return array Updating default settings array.
		 */
		public static function filterCommentFormDefaults($defaults) {
			$defaults['title_reply'] = 'Leave a Comment';
			$defaults['submit_button'] = '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>';
			$defaults['comment_notes_before'] = '';
			if(isset($defaults['fields'])) unset($defaults['fields']['url']);
			return $defaults;
		}


		/**
		 * Modify default gallery shortcode output.
		 *
		 * @param string $output Default gallery HTML output.
		 * @param array $attr Shortcode attribute variables.
		 * @param string $instance Unique gallery instance identifier.
		 *
		 * @return string Modified gallery HTML output.
		 */
		public static function filterGalleryShortcodeOutput($output, $attr, $instance) {

			$post = get_post();
			$atts = shortcode_atts(array(
				'order' => 'ASC',
				'orderby' => 'menu_order ID',
				'id' => $post ? $post->ID : 0,
				'columns' => 3,
				'size' => 'thumbnail',
				'include' => '',
				'exclude' => '',
			), $attr, 'gallery');

			$attachments = array();
			$id = intval($atts['id']);
			if(!empty($atts['include'])) {
				$attachmentPosts = get_posts(array('include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby']));
				foreach($attachmentPosts as $key => $val) $attachments[$val->ID] = $attachmentPosts[$key];
			} else if(!empty($atts['exclude'])) {
				$attachments = get_children(array('post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby']));
			} else {
				$attachments = get_children(array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
			}
			if(empty($attachments)) return $output;

			if(is_feed()) {
				$output = "\n";
				foreach($attachments as $attId => $attachment) $output .= wp_get_attachment_link($attId, 'large', true)."\n";
				return $output;
			}

			$selector = 'gallery-'.$instance;
			$columns = intval($atts['columns']);
			$size = 'thumbnail';
			if($columns <= 6) $size = 'medium';
			if($columns <= 2) $size = 'large';

			ob_start();
			?>
				<div id="<?php echo $selector; ?>" class="gallery galleryid-<?php echo $id; ?> gallery-columns-<?php echo $columns; ?> gallery-size-<?php echo sanitize_html_class($atts['size']); ?>">
					<?php foreach($attachments as $id => $attachment) { ?>
						<?php $imageMeta = wp_get_attachment_metadata($id); ?>
						<?php if(!$imageMeta) continue; ?>
						<?php $imageSrc = wp_get_attachment_image_src($id, 'large'); ?>
						<?php $imagePreviewSrc = wp_get_attachment_image_src($id, $size); ?>
						<figure class="gallery-item">
							<div class="gallery-icon <?php echo isset($imageMeta['height'], $imageMeta['width']) && $imageMeta['height'] > $imageMeta['width'] ? 'portrait' : 'landscape'; ?>">
								<a href="<?php echo $imageSrc[0]; ?>">
									<div class="preview" style="background-image: url(<?php echo $imagePreviewSrc[0]; ?>);">
										<?php echo wp_get_attachment_image($id, $size, false, ((trim($attachment->post_excerpt)) ? array('aria-describedby' => $selector.'-'.$id) : '')); ?>
									</div>
								</a>
							</div>
							<?php if(trim($attachment->post_excerpt)) { ?>
								<figcaption class="wp-caption-text gallery-caption" id="<?php echo $selector.'-'.$id; ?>">
									<?php echo wptexturize($attachment->post_excerpt); ?>
								</figcaption>
							<?php } ?>
						</figure>
					<?php } ?>
				</div>
			<?php
			$output = ob_get_clean();

			return $output;
		}


		/**
		 * Filter theme color palette used by Crown plugins.
		 *
		 * @param array $colors Hex codes of colors.
		 * @param string $context Context for color palette to be used.
		 *
		 * @return array Hex codes of colors.
		 */
		public static function filterCrownThemeColors($colors, $context) {
			$colors = array(
				'#ffffff', // white
				'#8DC63F', // bright green
				'#818380', // gray
				'#000000', // black
				'#497C04', // medium green
				'#EFEFEE', // light gray
				'#F5F6F4', // light greenish gray
			);
			return $colors;
		}


		/**
		 * Add buttons to MCE editor.
		 *
		 * @param string[] $buttons Array of buttons to be filtered.
		 *
		 * @return string[] Filtered set of MCE buttons.
		 */
		public static function filterMceButtons2($buttons) {
			array_unshift($buttons, 'styleselect', 'fontsizeselect');
			return $buttons;
		}


		/**
		 * Adds styling options to MCE editor dropdowns.
		 *
		 * @param array $initArray MCE default settings to be filtered.
		 *
		 * @return array Updated MCE settings.
		 */
		public static function filterTinyMceBeforeInit($initArray) {

			// insert JSON-encoded array formatting options into 'style_formats'
			$initArray['style_formats'] = json_encode(array(
				array(
					'title' => 'Lead Copy',  
					'block' => 'div',  
					'classes' => 'lead',
					'wrapper' => true
				)
			));

			// insert font size options into 'fontsize_formats'
			$initArray['fontsize_formats'] = '10px 11px 12px 13px 14px 15px 16px 18px 20px 24px 36px 48px 64px';

			// define custom text colors
			$customTextColors = '
				"FFFFFF", "white",
				"FAFAFA", "light-gray",
				"428BCA", "blue",
				"5CB85C", "green",
				"5BC0DE", "light-blue",
				"F0AD4E", "orange",
				"D9534F", "red",
				"222222", "dark-gray"
			';
			$initArray['textcolor_map'] = '['.$customTextColors.']';
			
			return $initArray;
		}


		/**
		 * Register additional Twig functions.
		 *
		 * @param array $functions Existing registered Twig functions.
		 *
		 * @return array Updated list of Twig functions.
		 */
		public static function addTwigFunctions($functions) {
			return array_merge($functions, array(
				'get_map' => array('callback' => array(__CLASS__, 'twigFunctionGetMap')),
			));
		}


		/**
		 * Generates Google Map HTML to embed in template.
		 *
		 * @param array $points Array of point data with at least `lat` and `lng` keys defined.
		 *
		 * @return string Generated map HTML.
		 */
		public static function twigFunctionGetMap($points = array(), $settings = array()) {
			if(!class_exists('Crown\Api\GoogleMaps')) return '';
			$settings = (array)$settings;
			$settings = array_merge(array(
				'points' => $points,
				'class' => '',
				'autoAddMarkers' => true,
				'options' => array_merge(array(
					'styles' => self::getMapStyle(),
					'scrollwheel' => false,
					'mapTypeControl' => false,
					'streetViewControl' => false,
					'zoom' => 11
				), (isset($settings['options']) ? (array)($settings['options']) : array()))
			), $settings);
			wp_enqueue_script('google-maps-infobox');
			return Crown\Api\GoogleMaps::getMap($settings);
		}


		/**
		 * Register additional Twig filters.
		 *
		 * @param array $filters Existing registered Twig filters.
		 *
		 * @return array Updated list of Twig filters.
		 */
		public static function addTwigFilters($filters) {
			return array_merge($filters, array());
		}


		/**
		 * Append additional data to global context for twig templates.
		 *
		 * @param array $context Existing context.
		 *
		 * @return array Updated context.
		 */
		public static function filterCarbonContext($context) {

			// add data to global context here
			
			$context['breadcrumbs'] = '';
			if(class_exists('CrownBreadcrumbs')) {
				$context['breadcrumbs'] = CrownBreadcrumbs::getBreadcrumbs();
			} else if(function_exists('yoast_breadcrumb')) {
				$context['breadcrumbs'] = yoast_breadcrumb('<div class="breadcrumbs">', '</div>', false);
			}

            $context['social_media_profiles'] = (object)array(
                'facebook' => (object)array('url' => get_option('theme_options_facebook_profile_url')),
                'instagram' => (object)array('url' => get_option('theme_options_instagram_profile_url')),
                'twitter' => (object)array('url' => get_option('theme_options_twitter_profile_url')),
                'youtube' => (object)array('url' => get_option('theme_options_youtube_profile_url')),
            );

            $context['site_branding'] = (object)array(
                'logo' => wp_get_attachment_image(get_option('theme_options_logo'), 'full'),
                'banner_default' => wp_get_attachment_url(get_option('theme_options_default_page_header_image')),
                'donate' => [
                    'url' => '',
                    'label' => '',
                    'newpage' => false
                ]
            );

            $context['page_header'] = (object)array(
                'configuration' => 'default',
                'title' => '',
//                'callout' => '',
                'page_header_image' => '',
                'page_header_slides' => [],
                'page_slider_enabled' => '',
//                'bg_image_position' => 'top center',
//                'contact' => array(),
//                'display_title' => true,
//                'title_alignment' => 'left',
                'banner_height_full' => false
            );

            $context['page_footer'] = (object)array(
                'email_signup' => get_option('theme_options_footer_subscribe'),
                'social_header' => get_option('theme_options_footer_social_header'),
                'nonprofit' => get_option('theme_options_footer_nonprofit_text'),
                'links' => get_repeater_entries('blog', 'theme_options_footer_links'),
            );


            if(is_search()) {
                $context['page_header']->title = 'Search Results';
            }


            $postId = is_singular() ? get_the_ID() : false;
            if(is_home() && ($indexPageId = get_option('page_for_posts'))) $postId = $indexPageId;

            if(is_singular() && is_preview()) {
                $revisions = wp_get_post_revisions(get_the_ID());
                if(!empty($revisions)) {
                    $revision = current($revisions);
                    $postId = $revision->ID;
                }
            }


            if (!$postId && is_post_type_archive()) {
                $post_type = get_post_type();
                $postId = get_option("theme_options_{$post_type}_index_page");
            }

            if(is_category()) {
                $context['page_header']->configuration = 'default';
                $current_cat = get_the_category(get_the_ID());
                $cat_name = $current_cat[0]->name;
                $context['page_header']->title = 'All ' . $cat_name;
            }

            $donate_type = get_option('theme_header_donate_link_type');
            $donate_internal = get_option('theme_header_donate_internal');
            $donate_external = get_option('theme_header_donate_external');
            if ($donate_type == 'internal' && is_numeric($donate_internal)) {
                $context['site_branding']->donate['url'] = get_permalink($donate_internal);
                $context['site_branding']->donate['newpage'] = false;
            } else if ($donate_type == 'external' && $donate_external != '') {
                $context['site_branding']->donate['url'] = $donate_external;
                $context['site_branding']->donate['newpage'] = true;
            }
            $donate_label = get_option('theme_header_donate_label');
            if(!empty($donate_label)) $context['site_branding']->donate['label'] = $donate_label;


            if($postId) {

                $configuration = get_post_meta($postId, 'page_header_type', true);
                if(!empty($configuration)) $context['page_header']->configuration = $configuration;

                $context['page_header']->title = get_the_title($postId);
                $title = $configuration != 'disabled' ? get_post_meta($postId, 'page_header_title', true) : get_post_meta($postId, 'page_header_disabled_title', true);
                if(!empty($title)) $context['page_header']->title = $title;

//                $text = get_post_meta($postId, 'page_header_text', true);
//                if(!empty($text)) $context['page_header']->content = $text;

//                $width = get_post_meta($postId, 'page_header_width', true);
//                if(!empty($width)) $context['page_header']->content_width = $width;

//                $header_position = get_post_meta($postId, 'page_header_position', true);
//                if(!empty($header_position)) $context['page_header']->content_position = $header_position;

                if (get_post_meta($postId, 'slider_enabled')[0]) {
                    $page_slider = get_repeater_entries('post', 'page_header_slides', $postId);
                    if(!empty($page_slider)) $context['page_header']->page_header_slides = ($page_slider) ? $page_slider : get_option('theme_options_default_page_header_image');
                } else {
                    $page_image = get_post_meta($postId, 'page_header_image', true);
                    $image = wp_get_attachment_image_url(($page_image) ? $page_image : get_option('theme_options_default_page_header_image'), 'full');
                    if(!empty($image)) $context['page_header']->page_header_image = $image;
                }
                $context['page_header']->page_slider_enabled = get_post_meta($postId, 'slider_enabled')[0];

//                $display_title = get_post_meta($postId, 'title_enabled', true);
//                if(!empty($display_title)) $context['page_header']->display_title = $display_title;

//                $title_alignment = get_post_meta($postId, 'page_title_alignment', true);
//                if(!empty($title_alignment)) $context['page_header']->title_alignment = $title_alignment;

                $banner_height = get_post_meta($postId, 'banner_height_full', true);
                if(!empty($banner_height)) $context['page_header']->banner_height_full = $banner_height;


            }

//            if(is_archive('member')) {
//                $context['page_header']->configuration = 'default';
//            }
//            if(is_singular('member')) {
//                $context['page_header']->configuration = 'default';
//            }

            if(is_single()) {
                $context['page_header']->configuration = 'disabled';
//                $context['page_header']->display_title = false;
            }
            if(is_404()) {
                $context['page_header']->configuration = 'default';
//                $context['page_header']->title = get_option('theme_options_404_page_message_title');
            }

			return $context;
		}


		/**
		 * Append additional data to site meta object.
		 *
		 * @param array $meta Existing site meta data.
		 * @param int $id Site ID.
		 * @param CarbonSite $site Site object.
		 *
		 * @return array Updated site meta data.
		 */
		public static function filterCarbonSiteMeta($meta, $id, $site) {

			$meta = array_merge($meta, array(

			// 	'org_details_phone' => get_option('org_details_phone'),
			// 	'org_details_email' => get_option('org_details_email'),
			// 	'org_details_hours' => get_option('org_details_hours'),
			// 	'org_details_street_address_1' => get_option('org_details_street_address_1'),
			// 	'org_details_street_address_2' => get_option('org_details_street_address_2'),
			// 	'org_details_city' => get_option('org_details_city'),
			// 	'org_details_state' => get_option('org_details_state'),
			// 	'org_details_zip' => get_option('org_details_zip'),

			// 	'org_details_facebook_url' => get_option('org_details_facebook_url'),
			// 	'org_details_twitter_url' => get_option('org_details_twitter_url'),

			));

			return $meta;
		}


		/**
		 * Append additional data to theme meta object.
		 *
		 * @param array $meta Existing theme meta data.
		 * @param int $id Theme name.
		 * @param CarbonTheme $theme Theme object.
		 *
		 * @return array Updated theme meta data.
		 */
		public static function filterCarbonThemeMeta($meta, $name, $theme) {

			$meta = array_merge($meta, array());

			return $meta;
		}


		/**
		 * Append additional data to post meta object.
		 *
		 * @param array $meta Existing post meta data.
		 * @param int $id Post ID.
		 * @param CarbonPost $post Post object.
		 *
		 * @return array Updated post meta data.
		 */
		public static function filterCarbonPostMeta($meta, $id, $post) {

			$meta = array_merge($meta, array());

			return $meta;
		}


		/**
		 * Append additional data to Crown repeater entry meta object.
		 *
		 * @param array $meta Existing repeater entry meta data.
		 * @param int $id Entry post ID.
		 * @param CarbonPost $post Entry post object.
		 *
		 * @return array Updated repeater entry meta data.
		 */
		public static function filterCarbonCrownRepeaterEntryMeta($meta, $id, $post) {

			$meta = array_merge($meta, array());

			return $meta;
		}


		/**
		 * Filter index of menu items before ordering into parent-child tree.
		 *
		 * @param array $index Indexed array of menu items.
		 * @param array $items Menu items.
		 *
		 * @return array Indexed array of menu items.
		 */
		public static function filterCarbonMenuItemIndex($index, $items) {

			$eventsUrl = get_permalink(get_option('theme_options_event_index_page'));

			foreach($index as $itemId => $item) {
				$markAsCurrent = false;
				$markAsAncestor = false;

				if($eventsUrl && $item->url == $eventsUrl && is_singular('event')) {
					$markAsAncestor = true;
				}

				if($markAsCurrent || $markAsAncestor) {
					
					$item->addClasses($markAsCurrent ? array('current-menu-item') : array('current-menu-ancestor'));
					if(array_key_exists($item->parent_id, $index)) {
						$parentItem = $index[$item->parent_id];
						$parentItem->addClasses($markAsCurrent ? array('current-menu-ancestor', 'current-menu-parent') : array('current-menu-ancestor'));
						while(array_key_exists($parentItem->parent_id, $index)) {
							$parentItem = $index[$parentItem->parent_id];
							$parentItem->addClasses(array('current-menu-ancestor'));
						}
					}
				}

			}

			return $index;
		}


		/**
		 * Set the Gravity Forms' submit button HTML.
		 *
		 * @param string $button Submit button HTML.
		 * @param array $form Current form object.
		 *
		 * @return string Submit button HTML.
		 */
		public static function filterGformSubmitButton($button, $form) {
			if(preg_match('/^\s*<input\s.*value=\'([^\']*)\'/', $button, $matches)) {
				$button = preg_replace(array('/^<input/', '/\/?>$/'), array('<button', '>'.$matches[1].'</button>'), $button);
			}
			return $button;
		}


		/**
		 * Retrieve Google Maps styling object.
		 *
		 * @return string JSON-encoded map styling object.
		 */
		public static function getMapStyle() {
			$path = get_template_directory().'/js/map-style.json';
			if(!file_exists($path)) return '{}';
			return json_decode(file_get_contents($path));
		}


	}
}

// initialize theme loader
Zero::init();