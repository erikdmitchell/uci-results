<?php
global $uci_rankings;

class UCIRankings {
	
	public $last_update;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $wpdb;
	
		$this->table_name=$wpdb->prefix.'uci_results_uci_rankings';
		$this->primary_key='id';
		$this->version='1.0.0';
		$this->last_update=get_option('uci_rankings_last_update', 0);
		
		add_action('wp_ajax_uci_add_rider_rankings', array($this, 'ajax_process_csv_file'));
		
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts_styles'));
	}

    /**
     * admin_scripts_styles function.
     * 
     * @access public
     * @param mixed $hook
     * @return void
     */
    public function admin_scripts_styles($hook) {
	    global $wp_scripts; 
	    
	    wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('uci-rankings-script', UCI_RESULTS_ADMIN_URL.'js/uci-rankings.js', array('jquery-ui-datepicker'), '0.2.0');
		
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/'.$wp_scripts->registered['jquery-ui-core']->ver.'/themes/ui-lightness/jquery-ui.min.css');
		
		wp_enqueue_media();
    }

	/**
	 * ajax_process_csv_file function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_process_csv_file() {
		$args=array();
		parse_str($_POST['form'], $args);

		$this->process_csv_file($args);

		echo '<div class="success">CSV file processed and inserted into db.</div>';
		
		wp_die();
	}

	/**
	 * process_csv_file function.
	 * 
	 * @access public
	 * @param string $args (default: '')
	 * @return void
	 */
	public function process_csv_file($args='') {
		global $wpdb;
	
		$default_args=array(
			'file' => '', 
			'date' => '', 
			'discipline' => 0,
			'clean_names' => 0,
		);
		$args=wp_parse_args($args, $default_args);
	
		extract($args);
	
		if (empty($file) || $file=='')
			return false;
			
		if (empty($date))
			$date=date('Y-m-d');
	
		ini_set('auto_detect_line_endings',TRUE); // added for issues with MAC
	
		$data=array();
		$row_counter=1;
		$headers=array();
		$file=wp_remote_fopen($file);

	    $file = str_replace( "\r\n", "\n", trim( $file ) );
	    $rows = explode( "\n", $file );

		// process csv rows //
	    foreach ($rows as $row => $cols) :
	    	$cols = str_getcsv( $cols, ',' );

				if ($row_counter==1) :
					$headers=array_map('sanitize_title_with_dashes', $cols);
				else :
					$data[]=array_combine($headers, $cols);
				endif;
	
				$row_counter++;
	    endforeach;
	    	
		// clean rank, add rider id via name and add date //
		foreach ($data as $key => $row) :		
			$rank_arr=explode(' ', $row['rank']);
			$name=trim(str_replace('*', '', $row['name']));
			
			// clean name //
			if ($clean_names)
				$name=$this->clean_names($name);
			
			// nation check //
			$found_nation_key=false;
			
			foreach ($row as $k => $v) :		
				if (strpos($k, 'nation')!==false) :
					$found_nation_key=$k;
					break;
				endif;
			endforeach;

			if ($found_nation_key) :			
				$country=$this->convert_country($row[$found_nation_key]);			
			endif;
			// end nation check //
			
			$data[$key]['rank']=$rank_arr[0];
			$data[$key]['rider_id']=uci_results_add_rider($name, $country);
			$data[$key]['date']=$date;
			$data[$key]['name']=$name;
			$data[$key]['discipline']=$discipline;
		endforeach;

		$this->insert_rankings_into_db($data);
		
		// update our option so we know we have a ranking change THIS NEEDS TO CHANGE TO INCLUDE DISCIPLINE //
		$update_date=$date.' '.date('H:i:s');
		update_option('uci_rankings_last_update', $update_date);
		$this->last_update=$date;
		
		return true;
	}
	
	/**
	 * clean_names function.
	 * 
	 * @access protected
	 * @param string $name (default: '')
	 * @return void
	 */
	protected function clean_names($name='') {
		if (empty($name))
			return '';
		
		$name_arr=explode(' ', $name);
		$last_el=array_pop($name_arr);
		array_unshift($name_arr, $last_el);
		
		return implode(' ', $name_arr);
	}
	
	/**
	 * convert_country function.
	 * 
	 * @access protected
	 * @param string $country (default: '')
	 * @return void
	 */
	protected function convert_country($country='') {
		global $flags_countries_arr;

		$country_code='';
		
		if (strtolower($country)=='great britain') :
			return 'GBR';
		endif;

		foreach ($flags_countries_arr as $code => $arr) :
			if (strtolower($arr[0])==strtolower($country)) :
				$country_code=$arr[2];
				break;
			endif;		
		endforeach;

		return $country_code;
	}

	/**
	 * insert_rankings_into_db function.
	 * 
	 * @access protected
	 * @param array $data (default: array())
	 * @return void
	 */
	protected function insert_rankings_into_db($data=array()) {
		global $wpdb;
		
		$table_columns=$this->get_columns();
		$data_clean=$this->data_table_cols_match($data, $table_columns);

		foreach ($data_clean as $arr) :		
			// skip if no name //
			if ($arr['name']=='')
				continue;
				
			// check if this entry exists and pull ID so we can update //
			$id=$wpdb->get_var("SELECT id FROM ".$this->table_name." WHERE name = \"".$arr['name']."\" AND date = '".$arr['date']."'");
			
			if ($id !== null) :
				$wpdb->update($this->table_name, $arr, array('id' => $id));
			else :
				$wpdb->insert($this->table_name, $arr);
			endif;
		endforeach;
		
		return;
	}
	
	/**
	 * data_table_cols_match function.
	 * 
	 * @access protected
	 * @param string $data (default: '')
	 * @param string $columns (default: '')
	 * @return void
	 */
	protected function data_table_cols_match($data='', $columns='') {
		if (empty($data) || empty($columns))
			return $data;
			
		$data_clean=array();
		
		foreach ($data as $arr) :
			$new_arr=array();
				
			foreach ($arr as $key => $value) :
				if (in_array($key, $columns)) :
					$new_arr[$key]=$arr[$key];
				endif;
			endforeach;
			
			$data_clean[]=$new_arr;
		endforeach;

		return $data_clean;
	}

	/**
	 * file_input function.
	 * 
	 * @access public
	 * @param bool $echo (default: true)
	 * @return void
	 */
	public function file_input($echo=true) {
		$html=null;
	
		$html.='<input type="text" id="add-rider-rankings-input" name="file" value="" class="regular-text" /> <a class="button add-rider-rankings" href="">Add File</a>';
		
		if ($echo)
			echo $html;
			
		return $html;
	}

	/**
	 * process_button function.
	 * 
	 * @access public
	 * @param string $text (default: 'Insert Into DB')
	 * @param bool $echo (default: true)
	 * @return void
	 */
	public function process_button($text='Insert Into DB', $echo=true) {
		$html=null;
	
		$html.='<p><a class="button button-primary" id="insert-rider-rankings" href="">'.$text.'</a></p>';
		
		if ($echo)
			echo $html;
			
		return $html;
	}

	/**
	 * get_rankings function.
	 * 
	 * @access public
	 * @param string $args (default: '')
	 * @return void
	 */
	public function get_rankings($args='') {
		global $wpdb;
		
		$default_args=array(
			'fields' => 'all',
			'order' => 'ASC',
			'order_by' => 'date',
			'group_by' => '',
			'date' => '',
			'discipline' => 'road',
			'limit' => -1,
		);
		$args=wp_parse_args($args, $default_args);
		$where=array();
		
		extract($args);
		
		// setup group by //
		if (!empty($group_by)) :
			$group_by="GROUP BY $group_by";
		endif;
		
		// setup fields //
		if ($fields=='all')
			$fields='*';
			
		// setup where vars //	
		if (!empty($discipline)) :
			if (!is_numeric($discipline)) :
				$term=get_term_by('slug', $discipline, 'discipline');
				$discipline=$term->term_id;
			endif;
			
			$where[]="discipline = '$discipline'";
		endif;
		
		if (!empty($date)) :
			$where[]="date = '$date'";
		else :
			$where[]="date = '".$wpdb->get_var("SELECT date FROM ".$this->table_name." WHERE discipline = $discipline ORDER BY date DESC LIMIT 1")."'";
		endif;
			
		// clean where var //
		if (!empty($where)) :
			$where='WHERE '.implode(' AND ', $where);
		else :
			$where='';
		endif;
		
		// setup limit //
		if ($limit >= 0) :
			$limit=" LIMIT $limit";
		else :
			$limit='';
		endif;

		$db_results=$wpdb->get_results("
			SELECT $fields FROM $this->table_name
			$where
			$group_by			
			ORDER BY $order_by $order
			$limit
		");
		
		if (is_wp_error($db_results))
			return false;
			
		return $db_results;
	}
	
	/**
	 * is_ranks_updated function.
	 * 
	 * @access public
	 * @param string $date (default: '')
	 * @return void
	 */
	public function is_ranks_updated($date='') {
		if (empty($date))
			$date=date('Y-m-d');
			
		if ($date<=$this->last_updated)
			return true;
		
		return false;
	}
	
	/**
	 * get_rank function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @param string $discipline (default: 0)
	 * @return void
	 */
	public function get_rank($rider_id=0, $discipline=0) {
		global $wpdb;
		
		if (!is_integer($discipline)) :
			$_discipline=get_term_by('slug', $discipline, 'discipline');
	
			$discipline=$_discipline->term_id;
		endif;
		
		$rank=$wpdb->get_row("SELECT rank, points, date, discipline FROM ".$this->table_name." WHERE rider_id = $rider_id AND discipline = $discipline ORDER BY date ASC LIMIT 1");
		
		if ($rank===null) :
			$rank=new stdClass();
			
			$rank->rank=0;
			$rank->points=0;
			$rank->date='';
			$rank->discipline=$discipline;						
		endif;
		
		return $rank;
	}
	
	/**
	 * max_rank function.
	 * 
	 * @access public
	 * @param string $date (default: '')
	 * @param string $discipline (default: '')
	 * @return void
	 */
	public function max_rank($date='', $discipline='') {
		global $wpdb;
		
		return $wpdb->get_var("SELECT MAX(rank) FROM ".$this->table_name." ORDER BY date ASC");
	}	
	
	/**
	 * get_rankings_dates function.
	 * 
	 * @access public
	 * @param int $discipline (default: 0)
	 * @return void
	 */
	public function get_rankings_dates($discipline=0) {
		global $wpdb;
		
		$select='date, t.name AS discipline';
		$join=" INNER JOIN ".$wpdb->terms." t ON ".$this->table_name.".discipline = t.term_id";
		$where='';
		
		if ($discipline) :		
			if (!is_numeric($discipline)) :
				$term=get_term_by('slug', $discipline, 'discipline');
				$discipline=$term->term_id;
			endif;
		
			$select='date';
			$join='';
			$where="WHERE discipline = $discipline";
		endif;

		$dates=$wpdb->get_results("SELECT DISTINCT $select FROM ".$this->table_name." $join $where");
		
		return $dates;
	}
	
	/**
	 * disciplines function.
	 * 
	 * @access public
	 * @return void
	 */
/*
	public function disciplines() {
		global $wpdb;
		
		$results=$wpdb->get_results("SELECT DISTINCT discipline AS id, t.name AS discipline FROM ".$this->table_name." INNER JOIN ".$wpdb->terms." t ON ".$this->table_name.".discipline = t.term_id");
		
		return $results;
	}
*/

	/**
	 * recent_date function.
	 * 
	 * @access public
	 * @param int $discipline (default: 0)
	 * @return void
	 */
	public function recent_date($discipline=0) {
		global $wpdb;
		
		if (!is_numeric($discipline)) :
			$term=get_term_by('slug', $discipline, 'discipline');
			$discipline=$term->term_id;
		endif;
		
		return $wpdb->get_var("SELECT date FROM ".$this->table_name." WHERE discipline = $discipline ORDER BY date DESC LIMIT 1");
	}

	/**
	 * get_columns function.
	 * 
	 * @access public
	 * @return void
	 */
	public function get_columns() {
		global $wpdb;
		
		return $wpdb->get_col("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$this->table_name."'");
	}
}

$uci_rankings = new UCIRankings();

/**
 * uci_rankings_last_update function.
 * 
 * @access public
 * @return void
 */
function uci_rankings_last_update() {
	global $uci_rankings;
	
	return $uci_rankings->last_update;
}	
?>