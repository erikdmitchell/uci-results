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
 * ucicurl_rider_url function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function ucicurl_rider_url($slug='') {
	$url="/rider/$slug";

	echo $url;
}

/**
 * ucicurl_race_url function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function ucicurl_race_url($slug='') {
	$url="/races/$slug";

	echo $url;
}

/**
 * ucicurl_country_url function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function ucicurl_country_url($slug='') {
	$url="/country/$slug";

	echo $url;
}

/**
 * ucicurl_create_virtual_page function.
 *
 * @access public
 * @return void
 */
function ucicurl_create_virtual_page() {
	global $wp;

	$args=array();
	$url = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
	$url_array=explode('/', $url); // split into page slug and slug

  if ($url_array[0]=='rider') :
		$args = array(
			'slug' => 'rider',
			'title' => ucicurl_rider_slug_to_name($url_array[1]),
			'content' => ucicurl_get_template('rider', array('rider_id' => ucicurl_get_rider_id($url_array[1]))),
			'pagename' => $url
		);
	elseif ($url_array[0]=='races') :
		$args = array(
			'slug' => 'races',
			'title' => ucicurl_race_slug_to_name($url_array[1]),
			'content' => "TEMPLATE",
			'pagename' => $url
		);
	elseif ($url_array[0]=='country') :
		$args = array(
			'slug' => 'country',
			'title' => 'Rider Page',
			'content' => "This can be generated content, or static content<br />Whatever you put here will appear on your virtual page.",
			'pagename' => $url
		);
	endif;

	// generate page if we have args //
	if (!empty($args))
		$pg = new ucicurlVirtualPage($args);
}
add_action('init', 'ucicurl_create_virtual_page');

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
* Build the entire current page URL (incl query strings) and output it
* Useful for social media plugins and other times you need the full page URL
* Also can be used outside The Loop, unlike the_permalink
*
* @returns the URL in PHP (so echo it if it must be output in the template)
* Also see the_current_page_url() syntax that echoes it
*/
if ( ! function_exists( 'get_current_page_url' ) ) {
	function get_current_page_url() {
	  global $wp;
	  return add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );
	}
}

/*
* Shorthand for echo get_current_page_url();
* @returns echo'd string
*/
if ( ! function_exists( 'the_current_page_url' ) ) {
	function the_current_page_url() {
	  echo get_current_page_url();
	}
}

if ( ! function_exists( 'get_current_admin_page_url' ) ) {
	function get_current_admin_page_url() {
	  global $wp;
	  return add_query_arg( $_SERVER['QUERY_STRING'], '', admin_url( $wp->request ) );
	}
}

/*
* Shorthand for echo the_current_admin_page_url();
* @returns echo'd string
*/
if ( ! function_exists( 'the_current_admin_page_url' ) ) {
	function the_current_admin_page_url() {
	  echo get_current_admin_page_url();
	}
}
?>