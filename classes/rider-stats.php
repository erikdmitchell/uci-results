<?php

class UCIRiderStats {
	
	public $final_rankings;
	public $wins;
	
	public function __construct($rider_id=0, $args='') {
		if (!$rider_id)
			return false;
			
		$this->final_rankings=$this->final_rankings($rider_id);
		$this->wins=$this->wins($rider_id);
		//$stats->podiums=$this->get_rider_podiums($rider_id);
		//$stats->world_champs=$this->world_championships($rider_id);
		//$stats->world_cup_wins=$this->world_cup_wins($rider_id);
		//$stats->superprestige_wins=$this->superprestige_wins($rider_id);
		//$stats->gva_bpost_bank_wins=$this->gva_bpost_bank_wins($rider_id);
		//$stats->world_cup_titles=$this->world_cup_titles($rider_id);
		//$stats->superprestige_titles=$this->superprestige_titles($rider_id);
		//$stats->gva_bpost_bank_titles=$this->gva_bpost_bank_titles($rider_id);
	}
	
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
	
	public function wins($rider_id=0) {
		uci_results_get_rider_results($rider_id, '', '', 1);
	}
		
}	
?>