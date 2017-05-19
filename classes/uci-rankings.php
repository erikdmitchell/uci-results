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
		wp_enqueue_script('uci-rankings-script', UCI_RESULTS_ADMIN_URL.'js/uci-rankings.js', array('jquery'), '0.1.0');
		
		wp_enqueue_media();
    }

	/**
	 * ajax_process_csv_file function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_process_csv_file() {
		$this->process_csv_file($_POST['file'], $_POST['custom_date'], $_POST['discipline']);
		
		echo '<div class="success">CSV file processed and inserted into db.</div>';
		
		wp_die();
	}

	/**
	 * process_csv_file function.
	 * 
	 * @access public
	 * @param string $file (default: '')
	 * @param string $date (default: '')
	 * @param int $discipline (default: 0)
	 * @return void
	 */
	public function process_csv_file($file='', $date='', $discipline=0) {
		global $wpdb;
	
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
			
			if (isset($row['nation'])) :
				$country=$this->convert_country($row['nation']);			
			endif;
			
			$data[$key]['rank']=$rank_arr[0];
			$data[$key]['rider_id']=uci_results_add_rider($name, $country);
			$data[$key]['date']=$date;
			$data[$key]['name']=$name;
			$data[$key]['discipline']=$discipline;
		endforeach;

		$this->insert_rankings_into_db($data);
		
		// update our option so we know we have a ranking change //
		update_option('fc_uci_rankings_last_update', $date);
		$this->last_update=$date;
		
		return true;
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
	 * add_button function.
	 * 
	 * @access public
	 * @param bool $echo (default: true)
	 * @return void
	 */
	public function add_button($echo=true) {
		$html=null;
	
		$html.='<a class="button add-rider-rankings" href="">Add Rider Rankings</a>';
		
		if ($echo)
			echo $html;
			
		return $html;
	}
	
	/**
	 * rankings_list_dropdown function.
	 * 
	 * @access public
	 * @param string $args (default: '')
	 * @return void
	 */
	public function rankings_list_dropdown($args='') {
		$default_args=array(
			'echo' => true,
			'selected' => '',
		);
		$args=wp_parse_args($args, $default_args);
		$html='';
		$rankings_dates=$this->get_rankings(array(
			'group_by' => 'date',
			'fields' => 'date',
		));
		
		if (!$rankings_dates)
			return;
			
		$html.='<select name="fc_rankings_list_date">';
			$html.='<option value="0">Select Date</option>';
			
			foreach ($rankings_dates as $arr) :
				$html.='<option value="'.$arr->date.'" '.selected($args['selected'], $arr->date, false).'>'.date(get_option('date_format'), strtotime($arr->date)).'</option>';		
			endforeach;
			
		$html.='</select>';
		
		if ($args['echo'])
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
		if (!empty($date))
			$where[]="date = '$date'";
			
		// clean where var //
		if (!empty($where)) :
			$where='WHERE '.implode(' AND ', $where);
		else :
			$where='';
		endif;
		
		$db_results=$wpdb->get_results("
			SELECT $fields FROM $this->table_name
			$where
			$group_by			
			ORDER BY $order_by $order
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
	 * @param string $discipline (default: '')
	 * @return void
	 */
	public function get_rank($rider_id=0, $discipline='') {
		global $wpdb;
		
		$rank=$wpdb->get_row("SELECT rank, points, date, discipline FROM ".$this->table_name." WHERE rider_id = 1429 ORDER BY date ASC LIMIT 1");
		
		// render discipline
		$discipline=get_term_by('id', $rank->discipline, 'discipline');
		$rank->discipline=$discipline->name;
		
		return $rank;
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
?>