<?php
/**
 * Plugin Name: Crown Events
 * Description: Adds support for event entries.
 * Version: 1.1.0
 * Author: Jordan Crown
 * Author URI: http://www.jordancrown.com
 * License: GNU General Pulic License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use Crown\Form\FieldRepeater;
use Crown\Post\Type as PostType;
use Crown\Post\MetaBox;
use Crown\Post\Taxonomy;

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\Input\Hidden as HiddenInput;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Checkbox as CheckboxInput;
use Crown\Form\Input\RadioSet;
use Crown\Form\Input\Date as DateInput;
use Crown\Form\Input\Time as TimeInput;
use Crown\Form\Input\GeoCoordinates as GeoCoordinatesInput;
use Crown\Form\Input\Color as ColorInput;
use Crown\Form\Input\RichTextarea;
use Crown\Form\Input\Select;
use Crown\Form\Input\Media as MediaInput;

use Crown\ListTableColumn;
use Crown\UIRule;

use Crown\Api\GoogleMaps;
use PhpOffice\PhpSpreadsheet\Writer\Ods\Meta;

use Crown\Shortcode;

//use Crown\AdminPage;


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownEvents')) {
    class CrownEvents {

        public static $init = false;

        public static $eventPostType;
        public static $eventTypeTaxonomy;
        public static $eventListShortcode;


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
            add_action('wp_enqueue_scripts', array(__CLASS__, 'registerScripts'));
            add_action('wp_enqueue_scripts', array(__CLASS__, 'registerStyles'));


        }


        public static function activate() {
            global $wp_roles;

            foreach($wp_roles->role_objects as $role) {
                foreach(array('publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private') as $cap) {
                    if($role->has_cap($cap.'_posts')) {
                        $role->add_cap($cap.'_events');
                    }
                }
//                foreach(array('manage', 'edit', 'delete') as $cap) {
//                    if($role->has_cap('manage_categories')) {
//                        $role->add_cap($cap.'_event_types');
//                    }
//                }
//                if($role->has_cap('edit_posts')) {
//                    $role->add_cap('assign_event_types');
//                }
            }

            flush_rewrite_rules();
        }


        public static function deactivate() {
            global $wp_roles;

            foreach($wp_roles->role_objects as $role) {
                foreach(array('publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private') as $cap) {
                    $role->remove_cap($cap.'_events');
                }
//                foreach(array('manage', 'edit', 'delete', 'assign') as $cap) {
//                    $role->remove_cap($cap.'_event_types');
//                }
            }

            flush_rewrite_rules();
        }

        public static function registerPostTypes() {

            self::$eventPostType = new PostType(array(
                'name' => 'event',
                'singularLabel' => 'Event',
                'pluralLabel' => 'Events',
                'settings' => array(
                    'supports' => array('title', 'thumbnail', 'page-attributes', 'excerpt'),
                    'rewrite' => array('slug' => 'events', 'with_front' => false),
                    'menu_icon' => 'dashicons-calendar',
                    'has_archive' => true,
                    'publicly_queryable' => true,
                    'exclude_from_search' => false,
                    'show_in_nav_menus' => true,
                    'capability_type' => array('event', 'events'),
                    'map_meta_cap' => true
                ),
                'metaBoxes' => array(
                    new MetaBox(array(
                        'id' => 'event-date',
                        'title' => 'Event Date',
                        'priority' => 'high',
                        'fields' => array(
                            new Field(array(
                                'label' => 'Date',
                                'input' => new DateInput(array(
                                    'name' => 'event_start_date',
                                    'id' => 'event-start-date-input',
                                    'datepickerOptions' => array(
                                        'onClose' => 'function(selectedDate) { $("#event-end-date-input").datepicker("option", "minDate", selectedDate); }'
                                    )
                                ))
                            )),
                            new FieldGroup(array(
                                'class' => 'no-border two-column',
                                'fields' => array(
                                    new Field(array(
                                        'label' => 'Doors Open',
                                        'input' => new TimeInput(array('name' => 'event_time_doors', 'defaultValue' => '12:00:00')),
//                                        'class' => 'ce-field-dependent ce-field-dependent-ead ce-field-dependent-ead-0',
                                    )),
                                    new Field(array(
                                        'label' => 'First Whistle',
                                        'input' => new TimeInput(array('name' => 'event_time_whistle', 'defaultValue' => '12:00:00')),
//                                        'class' => 'ce-field-dependent ce-field-dependent-ead ce-field-dependent-ead-0',
                                    )),
                                )
                            )),
                            new Field(array(
                                'input' => new HiddenInput(array('name' => 'event_start_timestamp'))
                            )),
                        ),
                        'saveMetaCb' => array(__CLASS__, 'saveEventDateMetaBox'),
                    )),
                    new MetaBox(array(
                        'id' => 'event-location',
                        'title' => 'Event Location',
                        'priority' => 'high',
                        'fields' => array(
                            new Field(array(
                                'label' => 'Venue Name',
                                'input' => new TextInput(array('name' => 'event_venue')),
                            )),
                            new FieldGroup(array(
                                'class' => 'no-border two-column',
                                'fields' => array(
                                    new Field(array(
                                        'label' => 'Address',
                                        'input' => new TextInput(array('name' => 'event_address')),
                                    )),
                                    new Field(array(
                                        'label' => 'City, State Zip',
                                        'input' => new TextInput(array('name' => 'event_city_state_zip')),
                                    )),
                                )
                            )),
                        )
                    )),
                    new MetaBox(array(
                        'id' => 'event-tickets',
                        'title' => 'Ticketing Info',
                        'priority' => 'high',
                        'fields' => array(
                            new Field(array(
                                'label' => 'Ticket Link',
                                'input' => new TextInput(array('name' => 'event_ticket_link'))
                            )),
                            new Field(array(
                                'label' => 'Ticket Blurb',
                                'input' => new RichTextarea(array('name' => 'event_ticket_text'))
                            )),
                        )
                    )),
                    new MetaBox(array(
                        'id' => 'event-details',
                        'title' => 'Event Details',
                        'priority' => 'high',
                        'fields' => array(
                            new Field(array(
                                'label' => 'Event Copy',
                                'input' => new RichTextarea(array('name' => 'event_details')),
                                // 'saveMetaCb' => array(__CLASS__, 'saveExcerpt'),
                            )),
                            new Field(array(
                                'label' => 'Facebook Event Link',
                                'input' => new TextInput(array('name' => 'event_fbevent_link'))
                            )),
                        )
                    )),
                ),
                'listTableColumns' => array(
                    new ListTableColumn(array(
                        'key' => 'event-date',
                        'title' => 'Date',
                        'position' => 2,
                        'outputCb' => array(__CLASS__, 'outputEventDateColumn'),
                        'sortCb' => function($queryVars) {
                            $queryVars['meta_key'] = 'event_start_timestamp';
                            $queryVars['orderby'] = 'meta_key';
                            return $queryVars;
                        }
                    )),
//                    new ListTableColumn(array(
//                        'key' => 'event-location',
//                        'title' => 'Location',
//                        'position' => 3,
//                        'outputCb' => array(__CLASS__, 'outputEventLocationColumn')
//                    )),
                )
            ));

        }



        public static function getStartTime($field, $input) {
            $startTime = isset($input['event_start_time']) ? $input['event_start_time'] : '';
            $field->getInput()->setPlaceholder($startTime);
        }



        public static function saveEventDateMetaBox($post, $input, $args, $fields) {
            $startDate = isset($input['event_start_date']) ? $input['event_start_date'] : '';
            $endDate = isset($input['event_end_date']) ? $input['event_end_date'] : '';
            $startTime = isset($input['event_start_time']) ? $input['event_start_time'] : '';
            $endTime = isset($input['event_end_time']) ? $input['event_end_time'] : '';

            $startTimestamp = strtotime($startDate) ? strtotime($startDate.' '.$startTime) : '';
            $endTimestamp = strtotime($endDate) ? strtotime($endDate.' '.$endTime) : '';


            if(empty($startTimestamp) && !empty($endTimestamp)) {
                $startTimestamp = $endTimestamp - 3600;
                $input['event_start_date'] = date('Y-m-d', $startTimestamp);
                $input['event_start_time'] = date('H:i:s', $startTimestamp);
            }
            if(!empty($startTimestamp) && empty($endTimestamp)) {
                $endTimestamp = $startTimestamp + (3600);
                $input['event_end_date'] = date('Y-m-d', $endTimestamp);
                $input['event_end_time'] = date('H:i:s', $endTimestamp);
            }

            if(!empty($startTimestamp)) $input['event_start_timestamp'] = date('Y-m-d H:i:s', $startTimestamp);
            if(!empty($endTimestamp)) $input['event_end_timestamp'] = date('Y-m-d H:i:s', $endTimestamp);

            foreach($fields as $field) {
                $field->saveValue($input, 'post', $post->ID);
            }
        }

        public static function saveExcerpt($post, $input, $fields) {
            if (!isset($post['post_excerpt'])) $post['post_excerpt'] = $input;

            $post->saveValue($post['post_excerpt'], 'post', $post->ID);

        }


        public static function outputEventDateColumn($postId, $args) {
            $output = array();
            $allDayEvent = (bool)get_post_meta($postId, 'event_all_day', true);
            foreach(array('start', 'end') as $key) {
                $date = '';
                if($allDayEvent) {
                    $date = strtotime(get_post_meta($postId, 'event_'.$key.'_date', true));
                    $date = !empty($date) ? date('D, M j, Y', $date) : '';
                } else {
                    $date = strtotime(get_post_meta($postId, 'event_'.$key.'_timestamp', true));
                    $date = !empty($date) ? date('D, M j, Y - g:ia', $date) : '';
                }
                if(empty($date)) continue;
                $output[] = '<strong>'.($key == 'start' ? 'From' : 'To').':</strong> '.$date;
            }
            echo implode('<br>', $output);
        }


        public static function outputEventLocationColumn($postId, $args) {
            $output = array();
            $venue = get_post_meta($postId, 'event_venue', true);
            $address = get_post_meta($postId, 'event_address', true);
            if(!empty($venue)) $output[] = '<strong>'.$venue.'</strong>';
            if(!empty($address)) $output[] = nl2br($address);
            echo implode('<br>', $output);
        }


        public static function registerTaxonomies() {

            self::$eventTypeTaxonomy = new Taxonomy(array(
                'name' => 'event_type',
                'singularLabel' => 'Type',
                'pluralLabel' => 'Types',
                'postTypes' => array('event', 'result'),
                'settings' => array(
                    'hierarchical' => true,
                    'rewrite' => array('slug' => 'types', 'with_front' => false),
                    'show_in_nav_menus' => false,
//                    'capabilities' => array(
//                        'manage_terms' => 'manage_event_types',
//                        'edit_terms' => 'edit_event_types',
//                        'delete_terms' => 'delete_event_types',
//                        'assign_terms' => 'assign_event_types'
//                    )
                ),
                'listTableColumns' => array(
                    new ListTableColumn(array(
                        'key' => 'event-type',
                        'title' => '',
                        'position' => 1,
                        'outputCb' => function($termId, $args) {
                            $type = get_term_meta($termId, 'event_type', true);
                            if(!empty($type)) echo '<span class="event-type">'.$type.'</span>';
                        }
                    ))
                )
            ));

        }


        public static function registerShortcodes() {

            self::$eventListShortcode = new Shortcode(array(
                'tag' => 'event_list',
                'getOutputCb' => array(__CLASS__, 'getEventListShortcode'),
                'defaultAtts' => array(
                    'type' => '',
                    'series' => ''
                )
            ));

        }




        public static function registerAdminScripts($hook) {

            $screen = get_current_screen();
            if($screen->base == 'post' && in_array($screen->post_type, array('event'))) {

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
            if($screen->base == 'post' && $screen->post_type == 'event') {

                $css = "
					.ce-field-dependent {
						display: none !important;
					}
					.ce-field-dependent.active {
						display: block !important;
					}
					.champ-field-dependent {
						display: none !important;
					}
					.champ-field-dependent.active {
						display: block !important;
					}
				";
                wp_add_inline_style('common', $css);

            } else if($screen->base == 'edit' && $screen->post_type == 'event') {

                $css = "
					table.wp-list-table	th.column-event-image,
					table.wp-list-table	td.column-event-image {
						width: 42px;
					}
					table.wp-list-table	td.column-event-image .image {
						padding-top: 100%;
						background-position: center center;
						background-repeat: no-repeat;
						background-size: cover;
					}
				";
                wp_add_inline_style('common', $css);

            } else if($screen->base == 'edit-tags' && $screen->taxonomy == 'event_calendar') {

                $css = "
					table.wp-list-table	th.column-event-calendar-color,
					table.wp-list-table	td.column-event-calendar-color {
						width: 21px;
					}
					table.wp-list-table	td.column-event-calendar-color .color-swatch {
						display: block;
						width: 21px;
						height: 21px;
						border-radius: 3px;
					}
				";
                wp_add_inline_style('common', $css);

            }

        }


        public static function registerScripts() {
            wp_register_script('moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js', array(), '2.13.0', true);
            //wp_register_script('equinox', plugins_url('assets/library/equinox/dist/equinox.min.js', __FILE__), array('jquery', 'moment'), '1.0.0', true);
        }


        public static function registerStyles() {
            //wp_register_style('equinox', plugins_url('assets/library/equinox/dist/equinox.css', __FILE__), array(), '1.0.0');
        }

        /**
         * Reassign Event Post Type rewrite rule to include selected parent page.
         *
         * @param $args
         * @param $post_type
         * @return string path for rewrite rule.
         */
        public static function event_rewrite_slug($args, $post_type) {

            $indexPage = get_post(get_option('theme_options_event_index_page'));
            $parentID = $indexPage->post_parent;

            if ( 'event' !== $post_type || $parentID == 0 )
                return $args;

            $parentSlug = get_post($parentID)->post_name;

            $args['rewrite']['slug'] = $parentSlug . '/events';
            var_dump($args['rewrite']['slug']);

            return $args;
        }


    }
}

if(class_exists('CrownEvents')) {
    CrownEvents::init();
}