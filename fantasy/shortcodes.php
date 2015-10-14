<?php
function fantasy_cycling() {
	ob_start();
	include_once(plugin_dir_path(__FILE__).'templates/fantasy-main.php');
	return ob_get_clean();
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