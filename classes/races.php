<?php
global $uci_races;

/**
 * UCIRaces class.
 *
 * @since Version 2.0.0
 */
class UCIRaces {
	
	public $version='0.1.0';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action('admin_init', array($this, 'add_related_races'));
		add_action('wp_ajax_search_related_races', array($this, 'ajax_search_related_races'));
	}



	/**
	 * get_related_races function.
	 *
	 * @access public
	 * @param int $race_id (default: 0)
	 * @return void
	 */


	/**
	 * get_related_races_ids function.
	 *
	 * @access public
	 * @param int $race_id (default: 0)
	 * @return void
	 */
	public function get_related_races_ids($race_id=0) {
		global $wpdb;

		$related_race_id=$this->get_related_race_id($race_id);
		$related_races_db=$wpdb->get_var("SELECT race_ids FROM {$wpdb->uci_results_related_races} WHERE id={$related_race_id}");

		if (!$related_races_db)
			return false;

		return explode(',', $related_races_db);
	}

	/**
	 * ajax_search_related_races function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_search_related_races() {
		global $wpdb;

		$html=null;
		$query=$_POST['query'];
		$races=$wpdb->get_results("SELECT * FROM {$wpdb->uci_results_races} WHERE event LIKE '%{$query}%'");
		$related_races=$this->get_related_races_ids($_POST['race_id']);

		// build out html //
		foreach ($races as $race) :
			if ($race->id==$_POST['race_id'] || in_array($race->id, $related_races))
				continue; // skip if current race or already linked

			$html.='<tr>';
				$html.='<th scope="row" class="check-column"><input id="cb-select-'.$race->id.'" type="checkbox" name="races[]" value="'.$race->id.'"></th>';
				$html.='<td class="race-date">'.date(get_option('date_format'), strtotime($race->date)).'</td>';
				$html.='<td class="race-name">'.$race->event.'</td>';
				$html.='<td class="race-nat">'.$race->nat.'</td>';
				$html.='<td class="race-class">'.$race->class.'</td>';
				$html.='<td class="race-season">'.$race->season.'</td>';
			$html.='</tr>';
		endforeach;

		echo $html;

		wp_die();
	}

	/**
	 * add_related_races function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_related_races() {
		global $wpdb;

		if (!isset($_POST['uci_curl']) || !wp_verify_nonce($_POST['uci_curl'], 'add_related_races'))
			return false;

		$related_race_id=0;
		$races=array();

		if ($_POST['related_race_id']) :
			$related_race_id=$_POST['related_race_id'];

			// update if we have races, other wise remove //
			if (isset($_POST['races']) && !empty($_POST['races'])) :
				$data=array(
					'race_ids' => implode(',', $_POST['races']).','.$_POST['race_id'] // get our ids and append the current race id
				);

				$races=$_POST['races'];
				array_push($races, $_POST['race_id']);

				$wpdb->update($wpdb->uci_results_related_races, $data, array('id' => $_POST['related_race_id']));
			else :
				$wpdb->delete($wpdb->uci_results_related_races, array('id' => $_POST['related_race_id']));
			endif;

		else :
			$data=array(
				'race_ids' => implode(',', $_POST['races']).','.$_POST['race_id'] // get our ids and append the current race id
			);

			$races=$_POST['races'];
			array_push($races, $_POST['race_id']);

			$related_race_id=$wpdb->insert($wpdb->uci_results_related_races, $data);
		endif;

		// add related race id to each race //
		foreach ($races as $race) :
			$wpdb->update($wpdb->uci_results_races, array('related_races_id' => $related_race_id), array('id' => $race));
		endforeach;

		echo '<div class="updated">Related Races Updated!</div>';
	}

	/**
	 * weeks function.
	 *
	 * @access public
	 * @param string $season (default: '2015/2016')
	 * @return void
	 */
	public function weeks($season='2015/2016') {
		global $wpdb;

		$weeks=$wpdb->get_col("SELECT week FROM $wpdb->uci_results_races WHERE season='$season' GROUP BY week ORDER BY week ASC");

		return $weeks;
	}

}

$uci_races=new UCIRaces();
?>