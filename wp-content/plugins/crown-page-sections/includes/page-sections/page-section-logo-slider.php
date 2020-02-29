<?php

use Crown\Form\FieldGroup;

include_once(dirname(__FILE__).'/page-section-image-slider.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionLogoSlider')) {
	class CrownPageSectionLogoSlider extends CrownPageSectionImageSlider {


		protected static $name = 'Logo Slider Section';


		protected static function getContentFields() {
			return array(
				new FieldGroup(array(
					'label' => 'Intro Content',
					'fields' => array(
						static::getSectionTitleField(),
						static::getContentField(array('rows' => 3)),
					)
				)),
				static::getImageGalleryField(array('label' => 'Slider Logos', 'buttonEditLabel' => 'Edit Slides'))
			);
		}


	}
}