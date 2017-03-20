<?php
/**
 * Plugin Name: UCI Results
 * Plugin URI: http://therunup.com
 * Description: Pulls in race results from the UCI website and adds it to your site.
 * Version: 0.2.0
 * Author: Erik Mitchell
 * Author URI: http://erikmitchell.net
 * Text Domain: uci-results
 */

define('UCI_RESULTS_PATH', plugin_dir_path(__FILE__));
define('UCI_RESULTS_URL', plugin_dir_url(__FILE__));
define('UCI_RESULTS_VERSION', '0.2.0');
define('UCI_RESULTS_ADMIN_PATH', plugin_dir_path(__FILE__).'admin/');
define('UCI_RESULTS_ADMIN_URL', plugin_dir_url(__FILE__).'admin/');

include_once(UCI_RESULTS_PATH.'classes/riders.php'); // our riders functions
include_once(UCI_RESULTS_PATH.'classes/rider-stats.php'); // rider stats function
include_once(UCI_RESULTS_PATH.'classes/rider-rankings-query.php'); // rider rankings query class
include_once(UCI_RESULTS_PATH.'classes/seasons.php');

include_once(UCI_RESULTS_PATH.'database.php'); // sets up our db tables
include_once(UCI_RESULTS_PATH.'functions/ajax.php'); // ajax functions
include_once(UCI_RESULTS_PATH.'functions/races.php'); // races functions
include_once(UCI_RESULTS_PATH.'functions/riders.php'); // riders functions
include_once(UCI_RESULTS_PATH.'functions/search.php'); // search functions
include_once(UCI_RESULTS_PATH.'functions/seasons.php'); // seasons functions
include_once(UCI_RESULTS_PATH.'functions/utility.php'); // utility functions
include_once(UCI_RESULTS_PATH.'functions/wp-query.php'); // modify wp query functions
include_once(UCI_RESULTS_PATH.'functions.php'); // generic functions

include_once(UCI_RESULTS_PATH.'init.php'); // functions to run on init

include_once(UCI_RESULTS_PATH.'admin/admin.php'); // admin page
include_once(UCI_RESULTS_PATH.'admin/notices.php'); // admin notices function
include_once(UCI_RESULTS_PATH.'admin/add-races.php'); // cURL and add races/results to db
include_once(UCI_RESULTS_PATH.'admin/rider-rankings.php'); // add and update rider rankings
include_once(UCI_RESULTS_PATH.'admin/wp-cli.php'); // wp cli functions
include_once(UCI_RESULTS_PATH.'admin/custom-columns.php'); // custom columns for our admin pages

include_once(UCI_RESULTS_PATH.'lib/name-parser.php'); // a php nameparser
include_once(UCI_RESULTS_PATH.'shortcode.php'); // our shortcodes
include_once(UCI_RESULTS_PATH.'lib/flags.php'); // our flag stuff
include_once(UCI_RESULTS_PATH.'cron.php'); // cron jobs
include_once(UCI_RESULTS_PATH.'update-to-twitter.php'); // updates results and rankings to twitter

include_once(UCI_RESULTS_PATH.'rest-api/uci-rest-controller.php'); // rest api controller
include_once(UCI_RESULTS_PATH.'rest-api/uci-rest-posts-controller.php'); // rest api posts
include_once(UCI_RESULTS_PATH.'rest-api/rest-api.php'); // basic rest api functions
include_once(UCI_RESULTS_PATH.'rest-api/filters.php'); // filters for rest api functions

/**
 * is_uci_results_active function.
 *
 * @access public
 * @return void
 */
function is_uci_results_active() {
	if (in_array('uci-results/uci-results.php', apply_filters('active_plugins', get_option('active_plugins'))))
		return true;

	return false;
}

/**
 * uci_results_activation function.
 *
 * @access public
 * @return void
 */
function uci_results_activation() {
	// schedule crons	//
	uci_results_schedule_event(current_time('timestamp'), 'threehours', 'uci_results_add_races');

	do_action('uci_results_activation');
}

/**
 * uci_results_deactivation function.
 *
 * @access public
 * @return void
 */
function uci_results_deactivation() {
	// remove crons //
	wp_clear_scheduled_hook('uci_results_add_races');

	do_action('uci_results_deactivation');
}

register_activation_hook(__FILE__, 'uci_results_activation');
register_deactivation_hook(__FILE__, 'uci_results_deactivation');

/**
 * uci_results_plugin_updater function.
 * 
 * @access public
 * @return void
 */
function uci_results_plugin_updater() {
	if (!is_admin())
		return false;

	if (!defined('WP_GITHUB_FORCE_UPDATE'))
		define('WP_GITHUB_FORCE_UPDATE', true);
		
	$username='erikdmitchell';
	$repo_name='uci-results';
	$folder_name='uci-results';
    
    $config = array(
        'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
        'proper_folder_name' => $folder_name, // this is the name of the folder your plugin lives in
        'api_url' => 'https://api.github.com/repos/'.$username.'/'.$repo_name, // the github API url of your github repo
        'raw_url' => 'https://raw.github.com/'.$username.'/'.$repo_name.'/master', // the github raw url of your github repo
        'github_url' => 'https://github.com/'.$username.'/'.$repo_name, // the github url of your github repo
        'zip_url' => 'https://github.com/'.$username.'/'.$repo_name.'/zipball/master', // the zip url of the github repo
        'sslverify' => true, // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
        'requires' => '4.0', // which version of WordPress does your plugin require?
        'tested' => '4.7', // which version of WordPress is your plugin tested up to?
        'readme' => 'readme.txt', // which file to use as the readme for the version number
    );
   
	new WP_GitHub_Updater($config);
}
add_action('admin_init', 'uci_results_plugin_updater');
?>
