<?php

/**
 * Flags class.
 */
class flags {
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
	 * flag function.
	 *
	 * @access public
	 * @return void
	 */
	public function flag() {
		$flag=uci_results_get_country_flag($this->_params['country'], $this->_params['addon']);

		return $flag;
	}

}
?>