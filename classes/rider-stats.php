<?php
class RiderStats {

	function __construct() {
	
	}
	
	function get_season_rider_rankings($season=false) {
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
		$races_db=$wpdb->get_results("SELECT * FROM ".$uci_curl->table);		
		
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
		if (!$rider)
			return $false;
			
		$obj=new stdClass();
		$obj->name=$rider;
		$obj->uci_points=$this->get_rider_points($rider,'uci',$season);
		$obj->wcp_points=$this->get_rider_points($rider,'cdm',$season);
		$obj->sos=$this->get_sos($rider);
		$obj->winning_perc=$this->get_rider_winning_perc($rider,$season);
		$obj->total=$this->get_rider_final_number($rider,$season);
		
		return $obj;
	}

	/**
	 * strength of schedule
	 */
	function get_sos($rider=false,$season=false) {
		if (!$rider)
			return false;

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
		global $wpdb,$uci_curl;
		$rider_races=array();
		$races=array();
		$races_db=$wpdb->get_results("SELECT * FROM ".$uci_curl->table);
		
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
		$races_db=$wpdb->get_results("SELECT * FROM ".$uci_curl->table);
		
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
		$uci=$this->get_rider_points($rider,'uci',$season);
		$wcp=$this->get_rider_points($rider,'cdm',$season);
		$sos=$this->get_sos($rider);
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
		$races_db=$wpdb->get_results("SELECT * FROM ".$uci_curl->table);
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

	public static function arrayToObject( $array ) {
	  foreach( $array as $key => $value ){
	    if( is_array( $value ) ) $array[ $key ] = SELF::arrayToObject( $value );
	  }
	  return (object) $array;
	}

}
?>