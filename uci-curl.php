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

include_once(UCICURL_PATH.'database.php'); // sets up our db tables
include_once(UCICURL_PATH.'functions.php'); // all our outlying functions
include_once(UCICURL_PATH.'classes/admin.php'); // our admin panel and curl functions
include_once(UCICURL_PATH.'lib/name-parser.php'); // a php nameparser
include_once(UCICURL_PATH.'classes/races.php'); // our races functions
include_once(UCICURL_PATH.'classes/riders.php'); // our riders functions
include_once(UCICURL_PATH.'classes/pagination.php'); // our pagination functions
include_once(UCICURL_PATH.'shortcode.php'); // our shortcodes
include_once(UCICURL_PATH.'flasgs.php'); // our flag stuff
?>