<?php
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

/**
 * array_to_object function.
 * 
 * @access public
 * @param mixed $array
 * @return void
 */
function array_to_object($array) {
    $object = new stdClass();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $value = array_to_object($value);
        }
        $object->$key = $value;
    }
    return $object;
}

/**
 * uci_results_format_size function.
 * 
 * @access public
 * @param string $size (default: '')
 * @return void
 */
function uci_results_format_size($size='') {
	$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
	
    if ($size == 0) : 
	    return('n/a');
	else :
      	return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);
    endif;
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

/**
 * uci_results_parse_args function.
 * 
 * @access public
 * @param mixed &$a
 * @param mixed $b
 * @return void
 */
function uci_results_parse_args( &$a, $b ) {
	$a = (array) $a;
	$b = (array) $b;
	$result = $b;
	foreach ( $a as $k => &$v ) {
		if ( is_array( $v ) && isset( $result[ $k ] ) ) {
			$result[ $k ] = uci_results_parse_args( $v, $result[ $k ] );
		} else {
			$result[ $k ] = $v;
		}
	}

	return $result;
}
 
?>