<?php

$view = new CarbonView();

$context = Carbon::getGlobalContext();
//var_dump($context);

$all_events = $context['events'];

$type = isset($_GET['type']) ? $_GET['type'] : 'all';

foreach ($all_events as $key => $event) {
    if ($event['event_start_date'] < date('Y-m-d', 'America/Los_Angeles')) {
        unset($all_events[$key]);
    }
}

$filtered = [];

if ( $type ) {

    foreach ($all_events as $key => $event) {
        if ($type !== 'all') {
            $terms = wp_get_post_terms($event['id'], 'event_type');
            foreach ($terms as $term) {
                if ($type == $term->slug) {
                    $filtered[$key] = $event;
                }
            }
        } else {
            $filtered[$key] = $event;
        }
    }

    $context['events'] = $filtered;

}

//var_dump($context);

$view->setContext($context);

$templates = array('archive-' . get_queried_object()->name . '.html.twig', 'index.html.twig');
$view->setTemplates($templates);

$view->render();