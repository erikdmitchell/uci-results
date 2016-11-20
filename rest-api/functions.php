<?php

function uci_results_get_race_rider_country($id=0) {
	$api_url=site_url('/wp-json/wp/v2/');
	$response=wp_remote_get($api_url.'country/'.$id);
	
	if (is_wp_error($response))
		return false;

	$posts=json_decode(wp_remote_retrieve_body($response));

	if (empty($posts))
		return false;	

	return $posts;
}	

function uci_results_get_race_class($id=0) {
	$api_url=site_url('/wp-json/wp/v2/');
	$response=wp_remote_get($api_url.'race_class/'.$id);
	
	if (is_wp_error($response))
		return false;

	$posts=json_decode(wp_remote_retrieve_body($response));

	if (empty($posts))
		return false;	

	return $posts;
}

function uci_results_get_race_series($id=0) {
	$api_url=site_url('/wp-json/wp/v2/');
	$response=wp_remote_get($api_url.'series/'.$id);
	
	if (is_wp_error($response))
		return false;

	$posts=json_decode(wp_remote_retrieve_body($response));

	if (empty($posts))
		return false;	

	return $posts;
}

function uci_results_get_race_season($id=0) {
	$api_url=site_url('/wp-json/wp/v2/');
	$response=wp_remote_get($api_url.'season/'.$id);
	
	if (is_wp_error($response))
		return false;

	$posts=json_decode(wp_remote_retrieve_body($response));

	if (empty($posts))
		return false;	

	return $posts;
}	
?>