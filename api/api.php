<?php
/**
 * uci_results_api_template_loader function.
 *
 * @access public
 * @param mixed $template
 * @return void
 */
function uci_results_api_template_loader($template) {
	global $wp;

	if ($wp->request=='api')
		return UCI_RESULTS_PATH.'api/index.php';

	return $template;
}
add_filter('template_include', 'uci_results_api_template_loader');
?>