<?php
/**
 * Plugin Name: Crown Sponsors
 * Description: Adds support for sponsor entries.
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
use Crown\Form\Input\Date as DateInput;
use Crown\Form\Input\Hidden as HiddenInput;

use Crown\ListTableColumn;
use Crown\Shortcode;
use Crown\UIRule;


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownSponsor')) {
    class CrownSponsor {

        public static $init = false;

        public static $sponsorPostType;
        public static $sponsorBenefitsTaxonomy;
        public static $MemberListShortcode;


        public static function init() {
            if(self::$init) return;
            self::$init = true;

            register_activation_hook(__FILE__, array(__CLASS__, 'activate'));
            register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivate'));

            add_action('after_setup_theme', array(__CLASS__, 'registerPostTypes'));
            add_action('after_setup_theme', array(__CLASS__, 'registerTaxonomies'));
//            add_action('after_setup_theme', array(__CLASS__, 'registerShortcodes'));

            add_action('admin_enqueue_scripts', array(__CLASS__, 'registerAdminScripts'));
            add_action('admin_enqueue_scripts', array(__CLASS__, 'registerAdminStyles'));

        }


        public static function activate() {
            global $wp_roles;

            foreach($wp_roles->role_objects as $role) {
                foreach(array('publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private') as $cap) {
                    if($role->has_cap($cap.'_posts')) {
                        $role->add_cap($cap.'_sponsors');
                    }
                }
//				foreach(array('manage', 'edit', 'delete') as $cap) {
//					if($role->has_cap('manage_categories')) {
//						$role->add_cap($cap.'_sponsor_benefits');
//					}
//				}
//				if($role->has_cap('edit_posts')) {
//					$role->add_cap('assign_sponsor_benefits');
//				}
            }

            flush_rewrite_rules();
        }


        public static function deactivate() {
            global $wp_roles;

            foreach($wp_roles->role_objects as $role) {
                foreach(array('publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private') as $cap) {
                    $role->remove_cap($cap.'_sponsors');
                }
//				foreach(array('manage', 'edit', 'delete', 'assign') as $cap) {
//					$role->remove_cap($cap.'_sponsor_benefits');
//				}
            }

            flush_rewrite_rules();
        }


        public static function registerPostTypes() {

            self::$sponsorPostType = new PostType(array(
                'name' => 'sponsor',
                'singularLabel' => 'Sponsor',
                'pluralLabel' => 'Sponsors',
                'settings' => array(
                    'supports' => array('title', 'thumbnail'),
                    'rewrite' => array('slug' => 'sponsors', 'with_front' => false),
                    'menu_icon' => 'dashicons-money',
                    'has_archive' => false,
                    'publicly_queryable' => true,
                    'show_in_nav_menus' => false,
                    'exclude_from_search' => false,
                    'capability_type' => array('sponsor', 'sponsors'),
                    'map_meta_cap' => true
                ),
                'metaBoxes' => array(
                    new MetaBox(array(
                        'id' => 'sponsor-details',
                        'title' => 'Sponsor Details',
                        'priority' => 'high',
                        'fields' => array(
                            new FieldGroup(array(
                                'label' => 'Sponsorship Effective Dates',
                                'class' => 'no-border two-column',
                                'fields' => array(
                                    new FieldGroup(array(
                                        'class' => 'no-border',
                                        'fields' => array(
                                            new Field(array(
                                                'label' => 'Start Date',
                                                'input' => new DateInput(array(
                                                    'name' => 'sponsorship_start_date',
                                                    'id' => 'sponsorship-start-date-input',
                                                    'datepickerOptions' => array(
                                                        'onClose' => 'function(selectedDate) { $("#sponsorship-end-date-input").datepicker("option", "minDate", selectedDate); }'
                                                    )
                                                ))
                                            )),
                                            new Field(array(
                                                'input' => new HiddenInput(array('name' => 'sponsorship_start_timestamp'))
                                            )),
                                            new Field(array(
                                                'input' => new HiddenInput(array('name' => 'sponsorship_start_year'))
                                            )),
                                            new Field(array(
                                                'input' => new HiddenInput(array('name' => 'sponsorship_start_month'))
                                            ))
                                        )
                                    )),
                                    new FieldGroup(array(
                                        'class' => 'no-border',
                                        'fields' => array(
                                            new Field(array(
                                                'label' => 'End Date',
                                                'input' => new DateInput(array(
                                                    'name' => 'sponsorship_end_date',
                                                    'id' => 'sponsorship-end-date-input',
                                                    'datepickerOptions' => array(
                                                        'onClose' => 'function(selectedDate) { $("#sponsorship-start-date-input").datepicker("option", "maxDate", selectedDate); }'
                                                    )
                                                ))
                                            )),
                                            new Field(array(
                                                'input' => new HiddenInput(array('name' => 'sponsorship_end_timestamp'))
                                            )),
                                            new Field(array(
                                                'input' => new HiddenInput(array('name' => 'sponsorship_end_year'))
                                            )),
                                            new Field(array(
                                                'input' => new HiddenInput(array('name' => 'sponsorship_end_month'))
                                            ))
                                        )
                                    )),
                                ),
                            )),
                            new Field(array(
                                'label' => 'Website',
                                'input' => new TextInput(array('name' => 'sponsor_website'))
                            )),

                        ),
                                'saveMetaCb' => array(__CLASS__, 'saveSponsorshipDateMetaBox'),
                    )),
                ),
                'listTableColumns' => array(
//                    new ListTableColumn(array(
//                        'key' => 'member-image',
//                        'title' => 'Photo',
//                        'position' => 1,
//                        'outputCb' => function($postId, $args) {
//                            $imageId = get_post_meta($postId, '_thumbnail_id', true);
//                            $imageSrc = wp_get_attachment_image_src($imageId, 'thumbnail');
//                            if($imageSrc) echo '<a href="'.admin_url('post.php?post='.$postId.'&action=edit').'"><div class="image-wrap"><div class="image" style="background-image: url('.$imageSrc[0].');"></div></div></a>';
//                        }
//                    )),
                )
            ));

        }


        public static function saveSponsorshipDateMetaBox($post, $input, $args, $fields) {
            $startDate = isset($input['sponsorship_start_date']) ? $input['sponsorship_start_date'] : '';
//            $startYear = isset($input['sponsorship_start_year']) ? $input['sponsorship_start_year'] : '';
//            $startMonth = isset($input['sponsorship_start_month']) ? $input['sponsorship_start_month'] : '';

            $endDate = isset($input['sponsorship_end_date']) ? $input['sponsorship_end_date'] : '';
//            $endYear = isset($input['sponsorship_end_year']) ? $input['sponsorship_end_year'] : '';
//            $endMonth = isset($input['sponsorship_end_month']) ? $input['sponsorship_end_month'] : '';

//            $startTimestamp = strtotime($startDate) ? strtotime($startDate) : '';
//            $endTimestamp = strtotime($endDate) ? strtotime($endDate) : '';

//            if(!empty($startDate)) {
//                $input['sponsorship_start_year'] = date('Y', $startDate);
//                $input['sponsorship_start_month'] = date('F', $startDate);
//            }
//            if(!empty($endDate)) {
//                $input['sponsorship_end_year'] = date('Y', $endDate);
//                $input['sponsorship_end_month'] = date('F', $endDate);
//            }
//            if(empty($startDate) && !empty($endDate)) {
//                $startDate = $endDate - (60*60*24*365);
//                $input['sponsorship_start_date'] = date('Y-m-d', $startDate);
//                $input['sponsorship_start_time'] = date('H:i:s', $startDate);
//            }
//            if(!empty($startDate) && empty($endDate)) {
//                $endDate = $startDate + (60*60*24*365);
//                $input['sponsorship_end_date'] = date('Y-m-d', $endDate);
//                $input['sponsorship_end_time'] = date('H:i:s', $endDate);
//            }

            if(!empty($startDate)) $input['sponsorship_start_timestamp'] = date('Y-m-d H:i:s',strtotime($startDate));
            if(!empty($endDate)) $input['sponsorship_end_timestamp'] = date('Y-m-d H:i:s',strtotime($endDate));

            foreach($fields as $field) {
                $field->saveValue($input, 'post', $post->ID);
            }
        }


        public static function registerTaxonomies() {

            self::$sponsorBenefitsTaxonomy = new Taxonomy(array(
                'name' => 'sponsor_benefit',
                'singularLabel' => 'Benefit',
                'pluralLabel' => 'Benefits',
                'postTypes' => array('sponsor'),
                'settings' => array(
                    'hierarchical' => true,
                    'rewrite' => array('slug' => 'benefits', 'with_front' => false),
                    'show_in_nav_menus' => false,
                    'publicly_queryable' => false,
                    'labels' => array(
                        'menu_name' => 'Benefits',
                        'all_items' => 'All Benefits'
                    ),
//					'capabilities' => array(
//						'manage_terms' => 'manage_sponsor_benefits',
//						'edit_terms' => 'edit_sponsor_benefits',
//						'delete_terms' => 'delete_sponsor_benefits',
//						'assign_terms' => 'assign_sponsor_benefits'
//					)
                ),
//                'fields' => array(
//                    new Field(array(
//                        'label' => 'Team Logo',
//                        'input' => new MediaInput(array('name' => 'team_logo', 'buttonLabel' => 'Select Image', 'mimeType' => 'image'))
//                    ))
//                ),
            ));

        }


        public static function registerShortcodes() {

            self::$MemberListShortcode = new Shortcode(array(
                'tag' => 'sponsor_list',
                'getOutputCb' => array(__CLASS__, 'getSponsorListShortcode'),
                'defaultAtts' => array(
                    'benefits' => ''
                )
            ));

        }


        public static function getSponsorListShortcode($atts, $content) {

            $queryArgs = array(
                'post_type' => 'sponsor',
                'posts_per_page' => -1,
                'meta_key' => 'title',
                'orderby' => 'meta_value title',
                'order' => 'ASC'
            );
//            if(!empty($atts['team'])) {
//                $queryArgs['tax_query'] = array(
//                    array(
//                        'taxonomy' => 'member_team',
//                        'field' => strcmp(intval($atts['team']), $atts['team']) === 0 ? 'term_id' : 'slug',
//                        'terms' => $atts['team']
//                    )
//                );
//            }
            $sponsors = get_posts($queryArgs);

//            $template = locate_template_with_fallback('member/member-list.php', plugin_dir_path(__FILE__).'templates');
//            if(!empty($template)) {
//                ob_start();
//                include($template);
//                return ob_get_clean();
//            }
        }


        public static function registerAdminScripts($hook) {

            $screen = get_current_screen();
            if($screen->base == 'post' && in_array($screen->post_type, array('sponsor'))) {

                $js = "
					(function($) {
						$(document).ready(function() {
							$(document).on('change', '.ce-field-dependent-control', function(e) {
								var input = $(this);
								var inputClasses = input.attr('class').trim().split(/\s+/);
								var key = false;
								for(var i = 0; i < inputClasses.length; i++) {
									var matches = inputClasses[i].match(/^ce-field-dependent-control-(.+)$/);
									if(matches) {
										key = matches[1];
										break;
									}
								}
								if(key) {
									var container = input.closest('.entry-fields');
									if(!container.length) container = $('body');
									$('.ce-field-dependent-' + key, container).removeClass('active');
									var value = input.val();
									if(input.is('[type=checkbox]')) value = input.is(':checked') ? value : 0;
									$('.ce-field-dependent-' + key + '-' + value, container).addClass('active');
								}
							})
							$('.ce-field-dependent-control').trigger('change');
						});
					})(jQuery);
				";
                wp_add_inline_script('common', $js);

            }

        }


        public static function registerAdminStyles($hook) {

            $screen = get_current_screen();
            if($screen->base == 'edit' && $screen->post_type == 'sponsor') {

                $css = "
					.ce-field-dependent {
						display: none !important;
					}
					.ce-field-dependent.active {
						display: block !important;
					}
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

            } else if($screen->base == 'post' && $screen->post_type == 'sponsor') {

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

if(class_exists('CrownSponsor')) {
    CrownSponsor::init();
}