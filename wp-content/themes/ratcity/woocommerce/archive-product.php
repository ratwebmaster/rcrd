<?php


add_filter('carbon_twig_functions', function($functions) {
	return array_merge($functions, array(
		'get_woocommerce_content' => array('callback' => function() {

			ob_start();
			do_action('woocommerce_before_main_content');
			if(apply_filters('woocommerce_show_page_title', true)) {
				echo '<h1 class="page-title">';
				woocommerce_page_title();
				echo '</h1>';
			}
			do_action('woocommerce_archive_description');
			if(have_posts()) {
				do_action('woocommerce_before_shop_loop');
				woocommerce_product_loop_start();
				woocommerce_product_subcategories();
				while(have_posts()) {
					the_post();
					wc_get_template_part('content', 'product');
				}
				woocommerce_product_loop_end();
				do_action('woocommerce_after_shop_loop');
			} else if(!woocommerce_product_subcategories(array('before' => woocommerce_product_loop_start(false), 'after' => woocommerce_product_loop_end(false)))) {
				wc_get_template('loop/no-products-found.php');
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

$templates = array('woocommerce/archive-product.html.twig');
$view->setTemplates($templates);

$view->render();