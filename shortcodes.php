<?php
/**
 * races_shortcode function.
 *
 * @access public
 * @param mixed $atts
 * @return void
 */
/*
function races_shortcode($atts) {
	global $RaceStats;

	$html=null;
	extract(shortcode_atts(array(
		'season' => false,
	),$atts));

	$html.=$RaceStats->get_season_race_rankings($season);

	return $html;
}
add_shortcode('uci-races','races_shortcode');
*/

/*
function riders_shortcode($atts) {
	global $RiderStats;

	$html=null;
	extract(shortcode_atts(array(
		'season' => '2015/2016',
		'title' => 'Rider Rankings',
	),$atts));

	$html.=$RiderStats->get_season_rider_rankings(array(
		'season' => $season
	));

	return $html;
}
add_shortcode('uci-riders','riders_shortcode');
*/
?>