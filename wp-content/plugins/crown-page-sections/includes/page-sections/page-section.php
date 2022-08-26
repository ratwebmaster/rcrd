<?php

use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\FieldRepeater;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Checkbox as CheckboxInput;
use Crown\Form\Input\Color as ColorInput;
use Crown\Form\Input\Media as MediaInput;
use Crown\Form\Input\Gallery as GalleryInput;
use Crown\Form\Input\Select;
use Crown\Form\Input\RichTextarea;
use Crown\Form\Input\CheckboxSet;
use Crown\Form\Input\RadioImageSet;
use Crown\UIRule;


if(defined('CROWN_FRAMEWORK_VERSION') && !class_exists('CrownPageSection')) {
	class CrownPageSection {


		protected static $name = '';
		protected static $slug = '';

		protected static $optionsCache = array();


		public static function getName() {
			return static::$name;
		}


		public static function getSlug() {
			return !empty(static::$slug) ? static::$slug : sanitize_title(static::$name);
		}


		public static function getConfig() {
			if(empty(static::$name)) return array();
			$config = array(
				'name' => static::$name,
				'fields' => array(
					new FieldGroupSet(array(
						// 'class' => 'nav-top',
						'fieldGroups' => static::getFieldGroups()
					))
				)
			);
			return $config;
		}


		protected static function getFieldGroups() {
			return array(
				new FieldGroup(array(
					'label' => '<span class="dashicons dashicons-welcome-write-blog" style="margin: -1px .2em -1px 0;"></span> Content',
					'fields' => static::getContentFields()
				)),
				new FieldGroup(array(
					'label' => '<span class="dashicons dashicons-align-left" style="margin: -1px .2em -1px 0;"></span> Layout',
					'fields' => static::getLayoutFields()
				)),
				new FieldGroup(array(
					'label' => '<span class="dashicons dashicons-admin-appearance" style="margin: -1px .2em -1px 0;"></span> Style',
					'fields' => static::getStyleFields()
				))
			);
		}


		protected static function getContentFields() {
			return array();
		}


		protected static function getLayoutFields() {
			return array();
		}


		protected static function getStyleFields() {
			return array();
		}


		public static function getFallbackContent($input) {
			return '';
		}



		// Page Section Fields
		// --------------------------------------------------------------------


		protected static function getSectionTitleField() {
			return new FieldGroup(array(
				'class' => 'no-border two-column large-left',
				'fields' => array(
					static::getTitleField(array('label' => 'Section Title')),
					static::getHeadingLevelField(array('inputName' => 'title_h_level'))
				)
			));
		}


		protected static function getSectionIntroContentField() {
			return new FieldGroup(array(
				'label' => 'Intro Content',
				'fields' => array(
					static::getSectionTitleField(),
					static::getContentField(array('rows' => 3)),
				)
			));
		}


		protected static function getSectionColumn1Fields() {
			return new FieldGroup(array(
				'label' => 'Column One',
				'fields' => array(
					new FieldGroup(array(
						'class' => 'no-border two-column large-left',
						'fields' => array(
							static::getTitleField(array('label' => 'Column Title', 'inputName' => 'title_column_1')),
							static::getHeadingLevelField(array('inputName' => 'title_h_level_column_1'))
						)
					)),
					static::getContentField(array('inputName' => 'content_column_1', 'rows' => 10)),
			        static::getColorField(array('label' => 'Background Color', 'inputName' => 'bg_color_column_1', 'themeColorsContext' => 'section_bg', 'defaultValue' => ''))
				)
			));
		}


		protected static function getSectionColumn2Fields() {
			return new FieldGroup(array(
				'label' => 'Column Two',
				'fields' => array(
					new FieldGroup(array(
						'class' => 'no-border two-column large-left',
						'fields' => array(
							static::getTitleField(array('label' => 'Column Title', 'inputName' => 'title_column_2')),
							static::getHeadingLevelField(array('inputName' => 'title_h_level_column_2'))
						)
					)),
					static::getContentField(array('inputName' => 'content_column_2', 'rows' => 10)),
                    static::getColorField(array('label' => 'Background Color', 'inputName' => 'bg_color_column_2', 'themeColorsContext' => 'section_bg', 'defaultValue' => ''))
				)
			));
		}


		protected static function getSectionCellsField() {
			return new FieldGroup(array(
				'label' => 'Grid Cells',
				'fields' => array(
					new FieldRepeater(array(
						'name' => 'cells',
						'addNewLabel' => 'Add New Cell',
						'fields' => static::getSectionCellFields()
					))
				)
			));
		}


		protected static function getSectionCellFields() {
			return array(
				new FieldGroup(array(
					'class' => 'no-border two-column large-left',
					'atts' => array('style' => 'margin-top: 0;'),
					'fields' => array(
						static::getTitleField(array('label' => 'Cell Title')),
						new FieldGroup(array(
							'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'configuration'), 'value' => 'thumbnails', 'compare' => '!='))),
							'class' => 'no-border',
							'fields' => array(
								static::getIconField(array('themeIconsContext' => 'section_cell_icon'))
							)
						))
					)
				)),
				new FieldGroup(array(
					'uIRules' => array(new UIRule(array('property' => 'input', 'options' => array('inputName' => 'configuration'), 'value' => 'thumbnails'))),
					'class' => 'no-border',
					'fields' => array(
						static::getImageField(array('label' => 'Thumbnail Image', 'buttonLabel' => 'Select Thumbnail'))
					)
				)),
				static::getContentField(array('rows' => 6)),
				new FieldGroup(array(
					'label' => 'CTA Link',
					'fields' => array(
						static::getSectionLinkFields(array('inputNamePrefix' => 'link_', 'labelPlaceholder' => 'Learn More'))
					)
				))
			);
		}


		protected static function getSectionLinkListGroupsField($args = array()) {
			$defaultArgs = array(
				'inputName' => 'link_list_groups'
			);
			$args = array_merge($defaultArgs, $args);
			return new FieldRepeater(array(
				'name' => $args['inputName'],
				'addNewLabel' => 'Add Group',
				'fields' => array(
					new FieldGroup(array(
						'class' => 'no-border two-column large-left',
						'fields' => array(
							static::getTitleField(),
							static::getIconField()
						)
					)),
					static::getSectionLinksField()
				)
			));
		}


		protected static function getSectionLinksField($args = array()) {
			$defaultArgs = array(
				'inputName' => 'links',
				'labelPlaceholder' => ''
			);
			$args = array_merge($defaultArgs, $args);
			return new FieldRepeater(array(
				'name' => $args['inputName'],
				'addNewLabel' => 'Add Link',
				'fields' => array(
					static::getSectionLinkFields(array('labelPlaceholder' => $args['labelPlaceholder']))
				)
			));
		}


		protected static function getSectionGridLinksField($args = array()) {
			$defaultArgs = array(
				'inputName' => 'grid_links',
				'labelPlaceholder' => 'Learn More'
			);
			$args = array_merge($defaultArgs, $args);
			return new FieldRepeater(array(
				'name' => $args['inputName'],
				'addNewLabel' => 'Add Link',
				'fields' => array(
					new FieldGroup(array(
						'class' => 'no-border two-column large-left',
						'fields' => array(
							static::getTitleField(),
							static::getIconField()
						)
					)),
					static::getSectionLinkFields(array('labelPlaceholder' => $args['labelPlaceholder']))
				)
			));
		}


		protected static function getSectionLinkFields($args = array()) {
			$defaultArgs = array(
				'inputNamePrefix' => '',
				'urlInputName' => 'url',
				'labelInputName' => 'label',
				'labelPlaceholder' => ''
			);
			$args = array_merge($defaultArgs, $args);
			$urlInputName = $args['inputNamePrefix'].$args['urlInputName'];
			$labelInputName = $args['inputNamePrefix'].$args['labelInputName'];
			return new FieldGroup(array(
				'class' => 'no-border two-column large-left',
				'fields' => array(
					new Field(array(
						'input' => new TextInput(array('name' => $urlInputName, 'label' => 'Link URL', 'placeholder' => 'https://'))
					)),
					new Field(array(
						'input' => new TextInput(array('name' => $labelInputName, 'label' => 'Link Label', 'placeholder' => $args['labelPlaceholder']))
					))
				)
			));
		}


		protected static function getSectionIntroContentLayoutField($args = array()) {
			$defaultArgs = array(
				'label' => 'Intro Content Layout',
				'description' => '',
				'inputName' => 'intro_content_layout',
				'defaultValue' => 'centered',
				'options' => array(
					array('value' => 'centered', 'label' => 'Centered', 'image' => plugins_url('../../assets/images/icons/layout-003.png', __FILE__)),
					array('value' => 'left-aligned', 'label' => 'Left-Aligned', 'image' => plugins_url('../../assets/images/icons/layout-004.png', __FILE__))
				),
				'ignoreOptions' => array()
			);
			$args = array_merge($defaultArgs, $args);

			$options = array();
			foreach($args['options'] as $option) {
				if(!in_array($option['value'], $args['ignoreOptions'])) {
					$options[] = $option;
				}
			}

			return new FieldGroup(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'fields' => array(
					new Field(array(
						'input' => new RadioImageSet(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'options' => $options))
					))
				)
			));
		}


		protected static function getSectionWidthConstraintField($args = array()) {
			$defaultArgs = array(
				'label' => 'Width Constraint',
				'description' => '',
				'inputName' => 'width_constraint',
				'defaultValue' => 'full',
				'options' => array(
					array('value' => 'full', 'label' => 'Full', 'image' => plugins_url('../../assets/images/icons/width-full.png', __FILE__)),
					array('value' => 'lg', 'label' => 'Large', 'image' => plugins_url('../../assets/images/icons/width-lg.png', __FILE__)),
					array('value' => 'md', 'label' => 'Medium', 'image' => plugins_url('../../assets/images/icons/width-md.png', __FILE__)),
					array('value' => 'sm', 'label' => 'Small', 'image' => plugins_url('../../assets/images/icons/width-sm.png', __FILE__)),
					array('value' => 'xs', 'label' => 'X-Small', 'image' => plugins_url('../../assets/images/icons/width-xs.png', __FILE__))
				),
				'ignoreOptions' => array()
			);
			$args = array_merge($defaultArgs, $args);

			$options = array();
			foreach($args['options'] as $option) {
				if(!in_array($option['value'], $args['ignoreOptions'])) {
					$options[] = $option;
				}
			}

			return new FieldGroup(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'fields' => array(
					new Field(array(
						'input' => new RadioImageSet(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'options' => $options))
					))
				)
			));
		}


		protected static function getSectionTwoColumnLayoutField($args = array()) {
			$defaultArgs = array(
				'label' => 'Column Layout',
				'description' => '',
				'inputName' => 'layout',
				'defaultValue' => '1-1',
				'reverseColumnOrderOptionField' => true,
				'options' => array(
					array('value' => '1-1', 'label' => '1:1', 'image' => plugins_url('../../assets/images/icons/columns-1-1.png', __FILE__)),
					array('value' => '1-6', 'label' => '1:6', 'image' => plugins_url('../../assets/images/icons/columns-1-6.png', __FILE__)),
					array('value' => '1-3', 'label' => '1:3', 'image' => plugins_url('../../assets/images/icons/columns-1-3.png', __FILE__)),
					array('value' => '1-2', 'label' => '1:2', 'image' => plugins_url('../../assets/images/icons/columns-1-2.png', __FILE__)),
					array('value' => '5-7', 'label' => '5:7', 'image' => plugins_url('../../assets/images/icons/columns-5-7.png', __FILE__)),
					array('value' => '7-5', 'label' => '7:5', 'image' => plugins_url('../../assets/images/icons/columns-7-5.png', __FILE__)),
					array('value' => '2-1', 'label' => '2:1', 'image' => plugins_url('../../assets/images/icons/columns-2-1.png', __FILE__)),
					array('value' => '3-1', 'label' => '3:1', 'image' => plugins_url('../../assets/images/icons/columns-3-1.png', __FILE__)),
					array('value' => '6-1', 'label' => '6:1', 'image' => plugins_url('../../assets/images/icons/columns-6-1.png', __FILE__)),
					array('value' => '2-_1-3', 'label' => '2:3 gapped', 'image' => plugins_url('../../assets/images/icons/columns-2-_1-3.png', __FILE__)),
					array('value' => '3-_1-2', 'label' => '3:2 gapped', 'image' => plugins_url('../../assets/images/icons/columns-3-_1-2.png', __FILE__)),
					array('value' => '5-_1-6', 'label' => '5:6 gapped', 'image' => plugins_url('../../assets/images/icons/columns-5-_1-6.png', __FILE__)),
					array('value' => '6-_1-5', 'label' => '6:5 gapped', 'image' => plugins_url('../../assets/images/icons/columns-6-_1-5.png', __FILE__))
				),
				'ignoreOptions' => array()
			);
			$args = array_merge($defaultArgs, $args);

			$options = array();
			foreach($args['options'] as $option) {
				if(!in_array($option['value'], $args['ignoreOptions'])) {
					$options[] = $option;
				}
			}

			$fields = array(new Field(array(
				'input' => new RadioImageSet(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'options' => $options))
			)));

			if($args['reverseColumnOrderOptionField']) {
				$fields[] = new Field(array(
					'input' => new CheckboxInput(array('name' => 'reverse_column_order', 'label' => 'Reverse column order'))
				));
			}

			return new FieldGroup(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'fields' => $fields
			));
		}


		protected static function getSectionColumnCountField($args = array()) {
			$defaultArgs = array(
				'label' => 'Column Count',
				'description' => '',
				'inputName' => 'column_count',
				'defaultValue' => '3',
				'options' => array(
					array('value' => 1, 'label' => '1', 'image' => plugins_url('../../assets/images/icons/column-count-1.png', __FILE__)),
					array('value' => 2, 'label' => '2', 'image' => plugins_url('../../assets/images/icons/column-count-2.png', __FILE__)),
					array('value' => 3, 'label' => '3', 'image' => plugins_url('../../assets/images/icons/column-count-3.png', __FILE__)),
					array('value' => 4, 'label' => '4', 'image' => plugins_url('../../assets/images/icons/column-count-4.png', __FILE__)),
					array('value' => 5, 'label' => '5', 'image' => plugins_url('../../assets/images/icons/column-count-5.png', __FILE__)),
					array('value' => 6, 'label' => '6', 'image' => plugins_url('../../assets/images/icons/column-count-6.png', __FILE__)),
					array('value' => 7, 'label' => '7', 'image' => plugins_url('../../assets/images/icons/column-count-7.png', __FILE__)),
					array('value' => 8, 'label' => '8', 'image' => plugins_url('../../assets/images/icons/column-count-8.png', __FILE__)),
					array('value' => 9, 'label' => '9', 'image' => plugins_url('../../assets/images/icons/column-count-9.png', __FILE__)),
					array('value' => 10, 'label' => '10', 'image' => plugins_url('../../assets/images/icons/column-count-10.png', __FILE__)),
					array('value' => 11, 'label' => '11', 'image' => plugins_url('../../assets/images/icons/column-count-11.png', __FILE__)),
					array('value' => 12, 'label' => '12', 'image' => plugins_url('../../assets/images/icons/column-count-12.png', __FILE__))
				),
				'ignoreOptions' => array()
			);
			$args = array_merge($defaultArgs, $args);

			$options = array();
			foreach($args['options'] as $option) {
				if(!in_array($option['value'], $args['ignoreOptions'])) {
					$options[] = $option;
				}
			}

			return new FieldGroup(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'fields' => array(
					new Field(array(
						'input' => new RadioImageSet(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'options' => $options))
					))
				)
			));
		}


		protected static function getSectionLayoutOptionsField($args = array()) {
			$defaultArgs = array(
				'label' => 'Layout Options',
				'description' => '',
				'inputName' => 'layout_options',
				'defaultValue' => array(),
				'options' => array(
					array('value' => 'text-center', 'label' => 'Center-Align Text'),
					array('value' => 'full-window-height', 'label' => 'Full Window Height')
//					array('value' => 'reduced-padding-top', 'label' => 'Reduced Top Padding'),
//					array('value' => 'reduced-padding-bottom', 'label' => 'Reduced Bottom Padding')
				),
				'ignoreOptions' => array()
			);
			$args = array_merge($defaultArgs, $args);

			$options = array();
			foreach($args['options'] as $option) {
				if(!in_array($option['value'], $args['ignoreOptions'])) {
					$options[] = $option;
				}
			}

			return new FieldGroup(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'fields' => array(
                    new Field(array(
                        'input' => new CheckboxSet(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'options' => $options))
                    )),
                    static::getPaddingSelectField(),
				)
			));
		}


        protected static function getPaddingTopField($args = array()) {
            $defaultArgs = array(
                'label' => 'Padding Top',
                'description' => '',
                'inputName' => 'padding_top',
                'defaultValue' => 'md',
                'options' => array(
                    array('value' => 'lg', 'label' => 'Large'),
                    array('value' => 'md', 'label' => 'Medium'),
                    array('value' => 'sm', 'label' => 'Small')
                ),
                'ignoreOptions' => array()
            );
            $args = array_merge($defaultArgs, $args);

            $options = array();
            foreach($args['options'] as $option) {
                if(!in_array($option['value'], $args['ignoreOptions'])) {
                    $options[] = $option;
                }
            }

            return new Field(array(
                'label' => $args['label'],
                'description' => $args['description'],
                'input' => new Select(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'options' => $options))
            ));
        }

        protected static function getPaddingBottomField($args = array()) {
            $defaultArgs = array(
                'label' => 'Padding Bottom',
                'description' => '',
                'inputName' => 'padding_bottom',
                'defaultValue' => 'md',
                'options' => array(
                    array('value' => 'lg', 'label' => 'Large'),
                    array('value' => 'md', 'label' => 'Medium'),
                    array('value' => 'sm', 'label' => 'Small')
                ),
                'ignoreOptions' => array()
            );
            $args = array_merge($defaultArgs, $args);

            $options = array();
            foreach($args['options'] as $option) {
                if(!in_array($option['value'], $args['ignoreOptions'])) {
                    $options[] = $option;
                }
            }

            return new Field(array(
                'label' => $args['label'],
                'description' => $args['description'],
                'input' => new Select(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'options' => $options))
            ));
        }

        protected static function getPaddingSelectField($args = array()) {
            $defaultArgs = array(
                'label' => 'Padding Options',
                'description' => '',
            );
            $args = array_merge($defaultArgs, $args);

            return new FieldGroup(array(
                'label' => $args['label'],
                'description' => $args['description'],
                'atts' => array('style' => 'margin-top: 20px; border-top: 1px solid #eee; padding-top: 4px;'),
                'class' => 'no-border two-column',
                'fields' => array(
                    static::getPaddingTopField(),
                    static::getPaddingBottomField(),
                )
            ));
        }


		protected static function getSectionBgColorField() {
			return static::getColorField(array('label' => 'Background Color', 'inputName' => 'bg_color', 'themeColorsContext' => 'section_bg'));
		}


		protected static function getSectionBgImageField() {
			return static::getImageField(array('label' => 'Background Image', 'inputName' => 'bg_image', 'buttonLabel' => 'Select Background Image'));
		}


		protected static function getSectionCustomIdAndClassFields() {
			return new FieldGroup(array(
				'class' => 'no-border two-column small-left',
				'atts' => array('style' => 'margin-top: 20px; border-top: 1px solid #eee; padding-top: 4px;'),
				'fields' => array(
					static::getCustomIdField(array('label' => 'Custom Section ID')),
					static::getCustomClassField(array('label' => 'Custom Section Class'))
				)
			));
		}


		protected static function getSectionConfigurationField($args = array()) {
			$defaultArgs = array(
				'label' => 'Configuration',
				'description' => '',
				'inputName' => 'configuration',
				'defaultValue' => '',
				'options' => array(),
				'ignoreOptions' => array()
			);
			$args = array_merge($defaultArgs, $args);

			$options = array();
			foreach($args['options'] as $option) {
				if(!in_array($option['value'], $args['ignoreOptions'])) {
					$options[] = $option;
				}
			}

			return new Field(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'input' => new Select(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'options' => $options, 'class' => 'input-medium'))
			));
		}



		// Core Fields
		// --------------------------------------------------------------------
		

		protected static function getTitleField($args = array()) {
			$defaultArgs = array(
				'label' => 'Title',
				'description' => '',
				'inputName' => 'title',
				'inputClass' => 'input-large'
			);
			$args = array_merge($defaultArgs, $args);
			return new Field(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'input' => new TextInput(array('name' => $args['inputName'], 'class' => $args['inputClass']))
			));
		}


		protected static function getContentField($args = array()) {
			$defaultArgs = array(
				'label' => '',
				'description' => '',
				'inputName' => 'content',
				'rows' => 14
			);
			$args = array_merge($defaultArgs, $args);
			return new Field(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'input' => new RichTextarea(array('name' => $args['inputName'], 'rows' => $args['rows']))
			));
		}


		protected static function getHeadingLevelField($args = array()) {
			$defaultArgs = array(
				'label' => 'Heading Level',
				'description' => '',
				'inputName' => 'h_level',
				'defaultValue' => 'h2',
				'options' => array(
					array('value' => 'h1', 'label' => 'Heading 1'),
					array('value' => 'h2', 'label' => 'Heading 2'),
					array('value' => 'h3', 'label' => 'Heading 3'),
					array('value' => 'h4', 'label' => 'Heading 4'),
					array('value' => 'h5', 'label' => 'Heading 5'),
					array('value' => 'h6', 'label' => 'Heading 6')
				),
				'ignoreOptions' => array()
			);
			$args = array_merge($defaultArgs, $args);

			$options = array();
			foreach($args['options'] as $option) {
				if(!in_array($option['value'], $args['ignoreOptions'])) {
					$options[] = $option;
				}
			}

			return new Field(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'input' => new Select(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'options' => $options))
			));
		}


		protected static function getColorField($args = array()) {
			$defaultArgs = array(
				'label' => 'Color',
				'description' => '',
				'inputName' => 'color',
				'defaultValue' => '#FFFFFF',
				'palettes' => array(),
				'themeColorsContext' => ''
			);
			$args = array_merge($defaultArgs, $args);
			if(empty($args['palettes'])) $args['palettes'] = apply_filters('crown_theme_colors', array(), $args['themeColorsContext']);
			return new Field(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'input' => new ColorInput(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'colorpickerOptions' => array('palettes' => $args['palettes'])))
			));
		}


		protected static function getImageField($args = array()) {
			$defaultArgs = array(
				'label' => 'Image',
				'description' => '',
				'inputName' => 'image',
				'buttonLabel' => 'Select Image'
			);
			$args = array_merge($defaultArgs, $args);
			return new Field(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'input' => new MediaInput(array('name' => $args['inputName'], 'buttonLabel' => $args['buttonLabel'], 'mimeType' => 'image'))
			));
		}


		protected static function getImageGalleryField($args = array()) {
			$defaultArgs = array(
				'label' => 'Image Gallery',
				'description' => '',
				'inputName' => 'images',
				'buttonLabel' => 'Add Images',
				'buttonEditLabel' => 'Edit Gallery'
			);
			$args = array_merge($defaultArgs, $args);
			return new FieldGroup(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'fields' => array(
					new Field(array(
						'input' => new GalleryInput(array('name' => $args['inputName'], 'buttonLabel' => $args['buttonLabel'], 'buttonEditLabel' => $args['buttonEditLabel']))
					))
				)
			));
		}


		protected static function getCustomIdField($args = array()) {
			$defaultArgs = array(
				'label' => 'Custom ID',
				'inputName' => 'custom_id'
			);
			$args = array_merge($defaultArgs, $args);
			return new Field(array(
				'input' => new TextInput(array('name' => $args['inputName'], 'label' => $args['label']))
			));
		}


		protected static function getCustomClassField($args = array()) {
			$defaultArgs = array(
				'label' => 'Custom Class',
				'inputName' => 'custom_class'
			);
			$args = array_merge($defaultArgs, $args);
			return new Field(array(
				'input' => new TextInput(array('name' => $args['inputName'], 'label' => $args['label']))
			));
		}


		protected static function getVerticalAlignmentField($args = array()) {
			$defaultArgs = array(
				'label' => 'Vertical Alignment',
				'description' => '',
				'inputName' => 'vertical_alignment',
				'defaultValue' => 'top',
				'options' => array(
					array('value' => 'top', 'label' => 'Top', 'image' => plugins_url('../../assets/images/icons/vertical-alignment-top.png', __FILE__)),
					array('value' => 'middle', 'label' => 'Middle', 'image' => plugins_url('../../assets/images/icons/vertical-alignment-middle.png', __FILE__)),
					array('value' => 'bottom', 'label' => 'Bottom', 'image' => plugins_url('../../assets/images/icons/vertical-alignment-bottom.png', __FILE__))
				),
				'ignoreOptions' => array()
			);
			$args = array_merge($defaultArgs, $args);

			$options = array();
			foreach($args['options'] as $option) {
				if(!in_array($option['value'], $args['ignoreOptions'])) {
					$options[] = $option;
				}
			}

			return new FieldGroup(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'fields' => array(
					new Field(array(
						'input' => new RadioImageSet(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'options' => $options))
					))
				)
			));
		}


		protected static function getHorizontalAlignmentField($args = array()) {
			$defaultArgs = array(
				'label' => 'Horizontal Alignment',
				'description' => '',
				'inputName' => 'horizontal_alignment',
				'defaultValue' => 'left',
				'options' => array(
					array('value' => 'left', 'label' => 'Left', 'image' => plugins_url('../../assets/images/icons/horizontal-alignment-left.png', __FILE__)),
					array('value' => 'center', 'label' => 'Center', 'image' => plugins_url('../../assets/images/icons/horizontal-alignment-center.png', __FILE__)),
					array('value' => 'right', 'label' => 'Right', 'image' => plugins_url('../../assets/images/icons/horizontal-alignment-right.png', __FILE__))
				),
				'ignoreOptions' => array()
			);
			$args = array_merge($defaultArgs, $args);

			$options = array();
			foreach($args['options'] as $option) {
				if(!in_array($option['value'], $args['ignoreOptions'])) {
					$options[] = $option;
				}
			}

			return new FieldGroup(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'fields' => array(
					new Field(array(
						'input' => new RadioImageSet(array('name' => $args['inputName'], 'defaultValue' => $args['defaultValue'], 'options' => $options))
					))
				)
			));
		}


		protected static function getIconField($args = array()) {
			$defaultArgs = array(
				'label' => 'Icon',
				'description' => '',
				'inputName' => 'icon',
				'themeIconsContext' => ''
			);
			$args = array_merge($defaultArgs, $args);
			return new Field(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'input' => new Select(array(
					'name' => $args['inputName'],
					'class' => 'input-small',
					'options' => apply_filters('crown_theme_icons', static::getIconSelectOptions(), $args['themeIconsContext']),
					'select2' => array(
						'templateResult' => 'function(option) { return option.text.match(/^fa-/) ? $(\'<span><span class="fa fa-fw \' + option.text + \'"></span> \' + option.text + \'</span>\') : option.text; }',
						'templateSelection' => 'function(option) { return option.text.match(/^fa-/) ? $(\'<span><span class="fa fa-fw \' + option.text + \'"></span> \' + option.text + \'</span>\') : option.text; }'
					)
				))
			));
		}


		protected static function getPostCheckboxSetField($args = array()) {
			$defaultArgs = array(
				'label' => '',
				'description' => '',
				'inputName' => 'posts',
				'postType' => '',
				'sortable' => false,
				'scrollable' => true,
				'lineWrap' => false,
				'uIRules' => array()
			);
			$args = array_merge($defaultArgs, $args);

			$classes = array();
			if($args['scrollable']) $classes[] = 'overflow-scroll';
			if(!$args['lineWrap']) $classes[] = 'no-line-wrap';

			return new Field(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'input' => new CheckboxSet(array('name' => $args['inputName'], 'sortable' => $args['sortable'], 'class' => implode(' ', $classes))),
				'getOutputCb' => array(__CLASS__, 'setPostCheckboxSetFieldOptions'),
				'atts' => array('data-post-type' => json_encode($args['postType'])),
				'uIRules' => $args['uIRules']
			));
		}


		protected static function getPostSelectField($args = array()) {
			$defaultArgs = array(
				'label' => '',
				'description' => '',
				'inputName' => 'posts',
				'placeholder' => '',
				'postType' => '',
				'multiple' => false,
				'sortable' => false,
				'uIRules' => array()
			);
			$args = array_merge($defaultArgs, $args);
			return new Field(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'input' => new Select(array('name' => $args['inputName'], 'multiple' => $args['multiple'], 'select2' => array('placeholder' => $args['placeholder'], 'allowClear' => true, 'sortable' => $args['sortable']))),
				'getOutputCb' => array(__CLASS__, 'setPostSelectFieldOptions'),
				'atts' => array('data-post-type' => json_encode($args['postType'])),
				'uIRules' => $args['uIRules']
			));
		}


		protected static function getTermCheckboxSetField($args = array()) {
			$defaultArgs = array(
				'label' => '',
				'description' => '',
				'inputName' => 'terms',
				'taxonomy' => '',
				'sortable' => false,
				'scrollable' => true,
				'lineWrap' => false,
				'uIRules' => array()
			);
			$args = array_merge($defaultArgs, $args);

			$classes = array();
			if($args['scrollable']) $classes[] = 'overflow-scroll';
			if(!$args['lineWrap']) $classes[] = 'no-line-wrap';

			return new Field(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'input' => new CheckboxSet(array('name' => $args['inputName'], 'sortable' => $args['sortable'], 'class' => implode(' ', $classes))),
				'getOutputCb' => array(__CLASS__, 'setTermCheckboxSetFieldOptions'),
				'atts' => array('data-taxonomy' => json_encode($args['taxonomy'])),
				'uIRules' => $args['uIRules']
			));
		}


		protected static function getTermSelectField($args = array()) {
			$defaultArgs = array(
				'label' => '',
				'description' => '',
				'inputName' => 'terms',
				'placeholder' => '',
				'taxonomy' => '',
				'multiple' => false,
				'sortable' => false,
				'uIRules' => array()
			);
			$args = array_merge($defaultArgs, $args);
			return new Field(array(
				'label' => $args['label'],
				'description' => $args['description'],
				'input' => new Select(array('name' => $args['inputName'], 'multiple' => $args['multiple'], 'select2' => array('placeholder' => $args['placeholder'], 'allowClear' => true, 'sortable' => $args['sortable']))),
				'getOutputCb' => array(__CLASS__, 'setTermSelectFieldOptions'),
				'atts' => array('data-taxonomy' => json_encode($args['taxonomy'])),
				'uIRules' => $args['uIRules']
			));
		}



		// Helper Functions
		// --------------------------------------------------------------------


		protected static function getIconSelectOptions() {
			if(array_key_exists('icon_select', static::$optionsCache)) return static::$optionsCache['icon_select'];
			$options = array();
			$cacheKey = 'icons';
			if(array_key_exists($cacheKey, static::$optionsCache)) {
				$options = static::$optionsCache[$cacheKey];
			} else {
				$filename = dirname(__FILE__).'/../../assets/data/fa-icons.json';
				if(file_exists($filename)) {
					$icons = json_decode(file_get_contents($filename));
					$options = array_merge($options, array_map(function($n) { return array('value' => 'fa '.$n->name, 'label' => $n->name); }, $icons));
				}
				static::$optionsCache[$cacheKey] = $options;
			}
			$options = array_merge(array(array('value' => '', 'label' => '&mdash;')), $options);
			return $options;
		}


		public static function setPostCheckboxSetFieldOptions($field, $args) {
			$fieldAtts = $field->getAtts();
			$postType = array_key_exists('data-post-type', $fieldAtts) ? json_decode($fieldAtts['data-post-type']) : '';
			if(!empty($postType)) {
				$options = static::getPostOptions($postType, is_post_type_hierarchical($postType) ? 0 : false);
				$field->getInput()->setOptions($options);
			}
		}

		public static function setPostSelectFieldOptions($field, $args) {
			$options = array();
			if(!$field->getInput()->getMultiple()) $options[] = array('value' => '', 'label' => '&mdash;');
			$fieldAtts = $field->getAtts();
			$postType = array_key_exists('data-post-type', $fieldAtts) ? json_decode($fieldAtts['data-post-type']) : '';
			if(!empty($postType)) {
				$options = array_merge($options, static::getPostOptions($postType, is_post_type_hierarchical($postType) ? 0 : false));
			}
			$field->getInput()->setOptions($options);
		}

		protected static function getPostOptions($postType, $parent = 0, $depth = 0) {
			$cacheKey = 'post_type_'.implode('_', (array)$postType).($parent === false ? '' : '_'.$parent);
			if(array_key_exists($cacheKey, static::$optionsCache)) return static::$optionsCache[$cacheKey];
			$options = array();
			$posts = get_posts(array('post_type' => $postType, 'posts_per_page' => -1, 'post_parent' => $parent === false ? '' : $parent, 'orderby' => 'title', 'order' => 'ASC', 'lang' => ''));
			foreach($posts as $post) {
				$options[] = array('value' => $post->ID, 'label' => $post->post_title, 'depth' => $depth);
				if($parent !== false) {
					$options = array_merge($options, static::getPostOptions($postType, $post->ID, $depth + 1));
				}
			}
			static::$optionsCache[$cacheKey] = $options;
			return $options;
		}


		public static function setTermCheckboxSetFieldOptions($field, $args) {
			$fieldAtts = $field->getAtts();
			$taxonomy = array_key_exists('data-taxonomy', $fieldAtts) ? json_decode($fieldAtts['data-taxonomy']) : '';
			if(!empty($taxonomy)) {
				$options = static::getTermOptions($taxonomy, is_taxonomy_hierarchical($taxonomy) ? 0 : false);
				$field->getInput()->setOptions($options);
			}
		}

		public static function setTermSelectFieldOptions($field, $args) {
			$options = array();
			if(!$field->getInput()->getMultiple()) $options[] = array('value' => '', 'label' => '&mdash;');
			$fieldAtts = $field->getAtts();
			$taxonomy = array_key_exists('data-taxonomy', $fieldAtts) ? json_decode($fieldAtts['data-taxonomy']) : '';
			if(!empty($taxonomy)) {
				$options = array_merge($options, static::getTermOptions($taxonomy, is_taxonomy_hierarchical($taxonomy) ? 0 : false));
			}
			$field->getInput()->setOptions($options);
		}

		protected static function getTermOptions($taxonomy, $parent = 0, $depth = 0) {
			$cacheKey = 'taxonomy_'.implode('_', (array)$taxonomy).($parent === false ? '' : '_'.$parent);
			if(array_key_exists($cacheKey, static::$optionsCache)) return static::$optionsCache[$cacheKey];
			$options = array();
			$terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false, 'parent' => $parent === false ? '' : $parent, 'lang' => ''));
			foreach($terms as $term) {
				$options[] = array('value' => $term->term_id, 'label' => $term->name, 'depth' => $depth);
				if($parent !== false) {
					$options = array_merge($options, static::getTermOptions($taxonomy, $term->term_id, $depth + 1));
				}
			}
			static::$optionsCache[$cacheKey] = $options;
			return $options;
		}


	}
}