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

/**
 * uci_rankings_rest_routes function.
 * 
 * @access public
 * @return void
 */
function uci_rankings_rest_routes() {
	global $uci_rankings;
	
	register_rest_route('uci/v1', '/uci-rankings/last-update', array(
		'methods' => 'GET',
		'callback' => 'uci_rankings_last_update',
	));
	
	register_rest_route('uci/v1', '/uci-rankings/(?P<id>[\d]+)', array(
		'methods' => 'GET',
		'callback' => 'uci_rankings_api_get_rank',
	));	
	
	register_rest_route('uci/v1', '/uci-rankings/max-rank', array(
		'methods' => 'GET',
		'callback' => 'uci_rankings_api_max_rank',
	));		

	register_rest_route('uci/v1', '/uci-rankings/', array(
		'methods' => 'GET',
		'callback' => 'uci_rankings_api_get_rankings',
	));	
}
add_action('rest_api_init', 'uci_rankings_rest_routes');

/**
 * uci_rankings_api_get_rank function.
 * 
 * @access public
 * @param mixed $request
 * @return void
 */
function uci_rankings_api_get_rank($request) {
	global $uci_rankings;

	return $uci_rankings->get_rank($request['id']);
}

/**
 * uci_rankings_api_max_rank function.
 * 
 * @access public
 * @return void
 */
function uci_rankings_api_max_rank() {
	global $uci_rankings;
	
	return $uci_rankings->max_rank();
}

/**
 * uci_rankings_api_get_rankings function.
 * 
 * @access public
 * @param mixed $request
 * @return void
 */
function uci_rankings_api_get_rankings($request) {
	global $uci_rankings;
	
	return $uci_rankings->get_rankings($request->get_params());
}
?>