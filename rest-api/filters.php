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
	global $uci_riders;
	
	// append results if need be //
	if ($request['results'])
		$response->data['results']=uci_results_get_rider_results(array('rider_id' => $post->ID));
		
	// append last race results if need be //
	if ($request['lastrace'])
		$response->data['last_race']=$uci_riders->rider_last_race_result($post->ID);

	// append stats if need be //
	if ($request['stats'])
		$response->data['stats']=new UCIRiderStats($post->ID);
		
	// append ranking if need be //
	$response->data['ranking']=$uci_riders->get_rider_rank($post->ID);
		
	// append acutal country //
	if (isset($response->data['country'][0])) :
		$country=get_term_by('id', $response->data['country'][0], 'country');
		$country_name=$country->name;
	else :
		$country_name='';
	endif;
	
	$response->data['country']['rendered']=$country_name;
	
	return $response;
}
add_filter('rest_prepare_riders', 'rest_prepare_riders', 10, 3);

function uci_posts_item_args($item_args, $post_type, $schema) {	
	switch ($post_type) :
		case 'riders' :
			$item_args['results']=array(
				'description' => 'Riders results',
				'type' => 'boolean',
				'sanitize_callback' => '',
				'validate_callback' => '',
				'default' => 0,
			);

			$item_args['lastrace']=array(
				'description' => 'Riders last race results',
				'type' => 'boolean',
				'sanitize_callback' => '',
				'validate_callback' => '',
				'default' => 0,
			);
			
			$item_args['stats']=array(
				'description' => 'Riders stats',
				'type' => 'boolean',
				'sanitize_callback' => '',
				'validate_callback' => '',
				'default' => 0,
			);			
			break;
    endswitch;

	return $item_args;
}
add_filter('uci_posts_item_args', 'uci_posts_item_args', 10, 3);
?>