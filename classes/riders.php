<?php

/**
 * UCIcURLRiders class.
 */
class UCIcURLRiders {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {}

	/**
	 * get_rider function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @param bool $results (default: false)
	 * @param bool $rankings (default: false)
	 * @return void
	 */
	public function get_rider($rider_id=0, $results=false, $rankings=false) {
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

		if ($results)
			$rider->results=$wpdb->get_results($results_sql);

		if ($rankings)
			$rider->rankings=$wpdb->get_results($rankings_sql);

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

		$id=$wpdb->get_var("SELECT id FROM {$wpdb->uci_results_riders} WHERE name='{$name}'");

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

}

$ucicurl_riders=new UCIcURLRiders();
?>