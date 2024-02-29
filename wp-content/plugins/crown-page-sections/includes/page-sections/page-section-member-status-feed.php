<?php

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Checkbox as CheckboxInput;
use Crown\Form\Input\CheckboxSet;
use Crown\Form\Input\Select;
use Crown\UIRule;

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionMemberStatusFeed')) {
	class CrownPageSectionMemberStatusFeed extends CrownPageSection {


		protected static $name = 'Member Status Feed Section';


		protected static function getContentFields() {
			return array(
				static::getSectionIntroContentField(),
				new FieldGroup(array(
					'label' => 'Members',
					'fields' => array(
                        new Field(array(
                            'label' => 'Member Status to Display',
                            'input' => new Select(array('name' => 'status')),
                            'getOutputCb' => array(__CLASS__, 'setStatusSelectInputOptions')
                        )),
                        new Field(array(
                            'label' => 'Options',
                            'input' => new CheckboxSet(array('name' => 'display_options', 'options' => array(
                                array('value' => 'byteam', 'label' => 'Organize into Teams'),
                            )))
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



        public static function setStatusSelectInputOptions($field) {
            $options[] = array('value' => '', 'label' => 'Please Select');
            //$teams = get_categories(array( 'taxonomy' => 'member_team', 'posts_per_page' => -1 ));
            $statuses = get_terms( array(
                'taxonomy' => 'member_status',
                'hide_empty' => false,
                'hierarchical' => false,
                'orderby' => 'date',
                'posts_per_page' => -1
            ) );
            foreach($statuses as $status) {
                $options[] = array('value' => $status->term_id, 'label' => $status->name);
            }
            $field->getInput()->setOptions($options);

        }


//        public static function setStatusSelectInputOptions($field) {
//            $options[] = array('value' => '', 'label' => 'Please Select');
//            $posts = get_posts(array('post_type' => 'member', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
//            foreach($posts as $post) {
//                $options[] = array('value' => $post->ID, 'label' => $post->post_title, 'depth' => 0);
//            }
//            $field->getInput()->setOptions($options);
//
//        }


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