<?php
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