<?php
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
	 * get_races function.
	 *
	 * @access public
	 * @param array $args (default: array())
	 * @return void
	 */
	public function get_races($args=array()) {
		global $wpdb, $wp_query;

		$limit=null;
		$where=array();
		$paged=isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 1;
		$default_args=array(
			'pagination' => false,
			'per_page' => 30,
			'order_by' => 'date',
			'order' => 'DESC',
			'class' => false,
			'season' => false,
			'nat' => false,
			'start_date' => false,
			'end_date' => false
		);
		$args=array_merge($default_args, $args);

		// check filters //
		if (isset($_POST['ucicurl_admin']) && wp_verify_nonce($_POST['ucicurl_admin'], 'filter_races'))
			$args=wp_parse_args($_POST, $args);

		// check search //
		if (isset($_GET['search']) && $_GET['search']!='')
			$where[]="event LIKE '%{$_GET['search']}%'";

		extract($args);

		if ($pagination) :
			if ($paged==0) :
				$start=0;
			else :
				$start=$per_page*($paged-1);
			endif;
			$end=$per_page;
			$limit="LIMIT $start,$end";
		endif;

		// check class //
		if ($class)
			$where[]="class='{$class}'";

		// check season //
		if ($season)
			$where[]="season='{$season}'";

		// check nat //
		if ($nat)
			$where[]="nat='{$nat}'";

		// check start and end //
		if ($start_date && $end_date) :
			$where[]="(date BETWEEN '{$start_date}' AND '{$end_date}')";
		endif;

		if (!empty($where)) :
			$where=' WHERE '.implode(' AND ',$where);
		else :
			$where='';
		endif;

		$sql="
			SELECT
				*
			FROM {$wpdb->ucicurl_races} AS races
			{$where}
			ORDER BY {$order_by} {$order}
			{$limit}
		";
		$races=$wpdb->get_results($sql);

		// for pagination //
		$this->admin_pagination['limit']=$args['per_page'];
		$this->admin_pagination['total']=$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->ucicurl_races} {$where}");

		// clean up some misc db slashes and formatting //
		foreach ($races as $race) :
			$race->code=stripslashes($race->code);
			$race->name=stripslashes($race->event);
			$race->date=date(get_option('date_format'), strtotime($race->date));
		endforeach;

		return $races;
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
		$pagination=new UCIcURLPagination($this->admin_pagination['total'], $this->admin_pagination['limit'], admin_url('admin.php?page=uci-curl&tab=races'));

		echo $pagination->get_pagination();
	}

	/**
	 * races function.
	 *
	 * @access public
	 * @return void
	 */
	public function races() {
		return $this->get_races();
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

	public function get_related_races($race_id=0) {
		global $wpdb;

		$related_races=$wpdb->get_col("SELECT race_ids FROM {$wpdb->ucicurl_related_races} WHERE id={$race_id}");

		return $related_races;
	}

	public function ajax_search_related_races() {
		global $wpdb;

		$html=null;
		$query=$_POST['query'];
		$races=$wpdb->get_results("SELECT * FROM {$wpdb->ucicurl_races} WHERE event LIKE '%{$query}%'");

		// build out html //
		foreach ($races as $race) :
			if ($race->id==$_POST['race_id'])
				continue; // skip if current race

			$html.='<tr>';
				$html.='<th scope="row" class="check-column"><input id="cb-select-'.$race->ID.'" type="checkbox" name="race[]" value="'.$race->ID.'"></th>';
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

	public function add_related_races() {

	}

}

$ucicurl_races=new UCIcURLRaces();
?>