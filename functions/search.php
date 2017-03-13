<?php
/**
 * uci_search function.
 * 
 * @access public
 * @param string $args (default: '')
 * @return void
 */
function uci_search($args='') {
	$default_args=array(
		'posts_per_page' => 20,
		'post_type' => array('riders', 'races'),
	);
	$args=wp_parse_args($args, $default_args);
	$results=get_posts($args);

	return $results;
}	

/**
 * uci_search_race_details function.
 * 
 * @access public
 * @param string $race (default: '')
 * @return void
 */
function uci_search_race_details($race='') {
	if (empty($race))
		return;
		
	$race->race_date=get_post_meta($race->ID, '_race_date', true);
	$race->nat=uci_race_country($race->ID);
	$race->class=uci_race_class($race->ID);
	$race->season=uci_race_season($race->ID);
	$race->series=uci_race_series($race->ID);
	
	return $race;
}

/**
 * uci_search_rider_details function.
 * 
 * @access public
 * @param string $rider (default: '')
 * @return void
 */
function uci_search_rider_details($rider='') {
	if (empty($rider))
		return;
		
	$rider->nat=uci_get_first_term($rider->ID, 'country');
	
	return $rider;
}
?>