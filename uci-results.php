<?php
/**
 * Plugin Name: UCI Results
 * Plugin URI: http://therunup.com
 * Description: Pulls in information from the UCI website and adds it to your site.
 * Version: 0.1.0
 * Author: Erik Mitchell
 * Author URI: http://erikmitchell.net
 * Text Domain: uci-results
 */

define('UCI_RESULTS_PATH', plugin_dir_path(__FILE__));
define('UCI_RESULTS_URL', plugin_dir_url(__FILE__));

include_once(UCI_RESULTS_PATH.'classes/races.php'); // our races functions
include_once(UCI_RESULTS_PATH.'classes/riders.php'); // our riders functions
include_once(UCI_RESULTS_PATH.'classes/uci-results-query.php'); // our query class and pagination
include_once(UCI_RESULTS_PATH.'database.php'); // sets up our db tables
include_once(UCI_RESULTS_PATH.'functions.php'); // all our outlying functions
include_once(UCI_RESULTS_PATH.'init.php'); // functions to run on init
include_once(UCI_RESULTS_PATH.'admin/admin-pages.php'); // admin page
include_once(UCI_RESULTS_PATH.'admin/add-races.php'); // cURL and add races/results to db
include_once(UCI_RESULTS_PATH.'admin/rider-rankings.php'); // add and update rider rankings
include_once(UCI_RESULTS_PATH.'admin/wp-cli.php'); // wp cli functions
include_once(UCI_RESULTS_PATH.'lib/name-parser.php'); // a php nameparser
include_once(UCI_RESULTS_PATH.'shortcode.php'); // our shortcodes
include_once(UCI_RESULTS_PATH.'lib/flags.php'); // our flag stuff
include_once(UCI_RESULTS_PATH.'cron.php'); // cron jobs
include_once(UCI_RESULTS_PATH.'api/api.php'); // api
include_once(UCI_RESULTS_PATH.'update-to-twitter.php'); // updates results and rankings to twitter

/**
 * is_uci_results_active function.
 *
 * @access public
 * @return void
 */
function is_uci_results_active() {
	if ( in_array( 'uci-results/uci-results.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
		return true;

	return false;
}

function uci_results_activation() {
	// schedule crons	//
	uci_results_schedule_event(current_time('timestamp'), 'daily', 'uci_results_add_races');

	do_action('uci_results_activation');
}

function uci_results_deactivation() {
	// remove crons //
	wp_clear_scheduled_hook('fantasy_cycling_cron_lock_teams');

	do_action('uci_results_deactivation');
}
register_activation_hook(__FILE__, 'uci_results_activation');
register_deactivation_hook(__FILE__, 'uci_results_deactivation');
?>