<?php
/**
 * RiderStats class.
 *
 * @since Version 1.0.1
 */
class RiderStats {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
	}

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
		$limit=null;
		$where='';
		$rank=1;
		$total_divider=4;
		$dates='';
		$default_args=array(
			'season' => '2015/2016',
			'pagination' => true,
			'paged' => 1,
			'per_page' => 15,
			'limit' => false,
			'order_by' => 'total DESC',
			'start_date' => false,
			'end_date' => false
		);
		$args=array_merge($default_args,$user_args);

		extract($args);

		if ($pagination) :
			if ($paged==0) :
				$start=0;
			else :
				$start=$per_page*($paged-1);
			endif;
			$end=$per_page;
			$limit="LIMIT $start,$end";
			$rank=$start+1;
		elseif ($limit) :
			$limit="LIMIT $limit";
		endif;

		if ($start_date && $end_date) :
			$dates=" AND (STR_TO_DATE(races.date,'%e %M %Y') BETWEEN '{$start_date}' AND '{$end_date}')";

			if ($this->get_total_points(array('type' => 'wcp','season' => $season,'start_date' => $start_date,'end_date' => $end_date))==0) :
				$total_divider--;
			endif;
		elseif ($start_date) :
			$dates=" AND (STR_TO_DATE(races.date,'%e %M %Y') >= '{$start_date}')";

			if ($this->get_total_points(array('type' => 'wcp','season' => $season,'start_date' => $start_date))==0) :
				$total_divider--;
			endif;
		elseif ($end_date) :
			$dates=" AND (STR_TO_DATE(races.date,'%e %M %Y') <= '{$end_date}')";

			if ($this->get_total_points(array('type' => 'wcp','season' => $season,'end_date' => $end_date))==0) :
				$total_divider--;
			endif;
		endif;

		$sql="
			SELECT
				name AS rider,
				nat,
				SUM(uci_total) AS uci,
				SUM(wcp_total) AS wcp,
				SUM(wins) AS wins,
				SUM(races) AS races,
				SUM(wins/races) AS win_perc,
				SUM(uci_races) AS uci_races,
				SUM(races/uci_races) AS race_perc,
				SUM(((wins/races)+(races/uci_races))/2) AS weighted_win_perc,
				SUM(max_uci_points) AS max_uci_points,
				SUM(max_wcp_points) AS max_wcp_points,
				SUM(uci_total/max_uci_points) AS uci_perc,
				COALESCE(SUM(wcp_total/max_wcp_points),0) AS wcp_perc,
				SUM(sos) AS sos,
				(SELECT SUM( (COALESCE(SUM(wcp_total/max_wcp_points),0) + SUM(sos) + SUM(uci_total/max_uci_points) + SUM((((wins/races)+(races/uci_races))/2))) / {$total_divider} ) ) AS total
			FROM

			(
			SELECT
				results.name AS name,
				results.nat AS nat,
				SUM(results.par) AS uci_total,
				0 AS wcp_total,
				SUM(IF(results.place=1,1,0)) AS wins,
				COUNT(results.code) AS races,
				(SELECT COUNT(*) FROM $uci_curl->table WHERE season='$season') AS uci_races,
				(SELECT total FROM( SELECT SUM(results.par) AS total FROM $uci_curl->table AS races LEFT JOIN $uci_curl->results_table AS results ON races.code=results.code WHERE results.place=1 AND races.season='$season' {$dates} GROUP BY races.code WITH ROLLUP ) t ORDER BY total DESC LIMIT 1) AS max_uci_points,
				0 AS max_wcp_points,
				0 AS sos
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			WHERE season='$season'
				{$dates}
			GROUP BY results.name

			UNION

			SELECT
				results.name AS name,
				results.nat AS nat,
				0 AS uci_total,
				SUM(results.par) AS wcp_total,
				0 AS wins,
				0 AS races,
				0 AS uci_races,
				0 AS max_uci_points,
				(SELECT	SUM(results.par) AS points FROM $uci_curl->table AS races LEFT JOIN $uci_curl->results_table AS results ON races.code=results.code WHERE results.place=1 AND races.class='CDM' AND races.season='$season' {$dates}) AS max_wcp_points,
				0 AS sos
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			WHERE season='$season'
				AND races.class='CDM'
				{$dates}
			GROUP BY results.name

			UNION

			SELECT
				results.name,
				results.nat AS nat,
				0 AS uci_total,
				0 AS wcp_total,
				0 AS wins,
				0 AS races,
				0 AS uci_races,
				0 AS max_uci_points,
				0 AS max_wcp_points,
				(SELECT SUM(((SELECT SUM(SUM(CASE races.class WHEN 'CM' THEN 5 WHEN 'CDM' THEN 4 WHEN 'CN' THEN 3 WHEN 'C1' THEN 2 WHEN 'C2' THEN 1 ELSE 0 END)/100)) + (SELECT SUM(SUM(races.fq)/100)))/2)) AS sos
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			WHERE season='$season'
				{$dates}
			GROUP BY results.name

			) t
			GROUP BY name
			ORDER BY $order_by
			$limit
		";

		$riders=$wpdb->get_results($sql);

		$max_riders=$wpdb->get_results("SELECT name FROM $uci_curl->results_table GROUP BY name");
		$wp_query->uci_curl_max_pages=$wpdb->num_rows; // set max

		// add rank //
		foreach ($riders as $rider) :
			$rider->rank=$rank;
			$rank++;
		endforeach;

		return $riders;
	}

	/**
	 * get_rider function.
	 *
	 * @access public
	 * @param int $name (default: 0)
	 * @return void
	 */
	public function get_rider($name=0) {
		global $wpdb,$uci_curl;

		if (!$name)
			return false;

		$season=get_query_var('season','2015/2016');
		$sql="
			SELECT
				results.place,
				results.nat AS country,
				results.par,
				races.code,
				races.date,
				races.event AS race,
				races.class,
				races.nat AS race_country,
				races.fq
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			WHERE season='$season'
			AND name='$name'
			ORDER BY races.date
		";
		$results=$wpdb->get_results($sql);

		return $results;
	}

	/**
	 * get_country function.
	 *
	 * @access public
	 * @param int $name (default: 0)
	 * @return void
	 */
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

	// rider not used yet
	public function get_rank_seasons($rider=false) {
		global $wpdb,$uci_curl;

		$sql="
			SELECT
				season
			FROM $uci_curl->table
			WHERE season!=false
			GROUP BY season
			ORDER BY season ASC
		";

		$seasons=$wpdb->get_col($sql);

		return $seasons;
	}

	/**
	 * get_rider_sos function.
	 *
	 * @access public
	 * @param bool $rider (default: false)
	 * @param bool $season (default: false)
	 * @return void
	 */
	public function get_rider_sos($rider=false,$season=false) {
		global $wpdb,$uci_curl;

		if (!$rider || !$season)
			return false;

		$sql="
			SELECT
				results.name AS rider,
				(SELECT SUM(((SELECT SUM(SUM(CASE races.class WHEN 'CM' THEN 5 WHEN 'CDM' THEN 4 WHEN 'CN' THEN 3 WHEN 'C1' THEN 2 WHEN 'C2' THEN 1 ELSE 0 END)/100)) + (SELECT SUM(SUM(races.fq)/100)))/2)) AS sos
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			WHERE races.season='{$season}'
			GROUP BY rider
			ORDER BY sos DESC
		";
		$db_results=$wpdb->get_results($sql);
		$counter=1;
		$sos=false;

		// append rank //
		foreach ($db_results as $result) :
			$result->rank=$counter;

			if ($result->rider==$rider) :
				$sos=$result;
				break;
			endif;

			$counter++;
		endforeach;

		return $sos;
	}

	/**
	 * get_rider_uci_points function.
	 *
	 * @access public
	 * @param bool $rider (default: false)
	 * @param bool $season (default: false)
	 * @param string $type (default: 'uci')
	 * @return void
	 */
	public function get_rider_uci_points($rider=false,$season=false,$type='uci') {
		global $wpdb,$uci_curl;

		if (!$rider || !$season)
			return false;

		$where='';
		if ($type=='wcp')
			$where="AND races.class='CDM'";

		$sql="
			SELECT
				results.name AS rider,
				SUM(results.par) AS total
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			WHERE races.season='{$season}'
			{$where}
			GROUP BY rider
			ORDER BY total DESC
		";
		$db_results=$wpdb->get_results($sql);
		$counter=1;
		$uci_point=false;

		// append rank //
		foreach ($db_results as $result) :
			$result->rank=$counter;

			if ($result->rider==$rider) :
				$uci_point=$result;
				break;
			endif;

			$counter++;
		endforeach;

		return $uci_point;
	}

	/**
	 * get_rider_winning_perc function.
	 *
	 * @access public
	 * @param bool $rider (default: false)
	 * @param bool $season (default: false)
	 * @return void
	 */
	public function get_rider_winning_perc($rider=false,$season=false) {
		global $wpdb,$uci_curl;

		if (!$rider || !$season)
			return false;

		$sql="
			SELECT
				rider,
				SUM(wins/races) AS winning_perc
			FROM (
				SELECT
					results.name AS rider,
					SUM(IF(results.place=1,1,0)) AS wins,
					COUNT(results.code) AS races
				FROM $uci_curl->results_table AS results
				LEFT JOIN $uci_curl->table AS races
				ON results.code=races.code
				WHERE races.season='{$season}'
				GROUP BY rider
			) t
			GROUP BY rider
			ORDER BY winning_perc DESC, races DESC
		";
		$db_results=$wpdb->get_results($sql);
		$counter=1;
		$win_perc=false;

		// append rank //
		foreach ($db_results as $result) :
			$result->rank=$counter;

			if ($result->rider==$rider) :
				$win_perc=$result;
				break;
			endif;

			$counter++;
		endforeach;

		return $win_perc;
	}

	/**
	 * get_rider_total function.
	 *
	 * @access public
	 * @param bool $rider (default: false)
	 * @param bool $season (default: false)
	 * @return void
	 */
	public function get_rider_total($rider=false,$season=false) {
		global $wpdb,$uci_curl;

		if (!$rider || !$season)
			return false;

		$riders=$this->get_riders(array(
			'season' => $season,
			'pagination' => false
		));
		$total=false;

		// get rider //
		foreach ($riders as $_rider) :
			if ($_rider->rider==$rider) :
				$total=$_rider;
				break;
			endif;
		endforeach;

		return $total;
	}

// SLOW??? //
	public function get_total_points($user_args=array()) {
		global $wpdb,$uci_curl;

		$default_args=array(
			'type' => 'uci',
			'season' => false,
			'start_date' => false,
			'end_date' => false
		);
		$args=array_merge($default_args,$user_args);

		extract($args);

		$where='';

		if ($season)
			$where=" AND races.season='{$season}'";

		if ($type=='wcp')
			$where.=" AND races.class='CDM'";

		if ($start_date && $end_date) :
			$where.=" AND (STR_TO_DATE(races.date,'%e %M %Y') BETWEEN '{$start_date}' AND '{$end_date}')";
		elseif ($start_date) :
			$where.=" AND (STR_TO_DATE(races.date,'%e %M %Y') >= '{$start_date}')";
		elseif ($end_date) :
			$where.=" AND (STR_TO_DATE(races.date,'%e %M %Y') <= '{$end_date}')";
		endif;

		$sql="
			SELECT
				COALESCE(SUM(results.par),0) AS uci_total
			FROM wp_uci_races AS races
			LEFT JOIN wp_uci_rider_data AS results
			ON races.code=results.code
			WHERE results.place=1
			$where
		";
		$points=$wpdb->get_var($sql);

		return $points;
	}


	public function get_riders_in_season($season=false) {
		global $wpdb,$uci_curl;

		if (!$season)
			return false;

		$sql="
			SELECT
				results.name
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			WHERE races.season='{$season}'
			GROUP BY results.name
		";
		$riders=$wpdb->get_col($sql);

		return $riders;
	}

	/**
	 * generate_total_rank_per_week function.
	 *
	 * @access public
	 * @param bool $rider_name (default: false)
	 * @param bool $season (default: false)
	 * @return void
	 */
	public function generate_total_rank_per_week($rider_name=false,$season=false) {
		global $wpdb,$uci_curl;

		if (!$rider_name || !$season)
			return false;

		$CrossSeasons=new CrossSeasons();
		$weeks=$CrossSeasons->get_weeks($season);
		$rider_weeks_in_db=$wpdb->get_col("SELECT week FROM $uci_curl->weekly_rider_rankings_table WHERE name=\"{$rider_name}\" AND season='{$season}'");
		$week_counter=1;

		foreach ($weeks as $week) :
			$flag=0;
			$rider_data=array();

			// skip if week already in db //
			if (in_array($week_counter,$rider_weeks_in_db))
				continue;

			$riders=$this->get_riders(array(
				'season' => $season,
				'pagination' => false,
				'end_date' => $week[1]
			));

			foreach ($riders as $r) :
				if ($r->rider==$rider_name) :
					$rider_data=$r;
					$flag=1;
					break;
				endif;
			endforeach;

			// set an empty if nothing for that week
			if (!$flag) :
				$rider_data=new stdClass();
				$rider_data->rank=0;
			endif;

			$data=array(
				'name' => $rider_name,
				'season' => $_POST['season'],
				'week' => $week_counter,
				'start_date' => $week[0],
				'end_date' => $week[1],
				'data' => serialize($rider_data),
			);

			$wpdb->insert($uci_curl->weekly_rider_rankings_table,$data);

			if (strtotime($week[1])>strtotime(date('Y-m-d')))
				break;

			$week_counter++;
		endforeach;

		return;
	}

}

$RiderStats=new RiderStats();
?>