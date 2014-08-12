<?php
/**
 @since Version 1.0.1
**/
class ViewDB {

	function display_view_db_page() {
		global $wpdb;
		global $uci_curl;
		$html=null;
		//$limit=3;
		
		$html.='<h3>View DB</h3>';
		
		$races=$wpdb->get_results("SELECT * FROM ".$uci_curl->table);
		
		foreach ($races as $key => $race) :
			$html.=$this->display_race_table($race,false,false);
		endforeach;
		
		$html.='<div class="loading-modal"></div>';
		
		echo $html;
	}

	function display_race_table($race,$results=true,$fq=true) {
		$html=null;
		$alt=0;
		$data=unserialize(base64_decode($race->data));
		set_time_limit(0); // unlimited max execution time //
		$fq_class=null;
		$results_class=null;
		
		if ($results)
			$results_class='show';
		
		if ($fq)
			$fq_class='show';
	
		if (!isset($data->results)) :
			$html.=$race->id.'<br />';
			$html.=$race->code.'<br />';
			$html.='This has no results<br>';
		endif;
		
		$html.='<table class="race-table">';
			$html.='<tr class="header">';
				$html.='<td>Date</td>';
				$html.='<td>Event</td>';
				$html.='<td>Nat.</td>';
				$html.='<td>Class</td>';
				$html.='<td>Winner</td>';
				$html.='<td>Link</td>';
			$html.='</tr>';

			$html.='<tr>';
				$html.='<td>'.$data->date.'</td>';
				$html.='<td>'.$data->event.'</td>';
				$html.='<td>'.$data->nat.'</td>';
				$html.='<td>'.$data->class.'</td>';
				$html.='<td>'.$data->winner.'</td>';
				$html.='<td class="race-link"><a href="#" data-link="'.$data->link.'" data-id="race-'.$race->id.'">Results</a></td>';
				$html.='<td class="race-details"><a href="#" data-id="race-'.$race->id.'">Race Details</a></td>';
			$html.='</tr>';
			// race results //			
			$html.='<tr><td colspan="7">';
				$html.='<table id="race-'.$race->id.'" class="race-results-full-table '.$results_class.'">';
					$html.='<tr class="header">';
						$html.='<td>Place</td>';
						$html.='<td>Name</td>';
						$html.='<td>Nat.</td>';
						$html.='<td>PAR</td>';
						$html.='<td>PCR</td>';
						$html.='<td>Place</td>';
						$html.='<td>Result</td>';
					$html.='</tr>';
					
					if (isset($data->results)) :
						foreach ($data->results as $result) :
							if ($alt%2) :
								$class='alt';
							else :
								$class=null;
							endif;
							$html.='<tr class="'.$class.'">';
								$html.='<td>'.$result->place.'</td>';
								$html.='<td>'.$result->name.'</td>';
								$html.='<td>'.$result->nat.'</td>';
								$html.='<td>'.$result->age.'</td>';
								$html.='<td>'.$result->result.'</td>';
								$html.='<td>'.$result->par.'</td>';
								$html.='<td>'.$result->pcr.'</td>';
							$html.='</tr>';
							$alt++;
						endforeach;
					else :
						$html.=$race->id.' - This race had no results<br />';				
					endif;
				$html.='</table>';
			$html.='</td></tr>';
			// field quality //
			$html.='<tr><td colspan="7">';
				$html.='<table id="race-'.$race->id.'" class="race-fq '.$fq_class.'">';
					$html.='<tr class="header">';
						$html.='<td>WC Mult.</td>';
						$html.='<td>UCI Mult.</td>';
						$html.='<td>Field Quality</td>';
						$html.='<td>Total</td>';
						$html.='<td>Divider</td>';
						$html.='<td>Race Total</td>';
					$html.='</tr>';
					
					if (isset($data->field_quality)) :
//print_r($data->field_quality);					
						$html.='<tr class="'.$class.'">';
							$html.='<td>'.$data->field_quality->wcp_mult.'</td>';
							$html.='<td>'.$data->field_quality->uci_mult.'</td>';
							$html.='<td>'.$data->field_quality->field_quality.'</td>';
							$html.='<td>'.$data->field_quality->total.'</td>';
							$html.='<td>'.$data->field_quality->divider.'</td>';
							$html.='<td>'.$data->field_quality->race_total.'</td>';
						$html.='</tr>';
					else :
						$html.=$race->id.' - This race had no field quality<br />';				
					endif;
			$html.='</table>';
		$html.='</td></tr>';
			

		$html.='</table><!-- .race-table -->';
		
		$html.='<div id="race-data-'.$race->id.'"></div>';
		
		$html.='<hr />';
		
		return $html;
	}

}
?>