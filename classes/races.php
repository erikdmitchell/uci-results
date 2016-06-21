<?php
global $ucicurl_races;

/**
 * UCIcURLRaces class.
 *
 * @since Version 2.0.0
 */
class UCIcURLRaces {

	public $admin_pagination=array();

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
	 * get_race function.
	 *
	 * @access public
	 * @param int $race_id (default: 0)
	 * @return void
	 */
	public function get_race($race_id=0) {
		global $wpdb;

		// check if numeric, otherwise, it's a slug (code) //
		if (!is_numeric($race_id))
			$race_id=uci_results_get_race_id($race_id);

		$race=$wpdb->get_row("SELECT * FROM {$wpdb->ucicurl_races} WHERE id={$race_id}");
		$race->results=$wpdb->get_results("SELECT * FROM {$wpdb->ucicurl_results} WHERE race_id={$race_id}");

		return $race;
	}

	/**
	 * admin_pagination function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_pagination() {
		$pagination=new UCIcURLPagination($this->admin_pagination['total'], $this->admin_pagination['limit']);

		echo $pagination->get_pagination();
	}

	/**
	 * races function.
	 *
	 * @access public
	 * @param array $args (default: array())
	 * @return void
	 */
	public function races($args=array()) {
		return $this->get_races($args);
	}

	/**
	 * seasons function.
	 *
	 * @access public
	 * @return void
	 */
	public function seasons() {
		global $wpdb;

		$seasons=$wpdb->get_col("SELECT season FROM {$wpdb->ucicurl_races} GROUP BY season");

		return $seasons;
	}

	/**
	 * classes function.
	 *
	 * @access public
	 * @return void
	 */
	public function classes() {
		global $wpdb;

		$classes=$wpdb->get_col("SELECT class FROM {$wpdb->ucicurl_races} GROUP BY class");

		return $classes;
	}

	/**
	 * nats function.
	 *
	 * @access public
	 * @return void
	 */
	public function nats() {
		global $wpdb;

		$countries=$wpdb->get_col("SELECT nat FROM {$wpdb->ucicurl_races} GROUP BY nat ORDER BY nat");

		return $countries;
	}

	/**
	 * get_related_races function.
	 *
	 * @access public
	 * @param int $race_id (default: 0)
	 * @return void
	 */
	public function get_related_races($race_id=0) {
		global $wpdb;

		$related_races=array();
		$related_race_id=$this->get_related_race_id($race_id);
		$related_races_db=$wpdb->get_var("SELECT race_ids FROM {$wpdb->ucicurl_related_races} WHERE id={$related_race_id}");

		if (!$related_races_db)
			return false;

		$related_race_ids=explode(',', $related_races_db);

		// build out races //
		foreach ($related_race_ids as $id) :
			if ($id==$race_id)
				continue;

			$related_races[]=$this->get_race($id);
		endforeach;

		return $related_races;
	}

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
		$related_races_db=$wpdb->get_var("SELECT race_ids FROM {$wpdb->ucicurl_related_races} WHERE id={$related_race_id}");

		if (!$related_races_db)
			return false;

		return explode(',', $related_races_db);
	}

	/**
	 * get_related_race_id function.
	 *
	 * @access public
	 * @param int $race_id (default: 0)
	 * @return void
	 */
	public function get_related_race_id($race_id=0) {
		global $wpdb;

		$related_db=$wpdb->get_results("SELECT * FROM {$wpdb->ucicurl_related_races} WHERE race_ids LIKE '%{$race_id}%'");

		foreach ($related_db as $arr) :
			$ids=explode(',', $arr->race_ids);

			if (in_array($race_id, $ids))
				return $arr->id;

		endforeach;

		return 0;
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
		$races=$wpdb->get_results("SELECT * FROM {$wpdb->ucicurl_races} WHERE event LIKE '%{$query}%'");
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

		if ($_POST['related_race_id']) :
			// update if we have races, other wise remove //
			if (isset($_POST['races']) && !empty($_POST['races'])) :
				$data=array(
					'race_ids' => implode(',', $_POST['races']).','.$_POST['race_id'] // get our ids and append the current race id
				);

				$wpdb->update($wpdb->ucicurl_related_races, $data, array('id' => $_POST['related_race_id']));
			else :
				$wpdb->delete($wpdb->ucicurl_related_races, array('id' => $_POST['related_race_id']));
			endif;

		else :
			$data=array(
				'race_ids' => implode(',', $_POST['races']).','.$_POST['race_id'] // get our ids and append the current race id
			);

			$wpdb->insert($wpdb->ucicurl_related_races, $data);
		endif;

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

		$weeks=$wpdb->get_col("SELECT week FROM {$wpdb->ucicurl_races} WHERE season='{$season}' GROUP BY week ORDER BY week ASC");

		return $weeks;
	}
}

$ucicurl_races=new UCIcURLRaces();
?>