<?php

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Checkbox as CheckboxInput;
use Crown\Form\Input\Select;
use Crown\UIRule;

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionBlogPostSlider')) {
	class CrownPageSectionBlogPostSlider extends CrownPageSection {


		protected static $name = 'Blog Post Slider Section';


		protected static function getContentFields() {
			return array(
				static::getSectionIntroContentField(),
				new FieldGroup(array(
					'label' => 'Section Posts',
					'fields' => array(
						static::getSectionConfigurationField(array('label' => 'Post Source', 'inputName' => 'post_source', 'options' => array(
							array('value' => 'specific', 'label' => 'Specific Posts (custom order)'),
							array('value' => 'recent', 'label' => 'Most Recent'),
							array('value' => 'category', 'label' => 'Category (ordered by most recent)'),
							array('value' => 'tag', 'label' => 'Tag (ordered by most recent)')
						))),
						static::getPostSelectField(array(
							'label' => 'Posts to Display',
							'description' => 'Posts can be reordered by dragging selected items.',
							'inputName' => 'posts',
							'placeholder' => 'Select Posts...',
							'postType' => 'post',
							'multiple' => true,
							'sortable' => true,
							'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'post_source'), 'value' => 'specific'))),
						)),
						static::getTermSelectField(array(
							'label' => 'Post Categories to Display',
							'inputName' => 'post_categories',
							'placeholder' => 'Select Categories...',
							'taxonomy' => 'category',
							'multiple' => true,
							'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'post_source'), 'value' => 'category')))
						)),
						static::getTermSelectField(array(
							'label' => 'Post Tags to Display',
							'inputName' => 'post_tags',
							'placeholder' => 'Select Tags...',
							'taxonomy' => 'post_tag',
							'multiple' => true,
							'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'post_source'), 'value' => 'tag')))
						)),
						new Field(array(
							'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'post_source'), 'value' => array('recent', 'category', 'tag')))),
							'label' => 'Maximum Number of Slides to Display',
							'description' => 'Posts will be grouped three per slide.',
							'input' => new Select(array('name' => 'max_slides_to_display', 'defaultValue' => 3, 'class' => 'input-xsmall', 'options' => array(1, 2, 3, 4, 5, 6)))
						))
					)
				))
			);
		}


		protected static function getLayoutFields() {
			return array(
				static::getSectionIntroContentLayoutField(),
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

			return implode(" \n\n", $content);
		}


	}
}