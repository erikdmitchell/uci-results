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

function ucicurl_rider_url($slug='') {
	$url="/rider/$slug";

	echo $url;
}

function ucicurl_race_url($slug='') {
	$url='';

	echo $url;
}

function ucicurl_country_url($slug='') {
	$url='';

	echo $url;
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