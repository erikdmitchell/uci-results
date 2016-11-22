<?php
function uci_results_upgrade_0_2_0() {
	global $wpdb;
	
	$db_version='0.0.7';
	
	// setup tables //
	//$wpdb->hide_errors();
	$wpdb->uci_results_races=$wpdb->prefix.'uci_curl_races';
	$wpdb->uci_results_results=$wpdb->prefix.'uci_curl_results';
	$wpdb->uci_results_riders=$wpdb->prefix.'uci_curl_riders';
	$wpdb->uci_results_rider_rankings=$wpdb->prefix.'uci_curl_rider_rankings';
	$wpdb->uci_results_related_races=$wpdb->prefix.'uci_curl_related_races';
	$wpdb->uci_results_series=$wpdb->prefix.'uci_curl_series';
	$wpdb->uci_results_series_overall=$wpdb->prefix.'uci_results_series_overall';
	
	
	
	// build in hardcore migration //
	
	// remove tables //
	$remove_tables_sql_query="DROP TABLE IF EXISTS $wpdb->uci_results_races, $wpdb->uci_results_results, $wpdb->uci_results_riders, $wpdb->uci_results_series;";
	
	$wpdb->query($remove_tables_sql_query);
	
	return $db_version;
}	
?>