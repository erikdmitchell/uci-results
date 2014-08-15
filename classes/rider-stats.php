<?php
class RiderStats {

	function __construct() {
	
	}
	
	function get_season_race_rankings($season) {
		global $wpdb;
		global $uci_curl;
		
		$html=null;
		$sort_type='race_total';
		$sort='desc';
		//$races=$wpdb->get_results("SELECT * FROM ".$uci_curl->table);
		//$races=$this->sort_races($sort_type,$sort,$races);
		
/*
		$html.='<table id="season-race-rankings" class="season-race-rankings">';
			$html.='<tr class="header">';
				$html.='<td class="date">Date</td>';
				$html.='<td class="event">Event</td>';
				$html.='<td class="nat">Nat.</td>';
				$html.='<td class="class">Class</td>';
				$html.='<td class="winner">Winner</td>';
				$html.='<td>Field Quality</td>';
			$html.='</tr>';

			foreach ($races as $race) :
				$data=unserialize(base64_decode($race->data));
				$class=null;

				$html.='<tr class="'.$class.'">';
					$html.='<td class="date">'.$data->date.'</td>';
					$html.='<td class="event">'.$data->event.'</td>';
					$html.='<td class="nat">'.$data->nat.'</td>';
					$html.='<td class="class">'.$data->class.'</td>';
					$html.='<td class="winner">'.$data->winner.'</td>';
					$html.='<td class="fq">'.$data->field_quality->race_total.'</td>';
				$html.='</tr>';
			endforeach;
			
		$html.='</table>';
*/
		
		return $html;
	}

	/**
	 * strength of schedule
	 */
	function get_sos($rider=false,$season=false) {
		
		
		if (!$rider)
			return false;

		$sos=0;
		$html=null;
		$rider_races=$this->get_rider_races($rider,$season);

echo '<pre>';
print_r($rider_races);
echo '</pre>';

/*
		foreach ($rider["races"] as $races) {
			$pts=0;
			if ($races["type"]=="CDM") {
				$pts=4;
			} elseif ($races["type"]=="CN") {
				$pts=3;
			} elseif ($races["type"]=="C1") {
				$pts=2;
			} elseif ($races["type"]=="C2") {
				$pts=1;
			}
			$sos=$sos+$pts;
		}
		$sos_final=($rider["races_total"]/$rider["total_races"])+$sos;
		$rider_uci_points[$k]["sos"]["sos_num"]=$sos;
	} // end foreach //
	
	usort($rider_uci_points,'sos_sort');
	//echo "<pre>";
	//print_r($rider_uci_points);
	//echo "</pre>";
	
	$sos_rank=0;
	$prev_num=0;
	$max=0;
	foreach ($rider_uci_points as $k => $rider) {
		//echo $sos_rank." - ";
		if ($sos_rank==0) {
			$sos_rank=1; // first run through //
			$max=$rider["sos"]["sos_num"];
		} else {
			if ($rider["sos"]["sos_num"]==$prev_num) {
				// do nothing //
			} else {
				$sos_rank++;
			}
		}
		$prev_num=$rider["sos"]["sos_num"];
		$rider_uci_points[$k]["sos"]["sos_rank"]=$sos_rank;
		//$sos_perc=(101-$sos_rank)*0.01;
		$sos_perc=number_format(($rider["sos"]["sos_num"]/$max),3);
		if ($sos_perc<0) {
			$sos_perc=0;
		}
		$sos_perc=number_format($sos_perc,3);
		$rider_uci_points[$k]["sos"]["sos_perc"]=$sos_perc;
*/


		
		$html.=$rider.' - '.$sos.'<br>';
		
		return $html;
	}

	function get_rider_races($rider,$season) {
		global $wpdb,$uci_curl;
		$rider_races=array();
		$races=array();
		$races_db=$wpdb->get_results("SELECT * FROM ".$uci_curl->table);
		//$races_db=$races_db[0];
		
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
		
		return $rider_races;
	}

	/**
	 * sorts our races db object
	 * only does fq, need our variable to be more robust
	 */
	function sort_riders($field=false,$method=false,$riders=false) {	
		if (!($field) || !($method) || !($riders))
			return array();

/*
		$method=constant('SORT_'.strtoupper($method));
			
		foreach ($riders as $race) :
			$race->data=unserialize(base64_decode($race->data));
		endforeach;
	
		$dates = array(); 
		foreach ($races as $race) :
    	$arr[] = $race->data->field_quality->$field;
		endforeach; 

		array_multisort($arr,$method,$races); 

		foreach ($races as $race) :
			$race->data=base64_encode(serialize($race->data));
		endforeach;
		
		return $races;
*/
	}

}
?>