<?php

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionDefault')) {
	class CrownPageSectionDefault extends CrownPageSection {


		protected static $name = 'Default Section';


		protected static function getContentFields() {
			return array(
				static::getSectionTitleField(),
				static::getContentField()
			);
		}


		protected static function getLayoutFields() {
			return array(
				static::getSectionWidthConstraintField(),
				static::getSectionLayoutOptionsField()
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

			return implode(" \n\n", $content);
		}


	}
}