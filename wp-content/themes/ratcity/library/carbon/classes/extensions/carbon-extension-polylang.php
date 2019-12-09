<?php

if(!class_exists('CarbonExtensionPolylang')) {
	class CarbonExtensionPolylang {

		public function __construct() {
			add_filter('carbon_twig_filters', array(&$this, 'addTwigFilters'), 9, 1);
			add_action('admin_init', array(&$this, 'registerTranslatableStrings'), 9, 0);
		}

		public function addTwigFilters($filters) {
			return array_merge($filters, array(
				'translate' => array('callback' => array(&$this, 'twigFilterTranslate')),
			));
		}

		public function twigFilterTranslate($string, $context = '') {
			$this->addTranslatableString($string, $context);
			if(function_exists('pll__')) return pll__($string);
			return $string;
		}

		protected function addTranslatableString($string, $context = '') {
			$string = trim($string);
			$context = trim($context);
			if(empty($string)) return;
			$string .= '[{('.$context.')}]';
			$strings = get_option('carbon_translatable_strings', array());
			if(!in_array($string, $strings)) {
				$strings[] = $string;
				update_option('carbon_translatable_strings', $strings);
			}
		}

		public function registerTranslatableStrings() {
			if(function_exists('pll_register_string')) {
				$strings = get_option('carbon_translatable_strings', array());
				foreach($strings as $string) {
					if(preg_match('/^(.+(?=\[\{\())\[\{\((.*(?=\)\}\]))\)\}\]$/ms', $string, $matches)) {
						pll_register_string($matches[2], $matches[1], 'Theme', strpos($matches[1], "\n") !== false || strlen($matches[1]) > 100);
					}
				}
			}
		}

	}
}