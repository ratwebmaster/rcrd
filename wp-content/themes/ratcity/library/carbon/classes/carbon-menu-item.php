<?php

if(!class_exists('CarbonMenuItem')) {
	class CarbonMenuItem extends CarbonObject {

		public $id = null;
		public $label = '';
		public $title = '';
		public $description = '';
		public $attr_title = '';
		public $guid = '';
		public $url = '';
		public $link = '';
		public $type = '';
		public $type_label = '';
		public $parent_id = null;
		public $classes = array();
		public $class = '';
		public $xfn = '';
		public $target = '';
		public $meta = null;
		public $object = null;
		public $children = array();

		public function __construct($menuItem = null) {
			if($menuItem) $this->init($menuItem);
		}

		protected function init($menuItem = null) {

			if(array_key_exists($menuItem->ID, Carbon::$objectCache['menu-items'])) {
				$this->importProps(Carbon::$objectCache['menu-items'][$menuItem->ID]);
				return;
			}

			$this->id = $menuItem->ID;
			$this->title = $menuItem->title;
			$this->label = $this->title;
			$this->description = $menuItem->description;
			$this->attr_title = $menuItem->attr_title;
			$this->guid = $menuItem->guid;
			$this->url = $menuItem->url;
			$this->link = $this->url;
			$this->type = $menuItem->type;
			$this->type_label = $menuItem->type_label;
			$this->parent_id = $menuItem->menu_item_parent;
			$this->xfn = $menuItem->xfn;
			$this->target = $menuItem->target;
			$this->object = $menuItem;

			$this->addClasses(array_merge(array('menu-item-'.$this->id), apply_filters('nav_menu_css_class', $menuItem->classes, $menuItem)));

			$this->meta = new stdClass();
			$this->importMeta();

			Carbon::$objectCache['menu-items'][$this->id] = clone $this;

		}

		public function addClasses($classes = array()) {
			$classes = is_array($classes) ? $classes : explode(' ', $classes);
			$this->classes = array_merge($this->classes, $classes);
			$this->classes = array_unique($this->classes);
			$this->class = trim(implode(' ', $this->classes));
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
			$meta = apply_filters('carbon_import_menu_item_meta', $meta, $this->id, $this);
			
			if(is_array($meta)) {
				foreach($meta as $key => $value) {
					if(!empty($key)) {
						$this->meta->$key = $value;
					}
				}
			}

		}

		public function addChild($item) {
			if(empty($this->children)) {
				$this->addClasses('menu-item-has-children');
			}
			$this->children[] = $item;
		}

	}
}