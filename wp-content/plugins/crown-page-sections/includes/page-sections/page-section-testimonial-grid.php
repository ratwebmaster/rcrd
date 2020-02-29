<?php

include_once(dirname(__FILE__).'/page-section-testimonial-slider.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionTestimonialGrid')) {
	class CrownPageSectionTestimonialGrid extends CrownPageSectionTestimonialSlider {


		protected static $name = 'Testimonial Grid Section';


		protected static function getLayoutFields() {
			return array(
				static::getSectionIntroContentLayoutField(),
				static::getSectionColumnCountField(array('ignoreOptions' => array(7, 8, 9, 10, 11, 12))),
				static::getVerticalAlignmentField(),
				static::getHorizontalAlignmentField(),
				static::getSectionWidthConstraintField(),
				static::getSectionLayoutOptionsField(array('ignoreOptions' => array('text-center')))
			);
		}


	}
}