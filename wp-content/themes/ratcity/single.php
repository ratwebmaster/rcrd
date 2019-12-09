<?php

$view = new CarbonView();

$context = Carbon::getGlobalContext();
$context['sidebar'] = new CarbonSidebar('blog');
$view->setContext($context);

$templates = array('single-'.$post->post_type.'.html.twig', 'single.html.twig');
$view->setTemplates($templates);

$view->render();