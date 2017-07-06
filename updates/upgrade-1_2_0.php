<?php
/**
 * upgrade_1_2_0_db function.
 * 
 * @access public
 * @return void
 */
function upgrade_1_2_0_db() {
	// convert _race_date to _race_start/_race_end - for stage race compatability //
	global $wpdb;

	$db_version='1.2.0';
	
	$race_dates=$wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key = '_race_date'");
	
	foreach ($race_dates as $race_date) :
		update_post_meta($race_date->post_id, '_race_start', $race_date->meta_value);
		update_post_meta($race_date->post_id, '_race_end', $race_date->meta_value);
	endforeach;
	
	return $db_version;
}
?>