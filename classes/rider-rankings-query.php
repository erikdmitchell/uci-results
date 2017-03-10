<?php
global $rider_rankings_query;
global $rider_rankings_post;

class RiderRankingsQuery {

	public $posts;

	public $query;

	public $query_vars;

	public $current_post=-1;

	public $post_count=0;

	public $post;

	public $rider_rankings=false;

	public $rider_results=false;

	public $max_num_pages=0;

	public $found_posts=0;

	public $is_paged=false;

	public $is_search=false;

	public $is_rankings_stored=false;


	/**
	 * __construct function.
	 *
	 * @access public
	 * @param string $query (default: '')
	 * @return void
	 */
	public function __construct($query='') {
		global $rider_rankings_query;

		if (!empty($query))
			$rider_rankings_query=$this->query($query);
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
			'order_by' => 'rank',
			'order' => 'DESC',
			'season' => uci_results_get_default_rider_ranking_season(),
			'week' => uci_results_get_default_rider_ranking_week(),
			'nat' => '',
			'search' => false,
			'paged' => get_query_var('page'),
		);

		// for our admin, we pass a get var //
		if (is_admin() && empty($array['paged']) && isset($_GET['paged']))
			$array['paged']=$_GET['paged'];

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

		// check for search //
		if (isset($_GET['search']) || (isset($query['search']) && $query['search']))
			$this->is_search=true;

		// check if paged //
		if ($args['paged'])
			$this->is_paged=true;

		return $args;
	}

	/**
	 * query function.
	 *
	 * @access public
	 * @param string $query (default: '')
	 * @return void
	 */
	public function query($query='') {
		global $wpdb;

		$limit='';
		$where='';
		$order='';
		$meta='';
		$select='';

		$this->query_vars=$this->set_query_vars($query);
		$q=$this->query_vars;

		$where=$this->where_clause($q);
		$order=$this->order_clause($q);
		$limit=$this->set_limit($q);

		// run specific query if need be //
		//if ($this->is_search) : // a search //
			//$this->query=$this->search_query($db_table);
		
		$this->query="
			SELECT SQL_CALC_FOUND_ROWS 
			posts.ID,
			posts.post_title,
			posts.post_name,
			rankings.points,
			rankings.rank 
			FROM $wpdb->uci_results_rider_rankings AS rankings
			INNER JOIN $wpdb->posts AS posts ON posts.ID = rankings.rider_id
			$where $order $limit
		";

		$this->get_posts();

		// set max number of pages //
		if (!empty($limit))
			$this->max_num_pages=ceil($this->found_posts/$q['per_page']);

		// force update 'paged' query var //
		if ($this->is_paged)
			set_query_var('paged', $q['paged']);

		return $this;
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

		// set total number of posts found //
		$this->found_posts = $wpdb->get_var('SELECT FOUND_ROWS()');

		// append nation //
		foreach ($posts as $post) :
			$nat=wp_get_post_terms($post->ID, 'country');
			
			if (isset($nat[0])) :
				$post->nat=$nat[0]->name;
			else :
				$post->nat='';
			endif;
		endforeach;

		$this->posts=$posts;
		$this->post_count=count($posts);

		return $this->posts;
	}

	/**
	 * where_clause function.
	 *
	 * @access protected
	 * @param mixed $q
	 * @return void
	 */
	protected function where_clause($q) {
		$where=array();

		// check season //
		if ($q['season'])
			$where[]="season='".$q['season']."'";

		// check nat //
		if ($q['nat'])
			$where[]="nat='".$q['nat']."'";

		// check week, get last week in season by default //
		if (!empty($q['week'])) :
			$week=absint($q['week']);
		else :
			$last_week=$wpdb->get_var("SELECT MAX(week) FROM $wpdb->uci_results_races WHERE season='".$q['season']."'");
			$week=absint($last_week);
		endif;
		
		// setup week //
		if ($week)
			$where[]="week=$week";

		// build our where query //
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
	 * search_query function.
	 *
	 * @access protected
	 * @param string $table (default: '')
	 * @return void
	 */
	protected function search_query($table='') {
		global $wpdb;

		$query='';

		// set search value //
		if (isset($_GET['search'])) :
			$search_value=$_GET['search'];
		else :
			$search_value=$this->query_vars['search'];
		endif;

		if ($this->query_vars['type']=='races') :
			$query="SELECT * FROM $table WHERE event LIKE '%".$search_value."%'";
		elseif ($this->query_vars['type']=='riders') :
			$query="SELECT * FROM $table WHERE name LIKE '%".$search_value."%'";
		else :
			$query="
				SELECT id, event COLLATE utf8mb4_general_ci AS name, 'race' AS type FROM $wpdb->uci_results_races WHERE event LIKE '%".$search_value."%'
				UNION
				SELECT id, name, 'rider' AS type FROM $wpdb->uci_results_riders WHERE name LIKE '%".$search_value."%'
			";
		endif;

		return $query;
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
		global $rider_rankings_post;

		$rider_rankings_post = $this->next_post();
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
?>