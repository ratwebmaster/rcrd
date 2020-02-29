<?php
/**
 * Plugin Name: Crown Page Sections
 * Description: Adds flexible section capabilities to pages.
 * Version: 1.3.0
 * Author: Jordan Crown
 * Author URI: http://www.jordancrown.com
 * License: GNU General Pulic License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// self::getDefaultPageContentSectionType(),
// self::getTwoColumnContentSectionType(),
// self::getGridContentSectionType(),
// self::getHeroContentSectionType(),
// self::getHeroSliderContentSectionType(),
// self::getImageGalleryContentSectionType(),
// self::getImageSliderContentSectionType(),
// self::getLogoGalleryContentSectionType(),
// self::getLogoSliderContentSectionType(),
// self::getTestimonialGridContentSectionType(),
// self::getTestimonialSliderContentSectionType(),
// self::getFaqContentSectionType(),
// self::getStaffContentSectionType(),
// self::getLocationsMapContentSectionType()

use Crown\Api\GoogleMaps;
use Crown\Form\Field;
use Crown\Form\FieldRepeaterFlex;
use Crown\Form\Input\Hidden as HiddenInput;
use Crown\Post\Type as PostType;


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSections')) {
	class CrownPageSections {

		// define which page sections to include here
		public static $sectionClassNames = array(
			'CrownPageSectionDefault',
			'CrownPageSectionTwoColumn',
			'CrownPageSectionGrid',
			'CrownPageSectionImageGallery',
			'CrownPageSectionImageSlider',
			'CrownPageSectionLogoGallery',
			'CrownPageSectionLogoSlider',
			'CrownPageSectionFaq',
			'CrownPageSectionTestimonialSlider',
			'CrownPageSectionTestimonialGrid',
			'CrownPageSectionBlogPostSlider',
            'CrownPageSectionEventFeed'
		);

		public static $init = false;

		public static $pagePostType;


		public static function init() {
			if(self::$init) return;
			self::$init = true;

			// include all page section class files
			$pageSectionClassDir = dirname(__FILE__).'/includes/page-sections';
			foreach(scandir($pageSectionClassDir) as $file) {
				if(preg_match('/^[^\.]+\.php$/', $file)) {
					include_once($pageSectionClassDir.'/'.$file);
				}
			}

			GoogleMaps::init(array('apiKey' => defined('CROWN_GOOGLE_API_KEY') ? CROWN_GOOGLE_API_KEY : ''));

			add_action('after_setup_theme', array(__CLASS__, 'setupPageSections'));
			add_filter('wp_insert_post_data', array(__CLASS__, 'filterInsertPostData'), 10, 2);
			add_action('admin_enqueue_scripts', array(__CLASS__, 'registerAdminScripts'));
			add_action('admin_enqueue_scripts', array(__CLASS__, 'registerAdminStyles'));

		}


		public static function setupPageSections() {
			self::$pagePostType = new PostType(array(
				'name' => 'page',
				'fields' => array(
					new Field(array('input' => new HiddenInput(array('name' => 'override_post_content', 'defaultValue' => 1)))),
					new FieldRepeaterFlex(array(
						'name' => 'page_content_sections',
						'types' => self::getPageSectionTypes()
					))
				)
			));
		}


		public static function getPageSectionTypes() {
			$types = array();
			foreach(self::$sectionClassNames as $sectionClassName) {
				if(class_exists($sectionClassName)) {
					$sectionConfig = $sectionClassName::getConfig();
					if(empty($sectionConfig)) continue;
					$types[] = $sectionConfig;
				}
			}
			return $types;
		}


		public static function filterInsertPostData($data, $postArr) {
			if($data['post_type'] == 'page') {
				if(array_key_exists('override_post_content', $postArr) && $postArr['override_post_content'] == '1') {
					$contentSections = array();
					if(array_key_exists('page_content_sections', $postArr) && is_array($postArr['page_content_sections'])) {
						foreach(array_values($postArr['page_content_sections']) as $sectionIndex => $contentSection) {

							$sectionType = $contentSection['crown_repeater_entry_type'];
							foreach(self::$sectionClassNames as $sectionClassName) {
								if(class_exists($sectionClassName) && $sectionClassName::getSlug() == $sectionType) {
									$fallbackContent = trim($sectionClassName::getFallbackContent($contentSection));
									if(!empty($fallbackContent)) $contentSections[] = $fallbackContent;
								}
							}

						}
					}
					$data['post_content'] = stripslashes(implode(" \n\n", $contentSections));
				}
			}
			return $data;
		}


		public static function registerAdminScripts($hook) {

			$screen = get_current_screen();
			if($screen->base == 'post' && $screen->post_type == 'page') {

				$js = "
					(function($) {
						$(document).ready(function() {});
					})(jQuery);
				";
				wp_add_inline_script('common', $js);

			}

		}


		public static function registerAdminStyles($hook) {

			$screen = get_current_screen();
			if($screen->base == 'post' && $screen->post_type == 'page') {

				wp_enqueue_style('font-awesome', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', false, '4.7.0', 'all');

				$css = "
					#postdivrich {
						display: none;
					}
					.crown-media-input.icon .media-input-preview {
						max-width: 100px;
					}
				";
				wp_add_inline_style('common', $css);

			}

		}


	}
}

if(class_exists('CrownPageSections')) {
	CrownPageSections::init();
}