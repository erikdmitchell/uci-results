<?php
function races_init() {
	register_post_type( 'races', array(
		'labels'            => array(
			'name'                => __( 'Races', 'YOUR-TEXTDOMAIN' ),
			'singular_name'       => __( 'Races', 'YOUR-TEXTDOMAIN' ),
			'all_items'           => __( 'All Races', 'YOUR-TEXTDOMAIN' ),
			'new_item'            => __( 'New races', 'YOUR-TEXTDOMAIN' ),
			'add_new'             => __( 'Add New', 'YOUR-TEXTDOMAIN' ),
			'add_new_item'        => __( 'Add New races', 'YOUR-TEXTDOMAIN' ),
			'edit_item'           => __( 'Edit races', 'YOUR-TEXTDOMAIN' ),
			'view_item'           => __( 'View races', 'YOUR-TEXTDOMAIN' ),
			'search_items'        => __( 'Search races', 'YOUR-TEXTDOMAIN' ),
			'not_found'           => __( 'No races found', 'YOUR-TEXTDOMAIN' ),
			'not_found_in_trash'  => __( 'No races found in trash', 'YOUR-TEXTDOMAIN' ),
			'parent_item_colon'   => __( 'Parent races', 'YOUR-TEXTDOMAIN' ),
			'menu_name'           => __( 'Races', 'YOUR-TEXTDOMAIN' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_nav_menus' => false,
		'show_in_menu' 		=> 'uci-results-api',
		'supports'          => array( 'title' ),
		'has_archive'       => true,
		'rewrite'           => true,
		'query_var'         => true,
		'menu_icon'         => 'dashicons-admin-post',
		'show_in_rest'      => true,
		'rest_base'         => 'races',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'races_init' );

function races_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['races'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Races updated. <a target="_blank" href="%s">View races</a>', 'YOUR-TEXTDOMAIN'), esc_url( $permalink ) ),
		2 => __('Custom field updated.', 'YOUR-TEXTDOMAIN'),
		3 => __('Custom field deleted.', 'YOUR-TEXTDOMAIN'),
		4 => __('Races updated.', 'YOUR-TEXTDOMAIN'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Races restored to revision from %s', 'YOUR-TEXTDOMAIN'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Races published. <a href="%s">View races</a>', 'YOUR-TEXTDOMAIN'), esc_url( $permalink ) ),
		7 => __('Races saved.', 'YOUR-TEXTDOMAIN'),
		8 => sprintf( __('Races submitted. <a target="_blank" href="%s">Preview races</a>', 'YOUR-TEXTDOMAIN'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		9 => sprintf( __('Races scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview races</a>', 'YOUR-TEXTDOMAIN'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		10 => sprintf( __('Races draft updated. <a target="_blank" href="%s">Preview races</a>', 'YOUR-TEXTDOMAIN'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'races_updated_messages' );	
?>