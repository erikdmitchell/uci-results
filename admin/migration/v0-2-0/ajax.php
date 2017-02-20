<?php
function ajax_uci_results_migrate_series() {
	//uci_results_migrate_series();
	
	echo json_encode(array(
		'step' => 1,
		'success' => true,
		'percent' => 10
	));
	
	wp_die();
}
add_action('wp_ajax_migrate_series', 'ajax_uci_results_migrate_series');


/**
 * uci_results_migrate_series function.
 * 
 * @access public
 * @return void
 */
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
	
	return true;
}
?>