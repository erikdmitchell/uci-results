<?php
define('UCI_RESULTS_API_PATH', plugin_dir_path(__FILE__));
define('UCI_RESULTS_API_URL', plugin_dir_url(__FILE__));	

include_once(UCI_RESULTS_API_PATH.'riders-post-type.php');
include_once(UCI_RESULTS_API_PATH.'races-post-type.php');
include_once(UCI_RESULTS_API_PATH.'riders-metabox.php');
include_once(UCI_RESULTS_API_PATH.'races-metabox.php');
include_once(UCI_RESULTS_API_PATH.'riders-taxonomies.php');
?>