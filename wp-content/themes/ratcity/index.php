<?php

$view = new CarbonView();

$context = Carbon::getGlobalContext();
$context['sidebar'] = new CarbonSidebar('blog');
$view->setContext($context);

$templates = array('index.html.twig');
if(is_home()) {
	array_unshift($templates, 'home.html.twig');
}
$view->setTemplates($templates);

$view->render();