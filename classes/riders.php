<?php

/**
 * UCIResultsRiders class.
 *
 * @since 0.1.0
 *
 */
class UCIResultsRiders {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {}

	public function get_rider($rider_id=0, $results=false, $rankings=false, $stats=false) {
		global $wpdb;

		// if not an int, it's a slug //
		if (!is_numeric($rider_id)) :
			$rider_id=ucicurl_get_rider_id($rider_id);
		endif;

		// last rider id check //
		if (empty($rider_id))
			return false;

		$results_sql="
			SELECT *
			FROM {$wpdb->uci_results_results}
			LEFT JOIN {$wpdb->uci_results_races}
			ON {$wpdb->uci_results_results}.race_id={$wpdb->uci_results_races}.id
			WHERE rider_id = $rider_id
			ORDER BY date DESC
		";
		$rankings_sql="
			SELECT
				*
			FROM $wpdb->uci_results_rider_rankings
			WHERE rider_id = $rider_id
			ORDER BY season, week DESC
		";
		$rider=$wpdb->get_row("SELECT * FROM $wpdb->uci_results_riders WHERE id=$rider_id");

		// get results //
		if ($results)
			$rider->results=$wpdb->get_results($results_sql);

		// get rankings //
		if ($rankings)
			$rider->rankings=$wpdb->get_results($rankings_sql);

		// get stats //
		if ($stats)
			$rider->stats=$this->get_stats($rider_id);

		return $rider;
	}

	/**
	 * get_rider_id function.
	 *
	 * @access public
	 * @param string $name (default: '')
	 * @return void
	 */
	public function get_rider_id($name='') {
		global $wpdb;

		$id=$wpdb->get_var("SELECT id FROM $wpdb->uci_results_riders WHERE name='{$name}'");

		return $id;
	}

	/**
	 * nats function.
	 *
	 * @access public
	 * @return void
	 */
	public function nats() {
		global $wpdb;

		$nats=$wpdb->get_col("SELECT DISTINCT(nat) FROM $wpdb->uci_results_riders ORDER BY nat ASC");

		return $nats;
	}

	/**
	 * get_stats function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_stats($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$stats=new stdClass();
		$stats->final_rankings=$this->get_rider_final_rankings($rider_id);
		$stats->wins=$this->get_rider_wins($rider_id);
		$stats->podiums=$this->get_rider_podiums($rider_id);

		return $stats;
	}

	/**
	 * get_rider_final_rankings function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_rider_final_rankings($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$current_season=uci_results_get_current_season();
		$rankings=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_rider_rankings WHERE rider_id=$rider_id AND week=(SELECT MAX(week) FROM $wpdb->uci_results_rider_rankings) AND season!='$current_season' GROUP BY season");

		if (!count($rankings))
			return false;

		return $rankings;
	}

	/**
	 * get_rider_wins function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_rider_wins($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$wins=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_results WHERE rider_id=$rider_id AND place=1 ORDER BY race_id");

		if (!count($wins))
			return false;

		return $wins;
	}

	/**
	 * get_rider_podiums function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_rider_podiums($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$podiums=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_results WHERE rider_id=$rider_id AND (place=1 OR place=2 OR place=3) ORDER BY race_id");

		if (!count($podiums))
			return false;

		return $podiums;
	}

}

$ucicurl_riders=new UCIResultsRiders();
?>