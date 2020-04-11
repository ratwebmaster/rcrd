<?php

use Crown\Form\Field;
use Crown\Form\FieldGroup;

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionTwoColumn')) {
	class CrownPageSectionTwoColumn extends CrownPageSection {


		protected static $name = 'Two-Column Section';


		protected static function getContentFields() {
			return array(
				static::getSectionIntroContentField(),
				static::getSectionColumn1Fields(),
				static::getSectionColumn2Fields()
			);
		}


		protected static function getLayoutFields() {
			return array(
				static::getSectionIntroContentLayoutField(),
				static::getSectionTwoColumnLayoutField(),
				static::getVerticalAlignmentField(),
				static::getSectionWidthConstraintField(),
				static::getSectionLayoutOptionsField()
			);
		}


		protected static function getStyleFields() {
			return array(
                new FieldGroup(array(
                    'class' => 'no-border two-column',
                    'fields' => array(
                        static::getSectionBgColorField(),
                        static::getSectionBgImageField(),
                    )
                )),
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

			if(array_key_exists('title_column_1', $input) && !empty($input['title_column_1'])) {
				$hLevel = array_key_exists('title_h_level_column_1', $input) && !empty($input['title_h_level_column_1']) ? $input['title_h_level_column_1'] : 'h2';
				$content[] = '<'.$hLevel.'>'.$input['title_column_1'].'</'.$hLevel.'>';
			}

			if(array_key_exists('content_column_1', $input) && !empty($input['content_column_1'])) {
				$content[] = $input['content_column_1'];
			}

			if(array_key_exists('title_column_2', $input) && !empty($input['title_column_2'])) {
				$hLevel = array_key_exists('title_h_level_column_2', $input) && !empty($input['title_h_level_column_2']) ? $input['title_h_level_column_2'] : 'h2';
				$content[] = '<'.$hLevel.'>'.$input['title_column_2'].'</'.$hLevel.'>';
			}

			if(array_key_exists('content_column_2', $input) && !empty($input['content_column_2'])) {
				$content[] = $input['content_column_2'];
			}

			return implode(" \n\n", $content);
		}


	}
}