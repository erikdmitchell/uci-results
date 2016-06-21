<?php

class UCI_Results_Query {

	public $posts;

	public $query;

	public $query_vars;


	/**
	 * __construct function.
	 *
	 * @access public
	 * @param string $query (default: '')
	 * @return void
	 */
	public function __construct($query='') {
		if (!empty($query))
			$this->query($query);
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
			'order_by' => 'date',
			'order' => 'DESC',
			'class' => false,
			'season' => false,
			'nat' => false,
			'start_date' => false,
			'end_date' => false,
			'paged' => 1
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

		$where=$this->where_clause($q);
		$order=$this->order_clause($q);
		$limit=$this->set_limit($q);

		$this->query="SELECT * FROM {$wpdb->ucicurl_races} AS races	{$where} {$order}	{$limit}";

		//$this->get_posts();

echo '<pre>';
print_r($query);
print_r($this);
echo '</pre>';
	}

	public function get_posts() {
		global $wpdb;

		$posts=$wpdb->get_results($this->query);

		if ($this->query_vars['type']=='races')
			$posts=$this->races_clean_up($posts);

		$this->posts=$posts;
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
		$per_page=absint($q['per_page']);

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

}
?>