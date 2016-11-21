<?php
function races_init() {
	register_post_type( 'races', array(
		'labels'            => array(
			'name'                => __( 'Races', 'uci-results' ),
			'singular_name'       => __( 'Races', 'uci-results' ),
			'all_items'           => __( 'All Races', 'uci-results' ),
			'new_item'            => __( 'New races', 'uci-results' ),
			'add_new'             => __( 'Add New', 'uci-results' ),
			'add_new_item'        => __( 'Add New races', 'uci-results' ),
			'edit_item'           => __( 'Edit races', 'uci-results' ),
			'view_item'           => __( 'View races', 'uci-results' ),
			'search_items'        => __( 'Search races', 'uci-results' ),
			'not_found'           => __( 'No races found', 'uci-results' ),
			'not_found_in_trash'  => __( 'No races found in trash', 'uci-results' ),
			'parent_item_colon'   => __( 'Parent races', 'uci-results' ),
			'menu_name'           => __( 'Races', 'uci-results' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_nav_menus' => false,
		//'show_in_menu' 		=> 'uci-results-api',
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
		1 => sprintf( __('Races updated. <a target="_blank" href="%s">View races</a>', 'uci-results'), esc_url( $permalink ) ),
		2 => __('Custom field updated.', 'uci-results'),
		3 => __('Custom field deleted.', 'uci-results'),
		4 => __('Races updated.', 'uci-results'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Races restored to revision from %s', 'uci-results'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Races published. <a href="%s">View races</a>', 'uci-results'), esc_url( $permalink ) ),
		7 => __('Races saved.', 'uci-results'),
		8 => sprintf( __('Races submitted. <a target="_blank" href="%s">Preview races</a>', 'uci-results'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		9 => sprintf( __('Races scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview races</a>', 'uci-results'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		10 => sprintf( __('Races draft updated. <a target="_blank" href="%s">Preview races</a>', 'uci-results'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'races_updated_messages' );	
?>