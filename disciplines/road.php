<?php

class UCIResultsRoad extends UCIResultsDiscipline {
	
	public function __construct() {
		add_filter('race_results_metabox_rider_output_road', array($this, 'race_results_metabox')), 10, 2);		
		add_filter('uci_results_insert_race_result_road', array($this, 'clean_results'), 10, 3);
	}
	
	public function clean_results() {
		//$meta_value['place']=$meta_value['rank'];
		
		//unset($meta_value['rank']);
	
		return $meta_values;		
	}
	
	public function race_results_metabox($output, $post_id) {
		$output=array('place', 'name', 'nat', 'age', 'result');
		
		return $output;
	}
	
}
?>