<?php
function fantasy_cycling($atts) {
	$html=null;
	extract(shortcode_atts(array(
	),$atts));

	$html.='<h3>Fantasy Cycling</h3>';
	// login - register //
	// if logged in, goto team page //

	return $html;
}
add_shortcode('fantasy-cycling','fantasy_cycling');

function fantasy_cycling_team($atts) {
	$html=null;
	extract(shortcode_atts(array(
	),$atts));

	fc_login_protect_page();

	$html.='<h3>Fantasy Cycling - Team</h3>';

	return $html;
}
add_shortcode('fantasy-cycling-team','fantasy_cycling_team');

function fantasy_cycling_standings($atts) {
	$html=null;
	extract(shortcode_atts(array(
	),$atts));

	fc_login_protect_page();

	$html.='<h3>Fantasy Cycling - Standings</h3>';

	return $html;
}
add_shortcode('fantasy-cycling-standings','fantasy_cycling_standings');
?>