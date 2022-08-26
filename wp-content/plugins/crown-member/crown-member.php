<?php
/**
 * Plugin Name: Crown Members
 * Description: Adds support for member entries.
 * Version: 1.1.0
 * Author: RCRD Webmaster
 * Author URI: http://www.ratcityrollerderby.com
 * License: GNU General Pulic License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use Crown\Post\Type as PostType;
use Crown\Post\MetaBox;
use Crown\Post\Taxonomy;
use Crown\AdminPage;

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Media as MediaInput;
use Crown\Form\Input\RichTextarea;
use Crown\Form\Input\Select;
use Crown\Form\FieldRepeater;

use Crown\ListTableColumn;
use Crown\Shortcode;
use Crown\UIRule;


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownMember')) {
    class CrownMember {

		public static $init = false;

		public static $MemberPostType;
		public static $MemberTeamTaxonomy;
		public static $MemberStatusTaxonomy;
		public static $MemberListShortcode;
		public static $MemberInterviewPage;


		public static function init() {
			if(self::$init) return;
			self::$init = true;

			register_activation_hook(__FILE__, array(__CLASS__, 'activate'));
			register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivate'));

			add_action('after_setup_theme', array(__CLASS__, 'registerPostTypes'));
			add_action('after_setup_theme', array(__CLASS__, 'registerTaxonomies'));
			add_action('after_setup_theme', array(__CLASS__, 'registerShortcodes'));
//            add_action('after_setup_theme', array(__CLASS__, 'registerInterviewPage'));

			add_action('admin_enqueue_scripts', array(__CLASS__, 'registerAdminStyles'));

		}


		public static function activate() {
			global $wp_roles;
			
			foreach($wp_roles->role_objects as $role) {
				foreach(array('publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private') as $cap) {
                    if($role->has_cap($cap.'_posts')) {
                        $role->add_cap($cap.'_members');
                    }
				}
//				foreach(array('manage', 'edit', 'delete') as $cap) {
//					if($role->has_cap('manage_categories')) {
//						$role->add_cap($cap.'_member_teams');
//					}
//				}
//				if($role->has_cap('edit_posts')) {
//					$role->add_cap('assign_member_teams');
//				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach($wp_roles->role_objects as $role) {
				foreach(array('publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private') as $cap) {
                    $role->remove_cap($cap.'_members');
				}
//				foreach(array('manage', 'edit', 'delete', 'assign') as $cap) {
//					$role->remove_cap($cap.'_member_teams');
//				}
			}
			
			flush_rewrite_rules();
		}


		public static function registerPostTypes() {

			self::$MemberPostType = new PostType(array(
                'name' => 'member',
                'singularLabel' => 'Member',
                'pluralLabel' => 'Members',
                'settings' => array(
                    'supports' => array('title', 'thumbnail', 'excerpt'),
                    'rewrite' => array('slug' => 'member', 'with_front' => false),
                    'menu_icon' => 'dashicons-id',
                    'has_archive' => false,
                    'publicly_queryable' => true,
                    'show_in_nav_menus' => false,
                    'exclude_from_search' => false,
                    'capability_type' => array('member', 'members'),
                    'map_meta_cap' => true
                ),
				'metaBoxes' => array(
					new MetaBox(array(
						'id' => 'member-details',
						'title' => 'Member Details',
						'priority' => 'high',
						'fields' => array(
							new FieldGroup(array(
								'class' => 'no-border two-column',
								'fields' => array(
                                    new Field(array(
                                        'label' => 'Number',
                                        'input' => new TextInput(array('name' => 'member_number'))
                                    )),
                                    new Field(array(
                                        'label' => 'Pronouns',
                                        'input' => new TextInput(array('name' => 'member_pronouns'))
                                    )),
								)
							)),
                            new Field(array(
                                'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('taxonomy' => 'member_team', 'inputName' => 'tax_input[member_team][announcers]'), 'value' => 1))),
                                'label' => 'Website (where applicable for volunteers or affiliates without numbers)',
                                'input' => new TextInput(array('name' => 'member_website'))
                            )),
                            new Field(array(
                                'label' => 'Description/Bio copy',
                                'input' => new RichTextarea(array('name' => 'member_bio', 'rows' => '10'))
                            )),
						)
					)),
					new MetaBox(array(
						'id' => 'member-interview',
						'title' => 'Member Interview',
						'priority' => 'high',
						'fields' => array(
                            new FieldRepeater(array(
                                'addNewLabel' => 'Select A Question',
                                'name' => 'member_questions',
                                'fields' => array(
                                    new Field(array(
                                        'label' => 'Question',
                                        'input' => new Select(array('name' => 'question', 'options' => array(
                                            array('value' => 'birthdate', 'label' => 'What is your birthdate?'),
                                            array('value' => 'hometown', 'label' => 'What is your hometown?'),
                                            array('value' => 'sports', 'label' => 'What other sports have you played?'),
                                            array('value' => 'position', 'label' => 'What position do you prefer on the track?'),
                                            array('value' => 'career', 'label' => 'What do you do for a living?'),
                                            array('value' => 'quote', 'label' => 'Favorite Quote'),
                                            array('value' => 'history', 'label' => 'Derby History'),
                                            array('value' => 'injury', 'label' => 'Injuries'),
                                            array('value' => 'movie', 'label' => 'Favorite movie?'),
                                            array('value' => 'awards', 'label' => 'Awards?'),
                                            array('value' => 'song', 'label' => 'Get Pumped-Up Song'),
                                            array('value' => 'fightsong', 'label' => 'Fight Song'),
                                        )))
                                    )),
                                    new Field(array(
                                        'label' => 'Answer',
                                        'input' => new TextInput(array('name' => 'answer'))
                                    ))
                                )
                            ))
						)
					))
				),
				'listTableColumns' => array(
					new ListTableColumn(array(
						'key' => 'member-image',
						'title' => 'Photo',
						'position' => 1,
						'outputCb' => function($postId, $args) {
							$imageId = get_post_meta($postId, '_thumbnail_id', true);
							$imageSrc = wp_get_attachment_image_src($imageId, 'thumbnail');
							if($imageSrc) echo '<a href="'.admin_url('post.php?post='.$postId.'&action=edit').'"><div class="image-wrap"><div class="image" style="background-image: url('.$imageSrc[0].');"></div></div></a>';
						}
					)),
					new ListTableColumn(array(
						'key' => 'member-details',
						'title' => 'Number',
						'position' => 3,
						'outputCb' => array(__CLASS__, 'outputMemberDetailsColumn')
					))
				)
			));

		}


		public static function outputMemberDetailsColumn($postId, $args) {
			$details = array(
				'number' => get_post_meta($postId, 'member_number', true)
			);
            $output = '';
			if(!empty($details['number'])) $output = $details['number'];
			echo $output;
		}


		public static function registerTaxonomies() {

			self::$MemberTeamTaxonomy = new Taxonomy(array(
				'name' => 'member_team',
				'singularLabel' => 'Team',
				'pluralLabel' => 'Teams',
				'postTypes' => array('member'),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array('slug' => 'teams', 'with_front' => false),
                    'has_archive' => true,
					'show_in_nav_menus' => false,
					'publicly_queryable' => false,
					'labels' => array(
						'menu_name' => 'Teams',
						'all_items' => 'All Teams'
					),
//					'capabilities' => array(
//						'manage_terms' => 'manage_member_teams',
//						'edit_terms' => 'edit_member_teams',
//						'delete_terms' => 'delete_member_teams',
//						'assign_terms' => 'assign_member_teams'
//					)
				),
                'fields' => array(
                    new Field(array(
                        'label' => 'Team Logo',
                        'input' => new MediaInput(array('name' => 'team_logo', 'buttonLabel' => 'Select Image', 'mimeType' => 'image'))
                    )),
                    new FieldRepeater(array(
                        'name' => 'team_coaches',
                        'label' => 'Team Coaches',
                        'addNewLabel' => 'Add a Coach',
                        'fields' => array(
                            new Field(array(
                                'label' => 'Coach',
                                'input' => new Select(array('name' => 'coach', 'select2' => array('placeholder' => 'Select Member...', 'allowClear' => true))),
                                'getOutputCb' => array(__CLASS__, 'setMemberSelectInputOptions')
                            )),
                        )
                    )),
                    new FieldRepeater(array(
                        'name' => 'team_captains',
                        'label' => 'Team Captains',
                        'addNewLabel' => 'Add a Captain',
                        'fields' => array(
                            new Field(array(
                                'label' => 'Captain',
                                'input' => new Select(array('name' => 'captain', 'select2' => array('placeholder' => 'Select Member...', 'allowClear' => true))),
                                'getOutputCb' => array(__CLASS__, 'setMemberSelectInputOptions')
                            )),
                        )
                    )),
//                    new FieldRepeater(array(
//                        'label' => 'Captains',
//                        'name' => 'captains',
//                        'fields' => array(
//                            new Field(array(
//                                'input' => new Select(array('name' => 'captain')),
//                                'getOutputCb' => array(__CLASS__, 'setMemberSelectInputOptions')
//                            ))
//                        )
//                    )),
                ),
			));

            self::$MemberStatusTaxonomy = new Taxonomy(array(
                'name' => 'member_status',
                'singularLabel' => 'Status',
                'pluralLabel' => 'Statuses',
                'postTypes' => array('member'),
                'settings' => array(
                    'hierarchical' => true,
                    'rewrite' => array('slug' => 'statuses', 'with_front' => false),
                    'show_in_nav_menus' => false,
                    'publicly_queryable' => false,
                    'labels' => array(
                        'menu_name' => 'Status',
                        'all_items' => 'All Statuses'
                    ),
//                    'capabilities' => array(
//                        'manage_terms' => 'manage_member_statuses',
//                        'edit_terms' => 'edit_member_statuses',
//                        'delete_terms' => 'delete_member_statuses',
//                        'assign_terms' => 'assign_member_statuses'
//                    )
                )
            ));

		}


        public static function setMemberSelectInputOptions($field) {
            $options[] = array('value' => '', 'label' => 'Please Select');
            $posts = get_posts(array('post_type' => 'member', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
            foreach($posts as $post) {
                $options[] = array('value' => $post->ID, 'label' => $post->post_title, 'depth' => 0);
            }
            $field->getInput()->setOptions($options);

        }


		public static function registerShortcodes() {

			self::$MemberListShortcode = new Shortcode(array(
				'tag' => 'member_list',
				'getOutputCb' => array(__CLASS__, 'getMemberListShortcode'),
				'defaultAtts' => array(
					'team' => ''
				)
			));

		}


        public static function registerInterviewPage() {

            self::$MemberInterviewPage = new AdminPage(array(
                'parent' => 'edit.php?post_type=member',
                'position' => 0,
                'title' => 'Interview Questions',
                'menuTitle' => 'Member Questions',
                'capability' => 'manage_options',
                'fields' => array(
                    new FieldGroup(array(
                        'outputCb' => array(__CLASS__, 'getQuestionsList'),
                    )),
                    new FieldRepeater(array(
                        'addNewLabel' => 'Add New Question',
                        'name' => 'member_questions',
                        'fields' => array(
                            new Field(array(
                                'label' => 'Question',
                                'input' => new Select(array('name' => 'question', 'options' => array(
                                    array('value' => 'q1', 'label' => 'What is your birthdate?'),
                                    array('value' => 'q2', 'label' => 'What is your hometown?'),
                                    array('value' => 'q3', 'label' => 'What other sports have you played?'),
                                    array('value' => 'q4', 'label' => 'What position do you prefer on the track?'),
                                    array('value' => 'q5', 'label' => 'What do you do for a living?'),
                                )))
                            )),
                        )
                    ))
                )

            ));

        }

        public static function getQuestionsList() {
		    return "Hello!";
        }


		public static function getMemberListShortcode($atts, $content) {

			$queryArgs = array(
				'post_type' => 'member',
				'posts_per_page' => -1,
				'meta_key' => 'member_last_name',
				'orderby' => 'meta_value title',
				'order' => 'ASC'
			);
			if(!empty($atts['team'])) {
				$queryArgs['tax_query'] = array(
					array(
						'taxonomy' => 'member_team',
						'field' => strcmp(intval($atts['team']), $atts['team']) === 0 ? 'term_id' : 'slug',
						'terms' => $atts['team']
					)
				);
			}
			$Members = get_posts($queryArgs);

			$template = locate_template_with_fallback('member/member-list.php', plugin_dir_path(__FILE__).'templates');
			if(!empty($template)) {
				ob_start();
				include($template);
				return ob_get_clean();
			}
		}


		public static function registerAdminStyles($hook) {

			$screen = get_current_screen();
			if($screen->base == 'edit' && $screen->post_type == 'member') {

				$css = "
					table.wp-list-table	th.column-member-image,
					table.wp-list-table	td.column-member-image {
						width: 42px;
					}
					table.wp-list-table	td.column-member-image .image {
						padding-top: 100%;
						background-position: center center;
						background-repeat: no-repeat;
						background-size: cover;
					}
				";
				wp_add_inline_style('common', $css);

			} else if($screen->base == 'post' && $screen->post_type == 'member') {

				$css = "
					#member-details .crown-media-input.mime-type-image.headshot .media-input-preview {
						max-width: 150px;
					}
				";
				wp_add_inline_style('common', $css);

			}

		}


	}
}

if(class_exists('CrownMember')) {
	CrownMember::init();
}