<?php

use Crown\Form\FieldGroup;

include_once(dirname(__FILE__).'/page-section-image-gallery.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionLogoGallery')) {
	class CrownPageSectionLogoGallery extends CrownPageSectionImageGallery {


		protected static $name = 'Logo Gallery Section';


		protected static function getContentFields() {
			return array(
				new FieldGroup(array(
					'label' => 'Intro Content',
					'fields' => array(
						static::getSectionTitleField(),
						static::getContentField(array('rows' => 3)),
					)
				)),
				static::getImageGalleryField(array('label' => 'Gallery Logos', 'buttonLabel' => 'Add Logos'))
			);
		}


	}
}