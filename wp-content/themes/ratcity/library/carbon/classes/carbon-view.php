<?php

if(!class_exists('CarbonView')) {
	class CarbonView {

		protected $twig = null;

		protected $templates = array();
		protected $context = array();

		public function __construct() {
			$this->twig = new CarbonTwig();
		}

		public function getTemplates() {
			return $templates;
		}
		public function setTemplates($templates = array()) {
			$this->templates = is_array($templates) ? $templates : array($templates);
		}
		public function setTemplate($template) {
			$this->setTemplates($template);
		}

		public function getContext() {
			return $context;
		}
		public function setContext($context = array()) {
			if(is_array($context)) $this->context = $context;
		}
		public function addContext($context = array()) {
			if(is_array($context)) $this->context = array_merge($this->context, $context);
		}

		public function getRender() {
			return $this->twig->render($this->templates, $this->context);
		}
		public function render() {
			echo $this->getRender();
		}

	}
}