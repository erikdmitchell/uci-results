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
	//uci_results_migrate_related_races(); // fin
	uci_results_migrate_riders();
	//uci_results_migrate_races();
	//$related_race_id=uci_results_convert_related_race_id($db_race->id, $db_race->related_races_id);
		
	// remove tables //
	$remove_tables_sql_query="DROP TABLE IF EXISTS $wpdb->uci_results_races, $wpdb->uci_results_results, $wpdb->uci_results_riders, $wpdb->uci_results_series;";
	
	//$wpdb->query($remove_tables_sql_query);
	
	// remove race ids cold from related races
	
	return $db_version;
}

function uci_results_migrate_races() {
	global $wpdb;
	
	$db_races=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_races LIMIT 3");
	
	if (!count($db_races))
		return;
		
	foreach ($db_races as $db_race) :
		$old_id=$db_race->id;
		$series=uci_results_convert_series($db_race->series_id);
		$race=get_page_by_path($db_race->code, OBJECT, 'races');
		$race_data=array(
			'post_title' => $db_race->event,
			'post_content' => '',
			'post_status' => 'publish',	
			'post_type' => 'races',
			'post_name' => $db_race->code,		
		);
$post_id=1;
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
		
		//wp_set_object_terms($post_id, $db_race->nat, 'country', false);
		//wp_set_object_terms($post_id, $db_race->class, 'race_class', false);
		//wp_set_object_terms($post_id, $db_race->season, 'season', false);
		//wp_set_object_terms($post_id, $series, 'series', false);
		
		// update meta //
		//update_post_meta($post_id, '_race_date', $db_race->date);
		//update_post_meta($post_id, '_race_winner', $db_race->winner);
		//update_post_meta($post_id, '_race_week', $db_race->week);
		//update_post_meta($post_id, '_race_link', $db_race->link);	
		//update_post_meta($post_id, '_race_twitter', $db_race->twitter);	
	
		uci_results_migrate_results($post_id, $old_id);
	endforeach;
	
}

function uci_results_convert_related_race_id($old_id=0, $old_related_races_id=0) {
	global $wpdb;
	
	if (!$old_id || !$old_related_races_id)
		return;
		
	$related_race_row=$wpdb->get_row("SELECT * FROM $wpdb->uci_results_related_races WHERE related_race_id = $old_related_races_id AND race_id = $old_id");
		
	print_r($related_race_row);
// race id is old, related race id, get post slug then get post id and convert

//update_post_meta($post_id, '_race_related', NULL); // there needs to be a conversion here
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

function uci_results_migrate_results($post_id=0, $old_id=0) {
	global $wpdb;
	
	if (!$post_id || !$old_id)
		return false;
	
	$race_results=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_results WHERE race_id = $old_id");
	
	if (!count($race_results))
		return;
			
	foreach ($race_results as $result) :
		$rider=get_page_by_title($result->name, OBJECT, 'riders');

		// check if we have a rider id, otherwise create one //
		if ($rider===null || empty($rider->ID)) :
			$rider_insert=array(
				'post_title' => $result->name,
				'post_content' => '',
				'post_status' => 'publish',	
				'post_type' => 'riders',
				'post_name' => sanitize_title_with_dashes($result->name)
			);
			//$rider_id=wp_insert_post($rider_insert);
			//wp_set_object_terms($rider_id, $result->nat, 'country', false);
		else :
			$rider_id=$rider->ID;
		endif;

		if (!isset($result->par) || empty($result->par) || is_null($result->par)) :
			$par=0;
		else :
			$par=$result->par;
		endif;

		if (!isset($result->pcr) || empty($result->pcr) || is_null($result->pcr)) :
			$pcr=0;
		else :
			$pcr=$result->pcr;
		endif;

		$meta_value=array(
			'place' => $result->place,
			'name' => $result->name,
			'nat' => $result->nat,
			'age' => $result->age,
			'result' => $result->result,
			'par' => $par,
			'pcr' => $pcr,
		);					
		//update_post_meta($race_id, "_rider_$rider_id", $meta_value);
	endforeach;	
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
	
	// remove old entries //
	$wpdb->delete($wpdb->uci_results_related_races, array('race_id' => 0));
	
	// remove duplicates of new entries //
	$wpdb->query("
		DELETE FROM $wpdb->uci_results_related_races USING $wpdb->uci_results_related_races, $wpdb->uci_results_related_races rr1
		WHERE $wpdb->uci_results_related_races.id > rr1.id AND $wpdb->uci_results_related_races.race_id = rr1.race_id AND $wpdb->uci_results_related_races.related_race_id = rr1.related_race_id
	");  
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