<?php
function riders_init() {
	register_post_type( 'riders', array(
		'labels'            => array(
			'name'                => __( 'Riders', 'YOUR-TEXTDOMAIN' ),
			'singular_name'       => __( 'Riders', 'YOUR-TEXTDOMAIN' ),
			'all_items'           => __( 'All Riders', 'YOUR-TEXTDOMAIN' ),
			'new_item'            => __( 'New riders', 'YOUR-TEXTDOMAIN' ),
			'add_new'             => __( 'Add New', 'YOUR-TEXTDOMAIN' ),
			'add_new_item'        => __( 'Add New riders', 'YOUR-TEXTDOMAIN' ),
			'edit_item'           => __( 'Edit riders', 'YOUR-TEXTDOMAIN' ),
			'view_item'           => __( 'View riders', 'YOUR-TEXTDOMAIN' ),
			'search_items'        => __( 'Search riders', 'YOUR-TEXTDOMAIN' ),
			'not_found'           => __( 'No riders found', 'YOUR-TEXTDOMAIN' ),
			'not_found_in_trash'  => __( 'No riders found in trash', 'YOUR-TEXTDOMAIN' ),
			'parent_item_colon'   => __( 'Parent riders', 'YOUR-TEXTDOMAIN' ),
			'menu_name'           => __( 'Riders', 'YOUR-TEXTDOMAIN' ),
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
		1 => sprintf( __('Riders updated. <a target="_blank" href="%s">View riders</a>', 'YOUR-TEXTDOMAIN'), esc_url( $permalink ) ),
		2 => __('Custom field updated.', 'YOUR-TEXTDOMAIN'),
		3 => __('Custom field deleted.', 'YOUR-TEXTDOMAIN'),
		4 => __('Riders updated.', 'YOUR-TEXTDOMAIN'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Riders restored to revision from %s', 'YOUR-TEXTDOMAIN'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Riders published. <a href="%s">View riders</a>', 'YOUR-TEXTDOMAIN'), esc_url( $permalink ) ),
		7 => __('Riders saved.', 'YOUR-TEXTDOMAIN'),
		8 => sprintf( __('Riders submitted. <a target="_blank" href="%s">Preview riders</a>', 'YOUR-TEXTDOMAIN'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		9 => sprintf( __('Riders scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview riders</a>', 'YOUR-TEXTDOMAIN'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		10 => sprintf( __('Riders draft updated. <a target="_blank" href="%s">Preview riders</a>', 'YOUR-TEXTDOMAIN'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'riders_updated_messages' );	
?>