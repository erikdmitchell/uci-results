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
			$results=$this->_params['ranking'];

		if (isset($this->_params['stats']))
			$results=$this->_params['stats'];

		$rider=$ucicurl_riders->get_rider($this->_params['id'], $results, $ranking, $stats);

		return $rider;
	}

}
?>