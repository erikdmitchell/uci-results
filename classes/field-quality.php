<?php
/**
 @since Version 1.0.1
**/

class Field_Quality {

	public $version='0.1.0';
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

	private $races_table;
	private $season_rankings_table;

	public $start_of_season='07 Sep 2013'; // NOT IN USE YET

	function __construct() {
		global $wpdb;

		$this->racers_table=$wpdb->prefix.'uci_races';
		$this->season_rankings_table=$wpdb->prefix.'uci_season_rankings';
	}

	public function get_race_math($race) {
		$race_fq=$this->process_race_fq($race);

		return $race_fq;
	}

	public function get_race_math_by_id($id,$format=true) {
		global $wpdb;

		$race_db=$wpdb->get_row("SELECT * FROM $this->races_table WHERE id=".$id);
		$race=$this->decode_race_data($race_db->data);
		$race=$this->process_race_fq($race);

		if ($format)
			$race=$this->format_race_fq_results($race);

		return $race;
	}

	/**
	 * @param object $race - race object from db
	**/
	function process_race_fq($race=false) {
		if (!$race)
			return false;

		global $wpdb;

		$races_db=$wpdb->get_results("SELECT data FROM $this->races_table");
		$race_date=$race->date;
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
		$races_before_data=$this->get_all_prior_races($race_date);

		$rider_uci_points=$this->get_previous_races_points($races_before_data['all']); // all uci points
		$rider_wcp_points=$this->get_previous_races_points($races_before_data['wcp']); // world cup points
		$this->rider_points=$this->merge_rider_points_arrays($races_before_data['all'],$rider_uci_points,$rider_wcp_points); // combine uci and wcp points arrays
		$this->wcp_field=$this->get_world_cup_points_in_field($race->results); // wc points in race

		// get our point totals //
		foreach ($this->rider_points as $rider) :
			$this->wcp_total=$this->wcp_total+$rider['wcp_points'];
			$this->uci_total=$this->uci_total+$rider['uci_points'];
		endforeach;

		$this->wcp_field=$this->get_points_in_field($this->rider_points,$racers_in_field,'wcp_points'); // get world cup points in field //
		$this->wcp_mult=$this->get_multiplier($this->wcp_field,$this->wcp_total); // get the wcp multiplyer //
		$this->uci_field=$this->get_points_in_field($this->rider_points,$racers_in_field,'uci_points'); // get uci points in field //
		$this->uci_mult=$this->get_multiplier($this->uci_field,$this->uci_total); // uci multiplyer //

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

		// if no uci total, change some values - first race of season - or second race with all points //
		if ($this->uci_total==0) :
			$this->get_modified_uci_data($racers_in_field);
		elseif (count($races_before_data['all'])==1 && $this->uci_total==$this->uci_field) :
			$this->get_modified_uci_data($racers_in_field);
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

/*
echo '<pre>';
echo 'Race: '.$race->event.' - '.$race->date.'<br>';
echo 'WCP Total - '.$this->wcp_total.'<br>';
echo 'UCI Total - '.$this->uci_total.'<br>';
echo 'WCP Total (Field) - '.$this->wcp_field.'<br>';
echo 'WCP Mult - '.$this->wcp_mult.'<br>';
echo 'UCI Total (Field) - '.$this->uci_field.'<br>';
echo 'UCI Mult - '.$this->uci_mult.'<br>';
echo 'finishers - '.$this->number_of_finishers.'<br>';
echo 'finisher multiplier - '.$this->nof_mult.'<br>';
echo 'Field Quality - ('.$this->wcp_mult.'+'.$this->uci_mult.'+'.$this->nof_mult.')/'.$this->divider.' = '.$this->field_quality.'<br>';
echo 'Total - '.$this->total.'<br>';
echo 'Race Total - '.$this->race_total.'<br>';
echo 'Final Object<br>';
print_r($final_object);
echo '</pre>';
*/

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
	function get_previous_races_points($arr) {
		$all_races_data=$this->reformat_race_data($arr);
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
	function reformat_race_data($arr) {
		$all_races_data=array();

		foreach ($arr as $race) :
			foreach ($race as $rider) :
				if (!empty($rider->par)) : // prevents those with no points from being in the arr //
					$data=array(
						'name'=>$rider->name,
						'points'=>$rider->par,
					);
					array_push($all_races_data,$data);
				endif;
			endforeach;
		endforeach;

		return $all_races_data;
	}

	/**
	 * create one array with uci and wcp points
	 */
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

	/**
	 *
	 */
	function get_multiplier($field,$total) {
		if ($total!=0) :
			$multiplyer=$field/$total; // multiplyer
		else :
			return 0; // there are no points
		endif;

		$multiplyer=round($multiplyer,3);
		$multiplyer=number_format($multiplyer,3);

		return $multiplyer;
	}

	/**
	 * get all races and all world cup races that occured before this one
	 */
	function get_all_prior_races($race_date) {
		global $wpdb;
		$arr=array();
		$races_before_data=array();
		$wcp_races_before_data=array();
		$races_db=$wpdb->get_results("SELECT data FROM ".$this->races_table);

		foreach ($races_db as $race_db) :
			$race_data=$this->decode_race_data($race_db->data);

			if (strtotime($race_data->date)<strtotime($race_date)) :
				array_push($races_before_data,$race_data->results);
				if ($race_data->class=="CDM") :
					array_push($wcp_races_before_data,$race_data->results);
				endif;
			endif;
		endforeach;

		$arr['all']=$races_before_data;
		$arr['wcp']=$wcp_races_before_data;

		return $arr;
	}

	/**
	 *
	 */
	function get_points_in_field($rider_points,$riders,$field) {
		$field_points=0;

		foreach ($riders as $rider) :
			foreach ($rider_points as $points) :
				if ($rider==$points['name']) :
					$field_points=$field_points+$points[$field];
				endif;
			endforeach;
		endforeach;

		return $field_points;
	}

	/**
	 *
	 */
	function get_modified_uci_data($riders) {
		global $wpdb;
		$uci_total=0;
		$uci_field=0;
		$rider_season_rankings=$wpdb->get_results("SELECT name,points FROM $this->season_rankings_table WHERE season='2013/2014'");

		foreach ($riders as $rider) :
			foreach ($rider_season_rankings as $r) :
				if ($rider==$r->name) :
					$uci_field=$uci_field+$r->points;
				endif;
			endforeach;
		endforeach;

		foreach ($rider_season_rankings as $r) :
			$uci_total=$uci_total+$r->points;
		endforeach;

		$this->uci_field=$uci_field;
		$this->uci_total=$uci_total;
		$this->uci_mult=$this->get_multiplier($uci_field,$uci_total); // uci multiplyer //
	}

}

$field_quality=new Field_Quality();
?>