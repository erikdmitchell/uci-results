<?php
global $ucicurl_db_version;

$ucicurl_db_version='0.2.0';

/**
 * ucicurl_set_db_tables function.
 *
 * @access public
 * @return void
 */
function ucicurl_set_db_tables() {
	global $wpdb;

	$wpdb->uci_results_rider_rankings=$wpdb->prefix.'uci_curl_rider_rankings';
	$wpdb->uci_results_related_races=$wpdb->prefix.'uci_curl_related_races';
	$wpdb->uci_results_series_overall=$wpdb->prefix.'uci_results_series_overall';
}
ucicurl_set_db_tables();

/**
 * ucicurl_db_install function.
 *
 * @access public
 * @return void
 */
function ucicurl_db_install() {
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');

	global $wpdb, $ucicurl_db_version;

	$wpdb->hide_errors();

	$wpdb->uci_results_rider_rankings=$wpdb->prefix.'uci_curl_rider_rankings';
	$wpdb->uci_results_related_races=$wpdb->prefix.'uci_curl_related_races';
	$wpdb->uci_results_series_overall=$wpdb->prefix.'uci_results_series_overall';

	$charset=$wpdb->get_charset_collate();

	$sql_rider_rankings="
		CREATE TABLE $wpdb->uci_results_rider_rankings (
		  id bigint(20) NOT NULL AUTO_INCREMENT,
			rider_id bigint(20) NOT NULL,
			points bigint(20) NOT NULL DEFAULT '0',
			season VARCHAR(50) NOT NULL,
			rank bigint(20) NOT NULL DEFAULT '0',
			week bigint(20) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		) $charset;
	";

	$sql_related_races="
		CREATE TABLE $wpdb->uci_results_related_races (			
			id bigint(20) NOT NULL AUTO_INCREMENT,
			race_id bigint(20) NOT NULL DEFAULT '0',
			related_race_id bigint(20) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		) $charset;
	";

	$sql_series_overall="
		CREATE TABLE $wpdb->uci_results_series_overall (
		  id bigint(20) NOT NULL AUTO_INCREMENT,
			rider_id bigint(20) NOT NULL,
			points bigint(20) NOT NULL DEFAULT '0',
			series_id bigint(20) NOT NULL,
			season VARCHAR(50) NOT NULL,
			rank bigint(20) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		) $charset;
	";

	dbDelta(array(
		$sql_rider_rankings,
		$sql_related_races,
		$sql_series_overall,
	));

	add_option('ucicurl_db_version', $ucicurl_db_version);
}
register_activation_hook(__FILE__, 'ucicurl_db_install');

/**
 * ucicurl_db_update function.
 *
 * @access public
 * @return void
 */
function ucicurl_db_update() {
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');

	global $wpdb, $ucicurl_db_version;

	echo $installed_version=get_option('ucicurl_db_version');

	if ($installed_version!=$ucicurl_db_version) :
		$wpdb->hide_errors();
		
		$wpdb->uci_results_rider_rankings=$wpdb->prefix.'uci_curl_rider_rankings';
		$wpdb->uci_results_related_races=$wpdb->prefix.'uci_curl_related_races';
		$wpdb->uci_results_series_overall=$wpdb->prefix.'uci_results_series_overall';

		$sql_rider_rankings="
			CREATE TABLE $wpdb->uci_results_rider_rankings (
			  id bigint(20) NOT NULL AUTO_INCREMENT,
				rider_id bigint(20) NOT NULL,
				points bigint(20) NOT NULL DEFAULT '0',
				season VARCHAR(50) NOT NULL,
				rank bigint(20) NOT NULL DEFAULT '0',
				week bigint(20) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			);
		";

		$sql_related_races="
			CREATE TABLE $wpdb->uci_results_related_races (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				race_id bigint(20) NOT NULL DEFAULT '0',
				related_race_id bigint(20) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			);
		";

		$sql_series_overall="
			CREATE TABLE $wpdb->uci_results_series_overall (
			  id bigint(20) NOT NULL AUTO_INCREMENT,
				rider_id bigint(20) NOT NULL,
				points bigint(20) NOT NULL DEFAULT '0',
				series_id bigint(20) NOT NULL,
				season VARCHAR(50) NOT NULL,
				rank bigint(20) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			);
		";

		dbDelta(array(
			$sql_rider_rankings,
			$sql_related_races,
			$sql_series_overall,
		));

		return $ucicurl_db_version;
	endif;
}

/**
 * ucicurl_update_db_check function.
 *
 * @access public
 * @return void
 */
function ucicurl_update_db_check() {
	global $ucicurl_db_version;

	if (get_option('ucicurl_db_version') < '0.2.0') :
		include_once(UCI_RESULTS_PATH.'updates/upgrade-0_2_0.php');
	elseif (get_option('ucicurl_db_version') != $ucicurl_db_version) :
		$ucicurl_db_version=ucicurl_db_update();
	endif;

	update_option('ucicurl_db_version', $ucicurl_db_version);

	return;
}
add_action('init', 'ucicurl_update_db_check', 99);

/**
 * uci_results_empty_database_tables function.
 *
 * @access public
 * @return void
 */
function uci_results_empty_database_tables() {
	global $wpdb;

	$wpdb->query("TRUNCATE TABLE $wpdb->uci_results_rider_rankings");
	$wpdb->query("TRUNCATE TABLE $wpdb->uci_results_related_races");
	$wpdb->query("TRUNCATE TABLE $wpdb->uci_results_series_overall");
}

/**
 * uci_results_remove_database_tables function.
 *
 * @access public
 * @return void
 */
function uci_results_remove_database_tables() {
	global $wpdb;

	$wpdb->query("DROP TABLE $wpdb->uci_results_rider_rankings");
	$wpdb->query("DROP TABLE $wpdb->uci_results_related_races");
	$wpdb->query("DROP TABLE $wpdb->uci_results_series_overall");
}

/**
 * uci_results_column_exists function.
 * 
 * @access public
 * @param string $table (default: '')
 * @param string $column (default: '')
 * @return void
 */
function uci_results_column_exists($table='', $column='') {
	global $wpdb;
	
	if (empty($table) || empty($column))
		return false;
		
	$column_info=$wpdb->get_row("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = '$table' AND COLUMN_NAME = '$column'");
	
	if ($column_info===null || is_wp_error($column_info))
		return false;
		
	return true;
}
?>