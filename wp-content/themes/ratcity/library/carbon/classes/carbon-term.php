<?php

if(!class_exists('CarbonTerm')) {
	class CarbonTerm extends CarbonObject {

		public $id = null;
		public $name = '';
		public $slug = '';
		public $group = '';
		public $taxonomy = '';
		public $description = '';
		public $parent_id = '';
		public $count = 0;
		public $url = '';
		public $meta = null;

		public function __construct($termId = null, $taxonomy = '') {
			if(!empty($termId) && !empty($taxonomy)) {
				$this->init($termId, $taxonomy);
			}
		}

		protected function init($termId = null, $taxonomy = '') {

			if(array_key_exists($termId, Carbon::$objectCache['terms'])) {
				$this->importProps(Carbon::$objectCache['terms'][$termId]);
				return;
			}

			// retrieve term data
			$termData = get_term($termId, $taxonomy);
			if(empty($termData) && !is_a($termData, 'WP_Error')) return;

			$this->id = $termData->term_id;
			$this->name = $termData->name;
			$this->slug = $termData->slug;
			$this->group = $termData->term_group;
			$this->taxonomy = new CarbonTaxonomy($termData->taxonomy);
			$this->description = $termData->description;
			$this->parent_id = $termData->parent;
			$this->count = $termData->count;
			$this->url = get_term_link($this->id, $termData->taxonomy);

			$this->meta = new stdClass();
			$this->importMeta();

			Carbon::$objectCache['terms'][$this->id] = clone $this;

		}

		protected function importMeta() {

			$meta = array();
			if(is_callable('get_term_meta')) {
				$meta = get_term_meta($this->id);
				if(!is_array($meta) || empty($meta)) $meta = array();
				foreach($meta as $key => $value) {
					if(is_array($value) && count($value) == 1) {
						$value = $value[0];
					}
					$meta[$key] = maybe_unserialize($value);
				}
			}
			$meta = apply_filters('carbon_import_term_meta', $meta, $this->id, $this);
			
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