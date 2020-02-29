<?php

$view = new CarbonView();

$context = Carbon::getGlobalContext();

$view->setContext($context);

$templates = array('archive-'.get_queried_object()->name.'.html.twig', 'index.html.twig');
$view->setTemplates($templates);

$view->render();