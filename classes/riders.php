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
	 * get_riders function.
	 *
	 * @access public
	 * @param array $user_args (default: array())
	 * @return void
	 */
	public function get_riders($user_args=array()) {
		global $wpdb,$uci_curl,$wp_query;

		$riders=array();
		$limit=false;
		$where=array();
		$paged=get_query_var('paged',1);
		$default_args=array(
			'per_page' => 30,
			'order_by' => 'name',
			'order' => 'ASC',
			'name' => false,
			'nat' => false,
		);
		$args=array_merge($default_args,$user_args);

		// check filters //
		if (isset($_POST['ucicurl_admin']) && wp_verify_nonce($_POST['ucicurl_admin'], 'filter_riders'))
			$args=wp_parse_args($_POST, $args);

		// check search //
		if (isset($_GET['search']) && $_GET['search']!='')
			$where[]="name LIKE '%{$_GET['search']}%'";

		extract($args);

		// if we dont have a name and we have a limit, setup pagination //
		if (!$name && $per_page>0) :
			if ($paged==0) :
				$start=0;
			else :
				$start=$per_page*($paged-1);
			endif;
			$end=$per_page;
			$limit="LIMIT $start,$end";
			//$rank=$start+1;
		endif;

		// setup our where stuff //
		if ($name)
			$where[]="name='{$name}'";

		if ($nat)
			$where[]="nat='{$nat}'";

		// run our where //
		if (!empty($where)) :
			$where='WHERE '.implode(' AND ',$where);
		else :
			$where='';
		endif;

		 $sql="
			SELECT
				*
			FROM $wpdb->ucicurl_riders
			$where
			ORDER BY $order_by $order
			$limit
		";

		$riders=$wpdb->get_results($sql);

		//set our max pages var for pagination //
/*
		if ($per_page>0) :
			$max_riders=$wpdb->get_var("SELECT COUNT(*) FROM $wpdb->ucicurl_riders $where");
			$wp_query->uci_curl_max_pages=$max_riders;
		endif;
*/

		return $riders;
	}

	/**
	 * get_rider function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_rider($rider_id=0) {
		global $wpdb;

		$sql="
			SELECT *
			FROM {$wpdb->ucicurl_results}
			LEFT JOIN {$wpdb->ucicurl_races}
			ON {$wpdb->ucicurl_results}.race_id={$wpdb->ucicurl_races}.id
			WHERE rider_id={$rider_id}
		";
		$rider=$wpdb->get_row("SELECT * FROM {$wpdb->ucicurl_riders} WHERE id={$rider_id}");
		$rider->results=$wpdb->get_results($sql);

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
	 * riders function.
	 *
	 * @access public
	 * @return void
	 */
	public function riders() {
		return $this->get_riders();
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