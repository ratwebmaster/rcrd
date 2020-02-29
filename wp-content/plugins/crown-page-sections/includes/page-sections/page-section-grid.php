<?php

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\Input\RadioImageSet;
use Crown\UIRule;

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionGrid')) {
	class CrownPageSectionGrid extends CrownPageSection {


		protected static $name = 'Grid Section';


		protected static function getContentFields() {
			return array(
				static::getSectionConfigurationField(array('options' => array(
					array('value' => 'default', 'label' => 'Default'),
					array('value' => 'thumbnails', 'label' => 'Thumbnails')
				))),
				static::getSectionIntroContentField(),
				static::getSectionCellsField()
			);
		}


		protected static function getLayoutFields() {
			return array(
				static::getSectionIntroContentLayoutField(),
				new FieldGroup(array(
					'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'configuration'), 'value' => 'thumbnails', 'compare' => '!='))),
					'label' => 'Cell Content Layout',
					'fields' => array(
						new Field(array(
							'input' => new RadioImageSet(array('name' => 'cell_content_layout', 'defaultValue' => 'centered', 'options' => array(
								array('value' => 'centered', 'label' => 'Centered', 'image' => plugins_url('../../assets/images/icons/layout-001.png', __FILE__)),
								array('value' => 'left-aligned', 'label' => 'Left-Aligned', 'image' => plugins_url('../../assets/images/icons/layout-002.png', __FILE__))
							)))
						))
					)
				)),
				static::getSectionColumnCountField(array('ignoreOptions' => array(7, 8, 9, 10, 11, 12))),
				static::getVerticalAlignmentField(),
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

			if(array_key_exists('cells', $input)) {
				foreach($input['cells'] as $cell) {

					if(array_key_exists('title', $cell) && !empty($cell['title'])) {
						$hLevel = 'h3';
						$content[] = '<'.$hLevel.'>'.$cell['title'].'</'.$hLevel.'>';
					}

					if(array_key_exists('content', $cell) && !empty($cell['content'])) {
						$content[] = $cell['content'];
					}

				}
			}

			return implode(" \n\n", $content);
		}


	}
}