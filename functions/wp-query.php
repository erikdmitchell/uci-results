<?php

/**
 * riders_the_posts_details function.
 * 
 * @access public
 * @param mixed $posts
 * @param mixed $query
 * @return void
 */
function riders_the_posts_details($posts, $query) {
	global $uci_riders;
	
	if ($query->query_vars['post_type']!='riders')
		return $posts;

	foreach ($posts as $post) :
		$post->nat=uci_get_first_term($post->ID, 'country');
		$post->tiwtter=get_post_meta($post->ID, '_rider_twitter', true);
		
		if ($query->get('ranking')) :
			$post->rank=$uci_riders->get_rider_rank($post->ID);
		endif;
	endforeach;

	return $posts;
}
add_action('the_posts', 'riders_the_posts_details', 10, 2);

/**
 * races_the_posts_details function.
 * 
 * @access public
 * @param mixed $posts
 * @param mixed $query
 * @return void
 */
function races_the_posts_details($posts, $query) {
	global $uci_riders;
	
	if ($query->query_vars['post_type']!='races')
		return $posts;

	foreach ($posts as $post) :
		$post=uci_race_details($post);
	endforeach;

	return $posts;
}
add_action('the_posts', 'races_the_posts_details', 10, 2);	
?>