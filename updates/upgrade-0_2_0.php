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
	//uci_results_migrate_series(); // fin
	//uci_results_migrate_related_races();
	
	//uci_results_migrate_races();
	
	//uci_results_migrate_results(); INCLUDED IN RACES
	//uci_results_migrate_riders();
	
	
	// remove tables //
	$remove_tables_sql_query="DROP TABLE IF EXISTS $wpdb->uci_results_races, $wpdb->uci_results_results, $wpdb->uci_results_riders, $wpdb->uci_results_series;";
	
	//$wpdb->query($remove_tables_sql_query);
	
	// remove race ids cold from related races
	
	return $db_version;
}

function uci_results_migrate_races() {
	global $wpdb;
	
	$db_races=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_races");
	
	if (!count($db_races))
		return;
		
	foreach ($db_races as $db_race) :
		$old_id=$db_race->id;
		$old_related_races_id=$db_race->related_races_id;
		$race=get_page_by_path($db_race->code, OBJECT, 'races');
		$race_data=array(
			'post_title' => $db_race->event,
			'post_content' => '',
			'post_status' => 'publish',	
			'post_type' => 'races',
			'post_name' => $db_race->code,		
		);

		// if race is null, add it, else update it //
		if ($race === null) :
			//$post_id=wp_insert_post($race_data);
		else :
			$race_data['ID']=$race->ID;
		 	//$post_id=wp_update_post($race_data);
		endif;		
		
		// check for error //
		//if (is_wp_error($post_id))
			//return false;
			
		// update taxonomies //
		//$series=uci_results_convert_series($db_race->series_id);
		//wp_set_object_terms($post_id, $db_race->nat, 'country', false);
		//wp_set_object_terms($post_id, $db_race->class, 'race_class', false);
		//wp_set_object_terms($post_id, $db_race->season, 'season', false);
		//wp_set_object_terms($post_id, $series, 'series', false);
		
		// update meta //
		//update_post_meta($post_id, '_race_date', $db_race->date);
		//update_post_meta($post_id, '_race_winner', $db_race->winner);
		//update_post_meta($post_id, '_race_week', $db_race->week);
		//update_post_meta($post_id, '_race_link', $db_race->link);	
		//update_post_meta($post_id, '_race_related', NULL); // there needs to be a conversion here
		//update_post_meta($post_id, '_race_twitter', $db_race->twitter);	
	
		//uci_results_migrate_results();
	endforeach;
	
}

/**
 * uci_results_convert_series function.
 * 
 * @access public
 * @param int $old_id (default: 0)
 * @return void
 */
function uci_results_convert_series($old_id=0) {
	global $wpdb;
	
	if (!$old_id)
		return;
		
	$series=$wpdb->get_var("SELECT name FROM $wpdb->uci_results_series WHERE id = $old_id");
	
	return $series;
}

function uci_results_migrate_results() {
	
}
	
function uci_results_migrate_riders() {
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
	
	$wpdb->delete($wpdb->uci_results_related_races, array('race_id' => 0));
}

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
}
?>