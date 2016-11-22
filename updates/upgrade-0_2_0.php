<?php
function uci_results_upgrade_0_2_0() {
	global $wpdb;
	
	$db_version='0.0.7';
	
	// setup tables //
	$wpdb->hide_errors();
	$wpdb->uci_results_races=$wpdb->prefix.'uci_curl_races';
	$wpdb->uci_results_results=$wpdb->prefix.'uci_curl_results';
	$wpdb->uci_results_riders=$wpdb->prefix.'uci_curl_riders';
	$wpdb->uci_results_rider_rankings=$wpdb->prefix.'uci_curl_rider_rankings';
	$wpdb->uci_results_related_races=$wpdb->prefix.'uci_curl_related_races';
	$wpdb->uci_results_series=$wpdb->prefix.'uci_curl_series';
	$wpdb->uci_results_series_overall=$wpdb->prefix.'uci_results_series_overall';
	
	// build in hardcore migration //
	uci_results_migrate_series();
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
	
	// remove tables //
	$remove_tables_sql_query="DROP TABLE IF EXISTS $wpdb->uci_results_races, $wpdb->uci_results_results, $wpdb->uci_results_riders, $wpdb->uci_results_series;";
	
	//$wpdb->query($remove_tables_sql_query);
	
	return $db_version;
}

function uci_results_migrate_series() {
	global $wpdb;
	
	$db_series=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_series");
	
	if (!count($db_series))
		return;
		
	foreach ($db_series as $series)	:
		if (!term_exists($series->name, 'series')) :	
			$inserted=wp_insert_term($series->name, 'series');		
		endif;
			
	endforeach;
}
?>