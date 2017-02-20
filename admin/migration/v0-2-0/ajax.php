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

function ajax_uci_results_migrate_related_races() {
	//uci_results_migrate_related_races();
	
	echo json_encode(array(
		'step' => 2,
		'success' => true,
		'percent' => 20
	));
	
	wp_die();
}
add_action('wp_ajax_migrate_related_races', 'ajax_uci_results_migrate_related_races');

function ajax_uci_results_migrate_riders() {
	//uci_results_migrate_related_races();
	
	echo json_encode(array(
		'step' => 3,
		'success' => true,
		'percent' => 30
	));
	
	wp_die();
}
add_action('wp_ajax_migrate_riders', 'ajax_uci_results_migrate_riders');

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

/**
 * uci_results_migrate_related_races function.
 * 
 * @access public
 * @return void
 */
function uci_results_migrate_related_races() {
	global $wpdb;
	
	$db_related_races=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_related_races");

	foreach ($db_related_races as $related_race_row) :
		$related_race_id=$related_race_row->id;
		$related_races=explode(',', $related_race_row->race_ids);

		foreach ($related_races as $race_id) :
			$insert=array(
				'race_id' => $race_id,
				'related_race_id' => $related_race_id,
			);		
			$wpdb->insert($wpdb->uci_results_related_races, $insert);
		endforeach;		
	endforeach;
	
	// remove old entries //
	$wpdb->delete($wpdb->uci_results_related_races, array('race_id' => 0));
	
	// remove duplicates of new entries //
	$wpdb->query("
		DELETE FROM $wpdb->uci_results_related_races USING $wpdb->uci_results_related_races, $wpdb->uci_results_related_races rr1
		WHERE $wpdb->uci_results_related_races.id > rr1.id AND $wpdb->uci_results_related_races.race_id = rr1.race_id AND $wpdb->uci_results_related_races.related_race_id = rr1.related_race_id
	");  
}

/**
 * uci_results_migrate_riders function.
 * 
 * @access public
 * @return void
 */
function uci_results_migrate_riders() {
	global $wpdb;
	
	$db_riders=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_riders");

	foreach ($db_riders as $db_rider) :
		$rider=get_page_by_title($db_rider->name, OBJECT, 'riders');

		if ($rider === null) :
			$arr=array(
				'post_title' => $db_rider->name,
				'post_content' => '',
				'post_status' => 'publish',	
				'post_type' => 'riders',
				'post_name' => $db_rider->slug,
			);
			
			$rider_id=wp_insert_post($arr);
			
			if (!is_wp_error($rider_id)) :
				add_post_meta($rider_id, '_rider_twitter', $db_rider->twitter);
				wp_set_object_terms($rider_id, $db_rider->nat, 'country', false);
			endif;
		endif;
	endforeach;	
}
?>