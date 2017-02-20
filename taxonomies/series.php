<?php
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
?>