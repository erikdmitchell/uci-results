<?php
/**
 * UCIcURLDB class.
 *
 * @since Version 1.0.1
 */
class UCIcURLDB {

	public $db_version='0.1.1';
	public $wp_option_name='ucicurl_version';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action('plugins_loaded',array($this,'update_db_check'));
	}

	/**
	 * db_install function.
	 *
	 * @access public
	 * @return void
	 */
	public function db_install() {
		global $wpdb;

		$charset_collate=$wpdb->get_charset_collate();

		$table_name=$wpdb->prefix.'uci_races';
		$uci_races_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `data` longtext NOT NULL,
		  `code` tinytext NOT NULL,
		  `season` tinytext NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$table_name=$wpdb->prefix.'uci_rider_data';
		$uci_rider_data_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` tinytext NOT NULL,
		  `name` varchar(100) NOT NULL,
		  `data` text NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$table_name=$wpdb->prefix.'uci_season_rankings';
		$uci_season_rankings_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `rank` varchar(12) NOT NULL,
		  `name` mediumtext NOT NULL,
		  `nation` tinytext NOT NULL,
		  `age` smallint(6) NOT NULL,
		  `points` smallint(6) NOT NULL,
		  `season` mediumtext NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$table_name=$wpdb->prefix.'uci_weekly_rider_rankings';
		$uci_weekly_rider_rankings_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` mediumtext NOT NULL,
		  `season` mediumtext NOT NULL,
		  `data` text NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta(array(
			$uci_races_sql,
			$uci_rider_data_sql,
			$uci_season_rankings_sql,
			$uci_weekly_rider_rankings_sql
		));

		add_option($this->wp_option_name,$this->db_version);
	}

	public function db_update() {
		global $wpdb;

		$table_name=$wpdb->prefix.'uci_races';
		$uci_races_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` tinytext NOT NULL,
		  `season` tinytext NOT NULL,
			`date` VARCHAR(30) NOT NULL,
			`event` TEXT NOT NULL,
			`nat` VARCHAR(5) NOT NULL,
			`class` VARCHAR(5) NOT NULL ,
			`winner` VARCHAR(50) NOT NULL,
			`season` VARCHAR(30) NOT NULL,
			`link` TEXT NOT NULL,
			`fq` VARCHAR(5) NOT NULL
		);";
		$alter_races_sql='"ALTER TABLE `'.$table_name.'` DROP `data`;"';

		$table_name=$wpdb->prefix.'uci_rider_data';
		$uci_rider_data_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` tinytext NOT NULL,
		  `name` varchar(100) NOT NULL,
			`place` int(11) NOT NULL,
			`nat` VARCHAR(5) NOT NULL,
			`age` int(11) NOT NULL ,
			`time` VARCHAR(10) NOT NULL,
			`par` VARCHAR(10) NOT NULL,
			`pcr` VARCHAR(10) NOT NULL,
		);";
		$alter_rider_data_sql='"ALTER TABLE `'.$table_name.'` DROP `data`;"';

		$table_name=$wpdb->prefix.'uci_season_rankings';
		$uci_season_rankings_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `rank` varchar(12) NOT NULL,
		  `name` mediumtext NOT NULL,
		  `nation` tinytext NOT NULL,
		  `age` smallint(6) NOT NULL,
		  `points` smallint(6) NOT NULL,
		  `season` mediumtext NOT NULL
		);";

		$table_name=$wpdb->prefix.'uci_weekly_rider_rankings';
		$uci_weekly_rider_rankings_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` mediumtext NOT NULL,
		  `season` mediumtext NOT NULL,
		  `data` text NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta(array(
			$uci_races_sql,
			$uci_rider_data_sql,
			$uci_season_rankings_sql,
			$uci_weekly_rider_rankings_sql
		));

		update_option($this->wp_option_name,$this->db_version);
	}

	/**
	 * update_db_check function.
	 *
	 * @access public
	 * @return void
	 */
	public function update_db_check() {
		if (get_option($this->wp_option_name)!=$this->db_version)
			$this->db_update();
	}

}

new UCIcURLDB();

register_activation_hook(__FILE__,array('UCIcURLDB','db_install'));
?>