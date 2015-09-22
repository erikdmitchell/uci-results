<?php
/**
 * Plugin Name: UCI cURL
 * Plugin URI: http://erikmitchell.net
 * Description: Pulls in results via cURL from the UCI website.
 * Version: 1.0.7
 * Author: Erik Mitchell
 * Author URI: http://erikmitchell.net
 * License:
 */

include_once(plugin_dir_path(__FILE__).'classes/databases.php');
include_once(plugin_dir_path(__FILE__).'classes/top25-curl.php');
include_once(plugin_dir_path(__FILE__).'classes/field-quality.php');
include_once(plugin_dir_path(__FILE__).'classes/view-db.php');
include_once(plugin_dir_path(__FILE__).'classes/race-stats.php');
include_once(plugin_dir_path(__FILE__).'classes/rider-stats.php');

define('UCICURLBASE',plugin_dir_url(__FILE__));


$config=array(
	'urls' => array(
		'2014/2015' => 'http://www.uci.infostradasports.com/asp/redirect/uci.asp?PageID=19004&SportID=306&CompetitionID=-1&EditionID=-1&SeasonID=-1&ClassID=1&GenderID=1&EventID=-1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&Phase3ID=0&CompetitionCodeInv=1&Detail=1&Ranking=0&All=0&TaalCode=2&StyleID=0&Cache=8',
	),
);


$uci_curl=new Top25_cURL($config);
?>