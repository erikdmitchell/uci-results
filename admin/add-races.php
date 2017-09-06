<?php
global $uci_results_add_races;

/**
 * UCIResultsAddRaces class.
 */
class UCIResultsAddRaces {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts_styles'));
		add_action('wp_ajax_add_race_to_db', array($this, 'ajax_add_race_to_db'));
		add_action('wp_ajax_process_csv_results', array($this, 'ajax_process_results_csv'));
		add_action('wp_ajax_csv_add_results', array($this, 'add_csv_results_to_race'));
	}
	
	/**
	 * admin_scripts_styles function.
	 * 
	 * @access public
	 * @return void
	 */
	public function admin_scripts_styles() {
		wp_enqueue_script('uci-results-add-races-admin-script', UCI_RESULTS_ADMIN_URL.'js/add-races.js', array('uci-results-admin'), '0.1.0', true);
	}

	public function build_race_code($args='') {
		$default=array(
			'event' => '',
			'date' => date('Y-m-d'),
		);
		$args=wp_parse_args($args, $default);

		$code_created=$this->check_for_race_already_created($args);

		if ($code_created)
			return $code_created;

		$code=$args['season'].'-'.$args['event']; // combine season and name
		$code=sanitize_title_with_dashes($code); // https://codex.wordpress.org/Function_Reference/sanitize_title_with_dashes

		return $code;
	}

	public function check_for_race_already_created($args='') {
		global $wpdb;

		$default=array(
			'date' => date('Y-m-d'),
			'nat' => '',
			'class' => '',
			'season' => ''
		);
		$args=wp_parse_args($args, $default);

		$races=get_posts(array(
			'post_type' => 'races',
			'tax_query' => array(
				array(
					'taxonomy' => 'country',
					'field' => 'name',
					'terms' => $args['nat']
				),
				array(
					'taxonomy' => 'race_class',
					'field' => 'name',
					'terms' => $args['class']
				),		
				array(
					'taxonomy' => 'season',
					'field' => 'name',
					'terms' => $args['season']
				)
			),
			'meta_query' => array(
				array(
					'key' => '_race_winner',
					'value' => '',
				),
				array(
					'key' => '_race_date',
					'value' => $args['date'],
				),
			)
		));

		if (count($races))
			return $races[0]->post_slug;

		// we need some sort of search to compare names

		return false;
	}

	public function reformat_date($date) {
		$date = htmlentities($date, null, 'utf-8');
		$date = str_replace("&nbsp;", "", $date);

		// if we have a '-' then it's a multi day so we return base date //
		if (strpos($date, '-') !== false)
			return $date;

		// make "readable" to php date //
		$day=substr($date, 0, 2);
		$month=date('m', strtotime(substr($date, 2, 3)));
		$year=substr($date, 5);

		$date="$year-$month-$day";

		return $date;
	}

	/**
	 * check_for_dups function.
	 *
	 * @access public
	 * @param mixed $code
	 * @return void
	 */
	public function check_for_dups($code='') {
		$race=get_page_by_path($code, OBJECT, 'races');

		// we have race, but make sure it's not empty ie we preloaded the race //
		if ($race !== null) :
			if (!$this->has_results($race->ID)) :
				return true;
			else :
				return false;
			endif;
		endif;

		return false;
	}
	
	public function has_results($id=0) {
		if (get_post_meta($id, '_race_results', true) == 1)
			return true;
			
		return false;
	}
		
	protected function update_to_twitter() {
		if (uci_results_post_results_to_twitter()) :
			$url=get_permalink($uci_results_pages['single_race']).$data['code'];
	
			// use twitter if we have it //
			$twitter=uci_get_race_twitter($race_id);
	
			if (!empty($twitter))
				$twitter='@'.$twitter;
	
			$status=$race_data->winner.' wins '.$race_data->event.' ('.$race_data->class.') '.$twitter.' '.$url;
			$uci_results_twitter->update_status($status);
		endif;		
	}

	/**
	 * get_race_week function.
	 * 
	 * @access public
	 * @param string $date (default: '')
	 * @param string $season (default: '')
	 * @return void
	 */
	public function get_race_week($date='', $season='') {
		global $uci_results_seasons;
		
		$season_weeks=$uci_results_seasons->get_season_weeks($season);

		if (empty($season_weeks))
			return 0;

		return $this->get_week_of_date($date, $season_weeks);
	}

	/**
	 * get_week_of_date function.
	 *
	 * @access public
	 * @param string $date (default: '')
	 * @param array $weeks (default:'')
	 * @return void
	 */
	public function get_week_of_date($date='', $weeks='') {
		if (empty($weeks))
			return;
			
		// cycle through weeks and if date falls in there, return the week //
		foreach ($weeks as $week) :		
			$week_start=strtotime($week->start);
			$week_end=strtotime($week->end);
			$date_raw=strtotime($date);

			if ($date_raw>=$week_start && $date_raw<=$week_end)
				return $week->week;
				
		endforeach;

		return 0;
	}

	/**
	 * add_race_results_to_db function.
	 * 
	 * @access public
	 * @param string $race (default: '')
	 * @param string $results (default: '')
	 * @return void
	 */
	public function add_race_results_to_db($race='', $results='') {
		$updated_results=array();
		
		if (empty($race))
			return false;
			
		if (empty($results))
			return false;

		// insert rider results //
		foreach ($results as $type => $result_list) :			
			foreach ($result_list as $type_results_list) :
				$updated_results[]=$this->insert_rider_result($type_results_list, $race, array('type' => $type));
			endforeach;
		endforeach;

		// update race results //
		update_post_meta($race->race_id, '_races_results', 1);

		do_action('uci_results_updated_results_'.$race->discipline, $race, $updated_results, $results);
		
		return $results;
	}
	
	/**
	 * insert_rider_result function.
	 * 
	 * @access protected
	 * @param string $result (default: '')
	 * @param string $race (default: '')
	 * @param string $args (default: '')
	 * @return void
	 */
	protected function insert_rider_result($result='', $race='', $args='') {
		$updated_results=array();
		$meta_values=array();
		$default_args=array(
			'insert' => true,
		);
		$args=wp_parse_args($args, $default_args);
		
		if (is_array($result))
			$result=array_to_object($result);

		// essentially converts our object to an array //			
		foreach ($result as $key => $value) :
			$_key=$args['type'].'_'.$key;
			$meta_values[$_key]=$value;
		endforeach;		

		// filter value //
		$meta_values=apply_filters('uci_results_insert_race_result_'.$race->discipline, $meta_values, $race, $args);
	
		// get rider id //
		if (isset($result->nat)) :
			$rider_nat=$result->nat;
		else :
			$rider_nat='';
		endif;
		
		$rider_id=$this->get_rider_id($result->name, $rider_nat, $args['insert']);

		// bail if no id //
		if (empty($rider_id) || !$rider_id)
			return;
			
		// bail on no meta values //
		if (empty($meta_values) || $meta_values=='')
			return;
			
		$updated_results=$this->rider_meta_values_to_results($meta_values, $rider_id);

		// input meta values //
		foreach ($meta_values as $meta_key => $meta_value) :
			$mk="_rider_".$rider_id."_".$meta_key;

			update_post_meta($race->race_id, $mk, $meta_value);
		endforeach;
		
		return $updated_results;
	}

	/**
	 * get_rider_id function.
	 * 
	 * @access public
	 * @param string $rider_name (default: '')
	 * @param string $rider_country (default: '')
	 * @param bool $insert (default: true)
	 * @return void
	 */
	public function get_rider_id($rider_name='', $rider_country='', $insert=true) {
		if (empty($rider_name))
			return 0;
			
		$rider=get_page_by_title($rider_name, OBJECT, 'riders');

		// check if we have a rider id, otherwise create one //
		if ($rider===null || empty($rider->ID)) :
			if ($insert) :
				$rider_insert=array(
					'post_title' => $rider_name,
					'post_content' => '',
					'post_status' => 'publish',	
					'post_type' => 'riders',
					'post_name' => sanitize_title_with_dashes($rider_name)
				);
				$rider_id=wp_insert_post($rider_insert);
				
				wp_set_object_terms($rider_id, $rider_country, 'country', false);
			else :
				$rider_id=0;
			endif;
		else :
			$rider_id=$rider->ID;
		endif;
		
		return $rider_id;			
	}
	
	public function rider_meta_values_to_results($meta_values=array(), $rider_id=0) {
		$clean_arr=array();
		
		foreach ($meta_values as $key => $meta_value) :
			if (strpos($key, 'result')!==false) :
				$clean_key=str_replace('result_', '', $key);
				
				$clean_arr[$clean_key]=$meta_value;
			endif;
		endforeach;
		
		$clean_arr['rider_id']=$rider_id;
		
		return $clean_arr;
	}

	/**
	 * ajax_process_results_csv function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_process_results_csv() {
		$form=array();
	
		foreach ($_POST['form'] as $arr) :
			$form[$arr['name']]=$arr['value'];
		endforeach;

		$data=$this->upload_csv_results($form);
	
		echo $this->csv_file_display($data);
	
		wp_die();
	}

	/**
	 * upload_csv_results function.
	 * 
	 * @access protected
	 * @param array $form (default: array())
	 * @return void
	 */
	protected function upload_csv_results($form=array()) {
		if (!isset($form['uci_results']) || !wp_verify_nonce($form['uci_results'], 'add-race-csv'))
			return false;
			
		if (empty($form['race_id']))
			$form['race_id']=$form['race_search_id'];
		
		$data=$this->process_csv_file($form['file']);
		$data['race_id']=$form['race_id'];	
		
		return $data;
	}
	
	/**
	 * process_csv_file function.
	 * 
	 * @access protected
	 * @param string $file (default: '')
	 * @return void
	 */
	protected function process_csv_file($file='') {
		global $wpdb;
		
		if (empty($file) || $file=='')
			return false;
		
		ini_set('auto_detect_line_endings',TRUE); // added for issues with MAC
		
		$data=array();
		$file=wp_remote_fopen($file);
    	$file=str_replace("\r\n", "\n", trim($file));
    	$rows=explode("\n", $file);
 
		// turn into easier to digest array //
    	foreach ($rows as $row => $cols) :
			$cols=str_getcsv($cols, ',');
			
			$data[]=$cols;
		endforeach;
		
		if (empty($data))
			return false;
			
		$header_row=array_shift($data);
		$header_row=array_map('sanitize_key', $header_row);		
		
		// builds out a more cleaner arr //
		foreach ($data as $key => $row) :
			$arr=array();
			
			foreach ($row as $k => $v) :
				$arr[$header_row[$k]]=$v;	
			endforeach;
			
			$data[$key]=$arr;
		endforeach;
		
		$clean_arr=array(
			'header' => $header_row,
			'rows' => $data	
		);

		return $clean_arr;		
	}
	
	/**
	 * csv_file_display function.
	 * 
	 * @access public
	 * @param array $arr (default: array())
	 * @return void
	 */
	public function csv_file_display($arr=array()) {
		if (empty($arr))
			return;
			
		$html='';
		
		$html.='<div class="race-info">';
			$html.='<h4>'.get_the_title($arr['race_id']).' <span class="race-date">'.get_post_meta($arr['race_id'], '_race_start', true).'</span></h4>';
		$html.='</div>';		
		
		$html.='<table class="form-table">';
		
		if (isset($arr['header'])) :
			$html.='<tr>';
			
				foreach ($arr['header'] as $head) :
					$html.='<th>'.$head.'</th>';
				endforeach;
			
			$html.='</tr>';
		endif;
		
		foreach ($arr['rows'] as $row_counter => $row) :
			$html.='<tr>';
				
				foreach ($row as $key => $col) :
					$html.='<td><input type="text" name="race[results][result]['.$row_counter.']['.$key.']" class="'.$key.'" value="'.$col.'" /></td>';
				endforeach;
				
			$html.='</tr>';
		endforeach;

		$html.='</table>';
		
		return $html;
	}
	
	/**
	 * add_csv_results_to_race function.
	 * 
	 * @access public
	 * @return void
	 */
	public function add_csv_results_to_race() {
		$this->deep_parse_str($_POST['data'], $formdata);

		if (!isset($formdata['uci_results']) || !wp_verify_nonce($formdata['uci_results'], 'add-csv-data'))
			return;
		
		$results=array_to_object($formdata['race']['results']);
		$race=get_post($formdata['race']['race_id']);
		$race->race_id=$race->ID;
		$race->discipline=uci_get_race_discipline($race->ID);	
		$this->add_race_results_to_db($race, $results);
	
		echo admin_url('post.php?post='.$formdata['race']['race_id'].'&action=edit');
		
		wp_die();
	}

	/**
	 * deep_parse_str function.
	 * 
	 * https://gist.github.com/rubo77/6821632
	 *
	 * @access public
	 * @param mixed $string
	 * @param mixed &$result
	 * @return void
	 */
	public function deep_parse_str($string, &$result) {
		if($string==='') return false;
		$result = array();
		// find the pairs "name=value"
		$pairs = explode('&', $string);
		foreach ($pairs as $pair) {
			// use the original parse_str() on each element
			parse_str($pair, $params);
			$k=key($params);
			if(!isset($result[$k])) $result+=$params;
			else $result[$k] = $this->array_merge_recursive_distinct($result[$k], $params[$k]);
		}
		return true;
	}

	/**
	 * array_merge_recursive_distinct function.
	 * 
	 * better recursive array merge function 
	 *
	 * @access public
	 * @param array &$array1
	 * @param array &$array2
	 * @return void
	 */
	public function array_merge_recursive_distinct ( array &$array1, array &$array2 ){
		$merged = $array1;
		foreach ( $array2 as $key => &$value ) {
			if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ){
			    $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
			} else {
			    $merged [$key] = $value;
			}
		}
		return $merged;
	}

}

$uci_results_add_races=new UCIResultsAddRaces();
?>