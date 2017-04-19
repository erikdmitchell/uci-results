<?php
function rest_prepare_races($response, $post, $request) {
	$response->data['country']['rendered']=uci_get_first_term($post->ID, 'country');
	$response->data['race_class']['rendered']=uci_get_first_term($post->ID, 'race_class');
	$response->data['season']['rendered']=uci_get_first_term($post->ID, 'season');
	$response->data['series']['rendered']=uci_get_first_term($post->ID, 'series');
	$response->data['race_date']=get_post_meta($post->ID, '_race_date', true);
	$response->data['results']=uci_results_get_race_results($post->ID, 'object');
	
	return $response;
}
add_filter('rest_prepare_races', 'rest_prepare_races', 10, 3);

function rest_prepare_riders($response, $post, $request) {
print_r($request);
/*
	$response->data['country']['rendered']=uci_get_first_term($post->ID, 'country');
	$response->data['race_class']['rendered']=uci_get_first_term($post->ID, 'race_class');
	$response->data['season']['rendered']=uci_get_first_term($post->ID, 'season');
	$response->data['series']['rendered']=uci_get_first_term($post->ID, 'series');
	$response->data['race_date']=get_post_meta($post->ID, '_race_date', true);
	$response->data['results']=uci_results_get_race_results($post->ID, 'object');
*/
	
	return $response;
}
//add_filter('rest_prepare_riders', 'rest_prepare_riders', 10, 3);

function rest_riders_collection_params($query_params, $post_type) {
	echo '<pre>';
	print_r($query_params);
	echo '</pre>';
}
//add_filter('rest_riders_collection_params', 'rest_riders_collection_params', 10, 2);

function uci_posts_item_args($item_args, $post_type, $schema) {	
	switch ($post_type) :
		case 'riders' :
			$item_args['foo']=array(
				'description' => 'description',
				'type' => 'string',
				'sanitize_callback' => '',
				'validate_callback' => '',
				'default' => 1,
			);
			break;
    endswitch;

	return $item_args;
}
add_filter('uci_posts_item_args', 'uci_posts_item_args', 10, 3);
?>