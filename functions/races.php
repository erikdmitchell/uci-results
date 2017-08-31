<?php
/**
 * uci_results_race_has_results function.
 *
 * @access public
 * @param string $code (default: '')
 * @return void
 */
function uci_results_race_has_results($code='') {
	global $wpdb;

	$race=get_page_by_path($code, OBJECT, 'races');

	// no race id, we out //
	if ($race===null)
		return false;

	// do we have results? //
	if (uci_race_has_results($race->ID))
		return true;

	return false;
}

/**
 * uci_get_races function.
 * 
 * @access public
 * @param string $args (default: '')
 * @return void
 */
function uci_get_races($args='') {
	$default_args=array(
		'id' => '',
		'per_page' => -1,
		'orderby' => 'meta_value',
		'meta_key' => '_race_date',
		'order' => 'DESC',
		'results' => false,
		'offset' => '',
	);
	$args=wp_parse_args($args, $default_args);
	
	extract($args);

	$argss=array(
		'posts_per_page' => $per_page,
		'include' => $id,
		'post_type' => 'races',
		//'orderby' => $orderby,
		//'meta_key' => $meta_key,
		//'order' => $order,
		'meta_query'      => array(
			'relation'    => 'OR',
			'_race_date' => array(
				'key'     => '_race_date',
				'compare' => 'EXISTS',
			),
			'_race_start' => array(
				'key'     => '_race_start',
				'compare' => 'EXISTS',
			),
		)
	);

	$races=get_posts($argss);
	global $wpdb;

	foreach ($races as $race) :
		$race=uci_race_details($race);
		
		if ($results)
			$race->results=uci_results_get_race_results($race->ID);
	endforeach;
	
	// check for single race //
	if (count($races)==1)
		$races=$races[0];
	
	return $races;
}

/**
 * uci_race_details function.
 * 
 * @access public
 * @param string $race (default: '')
 * @return void
 */
function uci_race_details($race='') {
	$race->race_date=get_post_meta($race->ID, '_race_date', true);
	$race->nat=uci_race_country($race->ID);
	$race->class=uci_race_class($race->ID);
	$race->season=uci_race_season($race->ID);
	$race->series=uci_race_series($race->ID);	
		
	return $race;
}

/**
 * uci_race_country function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_race_country($race_id=0) {
	$countries=wp_get_post_terms($race_id, 'country', array('fields' => 'names'));

	if (isset($countries[0])) :
		$country=$countries[0];
	else :
		$country='';
	endif;
	
	return $country;
}

/**
 * uci_race_class function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_race_class($race_id=0) {
	$classes=wp_get_post_terms($race_id, 'race_class', array('fields' => 'names'));

	if (isset($classes[0])) :
		$class=$classes[0];
	else :
		$class='';
	endif;
	
	return $class;
}

/**
 * uci_race_season function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_race_season($race_id=0) {
	$seasons=wp_get_post_terms($race_id, 'season', array('fields' => 'names'));

	if (isset($seasons[0])) :
		$season=$seasons[0];
	else :
		$season='';
	endif;
	
	return $season;
}

/**
 * uci_race_discipline function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_race_discipline($race_id=0) {
	$disciplines=wp_get_post_terms($race_id, 'discipline', array('fields' => 'names'));

	if (isset($disciplines[0])) :
		$discipline=$disciplines[0];
	else :
		$discipline='';
	endif;
	
	return $discipline;
}


/**
 * uci_race_series function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_race_series($race_id=0) {
	$series_arr=wp_get_post_terms($race_id, 'series', array('fields' => 'names'));

	if (isset($series_arr[0])) :
		$series=$series_arr[0];
	else :
		$series='';
	endif;
	
	return $series;
}

/**
 * uci_results_get_race_results function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @param string $format (default: 'array')
 * @return void
 */
function uci_results_get_race_results($race_id=0, $format='array') {
	$riders=array();
	$rider_ids=uci_race_results_rider_ids($race_id);
	$cols=uci_race_results_columns($race_id);

	// add rider details //
	foreach ($rider_ids as $id) :
		$post=get_post($id);
	
		$country=wp_get_post_terms($id, 'country', array("fields" => "names"));
	
		if (isset($country[0])) :
			$nat=$country[0];
		else :
			$nat='';
		endif;
			
		$arr=array(
			'ID' => $id,
			'name' => $post->post_title,
			'slug' => $post->post_name,
			'nat' => $nat,
		);
		
		// add results cols //
		foreach ($cols as $col) :
			$arr[$col]=get_post_meta($race_id, '_rider_'.$id.'_'.$col, true);
		endforeach;
		
		$riders[]=$arr;
	endforeach;	
	
	if ($format=='object')
		$riders=array_to_object($riders);

	return $riders;
}

/**
 * uci_race_results_columns function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_race_results_columns($race_id=0) {
	global $wpdb;
	
	$meta_keys=$wpdb->get_col("SELECT meta_key FROM $wpdb->postmeta WHERE post_id = $race_id AND meta_key LIKE '_rider_%'");
	$cols=array();
	
	foreach ($meta_keys as $meta_key) :
		$mk_arr=preg_split("/[0-9]+/", $meta_key);	
		$cols[]=ltrim(array_pop($mk_arr), '_');		
	endforeach;
	
	$cols=array_values(array_unique($cols));
	
	return $cols;
}

/**
 * uci_race_results_rider_ids function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_race_results_rider_ids($race_id=0) {
	global $wpdb;
	
	$ids=$wpdb->get_col("SELECT REPLACE (REPLACE (meta_key, '_rider_', ''), '_result_place', '') AS id FROM $wpdb->postmeta WHERE post_id = $race_id AND meta_key LIKE '_rider_%_result_place'");
	
	return $ids;	
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
				$html.='<option value="'.$season->slug.'" '.selected($selected, $season->slug, false).'>'.$season->name.'</option>';
			endforeach;
	$html.='</select>';
	
	return $html;
}

/**
 * uci_results_race_url function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function uci_results_race_url($slug='') {
	global $uci_results_pages;

	// check if id, not slug //
	if (is_numeric($slug))
		$slug=uci_get_race_slug($slug);

	$base_url=get_permalink($uci_results_pages['single_race']);
	$url=$base_url.$slug;

	echo $url;
}

/**
 * uci_get_race_slug function.
 *
 * @access public
 * @param int $id (default: 0)
 * @return void
 */
function uci_get_race_slug($id=0) {
	$race=get_post(absint($id));

	if (isset($race->post_name))
		return $race->post_name;

	return false;
}

/**
 * uci_results_races_url function.
 *
 * @access public
 * @return void
 */
function uci_results_races_url() {
	global $uci_results_pages;

	$url=get_permalink($uci_results_pages['races']);

	echo $url;
}

/**
 * uci_get_race_id function.
 * 
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function uci_get_race_id($slug='') {
	$race=get_page_by_path($slug, OBJECT, 'races');
	
	if (isset($race->ID))
		return $race->ID;
		
	return false;
}

/**
 * uci_get_race_discipline function.
 * 
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function uci_get_race_discipline($race_id=0) {
	$disciplines=wp_get_post_terms($race_id, 'discipline');
	
	if (isset($disciplines[0]))
		return $disciplines[0]->slug;
		
	return;
}
?>