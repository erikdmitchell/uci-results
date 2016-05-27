<?php

/**
 * ulm_results_rewrites function.
 *
 * @access public
 * @return void
 */
function ulm_results_rewrites() {
	global $ulm_sections;

	// results //
	add_rewrite_rule('^results/([^/]*)/?', 'index.php?pagename=results&ulm_event_slug=$matches[1]', 'top');
	add_rewrite_rule('^'.$ulm_sections['players']['slug'].'/results/([^/]*)/?', 'index.php?pagename=results&ulm_player_slug=$matches[1]', 'top');
}
add_action('init', 'ulm_results_rewrites');

/**
 * ulm_results_query_vars function.
 *
 * @access public
 * @param mixed $vars
 * @return void
 */
function ulm_results_query_vars($vars) {
  $vars[] = 'ulm_event_slug';
  $vars[] = 'ulm_player_slug';

  return $vars;
}
add_filter('query_vars', 'ulm_results_query_vars');
?>