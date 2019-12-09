<?php

if(!class_exists('GF_Field_Column') && class_exists('GF_Field')) {
	class GF_Field_Column extends GF_Field {

		public $type = 'column';

		public function get_form_editor_field_title() {
			return esc_attr__('Column Break', 'gravityforms');
		}

		public function is_conditional_logic_supported(){
			return false;
		}

		function get_form_editor_field_settings() {
			return array(
				'column_description',
				'css_class_setting'
			);
		}

		public function get_field_input($form, $value = '', $entry = null) {
			return '';
		}

		public function get_field_content($value, $forceFrontendLabel, $form) {

			$isEntryDetail = $this->is_entry_detail();
			$isFormEditor = $this->is_form_editor();
			$isAdmin = $isEntryDetail || $isFormEditor;

			if($isAdmin) {
				$adminButtons = $this->get_admin_buttons();
				return $adminButtons.'<label class=\'gfield_label\'>'.$this->get_form_editor_field_title().'</label>{FIELD}<hr>';
			}

			return '';
		}

	}
}


if(!class_exists('ZeroGFMultiColumn')) {
	class ZeroGFMultiColumn {

		public static function init() {
			add_action('init', array('ZeroGFMultiColumn', 'registerGformFields'), 20);
			add_action('gform_field_standard_settings', array('ZeroGFMultiColumn', 'addGformColumnFieldSettings'), 10, 2);
			add_filter('gform_field_container', array('ZeroGFMultiColumn', 'filterGformFieldContainer'), 10, 6);
			add_filter('gform_pre_render', array('ZeroGFMultiColumn', 'filterGformPreRender'), 10, 3);
		}

		public static function registerGformFields() {
			if(!class_exists('GFForms') || !class_exists('GF_Field_Column')) return;
			GF_Fields::register(new GF_Field_Column());
		}

		public static function addGformColumnFieldSettings($placement, $formId) {
			if($placement == 0) {
				?>
					<li class="column_description field_setting">
						Column breaks should be placed between fields to split form into separate columns. You do not need to place any column breaks at the beginning or end of the form, only in the middle.
					</li>
				<?php
			}
		}

		public static function filterGformFieldContainer($fieldContainer, $field, $form, $cssClass, $style, $fieldContent) {
			if($field['type'] == 'column') {
				if(IS_ADMIN) {
					$fieldContainer = str_replace('{FIELD_CONTENT}', $fieldContent, $fieldContainer);
					return preg_replace('/<label.*<\/label>(<hr>)?/', '<label class="gfield_label">Column Break</label><hr>', $fieldContainer);
				} else {
					$columnIndex = 2;
					foreach($form['fields'] as $formField) {
						if($formField['id'] == $field['id']) break;
						if($formField['type'] == 'column') $columnIndex++;
					}
					return '</ul><ul class="'.GFCommon::get_ul_classes($form).' column column_'.$columnIndex.' '.$field['cssClass'].'">';
				}
			}
			return $fieldContainer;
		}

		public static function filterGformPreRender($form, $ajax, $fieldValues) {
			$columnCount = 0;
			$prevPageField = null;
			foreach($form['fields'] as $field) {
				if($field['type'] == 'column') {
					$columnCount++;
				} else if($field['type'] == 'page') {
					if($columnCount > 0 && empty($prevPageField)) {
						$form['firstPageCssClass'] = trim((isset($field['firstPageCssClass']) ? $field['firstPageCssClass'] : '').' gform_page_multi_column gform_page_column_count_'.($columnCount + 1));
					} else if($columnCount > 0) {
						$prevPageField['cssClass'] = trim((isset($prevPageField['cssClass']) ? $prevPageField['cssClass'] : '').' gform_page_multi_column gform_page_column_count_'.($columnCount + 1));
					}
					$prevPageField = $field;
					$columnCount = 0;
				}
			}
			if($columnCount > 0 && empty($prevPageField)) {
				$form['cssClass'] = trim((isset($form['cssClass']) ? $form['cssClass'] : '').' gform_multi_column gform_column_count_'.($columnCount + 1));
			} else if($columnCount > 0) {
				$prevPageField['cssClass'] = trim((isset($prevPageField['cssClass']) ? $prevPageField['cssClass'] : '').' gform_page_multi_column gform_page_column_count_'.($columnCount + 1));
			}
			return $form;
		}

	}
}
ZeroGFMultiColumn::init();