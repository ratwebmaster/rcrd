<?php /* Template Name: Team Page */


$view = new CarbonView();

$context = Carbon::getGlobalContext();



$context['team_data'] = (object)array(
    'slug' => '',
    'name' => '',
    'logo' => '',
);

$postId = $post->ID;
$team_id = get_post_meta($postId, 'team_name', true);
$team_data = get_term($team_id, 'member_team');

$team_slug = $team_data->slug;
if(!empty($team_slug)) $context['team_data']->slug = $team_slug;

$team_name = $team_data->name;
if(!empty($team_name)) $context['team_data']->name = $team_name;

$team_logo = get_term_meta($team_id, 'team_logo');
if(!empty($team_logo)) $context['team_data']->logo = wp_get_attachment_image_src($team_logo[0]);



$view->setContext($context);
$templates = array('page-team.html.twig');
$view->setTemplates($templates);

$view->render();