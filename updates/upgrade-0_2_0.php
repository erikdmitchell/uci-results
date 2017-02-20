<?php
// this is a major migration, so we wrote a script for it (seperate admin page) //

function uci_results_upgrade_0_2_0_notice() {
	$class = 'notice notice-warning';
	$message = __('The UCI Results Plugin requires a major database upgrade. <a href="'.admin_url('?page=uci-results&subpage=migration&version=0_2_0').'">Click here</a>', 'uci-results');

	printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message); 
}
add_action('admin_notices', 'uci_results_upgrade_0_2_0_notice');
?>