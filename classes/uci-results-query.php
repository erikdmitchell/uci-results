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
			'order_by' => '', // date (races) -- name (riders)
			'order' => 'DESC', // DESC (races -- ASC (riders))
			'class' => false, // races
			'season' => false, // races, rider ranks
			'nat' => false,
			'name' => false, // riders
			'search' => false,
			'rider_id' => 0, // riders
			'start_date' => false, // races
			'end_date' => false, // races
			'paged' => get_query_var('page'),
			'type' => 'races',
			'rankings' => false, // riders
			'results' => false, // riders
			'meta' => array()
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

		// set default order by type if need be //
		if (empty($args['order_by'])) :
			switch ($args['type']) :
				case 'races':
					$args['order_by']='date';

					if (empty($args['order']))
						$args['order']='DESC';
					break;
				case 'riders':
					break;
			endswitch;
		endif;

		// setup some defaults for rankings //
		if ($args['rankings']) :
			if (!$args['season'] || empty($args['season'])) :
				$args['season']=uci_results_get_default_rider_ranking_season();
				$args['week']=uci_results_get_default_rider_ranking_week();
			endif;
		endif;

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

		$this->query_vars=$this->set_query_vars($query);
		$q=$this->query_vars;

		$db_table=$this->set_db_table($q);
		$where=$this->where_clause($q);
		$order=$this->order_clause($q);
		$limit=$this->set_limit($q);
		$meta=$this->meta_query($q);

		// run specific query if need be //
		if ($this->is_search) : // a search //
			$this->query=$this->search_query($db_table);
		//elseif ($q['rankings']) : // we are looking for rider rankings //
			//$this->rider_rankings_query=$this->rider_rankings_query($q, $where, $order, $limit, $meta);
			//$this->query=$this->rider_rankings_query;
		else : // general query //
			// cycle through meta and attach our "queries" //
			if (!empty($meta)) :
				foreach ($meta as $type => $queries) :
					foreach ($queries as $query) :
						// the where type - make sure we check that where exists //
						if ($type=='where') :
							if (empty($where)) :
								$where="WHERE ".$query;
							else :
								$where.=$query;
							endif;
						endif;
					endforeach;
				endforeach;
			endif;

			$this->query="SELECT SQL_CALC_FOUND_ROWS * FROM $db_table $where $order $limit";

			// setup query for rankings //
			if ($q['rankings']) :
				$this->rider_rankings=true;
				$this->query=$this->rider_rankings_query($q, $where, $order, $limit);
			endif;
		endif;

		$this->get_posts();

		// set total number of posts found //
		if (!empty($limit))
			$this->found_posts = $wpdb->get_var('SELECT FOUND_ROWS()');

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

		if ($this->query_vars['type']=='races')
			$posts=$this->races_clean_up($posts);

		// stored, so we need to append //
		//if ($this->is_rankings_stored)
			//$posts=$this->update_posts_with_stored_rankings($posts);

		// we want results //
		if ($this->query_vars['type']=='riders' && $this->query_vars['results'])
			$posts=$this->rider_results_posts($posts);

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

		// check class //
		if ($q['class'])
			$where[]="class='".$q['class']."'";

		// check season //
		if ($q['type']=='races' && $q['season'])
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

		// check rider id //
		if ($q['rider_id']) :
			if ($q['rankings']) :
				$where[]="riders.id=".$q['rider_id'];
			else :
				$where[]="id=".$q['rider_id'];
			endif;
		endif;

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
	 * @param string $q (default: '')
	 * @param bool $type (default: false)
	 * @return void
	 */
	protected function set_db_table($q='', $type=false) {
		global $wpdb;

		// check passed type directly //
		if (!$type)
			$type=$q['type'];

		switch ($type) :
			case 'races':
				$table=$wpdb->uci_results_races;
				break;
			case 'riders':
				$table=$wpdb->uci_results_riders;
				break;
			case 'series':
				$table=$wpdb->uci_results_series;
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

		// get stored rankings //
		$stored_rankings=uci_results_get_stored_rankings();

		// check week, get last week in season by default //
		if (!empty($q['week'])) :
			$week=absint($q['week']);
		else :
			$last_week=$wpdb->get_var("SELECT MAX(week) FROM $wpdb->uci_results_races WHERE season='".$q['season']."'");
			$week=absint($last_week);
		endif;

		// if we have where append it, else make it where //
		if (empty($where)) :
			$where="WHERE  $wpdb->uci_results_rider_rankings.week=$week AND  $wpdb->uci_results_rider_rankings.season='".$q['season']."'";
		else :
			$where.=" AND  $wpdb->uci_results_rider_rankings.week=$week";
		endif;

		// square away a default order by //
		if (empty($order))
			$order="ORDER BY rank";

		// check if we can use stored rankings and modify sql //
/*
		if (isset($q['season']) && isset($q['week']) && isset($stored_rankings->season) && isset($stored_rankings->week)) :
			if ($q['season']==$stored_rankings->season && $q['week']==$stored_rankings->week) :
				$this->is_rankings_stored=true;
				$where=$this->stored_rankings_clean_where($q, $where);
			endif;
		endif;
*/

		$sql="
			SELECT SQL_CALC_FOUND_ROWS
				$wpdb->uci_results_riders.*,
				$wpdb->uci_results_rider_rankings.points,
				$wpdb->uci_results_rider_rankings.rank
				FROM $wpdb->uci_results_riders
				INNER JOIN $wpdb->uci_results_rider_rankings ON $wpdb->uci_results_riders.id = $wpdb->uci_results_rider_rankings.rider_id
				$where
				$order
				$limit
		";

		return $sql;
	}

	/**
	 * stored_rankings_clean_where function.
	 *
	 * @access protected
	 * @param mixed $query
	 * @param mixed $where
	 * @return void
	 */
	protected function stored_rankings_clean_where($query, $where) {
		$where_clean=str_replace(' WHERE ', '', $where); // remove the WHERE text
		$where_arr=explode(' AND ', $where_clean); // put into array
		$rider_db_fields=array('id', 'riders.id', 'name' , 'nat', 'slug');
		$arr=array();
		$final_arr=array();

		// build array in key/value pairs //
		foreach ($where_arr as $string) :
			$string_arr=explode('=', $string);
			$arr[$string_arr[0]]=$string_arr[1];
		endforeach;

		// we need to remove things that are not vaild riders fields //
		foreach ($arr as $key => $value) :
			if (array_search($key, $rider_db_fields)===false) :
				unset($arr[$key]);
			endif;
		endforeach;

		// rebuild our arr from key/value pairs //
		foreach ($arr as $key => $value) :
			$final_arr[]="$key = $value";
		endforeach;

		// rebuild our string //
		if (!empty($final_arr)) :
			$where=' WHERE '.implode(' AND ', $final_arr);
		else :
			$where='';
		endif;

		return $where;
	}

	/**
	 * update_posts_with_stored_rankings function.
	 *
	 * @access protected
	 * @param mixed $posts
	 * @return void
	 */
	protected function update_posts_with_stored_rankings($posts) {
		if (!$this->is_rankings_stored)
			return $posts;

		$stored_rankings=uci_results_get_stored_rankings();

		if (empty($this->query_vars['order_by'])) :
			$order_by='rank';
		else :
			$order_by=$this->query_vars['order_by'];
		endif;

		// sort using our order and order by param //
		// setup proper order //
		if ($this->query_vars['order']=='ASC') :
			$order=SORT_ASC;
		elseif ($this->query_vars['order']=='DESC') :
			$order=SORT_DESC;
		endif;

		$sort=array();
		foreach ($stored_rankings->riders as $rider) :
			$sort[]=$rider->$order_by;
		endforeach;
		array_multisort($sort, SORT_ASC, $stored_rankings->riders);

		// if there's no where statement, we can just use the limit param //
		if (strpos($this->rider_rankings, 'WHERE') === false) :
			if (strpos($this->rider_rankings, 'LIMIT') === false) :
				return $stored_rankings->riders; // no LIMIT - return all
			else :
				$limit_exp=explode('LIMIT', $this->rider_rankings);
				$limit_arr=explode(',', $limit_exp[1]);

				return array_slice($stored_rankings->riders, $limit_arr[0], $limit_arr[1]);
			endif;
		endif;

		// loop through posts //
		foreach ($posts as $key => $post) :

			// loop through stored riders //
			foreach ($stored_rankings->riders as $rider) :
				// find a match and replace our post data //
				if ($post->id==$rider->id) :
					$posts[$key]=$rider;
				endif;

			endforeach;
		endforeach;

		return $posts;
	}

	/**
	 * rider_results_posts function.
	 *
	 * @access protected
	 * @param mixed $posts
	 * @return void
	 */
	protected function rider_results_posts($posts) {
		global $wpdb;

		// double check //
		if (empty($this->query_vars['results']))
			return false;

		$this->rider_results=true;

		foreach ($posts as $post) :
			$sql="
				SELECT
				 results.place,
				 results.result,
				 results.par AS points,
				 races.date,
				 races.event,
				 races.class,
				 races.season,
				 races.code,
				 races.related_races_id,
				 races.series_id
				FROM $wpdb->uci_results_results AS results
				LEFT JOIN $wpdb->uci_results_races AS races ON results.race_id=races.id
				WHERE rider_id = $post->id
				ORDER BY date DESC
			";
			$post->results=$wpdb->get_results($sql);
			$post->last_race=$post->results[0];
		endforeach;

		return $posts;
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
	 * meta_query function.
	 *
	 * @access protected
	 * @param mixed $query
	 * @return void
	 */
	protected function meta_query($query) {
		if (empty($query['meta']))
			return;

		$meta_queries=array();

		foreach ($query['meta'] as $meta) :
			$table='';
			$query_table=$this->set_db_table($query);

			// check we have what we need //
			if (empty($meta['field']) || empty($meta['value']))
				continue;

			// get table for query if passed //
			if (isset($meta['table']))
				$table=$this->set_db_table('', $meta['table']);

			// check tables aren't the same //
			if ($table==$query_table || empty($table)) :
				$meta_queries['where'][]=$meta['field']."=".$meta['value'];
			else :
				$table=$query_table;

				$meta_queries['join'][]="SELECT * FROM $table WHERE ".$meta['field']."=".$meta['value'];
			endif;
		endforeach;

		return $meta_queries;
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

/**
 * uci_results_pagination function.
 *
 * @access public
 * @param string $args (default: '')
 * @return void
 */
function uci_results_pagination($args='') {
	global $uci_results_query;

	$html=null;
	$pagenum_link = html_entity_decode( get_permalink() );
	$url_parts = explode( '?', $pagenum_link ); // -- this may be needed in the future if we have extra queries
	$total = isset( $uci_results_query->max_num_pages ) ? $uci_results_query->max_num_pages : 1;
  $current = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;

  $defaults=array(
		'base' => $pagenum_link,
		'total' => $total,
		'current' => $current,
    'prev_text' => __('&laquo; Previous'),
    'next_text' => __('Next &raquo;'),
	);
	$args=wp_parse_args($args, $defaults);

	// prev link //
	$prev_link=null;
	$prev_page=$args['current']-1;

	// only display if needed //
	if ($prev_page>0) :
		$prev_link.='<div class="prev-link">';

			if ($prev_page!=0)
				$prev_link.='<a href="'.$args['base'].$prev_page.'">'.$args['prev_text'].'</a>';

		$prev_link.='</div>';
	endif;

	// next link //
	$next_link=null;
	$next_page=$args['current']+1;

	// only display if we are not on last page //
	if ($next_page<=$args['total']) :
		$next_link.='<div class="next-link">';

			if ($next_page<=$args['total'])
				$next_link.='<a href="'.$args['base'].$next_page.'">'.$args['next_text'].'</a>';

		$next_link.='</div>';
	endif;

	$html.='<div class="uci-results-pagination">';
		$html.=$prev_link;
		$html.=$next_link;
	$html.='</div>';

	echo $html;
}

/**
 * uci_results_admin_pagination function.
 *
 * @access public
 * @param string $args (default: '')
 * @return void
 */
function uci_results_admin_pagination($args='') {
  global $wp, $uci_results_query;

	$html=null;
	$pagenum_link=add_query_arg( $_SERVER['QUERY_STRING'], '', admin_url( $wp->request ) );
	$url_parts = explode( '?', $pagenum_link );
	$pagenum_link = trailingslashit( $url_parts[0] );
	$total = isset( $uci_results_query->max_num_pages ) ? $uci_results_query->max_num_pages : 1;
	$current = isset($_GET['paged']) ? intval( $_GET['paged'] ) : 1;

  $defaults=array(
		'base' => $pagenum_link,
		'total' => $total,
		'current' => $current,
    'prev_text' => __('&laquo; Previous'),
    'next_text' => __('Next &raquo;'),
		'add_args' => array()
	);
	$args=wp_parse_args($args, $defaults);

	// Merge additional query vars found in the original URL into 'add_args' array. via - wp-includes/general-template.php
	if (isset($url_parts[1])) :
		// Find the query args of the requested URL.
		wp_parse_str( $url_parts[1], $url_query_args );

		$args['add_args'] = array_merge( $args['add_args'], urlencode_deep( $url_query_args ) );
	endif;

	// set our previous url //
	$prev_url_args=$args['add_args'];
	$prev_url_args['paged']=$args['current']-1;
	$prev_page=add_query_arg($prev_url_args, $args['base']);

	// prev link //
	$prev_link=null;

	// only display if needed //
	if ($prev_url_args['paged']>0) :
		$prev_link.='<div class="prev-link">';

			if ($prev_url_args['paged']!=0)
				$prev_link.='<a href="'.$prev_page.'">'.$args['prev_text'].'</a>';

		$prev_link.='</div>';
	endif;

	// set our next url //
	$next_url_args=$args['add_args'];
	$next_url_args['paged']=$args['current']+1;
	$next_page=add_query_arg($next_url_args, $args['base']);

	// next link //
	$next_link=null;

	// only display if we are not on last page //
	if ($next_url_args['paged']<=$args['total']) :
		$next_link.='<div class="next-link">';

			if ($next_url_args['paged']<=$args['total'])
				$next_link.='<a href="'.$next_page.'">'.$args['next_text'].'</a>';

		$next_link.='</div>';
	endif;

	$html.='<div class="uci-results-admin-pagination">';
		$html.=$prev_link;
		$html.=$next_link;
	$html.='</div>';

	echo $html;
}

/**
 * uci_results_get_stored_rider_rankings function.
 *
 * @access public
 * @return void
 */
function uci_results_get_stored_rider_rankings() {
	return get_option('uci_results_current_rankings', array());
}
?>