<?php

if(!class_exists('CarbonExtensionCrown')) {
	class CarbonExtensionCrown {

		public function __construct() {
			if(defined('CROWN_FRAMEWORK_VERSION')) {

				add_filter('carbon_content', 'preprocess_shortcodes', 7);

				add_filter('carbon_import_post_meta', array(&$this, 'importPostRepeaterEntries'), 9, 3);
				add_filter('carbon_import_term_meta', array(&$this, 'importTermRepeaterEntries'), 9, 3);
				add_filter('carbon_import_user_meta', array(&$this, 'importUserRepeaterEntries'), 9, 3);
				add_filter('carbon_import_crown_repeater_entry_repeater_entries', array(&$this, 'importPostRepeaterEntries'), 9, 3);

			}
		}

		public function importPostRepeaterEntries($meta, $postId, $post) {
			return $this->importRepeaterEntries($meta, 'post', $postId);
		}

		public function importTermRepeaterEntries($meta, $termId, $term) {
			return $this->importRepeaterEntries($meta, 'term', $termId);
		}

		public function importUserRepeaterEntries($meta, $userId, $user) {
			return $this->importRepeaterEntries($meta, 'user', $userId);
		}

		public function importRepeaterEntries($meta = array(), $type = 'site', $objectId = 0) {

			$entryQueryArgs = array(
				'posts_per_page' => -1,
				'post_type' => 'crown_repeater_entry',
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'meta_query' => array(
					array(
						'key' => 'crown_repeater_entry_object_type',
						'value' => $type
					)
				)
			);
			if(!in_array($type, array('site, blog'))) {
				$entryQueryArgs['post_parent'] = $objectId;
			}
			$entries = get_posts($entryQueryArgs);

			foreach($entries as $entry) {

				// $metaKey = get_post_meta($entry->ID, 'crown_repeater_entry_name', true);

				$repeaterEntry = new CarbonCrownRepeaterEntry($entry->ID);
				if(property_exists($repeaterEntry, 'crown_repeater_entry_name') && !empty($repeaterEntry->crown_repeater_entry_name)) {
					$metaKey = $repeaterEntry->crown_repeater_entry_name;
					if(!array_key_exists($metaKey, $meta) || !is_array($meta[$metaKey])) $meta[$metaKey] = array();
					$meta[$metaKey][] = $repeaterEntry;
				}

			}

			return $meta;

		}

	}
}



if(!class_exists('CarbonCrownRepeaterEntry')) {
	class CarbonCrownRepeaterEntry extends CarbonObject {

		public $id = null;
		
		protected $repeaterEntriesRetrieved = false;

		public function __construct($entryId) {

			if(array_key_exists($entryId, Carbon::$objectCache['posts'])) {
				$this->importProps(Carbon::$objectCache['posts'][$entryId]);
				return;
			}

			$this->id = $entryId;
			$this->importMeta();
			// $this->importRepeaterEntries();
			
			Carbon::$objectCache['posts'][$this->id] = clone $this;

		}

		protected function importMeta() {

			$meta = get_post_custom($this->id);
			if(!is_array($meta) || empty($meta)) $meta = array();
			foreach($meta as $key => $value) {
				if(is_array($value) && count($value) == 1) {
					$value = $value[0];
				}
				$meta[$key] = maybe_unserialize($value);
			}
			$meta = apply_filters('carbon_import_crown_repeater_entry_meta', $meta, $this->id, $this);
			
			if(is_array($meta)) {
				foreach($meta as $key => $value) {
					if(!empty($key)) {
						$this->$key = $value;
					}
				}
			}

		}

		protected function importRepeaterEntries() {

			$meta = apply_filters('carbon_import_crown_repeater_entry_repeater_entries', array(), $this->id, $this);
			
			if(is_array($meta)) {
				foreach($meta as $key => $value) {
					if(!empty($key)) {
						$this->$key = $value;
					}
				}
			}

		}

		public function __get($field) {
			if(array_key_exists($this->id, Carbon::$objectCache['posts']) && isset(Carbon::$objectCache['posts'][$this->id]->$field)) {
				return Carbon::$objectCache['posts'][$this->id]->$field;
			}

			if(!isset($this->$field) && !$this->repeaterEntriesRetrieved) {
				// $this->importMeta();
				$this->importRepeaterEntries();
				$this->repeaterEntriesRetrieved = true;
			}

			Carbon::$objectCache['posts'][$this->id] = clone $this;
			return isset($this->$field) ? $this->$field : null;
		}

	}
}