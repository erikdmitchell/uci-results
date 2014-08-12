<?php
/**
 * Plugin Name: UCI cURL
 * Plugin URI: 
 * Description: Pulls in results via cURL from the UCI website.
 * Version: 1.0.2
 * Author: Erik Mitchell
 * Author URI: erikmitchell.net
 * License: 
 */

include_once(plugin_dir_path(__FILE__).'classes/uci-curl.php');
include_once(plugin_dir_path(__FILE__).'classes/field-quality.php');
include_once(plugin_dir_path(__FILE__).'classes/view-db.php');

define('UCICURLBASE',plugin_dir_url(__FILE__));
?>