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

	return uci_results_get_template_part('main');
}
add_shortcode('uci_results', 'uci_results_main');
?>