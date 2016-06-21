<?php
/**
 * uci_results_init function.
 *
 * @access public
 * @return void
 */
function uci_results_init() {
	global $uci_results_pages;

	$uci_results_pages=array();
	$uci_results_pages['single_rider'] = get_option('single_rider_page_id', 0);
}
add_action('init', 'uci_results_init', 1);

/**
 * uci_results_rewrite_rules function.
 *
 * @access public
 * @return void
 */
function uci_results_rewrite_rules() {
	global $uci_results_pages;

	$single_rider_url=ltrim(str_replace( home_url(), "", get_permalink($uci_results_pages['single_rider'])), '/');

	add_rewrite_rule($single_rider_url.'([^/]*)/?', 'index.php?page_id='.$uci_results_pages['single_rider'].'&rider_slug=$matches[1]', 'top');
}
add_action('init', 'uci_results_rewrite_rules', 10, 0);

/**
 * uci_results_register_query_vars function.
 *
 * @access public
 * @param mixed $vars
 * @return void
 */
function uci_results_register_query_vars( $vars ) {
  $vars[] = 'rider_slug';

  return $vars;
}
add_filter( 'query_vars', 'uci_results_register_query_vars');
?>