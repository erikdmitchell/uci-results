<?php
/**
 * UCIcURLDB class.
 *
 * @since Version 1.0.1
 */
class UCIcURLDB {

	public $db_version='0.3.1';
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
			`date` VARCHAR(30) NOT NULL,
			`event` TEXT NOT NULL,
			`nat` VARCHAR(5) NOT NULL,
			`class` VARCHAR(5) NOT NULL ,
			`winner` VARCHAR(50) NOT NULL,
			`season` VARCHAR(30) NOT NULL,
			`link` TEXT NOT NULL,
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

		$table_name=$wpdb->prefix.'uci_fq_rankings';
		$uci_fq_rankings_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` tinytext NOT NULL,
		  `uci_points_in_field` int(11) NOT NULL,
		  `wcp_points_in_field` int(11) NOT NULL,
		  `race_class_number` int(11) NOT NULL,
		  `finishers_multiplier` DECIMAL(7,4) NOT NULL,
		  `divider` int(11) NOT NULL,
		  `fq` DECIMAL(7,4) NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$rider_season_points="CREATE TABLE ".$wpdb->prefix."uci_rider_season_points (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` mediumtext NOT NULL,
		  `nat` tinytext NOT NULL,
		  `season` mediumtext NOT NULL,
		  `c2` int(11) NOT NULL,
		  `c1` int(11) NOT NULL,
		  `cn` int(11) NOT NULL,
		  `cc` int(11) NOT NULL,
		  `cdm` int(11) NOT NULL,
		  `cm` int(11) NOT NULL,
		  `total` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$rider_season_sos="CREATE TABLE ".$wpdb->prefix."uci_rider_season_sos (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` mediumtext NOT NULL,
		  `season` mediumtext NOT NULL,
		  `sos` DECIMAL(7,3) NOT NULL,
		  `rank` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$rider_season_wins="CREATE TABLE ".$wpdb->prefix."uci_rider_season_wins (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` mediumtext NOT NULL,
		  `season` mediumtext NOT NULL,
		  `wins` int(11) NOT NULL,
		  `races` int(11) NOT NULL,
		  `win_perc` DECIMAL(7,3) NOT NULL,
		  `rank` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$rider_season_rankings="CREATE TABLE ".$wpdb->prefix."uci_rider_season_rankings (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` mediumtext NOT NULL,
		  `coutnry` mediumtext NOT NULL,
		  `season` int(11) NOT NULL,
		  `rank` int(11) NOT NULL,
		  `date` DECIMAL(7,3) NOT NULL
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta(array(
			$uci_races_sql,
			$uci_rider_data_sql,
			$uci_season_rankings_sql,
			$uci_fq_rankings_sql,
			$rider_season_points,
			$rider_season_sos,
			$rider_season_wins,
			$rider_season_rankings
		));

		add_option($this->wp_option_name,$this->db_version);
	}

	public function db_update() {
		global $wpdb;

		$uci_races_sql="CREATE TABLE ".$wpdb->prefix."uci_races (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` tinytext NOT NULL,
		  `season` tinytext NOT NULL,
			`date` VARCHAR(30) NOT NULL,
			`event` TEXT NOT NULL,
			`nat` VARCHAR(5) NOT NULL,
			`class` VARCHAR(5) NOT NULL ,
			`winner` VARCHAR(50) NOT NULL,
			`season` VARCHAR(30) NOT NULL,
			`link` TEXT NOT NULL
		)";
		$alter_races_sql='"ALTER TABLE `'.$wpdb->prefix.'uci_races` DROP `data`;"';

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
			`pcr` VARCHAR(10) NOT NULL
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

		$rider_season_points="CREATE TABLE ".$wpdb->prefix."uci_rider_season_points (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` mediumtext NOT NULL,
		  `nat` tinytext NOT NULL,
		  `season` mediumtext NOT NULL,
		  `c2` int(11) NOT NULL,
		  `c1` int(11) NOT NULL,
		  `cn` int(11) NOT NULL,
		  `cc` int(11) NOT NULL,
		  `cdm` int(11) NOT NULL,
		  `cm` int(11) NOT NULL,
		  `total` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		);";

		$table_name=$wpdb->prefix.'uci_fq_rankings';
		$uci_fq_rankings_sql="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` tinytext NOT NULL,
		  `uci_points_in_field` int(11) NOT NULL,
      `wcp_points_in_field` int(11) NOT NULL,
      `race_class_number` int(11) NOT NULL,
      `finishers_multiplier` DECIMAL(7,4) NOT NULL,
      `divider` int(11) NOT NULL,
		  `fq` DECIMAL(7,4) NOT NULL,
		  PRIMARY KEY (`id`)
		)";

		$rider_season_sos="CREATE TABLE ".$wpdb->prefix."uci_rider_season_sos (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` mediumtext NOT NULL,
		  `season` mediumtext NOT NULL,
		  `sos` DECIMAL(7,3) NOT NULL,
		  `rank` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ;";

		$rider_season_wins="CREATE TABLE ".$wpdb->prefix."uci_rider_season_wins (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` mediumtext NOT NULL,
		  `season` mediumtext NOT NULL,
		  `wins` int(11) NOT NULL,
		  `races` int(11) NOT NULL,
		  `win_perc` DECIMAL(7,3) NOT NULL,
		  `rank` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		);";

		$rider_season_rankings="CREATE TABLE ".$wpdb->prefix."uci_rider_season_rankings (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` mediumtext NOT NULL,
		  `coutnry` mediumtext NOT NULL,
		  `season` int(11) NOT NULL,
		  `rank` int(11) NOT NULL,
		  `date` DECIMAL(7,3) NOT NULL
		  PRIMARY KEY (`id`)
		);";

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta(array(
			$uci_races_sql,
			$uci_rider_data_sql,
			$uci_season_rankings_sql,
			$uci_fq_rankings_sql,
			$rider_season_points,
			$rider_season_sos,
			$rider_season_wins,
			$rider_season_rankings
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