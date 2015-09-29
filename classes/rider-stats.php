<?php
/**
 * RiderStats class.
 *
 * @since Version 1.6.1
 */
class RiderStats {

	public $version='0.1.2';
	public $riders_pagination_trainsient_variable='riders_pagination_array';
	public $admin_url_vars='?page=uci-cross&tab=riders';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

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
		$rank=1;
		$pagination=true;
		$paged=1;
		$per_page=50;
		$riders=$this->get_riders($args);

		if (isset($_GET['paged']))
			$paged=$_GET['paged'];

		if ($pagination) :
			if ($paged!=1) :
				$start=($paged-1)*$per_page;
				$rank=$start+1;
			else :
				$start=0;
			endif;

			$riders=object_slice($riders,$start,$per_page); // limit (pagination)
		endif;

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
					$html.='<div class="rank col-md-1">'.$rank.'</div>';
					$html.='<div class="rider col-md-3">'.$rider->name.'</div>';
					$html.='<div class="uci col-md-1">'.$rider->uci_points.'</div>';
					$html.='<div class="wcp col-md-1">'.$rider->wcp_points.'</div>';
					$html.='<div class="winning col-md-1">'.$rider->weighted_winning_perc.'</div>';
					$html.='<div class="sos col-md-1">'.$rider->sos.'</div>';
					$html.='<div class="total col-md-1">'.$rider->total.'</div>';
				$html.='</div>';
				$rank++;
			endforeach;

			$html.=$this->rider_pagination(array(
				'paged' => $paged,
				'per_page' => $per_page
			));

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
		$counter=0;
		$where=null;

		$default_args=array(
			'override' => false, // allows us to force override transient
			'season' => false
		);
		$args=array_merge($default_args,$user_args);

		extract($args);

		if (!$override) :
			if (false===get_transient($this->riders_pagination_trainsient_variable)) :
				// do nothing, there is no transient, we will run the whole thing
			else :
				return get_transient($this->riders_pagination_trainsient_variable);
			endif;
		endif;


		$wcp_sql="
			SELECT total FROM(
				SELECT
					SUM(results.par) AS total
				FROM `wp_uci_races` AS races
				LEFT JOIN `wp_uci_rider_data` AS results
				ON races.code=results.code
				WHERE results.par !='' AND races.class='CDM'
				GROUP BY races.code
				WITH ROLLUP
			) t
			ORDER BY total DESC
			LIMIT 1
		";
		$wcp_total=$wpdb->get_var($wcp_sql);

		$uci_sql="
			SELECT total FROM(
				SELECT
					SUM(results.par) AS total
				FROM `wp_uci_races` AS races
				LEFT JOIN `wp_uci_rider_data` AS results
				ON races.code=results.code
				WHERE results.par !=''
				GROUP BY races.code
				WITH ROLLUP
			) t
			ORDER BY total DESC
			LIMIT 1
		";
		$uci_total=$wpdb->get_var($uci_sql);

		/*
		if ($season)
			$where=" WHERE season='$season'";
		ARGS -season, race
		*/
		$sql="
			SELECT
				results.name,
				results.place,
				results.par,
				races.code,
				races.class,
				races.fq,
				races.season
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
		";

		/*
				if ($season)
			$where=" WHERE season='$season'";
		*/
		$total_races=$wpdb->get_var("SELECT COUNT(*) FROM ".$uci_curl->table.$where);
		$riders_db=$wpdb->get_results($sql); // filter ie season

		// get all results for rider by grouping by name //
		foreach ($riders_db as $rider) :
			$_clean=str_replace(' ','',$rider->name);

			if (!isset($riders[$_clean]['name']))
				$riders[$_clean]['name']=$rider->name;

			$riders[$_clean]['races'][$rider->code]=$rider; // would be filtered ie season
		endforeach;

		// append some more data (stats) //
		foreach ($riders as $key => $rider) :
			$riders[$key]['total_races']=count($rider['races']);

			if ($season)
				$riders[$key]['season']=$season;

			$rider_points=$this->get_rider_points($rider['races']);

			$riders[$key]['uci_points']=$rider_points['uci'];
			$riders[$key]['wcp_points']=$rider_points['cdm'];
			$riders[$key]['winning_perc']=$this->get_rider_winning_perc($rider['races']);
			$riders[$key]['race_perc']=number_format($riders[$key]['total_races']/$total_races,3);
			$riders[$key]['weighted_winning_perc']=number_format(($riders[$key]['winning_perc']+$riders[$key]['race_perc'])/2,3);
			$riders[$key]['sos']=$this->get_sos($rider['races'],$total_races,$season);
			$riders[$key]['uci_perc']=$riders[$key]['uci_points']/$uci_total;
			$riders[$key]['wcp_perc']=$riders[$key]['wcp_points']/$wcp_total;
			$riders[$key]['total']=$this->get_rider_final_number(array(
				'uci' => $riders[$key]['uci_points'],
				'wcp' => $riders[$key]['wcp_points'],
				'winning_perc' => $riders[$key]['winning_perc'],
				'sos' => $riders[$key]['sos'],
				'uci_total' => $uci_total,
				'wcp_total' => $wcp_total,
				'rider_total_races' => $riders[$key]['total_races'],
				'season_total_races' => $total_races,
			));
		endforeach;

		$riders=$this->sort_riders($riders);

		set_transient('total_riders_count',count($riders),HOUR_IN_SECONDS);

		$riders=json_decode(json_encode($riders),FALSE); // make object

		set_transient($this->riders_pagination_trainsient_variable,$riders,HOUR_IN_SECONDS);

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
	 * sort_riders function.
	 *
	 * @access public
	 * @param bool $riders (default: false)
	 * @param array $args (default: array())
	 * @return void
	 */
	public function sort_riders($riders=false,$args=array()) {
		if (!$riders)
			return false;

		$default_args=array(
			'arr' => array(),
			'sort_order' => SORT_DESC,
			'sort_flags' => SORT_NUMERIC,
		);
		$args=array_merge($default_args,$args);

		extract($args);

		if (empty($arr)) :
			foreach ($riders as $rider) :
  	  	$arr[]=$rider['total'];
			endforeach;
		endif;

		array_multisort($arr,SORT_DESC,SORT_NUMERIC,$riders);

		return $riders;
	}

	public function rider_pagination($args=array()) {
		$html=null;
		$total_riders=0;
		$default_args=array(
			'pagination' => true,
			'per_page' => 10
		);
		$args=array_merge($default_args,$args);
		extract($args);

		if (!$pagination)
			return false;

		if (false===get_transient('total_riders_count')) :
			return false;
		else :
			$total_riders=get_transient('total_riders_count');
		endif;

		$max_pages=$total_riders/$per_page;

		$prev_page=$paged-1;
		$next_page=$paged+1;

		$html.='<div class="rider-pagination uci-pagination">';
			if ($paged!=1)
				$html.='<div class="prev-page"><a href="'.admin_url($this->admin_url_vars).'&paged='.$prev_page.'">Previous</a></div>';

			if ($paged!=$max_pages)
				$html.='<div class="next-page"><a href="'.admin_url($this->admin_url_vars).'&paged='.$next_page.'">Next</a></div>';
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