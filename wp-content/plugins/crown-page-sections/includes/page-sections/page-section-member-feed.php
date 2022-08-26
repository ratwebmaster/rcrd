<?php

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Checkbox as CheckboxInput;
use Crown\Form\Input\CheckboxSet;
use Crown\Form\Input\Select;
use Crown\UIRule;

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionMemberFeed')) {
	class CrownPageSectionMemberFeed extends CrownPageSection {


		protected static $name = 'Member Feed Section';


		protected static function getContentFields() {
			return array(
				static::getSectionIntroContentField(),
				new FieldGroup(array(
					'label' => 'Members',
					'fields' => array(
                        new Field(array(
                            'label' => 'Team to Display',
                            'input' => new Select(array('name' => 'team')),
                            'getOutputCb' => array(__CLASS__, 'setTeamSelectInputOptions')
                        )),
                        new Field(array(
                            'label' => 'Groups to Display',
                            'input' => new CheckboxSet(array('name' => 'display_group', 'options' => array(
                                array('value' => 'skaters', 'label' => 'Skaters'),
                                array('value' => 'coaches', 'label' => 'Coaches'),
                                array('value' => 'alums', 'label' => 'Alums'),
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



        public static function setTeamSelectInputOptions($field) {
            $options[] = array('value' => '', 'label' => 'Please Select');
            //$teams = get_categories(array( 'taxonomy' => 'member_team', 'posts_per_page' => -1 ));
            $teams = get_terms( array(
                'taxonomy' => 'member_team',
                'hide_empty' => false,
                'hierarchical' => true,
                'orderby' => 'date',
                'posts_per_page' => -1
            ) );
            foreach($teams as $team) {
                $options[] = array('value' => $team->term_id, 'label' => $team->name);
            }
            $field->getInput()->setOptions($options);

        }


        public static function setStatusSelectInputOptions($field) {
            $options[] = array('value' => '', 'label' => 'Please Select');
            $posts = get_posts(array('post_type' => 'member', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
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