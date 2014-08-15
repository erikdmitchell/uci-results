<?php
/**
 * Plugin Name: UCI cURL
 * Plugin URI: 
 * Description: Pulls in results via cURL from the UCI website.
 * Version: 1.0.4
 * Author: Erik Mitchell
 * Author URI: erikmitchell.net
 * License: 
 */

include_once(plugin_dir_path(__FILE__).'classes/uci-curl.php');
include_once(plugin_dir_path(__FILE__).'classes/field-quality.php');
include_once(plugin_dir_path(__FILE__).'classes/view-db.php');
include_once(plugin_dir_path(__FILE__).'classes/race-stats.php');

define('UCICURLBASE',plugin_dir_url(__FILE__));

$config=array(
	'url' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=485&StartDateSort=20130907&EndDateSort=20140223&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8'
);
$uci_curl=new Top25_cURL($config);
?>