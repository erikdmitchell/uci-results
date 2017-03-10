<?php
///////// RIDERS

/**
 * uci_results_get_rider_results function.
 * 
 * @access public
 * @param int $rider_id (default: 0)
 * @return void
 */
function uci_results_get_rider_results($rider_id=0) {
	if (!$rider_id)
		return false;
		
	$results=array();
		
    // get race ids via meta //
	$results_args_meta = array(
		'posts_per_page' => -1,
		'post_type' => 'races',
		'meta_query' => array(
		    array(
		        'key' => '_rider_'.$rider_id,
		    )
		),
		'fields' => 'ids'
	);
	$race_ids=get_posts($results_args_meta);
	
	foreach ($race_ids as $race_id) :
		$result=get_post_meta($race_id, '_rider_'.$rider_id, true);
		$result['race_id']=$race_id;
		$result['race_name']=get_the_title($race_id);
		
		$results[]=$result;
	endforeach;

	return $results;
}

///////// RACES

/**
 * uci_results_get_race_results function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_results_get_race_results($race_id=0) {
	$post_meta=get_post_meta($race_id);
	$riders=array();
	
	// get only meta (riders); we need //
	foreach ($post_meta as $key => $value) :
		if (strpos($key, '_rider_') !== false) :
			if (isset($value[0])) :
				$riders[]=unserialize($value[0]);
			endif;			
		endif;
	endforeach;
	
	return $riders;
}

/**
 * uci_race_has_results function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_race_has_results($race_id=0) {
	$post_meta=get_post_meta($race_id);
	$keys=array_keys($post_meta);    

	return (int) preg_grep('/_rider_/', $keys);	
}

/**
 * uci_get_race_twitter function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_get_race_twitter($race_id=0) {
	if (empty($race_id))
		return false;

	return get_post_meta($race_id, '_race_twitter', true);
}

/**
 * uci_get_related_races function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_get_related_races($race_id=0) {
	global $wpdb;

	$related_races=array();
	$related_race_id=uci_get_related_race_id($race_id);
	
	if (!$related_race_id)
		return array();
	
	$related_races_ids=uci_get_related_races_ids($race_id);

	if (is_wp_error($related_races_ids) || $related_races_ids===null)
		return false;

	$related_races=get_posts(array(
		'include' => $related_races_ids,
		'post_type' => 'races',
		'orderby' => 'meta_value',
		'meta_key' => '_race_date',
	));
	
	// append some meta //
	foreach ($related_races as $race) :
		$race->race_date=get_post_meta($race->ID, '_race_date', true);
	endforeach;

	return $related_races;
}

/**
 * uci_get_related_races_ids function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_get_related_races_ids($race_id=0) {
	global $wpdb;

	$related_race_id=uci_get_related_race_id($race_id);
	
	if (!$related_race_id)
		return array();
	
	$related_races_ids=$wpdb->get_col("SELECT race_id FROM $wpdb->uci_results_related_races WHERE related_race_id = $related_race_id");

	if (is_wp_error($related_races_ids) || $related_races_ids===null)
		return false;

	return $related_races_ids;
}

/**
 * uci_get_related_race_id function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_get_related_race_id($race_id=0) {
	return get_post_meta($race_id, '_race_related', true);
}

/**
 * uci_get_race_seasons_dropdown function.
 * 
 * @access public
 * @param string $name (default: 'season')
 * @param string $selected (default: '')
 * @return void
 */
function uci_get_race_seasons_dropdown($name='season', $selected='') {
	$html=null;
	$seasons=get_terms( array(
	    'taxonomy' => 'season',
		'hide_empty' => false,
	));

	$html.='<select id="'.$name.'" name="'.$name.'" class="'.$name.'">';
		$html.='<option value="0">-- Select Season --</option>';
			foreach ($seasons as $season) :
				$html.='<option value="'.$season->name.'" '.selected($selected, $season->name, false).'>'.$season->name.'</option>';
			endforeach;
	$html.='</select>';
	
	return $html;
}

/**
 * uci_get_country_dropdown function.
 * 
 * @access public
 * @param string $name (default: 'country')
 * @param string $selected (default: '')
 * @return void
 */
function uci_get_country_dropdown($name='country', $selected='') {
	$html=null;
	$countries=get_terms( array(
	    'taxonomy' => 'country',
		'hide_empty' => false,
	));

	$html.='<select id="'.$name.'" name="'.$name.'" class="'.$name.'">';
		$html.='<option value="0">-- Select Country --</option>';
			foreach ($countries as $country) :
				$html.='<option value="'.$country->slug.'" '.selected($selected, $country->slug, false).'>'.$country->name.'</option>';
			endforeach;
	$html.='</select>';
	
	return $html;
}

/**
 * uci_get_season_weeks function.
 * 
 * @access public
 * @param string $season (default: '')
 * @param string $selected (default: '')
 * @param string $name (default: 'weeks')
 * @return void
 */
function uci_get_season_weeks($season='', $selected='', $name='weeks') {
	global $uci_cross_seasons;	
	
	$html=null;
	$weeks=$uci_cross_seasons->get_season_weeks($season);
	
	if (empty($weeks))
		return;

	$html.='<select id="'.$name.'" name="'.$name.'" class="'.$name.'">';
		$html.='<option value="0">-- Select Season --</option>';
			foreach ($weeks as $week) :
				$html.='<option value="'.$week->week.'" '.selected($selected, $week->week, false).'>'.$week->week.'</option>';
			endforeach;
	$html.='</select>';
	
	return $html;	
}

?>