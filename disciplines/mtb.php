<?php

class UCI_Results_MTB extends UCI_Results_Discipline {
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_filter('race_results_metabox_rider_output_mtb', array($this, 'race_results_metabox'), 10, 2);		
	}
	
	/**
	 * race_results_metabox function.
	 * 
	 * @access public
	 * @param mixed $output
	 * @param mixed $post_id
	 * @return void
	 */
	public function race_results_metabox($output, $post_id) {
		$output=array('result_place', 'name', 'nat');
		
		return $output;
	}
	
}
?>