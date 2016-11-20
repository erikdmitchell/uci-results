<?php
function country_init() {
	register_taxonomy('country', array('riders', 'races'), array(
		'hierarchical'      => true,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_ui'           => true,
		'show_admin_column' => false,
		'query_var'         => true,
		'rewrite'           => true,
		'capabilities'      => array(
			'manage_terms'  => 'edit_posts',
			'edit_terms'    => 'edit_posts',
			'delete_terms'  => 'edit_posts',
			'assign_terms'  => 'edit_posts'
		),
		'labels'            => array(
			'name'                       => __( 'Countries', 'uci-results' ),
			'singular_name'              => _x( 'Country', 'taxonomy general name', 'uci-results' ),
			'search_items'               => __( 'Search countries', 'uci-results' ),
			'popular_items'              => __( 'Popular countries', 'uci-results' ),
			'all_items'                  => __( 'All countries', 'uci-results' ),
			'parent_item'                => __( 'Parent country', 'uci-results' ),
			'parent_item_colon'          => __( 'Parent country:', 'uci-results' ),
			'edit_item'                  => __( 'Edit country', 'uci-results' ),
			'update_item'                => __( 'Update country', 'uci-results' ),
			'add_new_item'               => __( 'New country', 'uci-results' ),
			'new_item_name'              => __( 'New country', 'uci-results' ),
			'separate_items_with_commas' => __( 'Separate countries with commas', 'uci-results' ),
			'add_or_remove_items'        => __( 'Add or remove countries', 'uci-results' ),
			'choose_from_most_used'      => __( 'Choose from the most used countries', 'uci-results' ),
			'not_found'                  => __( 'No countries found.', 'uci-results' ),
			'menu_name'                  => __( 'Countries', 'uci-results' ),
		),
		'show_in_rest'      => true,
		'rest_base'         => 'country',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	) );

}
add_action( 'init', 'country_init' );

function race_class_init() {
	register_taxonomy('race_class', array('races'), array(
		'hierarchical'      => true,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_ui'           => true,
		'show_admin_column' => false,
		'query_var'         => true,
		'rewrite'           => true,
		'capabilities'      => array(
			'manage_terms'  => 'edit_posts',
			'edit_terms'    => 'edit_posts',
			'delete_terms'  => 'edit_posts',
			'assign_terms'  => 'edit_posts'
		),
		'labels'            => array(
			'name'                       => __( 'Class', 'uci-results' ),
			'singular_name'              => _x( 'Class', 'taxonomy general name', 'uci-results' ),
			'search_items'               => __( 'Search classes', 'uci-results' ),
			'popular_items'              => __( 'Popular classes', 'uci-results' ),
			'all_items'                  => __( 'All classes', 'uci-results' ),
			'parent_item'                => __( 'Parent class', 'uci-results' ),
			'parent_item_colon'          => __( 'Parent class:', 'uci-results' ),
			'edit_item'                  => __( 'Edit class', 'uci-results' ),
			'update_item'                => __( 'Update class', 'uci-results' ),
			'add_new_item'               => __( 'New class', 'uci-results' ),
			'new_item_name'              => __( 'New class', 'uci-results' ),
			'separate_items_with_commas' => __( 'Separate classes with commas', 'uci-results' ),
			'add_or_remove_items'        => __( 'Add or remove classes', 'uci-results' ),
			'choose_from_most_used'      => __( 'Choose from the most used classes', 'uci-results' ),
			'not_found'                  => __( 'No classes found.', 'uci-results' ),
			'menu_name'                  => __( 'Class', 'uci-results' ),
		),
		'show_in_rest'      => true,
		'rest_base'         => 'race_class',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	) );

}
add_action('init', 'race_class_init');	

function series_init() {
	register_taxonomy('series', array('races'), array(
		'hierarchical'      => true,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_ui'           => true,
		'show_admin_column' => false,
		'query_var'         => true,
		'rewrite'           => true,
		'capabilities'      => array(
			'manage_terms'  => 'edit_posts',
			'edit_terms'    => 'edit_posts',
			'delete_terms'  => 'edit_posts',
			'assign_terms'  => 'edit_posts'
		),
		'labels'            => array(
			'name'                       => __( 'Series', 'uci-results' ),
			'singular_name'              => _x( 'Series', 'taxonomy general name', 'uci-results' ),
			'search_items'               => __( 'Search series', 'uci-results' ),
			'popular_items'              => __( 'Popular series', 'uci-results' ),
			'all_items'                  => __( 'All series', 'uci-results' ),
			'parent_item'                => __( 'Parent series', 'uci-results' ),
			'parent_item_colon'          => __( 'Parent series:', 'uci-results' ),
			'edit_item'                  => __( 'Edit series', 'uci-results' ),
			'update_item'                => __( 'Update series', 'uci-results' ),
			'add_new_item'               => __( 'New series', 'uci-results' ),
			'new_item_name'              => __( 'New series', 'uci-results' ),
			'separate_items_with_commas' => __( 'Separate series with commas', 'uci-results' ),
			'add_or_remove_items'        => __( 'Add or remove series', 'uci-results' ),
			'choose_from_most_used'      => __( 'Choose from the most used series', 'uci-results' ),
			'not_found'                  => __( 'No series found.', 'uci-results' ),
			'menu_name'                  => __( 'Series', 'uci-results' ),
		),
		'show_in_rest'      => true,
		'rest_base'         => 'series',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	) );

}
add_action('init', 'series_init');	

function season_init() {
	register_taxonomy('season', array('races'), array(
		'hierarchical'      => true,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_ui'           => true,
		'show_admin_column' => false,
		'query_var'         => true,
		'rewrite'           => true,
		'capabilities'      => array(
			'manage_terms'  => 'edit_posts',
			'edit_terms'    => 'edit_posts',
			'delete_terms'  => 'edit_posts',
			'assign_terms'  => 'edit_posts'
		),
		'labels'            => array(
			'name'                       => __( 'Season', 'uci-results' ),
			'singular_name'              => _x( 'Season', 'taxonomy general name', 'uci-results' ),
			'search_items'               => __( 'Search season', 'uci-results' ),
			'popular_items'              => __( 'Popular season', 'uci-results' ),
			'all_items'                  => __( 'All season', 'uci-results' ),
			'parent_item'                => __( 'Parent season', 'uci-results' ),
			'parent_item_colon'          => __( 'Parent season:', 'uci-results' ),
			'edit_item'                  => __( 'Edit season', 'uci-results' ),
			'update_item'                => __( 'Update season', 'uci-results' ),
			'add_new_item'               => __( 'New season', 'uci-results' ),
			'new_item_name'              => __( 'New season', 'uci-results' ),
			'separate_items_with_commas' => __( 'Separate season with commas', 'uci-results' ),
			'add_or_remove_items'        => __( 'Add or remove season', 'uci-results' ),
			'choose_from_most_used'      => __( 'Choose from the most used season', 'uci-results' ),
			'not_found'                  => __( 'No season found.', 'uci-results' ),
			'menu_name'                  => __( 'Season', 'uci-results' ),
		),
		'show_in_rest'      => true,
		'rest_base'         => 'season',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	) );

}
add_action('init', 'season_init');
?>