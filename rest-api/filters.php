<?php
	
/**
 * rest_prepare_races function.
 * 
 * @access public
 * @param mixed $response
 * @param mixed $post
 * @param mixed $request
 * @return void
 */
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

/**
 * rest_prepare_riders function.
 * 
 * @access public
 * @param mixed $response
 * @param mixed $post
 * @param mixed $request
 * @return void
 */
function rest_prepare_riders($response, $post, $request) {
	global $uci_riders, $uci_rankings;
	
	// append results if need be //
	if ($request['results']) :
		$args=array('rider_id' => $post->ID);
		
		if (isset($request['start_date']) && isset($request['end_date'])) :
			$args['start_date']=$request['start_date'];
			$args['end_date']=$request['end_date'];			
		endif;
		
		$response->data['results']=uci_results_get_rider_results($args);
	endif;
		
	// append last race results if need be //
	if ($request['lastrace'])
		$response->data['last_race']=$uci_riders->rider_last_race_result($post->ID);

	// append stats if need be //
	if ($request['stats'])
		$response->data['stats']=new UCIRiderStats($post->ID);

	// append recent uci rank if need be //
	if ($request['uci_rank'])
		$response->data['uci_rank']=$uci_rankings->get_rank($post->ID);
		
	// append ranking //
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

/**
 * uci_posts_item_args function.
 * 
 * @access public
 * @param mixed $item_args
 * @param mixed $post_type
 * @param mixed $schema
 * @return void
 */
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