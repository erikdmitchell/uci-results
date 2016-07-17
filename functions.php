<?php

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
		include(UCICURL_PATH.'templates/'.$template_name.'.php');
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
	elseif (file_exists(UCICURL_PATH.'templates/'.$template_slug.'.php')) :
		$located=UCICURL_PATH.'templates/'.$template_slug.'.php';
	endif;

	// we found a template //
	if ($located)
		$template=$located;

	return $template;
}
add_filter('template_include', 'uci_results_template_loader');

/*
function rcp_locate_template( $template_names, $load = false, $require_once = true ) {
	// No file found yet
	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) )
			continue;

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// Check child theme first
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'rcp/' . $template_name ) ) {
			$located = trailingslashit( get_stylesheet_directory() ) . 'rcp/' . $template_name;
			break;

		// Check parent theme next
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . 'rcp/' . $template_name ) ) {
			$located = trailingslashit( get_template_directory() ) . 'rcp/' . $template_name;
			break;

		// Check theme compatibility last
		} elseif ( file_exists( trailingslashit( rcp_get_templates_dir() ) . $template_name ) ) {
			$located = trailingslashit( rcp_get_templates_dir() ) . $template_name;
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) )
		load_template( $located, $require_once );

	return $located;
}
*/

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

	if (isset($_GET['paged']))
		$args['paged']=$_GET['paged'];

	echo add_query_arg($args, admin_url());
}

function uci_results_get_rider_rank($rider_id=0, $season='', $week='') {
	global $wpdb;

	$rank=$wpdb->get_var("SELECT rank FROM $wpdb->ucicurl_rider_rankings WHERE rider_id=$rider_id AND season='$season' AND week=$week");

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

	$week=$wpdb->get_var("SELECT MAX(week) FROM $wpdb->ucicurl_races WHERE season='$season'");

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
?>