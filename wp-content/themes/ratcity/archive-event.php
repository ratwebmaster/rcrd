<?php

$view = new CarbonView();

$context = Carbon::getGlobalContext();
//var_dump($context);

$all_events = $context['posts'];

$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$filtered = [];

if ($type) {

    foreach ($all_events as $key => $event) {
        if ($type !== 'all') {
            $terms = wp_get_post_terms($event->id, 'event_type');
            foreach ($terms as $term) {
                if ($type == $term->slug) {
                    $filtered[$key] = $event;
                }
            }
        } else {
            $filtered[$key] = $event;
        }
    }

    $context['posts'] = $filtered;

}

//var_dump($context);

$view->setContext($context);

$templates = array('archive-' . get_queried_object()->name . '.html.twig', 'index.html.twig');
$view->setTemplates($templates);

$view->render();