<?php
/**
 * uci_scripts_styles function.
 *
 * @access public
 * @return void
 */
function uci_scripts_styles() {
	global $uci_results_pages;

	// include on search page //
	if (is_page($uci_results_pages['search'])) :
		wp_enqueue_script('uci-results-search-script', UCI_RESULTS_URL.'/js/search.js', array('jquery'), '0.1.0');

		wp_localize_script('uci-results-search-script', 'searchAJAXObject', array('ajax_url' => admin_url('admin-ajax.php')));
	endif;

	wp_enqueue_style('uci-results-fa-style', UCI_RESULTS_URL.'css/font-awesome.min.css');
	wp_enqueue_style('uci-results-style', UCI_RESULTS_URL.'/css/main.css');
	wp_enqueue_style('uci-results-grid', UCI_RESULTS_URL.'/css/em-bs-grid.css');
}
add_action('wp_enqueue_scripts', 'uci_scripts_styles');
	
///////// RIDERS

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



function riders_the_posts_details($posts, $query) {
	global $uci_riders;
	
	if ($query->query_vars['post_type']!='riders')
		return $posts;

	foreach ($posts as $post) :
		$post->nat=uci_get_first_term($post->ID, 'country');
		$post->tiwtter=get_post_meta($post->ID, '_rider_twitter', true);
		
		if ($query->get('ranking')) :
			$post->rank=$uci_riders->get_rider_rank($post->ID);
		endif;
	endforeach;

	return $posts;
}
add_action('the_posts', 'riders_the_posts_details', 10, 2);


function races_the_posts_details($posts, $query) {
	global $uci_riders;
	
	if ($query->query_vars['post_type']!='races')
		return $posts;

	foreach ($posts as $post) :
		$post=uci_race_details($post);
	endforeach;

	return $posts;
}
add_action('the_posts', 'races_the_posts_details', 10, 2);


/**
 * uci_get_first_term function.
 * 
 * @access public
 * @param int $post_id (default: 0)
 * @param string $taxonomy (default: '')
 * @return void
 */
function uci_get_first_term($post_id=0, $taxonomy='') {
	$terms=wp_get_post_terms($post_id, $taxonomy, array('fields' => 'names'));
	
	if (is_wp_error($terms))
		return false;
		
	if (isset($terms[0]))
		return $terms[0];
		
	return false;
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

function uci_get_rider_id($slug='') {
	global $wpdb;

	$id=$wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '$slug'");

	return $id;
}

///////// RACES

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

	$races=get_posts(array(
		'posts_per_page' => $per_page,
		'include' => $id,
		'post_type' => 'races',
		'orderby' => $orderby,
		'meta_key' => $meta_key,
		'order' => $order,
	));

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
	
	// add rider details //
	foreach ($riders as $key => $rider) :
		$rider_post=get_page_by_title($rider['name'], OBJECT, 'riders');
		$riders[$key]['ID']=$rider_post->ID;
		$riders[$key]['slug']=$rider_post->post_name;
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

////////// SEASON

/**
 * uci_get_season_weeks_dropdown function.
 * 
 * @access public
 * @param string $season (default: '')
 * @param string $selected (default: '')
 * @param string $name (default: 'week')
 * @return void
 */
function uci_get_season_weeks_dropdown($season='', $selected='', $name='week') {
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

/**
 * uci_results_get_season_weeks function.
 *
 * @access public
 * @param string $season (default: '')
 * @return void
 */
function uci_results_get_season_weeks($season='') {
	global $uci_cross_seasons;	
	
	$html=null;
	$weeks=$uci_cross_seasons->get_season_weeks($season);
	
	if (empty($weeks))
		return;
		
	return $weeks;
}

/**
 * uci_results_get_default_rider_ranking_week function.
 *
 * @access public
 * @return void
 */
function uci_results_get_default_rider_ranking_week() {
	global $uci_cross_seasons;	
	
	$html=null;
	$weeks=$uci_cross_seasons->get_last_season_week($season);
	
	if (empty($weeks))
		return;
		
	return $weeks;
}

/**
 * uci_results_get_current_season function.
 *
 * @access public
 * @return void
 */
function uci_results_get_current_season() {
	$season_id=get_option('uci_results_current_season', 0);
	
	$season=get_term_by('id', $season_id, 'season');
	
	return $season;
}

/**
 * uci_results_get_previous_season function.
 * 
 * @access public
 * @return void
 */
function uci_results_get_previous_season() {
	$current_season=uci_results_get_current_season();
	$current_season_arr=explode('/', $current_season->name);

	// subtract one from each year //
	foreach ($current_season_arr as $key => $year) :
		$current_season_arr[$key]=absint($year)-1;
	endforeach;
	
	$prev_season_slug=implode('', $current_season_arr);
	$prev_season=$season=get_term_by('slug', $prev_season_slug, 'season');

	return $prev_season;
}

/**
 * uci_results_get_rider_rank function.
 * 
 * @access public
 * @param int $rider_id (default: 0)
 * @param string $season (default: '')
 * @param string $week (default: '')
 * @return void
 */
function uci_results_get_rider_rank($rider_id=0, $season='', $week='') {
	global $wpdb;

	$rank=$wpdb->get_var("SELECT rank FROM $wpdb->uci_results_rider_rankings WHERE rider_id=$rider_id AND season='$season' AND week=$week");

	if (!$rank)
		$rank=0;

	return $rank;
}







///////////////////
/**
 * uci_results_country_url function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function uci_results_country_url($slug='') {
	global $uci_results_pages;

	$base_url=get_permalink($uci_results_pages['country']);
	$url=$base_url.$slug;

	echo $url;
}

/**
 * uci_results_rider_rankings_url function.
 *
 * @access public
 * @return void
 */
function uci_results_rider_rankings_url() {
	global $uci_results_pages;

	$url=get_permalink($uci_results_pages['rider_rankings']);

	echo $url;
}



/**
 * uci_results_template_loader function.
 *
 * @access public
 * @param mixed $template
 * @return void
 */
function uci_results_template_loader($template) {
	global $uci_results_pages, $post;

	$located=false;
	$template_slug='';

	// check archive //
	if (is_archive()) :
		$template_slug='archive';

		// see if this page matches our set pages //
		foreach ($uci_results_pages as $slug => $id) :
			if (get_post_type()==$slug) :
				$template_slug=$slug;
			endif;
		endforeach;
	endif;
	
	// it's a page //
	if (is_page()) :
		$template_slug='page';

		// see if this page matches our set pages //
		foreach ($uci_results_pages as $slug => $id) :
			if ($post->ID==$id) :
				$template_slug=$slug;
			endif;
		endforeach;
	endif;

	// check theme(s), then plugin //
	if (file_exists(get_stylesheet_directory().'/uci-results/'.$template_slug.'.php')) :
		$located=get_stylesheet_directory().'/uci-results/'.$template_slug.'.php';
	elseif (file_exists(get_template_directory().'/uci-results/'.$template_slug.'.php')) :
		$located=get_template_directory().'/uci-results/'.$template_slug.'.php';
	elseif (file_exists(UCI_RESULTS_PATH.'templates/'.$template_slug.'.php')) :
		$located=UCI_RESULTS_PATH.'templates/'.$template_slug.'.php';
	endif;

	// we found a template //
	if ($located)
		$template=$located;

	return $template;
}
add_filter('template_include', 'uci_results_template_loader');

/**
 * uci_get_template_part function.
 * 
 * @access public
 * @param string $template_name (default: '')
 * @param string $atts (default: '')
 * @return void
 */
function uci_get_template_part($template_name='', $atts='') {
	if (empty($template_name))
		return false;

	ob_start();

	do_action('uci_results_get_template_part'.$template_name);

	if (file_exists(get_stylesheet_directory().'/uci-results/'.$template_name.'.php')) :
		include(get_stylesheet_directory().'/uci-results/'.$template_name.'.php');
	elseif (file_exists(get_template_directory().'/uci-results/'.$template_name.'.php')) :
		include(get_template_directory().'/uci-results/'.$template_name.'.php');
	else :
		include(UCI_RESULTS_PATH.'templates/'.$template_name.'.php');
	endif;

	$html=ob_get_contents();

	ob_end_clean();

	return $html;
}


function uci_pagination($numpages='', $pagerange='', $paged='') {
	global $paged;
	
	$html=null;
	
	if (empty($pagerange))
		$pagerange = 2;

	if (empty($paged))
		$paged = 1;
	
	if ($numpages == '') :
		global $wp_query;
		
		$numpages = $wp_query->max_num_pages;
		
		if (!$numpages) :
			$numpages = 1;
		endif;
	endif;

	$pagination_args = array(
		'base'            => get_pagenum_link(1) . '%_%',
		'format'          => '?paged=%#%',
		'total'           => $numpages,
		'current'         => $paged,
		'mid_size'        => $pagerange,
		'prev_text'       => __('&laquo;'),
		'next_text'       => __('&raquo;'),
	);
	
	$paginate_links = paginate_links($pagination_args);
	
	if ($paginate_links) :
		$html.="<nav class='custom-pagination'>";
			//$html.="<span class='page-numbers page-num'>Page " . $paged . " of " . $numpages . "</span> ";
			$html.=$paginate_links;
		$html.="</nav>";
	endif;
	
	echo $html;
}

//////////// search

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
 * ajax_uci_search function.
 * 
 * @access public
 * @return void
 */
function ajax_uci_search() {
	$rows=array();
	
	// set type param //
	if (isset($_POST['search_data']['types'])) :
		if (count($_POST['search_data']['types']) == 1) : // just one type
			$type=$_POST['search_data']['types'][0];
		elseif (count($_POST['search_data']['types'])) : // multiple
			$type='';
		else : // a basic fallback
			$type='';
		endif;
	else :
		$type=array('riders', 'races');
	endif;

	// build args //
	$args=array(
		'posts_per_page' => -1,
		'post_type' => $type,
		's' => $_POST['search']
	);

	// run query //
	$results=uci_search($args);
	
	foreach ($results as $result) :
		if (get_post_type($result->ID) == 'races') :
			$result=uci_search_race_details($result);
		elseif (get_post_type($result->ID) == 'riders') :
			$result=uci_search_rider_details($result);
		endif;
		
		$rows[]=uci_get_template_part('search/row', $result);
	endforeach;

	echo json_encode($rows);

	wp_die();
}
add_action('wp_ajax_uci_results_search', 'ajax_uci_search');
add_action('wp_ajax_nopriv_uci_results_search', 'ajax_uci_search');

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