<?php

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Checkbox as CheckboxInput;
use Crown\Form\Input\Select;
use Crown\UIRule;

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionEventFeed')) {
	class CrownPageSectionEventFeed extends CrownPageSection {


		protected static $name = 'Event Feed Section';


		protected static function getContentFields() {
			return array(
				static::getSectionIntroContentField(),
				new FieldGroup(array(
					'label' => 'Section Events',
					'fields' => array(
						static::getSectionConfigurationField(array('label' => 'Post Source', 'inputName' => 'post_source', 'options' => array(
							array('value' => 'specific', 'label' => 'Specific Events (custom order)'),
							array('value' => 'recent', 'label' => 'Most Recent'),
							array('value' => 'category', 'label' => 'Category (ordered by most recent)')
						))),
						static::getPostSelectField(array(
							'label' => 'Events to Display',
							'description' => 'Events can be reordered by dragging selected items.',
							'inputName' => 'events',
							'placeholder' => 'Select Events...',
							'postType' => 'event',
							'multiple' => true,
							'sortable' => true,
							'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'post_source'), 'value' => 'specific'))),
						)),
						static::getTermSelectField(array(
							'label' => 'Event Types to Display',
							'inputName' => 'event_types',
							'placeholder' => 'Select Types...',
							'taxonomy' => 'event_type',
							'multiple' => true,
							'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'post_source'), 'value' => 'category')))
						)),
						new Field(array(
							'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'post_source'), 'value' => array('recent', 'category', 'tag')))),
							'label' => 'Maximum Number of Events to Display',
							'input' => new Select(array('name' => 'max_slides_to_display', 'defaultValue' => 4, 'class' => 'input-xsmall', 'options' => array(1, 2, 3, 4, 5, 6)))
						)),
                        new FieldGroup(array(
                            'label' => 'View More Button',
                            'class' => 'two-column',
                            'fields' => array(
                                new Field(array(
                                    'label' => 'Events Link',
                                    'input' => new Select(array('name' => 'events_feed_button_link')),
                                    'getOutputCb' => array(__CLASS__, 'setPageSelectInputOptions')
                                )),
                                new Field(array(
                                    'label' => 'All Events button label',
                                    'input' => new TextInput(array('name' => 'events_feed_button_label', 'placeholder' => 'View All Events'))
                                )),
                            )
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