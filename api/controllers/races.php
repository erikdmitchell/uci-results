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
	}

	/**
	 * _default function.
	 *
	 * @access public
	 * @return void
	 */
	public function _default() {
		return $this->races();
	}

	/**
	 * races function.
	 *
	 * @access public
	 * @return void
	 */
	public function races() {
		global $ucicurl_races;

		$default_args=array(
			'limit' => 15,
			'results' => true
		);
		$args=wp_parse_args($_REQUEST, $default_args);

		// looking for single race //
		if (isset($this->_params['race']))
			return $ucicurl_races->get_race($this->_params['race'], $args['results']);

		$races=new UCI_Results_Query(array(
			'per_page' => $args['limit'],
			'type' => 'races'
		));

		return $races->posts;
	}

	/**
	 * seasons function.
	 *
	 * @access public
	 * @return void
	 */
	public function seasons() {
		global $ucicurl_races;

		return $ucicurl_races->seasons();
	}

	/**
	 * series function.
	 *
	 * @access public
	 * @return void
	 */
	public function series() {
		global $wpdb;

		if (isset($this->_params['id']) && $this->_params['id']) :
			$series=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_series WHERE id=".$this->_params['id']);
		else :
			$series=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_series");
		endif;

		return $series;
	}

	/**
	 * seriesName function.
	 *
	 * @access public
	 * @return void
	 */
	public function seriesName() {
		global $wpdb;

		$name='';

		if (isset($this->_params['id']) && $this->_params['id'])
			$name=$wpdb->get_var("SELECT name FROM $wpdb->uci_results_series WHERE id=".$this->_params['id']);

		return $name;
	}

	/**
	 * buildRaceCode function.
	 *
	 * @access public
	 * @return void
	 */
	public function buildRaceCode() {
		global $uci_results_add_races;

		$code=$uci_results_add_races->build_race_code($this->_params['name'], $this->_params['date']);

		return $code;
	}

	/**
	 * raceID function.
	 *
	 * @access public
	 * @return void
	 */
	public function raceID() {
		$id=uci_results_get_race_id($this->_params['slug']);

		return $id;
	}

	/**
	 * related function.
	 *
	 * @access public
	 * @return void
	 */
	public function related() {
		global $wpdb, $ucicurl_races;

		$related_races=array();
		$race_id=uci_results_get_race_id($this->_params['slug']);
		$related_race_ids_string=$wpdb->get_var("SELECT race_ids FROM $wpdb->uci_results_related_races WHERE race_ids LIKE '%$race_id%'");
		$related_race_ids=explode(',', $related_race_ids_string);

		// get race details //
		foreach ($related_race_ids as $id) :
			$related_races[]=$ucicurl_races->get_race($id, false);
		endforeach;

		return $related_races;
	}

}
?>