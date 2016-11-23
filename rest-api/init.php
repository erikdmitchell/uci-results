<?php
define('UCI_RESULTS_API_PATH', plugin_dir_path(__FILE__));
define('UCI_RESULTS_API_URL', plugin_dir_url(__FILE__));	

include_once(UCI_RESULTS_API_PATH.'post-types/riders.php');
include_once(UCI_RESULTS_API_PATH.'post-types/races.php');
include_once(UCI_RESULTS_API_PATH.'metaboxes/riders.php');
include_once(UCI_RESULTS_API_PATH.'metaboxes/races.php');
include_once(UCI_RESULTS_API_PATH.'metaboxes/race-results.php');
include_once(UCI_RESULTS_API_PATH.'taxonomies.php');
include_once(UCI_RESULTS_API_PATH.'functions.php');
include_once(UCI_RESULTS_API_PATH.'api-requests.php');
?>