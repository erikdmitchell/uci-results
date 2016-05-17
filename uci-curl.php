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


// need to be auto done or something //
$core_page_slug='uci-cross-rankings';
$rider_page_slug='rider-rankings';
$race_page_slug='race-rankings';

// url for click throughs

/**
 * single_rider_link function.
 *
 * @access public
 * @param bool $rider (default: false)
 * @param bool $season (default: false)
 * @return void
 */
function single_rider_link($rider=false,$season=false) {
	global $core_page_slug,$rider_page_slug;

	if (!$rider || !$season)
		return false;

	return "/{$core_page_slug}/{$rider_page_slug}/rider/?rider=".urlencode($rider)."&season=".urlencode($season);
}

/**
 * single_country_link function.
 *
 * @access public
 * @param bool $country (default: false)
 * @param bool $season (default: false)
 * @return void
 */
function single_country_link($country=false,$season=false) {
	global $core_page_slug,$rider_page_slug;

	if (!$country || !$season)
		return '#';

	return "/{$core_page_slug}/{$rider_page_slug}/country/?country={$country}&season=".urlencode($season);
}

/**
 * single_race_link function.
 *
 * @access public
 * @param bool $code (default: false)
 * @return void
 */
function single_race_link($code=false) {
	global $core_page_slug,$race_page_slug;

	if (!$code)
		return false;

	return "/{$core_page_slug}/{$race_page_slug}/race/?race=".urlencode($code);
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

/**
 * uci_curl_scripts_styles function.
 *
 * @access public
 * @param mixed $hook
 * @return void
 */
function uci_curl_scripts_styles($hook) {
	wp_enqueue_style('flag-icon-css',plugins_url('/css/flag-icon.min.css',__FILE__),array(),'0.8.2');
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

function uci_template_scripts_styles() {
	global $RiderStats,$wpdb;

	if ( is_page_template('rider.php') ) :
		wp_register_script('uci-riders-script',plugins_url('/js/rider.js',__FILE__),array('chart-js'));

		$wpOptions=array(
			'rider' => $RiderStats->get_riders(array(
				'name' => $_GET['rider'],
				'season' => $_GET['season'],
				'order_by' => 'week',
				'order' => 'ASC',
			))
		);

		wp_localize_script('uci-riders-script','wpOptions',$wpOptions);

		wp_enqueue_script('chart-js','https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js',array('jquery'),'1.0.2');
		wp_enqueue_script('uci-riders-script');
	endif;
}
add_action('wp_enqueue_scripts','uci_template_scripts_styles');

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
 * uci_get_current_week function.
 *
 * @access public
 * @param bool $season (default: false)
 * @return void
 */
function uci_get_current_week($season=false) {
	global $wpdb,$uci_curl;

	if (!$season)
		return false;

	$week=$wpdb->get_var("SELECT MAX(week) FROM $uci_curl->weekly_rider_rankings_table WHERE season='{$season}'");

	return $week;
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


/**
 * get_country_flag function.
 *
 * @access public
 * @param bool $country (default: false)
 * @param bool $addon (default: false)
 * @return void
 */
function get_country_flag($country=false,$addon=false) {
	if (!$country)
		return false;

	$html=null;
	$countries_arr=array(
	  "AF" => array("AFGHANISTAN", "AF", "AFG", "004"),
	  "AL" => array("ALBANIA", "AL", "ALB", "008"),
	  "DZ" => array("ALGERIA", "DZ", "DZA", "012"),
	  "AS" => array("AMERICAN SAMOA", "AS", "ASM", "016"),
	  "AD" => array("ANDORRA", "AD", "AND", "020"),
	  "AO" => array("ANGOLA", "AO", "AGO", "024"),
	  "AI" => array("ANGUILLA", "AI", "AIA", "660"),
	  "AQ" => array("ANTARCTICA", "AQ", "ATA", "010"),
	  "AG" => array("ANTIGUA AND BARBUDA", "AG", "ATG", "028"),
	  "AR" => array("ARGENTINA", "AR", "ARG", "032"),
	  "AM" => array("ARMENIA", "AM", "ARM", "051"),
	  "AW" => array("ARUBA", "AW", "ABW", "533"),
	  "AU" => array("AUSTRALIA", "AU", "AUS", "036"),
	  "AT" => array("AUSTRIA", "AT", "AUT", "040"),
	  "AZ" => array("AZERBAIJAN", "AZ", "AZE", "031"),
	  "BS" => array("BAHAMAS", "BS", "BHS", "044"),
	  "BH" => array("BAHRAIN", "BH", "BHR", "048"),
	  "BD" => array("BANGLADESH", "BD", "BGD", "050"),
	  "BB" => array("BARBADOS", "BB", "BRB", "052"),
	  "BY" => array("BELARUS", "BY", "BLR", "112"),
	  "BE" => array("BELGIUM", "BE", "BEL", "056"),
	  "BZ" => array("BELIZE", "BZ", "BLZ", "084"),
	  "BJ" => array("BENIN", "BJ", "BEN", "204"),
	  "BM" => array("BERMUDA", "BM", "BMU", "060"),
	  "BT" => array("BHUTAN", "BT", "BTN", "064"),
	  "BO" => array("BOLIVIA", "BO", "BOL", "068"),
	  "BA" => array("BOSNIA AND HERZEGOVINA", "BA", "BIH", "070"),
	  "BW" => array("BOTSWANA", "BW", "BWA", "072"),
	  "BV" => array("BOUVET ISLAND", "BV", "BVT", "074"),
	  "BR" => array("BRAZIL", "BR", "BRA", "076"),
	  "IO" => array("BRITISH INDIAN OCEAN TERRITORY", "IO", "IOT", "086"),
	  "BN" => array("BRUNEI DARUSSALAM", "BN", "BRN", "096"),
	  "BG" => array("BULGARIA", "BG", "BGR", "100"),
	  "BF" => array("BURKINA FASO", "BF", "BFA", "854"),
	  "BI" => array("BURUNDI", "BI", "BDI", "108"),
	  "KH" => array("CAMBODIA", "KH", "KHM", "116"),
	  "CM" => array("CAMEROON", "CM", "CMR", "120"),
	  "CA" => array("CANADA", "CA", "CAN", "124"),
	  "CV" => array("CAPE VERDE", "CV", "CPV", "132"),
	  "KY" => array("CAYMAN ISLANDS", "KY", "CYM", "136"),
	  "CF" => array("CENTRAL AFRICAN REPUBLIC", "CF", "CAF", "140"),
	  "TD" => array("CHAD", "TD", "TCD", "148"),
	  "CL" => array("CHILE", "CL", "CHL", "152"),
	  "CN" => array("CHINA", "CN", "CHN", "156"),
	  "CX" => array("CHRISTMAS ISLAND", "CX", "CXR", "162"),
	  "CC" => array("COCOS (KEELING) ISLANDS", "CC", "CCK", "166"),
	  "CO" => array("COLOMBIA", "CO", "COL", "170"),
	  "KM" => array("COMOROS", "KM", "COM", "174"),
	  "CG" => array("CONGO", "CG", "COG", "178"),
	  "CK" => array("COOK ISLANDS", "CK", "COK", "184"),
	  "CR" => array("COSTA RICA", "CR", "CRI", "188"),
	  "CI" => array("COTE D'IVOIRE", "CI", "CIV", "384"),
	  "HR" => array("CROATIA (local name: Hrvatska)", "HR", "HRV", "191"),
	  "CU" => array("CUBA", "CU", "CUB", "192"),
	  "CY" => array("CYPRUS", "CY", "CYP", "196"),
	  "CZ" => array("CZECH REPUBLIC", "CZ", "CZE", "203"),
	  "DK" => array("DENMARK", "DK", "DNK", "208"),
	  "DJ" => array("DJIBOUTI", "DJ", "DJI", "262"),
	  "DM" => array("DOMINICA", "DM", "DMA", "212"),
	  "DO" => array("DOMINICAN REPUBLIC", "DO", "DOM", "214"),
	  "TL" => array("EAST TIMOR", "TL", "TLS", "626"),
	  "EC" => array("ECUADOR", "EC", "ECU", "218"),
	  "EG" => array("EGYPT", "EG", "EGY", "818"),
	  "SV" => array("EL SALVADOR", "SV", "SLV", "222"),
	  "GQ" => array("EQUATORIAL GUINEA", "GQ", "GNQ", "226"),
	  "ER" => array("ERITREA", "ER", "ERI", "232"),
	  "EE" => array("ESTONIA", "EE", "EST", "233"),
	  "ET" => array("ETHIOPIA", "ET", "ETH", "210"),
	  "FK" => array("FALKLAND ISLANDS (MALVINAS)", "FK", "FLK", "238"),
	  "FO" => array("FAROE ISLANDS", "FO", "FRO", "234"),
	  "FJ" => array("FIJI", "FJ", "FJI", "242"),
	  "FI" => array("FINLAND", "FI", "FIN", "246"),
	  "FR" => array("FRANCE", "FR", "FRA", "250"),
	  "FX" => array("FRANCE, METROPOLITAN", "FX", "FXX", "249"),
	  "GF" => array("FRENCH GUIANA", "GF", "GUF", "254"),
	  "PF" => array("FRENCH POLYNESIA", "PF", "PYF", "258"),
	  "TF" => array("FRENCH SOUTHERN TERRITORIES", "TF", "ATF", "260"),
	  "GA" => array("GABON", "GA", "GAB", "266"),
	  "GM" => array("GAMBIA", "GM", "GMB", "270"),
	  "GE" => array("GEORGIA", "GE", "GEO", "268"),
	  "DE" => array("GERMANY", "DE", "DEU", "276"),
	  "GH" => array("GHANA", "GH", "GHA", "288"),
	  "GI" => array("GIBRALTAR", "GI", "GIB", "292"),
	  "GR" => array("GREECE", "GR", "GRC", "300"),
	  "GL" => array("GREENLAND", "GL", "GRL", "304"),
	  "GD" => array("GRENADA", "GD", "GRD", "308"),
	  "GP" => array("GUADELOUPE", "GP", "GLP", "312"),
	  "GU" => array("GUAM", "GU", "GUM", "316"),
	  "GT" => array("GUATEMALA", "GT", "GTM", "320"),
	  "GN" => array("GUINEA", "GN", "GIN", "324"),
	  "GW" => array("GUINEA-BISSAU", "GW", "GNB", "624"),
	  "GY" => array("GUYANA", "GY", "GUY", "328"),
	  "HT" => array("HAITI", "HT", "HTI", "332"),
	  "HM" => array("HEARD ISLAND & MCDONALD ISLANDS", "HM", "HMD", "334"),
	  "HN" => array("HONDURAS", "HN", "HND", "340"),
	  "HK" => array("HONG KONG", "HK", "HKG", "344"),
	  "HU" => array("HUNGARY", "HU", "HUN", "348"),
	  "IS" => array("ICELAND", "IS", "ISL", "352"),
	  "IN" => array("INDIA", "IN", "IND", "356"),
	  "ID" => array("INDONESIA", "ID", "IDN", "360"),
	  "IR" => array("IRAN, ISLAMIC REPUBLIC OF", "IR", "IRN", "364"),
	  "IQ" => array("IRAQ", "IQ", "IRQ", "368"),
	  "IE" => array("IRELAND", "IE", "IRL", "372"),
	  "IL" => array("ISRAEL", "IL", "ISR", "376"),
	  "IT" => array("ITALY", "IT", "ITA", "380"),
	  "JM" => array("JAMAICA", "JM", "JAM", "388"),
	  "JP" => array("JAPAN", "JP", "JPN", "392"),
	  "JO" => array("JORDAN", "JO", "JOR", "400"),
	  "KZ" => array("KAZAKHSTAN", "KZ", "KAZ", "398"),
	  "KE" => array("KENYA", "KE", "KEN", "404"),
	  "KI" => array("KIRIBATI", "KI", "KIR", "296"),
	  "KP" => array("KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF", "KP", "PRK", "408"),
	  "KR" => array("KOREA, REPUBLIC OF", "KR", "KOR", "410"),
	  "KW" => array("KUWAIT", "KW", "KWT", "414"),
	  "KG" => array("KYRGYZSTAN", "KG", "KGZ", "417"),
	  "LA" => array("LAO PEOPLE'S DEMOCRATIC REPUBLIC", "LA", "LAO", "418"),
	  "LV" => array("LATVIA", "LV", "LVA", "428"),
	  "LB" => array("LEBANON", "LB", "LBN", "422"),
	  "LS" => array("LESOTHO", "LS", "LSO", "426"),
	  "LR" => array("LIBERIA", "LR", "LBR", "430"),
	  "LY" => array("LIBYAN ARAB JAMAHIRIYA", "LY", "LBY", "434"),
	  "LI" => array("LIECHTENSTEIN", "LI", "LIE", "438"),
	  "LT" => array("LITHUANIA", "LT", "LTU", "440"),
	  "LU" => array("LUXEMBOURG", "LU", "LUX", "442"),
	  "MO" => array("MACAU", "MO", "MAC", "446"),
	  "MK" => array("MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF", "MK", "MKD", "807"),
	  "MG" => array("MADAGASCAR", "MG", "MDG", "450"),
	  "MW" => array("MALAWI", "MW", "MWI", "454"),
	  "MY" => array("MALAYSIA", "MY", "MYS", "458"),
	  "MV" => array("MALDIVES", "MV", "MDV", "462"),
	  "ML" => array("MALI", "ML", "MLI", "466"),
	  "MT" => array("MALTA", "MT", "MLT", "470"),
	  "MH" => array("MARSHALL ISLANDS", "MH", "MHL", "584"),
	  "MQ" => array("MARTINIQUE", "MQ", "MTQ", "474"),
	  "MR" => array("MAURITANIA", "MR", "MRT", "478"),
	  "MU" => array("MAURITIUS", "MU", "MUS", "480"),
	  "YT" => array("MAYOTTE", "YT", "MYT", "175"),
	  "MX" => array("MEXICO", "MX", "MEX", "484"),
	  "FM" => array("MICRONESIA, FEDERATED STATES OF", "FM", "FSM", "583"),
	  "MD" => array("MOLDOVA, REPUBLIC OF", "MD", "MDA", "498"),
	  "MC" => array("MONACO", "MC", "MCO", "492"),
	  "MN" => array("MONGOLIA", "MN", "MNG", "496"),
	  "MS" => array("MONTSERRAT", "MS", "MSR", "500"),
	  "MA" => array("MOROCCO", "MA", "MAR", "504"),
	  "MZ" => array("MOZAMBIQUE", "MZ", "MOZ", "508"),
	  "MM" => array("MYANMAR", "MM", "MMR", "104"),
	  "NA" => array("NAMIBIA", "NA", "NAM", "516"),
	  "NR" => array("NAURU", "NR", "NRU", "520"),
	  "NP" => array("NEPAL", "NP", "NPL", "524"),
	  "NL" => array("NETHERLANDS", "NL", "NLD", "528"),
	  "AN" => array("NETHERLANDS ANTILLES", "AN", "ANT", "530"),
	  "NC" => array("NEW CALEDONIA", "NC", "NCL", "540"),
	  "NZ" => array("NEW ZEALAND", "NZ", "NZL", "554"),
	  "NI" => array("NICARAGUA", "NI", "NIC", "558"),
	  "NE" => array("NIGER", "NE", "NER", "562"),
	  "NG" => array("NIGERIA", "NG", "NGA", "566"),
	  "NU" => array("NIUE", "NU", "NIU", "570"),
	  "NF" => array("NORFOLK ISLAND", "NF", "NFK", "574"),
	  "MP" => array("NORTHERN MARIANA ISLANDS", "MP", "MNP", "580"),
	  "NO" => array("NORWAY", "NO", "NOR", "578"),
	  "OM" => array("OMAN", "OM", "OMN", "512"),
	  "PK" => array("PAKISTAN", "PK", "PAK", "586"),
	  "PW" => array("PALAU", "PW", "PLW", "585"),
	  "PA" => array("PANAMA", "PA", "PAN", "591"),
	  "PG" => array("PAPUA NEW GUINEA", "PG", "PNG", "598"),
	  "PY" => array("PARAGUAY", "PY", "PRY", "600"),
	  "PE" => array("PERU", "PE", "PER", "604"),
	  "PH" => array("PHILIPPINES", "PH", "PHL", "608"),
	  "PN" => array("PITCAIRN", "PN", "PCN", "612"),
	  "PL" => array("POLAND", "PL", "POL", "616"),
	  "PT" => array("PORTUGAL", "PT", "PRT", "620"),
	  "PR" => array("PUERTO RICO", "PR", "PRI", "630"),
	  "QA" => array("QATAR", "QA", "QAT", "634"),
	  "RE" => array("REUNION", "RE", "REU", "638"),
	  "RO" => array("ROMANIA", "RO", "ROU", "642"),
	  "RU" => array("RUSSIAN FEDERATION", "RU", "RUS", "643"),
	  "RW" => array("RWANDA", "RW", "RWA", "646"),
	  "KN" => array("SAINT KITTS AND NEVIS", "KN", "KNA", "659"),
	  "LC" => array("SAINT LUCIA", "LC", "LCA", "662"),
	  "VC" => array("SAINT VINCENT AND THE GRENADINES", "VC", "VCT", "670"),
	  "WS" => array("SAMOA", "WS", "WSM", "882"),
	  "SM" => array("SAN MARINO", "SM", "SMR", "674"),
	  "ST" => array("SAO TOME AND PRINCIPE", "ST", "STP", "678"),
	  "SA" => array("SAUDI ARABIA", "SA", "SAU", "682"),
	  "SN" => array("SENEGAL", "SN", "SEN", "686"),
	  "RS" => array("SERBIA", "RS", "SRB", "688"),
	  "SC" => array("SEYCHELLES", "SC", "SYC", "690"),
	  "SL" => array("SIERRA LEONE", "SL", "SLE", "694"),
	  "SG" => array("SINGAPORE", "SG", "SGP", "702"),
	  "SK" => array("SLOVAKIA (Slovak Republic)", "SK", "SVK", "703"),
	  "SI" => array("SLOVENIA", "SI", "SVN", "705"),
	  "SB" => array("SOLOMON ISLANDS", "SB", "SLB", "90"),
	  "SO" => array("SOMALIA", "SO", "SOM", "706"),
	  "ZA" => array("SOUTH AFRICA", "ZA", "ZAF", "710"),
	  "ES" => array("SPAIN", "ES", "ESP", "724"),
	  "LK" => array("SRI LANKA", "LK", "LKA", "144"),
	  "SH" => array("SAINT HELENA", "SH", "SHN", "654"),
	  "PM" => array("SAINT PIERRE AND MIQUELON", "PM", "SPM", "666"),
	  "SD" => array("SUDAN", "SD", "SDN", "736"),
	  "SR" => array("SURINAME", "SR", "SUR", "740"),
	  "SJ" => array("SVALBARD AND JAN MAYEN ISLANDS", "SJ", "SJM", "744"),
	  "SZ" => array("SWAZILAND", "SZ", "SWZ", "748"),
	  "SE" => array("SWEDEN", "SE", "SWE", "752"),
	  "CH" => array("SWITZERLAND", "CH", "CHE", "756"),
	  "SY" => array("SYRIAN ARAB REPUBLIC", "SY", "SYR", "760"),
	  "TW" => array("TAIWAN, PROVINCE OF CHINA", "TW", "TWN", "158"),
	  "TJ" => array("TAJIKISTAN", "TJ", "TJK", "762"),
	  "TZ" => array("TANZANIA, UNITED REPUBLIC OF", "TZ", "TZA", "834"),
	  "TH" => array("THAILAND", "TH", "THA", "764"),
	  "TG" => array("TOGO", "TG", "TGO", "768"),
	  "TK" => array("TOKELAU", "TK", "TKL", "772"),
	  "TO" => array("TONGA", "TO", "TON", "776"),
	  "TT" => array("TRINIDAD AND TOBAGO", "TT", "TTO", "780"),
	  "TN" => array("TUNISIA", "TN", "TUN", "788"),
	  "TR" => array("TURKEY", "TR", "TUR", "792"),
	  "TM" => array("TURKMENISTAN", "TM", "TKM", "795"),
	  "TC" => array("TURKS AND CAICOS ISLANDS", "TC", "TCA", "796"),
	  "TV" => array("TUVALU", "TV", "TUV", "798"),
	  "UG" => array("UGANDA", "UG", "UGA", "800"),
	  "UA" => array("UKRAINE", "UA", "UKR", "804"),
	  "AE" => array("UNITED ARAB EMIRATES", "AE", "ARE", "784"),
	  "GB" => array("UNITED KINGDOM", "GB", "GBR", "826"),
	  "US" => array("UNITED STATES", "US", "USA", "840"),
	  "UM" => array("UNITED STATES MINOR OUTLYING ISLANDS", "UM", "UMI", "581"),
	  "UY" => array("URUGUAY", "UY", "URY", "858"),
	  "UZ" => array("UZBEKISTAN", "UZ", "UZB", "860"),
	  "VU" => array("VANUATU", "VU", "VUT", "548"),
	  "VA" => array("VATICAN CITY STATE (HOLY SEE)", "VA", "VAT", "336"),
	  "VE" => array("VENEZUELA", "VE", "VEN", "862"),
	  "VN" => array("VIET NAM", "VN", "VNM", "704"),
	  "VG" => array("VIRGIN ISLANDS (BRITISH)", "VG", "VGB", "92"),
	  "VI" => array("VIRGIN ISLANDS (U.S.)", "VI", "VIR", "850"),
	  "WF" => array("WALLIS AND FUTUNA ISLANDS", "WF", "WLF", "876"),
	  "EH" => array("WESTERN SAHARA", "EH", "ESH", "732"),
	  "YE" => array("YEMEN", "YE", "YEM", "887"),
	  "YU" => array("YUGOSLAVIA", "YU", "YUG", "891"),
	  "ZR" => array("ZAIRE", "ZR", "ZAR", "180"),
	  "ZM" => array("ZAMBIA", "ZM", "ZMB", "894"),
	  "ZW" => array("ZIMBABWE", "ZW", "ZWE", "716"),
	);

	// codes: [0]full [1]2 [2]3 [3]number

	// maunual tweaks //
	switch ($country) :
		case 'NED':
			$country='NLD';
			break;
		case 'DEN':
			$country='DNK';
			break;
		case 'SUI':
			$country='CHE';
			break;
		case 'GER':
			$country='DEU';
			break;
	endswitch;

	switch (strlen($country)):
		case 2:
			//
			break;
		case 3:
			$code='';
			foreach ($countries_arr as $two_letter => $codes) :
				if (strtolower($codes[2])==strtolower($country)) :
					$code=strtolower($codes[1]);
					break;
				endif;
			endforeach;
			break;
		default:
			$code=strtolower($country);
	endswitch;

	switch ($addon) :
		case 'full':
			$html.='<span class="flag-icon flag-icon-'.$code.'"></span> '.$countries_arr[strtoupper($code)][0];
			break;
		default :
			$html.='<span class="flag-icon flag-icon-'.$code.'"></span>';
	endswitch;

	return $html;
}

/**
 * get_wcp_standings function.
 *
 * @access public
 * @param string $year (default: '2015/2016')
 * @param int $limit (default: 10)
 * @return void
 */
function get_wcp_standings($year='2015/2016',$limit=10) {
	global $wpdb,$uci_curl;

	if ($limit>0) :
		$limit="LIMIT $limit";
	else :
		$limit='';
	endif;

	$sql="
		SELECT
			results.name,
			SUM(results.par) AS points
		FROM wp_uci_races AS races
		LEFT JOIN wp_uci_rider_data AS results
		ON races.code=results.code
		WHERE races.season='2015/2016'
		AND races.class='CDM'
		GROUP BY results.name
		ORDER BY points DESC
		$limit
	";
	$standings=$wpdb->get_results($sql);

	// add rank //
	$counter=1;
	foreach ($standings as $standing) :
		$standing->rank=$counter;
		$counter++;
	endforeach;

	return $standings;
}
?>