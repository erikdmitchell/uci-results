<?php

/**
 * ucicurl_get_template function.
 *
 * @access public
 * @param bool $template_name (default: false)
 * @param mixed $attributes (default: null)
 * @return void
 */
function ucicurl_get_template($template_name=false, $attributes=null) {
	if (!$attributes )
		$attributes = array();

	if (!$template_name)
		return false;

	ob_start();

	do_action('ulm_before_'.$template_name);

	if (file_exists(get_stylesheet_directory().'/ultimate-league-management/templates/'.$template_name.'.php')) :
		include(get_stylesheet_directory().'/ultimate-league-management/templates/'.$template_name.'.php');
	elseif (file_exists(get_template_directory().'/ultimate-league-management/templates/'.$template_name.'.php')) :
		include(get_template_directory().'/ultimate-league-management/templates/'.$template_name.'.php');
	else :
		include(UCICURL_PATH.'templates/'.$template_name.'.php');
	endif;

	do_action('ulm_after_'.$template_name);

	$html=ob_get_contents();

	ob_end_clean();

	return $html;
}

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

	include(UCICURL_PATH.'adminpages/'.$template_name.'.php');

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

	$slug=$wpdb->get_var("SELECT code FROM {$wpdb->ucicurl_races} WHERE id=$id");

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

	$id=$wpdb->get_var("SELECT id FROM {$wpdb->ucicurl_races} WHERE code='$slug'");

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

	$name=$wpdb->get_var("SELECT name FROM {$wpdb->ucicurl_riders} WHERE slug='{$slug}'");

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

	$name=$wpdb->get_var("SELECT event FROM {$wpdb->ucicurl_races} WHERE code='{$slug}'");

	return $name;
}

/**
 * ucicurl_get_rider_id function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function ucicurl_get_rider_id($slug='') {
	global $wpdb;

	$id=$wpdb->get_var("SELECT id FROM {$wpdb->ucicurl_riders} WHERE slug='{$slug}'");

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

	$race_id=$wpdb->get_var("SELECT id FROM {$wpdb->ucicurl_races} WHERE code=\"{$code}\"");

	// no race id, we out //
	if (!$race_id)
		return false;

	$results=$wpdb->get_results("SELECT id FROM {$wpdb->ucicurl_results} WHERE race_id={$race_id}");

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
	global $ucicurl_races;

	$seasons=$ucicurl_races->seasons(); // get seasons
	$latest_season=array_pop($seasons); // get last season in arr

	return $latest_season;
}

/**
 * uci_results_get_default_rider_ranking_week function.
 *
 * @access public
 * @return void
 */
function uci_results_get_default_rider_ranking_week() {
	global $ucicurl_races;

	$season=uci_results_get_default_rider_ranking_season(); // get latest season
	$weeks=$ucicurl_races->weeks($season); // get weeks
	$latest_week=array_pop($weeks); // get latest week

	return $latest_week;
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
		'page' => 'uci-curl',
		'tab' => 'uci-curl'
	);
	$args=wp_parse_args($args, $default_args);

	echo add_query_arg($args, admin_url());
}
?>