<?php

/**
 * UCIcURLRiders class.
 */
class UCIcURLRiders {

	public $admin_pagination=array();

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
	 * @return void
	 */
	public function get_rider($rider_id=0) {
		global $wpdb;

		// if not an int, it's a slug //
		if (!is_integer($rider_id)) :
			$rider_id=ucicurl_get_rider_id($rider_id);
		endif;

		$where="rider_id=".absint($rider_id);

		$sql="
			SELECT *
			FROM {$wpdb->ucicurl_results}
			LEFT JOIN {$wpdb->ucicurl_races}
			ON {$wpdb->ucicurl_results}.race_id={$wpdb->ucicurl_races}.id
			WHERE $where
			ORDER BY date
		";
		$rider=$wpdb->get_row("SELECT * FROM {$wpdb->ucicurl_riders} WHERE id={$rider_id}");
		$rider->results=$wpdb->get_results($sql);

		return $rider;
	}

	/**
	 * admin_pagination function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_pagination() {
		$pagination=new UCIcURLPagination($this->admin_pagination['total'], $this->admin_pagination['limit'], $this->admin_pagination['url']);

		echo $pagination->get_pagination();
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