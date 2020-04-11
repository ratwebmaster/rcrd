<?php

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Checkbox as CheckboxInput;
use Crown\Form\Input\Select;
use Crown\UIRule;

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionSponsorFeed')) {
	class CrownPageSectionSponsorFeed extends CrownPageSection {


		protected static $name = 'Sponsor Feed Section';


		protected static function getContentFields() {
			return array(
				static::getSectionIntroContentField(),
				new FieldGroup(array(
					'label' => 'Section Sponsors',
					'fields' => array(
						static::getSectionConfigurationField(array('label' => 'Post Source', 'inputName' => 'post_source', 'options' => array(
							array('value' => 'specific', 'label' => 'Specific Sponsors (custom order)'),
							array('value' => 'recent', 'label' => 'Most Recent'),
						))),
						static::getPostSelectField(array(
							'label' => 'Sponsors to Display',
							'description' => 'Sponsors can be reordered by dragging selected items.',
							'inputName' => 'sponsors',
							'placeholder' => 'Select Sponsors...',
							'postType' => 'sponsor',
							'multiple' => true,
							'sortable' => true,
							'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'post_source'), 'value' => 'specific'))),
						)),
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



        public static function setPageSelectInputOptions($field) {
            $options[] = array('value' => '', 'label' => 'Please Select');
            $posts = get_posts(array('post_type' => 'page', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
            foreach($posts as $post) {
                $options[] = array('value' => $post->ID, 'label' => $post->post_title, 'depth' => 0);
            }
            $field->getInput()->setOptions($options);

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