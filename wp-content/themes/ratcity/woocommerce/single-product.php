<?php


add_filter('carbon_twig_functions', function($functions) {
	return array_merge($functions, array(
		'get_woocommerce_content' => array('callback' => function() {

			ob_start();
			do_action('woocommerce_before_main_content');
			while(have_posts()) {
				the_post();
				wc_get_template_part('content', 'single-product');
			}
			do_action('woocommerce_after_main_content');
			return ob_get_clean();

		})
	));
});


$view = new CarbonView();

$context = Carbon::getGlobalContext();
$context['sidebar'] = new CarbonSidebar('blog');
$view->setContext($context);

$templates = array('woocommerce/single-product.html.twig');
$view->setTemplates($templates);

$view->render();