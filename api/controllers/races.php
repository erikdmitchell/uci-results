<?php

/**
 * Races class.
 */
class Races {
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

/*
		if (empty($this->_params['action']))
			$this->get_races();
*/
	}

	public function races() {
		$default_args=array(
			'limit' => 15
		);
		$args=wp_parse_args($_REQUEST, $default_args);

		$races=new UCI_Results_Query(array(
			'per_page' => $args['limit'],
			'type' => 'races'
		));

		$this->result=$races->posts;
	}

	public function seasons() {
		global $ucicurl_races;

		return $ucicurl_races->seasons();
	}

}
?>