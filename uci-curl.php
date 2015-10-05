<?php
/**
 * Plugin Name: UCI cURL
 * Plugin URI: http://erikmitchell.net
 * Description: Pulls in results via cURL from the UCI website.
 * Version: 1.1.0
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

		'2014/2015' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=487&StartDateSort=20140830&EndDateSort=20150809&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
		'2015/2016' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&SportID=306&CompetitionID=-1&EditionID=-1&SeasonID=-1&ClassID=1&GenderID=1&EventID=-1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&Phase3ID=0&CompetitionCodeInv=1&Detail=1&Ranking=0&All=0&TaalCode=2&StyleID=0&Cache=8',
	),
);
$uci_curl=new Top25_cURL($config); // this may be initated in the top25 file at some point

add_action('plugins_loaded',array('Page_Templates','get_instance')); // enables our templates to be added


// url for click throughs
function single_rider_link($rider=false,$season=false) {
	if (!$rider || !$season)
		return false;

	return 'rider/?rider='.urlencode($rider).'&season='.urlencode($season);
}
function single_country_link($country=false,$season=false) {
	if (!$country || !$season)
		return '#';

	return 'country/?country='.$country.'&season='.urlencode($season);
}
function single_race_link($code=false) {
	if (!$code)
		return false;
	return 'race/?race='.urlencode($code);
}




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
	wp_enqueue_script('uci-curl-core',plugins_url('/js/core.js',__FILE__),array('jquery'));
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

/**
 * uci_curl_pagination function.
 *
 * @access public
 * @return void
 */
function uci_curl_pagination() {
	global $wp_query;

	$paged=get_query_var('paged',1);
	$season=get_query_var('season','2015/2016');

	if ($paged==0)
		$paged=1;

	$html=null;
	$prev_page=$paged-1;
	$next_page=$paged+1;
	$per_page=15; // should be pulled from a central location so user can update
	$ppc=null;
	$npc=null;
	$max_pages=ceil($wp_query->uci_curl_max_pages/$per_page);

	if ($paged==1)
		$ppc='hide-opacity';

	if ($paged==$max_pages || $max_pages==0)
		$npc='hide-opacity';

	$html.='<div class="rider-pagination uci-pagination">';
		$html.='<div class="prev-page"><a class="'.$ppc.'" href="?paged='.$prev_page.'&season='.urlencode($season).'">Previous</a></div>';
		$html.='<div class="next-page"><a class="'.$npc.'" href="?paged='.$next_page.'&season='.urlencode($season).'">Next</a></div>';
	$html.='</div>';

	echo $html;
}

/**
 * uci_curl_add_pages function.
 *
 * @access public
 * @return void
 */
function uci_curl_add_pages() {
	$uci_cross=array(
		'post_content' => '',
		'post_title' => 'UCI Cross Rankings',
		'post_status' => 'publish',
		'post_type' => 'page',
		'post_parent' => 0,
		'page_template' => 'cross.php',
	);
	$uci_cross_id=wp_insert_post($riders_rankings);

	$riders_rankings=array(
		'post_content' => 'No content will be displayed with the usage of the Rider Rankings template.',
		'post_title' => 'Rider Rankings',
		'post_status' => 'publish',
		'post_type' => 'page',
		'post_parent' => $uci_cross_id,
		'page_template' => 'rider-rankings.php',
	);
	$riders_rankings_id=wp_insert_post($riders_rankings);

	$riders_single=array(
		'post_content' => 'No content will be displayed with the usage of the Rider (Single) template.',
		'post_title' => 'Rider',
		'post_status' => 'publish',
		'post_type' => 'page',
		'post_parent' => $riders_rankings_id,
		'page_template' => 'rider.php',
	);
	$riders_single_id=wp_insert_post($riders_single);

	$country_single=array(
		'post_content' => 'No content will be displayed with the usage of the Race Rankings template.',
		'post_title' => 'Country',
		'post_status' => 'publish',
		'post_type' => 'page',
		'post_parent' => $riders_rankings_id,
		'page_template' => 'country.php',
	);
	$country_single_id=wp_insert_post($country_single);

	$race_rankings=array(
		'post_content' => 'No content will be displayed with the usage of the Race (Single) template.',
		'post_title' => 'Race Rankings',
		'post_status' => 'publish',
		'post_type' => 'page',
		'post_parent' => $uci_cross_id,
		'page_template' => 'race-rankings.php',
	);
	$race_rankings_id=wp_insert_post($riders_rankings);

	$race_single=array(
		'post_content' => 'No content will be displayed with the usage of the Rider Rankings template.',
		'post_title' => 'Race',
		'post_status' => 'publish',
		'post_type' => 'page',
		'post_parent' => $race_rankings_id,
		'page_template' => 'race.php',
	);
	$race_single_id=wp_insert_post($riders_single);
}

/**
 * uci_curl_add_query_vars function.
 *
 * custom query vars for use in our template(s)
 *
 * @access public
 * @param mixed $vars
 * @return void
 */
function uci_curl_add_query_vars($vars) {
	$vars[]='rider';
	$vars[]='season';
	$vars[]='country';
	$vars[]='race';
	$vars[]='uci_curl_max_pages';

	return $vars;
}
add_filter('query_vars','uci_curl_add_query_vars');
?>