<?php

function ucicurl_results_rewrites() {
	//add_rewrite_rule('^results/([^/]*)/?', 'index.php?pagename=ucicurl-rider&ucicurl_rider_slug=$matches[1]', 'top');
	add_rewrite_rule('^race/([^/]*)/?', 'index.php?pagename=race&ucicurl_race_slug=$matches[1]', 'top');
	add_rewrite_rule('^country/([^/]*)/?', 'index.php?pagename=country&ucicurl_country_slug=$matches[1]', 'top');
}
add_action('init', 'ucicurl_results_rewrites');

function ucicurl_results_query_vars($vars) {
  $vars[] = 'ucicurl_rider_slug';
  $vars[] = 'ucicurl_race_slug';
  $vars[] = 'ucicurl_country_slug';

  return $vars;
}
add_filter('query_vars', 'ucicurl_results_query_vars');
?>