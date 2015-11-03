<?php
/**
 * fantasy_cycling function.
 *
 * @access public
 * @return void
 */
function fantasy_cycling() {
	ob_start();
	include_once(plugin_dir_path(__FILE__).'templates/fantasy-main.php');
	return ob_get_clean();
}
add_shortcode('fantasy-cycling','fantasy_cycling');

function fantasy_cycling_team($atts) {
	ob_start();
	fc_login_protect_page();
	include_once(plugin_dir_path(__FILE__).'templates/team.php');
	return ob_get_clean();
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