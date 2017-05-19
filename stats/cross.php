<?php
global $uci_results_cross_stats;

class UCICrossStats extends UCIRiderStats {

	public function __construct() {
		parent::__construct(array(
			'id' => 'cyclocross',
			'name' => 'Cyclocross Stats',
			'discipline' => 'cyclocross',
		));
	}
	
	public function get_stats($rider_id=0) {
		$stats=new stdClass();
		
		if (!$rider_id)
			return $stats;
			
		return $stats;
	}

/*
	public $final_rankings;
	public $wins=0;
	public $podiums=0;
	public $world_champs=0;
	public $world_cup_wins=0;
	public $superprestige_wins=0;
	public $gva_bpost_bank_wins=0;
	public $world_cup_titles=0;
	public $superprestige_titles=0;
	public $gva_bpost_bank_titles=0;
*/
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @param string $args (default: '')
	 * @return void
	 */
/*
	public function __construct($rider_id=0, $args='') {		
		if (!$rider_id)
			return false;
			
		$this->final_rankings=$this->final_rankings($rider_id);
		$this->wins=$this->wins($rider_id);
		$this->podiums=$this->podiums($rider_id);
		$this->world_champs=$this->world_championships($rider_id);
		$this->world_cup_wins=$this->world_cup_wins($rider_id);
		$this->superprestige_wins=$this->superprestige_wins($rider_id);
		$this->gva_bpost_bank_wins=$this->gva_bpost_bank_wins($rider_id);
		$this->world_cup_titles=$this->overall_titles($rider_id, 5);
		$this->superprestige_titles=$this->overall_titles($rider_id, 2);
		$this->gva_bpost_bank_titles=$this->overall_titles($rider_id, '3, 8, 4');
	}
*/
	
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

	/**
	 * world_cup_wins function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function world_cup_wins($rider_id=0) {
		$results=uci_results_get_rider_results(array(
			'rider_id' => $rider_id, 
			'places' => 1,
			'race_classes' => 'cdm',
		));
		
		return count($results);
	}

	/**
	 * overall_titles function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @param string $series_ids (default: '')
	 * @return void
	 */
	public function overall_titles($rider_id=0, $series_ids='') {
		global $wpdb;
		
		$titles=$wpdb->get_var("SELECT COUNT(*) FROM $wpdb->uci_results_series_overall WHERE rider_id = $rider_id AND series_id IN ($series_ids) AND rank = 1");

		if ($titles==null || is_wp_error($titles))
			$titles=0;
		

		return $titles;
	}

	/**
	 * superprestige_wins function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function superprestige_wins($rider_id=0) {
		$results=uci_results_get_rider_results(array(
			'rider_id' => $rider_id, 
			'places' => 1,
			'race_series' => 'superprestige',
		));
		
		return count($results);
	}

	/**
	 * gva_bpost_bank_wins function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function gva_bpost_bank_wins($rider_id=0) {
		$results=uci_results_get_rider_results(array(
			'rider_id' => $rider_id, 
			'places' => 1,
			'race_series' => array('gva-trofee', 'bpost-bank-trophy', 'dvv-verzekeringen-trofee'),
		));
		
		return count($results);
	}

}

$uci_results_cross_stats=new UCICrossStats();
?>