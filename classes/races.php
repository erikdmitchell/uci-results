<?php
global $uci_races;

/**
 * UCIRaces class.
 *
 * @since Version 2.0.0
 */
class UCIRaces {
	
	public $version='0.1.0';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		
	}

	/**
	 * weeks function.
	 *
	 * @access public
	 * @param string $season (default: '2015/2016')
	 * @return void
	 */
	public function weeks($season='2015/2016') {
		global $wpdb;

		$weeks=$wpdb->get_col("SELECT week FROM $wpdb->uci_results_races WHERE season='$season' GROUP BY week ORDER BY week ASC");

		return $weeks;
	}

}

$uci_races=new UCIRaces();
?>