<?php
define('UCI_RESULTS_API_PATH', plugin_dir_path(__FILE__));
define('UCI_RESULTS_API_URL', plugin_dir_url(__FILE__));	

include_once(UCI_RESULTS_API_PATH.'riders-post-type.php');
include_once(UCI_RESULTS_API_PATH.'races-post-type.php');
include_once(UCI_RESULTS_API_PATH.'riders-metabox.php');
include_once(UCI_RESULTS_API_PATH.'races-metabox.php');
include_once(UCI_RESULTS_API_PATH.'taxonomies.php');

// SUDO MIGRATION SCRIPT //
/*
global $wp_uci_curl_riders;

foreach ($wp_uci_curl_riders as $rider) :
	$arr=array(
		'post_title' => $rider['name'],
		'post_content' => '',
		'post_status' => 'publish',	
		'post_type' => 'riders',
		'post_name' => $rider['slug'],
	);
	//$post_id=wp_insert_post($arr);
	//add_post_meta($post_id, '_rider_twitter', $rider['twitter']);
	//wp_set_object_terms($post_id, $rider['nat'], 'country', false);
endforeach;
*/	
?>