<?php
class Races {
	public $result;

	private $_params;

	public function __construct($params) {
		$this->_params=$params;

		if (empty($this->_params['action']))
			$this->get_races();
	}

	public function get_races() {
//echo 'get races';
		$this->result='get races';
	}

}
?>