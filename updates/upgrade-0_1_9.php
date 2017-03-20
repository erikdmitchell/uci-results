<?php

/**
 * uci_results_upgrade_0_1_9 function.
 * 
 * @access public
 * @return void
 */
function uci_results_upgrade_0_1_9() {
	global $wpdb;
	
	$db_version='0.1.9';
	
	// we need to retro activelly trim our race names //
	$wpdb->query("UPDATE wp_uci_curl_races SET event = LTRIM(RTRIM(event))");
	
	return $db_version;
}
?>