<?php

if(!class_exists('CarbonMenu')) {
	class CarbonMenu extends CarbonObject {

		public $id = null;
		public $name = '';
		public $title = '';
		public $slug = '';
		public $description = '';
		public $meta = null;
		public $items = array();

		public function __construct($menuId = null) {

			if(!is_numeric($menuId) && !empty($menuId)) {
				$themeLocationMenus = get_nav_menu_locations();
				if(!empty($themeLocationMenus[$menuId])) {
					$menuId = $themeLocationMenus[$menuId];
				}
			}

			if(!empty($menuId)) {
				$this->init($menuId);
			}

		}

		protected function init($menuId) {

			if(array_key_exists($menuId, Carbon::$objectCache['menus'])) {
				$this->importProps(Carbon::$objectCache['menus'][$menuId]);
				return;
			}

			$menuItems = wp_get_nav_menu_items($menuId);
			if($menuItems) {

				_wp_menu_item_classes_by_context($menuItems);
				if(is_array($menuItems)) $menuItems = $this->orderItems($menuItems);
				$this->items = $menuItems;

				$menuData = wp_get_nav_menu_object($menuId);

				$this->id = $menuData->term_id;
				$this->name = $menuData->name;
				$this->title = $this->name;
				$this->slug = $menuData->slug;
				$this->description = $menuData->description;

				$this->meta = new stdClass();
				$this->importMeta();

				Carbon::$objectCache['menus'][$this->id] = clone $this;

			}

		}

		protected function importMeta() {

			$meta = apply_filters('carbon_import_menu_meta', array(), $this->id, $this);
			
			if(is_array($meta)) {
				foreach($meta as $key => $value) {
					if(!empty($key)) {
						$this->meta->$key = $value;
					}
				}
			}

		}

		protected function orderItems($items) {
			$index = array();
			$menu = array();
			foreach($items as $item) {
				if(isset($item->ID)) {
					$menuItem = new CarbonMenuItem($item);
					$index[$item->ID] = $menuItem;
				}
			}
			$index = apply_filters('carbon_menu_item_index', $index, $items);
			foreach($index as $i => $item) {
				if(isset($item->parent_id) && $item->parent_id && isset($index[$item->parent_id])) {
					$index[$item->parent_id]->addChild($item);
				} else {
					$menu[] = $index[$i];
				}
			}
			return $menu;
		}

	}
}