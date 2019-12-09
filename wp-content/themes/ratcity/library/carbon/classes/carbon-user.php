<?php

if(!class_exists('CarbonUser')) {
	class CarbonUser extends CarbonObject {

		public $id = null;
		public $username = '';
		public $email = '';
		public $url = '';
		public $nicename = '';
		public $display_name = '';
		public $status = 0;
		public $first_name = '';
		public $last_name = '';
		public $nickname = '';
		public $posts_url = '';
		public $post_count = 0;
		public $meta = null;
		public $object = null;

		public function __construct($userId = null) {
			$currentUserId = get_current_user_id();
			if($userId === null && !empty($currentUserId)) {
				$userId = $currentUserId;
			}
			$this->init($userId);
		}

		protected function init($userId = 0) {

			// resolve user ID
			if(is_numeric($userId) && $userId === 0) {
				$userId = get_current_user_id();
			} else if(!is_numeric($userId) && is_string($userId)) {
				if(($userIdFromName = username_exists($userId))) {
					$userId = $userIdFromName;
				} else if(($userIdFromEmail = email_exists($userId))) {
					$userId = $userIdFromEmail;
				}
			}

			$userCacheId = 0;
			if(is_object($userId) && get_class($userId) == 'WP_User') {
				$userCacheId = $userId->ID;
			} else {
				$userCacheId = $userId;
			}
			if(array_key_exists($userCacheId, Carbon::$objectCache['users'])) {
				$this->importProps(Carbon::$objectCache['users'][$userCacheId]);
				return;
			}

			// retrieve user data
			$userData = array();
			if(is_object($userId) && get_class($userId) == 'WP_User') {
				$userData = $userId;
			} else if(is_numeric($userId) && $userId > 0) {
				$userData = new WP_User($userId);
			} else {
				$userData = wp_get_current_user();
			}
			if(empty($userData)) return;

			$this->id = $userData->ID;
			$this->username = $userData->user_login;
			$this->email = $userData->user_email;
			$this->url = $userData->user_url;
			$this->nicename = $userData->user_nicename;
			$this->display_name = $userData->display_name;
			$this->status = $userData->user_status;
			$this->posts_url = get_author_posts_url($this->id);
			$this->post_count = count_user_posts($this->id);
			$this->object = $userData;

			$this->meta = new stdClass();
			$this->importMeta();

			if(property_exists($this->meta, 'first_name')) $this->first_name = $this->meta->first_name;
			if(property_exists($this->meta, 'last_name')) $this->last_name = $this->meta->last_name;
			if(property_exists($this->meta, 'nickname')) $this->nickname = $this->meta->nickname;

			Carbon::$objectCache['users'][$this->id] = clone $this;

		}

		protected function importMeta() {

			$meta = get_user_meta($this->id);
			if(!is_array($meta) || empty($meta)) $meta = array();
			foreach($meta as $key => $value) {
				if(is_array($value) && count($value) == 1) {
					$value = $value[0];
				}
				$meta[$key] = maybe_unserialize($value);
			}
			$meta = apply_filters('carbon_import_user_meta', $meta, $this->id, $this);
			
			if(is_array($meta)) {
				foreach($meta as $key => $value) {
					if(!empty($key)) {
						$this->meta->$key = $value;
					}
				}
			}

		}

		public function can($capability = '') {
			$args = array_slice(func_get_args(), 1);
			$args = array_merge(array($capability), $args);
			return call_user_func_array(array($this->object, 'has_cap'), $args);
		}

		public function getAvatar($size = 100) {
			return get_avatar($this->id, $size);
		}

	}
}