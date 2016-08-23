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

/**
 * uci_results_api_page_rewrite function.
 *
 * @access public
 * @return void
 */
function uci_results_api_page_rewrite() {
  global $wp_rewrite;

  //set up our query variable %test% which equates to index.php?test=
  add_rewrite_tag( '%api%', '([^&]+)');

  //add rewrite rule that matches /test
  add_rewrite_rule('^api/?','index.php?api=api','top');

  //add endpoint, in this case 'test' to satisfy our rewrite rule /test
  add_rewrite_endpoint( 'api', EP_PERMALINK | EP_PAGES );

  //flush rules to get this to work properly (do this once, then comment out)
  $wp_rewrite->flush_rules();
}
add_action('init', 'uci_results_api_page_rewrite');
?>