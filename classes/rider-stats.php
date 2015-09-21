<?php
class RiderStats {

	function __construct() {
		
	}
	
	function get_season_rider_rankings($season=false) {
		if (!$season)
			return '<div class="error">Error: Get rider rankings needs a season.</div>';

		set_time_limit(0);
	
		global $wpdb;
		global $uci_curl;
		
		$html=null;
		$sort_type='total';
		$sort='desc';
		$riders=$this->get_list_of_riders($season);
		$counter=0;
		$riders_x=array();
		
		foreach ($riders as $key => $rider) :
			$riders_x[]=$this->get_rider_stats($rider,$season);
			$counter++;
		endforeach;

		$riders=$riders_x;
		$riders=$this->sort_riders($sort_type,$sort,$riders);
		
		$html.='<h3>'.$season.' Rider Rankings</h3>';
		
		$html.='<table id="season-rider-rankings" class="season-rider-rankings">';
			$html.='<tr class="header">';
				$html.='<td class="rank">Rank</td>';
				$html.='<td class="rider">Rider</td>';
				$html.='<td class="uci">UCI Points</td>';
				$html.='<td class="wcp">WCP Points</td>';
				$html.='<td class="winning">Winning Perc.</td>';
				$html.='<td class="sos">SOS</td>';
				$html.='<td class="total">Total</td>';
			$html.='</tr>';

			foreach ($riders as $rider) :
				$class=null;
				if (is_object($rider)) :
					$html.='<tr class="'.$class.'">';
						$html.='<td class="rank"></td>';
						$html.='<td class="rider">'.$rider->name.'</td>';
						$html.='<td class="uci">'.$rider->uci_points.'</td>';
						$html.='<td class="wcp">'.$rider->wcp_points.'</td>';
						$html.='<td class="winning">'.$rider->winning_perc.'</td>';
						$html.='<td class="sos">'.$rider->sos.'</td>';
						$html.='<td class="total">'.$rider->total.'</td>';
					$html.='</tr>';
				endif;
			endforeach;
			
		$html.='</table>';
		
		return $html;
	}

	/**
	 *
	 */
	function get_list_of_riders($season=false) {
		global $wpdb,$uci_curl;
		$riders=array();
		$races_db=$wpdb->get_results("SELECT * FROM ".$uci_curl->table." WHERE season='$season'");		
		
		foreach ($races_db as $race) :
			$data=unserialize(base64_decode($race->data));
			foreach ($data->results as $result) :
				array_push($riders,$result->name);
			endforeach;
		endforeach;
		
		$riders=array_unique($riders);

		return $riders;
	}
	
	/**
	 *
	 */
	function get_rider_stats($rider=false,$season=false) {
		if (!$rider || !$season)
			return '<div class="error">Error: Get rider stats needs a rider and season.</div>';
			
		$obj=new stdClass();
		$obj->name=$rider;
		$obj->uci_points=$this->get_rider_points($rider,'uci',$season);
		$obj->wcp_points=$this->get_rider_points($rider,'cdm',$season);
		$obj->sos=$this->get_sos($rider,$season);
		$obj->winning_perc=$this->get_rider_winning_perc($rider,$season);
		$obj->total=$this->get_rider_final_number($rider,$season);
		$obj->season=$season;
		
		return $obj;
	}

	/**
	 * strength of schedule
	 */
	function get_sos($rider=false,$season=false) {
		if (!$rider || !$season)
			return '<div class="error">Error: Get sos needs a rider and season.</div>';

		$sos=0;
		$fq_total=0;
		$rider_races=$this->get_rider_races($rider,$season);
		$total_races=$this->get_total_races($season);
		$rider_total_races=$this->get_rider_total_races($rider_races);
		
		foreach ($rider_races as $race) :
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

	function get_rider_races($rider,$season) {
		if (!$rider || !$season)
			return '<div class="error">Error: Get rider races needs a rider and season.</div>';
	
		global $wpdb,$uci_curl;
		$rider_races=array();
		$races=array();
		$races_db=$wpdb->get_results("SELECT * FROM ".$uci_curl->table." WHERE season='$season'");
		
		foreach ($races_db as $race) :
			$races[]=unserialize(base64_decode($race->data));
		endforeach;

		foreach ($races as $race) :
			foreach ($race->results as $result) :
				if (strtolower($rider)==strtolower($result->name)) :
					$rider_races[]=array(
						'date' => $race->date,
						'event' => $race->event,
						'nat' => $race->nat,
						'class' => $race->class,
						'fq' => $race->field_quality->total,
						'place' => $result->place,
						'points' => $result->par
					);
				endif;
			endforeach;
		endforeach;
		
		return RiderStats::arrayToObject($rider_races);
	}

	/**
	 *
	 */
	function get_total_races($season) {
		global $wpdb,$uci_curl;
		
		if (!$season)
			return '<div class="error">Error: Get total races needs a season.</div>';
			
		$races_db=$wpdb->get_results("SELECT * FROM ".$uci_curl->table." WHERE season='$season'");
		
		return count($races_db);
	}

	/**
	 *
	 */
	function get_rider_total_races($races) {
		$counter=0;
		
		foreach ($races as $race) :
			$counter++;
		endforeach;
		
		return $counter;
	}

	/**
	 *
	 */
	function get_rider_winning_perc($rider,$season) {
		$winning_perc=0;
		$wins=0;
		$races=$this->get_rider_races($rider,$season);
		$rider_total_races=$this->get_rider_total_races($races);
		
		foreach ($races as $race) :
			if ($race->place==1)
				$wins++;
		endforeach;
		
		$winning_perc=number_format(($wins/$rider_total_races),3);
		
		return $winning_perc;
	}

	function get_rider_points($rider,$type,$season) {
		$points=0;
		$rider_races=$this->get_rider_races($rider,$season);
		
		foreach ($rider_races as $race) :
			if ($type=='cdm') :
				if ($race->class=='CDM') :
					$points=$points+$race->points;
				endif;
			else :
				$points=$points+$race->points;			
			endif;
		endforeach;
		
		return $points;
	}

	/**
	 *
	 */
	function get_rider_final_number($rider=false,$season=false) {
		if (!$rider || !$season)
			return '<div class="error">Error: Get final number needs a rider and season.</div>';
			
		$uci=$this->get_rider_points($rider,'uci',$season);
		$wcp=$this->get_rider_points($rider,'cdm',$season);
		$sos=$this->get_sos($rider,$season);
		$winning=$this->get_rider_winning_perc($rider,$season);
		$uci_total=$this->get_total_points('uci',$season);
		$wcp_total=$this->get_total_points('cdm',$season);
	
		$uci=number_format($uci/$uci_total,3);
		$wcp=number_format($wcp/$wcp_total,3);
		
		$total=($uci+$wcp+$sos+$winning)/4;
		
		return number_format($total,3);
	}

	/**
	 *
	 */
	function get_total_points($type,$season) {
		global $wpdb,$uci_curl;
		$races_db=$wpdb->get_results("SELECT * FROM ".$uci_curl->table." WHERE season='$season'");
		$points=0;
		
		foreach ($races_db as $race) :
			$data=unserialize(base64_decode($race->data));
			foreach ($data->results as $result) :
				if ($type=='cdm') :
					if ($data->class=='CDM') :
						$points=$points+$result->par;				
					endif;
				else :
					$points=$points+$result->par;				
				endif;
			endforeach;
		endforeach;		
		
		return $points;		
	}

	/**
	 * gets the final uci rankings per season from the db
	 */
	public static function get_uci_season_rankings($year=false,$display=true,$sort_field='rank',$sort_type='ASC') {
		if (!$year)
			return false;
			
		global $wpdb;
		$html=null;
		$rankings=$wpdb->get_results("SELECT * FROM uci_season_rankings WHERE season='$year'");
		
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

	/**
	 * gets the final uci rankings per season from the db
	 */
	public static function get_uci_season_ranking_seasons($display='list') {
		global $wpdb;
		$html=null;
		$seasons=$wpdb->get_results("SELECT season FROM uci_season_rankings GROUP BY season");
		
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

	/**
	 * sorts our riders
	 */
	function sort_riders($field=false,$method=false,$riders=false) {	
		if (!($field) || !($method) || !($riders))
			return array();

		$method=constant('SORT_'.strtoupper($method));
			
		$arr=array(); 
		foreach ($riders as $rider) :
    	$arr[]=$rider->$field;
		endforeach; 

		array_multisort($arr,$method,$riders); 

		return $riders;
	}

	/**
	 * converts an array to an object (including multidiemsional)
	 */
	public static function arrayToObject( $array ) {
	  foreach( $array as $key => $value ){
	    if( is_array( $value ) ) $array[ $key ] = SELF::arrayToObject( $value );
	  }
	  return (object) $array;
	}

	/**
	 * used with get_uci_season_rankings
	 */
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

}
?>