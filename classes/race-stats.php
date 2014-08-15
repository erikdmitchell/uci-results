<?php
class RaceStats {

	function __construct() {
	
	}
	
	function get_season_race_rankings($season) {
		global $wpdb;
		global $uci_curl;
		
		$html=null;
		$sort_type='race_total';
		$sort='desc';
		$races=$wpdb->get_results("SELECT * FROM ".$uci_curl->table);
		$races=$this->sort_races($sort_type,$sort,$races);
		
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
		
		return $html;
	}

	/**
	 * sorts our races db object
	 * only does fq, need our variable to be more robust
	 */
	function sort_races($field=false,$method=false,$races=false) {	
		if (!($field) || !($method) || !($races))
			return array();

		$method=constant('SORT_'.strtoupper($method));
			
		foreach ($races as $race) :
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
	}

}
?>