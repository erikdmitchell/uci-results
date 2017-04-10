<?php

/**
 * uci_register_rest_routes function.
 * 
 * @access public
 * @return void
 */
function uci_register_rest_routes() {
	$post_types=array('riders', 'races');
	
	foreach ($post_types as $post_type) :
		$controller = new UCI_REST_Posts_Controller($post_type);
		$controller->register_routes();	
	endforeach;
	
	// Taxonomies.
	$controller = new UCI_REST_Taxonomies_Controller;
	$controller->register_routes();
}
add_action('rest_api_init', 'uci_register_rest_routes', 99);
?>