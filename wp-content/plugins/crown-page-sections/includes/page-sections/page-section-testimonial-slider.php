<?php

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldRepeater;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Textarea;
use Crown\Form\Input\Select;
use Crown\UIRule;

include_once(dirname(__FILE__).'/page-section.php');


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSectionTestimonialSlider')) {
	class CrownPageSectionTestimonialSlider extends CrownPageSection {


		protected static $name = 'Testimonial Slider Section';


		public static function getConfig() {
			// disable page section if required plugin isn't active
			// if(!class_exists('CrownTestimonials')) return array();
			return parent::getConfig();
		}


		protected static function getContentFields() {
			return array(
				static::getSectionIntroContentField(),
				static::getSectionTestimonialsField()
			);
		}


		protected static function getSectionTestimonialsField() {

			$fieldGroup = new FieldGroup(array(
				'label' => 'Section Testimonials',
			));

			$staticTestimonialsField = new FieldRepeater(array(
				'name' => 'static_testimonials',
				'addNewLabel' => 'Add New Testimonial',
				'fields' => array(
					new Field(array(
						'input' => new Textarea(array('name' => 'content', 'rows' => 6))
					)),
					new FieldGroup(array(
						'label' => 'Source',
						'fields' => array(
							new Field(array(
								'input' => new TextInput(array('name' => 'source_name', 'label' => 'Name'))
							)),
							new Field(array(
								'input' => new TextInput(array('name' => 'source_title', 'label' => 'Job Title'))
							)),
							new FieldGroup(array(
								'class' => 'no-border two-column large-left',
								'fields' => array(
									new Field(array(
										'input' => new TextInput(array('name' => 'source_org', 'label' => 'Organization'))
									)),
									new Field(array(
										'input' => new TextInput(array('name' => 'source_org_url', 'label' => 'Organization URL', 'placeholder' => 'https://'))
									))
								)
							))
						)
					))
				)
			));

			if(class_exists('CrownTestimonials')) {

				$staticTestimonialsField->setUIRules(array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'testimonial_source'), 'value' => 'static'))));

				$fieldGroup->setFields(array(
					static::getSectionConfigurationField(array('label' => 'Testimonial Source', 'inputName' => 'testimonial_source', 'options' => array(
						array('value' => 'specific', 'label' => 'Specific Testimonials (custom order)'),
						array('value' => 'recent', 'label' => 'Most Recent'),
						array('value' => 'category', 'label' => 'Category (ordered by most recent)'),
						array('value' => 'static', 'label' => 'Static (custom-defined)')
					))),
					static::getPostSelectField(array(
						'label' => 'Testimonials to Display',
						'description' => 'Testimonials can be reordered by dragging selected items.',
						'inputName' => 'testimonials',
						'placeholder' => 'Select Testimonials...',
						'postType' => array('testimonial'),
						'multiple' => true,
						'sortable' => true,
						'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'testimonial_source'), 'value' => 'specific'))),
					)),
					static::getTermSelectField(array(
						'label' => 'Testimonial Categories to Display',
						'inputName' => 'testimonial_categories',
						'placeholder' => 'Select Categories...',
						'taxonomy' => 'testimonial_category',
						'multiple' => true,
						'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'testimonial_source'), 'value' => 'category')))
					)),
					new Field(array(
						'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'testimonial_source'), 'value' => array('recent', 'category')))),
						'label' => 'Maximum Number of Testimonials to Display',
						'input' => new Select(array('name' => 'max_entries_to_display', 'defaultValue' => 3, 'class' => 'input-xsmall', 'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)))
					)),
					$staticTestimonialsField
				));

			} else {

				$fieldGroup->setFields(array($staticTestimonialsField));

			}

			return $fieldGroup;
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

			$postSource = array_key_exists('testimonial_source', $input) ? $input['testimonial_source'] : '';

			if(in_array($postSource, array('specific', 'recent', 'category'))) {

				$queryArgs = array(
					'post_type' => 'testimonial',
					'posts_per_page' => array_key_exists('max_entries_to_display', $input) ? intval($input['max_entries_to_display']) : 3
				);

				if($postSource == 'specific') {
					$queryArgs['post__in'] = array_key_exists('testimonials', $input) ? $input['testimonials'] : array();
					$queryArgs['posts_per_page'] = -1;
					$queryArgs['orderby'] = 'post__in';
					$queryArgs['order'] = 'ASC';
				}

				if($postSource == 'category') {
					$queryArgs['tax_query'] = array(
						array(
							'taxonomy' => 'testimonial_category',
							'terms' => array_key_exists('testimonial_categories', $input) ? $input['testimonial_categories'] : array()
						)
					);
				}

				$posts = get_posts($queryArgs);

				foreach($posts as $post) {
					$blockquoteContent = array($post->post_content);
					$blockquoteSource = array();
					foreach(array('name', 'title', 'org') as $key) {
						$value = get_post_meta($post->ID, 'testimonial_source_'.$key, true);
						if(!empty($value)) $blockquoteSource[] = $value;
					}
					if(!empty($blockquoteSource)) $blockquoteContent[] = '<footer class="source">'.implode(', ', $blockquoteSource).'</footer>';
					$content[] = "<blockquote>\n".implode("\n", $blockquoteContent)."\n</blockquote>";
				}

			} else {

				if(array_key_exists('static_testimonials', $input)) {
					foreach($input['static_testimonials'] as $entry) {

						$blockquoteContent = array_key_exists('content', $entry) ? array($entry['content']) : array();
						$blockquoteSource = array();
						foreach(array('name', 'title', 'org') as $key) {
							$value = array_key_exists('source_'.$key, $entry) ? $entry['source_'.$key] : '';
							if(!empty($value)) $blockquoteSource[] = $value;
						}
						if(!empty($blockquoteSource)) $blockquoteContent[] = '<footer class="source">'.implode(', ', $blockquoteSource).'</footer>';
						$content[] = "<blockquote>\n".implode("\n", $blockquoteContent)."\n</blockquote>";

					}
				}

			}

			return implode(" \n\n", $content);
		}


	}
}