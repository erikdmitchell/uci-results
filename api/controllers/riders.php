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

}
?>