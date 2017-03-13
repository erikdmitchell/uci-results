<?php
global $uci_results_admin_notices;

/**
 * uci_results_admin_notices function.
 *
 * @access public
 * @return void
 */
function uci_results_admin_notices() {
	global $uci_results_admin_notices;

	if (empty($uci_results_admin_notices))
		return;

	foreach ($uci_results_admin_notices as $class => $notices) :
		foreach ($notices as $notice) :
			echo '<div class="'.$class.'">'.__($notice, 'uci-results').'</div>';
		endforeach;
	endforeach;
}
?>