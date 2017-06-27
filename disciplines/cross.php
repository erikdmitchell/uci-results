<?php

class UCIResultsCross extends UCIResultsDiscipline {
	
	public function __construct() {	
		add_filter('uci_results_insert_race_result_cyclocross', array($this, 'clean_results'), 10, 3);
	}
	
	public function clean_results() {
		/*
		if (!isset($result->par) || empty($result->par) || is_null($result->par)) :
			$par=0;
		else :
			$par=$result->par;
		endif;
	
		if (!isset($result->pcr) || empty($result->pcr) || is_null($result->pcr)) :
			$pcr=0;
		else :
			$pcr=$result->pcr;
		endif;
		*/
	
		return $meta_values;		
	}
	
}
?>