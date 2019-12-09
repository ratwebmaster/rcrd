<?php

if(!class_exists('CarbonTaxonomy')) {
	class CarbonTaxonomy extends CarbonObject {

		public $name = '';
		public $label = '';
		public $singular_label = '';
		public $meta = null;

		public function __construct($name = null) {
			if(!empty($name)) {
				$this->init($name);
			}
		}

		protected function init($name = null) {

			// retrieve taxonomy data
			$taxonomyData = wp_cache_get('carbon-taxonomy-'.$name, 'data');
			if($taxonomyData === false) {
				$taxonomyData = get_taxonomy($name);
				wp_cache_set('carbon-taxonomy-'.$name, $taxonomyData, 'data');
			}
			if(empty($taxonomyData)) return;

			$this->name = $taxonomyData->name;
			$this->label = $taxonomyData->label;
			if(property_exists($taxonomyData, 'singular_label')) {
				$this->singular_label = $taxonomyData->singular_label;
			} else if(property_exists($taxonomyData, 'labels') && property_exists($taxonomyData->labels, 'singular_name')) {
				$this->singular_label = $taxonomyData->labels->singular_name;
			}

			$this->meta = new stdClass();
			$this->importMeta();

		}

		protected function importMeta() {

			$meta = apply_filters('carbon_import_taxonomy_meta', array(), $this->name, $this);
			
			if(is_array($meta)) {
				foreach($meta as $key => $value) {
					if(!empty($key)) {
						$this->meta->$key = $value;
					}
				}
			}

		}

	}
}