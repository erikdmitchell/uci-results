<?php
/**
 * uci_get_riders function.
 * 
 * @access public
 * @param string $args (default: '')
 * @return void
 */
function uci_get_riders($args='') {
	global $uci_riders;

	$default_args=array(
		'per_page' => -1,
		'rider_ids' => '',
		'results' => false,
		'last_result' => false,
		'race_ids' => '',
		'results_season' => '',
		'ranking' => false,
		'stats' => false,
		'nat' => '',
		'page' => '',
	);
	$args=wp_parse_args($args, $default_args);	
	$riders=$uci_riders->get_riders($args);
	
	return $riders;
}			

/**
 * uci_results_get_rider_results function.
 * 
 * @access public
 * @param string $args (default: '')
 * @return void
 */
function uci_results_get_rider_results($args='') {
	$default_args=array(
		'rider_id' => 0, 
		'race_ids' => '', 
		'seasons' => '', 
		'places' => '',
		'race_classes' => '',
		'race_series' => '',
	);
	$args=wp_parse_args($args, $default_args);
	
	extract($args);
	
	if (!$rider_id)
		return false;
		
	$results=array();
	
	if (!is_array($race_ids) && !empty($race_ids))
		$race_ids=explode(',', $race_ids);

	if (!is_array($seasons) && !empty($seasons))
		$seasons=explode(',', $seasons);

	if (!is_array($places) && !empty($places))
		$places=explode(',', $places);

	if (!is_array($race_classes) && !empty($race_classes))
		$race_classes=explode(',', $race_classes);

	if (!is_array($race_series) && !empty($race_series))
		$race_series=explode(',', $race_series);
		
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
	
	// check specific race ids //
	if (!empty($race_ids))
		$results_args_meta['post__in']=$race_ids;

	// check specific seasons //
	if (!empty($seasons))
		$results_args_meta['tax_query'][]=array(
			'taxonomy' => 'season',
			'field' => 'slug',
			'terms' => $seasons
		);

	// check specific race_classes //
	if (!empty($race_classes))
		$results_args_meta['tax_query'][]=array(
			'taxonomy' => 'race_class',
			'field' => 'slug',
			'terms' => $race_classes
		);

	// check specific race_series //
	if (!empty($race_series))
		$results_args_meta['tax_query'][]=array(
			'taxonomy' => 'series',
			'field' => 'slug',
			'terms' => $race_series
		);

	$race_ids=get_posts($results_args_meta);
	
	foreach ($race_ids as $race_id) :
		$result=get_post_meta($race_id, '_rider_'.$rider_id, true);
		$result['race_id']=$race_id;
		$result['race_name']=get_the_title($race_id);
		$result['race_date']=get_post_meta($race_id, '_race_date', true);
		$result['race_class']=uci_get_first_term($race_id, 'race_class');
		$result['race_season']=uci_get_first_term($race_id, 'season');		
		
		if (!empty($places)) :
			if (in_array($result['place'], $places)) :
				$results[]=$result;			
			endif;
		else :
			$results[]=$result;
		endif;
	endforeach;

	return $results;
}

/**
 * uci_get_riders_by_rank function.
 * 
 * @access public
 * @param string $args (default: '')
 * @return void
 */
function uci_get_riders_by_rank($args='') {
	$default_args=array(
		'per_page' => 10,
		'order_by' => 'rank',
		'order' => 'ASC',
		'season' => uci_results_get_default_rider_ranking_season(),
		'week' => uci_results_get_default_rider_ranking_week(),
		'nat' => '',
		'paged' => get_query_var('page'),
	);
	$args=wp_parse_args($args, $default_args);
	$riders=new RiderRankingsQuery($args);

	return $riders->posts;
}

/**
 * uci_results_rider_url function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function uci_results_rider_url($slug='') {
	global $uci_results_pages;

	$base_url=get_permalink($uci_results_pages['single_rider']);
	$url=$base_url.$slug;

	echo $url;
}

/**
 * uci_get_rider_id function.
 * 
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function uci_get_rider_id($slug='') {
	global $wpdb;

	$id=$wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '$slug'");

	return $id;
}

/**
 * uci_get_rider_id_by_name function.
 * 
 * @access public
 * @param string $name (default: '')
 * @return void
 */
function uci_get_rider_id_by_name($name='') {
	global $wpdb;

	$id=$wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = '$name'");

	return $id;
}

/**
 * uci_results_rider_rankings_url function.
 *
 * @access public
 * @return void
 */
function uci_results_rider_rankings_url() {
	global $uci_results_pages;

	$url=get_permalink($uci_results_pages['riders']);

	echo $url;
}

/**
 * uci_results_add_rider function.
 * 
 * @access public
 * @param string $name (default: '')
 * @param string $country (default: '')
 * @return void
 */
function uci_results_add_rider($name='', $country='') {
	if (empty($name))
		return 0;
		
	$rider=get_page_by_title($name, OBJECT, 'riders');

	// check if we have a rider id, otherwise create one //
	if ($rider===null || empty($rider->ID)) :
		$rider_insert=array(
			'post_title' => $name,
			'post_content' => '',
			'post_status' => 'publish',	
			'post_type' => 'riders',
			'post_name' => sanitize_title_with_dashes($name)
		);
		$rider_id=wp_insert_post($rider_insert);
		
		wp_set_object_terms($rider_id, $country, 'country', false);
	else :
		$rider_id=$rider->ID;
	endif;
	
	return $rider_id;		
}

function uci_results_get_rider_stats($rider_id=0) {
	global $uci_rider_stats;
	
	if (!$rider_id)
		return;
echo "uci_results_get_rider_stats";		
	foreach ($uci_rider_stats as $id => $class) {
		echo $class->get_stats();
	}
}
?>