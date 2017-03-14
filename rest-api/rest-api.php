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
}
add_action('rest_api_init', 'uci_register_rest_routes', 99);
?>