<?php
function discipline_init() {
	register_taxonomy('discipline', array('races'), array(
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
			'name'                       => __( 'Disciplines', 'uci-results' ),
			'singular_name'              => _x( 'Discipline', 'taxonomy general name', 'uci-results' ),
			'search_items'               => __( 'Search Disciplines', 'uci-results' ),
			'popular_items'              => __( 'Popular Disciplines', 'uci-results' ),
			'all_items'                  => __( 'All Disciplines', 'uci-results' ),
			'parent_item'                => __( 'Parent Discipline', 'uci-results' ),
			'parent_item_colon'          => __( 'Parent Discipline:', 'uci-results' ),
			'edit_item'                  => __( 'Edit Discipline', 'uci-results' ),
			'update_item'                => __( 'Update Discipline', 'uci-results' ),
			'add_new_item'               => __( 'New Discipline', 'uci-results' ),
			'new_item_name'              => __( 'New Discipline', 'uci-results' ),
			'separate_items_with_commas' => __( 'Separate Disciplines with commas', 'uci-results' ),
			'add_or_remove_items'        => __( 'Add or remove Disciplines', 'uci-results' ),
			'choose_from_most_used'      => __( 'Choose from the most used Disciplines', 'uci-results' ),
			'not_found'                  => __( 'No Disciplines found.', 'uci-results' ),
			'menu_name'                  => __( 'Disciplines', 'uci-results' ),
		),
		'show_in_rest'      => true,
		'rest_base'         => 'discipline',
		'rest_controller_class' => 'UCI_REST_Terms_Controller',
	) );

}
add_action('init', 'discipline_init');	
?>