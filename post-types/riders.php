<?php
function riders_init() {
	register_post_type( 'riders', array(
		'labels'            => array(
			'name'                => __( 'Riders', 'uci-results' ),
			'singular_name'       => __( 'Riders', 'uci-results' ),
			'all_items'           => __( 'All Riders', 'uci-results' ),
			'new_item'            => __( 'New riders', 'uci-results' ),
			'add_new'             => __( 'Add New', 'uci-results' ),
			'add_new_item'        => __( 'Add New riders', 'uci-results' ),
			'edit_item'           => __( 'Edit riders', 'uci-results' ),
			'view_item'           => __( 'View riders', 'uci-results' ),
			'search_items'        => __( 'Search riders', 'uci-results' ),
			'not_found'           => __( 'No riders found', 'uci-results' ),
			'not_found_in_trash'  => __( 'No riders found in trash', 'uci-results' ),
			'parent_item_colon'   => __( 'Parent riders', 'uci-results' ),
			'menu_name'           => __( 'Riders', 'uci-results' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_nav_menus' => false,
		'show_in_menu' 		=> false,
		'supports'          => array('title'),
		'has_archive'       => false,
		'rewrite'           => true,
		'query_var'         => true,
		'menu_icon'         => 'dashicons-admin-post',
		'show_in_rest'      => true,
		'rest_base'         => 'riders',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'riders_init' );

function riders_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['riders'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Riders updated. <a target="_blank" href="%s">View riders</a>', 'uci-results'), esc_url( $permalink ) ),
		2 => __('Custom field updated.', 'uci-results'),
		3 => __('Custom field deleted.', 'uci-results'),
		4 => __('Riders updated.', 'uci-results'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Riders restored to revision from %s', 'uci-results'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Riders published. <a href="%s">View riders</a>', 'uci-results'), esc_url( $permalink ) ),
		7 => __('Riders saved.', 'uci-results'),
		8 => sprintf( __('Riders submitted. <a target="_blank" href="%s">Preview riders</a>', 'uci-results'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		9 => sprintf( __('Riders scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview riders</a>', 'uci-results'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		10 => sprintf( __('Riders draft updated. <a target="_blank" href="%s">Preview riders</a>', 'uci-results'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'riders_updated_messages' );	
?>