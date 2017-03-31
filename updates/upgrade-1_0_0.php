<?php
// this is a major migration, so we wrote a script for it (seperate admin page) //

/**
 * upgrade_1_0_0_db function.
 * 
 * @access public
 * @return void
 */
function upgrade_1_0_0_db() {
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');

	global $wpdb;

	$wpdb->hide_errors();
	$wpdb->uci_results_season_weeks=$wpdb->prefix.'uci_results_season_weeks';
	$charset=$wpdb->get_charset_collate();

	$sql_season_weeks="
		CREATE TABLE $wpdb->uci_results_season_weeks (
		  	id bigint(20) NOT NULL AUTO_INCREMENT,
		  	term_id bigint(20) NOT NULL DEFAULT '0',
		  	week bigint(20) NOT NULL DEFAULT '0',
			start date NOT NULL,
			end date NOT NULL,
			PRIMARY KEY (`id`)
		) $charset;
	";

	dbDelta(array(
		$sql_season_weeks,
	));
}

/**
 * uci_results_upgrade_1_0_0_notice function.
 * 
 * @access public
 * @return void
 */
function uci_results_upgrade_1_0_0_notice() {
	$class = 'migration-1_0_0 notice notice-warning';
	$message = __('The UCI Results Plugin requires a major database upgrade. <a href="'.admin_url('?page=uci-results&subpage=migration&version=1_0_0').'">Click here</a>', 'uci-results');

	printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message); 
}
add_action('admin_notices', 'uci_results_upgrade_1_0_0_notice');
?>