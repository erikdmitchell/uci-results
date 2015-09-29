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
include_once(plugin_dir_path(__FILE__).'shortcodes.php');

define('UCICURLBASE',plugin_dir_url(__FILE__));


$config=array(
	'urls' => array(
		'2014/2015' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&SportID=306&CompetitionID=-1&EditionID=-1&SeasonID=-1&ClassID=1&GenderID=1&EventID=-1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&Phase3ID=0&CompetitionCodeInv=1&Detail=1&Ranking=0&All=0&TaalCode=2&StyleID=0&Cache=8',
	),
);


$uci_curl=new Top25_cURL($config);

// generic functions //





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
?>