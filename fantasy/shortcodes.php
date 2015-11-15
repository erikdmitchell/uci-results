<?php
/**
 * fantasy_cycling function.
 *
 * @access public
 * @return void
 */
function fantasy_cycling() {
	return fc_get_template_html('fantasy-main');
}
add_shortcode('fantasy-cycling','fantasy_cycling');

function fantasy_cycling_team() {
	return fc_get_template_html('team');
}
add_shortcode('fantasy-cycling-team','fantasy_cycling_team');

function fantasy_cycling_standings() {
	return fc_get_template_html('standings');
}
add_shortcode('fantasy-cycling-standings','fantasy_cycling_standings');

function fantasy_cycling_create_team() {
	return fc_get_template_html('create-team');
}
add_shortcode('fantasy-cycling-create-team','fantasy_cycling_create_team');
?>