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
		$wpdb->uci_results_riders => array(
			'id'
		),
		$wpdb->uci_results_rider_rankings => array(
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
?>