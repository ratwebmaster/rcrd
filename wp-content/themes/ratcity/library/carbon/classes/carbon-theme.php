<?php

if(!class_exists('CarbonTheme')) {
	class CarbonTheme extends CarbonObject {

		public $name = '';
		public $slug = '';
		public $path = '';
		public $uri = '';
		public $link = '';
		public $parent = null;
		public $menus = array();
		public $meta = null;

		public function __construct($slug = null) {
			$this->init($slug);
		}

		protected function init($slug = null) {

			$themeDetails = wp_get_theme($slug);

			$this->name = $themeDetails->get('Name');
			$this->slug = $themeDetails->get_stylesheet();
			$this->path = get_stylesheet_directory();
			$this->uri = get_stylesheet_directory_uri();

			$parentSlug = $themeDetails->get('Template');
			if(!$parentSlug) {
				$this->path = get_template_directory();
				$this->uri = get_template_directory_uri();
			}
			if($parentSlug && $parentSlug != $this->slug) {
				$this->parent = new CarbonTheme($parentSlug);
			}

			$this->link = $this->uri;

			$this->menus = array();
			$themeLocationMenus = get_nav_menu_locations();
			foreach(array_keys(get_registered_nav_menus()) as $menuLocation) {
				$this->menus[$menuLocation] = !empty($themeLocationMenus[$menuLocation]) ? new CarbonMenu($themeLocationMenus[$menuLocation]) : null;
			}

			$this->meta = new stdClass();
			$this->importMeta();

		}

		protected function importMeta() {

			$meta = apply_filters('carbon_import_theme_meta', array(), $this->name, $this);
			
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