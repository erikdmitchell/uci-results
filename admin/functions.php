<?php

/**
 * uci_results_get_admin_page function.
 * 
 * @access public
 * @param bool $template_name (default: false)
 * @return void
 */
function uci_results_get_admin_page($template_name=false) {
	$html=null;
	
	if (!$template_name)
		return false;

	ob_start();

	if (file_exists(UCI_RESULTS_PATH."adminpages/$template_name.php"))
		include_once(UCI_RESULTS_PATH."adminpages/$template_name.php");

	$html=ob_get_contents();

	ob_end_clean();

	return $html;
}	
?>