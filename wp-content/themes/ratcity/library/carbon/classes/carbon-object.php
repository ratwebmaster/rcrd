<?php

if(!class_exists('CarbonObject')) {
	class CarbonObject {

		public function __isset($field) {
			if(isset($this->$field)) {
				return $this->$field;
			}
			return false;
		}

		public function __call($field, $args) {
			return $this->__get($field);
		}

		public function __get($field) {
			return isset($this->$field) ? $this->$field : null;
		}

		/*public function import($data) {
			if(is_object($data)) $data = get_object_vars($data);
			if(is_array($data)) {
				foreach($data as $key => $value) {
					if(!empty($key)) {
						$this->$key = $value;
					}
				}
			}
		}*/

		protected function importProps($object) {
			foreach(get_object_vars($object) as $key => $value) {
				$this->$key = $value;
			}
		}

	}
}