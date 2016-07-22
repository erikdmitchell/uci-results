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
			FROM {$wpdb->ucicurl_results}
			LEFT JOIN {$wpdb->ucicurl_races}
			ON {$wpdb->ucicurl_results}.race_id={$wpdb->ucicurl_races}.id
			WHERE rider_id = $rider_id
			ORDER BY date DESC
		";
		$rider=$wpdb->get_row("SELECT * FROM $wpdb->ucicurl_riders WHERE id=$rider_id");

		if ($results)
			$rider->results=$wpdb->get_results($results_sql);

		if ($rankings)
			$rider->rankings=array();

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

		$id=$wpdb->get_var("SELECT id FROM {$wpdb->ucicurl_riders} WHERE name='{$name}'");

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

		$nats=$wpdb->get_col("SELECT DISTINCT(nat) FROM $wpdb->ucicurl_riders ORDER BY nat ASC");

		return $nats;
	}

}

$ucicurl_riders=new UCIcURLRiders();
?>