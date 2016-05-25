<?php

/**
 * ucicurl_scripts_styles function.
 *
 * @access public
 * @return void
 */
function ucicurl_scripts_styles() {
	wp_enqueue_style('ucicurl-shortcode-styles', UCICURL_URL.'/css/main.css');
}
add_action('wp_enqueue_scripts', 'ucicurl_scripts_styles');

function ucicurl_landing($atts) {
	$atts = shortcode_atts( array(

	), $atts, 'ucicurl' );

	return ucicurl_get_template('main');
}
add_shortcode('ucicurl', 'ucicurl_landing');

function ucicurl_rider_rankings($atts) {
	$atts = shortcode_atts( array(

	), $atts, 'rider_rankings' );

	return;
}
add_shortcode('rider_rankings', 'ucicurl_rider_rankings');

function ucicurl_country_rankings($atts) {
	$atts = shortcode_atts( array(

	), $atts, 'country_rankings' );

	return;
}
add_shortcode('country_rankings', 'ucicurl_country_rankings');

function ucicurl_race_rankings($atts) {
	$atts = shortcode_atts( array(

	), $atts, 'race_rankings' );

	return;
}
add_shortcode('race_rankings', 'ucicurl_race_rankings');

// single rider
// single race
?>