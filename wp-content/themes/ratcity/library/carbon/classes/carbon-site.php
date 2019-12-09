<?php

if(!class_exists('CarbonSite')) {
	class CarbonSite extends CarbonObject {

		public $id = null;
		public $name = '';
		public $title = '';
		public $description = '';
		public $admin_email = '';
		public $path = '';
		public $url = '';
		public $link = '';
		public $language = '';
		public $charset = '';
		public $pingback_url = '';
		public $language_attributes = '';
		public $multisite = false;
		public $theme = null;
		public $meta = null;

		public function __construct($siteId = null) {
			$this->init($siteId);
		}

		protected function init($siteId = null) {

			if(is_multisite() && $siteId === null) {
				restore_current_blog();
				$siteId = get_current_blog_id();
			}
			if(is_multisite()) switch_to_blog($siteId);

			$this->id = $siteId;
			$this->name = get_bloginfo('name');
			$this->title = $this->name;
			$this->description = get_bloginfo('description');
			$this->admin_email = get_bloginfo('admin_email');
			$this->path = ABSPATH;
			$this->url = get_bloginfo('url');
			$this->link = $this->url;
			$this->language = get_bloginfo('language');
			$this->charset = get_bloginfo('charset');
			$this->pingback_url = get_bloginfo('pingback_url');
			$this->language_attributes = Carbon::getOB('language_attributes');
			$this->multisite = is_multisite();
			$this->theme = new CarbonTheme(get_option('stylesheet'));
			$this->template = $this->theme;
			if($this->theme->parent) {
				$this->template = $this->theme->parent;
			}

			$this->meta = new stdClass();
			$this->importMeta();

			if(is_multisite()) restore_current_blog();

		}

		public function __get($field) {
			if(!isset($this->$field)) {
				if($this->multisite) {
					$this->$field = get_blog_option($this->ID, $field);
				} else {
					$this->$field = get_option($field);
				}
			}
			return $this->$field;
		}

		protected function importMeta() {

			$meta = apply_filters('carbon_import_site_meta', array(), $this->id, $this);
			
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