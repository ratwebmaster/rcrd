<?php

if(!class_exists('CarbonSidebar')) {
	class CarbonSidebar extends CarbonObject {

		public $id = null;
		public $name = '';
		public $description = '';
		public $class = '';
		public $before_widget = '';
		public $after_widget = '';
		public $before_title = '';
		public $after_title = '';
		public $meta = null;
		// public $widgets = '';

		public function __construct($sidebarId = null) {

			if(!empty($sidebarId)) {
				$this->init($sidebarId);
			}

		}

		protected function init($sidebarId) {
			global $wp_registered_sidebars;
			if(!array_key_exists($sidebarId, $wp_registered_sidebars)) return;
			$sidebarData = $wp_registered_sidebars[$sidebarId];

			$this->id = $sidebarData['id'];
			$this->name = $sidebarData['name'];
			$this->description = $sidebarData['description'];
			$this->class = $sidebarData['class'];
			$this->before_widget = $sidebarData['before_widget'];
			$this->after_widget = $sidebarData['after_widget'];
			$this->before_title = $sidebarData['before_title'];
			$this->after_title = $sidebarData['after_title'];

			$this->meta = new stdClass();
			$this->importMeta();

			// $this->widgets = '';

		}

		protected function importMeta() {

			$meta = apply_filters('carbon_import_sidebar_meta', array(), $this->id, $this);
			
			if(is_array($meta)) {
				foreach($meta as $key => $value) {
					if(!empty($key)) {
						$this->meta->$key = $value;
					}
				}
			}

		}

		public function __get($field) {
			if($field == 'widgets') {
				return Carbon::getOB('dynamic_sidebar', $this->id);
			}
			return isset($this->$field) ? $this->$field : null;
		}

	}
}