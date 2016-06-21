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
add_action('init', 'uci_results_init');
?>