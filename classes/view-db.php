<?php
/**
 @since Version 1.0.2
**/
class ViewDB {

	public $version='0.1.1';

	public function __construct() {
		add_action('admin_enqueue_scripts',array($this,'viewdb_scripts_styles'));
	}

	public function viewdb_scripts_styles() {

	}

	public function display_view_db_page() {
		global $wpdb,$uci_curl;
		//set_time_limit(0); // mex ececution time
		$html=null;
		$sort_type='date';
		$sort='ASC';
		$races=$wpdb->get_results("SELECT * FROM ".$uci_curl->table);

		$races=$this->sort_races($sort_type,$sort,$races);

		$html.='<h3>Races In Database</h3>';

		if (isset($_POST['submit']) && $_POST['submit']=='Add/Update FQ' && isset($_POST['races'])) :
			foreach ($_POST['races'] as $race_id) :
echo "$race_id - update fq<br>";
				//echo $this->update_fq($race_id);
			endforeach;
		endif;

		$html.='<form name="add-races-to-db" method="post">';
			$html.='<div class="race-table">';
				$html.='<div class="header row">';
					$html.='<div class="checkbox col-md-1">&nbsp;</div>';
					$html.='<div class="date col-md-2">Date</div>';
					$html.='<div class="event col-md-2">Event</div>';
					$html.='<div class="nat col-md-1">Nat.</div>';
					$html.='<div class="class col-md-1">Class</div>';
					$html.='<div class="winner col-md-2">Winner</div>';
					$html.='<div class="season col-md-1">Season</div>';
					$html.='<div class="race-details col-md-2">&nbsp;</div>';
				$html.='</div>';

				foreach ($races as $key => $race) :
					$html.=$this->display_race_data($race,false,false);
				endforeach;

			$html.='</div><!-- .race-table -->';

			$html.='<input type="checkbox" id="selectall" />Select All';

			$html.='<p class="submit">';
				$html.='<input type="submit" name="submit" id="submit" class="button button-primary" value="Add/Update FQ">';
			$html.='</p>';

		$html.='</form>';

		echo $html;
	}

	public function display_race_data($race) {
		$html=null;
		$alt=0;
		$data=unserialize(base64_decode($race->data));

		$html.='<div class="row">';
			$html.='<div class="col-md-1"><input class="race-checkbox" type="checkbox" name="races[]" value="'.$race->id.'" /></div>';
			$html.='<div class="date col-md-2">'.$data->date.'</div>';
			$html.='<div class="event col-md-2">'.$data->event.'</div>';
			$html.='<div class="nat col-md-1">'.$data->nat.'</div>';
			$html.='<div class="class col-md-1">'.$data->class.'</div>';
			$html.='<div class="winner col-md-2">'.$data->winner.'</div>';
			$html.='<div class="season col-md-1">'.$data->season.'</div>';

			$html.='<div class="race-details col-md-2">';
				$html.='[<a class="result" href="#" data-link="'.$data->link.'" data-id="race-'.$race->id.'">Results</a>]&nbsp;';
				$html.='[<a class="details" href="#" data-id="race-'.$race->id.'">Details</a>]';
			$html.='</div>';
		$html.='</div>';

		// race results //
		$html.='<div id="race-'.$race->id.'" class="results">';
			if (isset($data->results)) :
				$html.='<div class="row header">';
					$html.='<div class="col-md-1">Place</div>';
					$html.='<div class="col-md-2">Name</div>';
					$html.='<div class="col-md-2">Nat.</div>';
					$html.='<div class="col-md-2">PAR</div>';
					$html.='<div class="col-md-2">PCR</div>';
					$html.='<div class="col-md-1">Place</div>';
					$html.='<div class="col-md-2">Result</div>';
				$html.='</div>';

				foreach ($data->results as $result) :
					$html.='<div class="row">';
						$html.='<div class="col-md-1">'.$result->place.'</div>';
						$html.='<div class="col-md-2">'.$result->name.'</div>';
						$html.='<div class="col-md-2">'.$result->nat.'</div>';
						$html.='<div class="col-md-2">'.$result->age.'</div>';
						$html.='<div class="col-md-2">'.$result->result.'</div>';
						$html.='<div class="col-md-1">'.$result->par.'</div>';
						$html.='<div class="col-md-2">'.$result->pcr.'</div>';
					$html.='</div';
				endforeach;
			else :
				$html.='<div class="col-md-12">'.$race->id.' - This race had no results</div>';
			endif;
		$html.='</div>';

		// race details, including field quality //
		$html.='<div id="race-'.$race->id.'" class="race-fq">';
			if (isset($data->field_quality)) :
				$html.='<div class="row header">';
					$html.='<div class="col-md-2">WC Mult.</div>';
					$html.='<div class="col-md-2">UCI Mult.</div>';
					$html.='<div class="col-md-2">Field Quality</div>';
					$html.='<div class="col-md-2">Total</div>';
					$html.='<div class="col-md-2">Divider</div>';
					$html.='<div class="col-md-2">Race Total</div>';
				$html.='</div>';

				$html.='<div class="row">';
					$html.='<div class="col-md-2">'.$data->field_quality->wcp_mult.'</div>';
					$html.='<div class="col-md-2">'.$data->field_quality->uci_mult.'</div>';
					$html.='<div class="col-md-2">'.$data->field_quality->field_quality.'</div>';
					$html.='<div class="col-md-2">'.$data->field_quality->total.'</div>';
					$html.='<div class="col-md-2">'.$data->field_quality->divider.'</div>';
					$html.='<div class="col-md-2">'.$data->field_quality->race_total.'</div>';
				$html.='</div>';
			else :
				$html.='<div class="col-md-12">'.$race->id.' - This race had no field quality</div>';
			endif;
		$html.='</div>';

		return $html;
	}

/*
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
*/

	/**
	 * sorts our races db object
	 * right now options are dummy, only does date in ASC order
	 */
	public function sort_races($field,$method,$races) {
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