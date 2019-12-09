<?php
/**
 * Plugin Name: Crown Members
 * Description: Adds support for member entries.
 * Version: 1.1.0
 * Author: Jordan Crown
 * Author URI: http://www.jordancrown.com
 * License: GNU General Pulic License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use Crown\Post\Type as PostType;
use Crown\Post\MetaBox;
use Crown\Post\Taxonomy;

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Media as MediaInput;
use Crown\Form\Input\RichTextarea;

use Crown\ListTableColumn;
use Crown\Shortcode;


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownMember')) {
	class CrownMember {

		public static $init = false;

		public static $MemberPostType;
		public static $MemberDepartmentTaxonomy;
		public static $MemberListShortcode;


		public static function init() {
			if(self::$init) return;
			self::$init = true;

			register_activation_hook(__FILE__, array(__CLASS__, 'activate'));
			register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivate'));

			add_action('after_setup_theme', array(__CLASS__, 'registerPostTypes'));
			add_action('after_setup_theme', array(__CLASS__, 'registerTaxonomies'));
			add_action('after_setup_theme', array(__CLASS__, 'registerShortcodes'));

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
				foreach(array('manage', 'edit', 'delete') as $cap) {
					if($role->has_cap('manage_categories')) {
						$role->add_cap($cap.'_member_departments');
					}
				}
				if($role->has_cap('edit_posts')) {
					$role->add_cap('assign_member_departments');
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach($wp_roles->role_objects as $role) {
				foreach(array('publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private') as $cap) {
					$role->remove_cap($cap.'_members');
				}
				foreach(array('manage', 'edit', 'delete', 'assign') as $cap) {
					$role->remove_cap($cap.'_member_departments');
				}
			}
			
			flush_rewrite_rules();
		}


		public static function registerPostTypes() {

			self::$MemberPostType = new PostType(array(
				'name' => 'member',
				'singularLabel' => 'Member',
				'pluralLabel' => 'Members',
				'settings' => array(
					'supports' => false,
					'rewrite' => array('slug' => 'member', 'with_front' => false),
					'menu_icon' => 'dashicons-businessman',
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
								'class' => 'no-border two-column large-left',
								'fields' => array(
									new FieldGroup(array(
										'class' => 'no-border',
										'fields' => array(
											new FieldGroup(array(
												'class' => 'no-border two-column',
												'saveMetaCb' => array(__CLASS__, 'saveMemberPostAttributes'),
												'fields' => array(
													new Field(array(
														'label' => 'First Name',
														'input' => new TextInput(array('name' => 'member_first_name', 'class' => 'input-large'))
													)),
													new Field(array(
														'label' => 'Last Name',
														'input' => new TextInput(array('name' => 'member_last_name', 'class' => 'input-large'))
													))
												)
											)),
											new Field(array(
												'label' => 'Job Title',
												'input' => new TextInput(array('name' => 'member_title'))
											)),
											new Field(array(
												'label' => 'Phone Number',
												'input' => new TextInput(array('name' => 'member_phone'))
											)),
											new Field(array(
												'label' => 'Email',
												'input' => new TextInput(array('name' => 'member_email'))
											)),
											new Field(array(
												'label' => 'Office',
												'input' => new TextInput(array('name' => 'member_office'))
											)),
											new Field(array(
												'label' => 'LinkedIn Profile URL',
												'input' => new TextInput(array('name' => 'member_linkedin_url'))
											))
										)
									)),
									new FieldGroup(array(
										'class' => 'no-border',
										'fields' => array(
											new Field(array(
												'label' => 'Headshot Image',
												'input' => new MediaInput(array('name' => 'member_headshot', 'buttonLabel' => 'Select Image', 'mimeType' => 'image', 'class' => 'headshot'))
											))
										)
									))
								)
							))
						)
					)),
					new MetaBox(array(
						'id' => 'member-bio',
						'title' => 'Member Bio',
						'priority' => 'high',
						'fields' => array(
							new Field(array(
								'input' => new RichTextarea(array('name' => 'member_bio', 'rows' => 16))
							))
						)
					))
				),
				'listTableColumns' => array(
					new ListTableColumn(array(
						'key' => 'member-image',
						'title' => '',
						'position' => 1,
						'outputCb' => function($postId, $args) {
							$imageId = get_post_meta($postId, 'member_headshot', true);
							$imageSrc = wp_get_attachment_image_src($imageId, 'thumbnail');
							if($imageSrc) echo '<a href="'.admin_url('post.php?post='.$postId.'&action=edit').'"><div class="image-wrap"><div class="image" style="background-image: url('.$imageSrc[0].');"></div></div></a>';
						}
					)),
					new ListTableColumn(array(
						'key' => 'member-details',
						'title' => 'Details',
						'position' => 3,
						'outputCb' => array(__CLASS__, 'outputMemberDetailsColumn')
					))
				)
			));

		}


		public static function saveMemberPostAttributes($field, $input, $type, $objectId, $value) {
			$postData = array(
				'ID' => $objectId,
				'post_title' => array(),
				'post_name' => ''
			);
			if(isset($input['member_first_name']) && !empty($input['member_first_name'])) $postData['post_title'][] = $input['member_first_name'];
			if(isset($input['member_last_name']) && !empty($input['member_last_name'])) $postData['post_title'][] = $input['member_last_name'];
			$postData['post_title'] = implode(' ', $postData['post_title']);
			$postData['post_name'] = sanitize_title($postData['post_title']);
			wp_update_post($postData);
		}


		public static function outputMemberDetailsColumn($postId, $args) {
			$details = array(
				'title' => get_post_meta($postId, 'member_title', true),
				'phone' => get_post_meta($postId, 'member_phone', true),
				'email' => get_post_meta($postId, 'member_email', true),
				'office' => get_post_meta($postId, 'member_office', true)
			);
			$output = array();
			if(!empty($details['title'])) $output[] = '<em>'.$details['title'].'</em>';
			if(!empty($details['phone'])) $output[] = $details['phone'];
			if(!empty($details['email'])) $output[] = '<a href="mailto:'.$details['email'].'">'.$details['email'].'</a>';
			if(!empty($details['office'])) $output[] = 'Office: '.$details['office'];
			echo implode('<br>', $output);
		}


		public static function registerTaxonomies() {

			self::$MemberDepartmentTaxonomy = new Taxonomy(array(
				'name' => 'member_department',
				'singularLabel' => 'Department',
				'pluralLabel' => 'Departments',
				'postTypes' => array('member'),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array('slug' => 'departments', 'with_front' => false),
					'show_in_nav_menus' => false,
					'publicly_queryable' => false,
					'labels' => array(
						'menu_name' => 'Departments',
						'all_items' => 'All Departments'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_member_departments',
						'edit_terms' => 'edit_member_departments',
						'delete_terms' => 'delete_member_departments',
						'assign_terms' => 'assign_member_departments'
					)
				)
			));

		}


		public static function registerShortcodes() {

			self::$MemberListShortcode = new Shortcode(array(
				'tag' => 'member_list',
				'getOutputCb' => array(__CLASS__, 'getMemberListShortcode'),
				'defaultAtts' => array(
					'department' => ''
				)
			));

		}


		public static function getMemberListShortcode($atts, $content) {

			$queryArgs = array(
				'post_type' => 'member',
				'posts_per_page' => -1,
				'meta_key' => 'member_last_name',
				'orderby' => 'meta_value title',
				'order' => 'ASC'
			);
			if(!empty($atts['department'])) {
				$queryArgs['tax_query'] = array(
					array(
						'taxonomy' => 'member_department',
						'field' => strcmp(intval($atts['department']), $atts['department']) === 0 ? 'term_id' : 'slug',
						'terms' => $atts['department']
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