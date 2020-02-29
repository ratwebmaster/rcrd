<?php

include_once(dirname(__FILE__).'/page-section-image-gallery.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionImageSlider')) {
	class CrownPageSectionImageSlider extends CrownPageSectionImageGallery {


		protected static $name = 'Image Slider Section';


		protected static function getContentFields() {
			return array(
				static::getSectionIntroContentField(),
				static::getImageGalleryField(array('label' => 'Slider Images', 'buttonEditLabel' => 'Edit Slides'))
			);
		}


		protected static function getLayoutFields() {
			return array(
				static::getSectionIntroContentLayoutField(),
				static::getSectionWidthConstraintField(),
				static::getSectionLayoutOptionsField(array('ignoreOptions' => array('text-center')))
			);
		}


	}
}