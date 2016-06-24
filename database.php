<?php
global $ucicurl_db_version;

$ucicurl_db_version='0.1.5';

function ucicurl_set_db_tables() {
	global $wpdb;

	$wpdb->ucicurl_races=$wpdb->prefix.'uci_curl_races';
	$wpdb->ucicurl_results=$wpdb->prefix.'uci_curl_results';
	$wpdb->ucicurl_riders=$wpdb->prefix.'uci_curl_riders';
	$wpdb->ucicurl_rider_rankings=$wpdb->prefix.'uci_curl_rider_rankings';
	$wpdb->ucicurl_related_races=$wpdb->prefix.'uci_curl_related_races';
	$wpdb->ucicurl_series=$wpdb->prefix.'uci_curl_series';
}
ucicurl_set_db_tables();

function ucicurl_db_install() {
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');

	global $wpdb, $ucicurl_db_version;

	$wpdb->hide_errors();
	$wpdb->ucicurl_races=$wpdb->prefix.'uci_curl_races';
	$wpdb->ucicurl_results=$wpdb->prefix.'uci_curl_results';
	$wpdb->ucicurl_riders=$wpdb->prefix.'uci_curl_riders';
	$wpdb->ucicurl_rider_rankings=$wpdb->prefix.'uci_curl_rider_rankings';
	$wpdb->ucicurl_related_races=$wpdb->prefix.'uci_curl_related_races';
	$wpdb->ucicurl_series=$wpdb->prefix.'uci_curl_series';

	$charset=$wpdb->get_charset_collate();

	$sql_races="
		CREATE TABLE $wpdb->ucicurl_races (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			date DATE NOT NULL,
			event TEXT NOT NULL,
			nat VARCHAR(5) NOT NULL,
			class VARCHAR(5) NOT NULL ,
			winner VARCHAR(250) NOT NULL,
			season VARCHAR(50) NOT NULL,
			week bigint(20) NOT NULL DEFAULT '0',
			link TEXT NOT NULL,
			code TEXT NOT NULL,
			related_races_id bigint(20) NOT NULL DEFAULT '0',
			series_id bigint(20) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		) $charset;
	";

	$sql_results="
		CREATE TABLE $wpdb->ucicurl_results (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			race_id bigint(20) NOT NULL,
			place SMALLINT NOT NULL DEFAULT '0',
			name LONGTEXT NOT NULL,
			nat VARCHAR(5) NOT NULL,
			age TINYINT NOT NULL DEFAULT '0',
			result VARCHAR(10) NOT NULL,
			pcr VARCHAR(10) NOT NULL DEFAULT '0',
			pcr VARCHAR(10) NOT NULL DEFAULT '0',
			rider_id bigint(20) NOT NULL,
			PRIMARY KEY (`id`)
		) $charset;
		ALTER DATABASE {$wpdb->ucicurl_results} CHARACTER SET utf8;
	";

	$sql_riders="
		CREATE TABLE $wpdb->ucicurl_riders (
		  id bigint(20) NOT NULL AUTO_INCREMENT,
			name LONGTEXT NOT NULL,
			nat VARCHAR(5) NOT NULL,
			slug LONGTEXT NOT NULL,
			PRIMARY KEY (`id`)
		) $charset;
	";

	$sql_rider_rankings="
		CREATE TABLE $wpdb->ucicurl_rider_rankings (
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
		CREATE TABLE $wpdb->ucicurl_related_races (
		  id bigint(20) NOT NULL AUTO_INCREMENT,
			race_ids TEXT NOT NULL,
			PRIMARY KEY (`id`)
		) $charset;
	";

	$sql_series="
		CREATE TABLE $wpdb->ucicurl_series (
		  id bigint(20) NOT NULL AUTO_INCREMENT,
			name TEXT NOT NULL,
			season VARCHAR(50) NOT NULL,
			PRIMARY KEY (`id`)
		) $charset;
	";

	dbDelta(array(
		$sql_races,
		$sql_results,
		$sql_riders,
		$sql_rider_rankings,
		$sql_related_races,
		$sql_series,
	));

	add_option('ucicurl_db_version', $ucicurl_db_version);
}

function ucicurl_db_update() {
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');

	global $wpdb, $ucicurl_db_version;

	echo $installed_version=get_option('ucicurl_db_version');

	if ($installed_version!=$ucicurl_db_version) :
		$wpdb->hide_errors();
		$wpdb->ucicurl_races=$wpdb->prefix.'uci_curl_races';
		$wpdb->ucicurl_results=$wpdb->prefix.'uci_curl_results';
		$wpdb->ucicurl_riders=$wpdb->prefix.'uci_curl_riders';
		$wpdb->ucicurl_rider_rankings=$wpdb->prefix.'uci_curl_rider_rankings';
		$wpdb->ucicurl_related_races=$wpdb->prefix.'uci_curl_related_races';
		$wpdb->ucicurl_series=$wpdb->prefix.'uci_curl_series';

		$sql_races="
			CREATE TABLE $wpdb->ucicurl_races (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				date DATE NOT NULL,
				event TEXT NOT NULL,
				nat VARCHAR(5) NOT NULL,
				class VARCHAR(5) NOT NULL ,
				winner VARCHAR(250) NOT NULL,
				season VARCHAR(50) NOT NULL,
				week bigint(20) NOT NULL DEFAULT '0',
				link TEXT NOT NULL,
				code TEXT NOT NULL,
				related_races_id bigint(20) NOT NULL DEFAULT '0',
				series_id bigint(20) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			);
		";

		$sql_results="
			CREATE TABLE $wpdb->ucicurl_results (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				race_id bigint(20) NOT NULL,
				place SMALLINT NOT NULL DEFAULT '0',
				name LONGTEXT NOT NULL,
				nat VARCHAR(5) NOT NULL,
				age TINYINT NOT NULL DEFAULT '0',
				result VARCHAR(10) NOT NULL,
				pcr VARCHAR(10) NOT NULL DEFAULT '0',
				pcr VARCHAR(10) NOT NULL DEFAULT '0',
				rider_id bigint(20) NOT NULL,
				PRIMARY KEY (`id`)
			);
			ALTER DATABASE {$wpdb->ucicurl_results} CHARACTER SET utf8;
		";

		$sql_riders="
			CREATE TABLE $wpdb->ucicurl_riders (
			  id bigint(20) NOT NULL AUTO_INCREMENT,
				name LONGTEXT NOT NULL,
				nat VARCHAR(5) NOT NULL,
				slug LONGTEXT NOT NULL,
				PRIMARY KEY (`id`)
			);
		";

		$sql_rider_rankings="
			CREATE TABLE $wpdb->ucicurl_rider_rankings (
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
			CREATE TABLE $wpdb->ucicurl_related_races (
			  id bigint(20) NOT NULL AUTO_INCREMENT,
				race_ids TEXT NOT NULL,
				PRIMARY KEY (`id`)
			);
		";

		$sql_series="
			CREATE TABLE $wpdb->ucicurl_series (
			  id bigint(20) NOT NULL AUTO_INCREMENT,
				name TEXT NOT NULL,
				season VARCHAR(50) NOT NULL,
				PRIMARY KEY (`id`)
			);
		";

		dbDelta(array(
			$sql_races,
			$sql_results,
			$sql_riders,
			$sql_rider_rankings,
			$sql_related_races,
			$sql_series,
		));

		update_option('ucicurl_db_version', $ucicurl_db_version);
	endif;
}

function ucicurl_update_db_check() {
	global $ucicurl_db_version;

	if (get_option('ucicurl_db_version')!=$ucicurl_db_version)
		ucicurl_db_update();

	return;
}
add_action('plugins_loaded', 'ucicurl_update_db_check');


register_activation_hook(__FILE__, 'ucicurl_db_install');
?>