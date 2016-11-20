<?php

function results_init() {
	register_post_type( 'results', array(
		'labels'            => array(
			'name'                => __( 'Results', 'uci-results' ),
			'singular_name'       => __( 'Results', 'uci-results' ),
			'all_items'           => __( 'All Results', 'uci-results' ),
			'new_item'            => __( 'New results', 'uci-results' ),
			'add_new'             => __( 'Add New', 'uci-results' ),
			'add_new_item'        => __( 'Add New results', 'uci-results' ),
			'edit_item'           => __( 'Edit results', 'uci-results' ),
			'view_item'           => __( 'View results', 'uci-results' ),
			'search_items'        => __( 'Search results', 'uci-results' ),
			'not_found'           => __( 'No results found', 'uci-results' ),
			'not_found_in_trash'  => __( 'No results found in trash', 'uci-results' ),
			'parent_item_colon'   => __( 'Parent results', 'uci-results' ),
			'menu_name'           => __( 'Results', 'uci-results' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'supports'          => array( 'title', 'editor' ),
		'has_archive'       => true,
		'rewrite'           => true,
		'query_var'         => true,
		'menu_icon'         => 'dashicons-admin-post',
		'show_in_rest'      => true,
		'rest_base'         => 'results',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'results_init' );

function results_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['results'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Results updated. <a target="_blank" href="%s">View results</a>', 'uci-results'), esc_url( $permalink ) ),
		2 => __('Custom field updated.', 'uci-results'),
		3 => __('Custom field deleted.', 'uci-results'),
		4 => __('Results updated.', 'uci-results'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Results restored to revision from %s', 'uci-results'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Results published. <a href="%s">View results</a>', 'uci-results'), esc_url( $permalink ) ),
		7 => __('Results saved.', 'uci-results'),
		8 => sprintf( __('Results submitted. <a target="_blank" href="%s">Preview results</a>', 'uci-results'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		9 => sprintf( __('Results scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview results</a>', 'uci-results'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		10 => sprintf( __('Results draft updated. <a target="_blank" href="%s">Preview results</a>', 'uci-results'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'results_updated_messages' );
?>