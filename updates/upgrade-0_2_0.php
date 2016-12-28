<?php

/**
 * uci_results_upgrade_0_2_0 function.
 * 
 * @access public
 * @return void
 */
function uci_results_upgrade_0_2_0() {
	global $wpdb;
	
	$db_version='0.2.0';
	
	// db indexs //
	$indexs=array(	
		$wpdb->uci_curl_riders => array(
			'id'
		),
		$wpdb->uci_curl_rider_rankings => array(
			'rider_id',
			'season',
			'week'
		),
	);

	// cycle through and setup queries //
	foreach ($indexs as $table => $index_cols) :
		foreach ($index_cols as $col) :
			if (!uci_results_index_exists($table, $col))
				uci_results_create_index($table, $col);
		endforeach;
	endforeach;
	
	return $db_version;
}

/**
 * uci_results_index_exists function.
 * 
 * @access public
 * @param string $table (default: '')
 * @param string $id (default: '')
 * @return void
 */
function uci_results_index_exists($table='', $id='') {
	global $wpdb;
	
	if (empty($id) || empty($table))
		return false;
		
	$key_name=$table.'_'.$id;
	$index=$wpdb->get_row("SHOW INDEX FROM $table WHERE KEY_NAME = '$key_name'");
	
	if ($index===null)
		return false;
		
	return true;
}

/**
 * uci_results_create_index function.
 * 
 * @access public
 * @param string $table (default: '')
 * @param string $id (default: '')
 * @return void
 */
function uci_results_create_index($table='', $id='') {
	global $wpdb;
	
	if (empty($id) || empty($table))
		return false;	
		
	$key_name=$table.'_'.$id;
	$wpdb->query("CREATE INDEX $key_name ON $table($id)");

	return;
}
?>
?>