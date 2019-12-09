<?php

$view = new CarbonView();

$context = Carbon::getGlobalContext();
$view->setContext($context);

$templates = array('page.html.twig');
if(is_front_page()) {
	array_unshift($templates, 'front-page.html.twig');
}
$view->setTemplates($templates);

$view->render();