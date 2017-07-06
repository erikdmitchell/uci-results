<?php
/**
 * upgrade_1_2_0_db function.
 * 
 * @access public
 * @return void
 */
function upgrade_1_2_0_db() {
	global $wpdb;

	$db_version='1.2.0';
	
	upgrade_1_2_0_update_race_dates(); // convert _race_date to _race_start/_race_end - for stage race compatability
	upgrade_1_2_0_update_rider_results(); // migrate results to "new" format
	
	return $db_version;
}

/**
 * upgrade_1_2_0_update_race_dates function.
 * 
 * @access public
 * @return void
 */
function upgrade_1_2_0_update_race_dates() {
	global $wpdb;
	
	$race_dates=$wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key = '_race_date'");
	
	foreach ($race_dates as $race_date) :
		update_post_meta($race_date->post_id, '_race_start', $race_date->meta_value);
		update_post_meta($race_date->post_id, '_race_end', $race_date->meta_value);
	endforeach;	
}

/**
 * upgrade_1_2_0_update_rider_results function.
 * 
 * @access public
 * @return void
 */
function upgrade_1_2_0_update_rider_results() {
	global $wpdb;

	$rider_results=$wpdb->get_results("SELECT * FROM wp_postmeta WHERE meta_key LIKE '_rider_%'");

	foreach ($rider_results as $rider_result) :
		if (!is_serialized($rider_result->meta_value))		
			continue;
	
		$result_arr=unserialize($rider_result->meta_value);	
		$rider_id=str_replace('_rider_', '', $rider_result->meta_key);

		update_post_meta($rider_result->post_id, '_rider_'.$rider_id.'_result_place', $result_arr['place']);
		update_post_meta($rider_result->post_id, '_rider_'.$rider_id.'_result_time', $result_arr['result']);
		update_post_meta($rider_result->post_id, '_rider_'.$rider_id.'_result_par', $result_arr['par']);
		update_post_meta($rider_result->post_id, '_rider_'.$rider_id.'_result_pcr', $result_arr['pcr']);
		
		delete_post_meta($rider_result->post_id, $rider_result->meta_key);
	endforeach;		
}
?>