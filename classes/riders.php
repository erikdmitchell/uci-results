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
		if ($per_page>0) :
			$max_riders=$wpdb->get_var("SELECT COUNT(*) FROM $wpdb->ucicurl_riders $where");
			$wp_query->uci_curl_max_pages=$max_riders;
		endif;

		return $riders;
	}

/*
	function get_rider_results($args=array()) {
		global $wpdb,$uci_curl;

		$html=null;
		$where=array();
		$default_args=array(
			'order_by' => 'date',
			'order' => 'DESC',
			'name' => false,
			'season' => false,
			'class' => false,
			'nat' => false,
			'place' => false
		);
		$args=array_merge($default_args,$args);

		extract($args);

		// setup our potential where statement //
		if ($name)
			$where[]="name='{$name}'";

		if ($season)
			$where[]="season='{$season}'";

		if ($class)
			$where[]="class='{$class}'";

		if ($nat)
			$where[]="nat='{$nat}'";

		if ($place)
			$where[]="place='{$place}'";

		if (!empty($where)) :
			$where=' WHERE '.implode(' AND ',$where);
		else :
			$where="";
		endif;

		$sql="
			SELECT
				name,
				place,
				results.nat,
				par AS points,
				season,
				STR_TO_DATE(date,'%e %M %Y') AS date,
				event,
				races.code,
				class,
				races.nat AS race_country,
				fq_table.fq
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			LEFT JOIN $uci_curl->fq_table AS fq_table
			ON results.code=fq_table.code
			$where
			ORDER BY $order_by $order
		";
		$wpdb->query("SET SQL_BIG_SELECTS=1"); // fixes a minor sql bug
		$results=$wpdb->get_results($sql);

		return $results;
	}
*/

/*
	public function get_country($name=0) {
		global $wpdb,$uci_curl;

		if (!$name)
			return false;

		$season=get_query_var('season','2015/2016');
		$sql="
			SELECT
				results.name AS rider,
				results.place,
				CASE WHEN results.par IS NULL OR results.par='' THEN 0 ELSE results.par END AS points,
				races.date,
				races.code,
				races.event AS race,
				races.class,
				races.nat AS race_country,
				races.fq
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			WHERE season='$season'
			AND results.nat='$name'
			ORDER BY results.name,races.date,results.place
		";

		$results=$wpdb->get_results($sql);

		return $results;
	}
*/

/*
	public function get_latest_rankings_week($season=false) {
		global $wpdb,$uci_curl;

		$week=$wpdb->get_var("SELECT MAX(week) FROM $uci_curl->uci_rider_rankings	WHERE season='{$season}'");

		return $week;
	}
*/

	/**
	 * riders function.
	 *
	 * @access public
	 * @return void
	 */
	public function riders() {
		return $this->get_riders();
	}

	public function nats() {
		global $wpdb;

		$nats=$wpdb->get_col("SELECT DISTINCT(nat) FROM $wpdb->ucicurl_riders ORDER BY nat ASC");

		return $nats;
	}

}

$ucicurl_riders=new UCIcURLRiders();
?>