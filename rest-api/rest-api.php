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
	
	// Terms.
	foreach ( get_taxonomies( array( 'show_in_rest' => true ), 'object' ) as $taxonomy ) :
		$class = ! empty( $taxonomy->rest_controller_class ) ? $taxonomy->rest_controller_class : 'UCI_REST_Terms_Controller';

		if ( ! class_exists( $class ) ) {
			continue;
		}
		$controller = new $class( $taxonomy->name );
		if ( ! is_subclass_of( $controller, 'UCI_REST_Controller' ) ) {
			continue;
		}

		$controller->register_routes();
	endforeach;	
}
add_action('rest_api_init', 'uci_register_rest_routes', 99);
?>