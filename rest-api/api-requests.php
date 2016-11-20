<?php
global $uci_results_api_url;

$uci_results_api_url=site_url('/wp-json/wp/v2/');

function uci_results_api_call($type='riders', $args='') {
	global $uci_results_api_url;
	
	$default_args=array(
		'per_page' => 15,
		'order' => 'asc',
		'orderby' => 'title',	
	);
	$args=wp_parse_args($args, $default_args);
	
	if (empty($type))
		return false;
	
	$url=add_query_arg($args, $uci_results_api_url.$type);
	$response=wp_remote_get($url);

	if (is_wp_error($response))
		return false;

	$posts=json_decode(wp_remote_retrieve_body($response));

	if (empty($posts))
		return false;
		
	return $posts;  
}
?>