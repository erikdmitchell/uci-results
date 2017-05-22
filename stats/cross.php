<?php
/**
 * UCICrossStats class.
 * 
 * @extends UCIRiderStats
 */
class UCICrossStats extends UCIRiderStats {

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct(array(
			'id' => 'cyclocross',
			'name' => 'Cyclocross Stats',
			'discipline' => 'cyclocross',
		));
	}
	
	/**
	 * get_stats function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_stats($rider_id=0) {
		$stats=new stdClass();
		
		if (!$rider_id)
			return $stats;

		$stats->final_rankings=$this->final_rankings($rider_id);
		$stats->wins=$this->wins($rider_id);
		$stats->podiums=$this->podiums($rider_id);
		$stats->world_champs=$this->world_championships($rider_id);
		$stats->world_cup_wins=$this->world_cup_wins($rider_id);
		$stats->superprestige_wins=$this->superprestige_wins($rider_id);
		$stats->gva_bpost_bank_wins=$this->gva_bpost_bank_wins($rider_id);
		$stats->world_cup_titles=$this->overall_titles($rider_id, 5);
		$stats->superprestige_titles=$this->overall_titles($rider_id, 2);
		$stats->gva_bpost_bank_titles=$this->overall_titles($rider_id, '3, 8, 4');
			
		return $stats;
	}
	
	/**
	 * final_rankings function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function final_rankings($rider_id=0) {
		global $wpdb, $uci_results_seasons;

		if (!$rider_id)
			return false;

		$rankings=array();
		$term_id=get_term_by('slug', $this->discipline, 'season');

		$seasons=$uci_results_seasons->get_seasons(array(
			'child_of' => $term_id->term_id
		));
		
		// build season => week array //
		foreach ($seasons as $season) :
			$last_week=$uci_results_seasons->get_last_season_week($season->slug);
			$rankings[$season->slug]=$wpdb->get_row("SELECT points, rank FROM $wpdb->uci_results_rider_rankings WHERE rider_id = $rider_id AND week = $last_week AND season = '$season->name'");
		endforeach;

		if (!count($rankings))
			return array();

		return $rankings;
	}	

	/**
	 * wins function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @param string $seasons (default: '')
	 * @return void
	 */
	public function wins($rider_id=0, $seasons='') {		
		$results=uci_results_get_rider_results(array(
			'rider_id' => $rider_id, 
			'places' => 1,
			'seasons' => $seasons,
		));
		
		return count($results);
	}

	/**
	 * podiums function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @param string $seasons (default: '')
	 * @return void
	 */
	public function podiums($rider_id=0, $seasons='') {
		$results=uci_results_get_rider_results(array(
			'rider_id' => $rider_id, 
			'places' => '1, 2, 3',
			'seasons' => $seasons,
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
	 * @param string $seasons (default: '')
	 * @return void
	 */
	public function world_cup_wins($rider_id=0, $seasons='') {
		$results=uci_results_get_rider_results(array(
			'rider_id' => $rider_id, 
			'places' => 1,
			'race_classes' => 'cdm',
			'seasons' => $seasons,
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
	 * @param string $seasons (default: '')
	 * @return void
	 */
	public function superprestige_wins($rider_id=0, $seasons='') {
		$results=uci_results_get_rider_results(array(
			'rider_id' => $rider_id, 
			'places' => 1,
			'race_series' => 'superprestige',
			'seasons' => $seasons,
		));
		
		return count($results);
	}

	/**
	 * gva_bpost_bank_wins function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @param string $seasons (default: '')
	 * @return void
	 */
	public function gva_bpost_bank_wins($rider_id=0, $seasons='') {
		$results=uci_results_get_rider_results(array(
			'rider_id' => $rider_id, 
			'places' => 1,
			'race_series' => array('gva-trofee', 'bpost-bank-trophy', 'dvv-verzekeringen-trofee'),
			'seasons' => $seasons,
		));
		
		return count($results);
	}

}
?>