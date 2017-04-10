<?php
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
		'rest_controller_class' => 'UCI_REST_Terms_Controller',
	) );

}
add_action('init', 'race_class_init');	
?>