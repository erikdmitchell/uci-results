<?php
global $uci_results_query;
global $uci_results_post;

class UCI_Results_Query {

	public $posts;

	public $query;

	public $query_vars;

	public $current_post=-1;

	public $post_count=0;

	public $post;

	public $rider_rankings_query=false;


	/**
	 * __construct function.
	 *
	 * @access public
	 * @param string $query (default: '')
	 * @return void
	 */
	public function __construct($query='') {
		global $uci_results_query;

		if (!empty($query))
			$uci_results_query=$this->query($query);
	}

	/**
	 * default_query_vars function.
	 *
	 * contains all our default query vars
	 *
	 * @access public
	 * @return void
	 */
	public function default_query_vars() {
		$array=array(
			'per_page' => 30,
			'order_by' => '', // date (races -- name (riders)
			'order' => '', // DESC (races -- ASC (riders)
			'class' => false, // races
			'season' => false, // races, rider ranks
			'nat' => false,
			'name' => false, // riders
			'start_date' => false, // races
			'end_date' => false, // races
			'paged' => 1,
			'type' => 'races',
			'rankings' => false // riders
		);

		return $array;
	}

	/**
	 * set_query_vars function.
	 *
	 * utalizes our query to setup our query vars
	 *
	 * @access public
	 * @param string $query (default: '')
	 * @return void
	 */
	public function set_query_vars($query='') {
		$args=wp_parse_args($query, $this->default_query_vars());

		// set default order by type if need be //
		if (empty($args['order_by'])) :
			switch ($args['type']) :
				case 'races':
					$args['order_by']='date';

					if (empty($args['order']))
						$args['order']='DESC';
					break;
				case 'riders':
					$args['order_by']='rank';

					if (empty($args['order']))
						$args['order']='ASC';
					break;
			endswitch;
		endif;

		return $args;
	}

	public function query($query='') {
		global $wpdb;

		$limit='';
		$where='';
		$order='';
		// $pieces = array( 'where', 'groupby', 'join', 'orderby', 'distinct', 'fields', 'limits' );

		$this->query_vars=$this->set_query_vars($query);
		$q=$this->query_vars;

		$db_table=$this->set_db_table($q);
		$where=$this->where_clause($q);
		$order=$this->order_clause($q);
		$limit=$this->set_limit($q);

		// we are looking for rider rankings //
		if ($q['rankings']) :
			$this->rider_rankings_query=$this->rider_rankings_query($q, $where, $order, $limit);
			$this->query=$this->rider_rankings_query;
		else :
			$this->query="SELECT * FROM $db_table $where $order $limit";
		endif;

		$this->get_posts();

/*
echo '<pre>';
print_r($this);
echo '</pre>';
*/

	}

	/**
	 * get_posts function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_posts() {
		global $wpdb;

		$posts=$wpdb->get_results($this->query);

		if ($this->query_vars['type']=='races')
			$posts=$this->races_clean_up($posts);

		$this->posts=$posts;
		$this->post_count=count($posts);

		return $this->posts;
	}

	protected function where_clause($q) {
		$where=array();

		// check class //
		if ($q['class'])
			$where[]="class='".$q['class']."'";

		// check season //
		if ($q['season'])
			$where[]="season='".$q['season']."'";

		// check nat //
		if ($q['nat'])
			$where[]="nat='".$q['nat']."'";

		// check start and end //
		if ($q['start_date'] && $q['end_date']) :
			$where[]="(date BETWEEN '".$q['start_date']."' AND '".$q['end_date']."')";
		endif;

		// check name //
		if ($q['name'])
			$where[]="name='".$q['name']."'";

		if (!empty($where)) :
			$where=' WHERE '.implode(' AND ',$where);
		else :
			$where='';
		endif;

		return $where;
	}

	/**
	 * order_clause function.
	 *
	 * @access protected
	 * @param mixed $q
	 * @return void
	 */
	protected function order_clause($q) {
		if (empty($q['order_by']))
			return;

		$order='ORDER BY '.$q['order_by'].' '.$q['order'];

		return $order;
	}

	/**
	 * set_limit function.
	 *
	 * sets the limit based on our query vars
	 *
	 * @access protected
	 * @param mixed $q
	 * @return void
	 */
	protected function set_limit($q) {
		$paged=absint($q['paged']);
		$per_page=$q['per_page'];

		// no limit //
		if ($per_page<0)
			return;

		if ($paged==0) :
			$start=0;
		else :
			$start=$per_page*($paged-1);
		endif;

		$end=$per_page;

		return "LIMIT $start,$end";
	}

	/**
	 * races_clean_up function.
	 *
	 * clean up some misc db slashes and formatting
	 *
	 * @access protected
	 * @param mixed $posts
	 * @return void
	 */
	protected function races_clean_up($posts) {
		foreach ($posts as $post) :
			$post->code=stripslashes($post->code);
			$post->name=stripslashes($post->event);
			$post->date=date(get_option('date_format'), strtotime($post->date));
		endforeach;

		return $posts;
	}

	/**
	 * set_db_table function.
	 *
	 * @access protected
	 * @param mixed $q
	 * @return void
	 */
	protected function set_db_table($q) {
		global $wpdb;

		switch ($q['type']) :
			case 'races':
				$table=$wpdb->ucicurl_races;
				break;
			case 'riders':
				$table=$wpdb->ucicurl_riders;
				break;
			default:
				$table=$wpdb->posts;
		endswitch;

		return $table;
	}

	/**
	 * rider_rankings_query function.
	 *
	 * @access protected
	 * @param mixed $q
	 * @param mixed $where
	 * @param mixed $order
	 * @param mixed $limit
	 * @return void
	 */
	protected function rider_rankings_query($q, $where, $order, $limit) {
		global $wpdb;

		// check week, get last week in season by default //
		if (!empty($q['week'])) :
			$week=absint($q['week']);
		else :
			$last_week=$wpdb->get_var("SELECT MAX(week) FROM $wpdb->ucicurl_races WHERE season='".$q['season']."'");
			$week=absint($last_week);
		endif;

		// if we have where append it, else make it where //
		if (empty($where)) :
			$where="WHERE week=$week";
		else :
			$where.=" AND week=$week";
		endif;

		// square away a default order by //
		if (empty($order))
			$order="ORDER BY rank";

		$sql="SELECT * FROM $wpdb->ucicurl_rider_rankings AS rankings	LEFT JOIN $wpdb->ucicurl_riders AS riders	ON riders.id=rankings.rider_id $where	$order $limit";

		return $sql;
	}

	/**
	 * have_posts function.
	 *
	 * @access public
	 * @return void
	 */
	public function have_posts() {
		if ($this->current_post + 1 < $this->post_count) :
			return true;
		elseif ( $this->current_post + 1 == $this->post_count && $this->post_count > 0 ) :
			$this->rewind_posts();
		endif;

		return false;
	}

	/**
	 * the_post function.
	 *
	 * @access public
	 * @return void
	 */
	public function the_post() {
		global $uci_results_post;

		$uci_results_post = $this->next_post();
	}

  /**
   * next_post function.
   *
   * @access public
   * @return void
   */
  public function next_post() {
		$this->current_post++;

		$this->post = $this->posts[$this->current_post];

		return $this->post;
	}

	/**
	 * rewind_posts function.
	 *
	 * @access public
	 * @return void
	 */
	public function rewind_posts() {
		$this->current_post = -1;

		if ( $this->post_count > 0 )
			$this->post = $this->posts[0];
	}

}



function uci_results_pagination($args='') {
	$defauls=array(

	);
	$args=wp_parse_args($args, $defaults);
}
// $wp_query->max_num_pages
// get_query_var( 'paged' )
// $pagenum_link = trailingslashit( $url_parts[0] ) . '%_%';
?>