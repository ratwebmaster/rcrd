<?php

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\Select;
use Crown\Form\Input\CheckboxSet;

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionMemberTabs')) {
	class CrownPageSectionMemberTabs extends CrownPageSection {


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
			    new FieldGroup(array(
			        'class' => 'no-border two-column',
                    'fields' => array(
                        static::getSectionBgColorField(),
                        static::getSectionBgImageField(),
                    )
                )),
				new Field(array(
				    'label' => 'Theme Accents',
                    'input' => new CheckboxSet(array('name' => 'accent', 'options' => array(
                        array('value' => 'underline', 'label' => 'Thick line below header')
                    )))
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

			return implode(" \n\n", $content);
		}


	}
}