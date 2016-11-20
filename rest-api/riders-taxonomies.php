<?php
function country_init() {
	register_taxonomy( 'country', array( 'riders' ), array(
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
			'name'                       => __( 'Countries', 'YOUR-TEXTDOMAIN' ),
			'singular_name'              => _x( 'Country', 'taxonomy general name', 'YOUR-TEXTDOMAIN' ),
			'search_items'               => __( 'Search countries', 'YOUR-TEXTDOMAIN' ),
			'popular_items'              => __( 'Popular countries', 'YOUR-TEXTDOMAIN' ),
			'all_items'                  => __( 'All countries', 'YOUR-TEXTDOMAIN' ),
			'parent_item'                => __( 'Parent country', 'YOUR-TEXTDOMAIN' ),
			'parent_item_colon'          => __( 'Parent country:', 'YOUR-TEXTDOMAIN' ),
			'edit_item'                  => __( 'Edit country', 'YOUR-TEXTDOMAIN' ),
			'update_item'                => __( 'Update country', 'YOUR-TEXTDOMAIN' ),
			'add_new_item'               => __( 'New country', 'YOUR-TEXTDOMAIN' ),
			'new_item_name'              => __( 'New country', 'YOUR-TEXTDOMAIN' ),
			'separate_items_with_commas' => __( 'Separate countries with commas', 'YOUR-TEXTDOMAIN' ),
			'add_or_remove_items'        => __( 'Add or remove countries', 'YOUR-TEXTDOMAIN' ),
			'choose_from_most_used'      => __( 'Choose from the most used countries', 'YOUR-TEXTDOMAIN' ),
			'not_found'                  => __( 'No countries found.', 'YOUR-TEXTDOMAIN' ),
			'menu_name'                  => __( 'Countries', 'YOUR-TEXTDOMAIN' ),
		),
		'show_in_rest'      => true,
		'rest_base'         => 'country',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	) );

}
add_action( 'init', 'country_init' );	
?>