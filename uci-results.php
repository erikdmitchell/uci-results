<?php
/**
 * Plugin Name: UCI cURL
 * Plugin URI: http://erikmitchell.net
 * Description: Pulls in results via cURL from the UCI website.
 * Version: 2.0.0
 * Author: Erik Mitchell
 * Author URI: http://erikmitchell.net
 * Text Domain: uci-curl
 */

define('UCICURL_PATH', plugin_dir_path(__FILE__));
define('UCICURL_URL', plugin_dir_url(__FILE__));

include_once(UCICURL_PATH.'classes/races.php'); // our races functions
include_once(UCICURL_PATH.'classes/riders.php'); // our riders functions
include_once(UCICURL_PATH.'classes/uci-results-query.php'); // our query class and pagination
include_once(UCICURL_PATH.'database.php'); // sets up our db tables
include_once(UCICURL_PATH.'functions.php'); // all our outlying functions
include_once(UCICURL_PATH.'init.php'); // functions to run on init
include_once(UCICURL_PATH.'admin/admin-pages.php'); // admin page
include_once(UCICURL_PATH.'admin/add-races.php'); // cURL and add races/results to db
include_once(UCICURL_PATH.'admin/rider-rankings.php'); // add and update rider rankings
include_once(UCICURL_PATH.'admin/wp-cli.php'); // wp cli functions
include_once(UCICURL_PATH.'lib/name-parser.php'); // a php nameparser
include_once(UCICURL_PATH.'shortcode.php'); // our shortcodes
include_once(UCICURL_PATH.'lib/flags.php'); // our flag stuff
include_once(UCICURL_PATH.'cron.php'); // cron jobs
include_once(UCICURL_PATH.'api/api.php'); // api
include_once(UCICURL_PATH.'update-to-twitter.php'); // updates results and rankings to twitter

/**
 * is_uci_results_active function.
 *
 * @access public
 * @return void
 */
function is_uci_results_active() {
	if ( in_array( 'uci-curl-wp-plugin/uci-curl.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
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