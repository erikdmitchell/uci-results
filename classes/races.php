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
			'pagination' => true,
			'per_page' => 30,
			'order_by' => 'date',
			'order' => 'DESC',
			'class' => false,
			'season' => false,
			'nat' => false,
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

		if ($class)
			$where[]="class='{$class}'";

		if ($season)
			$where[]="season='{$season}'";

		if ($nat)
			$where[]="nat='{$nat}'";

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

		$seasons=$wpdb->get_col("SELECT season FROM $wpdb->ucicurl_races GROUP BY season");

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

		$classes=$wpdb->get_col("SELECT class FROM $wpdb->ucicurl_races GROUP BY class");

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

		$countries=$wpdb->get_col("SELECT nat FROM $wpdb->ucicurl_races GROUP BY nat ORDER BY nat");

		return $countries;
	}

}

$ucicurl_races=new UCIcURLRaces();
?>