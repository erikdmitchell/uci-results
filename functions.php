<?php
/**
 * uci_results_scripts_styles function.
 *
 * @access public
 * @return void
 */
function uci_results_scripts_styles() {
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
add_action('wp_enqueue_scripts', 'uci_results_scripts_styles');

/**
 * uci_results_get_template_part function.
 *
 * @access public
 * @param string $template_name (default: '')
 * @return void
 */
function uci_results_get_template_part($template_name='') {
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
 * ucicurl_get_admin_page function.
 *
 * @access public
 * @param bool $template_name (default: false)
 * @param mixed $attributes (default: null)
 * @return void
 */
function ucicurl_get_admin_page($template_name=false, $attributes=null) {
	if (!$attributes )
		$attributes = array();

	if (!$template_name)
		return false;

	ob_start();

	do_action('ucicurl_before_admin_'.$template_name);

	include(UCI_RESULTS_PATH.'adminpages/'.$template_name.'.php');

	do_action('ucicurl_after_admin_'.$template_name);

	$html=ob_get_contents();

	ob_end_clean();

	return $html;
}

/**
 * curl_exec_utf8 function.
 *
 * The same as curl_exec except tries its best to convert the output to utf8
 *
 * @access public
 * @param mixed $ch
 * @return void
 */
function curl_exec_utf8($ch) {
  $data = curl_exec($ch);

  if (!is_string($data)) return $data;

  unset($charset);
  $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

  /* 1: HTTP Content-Type: header */
  preg_match( '@([\w/+]+)(;\s*charset=(\S+))?@i', $content_type, $matches );
  if ( isset( $matches[3] ) )
      $charset = $matches[3];

  /* 2: <meta> element in the page */
  if (!isset($charset)) {
      preg_match( '@<meta\s+http-equiv="Content-Type"\s+content="([\w/]+)(;\s*charset=([^\s"]+))?@i', $data, $matches );
      if ( isset( $matches[3] ) )
          $charset = $matches[3];
  }

  /* 3: <xml> element in the page */
  if (!isset($charset)) {
      preg_match( '@<\?xml.+encoding="([^\s"]+)@si', $data, $matches );
      if ( isset( $matches[1] ) )
          $charset = $matches[1];
  }

  /* 4: PHP's heuristic detection */
  if (!isset($charset)) {
      $encoding = mb_detect_encoding($data);
      if ($encoding)
          $charset = $encoding;
  }

  /* 5: Default for HTML */
  if (!isset($charset)) {
      if (strstr($content_type, "text/html") === 0)
          $charset = "ISO 8859-1";
  }

  /* Convert it if it is anything but UTF-8 */
  /* You can change "UTF-8"  to "UTF-8//IGNORE" to
     ignore conversion errors and still output something reasonable */
  if (isset($charset) && strtoupper($charset) != "UTF-8")
      $data = iconv($charset, 'UTF-8', $data);

  return $data;
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
		$slug=uci_results_get_race_slug($slug);

	$base_url=get_permalink($uci_results_pages['single_race']);
	$url=$base_url.$slug;

	echo $url;
}

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
 * uci_results_get_race_slug function.
 *
 * @access public
 * @param int $id (default: 0)
 * @return void
 */
function uci_results_get_race_slug($id=0) {
	global $wpdb;

	$id=absint($id);

	$slug=$wpdb->get_var("SELECT code FROM {$wpdb->uci_results_races} WHERE id=$id");

	return $slug;
}

/**
 * uci_results_get_race_id function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function uci_results_get_race_id($slug='') {
	global $wpdb;

	$id=$wpdb->get_var("SELECT id FROM {$wpdb->uci_results_races} WHERE code='$slug'");

	return absint($id);
}

/**
 * ucicurl_rider_slug_to_name function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function ucicurl_rider_slug_to_name($slug='') {
	global $wpdb;

	$name=$wpdb->get_var("SELECT name FROM {$wpdb->uci_results_riders} WHERE slug='{$slug}'");

	return $name;
}

/**
 * ucicurl_race_slug_to_name function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function ucicurl_race_slug_to_name($slug='') {
	global $wpdb;

	$name=$wpdb->get_var("SELECT event FROM {$wpdb->uci_results_races} WHERE code='{$slug}'");

	return $name;
}

/**
 * uci_results_get_rider_id function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function uci_results_get_rider_id($slug='') {
	global $wpdb;

	$id=$wpdb->get_var("SELECT id FROM {$wpdb->uci_results_riders} WHERE slug='{$slug}'");

	return $id;
}

/**
 * uci_results_race_has_results function.
 *
 * @access public
 * @param string $code (default: '')
 * @return void
 */
function uci_results_race_has_results($code='') {
	global $wpdb;

	$race_id=$wpdb->get_var("SELECT id FROM $wpdb->uci_results_races WHERE code = \"$code\"");

	// no race id, we out //
	if (!$race_id)
		return false;

	$results=$wpdb->get_results("SELECT id FROM $wpdb->uci_results_results WHERE race_id = $race_id");

	// do we have results? //
	if (count($results))
		return true;

	return false;
}

/**
 * uci_results_get_default_rider_ranking_season function.
 *
 * @access public
 * @return void
 */
function uci_results_get_default_rider_ranking_season() {
	global $wpdb;

	$season=$wpdb->get_var("SELECT MAX(season) FROM $wpdb->uci_results_rider_rankings");

	return $season;
}

/**
 * uci_results_get_default_rider_ranking_week function.
 *
 * @access public
 * @return void
 */
function uci_results_get_default_rider_ranking_week() {
	global $wpdb;

	$season=uci_results_get_default_rider_ranking_season(); // get latest season
	$week=$wpdb->get_var("SELECT MAX(week) FROM $wpdb->uci_results_rider_rankings WHERE season='$season'");

	return $week;
}

/**
 * uci_results_seasons_dropdown function.
 *
 * @access public
 * @param string $name (default: 'seasons')
 * @param string $selected (default: '')
 * @return void
 */
function uci_results_seasons_dropdown($name='seasons', $selected='') {
	global $ucicurl_races;

	$html=null;
	$seasons=$ucicurl_races->seasons();
	$seasons_arr=explode('/', end($seasons));

	// add one to each year //
	foreach ($seasons_arr as $key => $year) :
		$seasons_arr[$key]=absint($year)+1;
	endforeach;

	// append //
	$seasons[]=implode('/', $seasons_arr);

	$html.='<select id="'.$name.'" name="'.$name.'">';
		$html.='<option value="0">'.__('Select One', '').'</option>';
		foreach ($seasons as $season) :
			$html.='<option name="'.$season.'" '.selected($selected, $season, false).'>'.$season.'</option>';
		endforeach;
	$html.='</select>';

	echo $html;
}

/**
 * uci_results_seasons_checkboxes function.
 *
 * @access public
 * @param string $name (default: 'seasons')
 * @param string $checked (default: '')
 * @return void
 */
function uci_results_seasons_checkboxes($name='seasons', $checked='') {
	global $ucicurl_races;

	$html=null;
	$seasons=$ucicurl_races->seasons();

	foreach ($seasons as $key => $season) :
		$class='';

		if ($key==0)
			$class='first';

		$html.='<input type="checkbox" name="'.$name.'[]" class="'.$name.' '.$class.'" value="'.$season.'">'.$season.'<br />';
	endforeach;

	echo $html;
}

global $uci_results_admin_notices;

/**
 * uci_results_admin_notices function.
 *
 * @access public
 * @return void
 */
function uci_results_admin_notices() {
	global $uci_results_admin_notices;

	if (empty($uci_results_admin_notices))
		return;

	foreach ($uci_results_admin_notices as $class => $notices) :
		foreach ($notices as $notice) :
			echo '<div class="'.$class.'">'.__($notice, 'uci-results').'</div>';
		endforeach;
	endforeach;
}

/**
 * uci_results_admin_url function.
 *
 * @access public
 * @param string $args (default: '')
 * @return void
 */
function uci_results_admin_url($args='') {
	$default_args=array(
		'page' => 'uci-results',
		'tab' => 'results'
	);
	$args=wp_parse_args($args, $default_args);

	if (isset($_GET['paged']))
		$args['paged']=$_GET['paged'];

	echo add_query_arg($args, admin_url());
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

/**
 * uci_results_get_last_week_in_season function.
 *
 * @access public
 * @param string $season (default: '')
 * @return void
 */
function uci_results_get_last_week_in_season($season='') {
	global $wpdb;

	$week=$wpdb->get_var("SELECT MAX(week) FROM $wpdb->uci_results_races WHERE season='$season'");

	return $week;
}

/**
 * uci_results_schedule_event function.
 *
 * @access public
 * @param mixed $timestamp
 * @param mixed $recurrence
 * @param mixed $hook
 * @param array $args (default: array())
 * @return void
 */
function uci_results_schedule_event($timestamp, $recurrence, $hook, $args = array()) {
	$next = wp_next_scheduled($hook, $args);

	if (empty($next)) :
		return wp_schedule_event($timestamp, $recurrence, $hook, $args);
	else :
		return false;
	endif;
}

/**
 * uci_results_search_ajax function.
 *
 * @access public
 * @return void
 */
function uci_results_search_ajax() {
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
		$type='';
	endif;

	// build args //
	$args=array(
		'per_page' => 15,
		'type' => $type,
		'search' => $_POST['search']
	);

	// run query //
	$query=new UCI_Results_Query(array(
		'per_page' => 15,
		'type' => $type,
		'search' => $_POST['search'],
	));

//echo $query->query;
/*
			'order_by' => '', // date (races -- name (riders)
			'order' => '', // DESC (races -- ASC (riders)
			'class' => false, // races
			'season' => false, // races, rider ranks
			'nat' => false,
			'name' => false, // riders
			'rider_id' => 0, // riders
			'start_date' => false, // races
			'end_date' => false, // races
			'rankings' => false, // riders
			'meta' => array()
*/
	$return=array();

	//$return['details']=uci_results_build_search_details($query);
	$return['content']=uci_results_build_search_results($query->posts, $type);
	$return['details']=$query->query;

	echo json_encode($return);

	wp_die();
}
add_action('wp_ajax_uci_results_search', 'uci_results_search_ajax');
add_action('wp_ajax_nopriv_uci_results_search', 'uci_results_search_ajax');

/**
 * uci_results_build_search_results function.
 *
 * @access public
 * @param string $posts (default: '')
 * @param string $type (default: '')
 * @return void
 */
function uci_results_build_search_results($posts='', $type='') {
	global $ucicurl_riders, $ucicurl_races;

	if (empty($posts))
		return '<div class="not-found">No results found.</div>';

	$html=null;

	foreach ($posts as $post) :
		if (isset($post->type))
			$type=$post->type;

		if ($type=='rider' || $type=='riders') :
			$post_data=$ucicurl_riders->get_rider(array('rider_id' => $post->id));
			$icon='<i class="fa fa-user" aria-hidden="true"></i>';
		elseif ($type=='race' || $type='races') :
			$post_data=$ucicurl_races->get_race($post->id);
			$icon='<i class="fa fa-flag-checkered" aria-hidden="true"></i>';

			// conform to post //
			$post_data->name=$post_data->event;
		else :
			$post_data='';
			$icon='';
		endif;

		$html.='<div id="dbid-'.$post->id.'" class="em-row">';
			$html.='<div class="em-col-md-1 type">'.$icon.'</div>';
			$html.='<div class="em-col-md-10 name"><a href="#">'.$post->name.'</a></div>';
			$html.='<div class="em-col-md-1 country">'.ucicurl_get_country_flag($post_data->nat).'</div>';
		$html.='</div>';
	endforeach;

	return $html;
}

/**
 * uci_results_build_search_details function.
 *
 * @access public
 * @param mixed $query
 * @return void
 */
function uci_results_build_search_details($query) {
	$html='';

	$html.='<div class="total-posts">'.$query->found_posts.' posts found</div>';

	return $html;
}

/**
 * uci_results_current_season function.
 *
 * @access public
 * @return void
 */
function uci_results_current_season() {
	echo uci_results_get_current_season();
}

/**
 * uci_results_get_current_season function.
 *
 * @access public
 * @return void
 */
function uci_results_get_current_season() {
	return get_option('uci_results_current_season', 0);
}

/**
 * uci_results_get_previous_season function.
 *
 * @access public
 * @return void
 */
function uci_results_get_previous_season() {
	global $fantasy_cycling_settings;

	$current_season=get_option('uci_results_current_season', 0);
	$current_season_arr=explode('/', $current_season);

	// subtract one from each year //
	foreach ($current_season_arr as $key => $year) :
		$current_season_arr[$key]=absint($year)-1;
	endforeach;

	$prev_season=implode('/', $current_season_arr);

	return $prev_season;
}

/**
 * uci_results_get_season_weeks function.
 *
 * @access public
 * @param string $season (default: '')
 * @return void
 */
function uci_results_get_season_weeks($season='') {
	$season_weeks=get_option('uci_results_season_weeks', array());

	// specific season //
	if (!empty($season)) :
		foreach ($season_weeks as $key => $value) :
			if ($value['season']==$season)
				return $value;
		endforeach;
	endif;

	return $season_weeks;
}

/**
 * uci_results_build_season_weeks function.
 *
 * @access public
 * @param string $season (default: '')
 * @return void
 */
function uci_results_build_season_weeks($season='') {
	global $uci_results_admin_pages;

	$season_weeks=get_option('uci_results_season_weeks', array());

	// check for a specific season //
	if (!empty($season)) :
		foreach ($season_weeks as $key => $value) :
			if ($value['season']==$season)
				$season_weeks[$key]=uci_results_set_season_weeks($season);
		endforeach;
	else :
		$season_weeks=[]; // clear all

		foreach ($uci_results_admin_pages->config->urls as $season => $url) :
			$season_weeks[]=uci_results_set_season_weeks($season);
		endforeach;
	endif;

	update_option('uci_results_season_weeks', $season_weeks);
}

/**
 * uci_results_set_season_weeks function.
 *
 * @access public
 * @param string $season (default: '')
 * @return void
 */
function uci_results_set_season_weeks($season='') {
	global $uci_results_admin_pages, $uci_results_add_races;

	$uci_results_urls=get_object_vars($uci_results_admin_pages->config->urls);

	// see if the season is set in our admin pages (config) //
	if (!isset($uci_results_urls[$season]))
		return false;

	$season_url=$uci_results_urls[$season];
	$season_races=$uci_results_add_races->get_race_data($season, false, true, $season_url);

	$first_race=end($season_races);
	$last_race=reset($season_races);

	$dates=array(
		'season' => $season,
		'start' => date('M j Y', strtotime($first_race->date)),
		'end' => date('M j Y', strtotime($last_race->date)),
	);
	$season_data=uci_results_add_weeks_to_season($dates);

	return $season_data;
}

/**
 * uci_results_add_weeks_to_season function.
 *
 * @access public
 * @param string $season (default: '')
 * @return void
 */
function uci_results_add_weeks_to_season($season='') {
	if (empty($season))
		return false;

	// start (first) week //
	$start_date_arr=explode(' ', $season['start']); // get start day
	$next_monday=strtotime('monday', mktime(0, 0, 0, date('n', strtotime($season['start'])), $start_date_arr[1], $start_date_arr[2])); // get next monday
	$next_sunday=strtotime('sunday', $next_monday); // get next sunday
	$first_monday=strtotime('-1 week', $next_monday); // prev monday is start
	$first_sunday=strtotime('-1 week', $next_sunday); // prev sunday is start

	// end (last) week //
	$end_date_arr=explode(' ', $season['end']); // get end day
	$last_monday=strtotime('monday', mktime(0, 0, 0, date('n', strtotime($season['end'])), $end_date_arr[1], $end_date_arr[2])); // get next monday
	$last_sunday=strtotime('sunday', $last_monday); // get next sunday
	$final_monday=strtotime('-1 week', $last_monday); // prev monday is start
	$final_sunday=strtotime('-1 week', $last_sunday); // prev sunday is start

	// build out all our weeks //
	$monday=$first_monday;
	$sunday=$first_sunday;

	while ($monday != $final_monday) :
    $season['weeks'][]=array(
    	'start' => date('c', $monday),
    	'end' => date('c', $sunday)
    );

    $monday=strtotime('+1 week', $monday);
    $sunday=strtotime('+1 week', $sunday);
	endwhile;

	// append final week //
	$season['weeks'][]=array(
		'start' => date('c', $final_monday),
		'end' => date('c', $final_sunday)
	);

	return $season;
}

/**
 * uci_results_store_rider_rankings function.
 *
 * @access public
 * @param string $season (default: '')
 * @param string $week (default: '')
 * @return void
 */
function uci_results_store_rider_rankings($season='', $week='') {
	// check season //
	if (empty($season))
		$season=uci_results_get_default_rider_ranking_season();

	// check week //
	if (empty($week))
		$week=uci_results_get_default_rider_ranking_week();

	$riders=new UCI_Results_Query(array(
		'per_page' => -1,
		'type' => 'riders',
		'rankings' => true
	));

	$rankings=new stdClass();
	$rankings->season=$season;
	$rankings->week=$week;
	$rankings->riders=$riders->posts;

	$option=update_option('uci_results_current_rankings', $rankings);

	return $option;
}

/**
 * uci_results_get_stored_rankings function.
 *
 * @access public
 * @return void
 */
function uci_results_get_stored_rankings() {
	return get_option('uci_results_current_rankings', array());
}

/**
 * uci_results_display_total function.
 *
 * @access public
 * @param array $arr (default: array())
 * @return void
 */
function uci_results_display_total($arr=array()) {
	if (!$arr || empty($arr)) :
		echo 0;
	else :
		echo count($arr);
	endif;
}
?>