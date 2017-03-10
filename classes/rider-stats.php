<?php

class UCIRiderStats {
	
	public $final_rankings;
	public $wins=0;
	public $podiums=0;
	public $world_champs=0;
	
	public function __construct($rider_id=0, $args='') {
		if (!$rider_id)
			return false;
			
		$this->final_rankings=$this->final_rankings($rider_id);
		$this->wins=$this->wins($rider_id);
		$this->podiums=$this->podiums($rider_id);
		$this->world_champs=$this->world_championships($rider_id);
		//$stats->world_cup_wins=$this->world_cup_wins($rider_id);
		//$stats->superprestige_wins=$this->superprestige_wins($rider_id);
		//$stats->gva_bpost_bank_wins=$this->gva_bpost_bank_wins($rider_id);
		//$stats->world_cup_titles=$this->world_cup_titles($rider_id);
		//$stats->superprestige_titles=$this->superprestige_titles($rider_id);
		//$stats->gva_bpost_bank_titles=$this->gva_bpost_bank_titles($rider_id);
	}
	
	/**
	 * final_rankings function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function final_rankings($rider_id=0) {
		global $wpdb;
		global $uci_cross_seasons;
		// get_last_season_week

		if (!$rider_id)
			return false;

		$rankings=array();
		$current_season=uci_results_get_current_season();
		$seasons=$uci_cross_seasons->get_seasons(array(
			'exclude' => $current_season->term_id
		));
		
		// build season => week array //
		foreach ($seasons as $season) :
			$last_week=$uci_cross_seasons->get_last_season_week($season->slug);
			$rankings[$season->slug]=$wpdb->get_row("SELECT points, rank FROM $wpdb->uci_results_rider_rankings WHERE rider_id = $rider_id AND week = $last_week AND season = '$season->name'");
		endforeach;

		
		if (!count($rankings))
			return false;

		return $rankings;
	}	
	
	/**
	 * wins function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function wins($rider_id=0) {
		$results=uci_results_get_rider_results(array(
			'rider_id' => $rider_id, 
			'places' => 1
		));
		
		return count($results);
	}
	
	/**
	 * podiums function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function podiums($rider_id=0) {
		$results=uci_results_get_rider_results(array(
			'rider_id' => $rider_id, 
			'places' => '1, 2, 3'
		));
		
		return count($results);		
	}

	/**
	 * world_championships function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function world_championships($rider_id=0) {
		$results=uci_results_get_rider_results(array(
			'rider_id' => $rider_id, 
			'places' => 1,
			'race_classes' => 'cm',
		));
		
		return count($results);
	}		
}	
?>