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
 * uci_results_seasons_dropdown function.
 *
 * @access public
 * @param string $name (default: 'seasons')
 * @param string $selected (default: '')
 * @return void
 */
function uci_results_seasons_dropdown($name='seasons', $selected='') {
	wp_dropdown_categories(array(
		'show_option_none'   => 'Select One',
		'orderby'            => 'name',
		'order'              => 'DESC',
		'hide_empty'         => 0,
		'selected'           => $selected,
		'name'               => $name,
		'id'                 => $name,
		'class'              => 'seasons-dropdown',
		'taxonomy'           => 'season',
	));
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

function uci_results_build_search_results($posts='', $type='') {
	global $uci_riders;

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