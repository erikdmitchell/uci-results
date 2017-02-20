<?php
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