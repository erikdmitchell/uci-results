<?php

/**
 * Riders class.
 */
class Riders {
	private $_params;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $params
	 * @return void
	 */
	public function __construct($params) {
		$this->_params=$params;
	}

	/**
	 * getID function.
	 *
	 * @access public
	 * @return void
	 */
	public function getID() {
		$id=uci_results_get_rider_id($this->_params['slug']);

		return $id;
	}

	/**
	 * rider function.
	 *
	 * @access public
	 * @return void
	 */
	public function rider() {
		global $ucicurl_riders;

		$results=false;
		$ranking=false;
		$stats=false;

		if (isset($this->_params['results']))
			$results=$this->_params['results'];

		if (isset($this->_params['ranking']))
			$ranking=$this->_params['ranking'];

		if (isset($this->_params['stats']))
			$stats=$this->_params['stats'];

		$rider=$ucicurl_riders->get_rider($this->_params['id'], $results, $ranking, $stats);

		return $rider;
	}

	/**
	 * currentRank function.
	 *
	 * @access public
	 * @return void
	 */
	public function currentRank() {
		global $wpdb;

		if (!isset($this->_params['season']) || !isset($this->_params['rider_id']))
			return false;

		// if we have week, get specific, else get last week //
		if (isset($this->_params['week'])) :
			$current_rank=uci_results_get_rider_rank($this->_params['rider_id'], $this->_params['season'], $this->_params['week']);
		else :
			$current_rank=$wpdb->get_row("SELECT * FROM $wpdb->uci_results_rider_rankings WHERE season='".$this->_params['season']."' AND rider_id=".$this->_params['rider_id']." ORDER BY week DESC LIMIT 1");
		endif;

		return $current_rank;
	}

}
?>