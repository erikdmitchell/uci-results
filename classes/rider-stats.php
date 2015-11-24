<?php
/**
 * RiderStats class.
 *
 * @since Version 1.0.1
 */
class RiderStats {

	public $date_format='M. j Y';

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
	 * get_riders() is no longer used on public site and needs to be renamed
	 *
	 * @access public
	 * @param array $user_args (default: array())
	 * @return void
	 */
	public function get_riders($user_args=array()) {
		global $wpdb,$uci_curl,$wp_query;

		$riders=array();
		$limit=null;
		$where=array();
		$rank=1;
		$total_divider=4;
		$dates='';
		$org_orderby=false;
		$default_args=array(
			'season' => '2015/2016',
			'pagination' => true,
			'paged' => 1,
			'per_page' => 15,
			'limit' => false,
			'order_by' => 'total',
			'order' => 'DESC',
			'name' => false,
			//'start_date' => false,
			//'end_date' => false
		);
		$args=array_merge($default_args,$user_args);
/*
echo '<pre>';
print_r($args);
echo '</pre>';
*/
		extract($args);

		// we have a afew scenarios to alter our limit and/or where - name (single result), paginated result or basic limit //
		if ($name) :
			$limit='';
			$where[]="season_points.name=\"$name\"";
		elseif ($pagination) :
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

		// setup some where stuff //
		$where[]="season_points.season='{$season}'";

		// run our where //
		if (!empty($where)) :
			$where=implode(' AND ',$where);
		else :
			$where='';
		endif;

		// our rank can be off if we sort by anything besides total, so we do that now //
		if ($order_by!='total') :
			$org_orderby=$order_by;
			$order_by='total';
		endif;

		$sql="
			SELECT
				@curRow := @curRow + 1 AS rank,
				t.*
			FROM (
				SELECT
					season_points.name AS name,
					season_points.nat AS nat,
					season_points.total AS uci_total,
					season_points.cdm AS wcp_total,
					wins.win_perc AS win_perc,
					sos.sos AS sos,
					sos.rank AS sos_rank,
					SUM((season_points.total+sos.sos+wins.win_perc)/3) AS total
				FROM $uci_curl->rider_season_uci_points AS season_points
				LEFT JOIN $uci_curl->uci_rider_season_sos AS sos
				ON season_points.name=sos.name
				LEFT JOIN $uci_curl->uci_rider_season_wins AS wins
				ON season_points.name=wins.name
				WHERE $where
				GROUP BY season_points.name
			) t
			JOIN (SELECT @curRow := 0) r
			ORDER BY $order_by $order
			$limit
		";

		//$wpdb->query("SET SQL_BIG_SELECTS=1"); // fixes a minor sql bug

		$riders=$wpdb->get_results($sql);

		// if pagination, set our max pages var //
		if ($pagination) :
			$max_riders=$wpdb->get_var("SELECT COUNT(*) FROM wp_uci_rider_season_points WHERE season='{$season}'");
			$wp_query->uci_curl_max_pages=$max_riders;
		endif;

		// add rank, if no name run rank if name, run all and get rank //
		if ($name) :
			//$real_rank=0;
			$sql="
				SELECT
					@curRow := @curRow + 1 AS rank,
					t.*
				FROM (
					SELECT
						season_points.name AS name,
						SUM((season_points.total+sos.sos+wins.win_perc)/3) AS total
					FROM $uci_curl->rider_season_uci_points AS season_points
					LEFT JOIN $uci_curl->uci_rider_season_sos AS sos
					ON season_points.name=sos.name
					LEFT JOIN $uci_curl->uci_rider_season_wins AS wins
					ON season_points.name=wins.name
					WHERE season_points.season='{$season}'
					GROUP BY season_points.name
				) t
				JOIN (SELECT @curRow := 0) r
				ORDER BY $order_by $order
			";
			$riders_db=$wpdb->get_results($sql);

			// get real rank //
			foreach ($riders_db as $rider) :
				if ($rider->name==$name)
					$riders[0]->rank=$rider->rank;
			endforeach;
		endif;

		// clean variables //
		foreach ($riders as $rider) :
			$rider->sos=number_format($rider->sos,3);
			$rider->total=number_format($rider->total,3);
		endforeach;

		// if order by is not rank, do that here //
		if ($org_orderby) :
			if (strpos($org_orderby,',') === false) : // checks if we have multiple sorts -- NEED A METHOD FOR THIS
				$order=array();
				foreach ($riders as $rider) :
					$order[]=$rider->$org_orderby;
				endforeach;
				array_multisort($order,SORT_ASC,$riders);
			endif;
		endif;

		if ($name)
			$riders=$riders[0];

		return $riders;
	}

	/**
	 * get_riders_from_weekly_rank function.
	 *
	 * This is a user facing get_riders() type function
	 * get_riders() is no longer used on public site and needs to be renamed
	 *
	 * @access public
	 * @param array $args (default: array())
	 * @return void
	 */
/*
	public function get_riders_from_weekly_rank($args=array()) {
		global $wpdb,$uci_curl,$wp_query;

		$limit=null;
		$where=array();
		$default_args=array(
			'per_page' => 15,
			'paged' => 1,
			'order_by' => 'rank',
			'order' => 'ASC',
			'name' => false,
			'season' => false,
			'start_date' => false, // not setup yet
			'end_date' => false, // not setup yet
			'week' => false, // not setup yet
			'class' => false,
			'nat' => false,
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

		if ($week)
			$where[]="week='{$week}'";

		if (!empty($where)) :
			$where=' WHERE '.implode(' AND ',$where);
		else :
			$where="";
		endif;

		// we need some tweaks to our order by statement //
		if ($order_by=='rank') :
			$order_by="CASE rank WHEN 0 THEN 99999 ELSE rank END $order";
		else :
			$order_by="$order_by $order";
		endif;

		// setup our pagination aka limit //
		if ($per_page>0) :
			if ($paged==0) :
				$start=0;
			else :
				$start=$per_page*($paged-1);
			endif;
			$end=$per_page;
			$limit="LIMIT $start,$end";
			$rank=$start+1;
		//elseif ($limit) :
			//$limit="LIMIT $limit";
		else :
			//
		endif;

		$sql="
			SELECT
				name,
				MAX(nat) AS nat,
				GROUP_CONCAT(season) AS season,
				GROUP_CONCAT(week) AS week,
				GROUP_CONCAT(start_date) AS start_date,
				GROUP_CONCAT(end_date) AS end_date,
				GROUP_CONCAT(race_perc) AS race_perc,
				GROUP_CONCAT(races) AS races,
				GROUP_CONCAT(rank) AS rank,
				GROUP_CONCAT(sos) AS sos,
				GROUP_CONCAT(total) AS total,
				GROUP_CONCAT(uci) AS uci,
				GROUP_CONCAT(uci_perc) AS uci_perc,
				GROUP_CONCAT(wcp) AS wcp,
				GROUP_CONCAT(wcp_perc) AS wcp_perc,
				GROUP_CONCAT(wins) AS wins,
				GROUP_CONCAT(win_perc) AS win_perc,
				GROUP_CONCAT(c1) AS c1,
				GROUP_CONCAT(c2) AS c2,
				GROUP_CONCAT(cn) AS cn,
				GROUP_CONCAT(cc) AS cc,
				GROUP_CONCAT(cm) AS cm
			FROM $uci_curl->weekly_rider_rankings_table
			$where
			GROUP BY name
			ORDER BY $order_by
			$limit
		";
		$sql_max="
			SELECT
				name,
				MAX(nat) AS nat,
				GROUP_CONCAT(season) AS season,
				GROUP_CONCAT(week) AS week,
				GROUP_CONCAT(start_date) AS start_date,
				GROUP_CONCAT(end_date) AS end_date,
				GROUP_CONCAT(race_perc) AS race_perc,
				GROUP_CONCAT(races) AS races,
				GROUP_CONCAT(rank) AS rank,
				GROUP_CONCAT(sos) AS sos,
				GROUP_CONCAT(total) AS total,
				GROUP_CONCAT(uci) AS uci,
				GROUP_CONCAT(uci_perc) AS uci_perc,
				GROUP_CONCAT(wcp) AS wcp,
				GROUP_CONCAT(wcp_perc) AS wcp_perc,
				GROUP_CONCAT(wins) AS wins,
				GROUP_CONCAT(win_perc) AS win_perc,
				GROUP_CONCAT(c1) AS c1,
				GROUP_CONCAT(c2) AS c2,
				GROUP_CONCAT(cn) AS cn,
				GROUP_CONCAT(cc) AS cc,
				GROUP_CONCAT(cm) AS cm
			FROM $uci_curl->weekly_rider_rankings_table
			$where
			GROUP BY name
		";

		$wpdb->query("SET SQL_BIG_SELECTS=1"); // fixes a minor sql bug

		$riders=$wpdb->get_results($sql);
		$wp_query->uci_curl_max_pages=count($wpdb->get_results($sql_max));

		return $riders;
	}
*/

	/**
	 * get_rider_results function.
	 *
	 * @access public
	 * @param array $args (default: array())
	 * @return void
	 */
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
				(SELECT SUM(((SELECT SUM(SUM(CASE races.class WHEN 'CM' THEN 5 WHEN 'CDM' THEN 4 WHEN 'CN' THEN 4 WHEN 'CC' THEN 3 WHEN 'C1' THEN 2 WHEN 'C2' THEN 1 ELSE 0 END)/100)) + (SELECT SUM(SUM(fq_table.fq)/1000)))/2)) AS sos
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			LEFT JOIN $uci_curl->fq_table AS fq_table
			ON fq_table.code=races.code
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
		$uci_point=new stdClass();

		// append rank //
		foreach ($db_results as $result) :
			$result->rank=$counter;

			if ($result->rider==$rider) :
				$uci_point=$result;
				break;
			endif;

			$counter++;
		endforeach;

		if (!isset($uci_point->rank)) :
			$uci_point->rider=$rider;
			$uci_point->total=0;
			$uci_point->rank='n/a';
		endif;

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

	/**
	 * get_riders_in_season function.
	 *
	 * @access public
	 * @param bool $season (default: false)
	 * @return void
	 */
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
				$rider_data->nat='';
				$rider_data->rank=0;
				$rider_data->race_perc=0;
				$rider_data->races=0;
				$rider_data->rank=0;
				$rider_data->sos=0;
				$rider_data->total=0;
				$rider_data->uci=0;
				$rider_data->c1=0;
				$rider_data->c2=0;
				$rider_data->cn=0;
				$rider_data->cc=0;
				$rider_data->cm=0;
				$rider_data->uci_perc=0;
				$rider_data->wcp=0;
				$rider_data->wcp_perc=0;
				$rider_data->win_perc=0;
				$rider_data->wins=0;
			endif;

			$data=array(
				'name' => $rider_name,
				'nat' => $rider_data->nat,
				'season' => $season,
				'week' => $week_counter,
				'start_date' => $week[0],
				'end_date' => $week[1],
				'race_perc' => $rider_data->race_perc,
				'races' => $rider_data->races,
				'rank' => $rider_data->rank,
				'sos' => $rider_data->sos,
				'total' => $rider_data->total,
				'uci' => $rider_data->uci,
				'c1' => $rider_data->c1,
				'c2' => $rider_data->c2,
				'cn' => $rider_data->cn,
				'cc' => $rider_data->cc,
				'cm' => $rider_data->cm,
				'uci_perc' => $rider_data->uci_perc,
				'wcp' => $rider_data->wcp,
				'wcp_perc' => $rider_data->wcp_perc,
				'win_perc' => $rider_data->win_perc,
				'wins' => $rider_data->wins,
			);

			$wpdb->insert($uci_curl->weekly_rider_rankings_table,$data);

			if (strtotime($week[1])>strtotime(date('Y-m-d')))
				break;

			$week_counter++;
		endforeach;

		return;
	}

	/**
	 * get_rider_weekly_rank function.
	 *
	 * @access public
	 * @param bool $rider_name (default: false)
	 * @param bool $season (default: false)
	 * @param bool $week (default: false)
	 * @return void
	 */
/*
	function get_rider_weekly_rank($rider_name=false,$season=false,$week=false) {
		global $wpdb,$uci_curl;

		if (!$rider_name || !$season)
			return false;

		$weekly_rankings=$wpdb->get_results("SELECT * FROM $uci_curl->weekly_rider_rankings_table WHERE name=\"{$rider_name}\" AND season='{$season}'");

		return $weekly_rankings;
	}
*/

}

$RiderStats=new RiderStats();
?>