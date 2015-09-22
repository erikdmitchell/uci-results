<?php
/**
 * RiderStats class.
 *
 * @since Version 1.6.1
 */
class RiderStats {

	public $version='0.1.2';

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
	public function get_season_rider_rankings($user_args=array()) {
		global $wpdb,$uci_curl;

		$html=null;
		$rank=1;
		$riders=$this->get_riders();

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
		$where=null;
		$default_args=array(
			'season' => false
		);
		$args=array_merge($default_args,$user_args);
		$uci_total=$this->get_total_points('uci',$args['season']);
		$wcp_total=$this->get_total_points('cdm',$args['season']);

		extract($args);

		/*
		if ($season)
			$where=" WHERE season='$season'";
		ARGS -season, race
		*/
		$riders_db=$wpdb->get_results("SELECT * FROM ".$uci_curl->results_table.$where); // filter ie season

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

			$riders[$key]['uci_points']=$this->get_rider_points($rider['races'],'uci');
			$riders[$key]['wcp_points']=$this->get_rider_points($rider['races'],'cdm');
			$riders[$key]['winning_perc']=$this->get_rider_winning_perc($rider['races']);
			$riders[$key]['sos']=$this->get_sos($rider['races'],$season);
			$riders[$key]['uci_perc']=$riders[$key]['uci_points']/$uci_total;
			$riders[$key]['wcp_perc']=$riders[$key]['wcp_points']/$wcp_total;
			$riders[$key]['race_perc']=number_format($riders[$key]['total_races']/$this->get_total_races($season),3);
			$riders[$key]['weighted_winning_perc']=number_format(($riders[$key]['winning_perc']+$riders[$key]['race_perc'])/2,3);
			$riders[$key]['total']=$this->get_rider_final_number(array(
				'uci' => $riders[$key]['uci_points'],
				'wcp' => $riders[$key]['wcp_points'],
				'winning_perc' => $riders[$key]['winning_perc'],
				'sos' => $riders[$key]['sos'],
				'uci_total' => $uci_total,
				'wcp_total' => $wcp_total,
				'rider_total_races' => $riders[$key]['total_races'],
				'season_total_races' => $this->get_total_races($season),
			));

		endforeach;

		// sort - hardcoded for now //
		$arr=array();
		foreach ($riders as $rider) :
    	$arr[]=$rider['total'];
		endforeach;
		array_multisort($arr,SORT_DESC,SORT_NUMERIC,$riders);

		$riders=json_decode(json_encode($riders),FALSE); // make object

		return $riders;
	}

	/**
	 * get_rider_points function.
	 *
	 * @access protected
	 * @param bool $races (default: false)
	 * @param bool $type (default: false)
	 * @return void
	 */
	protected function get_rider_points($races=false,$type=false) {
		global $wpdb,$uci_curl;

		if (!$races)
			return false;

		$points=0;

		foreach ($races as $code => $race) :
			$class=$wpdb->get_var("SELECT class FROM $uci_curl->table WHERE code='$code'");

			if ($type=='cdm') :
				if (isset($class) && $class=='CDM') :
					$points=$points+$race->par;
				endif;
			else :
				if (isset($race->par)) :
					$points=$points+$race->par;
				endif;
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
			return false;

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
	 * @param bool $season (default: false)
	 * @return void
	 */
	protected function get_sos($races=array(),$season=false) {
		global $wpdb,$uci_curl;

		$sos=0;
		$fq_total=0;
		$total_races=$this->get_total_races($season);
		$rider_total_races=count($races);

		foreach ($races as $code => $race) :
			$race_class=$wpdb->get_var("SELECT class FROM $uci_curl->table WHERE code='$code'");

			switch ($race_class) :
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

		//$fq_total=$fq_total/$rider_total_races;
		//$races_perc=$rider_total_races/$total_races;
		$fq_total=$fq_total/100;
		$sos=$sos/100;

		//$sos=$fq_total+$races_perc;
		//$sos=($rider_total_races/$total_races)+$sos;
		//$sos=1/$fq_total;
		$sos=($fq_total+$sos)/2;

		return number_format($sos,3);
	}

	/**
	 * get_total_races function.
	 *
	 * @access protected
	 * @param bool $season (default: false)
	 * @return void
	 */
	protected function get_total_races($season=false) {
		global $wpdb,$uci_curl;

		$where=null;
		$races_count=0;

		if ($season)
			$where=" WHERE season='$season'";

		$races_count=$wpdb->get_var("SELECT COUNT(*) FROM ".$uci_curl->table.$where);

		return $races_count;
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
	 * get_total_points function.
	 *
	 * @access protected
	 * @param bool $type (default: false)
	 * @param bool $season (default: false)
	 * @return void
	 */
	protected function get_total_points($type=false,$season=false) {
		global $wpdb,$uci_curl,$RaceStats;

		$where=null;
		$points=0;

		$races=$RaceStats->get_races(array('season' => $season));

		foreach ($races as $race) :
			$race_class=$race->class;
			$results=$RaceStats->get_race_results_from_db($race->code);

			foreach ($results as $result) :
				if ($type=='cdm' && $race_class=='CDM') :
					$points=$points+$result->par;
				else :
					$points=$points+$result->par;
				endif;
			endforeach;
		endforeach;

		return $points;
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

}

$RiderStats=new RiderStats();
?>