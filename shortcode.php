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

/**
 * uci_results_main function.
 *
 * @access public
 * @param mixed $atts
 * @return void
 */
function uci_results_main($atts) {
	$atts = shortcode_atts( array(

	), $atts, 'uci_results' );

	return ucicurl_get_template('main');
}
add_shortcode('uci_results', 'uci_results_main');

/**
 * uci_results_rider function.
 *
 * single rider
 *
 * @access public
 * @param mixed $atts
 * @return void
 */
function uci_results_rider($atts) {
	$atts = shortcode_atts( array(

	), $atts, 'uci_results_rider' );

	return ucicurl_get_template('rider');
}
add_shortcode('uci_results_rider', 'uci_results_rider');

/**
 * uci_results_race function.
 *
 * @access public
 * @param mixed $atts
 * @return void
 */
function uci_results_race($atts) {
	$atts = shortcode_atts( array(

	), $atts, 'uci_results_race' );

	return ucicurl_get_template('race');
}
add_shortcode('uci_results_race', 'uci_results_race');

/**
 * uci_results_country function.
 *
 * @access public
 * @param mixed $atts
 * @return void
 */
function uci_results_country($atts) {
	$atts = shortcode_atts( array(

	), $atts, 'uci_results_country' );

	return ucicurl_get_template('country');
}
add_shortcode('uci_results_country', 'uci_results_country');




function ucicurl_rider_rankings($atts) {
	$atts = shortcode_atts( array(

	), $atts, 'rider_rankings' );

	return;
}
add_shortcode('rider_rankings', 'ucicurl_rider_rankings');

function ucicurl_race_rankings($atts) {
	$atts = shortcode_atts( array(

	), $atts, 'race_rankings' );

	return;
}
add_shortcode('race_rankings', 'ucicurl_race_rankings');
?>