<?php

/**
 * uci_results_api_template_loader function.
 *
 * @access public
 * @param mixed $template
 * @return void
 */
function uci_results_api_template_loader($template) {
	global $post;

	$located=false;
	$template_slug='';

	// it's a page //
	if (is_page('api'))
		return UCICURL_PATH.'api/index.php';

	return $template;
}
add_filter('template_include', 'uci_results_api_template_loader');

/**
 * uci_results_api_rewrite_rules function.
 *
 * @access public
 * @return void
 */
function uci_results_api_rewrite_rules() {
	add_rewrite_rule('([^/]*)/?', 'index.php?pagename=api&controller=$matches[1]', 'top');
	add_rewrite_rule('([^/]*)/([^/]*)/?', 'index.php?pagename=api&controller=$matches[1]&action=$matches[2]', 'top');
}
add_action('init', 'uci_results_api_rewrite_rules', 10, 0);

/**
 * uci_results_api_register_query_vars function.
 *
 * @access public
 * @param mixed $vars
 * @return void
 */
function uci_results_api_register_query_vars( $vars ) {
  $vars[]='controller';
  $vars[]='action';

  return $vars;
}
add_filter('query_vars', 'uci_results_api_register_query_vars');
?>