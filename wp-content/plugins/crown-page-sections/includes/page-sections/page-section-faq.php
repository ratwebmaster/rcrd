<?php

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldRepeater;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\RichTextarea;

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionFaq')) {
	class CrownPageSectionFaq extends CrownPageSection {


		protected static $name = 'FAQ Section';


		protected static function getContentFields() {
			return array(
				static::getSectionIntroContentField(),
				new FieldGroup(array(
					'label' => 'Section FAQs',
					'fields' => array(
						new FieldRepeater(array(
							'name' => 'faqs',
							'addNewLabel' => 'Add New FAQ',
							'fields' => array(
								new Field(array(
									'label' => 'Question',
									'input' => new TextInput(array('name' => 'question', 'class' => 'input-large'))
								)),
								new Field(array(
									'input' => new RichTextarea(array('name' => 'response', 'rows' => 10))
								))
							)
						))
					)
				))
			);
		}


		protected static function getLayoutFields() {
			return array(
				static::getSectionIntroContentLayoutField(),
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

			if(array_key_exists('faqs', $input)) {
				foreach($input['faqs'] as $faq) {

					if(array_key_exists('question', $faq) && !empty($faq['question'])) {
						$hLevel = 'h3';
						$content[] = '<'.$hLevel.'>'.$faq['question'].'</'.$hLevel.'>';
					}

					if(array_key_exists('response', $faq) && !empty($faq['response'])) {
						$content[] = $faq['response'];
					}

				}
			}

			return implode(" \n\n", $content);
		}


	}
}