<?php

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionImageGallery')) {
	class CrownPageSectionImageGallery extends CrownPageSection {


		protected static $name = 'Image Gallery Section';


		protected static function getContentFields() {
			return array(
				static::getSectionIntroContentField(),
				static::getImageGalleryField()
			);
		}


		protected static function getLayoutFields() {
			return array(
				static::getSectionIntroContentLayoutField(),
				static::getSectionColumnCountField(array('defaultValue' => 4, 'ignoreOptions' => array())),
				static::getHorizontalAlignmentField(),
				static::getSectionWidthConstraintField(),
				static::getSectionLayoutOptionsField(array('ignoreOptions' => array('text-center')))
			);
		}


		protected static function getStyleFields() {
			return array(
				static::getSectionBgColorField(),
				static::getSectionBgImageField(),
				static::getSectionCustomIdAndClassFields()
			);
		}


		public static function getFallbackContent($input) {
			$content = array();

			if(array_key_exists('title', $input) && !empty($input['title'])) {
				$hLevel = array_key_exists('title_h_level', $input) && !empty($input['title_h_level']) ? $input['title_h_level'] : 'h2';
				$content[] = '<'.$hLevel.'>'.$input['title'].'</'.$hLevel.'>';
			}

			if(array_key_exists('content', $input) && !empty($input['content'])) {
				$content[] = $input['content'];
			}

			if(array_key_exists('images', $input) && !empty($input['images'])) {
				foreach($input['images'] as $imageId) {
					$image = wp_get_attachment_image($imageId, 'medium');
					if(!empty($image)) $content[] = $image;
				}
			}

			return implode(" \n\n", $content);
		}


	}
}