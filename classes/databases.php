<?php
class UCIcURLDB {

	public $db_version='0.0.7';
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

		$table_name=$wpdb->prefix.'top25_races';
		$top25_races_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) NOT NULL,
		  `type` varchar(3) NOT NULL,
		  `quality` float(10,3) NOT NULL,
		  `total` float(10,3) NOT NULL,
		  `date` varchar(11) NOT NULL,
		  `results` text NOT NULL,
		  `filename` text NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$table_name=$wpdb->prefix.'top25_rank';
		$top25_rank_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `data` longtext NOT NULL,
		  `week` tinyint(4) NOT NULL,
		  `season` varchar(10) NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$table_name=$wpdb->prefix.'top25_riders';
		$top25_riders_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) NOT NULL,
		  `uci` mediumint(9) NOT NULL,
		  `wc` mediumint(9) NOT NULL,
		  `races` mediumint(9) NOT NULL,
		  `total` mediumint(9) NOT NULL,
		  `year` mediumint(9) NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$table_name=$wpdb->prefix.'top25_seasons';
		$top25_seasons_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `season` varchar(10) NOT NULL,
		  `start` varchar(12) NOT NULL,
		  `end` varchar(12) NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$table_name=$wpdb->prefix.'top25_votes';
		$top25_votes_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `date` varchar(11) NOT NULL,
		  `results` text NOT NULL,
		  `userID` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

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

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta(array(
			$top25_races_sql,
			$top25_rank_sql,
			$top25_riders_sql,
			$top25_seasons_sql,
			$top25_votes_sql,
			$uci_races_sql,
			$uci_rider_data_sql,
			$uci_season_rankings_sql
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
			$this->db_install();
	}

}

new UCIcURLDB();

register_activation_hook(__FILE__,array('UCIcURLDB','db_install'));
?>