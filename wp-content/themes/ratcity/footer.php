<?php

$context = $GLOBALS['carbon_context'];
if(!isset($context)) {
	throw new Exception('Carbon context not set in footer.');
}
$context['plugin_content'] = ob_get_clean();

$templates = array('page-plugin.html.twig', 'base.html.twig');

$view = new CarbonView();
$view->setContext($context);
$view->setTemplates($templates);
$view->render();