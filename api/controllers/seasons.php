<?php

/**
 * Seasons class.
 */
class Seasons {
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
	public function lastWeek() {
		$week=uci_results_get_last_week_in_season($this->_params['season']);

		return $week;
	}

}
?>