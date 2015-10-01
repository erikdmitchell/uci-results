<?php
/**
 * RiderStats class.
 *
 * @since Version 1.6.1
 */
class RiderStats {

	//public $version='0.1.2';
	//public $riders_pagination_trainsient_variable='riders_pagination_array';
	public $admin_url_vars='?page=uci-cross&tab=riders';
	public $pagination=array();

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->pagination=array(
			'active' => true,
			'paged' => 1,
			'per_page' => 15,
			'max' => 0
		);
	}

	/**
	 * get_season_rider_rankings function.
	 *
	 * @access public
	 * @param array $user_args (default: array())
	 * @return void
	 */
	public function get_season_rider_rankings($args=array()) {
		global $wpdb,$uci_curl;

		$html=null;
		$riders=$this->get_riders($args);

		$html.='<h3>Rider Rankings</h3>';

		$html.='<div id="season-rider-rankings" class="season-rider-rankings">';
			$html.='<div class="header row">';
				$html.='<div class="rank col-md-1">Rank</div>';
				$html.='<div class="rider col-md-3">Rider</div>';
				$html.='<div class="uci col-md-1">UCI</div>';
				$html.='<div class="wcp col-md-1">WCP</div>';
				$html.='<div class="winning col-md-1">Win %*</div>';
				$html.='<div class="sos col-md-1">SOS</div>';
				$html.='<div class="total col-md-1">Total</div>';
			$html.='</div>';

			foreach ($riders as $rider) :
				$html.='<div class="row">';
					$html.='<div class="rank col-md-1">'.$rider->rank.'</div>';
					$html.='<div class="rider col-md-3">'.$rider->rider.'</div>';
					$html.='<div class="uci col-md-1">'.$rider->uci.'</div>';
					$html.='<div class="wcp col-md-1">'.$rider->wcp.'</div>';
					$html.='<div class="winning col-md-1">'.number_format($rider->weighted_win_perc,3).'</div>';
					$html.='<div class="sos col-md-1">'.$rider->sos.'</div>';
					$html.='<div class="total col-md-1">'.number_format($rider->total,3).'</div>';
				$html.='</div>';
			endforeach;

			$html.=$this->rider_pagination();
		$html.='</div>';

		return $html;
	}

	/**
	 * get_riders function.
	 *
	 * @access public
	 * @param array $user_args (default: array())
	 * @return void
	 */
	public function get_riders($user_args=array()) {
		global $wpdb,$uci_curl;

		$riders=array();
		//$counter=0;
		$limit=null;
		$rank=1;
		$default_args=array(
			'season' => '2015/2016',
		);
		$args=array_merge($default_args,$user_args);

		if (isset($_GET['paged']))
			$this->pagination['paged']=$_GET['paged'];

		extract($args);

		if ($this->pagination['active']) :
			$start=$this->pagination['per_page']*($this->pagination['paged']-1);
			$end=$this->pagination['per_page'];
			$limit="LIMIT $start,$end";
			$rank=$start+1;
		endif;

		$sql="
			SELECT
				name AS rider,
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
				(SELECT SUM( (COALESCE(SUM(wcp_total/max_wcp_points),0) + SUM(sos) + SUM(uci_total/max_uci_points) + SUM((((wins/races)+(races/uci_races))/2))) / 4 ) ) AS total
			FROM

			(
			SELECT
				results.name AS name,
				SUM(results.par) AS uci_total,
				0 AS wcp_total,
				SUM(IF(results.place=1,1,0)) AS wins,
				COUNT(results.code) AS races,
				(SELECT COUNT(*) FROM $uci_curl->table WHERE season='$season') AS uci_races,
				(SELECT total FROM( SELECT SUM(results.par) AS total FROM $uci_curl->table AS races LEFT JOIN $uci_curl->results_table AS results ON races.code=results.code WHERE results.place=1 AND races.season='$season' GROUP BY races.code WITH ROLLUP ) t ORDER BY total DESC LIMIT 1) AS max_uci_points,
				0 AS max_wcp_points,
				0 AS sos
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			WHERE season='$season'
			GROUP BY results.name

			UNION

			SELECT
				results.name AS name,
				0 AS uci_total,
				SUM(results.par) AS wcp_total,
				0 AS wins,
				0 AS races,
				0 AS uci_races,
				0 AS max_uci_points,
			/* 	(SELECT total FROM( SELECT SUM(results.par) AS total FROM $uci_curl->table AS races LEFT JOIN $uci_curl->results_table AS results ON races.code=results.code WHERE results.place=1 AND races.class='CDM' AND races.season='$season' GROUP BY races.code WITH ROLLUP ) t ORDER BY total DESC LIMIT 1) AS max_wcp_points, */
				(SELECT	SUM(results.par) AS points FROM $uci_curl->table AS races LEFT JOIN $uci_curl->results_table AS results ON races.code=results.code WHERE results.place=1 AND races.class='CDM' AND races.season='$season') AS max_wcp_points,
				0 AS sos
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			WHERE season='$season'
				AND races.class='CDM'
			GROUP BY results.name

			UNION

			SELECT
				results.name,
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
			GROUP BY results.name

			) t
			GROUP BY name
			ORDER BY total DESC
			$limit
		";

		$riders=$wpdb->get_results($sql);
		//$this->pagination['max']=$wpdb->num_rows;

		// add rank //
		foreach ($riders as $rider) :
			$rider->rank=$rank;
			$rank++;
		endforeach;

		return $riders;
	}

	/**
	 * get_rider_points function.
	 *
	 * @access protected
	 * @param bool $races (default: false)
	 * @return array
	 */
	protected function get_rider_points($races=false) {
		if (!$races)
			return false;

		$points=array();
		$points['cdm']=0;
		$points['uci']=0;

		foreach ($races as $code => $race) :
			if ($race->class=='CDM') :
				$points['cdm']=$points['cdm']+$race->par;
			else :
				$points['uci']=$points['uci']+$race->par;
			endif;
		endforeach;

		return $points;
	}

	/**
	 * get_rider_winning_perc function.
	 *
	 * @access protected
	 * @param bool $races (default: false)
	 * @return void
	 */
	protected function get_rider_winning_perc($races=false) {
		if (!$races)
			return 0;

		$winning_perc=0;
		$wins=0;
		$rider_total_races=count($races);

		foreach ($races as $race) :
			if (isset($race->place) && $race->place==1)
				$wins++;
		endforeach;

		$winning_perc=number_format(($wins/$rider_total_races),3);

		return $winning_perc;
	}

	/**
	 * get_sos function.
	 *
	 * @access protected
	 * @param array $races (default: array())
	 * @param int $total_races (default: 0)
	 * @param bool $season (default: false)
	 * @return void
	 */
	protected function get_sos($races=array(),$total_races=0,$season=false) {
		global $wpdb,$uci_curl;

		$sos=0;
		$fq_total=0;
		$rider_total_races=count($races);

		foreach ($races as $race) :
			switch ($race->class) :
				case 'CM' :
					$pts=5;
					break;
				case 'CDM' :
					$pts=4;
					break;
				case 'CN' :
					$pts=3;
					break;
				case 'C1' :
					$pts=2;
					break;
				case 'C2' :
					$pts=1;
					break;
				default:
					$pts=0;
					break;
			endswitch;

			$sos=$sos+$pts;

			if (isset($race->field_quality))
				$fq_total=$fq_total+$race->fq;

		endforeach;

		$fq_total=$fq_total/100;
		$sos=$sos/100;
		$sos=($fq_total+$sos)/2;

		return number_format($sos,3);
	}

	/**
	 * get_rider_final_number function.
	 *
	 * @access protected
	 * @param array $args (default: array())
	 * @return void
	 */
	protected function get_rider_final_number($args=array()) {
		$default_args=array(
			'uci' => 0,
			'wcp' => 0,
			'sos' => 0,
			'winning_perc' => 0,
			'uci_total' => 0,
			'wcp_total' => 0,
			'rider_total_races' => 0,
			'season_total_races' => 0
		);
		$args=array_merge($default_args,$args);
		$race_perc=0;

		extract($args);

		if ($uci_total)
			$uci=number_format($uci/$uci_total,3);

		if ($wcp_total)
			$wcp=number_format($wcp/$wcp_total,3);

		if ($season_total_races!=0)
			$race_perc=number_format($rider_total_races/$season_total_races,3);

		$weighted_winning_perc=($winning_perc+$race_perc)/2;

		//$total=($uci+$wcp+$sos+$winning_perc)/4;
		$total=$uci+$wcp+$sos+$weighted_winning_perc;

		return number_format($total,3);
	}

	/**
	 * rider_pagination function.
	 *
	 * @access public
	 * @return void
	 */
	public function rider_pagination() {
		$html=null;
		$url=$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$next_url='#';
		$prev_url='#';
		$prev_page=$this->pagination['paged']-1;
		$next_page=$this->pagination['paged']+1;

		if (is_admin()) :
			$prev_url=admin_url($this->admin_url_vars)."&paged=$prev_page";
			$next_url=admin_url($this->admin_url_vars)."&paged=$next_page";
		endif;

		if (!$this->pagination['active'])
			return false;

		//$max_pages=$total_riders/$per_page; ERROR RIGHT NOW


//echo $url;
//echo 'p:'.get_query_var('paged');
		$html.='<div class="rider-pagination uci-pagination">';
			if ($this->pagination['paged']!=1)
				$html.='<div class="prev-page"><a href="'.$prev_url.'">Previous</a></div>';

			//if ($paged!=$max_pages)
				$html.='<div class="next-page"><a href="'.$next_url.'">Next</a></div>';
		$html.='</div>';

		return $html;
	}

/*
	public static function get_uci_season_rankings($year=false,$display=true,$sort_field='rank',$sort_type='ASC') {
		if (!$year)
			return false;

		global $wpdb;
		$html=null;
		$rankings=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'uci_season_rankings'." WHERE season='$year'");

		if (!$display)
			return $rankings;

		$rankings=SELF::sort_rankings($sort_field,$sort_type,$rankings);

		$html.='<h3>'.$year.' UCI Season Rankings</h3>';

		$html.='<table>';
			$html.='<thead>';
				$html.='<tr>';
					$html.='<th class="rank">Rank</th>';
					$html.='<th class="name">Name</th>';
					$html.='<th class="nation">Nation</th>';
					$html.='<th class="age">Age</th>';
					$html.='<th class="points">Points</th>';
				$html.='</tr>';
			$html.='</thead>';

			foreach ($rankings as $rank) :
				$html.='<tr>';
					$html.='<td class="rank">'.$rank->rank.'</td>';
					$html.='<td class="name">'.$rank->name.' </td>';
					$html.='<td class="nation">'.$rank->nation.'</td>';
					$html.='<td class="age">'.$rank->age.'</td>';
					$html.='<td class="points">'.$rank->points.'</td>';
				$html.='</tr>';
			endforeach;
		$html.='</table>';

		return $html;
	}
*/

/*
	// gets the final uci rankings per season from the db
	public static function get_uci_season_ranking_seasons($display='list') {
		global $wpdb;
		$html=null;
		$seasons=$wpdb->get_results("SELECT season FROM ".$wpdb->prefix.'uci_season_rankings'." GROUP BY season");

		switch ($display) :
			case 'dropdown' :
				$html.='<select name="season-ranking-seasons" class="season-ranking-seasons">';
					$html.='<option>Select Season</option>';
					foreach ($seasons as $season) :
						$html.='<option value="'.$season->season.'">'.$season->season.'</option>';
					endforeach;
				$html.='</select>';
				break;
			default :
				$html.='<ul class="season-ranking-seasons">';
					foreach ($seasons as $season) :
						$html.='<li>'.$season->season.'</li>';
					endforeach;
				$html.='</ul>';
				break;
		endswitch;

		return $html;
	}
*/

/*
	// used with get_uci_season_rankings
	public static function sort_rankings($field=false,$method=false,$rankings=false) {
		if (!($field) || !($method) || !($rankings))
			return array();

		$method=constant('SORT_'.strtoupper($method));

		foreach ($rankings as $rank) :
    	$arr[] = $rank->$field;
		endforeach;

		array_multisort($arr,$method,SORT_NUMERIC,$rankings);

		return $rankings;
	}
*/


// admin page
/*
			$html.='<form name="riders-admin-page-form" method="post">';

				$html.='<div class="season-dropdown">'.RiderStats::get_uci_season_ranking_seasons('dropdown').'</div>';
				$html.='<input type="submit" name="submit" id="submit" class="button button-primary" value="View Season Rankings">';
				$html.='<input type="submit" name="submit" id="submit" class="button button-primary" value="View UCI Season Rankings">';
				$html.='<input type="hidden" name="riders" value="g2g" />';

			$html.='</form>';

			if (isset($_POST['submit']) && isset($_POST['riders']) && isset($_POST['riders'])=='g2g') :
				switch ($_POST['submit']) :
					case 'View Season Rankings':
						$html.=$rider_stats->get_season_rider_rankings($_POST['season-ranking-seasons']);
						break;
					case 'View UCI Season Rankings':
						$html.=RiderStats::get_uci_season_rankings($_POST['season-ranking-seasons']);
						break;
					default:
						break;
				endswitch;
			endif;
*/
}

$RiderStats=new RiderStats();
?>