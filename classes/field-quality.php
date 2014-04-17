<?php
/**
 @since Version 1.0.1
**/

class Field_Quality {

	public $wcp_total=0;
	public $wcp_field;
	public $wcp_mult; 
	public $uci_total=0;
	public $uci_field=0;
	public $uci_mult; 
	public $number_of_finishers; 
	public $race_type; 
	public $type_num; 
	public $nof_mult; 
	public $field_quality; 
	public $total; 
	public $race_total; 
	public $divider=3;
	public $rider_points=array();
	
	public $start_of_season='07 Sep 2013'; // NOT IN USE YET
	
	function __construct() {
		//add_action('admin_menu',array($this,'admin_page'));
	}
/*	
	function admin_page() {
		add_options_page('Field Quality','Field Quality','administrator','field-quality',array($this,'display_admin_page'));
	}
	
	function display_admin_page() {
		global $wpdb;
		
		$race_db=$wpdb->get_row("SELECT * FROM uci_races WHERE id=790");
		$race=$this->decode_race_data($race_db->data);
		
		echo $this->display_filed_quality($race);
	}
*/
	/*
	 * race object
	*/	
	public function get_race_math($race) {
		return $this->process_race_fq($race);
	}

	public function get_race_math_by_id($id,$format=true) {
		global $wpdb;
		$race_db=$wpdb->get_row("SELECT * FROM uci_races WHERE id=".$id);
		$race=$this->decode_race_data($race_db->data);
		$race=$this->process_race_fq($race);
		
		if ($format)
			$race=$this->format_race_fq_results($race);
		
		return $race;
	}

	function format_race_fq_results($race) {
		$html=null;
		
		$html.='<div class="fq-results">';
			$html.='<div class="formula">(Field Quality + World Cup Mult. + UCI Points Mult.) / Divider = Race Total</div>';
			$html.='<table class="math">';
				$html.='<tr class="header">';
					$html.='<th>Field Quality</th>';
					$html.='<th>WC Multi</th>';
					$html.='<th>UCI Multi</th>';
					$html.='<th>Total</th>';
					$html.='<th>Divider</th>';
					$html.='<th>Race Total</th>';
				$html.='</tr>';
				$html.='<tr>';
					$html.='<td>'.$this->field_quality.'</td>';
					$html.='<td>'.$this->wcp_mult.'</td>';
					$html.='<td>'.$this->uci_mult.'</td>';
					$html.='<td>'.$this->total.'</td>';
					$html.='<td>'.$this->divider.'</td>';
					$html.='<td class="race-total">'.$this->race_total.'</td>';
				$html.='</tr>';
			$html.='</table>';
		$html.='</div>';
		
		return $html;
	}

/*
	function display_filed_quality($race=false) {
		$race=$this->process_race_fq($race);
		$html=null;
		
		$html.='<div class="field-quality">';
			$html.='<p>';
				$html.='Total World Cup Points: '.$this->wcp_total.'<br/>';
				$html.='Total World Cup Points in Field: '.$this->wcp_field.'<br/>';
				$html.='World Cup Points Multiplier: <b>'.$this->wcp_mult.'</b><br/>';
			$html.='</p>';
			$html.='<p>';
				$html.='Total UCI Points: '.$this->uci_total.'<br/>';
				$html.='Total UCI Points in Field: '.$this->uci_field.'<br/>';
				$html.='UCI Points Multiplier: <b>'.$this->uci_mult.'</b><br/>';
			$html.='</p>';
			$html.='<p>';
				$html.='Number of finishers: '.$this->number_of_finishers.'<br/>';
				$html.='Type: '.$this->race_type.'<br/>';
				$html.='Type Num: '.$this->type_num.'<br/>';
				$html.='No Finishers Multiplier: <b>'.$this->nof_mult.'</b><br/>';
			$html.='</p>';
			$html.='Field Quality: <b>'.$this->field_quality.'</b>';
			
			$html.='<div class="notes">';
				$html.='*The first race of the European/US season gets a modified race quality due to lack of UCI points<br />';
				$html.='**If no World Cup races have occurred, we need override<br />';
				$html.='Divider: '.$this->divider.' (3 as base)';
			$html.='</div>';

			$html.='<table class="math">';
				$html.='<tr class="header">';
					$html.='<th>Field Quality</th>';
					$html.='<th>WC Multi</th>';
					$html.='<th>UCI Multi</th>';
					$html.='<th>Total</th>';
				$html.='</tr>';
				$html.='<tr>';
					$html.='<td>'.$this->field_quality.'</td>';
					$html.='<td>'.$this->wcp_mult.'</td>';
					$html.='<td>'.$this->uci_mult.'</td>';
					$html.='<td>'.$this->total.'</td>';
				$html.='</tr>';
			$html.='</table>';
			$html.='Race Total: '.$this->race_total.'';
		$html.='</div><!-- .field-quality -->';
		
		return $html;
	}
*/
	
	/**
	 * @param object $race - race object from db
	**/
	function process_race_fq($race=false) {
		if (!$race)
			return false;
		
		global $wpdb;
		$races_table='uci_races';
		$races_db=$wpdb->get_results("SELECT data FROM $races_table");
		$race_date=$race->date;
		$races_before_data=array();
		$wcp_races_before_data=array();
		$all_races_data=array();
		$rider_uci_points=array();
		$rider_wcp_points=array();
		$rider_points=array();
		$this->number_of_finishers=count((array)$race->results);
		$racers_in_field=$this->get_racers_in_field($race->results);
		$wcp_mod=0;
		$type_num=0; // race type number
		$uci_mod=0;
		$nof_race_mult=0; // finisher multiplier
		$this->race_type=$race->class;
		$final_object=new stdClass();
		
		// get all races and all world cup races that occured before this one //
		foreach ($races_db as $race) :
			$race=$this->decode_race_data($race->data);
		
			if (strtotime($race->date)<strtotime($race_date)) :
				array_push($races_before_data,$race->results);
				if ($race->class=="CDM") :
					array_push($wcp_races_before_data,$race->results);
				endif;
			endif;
		endforeach;
		
		$rider_uci_points=$this->get_previous_races_points($races_before_data); // all uci points
		$rider_wcp_points=$this->get_previous_races_points($wcp_races_before_data); // world cup points
		$this->rider_points=$this->merge_rider_points_arrays($races_before_data,$rider_uci_points,$rider_wcp_points); // combine uci and wcp points arrays
		$this->wcp_field=$this->get_world_cup_points_in_field($race->results); // wc points in race

		// get our point totals //
		foreach ($this->rider_points as $rider) :
			$this->wcp_total=$this->wcp_total+$rider['wcp_points'];
			$this->uci_total=$this->uci_total+$rider['uci_points'];
		endforeach;
		
		// get world cup points in field //
		foreach ($racers_in_field as $rider) :
			foreach ($this->rider_points as $points) :
				if ($rider==$points['name']) :
					$this->wcp_field=$this->wcp_field+$points['wcp_points'];
				endif;
			endforeach;
		endforeach;
		
		$this->wcp_mult=$this->get_world_cup_multiplier(); // get the wcp multiplyer //
	
		// get uci points in field //
		foreach ($racers_in_field as $rider) :
			foreach ($this->rider_points as $points) :
				if ($rider==$points['name']) :
					$this->uci_field=$this->uci_field+$points["uci_points"];
				endif;
			endforeach;
		endforeach;
	
		$this->uci_mult=$this->get_uci_multiplier(); // get uci multiplier //
		
		// get race type conversion //
		switch ($race->class) :
			case 'C1':
				$this->type_num=2;
				break;
			case 'C2':
				$this->type_num=3;
				break;
			case 'CDM':
				$this->type_num=1;
				break;
			case 'CN':
				$this->type_num=2;
				break;
		endswitch;
	
		// build finisher multiplier //
		/*
		$nof_mult=$number_of_finishers/$type_num; //no of finisher multiplyer
		$nof_mult=$nof_mult/$number_of_finishers; //divide multiplyer by field to get percentage
		$nof_mult=round($nof_mult,3);
		*/
		// finisher multiplier new -- TODO : Calculate previous years averages automatically //
		switch ($race->class) :
			case 'C1':
				$nof_race_mult=42;
				break;
			case 'C2':
				$nof_race_mult=39;
				break;
			case 'CDM':
				$nof_race_mult=56;
				break;
			case 'CN':
				$nof_race_mult=29;
				break;
		endswitch;

		$this->nof_mult=$this->number_of_finishers/$nof_race_mult;
		if ($this->nof_mult>=1)
			$this->nof_mult=1;

		$this->nof_mult=round($this->nof_mult,3);
		$this->nof_mult=number_format($this->nof_mult,3);
	
		// we need a mod for no wc and/or uci races - we remove invalid values and change the divider //
		if (!$this->wcp_mult) :	
			$this->wcp_mult=0;
			$this->divider--;
		endif;
		
		if (!$this->uci_mult) :
			$this->uci_mult=0;
			$this->divider--;
		endif;
	
		// field quality = (wc mult + uci mult + nof mult) / 3 //
		$this->field_quality=($this->wcp_mult+$this->uci_mult+$this->nof_mult)/$this->divider;
		$this->field_quality=round($this->field_quality,3);
		$this->field_quality=number_format($this->field_quality,3);
		
		// final math //
		$this->total=$this->field_quality+$this->wcp_mult+$this->uci_mult;
		$this->race_total=round(($this->total/$this->divider),3);
		$this->race_total=number_format($this->race_total,3);	
		
		// build our return object //
		$final_object->field_quality=$this->field_quality;
		$final_object->total=$this->total;
		$final_object->race_total=$this->race_total;
		$final_object->wcp_mult=$this->wcp_mult;
		$final_object->uci_mult=$this->uci_mult;
		$final_object->divider=$this->divider;
		
		return $final_object;		
	}	
	
	function decode_race_data($data) {
		return $race=unserialize(base64_decode($data));
	}
	
	/**
	 * get uci points for riders in all races before this race
	 * takes in array
	 * returns array w/ name and points
	**/
	function get_previous_races_points($races_before_data) {
		$all_races_data=$this->reformat_race_data($races_before_data);
		$rider_names=$this->get_rider_names($all_races_data);

		// using list of names, get points //
		$rider_uci_points=array();
		foreach ($rider_names as $rider) :
			$points=0;
			foreach ($all_races_data as $data) :
				$c_rider=$data['name'];
				if ($rider==$c_rider) {
					$points=$data['points']+$points;
				}
			endforeach;
			$data=array(
				'name'=>$rider,
				'points'=>$points
			);
			array_push($rider_uci_points,$data);
		endforeach;
		
		return $rider_uci_points;
	}
	
	// get list of names //
	function get_rider_names($data_arr) {
		$rider_names=array();
		foreach ($data_arr as $rider) :
			array_push($rider_names,$rider['name']);
		endforeach;
		$rider_names=array_unique($rider_names);
		$rider_names=array_values($rider_names);
		
		return $rider_names;
	}
	
	// reformats our race data for use in getting rider names and bulind a better array //
	function reformat_race_data($races_before_data) {
		$all_races_data=array();
		
		foreach ($races_before_data as $race) :
			foreach ($race as $rider) :
				$data=array(
					'name'=>$rider->name,
					'points'=>$rider->par,
				);
				array_push($all_races_data,$data);
			endforeach;
		endforeach;
	
		return $all_races_data;	
	}
	
	// create one array with uci and wcp points //
	function merge_rider_points_arrays($races_before_data,$rider_uci_points,$rider_wcp_points) {
		$all_races_data=$this->reformat_race_data($races_before_data);
		$rider_names=$this->get_rider_names($all_races_data);
		
		$rider_points=array();
		for ($i=0;$i<count($rider_names);$i++) {
			$uci_points=0;
			$wcp_points=0;
			$rider=$rider_names[$i];
			// uci points //
			for ($x=0;$x<count($rider_uci_points);$x++) {
				$c_rider=$rider_uci_points[$x]["name"];
				if ($rider==$c_rider) {
					$uci_points=$rider_uci_points[$x]["points"]+$uci_points;
				}
			} // end for //
			// wcp points //
			for ($x=0;$x<count($rider_wcp_points);$x++) {
				$c_rider=$rider_wcp_points[$x]["name"];
				if ($rider==$c_rider) {
					$wcp_points=$rider_wcp_points[$x]["points"]+$wcp_points;
				}
			} // end for //
			$data=array(
				'name'=>$rider_names[$i],
				'uci_points'=>$uci_points,
				'wcp_points'=>$wcp_points
			);
			array_push($rider_points,$data);
		} // end for //
		
		return $rider_points;
	}

	function get_racers_in_field($field) {
		$racers=array();
		
		foreach ($field as $rider) :
			array_push($racers,$rider->name);
		endforeach;
		
		return $racers;
	}
	
	function get_world_cup_points_in_field($field) {
		$wcp_field=0;
		
		foreach ($field as $rider) :
			foreach ($this->rider_points as $points) :
				if ($rider->name==$points['name']) {
					$wcp_field=$wcp_field+$points['wcp_points'];
				}
			endforeach;
		endforeach;
		
		return $wcp_field;
	}
	
	// get the uci multiplier //
	
	function get_uci_multiplier() {
		$uci_mod=0;
		
		if ($this->uci_total!=0) :
			$uci_mult=$this->uci_field/$this->uci_total; //uci multiplyer
		else :
			return false; // there are no points
		endif;
		
		$uci_mult=round($uci_mult,3);
		$uci_mult=number_format($uci_mult,3);
		
		return $uci_mult;
	}
	
	function get_world_cup_multiplier() {
		$wcp_mult=0;
		
		if ($this->wcp_total!=0) :
			$wcp_mult=$this->wcp_field/$this->wcp_total;
		else :
			return false;
		endif;

		$wcp_mult=round($wcp_mult,3);
		$wcp_mult=number_format($wcp_mult,3);
		
		return $wcp_mult;
	}

}

$field_quality=new Field_Quality();
?>