<?php
/**
 * uci_results_upgrade_0_2_1 function.
 * 
 * @access public
 * @return void
 */
function uci_results_upgrade_0_2_1() {
	global $wpdb;
	
	$db_version='0.2.1';
	
	// db indexs //
	$indexs=array(	
		$wpdb->uci_results_results => array(
			'rider_id',
			'race_id'
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