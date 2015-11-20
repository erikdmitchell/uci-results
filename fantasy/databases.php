<?php
/**
 * FantasyCyclingDB class.
 *
 * @since Version 0.0.1
 */
class FantasyCyclingDB {

	public $db_version='0.0.7';
	public $wp_option_name='fantasy_cycling_db_version';
	public $table_name='';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name=$wpdb->prefix.'$FantasyCyclingDB';
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

		$table_name=$wpdb->prefix.'fc_teams';
		$fc_teams_table="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `wp_user_id` int(11) NOT NULL,
		  `data` text NOT NULL,
		  `team` varchar(250) NOT NULL,
		  `race_id` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		$table_name=$wpdb->prefix.'fc_races';
		$fc_races_table="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(250) NOT NULL,
		   `type` varchar(15) NOT NULL,
		  `season` varchar(15) NOT NULL,
		  `code` tinytext NOT NULL,
		  `start_list` text NOT NULL,
		  `series` VARCHAR(250) NOT NULL,
		  `race_start` DATE NOT NULL
		  `last_year_code` tinytext NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta(array(
			$fc_teams_table,
			$fc_races_table
		));

		add_option($this->wp_option_name,$this->db_version);
	}

	/**
	 * db_update function.
	 *
	 * @access public
	 * @return void
	 */
	public function db_update() {
		global $wpdb;

		$table_name=$wpdb->prefix.'fc_teams';
		$fc_teams_table="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `wp_user_id` int(11) NOT NULL,
		  `data` text NOT NULL,
		  `team` varchar(250) NOT NULL,
		  `race_id` int(11) NOT NULL,
		);";

		$table_name=$wpdb->prefix.'fc_races';
		$fc_races_table="CREATE TABLE $table_name (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(250) NOT NULL,
		   `type` varchar(15) NOT NULL,
		  `season` varchar(15) NOT NULL,
		  `code` tinytext NOT NULL,
		  `start_list` text NOT NULL,
		  `series` VARCHAR(250) NOT NULL,
		  `race_start` DATE NOT NULL
		  `last_year_code` tinytext NOT NULL,
		);";

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta(array(
			$fc_teams_table,
			$fc_races_table
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

$FantasyCyclingDB=new FantasyCyclingDB();

register_activation_hook(__FILE__,array('FantasyCyclingDB','db_install'));
?>