<?php
/**
 @since Version 1.0.2
**/
class ViewDB {

	function __construct() {
		add_action('admin_enqueue_scripts',array($this,'viewdb_scripts_styles'));
	}

	function viewdb_scripts_styles() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-tablesorter-script',UCICURLBASE.'js/jquery.tablesorter.min.js',array('jquery'),'2.0.5b',true);

		wp_enqueue_style('viewdb-admin-style',UCICURLBASE.'/css/viewdb.css');
	}

	function display_view_db_page() {
		global $wpdb,$uci_curl;

		set_time_limit(0); // mex ececution time
		$html=null;
		$sort_type='date';
		$sort='ASC';
		$races=$wpdb->get_results("SELECT * FROM ".$uci_curl->table);

		$races=$this->sort_races($sort_type,$sort,$races);

		$html.='<h3>Races In Database</h3>';

		if (isset($_POST['submit']) && $_POST['submit']=='Add/Update FQ' && isset($_POST['races'])) :
			foreach ($_POST['races'] as $race_id) :
				echo $this->update_fq($race_id);
			endforeach;
		endif;

		$html.='<form name="add-races-to-db" method="post">';
			$html.='<table class="race-table">';
				$html.='<thead>';
					$html.='<tr class="header">';
						$html.='<td>&nbsp;</td>';
						$html.='<td class="date">Date</td>';
						$html.='<td class="event">Event</td>';
						$html.='<td class="nat">Nat.</td>';
						$html.='<td class="class">Class</td>';
						$html.='<td class="winner">Winner</td>';
						$html.='<td class="season">Season</td>';
						$html.='<td>&nbsp;</td>';
						$html.='<td>&nbsp;</td>';
					$html.='</tr>';
				$html.='</thead>';

				foreach ($races as $key => $race) :
					$html.=$this->display_race_table_data($race,false,false);
				endforeach;

			$html.='</table><!-- .race-table -->';

			$html.='<input type="checkbox" id="selectall" />Select All';

			$html.='<p class="submit">';
				$html.='<input type="submit" name="submit" id="submit" class="button button-primary" value="Add/Update FQ">';
			$html.='</p>';
		$html.='</form>';

		echo $html;
	}

	function display_race_table_data($race,$results=true,$fq=true) {
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

		if (isset($data->field_quality)) :
			$class=null;
		else :
			$class='error';
		endif;

		$html.='<tr class="'.$class.'">';
			$html.='<td><input class="race-checkbox" type="checkbox" name="races[]" value="'.$race->id.'" /></td>';
			$html.='<td class="date">'.$data->date.'</td>';
			$html.='<td class="event">'.$data->event.'</td>';
			$html.='<td class="nat">'.$data->nat.'</td>';
			$html.='<td class="class">'.$data->class.'</td>';
			$html.='<td class="winner">'.$data->winner.'</td>';
			if (isset($data->season)) :
				$html.='<td class="winner">'.$data->season.'</td>';
			else :
				$html.='<td>&nbsp;</td>';
			endif;
			$html.='<td class="race-link"><a href="#" data-link="'.$data->link.'" data-id="race-'.$race->id.'">Results</a></td>';
			$html.='<td class="race-details"><a href="#" data-id="race-'.$race->id.'">Details</a></td>';
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
					$html.='<tr class="'.$class.'">';
						$html.='<td>'.$data->field_quality->wcp_mult.'</td>';
						$html.='<td>'.$data->field_quality->uci_mult.'</td>';
						$html.='<td>'.$data->field_quality->field_quality.'</td>';
						$html.='<td>'.$data->field_quality->total.'</td>';
						$html.='<td>'.$data->field_quality->divider.'</td>';
						$html.='<td>'.$data->field_quality->race_total.'</td>';
					$html.='</tr>';
				else :
					$html.='<tr><td colspan="6">'.$race->id.' - This race had no field quality</td></tr>';
				endif;
			$html.='</table>';
		$html.='</td></tr>';

		return $html;
	}

	function update_fq($race_id) {
		global $wpdb;
		global $uci_curl;
		$message=null;
		$fq=new Field_Quality();
		$race=$wpdb->get_row("SELECT * FROM $uci_curl->table WHERE id=$race_id");

		$race->data=unserialize(base64_decode($race->data));

		$race->data->field_quality=$fq->get_race_math($race->data);

		// build data array //
		$data=array(
			'data' => base64_encode(serialize($race->data)),
		);

		$where=array(
			'id' => $race_id
		);

		$wpdb->update($uci_curl->table,$data,$where);

		$message='<div class="updated">Updated '.$race->code.' fq.</div>';

		return $message;
	}

	/**
	 * sorts our races db object
	 * right now options are dummy, only does date in ASC order
	 */
	function sort_races($field,$method,$races) {
		foreach ($races as $race) :
			$race->data=unserialize(base64_decode($race->data));
		endforeach;

		$dates = array();
		foreach ($races as $race) :
    	$dates[] = strtotime($race->data->date);
		endforeach;

		array_multisort($dates,SORT_ASC,$races);

		foreach ($races as $race) :
			$race->data=base64_encode(serialize($race->data));
		endforeach;

		return $races;
	}

}
?>