<?php
/**
 * Plugin Name: UCI cURL
 * Plugin URI: http://erikmitchell.net
 * Description: Pulls in results via cURL from the UCI website.
 * Version: 1.0.8
 * Author: Erik Mitchell
 * Author URI: http://erikmitchell.net
 * License: GPL2
 */

include_once(plugin_dir_path(__FILE__).'classes/databases.php');
include_once(plugin_dir_path(__FILE__).'classes/top25-curl.php');
include_once(plugin_dir_path(__FILE__).'classes/field-quality.php');
include_once(plugin_dir_path(__FILE__).'classes/view-db.php');
include_once(plugin_dir_path(__FILE__).'classes/race-stats.php');
include_once(plugin_dir_path(__FILE__).'classes/rider-stats.php');
require_once(plugin_dir_path(__FILE__).'page-templating.php' );
include_once(plugin_dir_path(__FILE__).'shortcodes.php');

define('UCICURLBASE',plugin_dir_url(__FILE__));


$config=array(
	'urls' => array(
		'2014/2015' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&SportID=306&CompetitionID=-1&EditionID=-1&SeasonID=-1&ClassID=1&GenderID=1&EventID=-1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&Phase3ID=0&CompetitionCodeInv=1&Detail=1&Ranking=0&All=0&TaalCode=2&StyleID=0&Cache=8',
	),
);
$uci_curl=new Top25_cURL($config); // this may be initated in the top25 file at some point

add_action('plugins_loaded',array('Page_Templates','get_instance')); // enables our templates to be added




/**
 * ucicurl_activate function.
 *
 * @access public
 * @return void
 */
function ucicurl_activate() {
	uci_curl_add_pages();
}
register_activation_hook(__FILE__,'ucicurl_activate');
//add_action('wp_loaded','ucicurl_activate');

/**
 * uci_curl_scripts_styles function.
 *
 * @access public
 * @param mixed $hook
 * @return void
 */
function uci_curl_scripts_styles($hook) {
	wp_enqueue_style('uci-curl-style',plugins_url('/css/user.css',__FILE__));
}
add_action('wp_enqueue_scripts','uci_curl_scripts_styles');

/**
 * object_slice function.
 *
 * @access public
 * @param bool $obj (default: false)
 * @param int $start (default: 0)
 * @param int $end (default: 10)
 * @return void
 */
function object_slice($obj=false,$start=0,$end=10) {
	if (!$obj)
		return false;

	$counter=0;
	$obj_arr=(array) $obj;
	$obj_arr=array_slice($obj_arr,$start,$end);

	return json_decode(json_encode($obj_arr),FALSE);
}

//----- add pages to site ----- //

/**
 * uci_curl_add_pages function.
 *
 * @access public
 * @return void
 */
function uci_curl_add_pages() {
	$pages=array(
		'riders-rankings' => array(
			'post_content' => 'No content will be displayed with the usage of the Rider Rankings template.',
		  'post_title' => 'Rider Rankings',
		  'post_status' => 'publish',
		  'post_type' => 'page',
		  'post_parent' => 0,
		  'page_template' => 'rider-rankings.php',
		),
	);

	foreach ($pages as $page) :
		wp_insert_post($page);
	endforeach;
}
?>