<?php
class UCIResultsMigration100 {
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $wpdb;
		
		add_action('wp_ajax_migrate_series', array($this, 'ajax_migrate_series'));
		add_action('wp_ajax_migrate_related_races', array($this, 'ajax_migrate_related_races'));
		add_action('wp_ajax_migrate_riders', array($this, 'ajax_migrate_riders'));
		add_action('wp_ajax_migrate_races', array($this, 'ajax_migrate_races'));
		add_action('wp_ajax_update_series_overall_table', array($this, 'ajax_update_series_overall_table'));
		add_action('wp_ajax_update_rider_rankings_table', array($this, 'ajax_update_rider_rankings_table'));
		add_action('wp_ajax_run_clean_up', array($this, 'ajax_run_clean_up'));
		
		$wpdb->uci_results_races=$wpdb->prefix.'uci_curl_races';
		$wpdb->uci_results_results=$wpdb->prefix.'uci_curl_results';
		$wpdb->uci_results_riders=$wpdb->prefix.'uci_curl_riders';
		$wpdb->uci_results_rider_rankings=$wpdb->prefix.'uci_curl_rider_rankings';
		$wpdb->uci_results_related_races=$wpdb->prefix.'uci_curl_related_races';
		$wpdb->uci_results_series=$wpdb->prefix.'uci_curl_series';
		$wpdb->uci_results_series_overall=$wpdb->prefix.'uci_results_series_overall';									
	}

	/**
	 * ajax_migrate_series function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_migrate_series() {
		$this->migrate_series();
		
		echo json_encode(array(
			'step' => 1,
			'success' => true,
			'percent' => 10
		));
		
		wp_die();
	}
	
	/**
	 * ajax_migrate_related_races function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_migrate_related_races() {
		$this->migrate_related_races();
		
		echo json_encode(array(
			'step' => 2,
			'success' => true,
			'percent' => 20
		));
		
		wp_die();
	}
	
	/**
	 * ajax_migrate_riders function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_migrate_riders() {
		$this->migrate_riders();
		
		echo json_encode(array(
			'step' => 3,
			'success' => true,
			'percent' => 55
		));
		
		wp_die();
	}
	
	/**
	 * ajax_migrate_races function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_migrate_races() {
		$this->migrate_races();
		
		echo json_encode(array(
			'step' => 4,
			'success' => true,
			'percent' => 70
		));
		
		wp_die();
	}
	
	/**
	 * ajax_update_series_overall_table function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_update_series_overall_table() {
		$this->update_series_overall_table();
		
		echo json_encode(array(
			'step' => 5,
			'success' => true,
			'percent' => 80
		));
		
		wp_die();
	}
	
	/**
	 * ajax_update_rider_rankings_table function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_update_rider_rankings_table() {
		$this->update_rider_rankings_table();
		
		echo json_encode(array(
			'step' => 6,
			'success' => true,
			'percent' => 90
		));
		
		wp_die();
	}
	
	/**
	 * ajax_run_clean_up function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_run_clean_up() {
		global $wpdb;
		
		// remove tables //
		$wpdb->query("DROP TABLE IF EXISTS $wpdb->uci_results_races, $wpdb->uci_results_results, $wpdb->uci_results_riders, $wpdb->uci_results_series;");
		
		// remove race ids col from related races //
		$wpdb->query("ALTER TABLE $wpdb->uci_results_related_races DROP COLUMN race_ids");
		
		// convert years to slugs //
		$this->convert_years_to_slugs();
		
		$this->update_version();
		
		update_option('ucicurl_db_version', '1.0.0');
		
		echo json_encode(array(
			'step' => 7,
			'success' => true,
			'percent' => 100
		));
		
		wp_die();
	}
	
	/**
	 * convert_years_to_slugs function.
	 * 
	 * @access protected
	 * @return void
	 */
	protected function convert_years_to_slugs() {
		global $wpdb;
		
		$wpdb->query(
			$wpdb->prepare( 
				"UPDATE $wpdb->uci_results_rider_rankings SET season = REPLACE(season, '/', '')"
			)
		);
		
		$wpdb->query(
			$wpdb->prepare( 
				"UPDATE $wpdb->uci_results_series_overall SET season = REPLACE(season, '/', '')"
			)
		);
		
		return;
	}

	/**
	 * migrate_series function.
	 * 
	 * @access public
	 * @return void
	 */
	public function migrate_series() {
		global $wpdb;
		
		$db_series=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_series");
		
		if (!count($db_series))
			return;
			
		foreach ($db_series as $series)	:
			if (!term_exists($series->name, 'series')) :	
				$inserted=wp_insert_term($series->name, 'series');		
			endif;
		endforeach;
		
		return true;
	}

	/**
	 * migrate_related_races function.
	 * 
	 * @access public
	 * @return void
	 */
	public function migrate_related_races() {
		global $wpdb;
		
		$db_related_races=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_related_races");
	
		if (!uci_results_column_exists($wpdb->uci_results_related_races, 'race_id'))
			$wpdb->query("ALTER TABLE $wpdb->uci_results_related_races ADD COLUMN race_id bigint(20) NOT NULL DEFAULT '0'"); 

		if (!uci_results_column_exists($wpdb->uci_results_related_races, 'related_race_id'))
			$wpdb->query("ALTER TABLE $wpdb->uci_results_related_races ADD COLUMN related_race_id bigint(20) NOT NULL DEFAULT '0'");
	
		foreach ($db_related_races as $related_race_row) :
			$related_race_id=$related_race_row->id;
			$related_races=explode(',', $related_race_row->race_ids);
	
			foreach ($related_races as $race_id) :
				$insert=array(
					'race_id' => $race_id,
					'related_race_id' => $related_race_id,
				);		
				$wpdb->insert($wpdb->uci_results_related_races, $insert);
			endforeach;		
		endforeach;
		
		// remove old entries //
		$wpdb->delete($wpdb->uci_results_related_races, array('race_id' => 0));
		
		// remove duplicates of new entries //
		$wpdb->query("
			DELETE FROM $wpdb->uci_results_related_races USING $wpdb->uci_results_related_races, $wpdb->uci_results_related_races rr1
			WHERE $wpdb->uci_results_related_races.id > rr1.id AND $wpdb->uci_results_related_races.race_id = rr1.race_id AND $wpdb->uci_results_related_races.related_race_id = rr1.related_race_id
		");
		
		return true;
	}

	/**
	 * migrate_riders function.
	 * 
	 * @access public
	 * @return void
	 */
	public function migrate_riders() {
		global $wpdb;
		
		$db_riders=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_riders");
	
		foreach ($db_riders as $db_rider) :
			$rider=get_page_by_path(trim($db_rider->slug), OBJECT, 'riders');
	
			if ($rider === null) :			
				$arr=array(
					'post_title' => trim($db_rider->name),
					'post_content' => '',
					'post_status' => 'publish',	
					'post_type' => 'riders',
					'post_name' => trim($db_rider->slug),
				);
				
				$rider_id=wp_insert_post($arr);
			
				if (!is_wp_error($rider_id)) :
					add_post_meta($rider_id, '_rider_twitter', $db_rider->twitter);
					wp_set_object_terms($rider_id, $db_rider->nat, 'country', false);
				endif;				
			endif;
		endforeach;
		
		return true;
	}

	/**
	 * migrate_races function.
	 * 
	 * @access public
	 * @return void
	 */
	public function migrate_races() {
		global $wpdb;
		
		$db_races=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_races");
	
		if (!count($db_races))
			return;
			
		foreach ($db_races as $db_race) :
			$race=get_page_by_path($db_race->code, OBJECT, 'races');
			$old_id=$db_race->id;
			
			// race does not exist - add it //
			if ($race === null) :				
				$series=$this->convert_series($db_race->series_id);
			
				$race_data=array(
					'post_title' => $db_race->event,
					'post_content' => '',
					'post_status' => 'publish',	
					'post_type' => 'races',
					'post_name' => $db_race->code,		
				);
	
				$post_id=wp_insert_post($race_data);
				
				// check for error //
				if (is_wp_error($post_id))
					return false;
	
				$this->update_race_terms($post_id, $db_race, $series); // update taxonomies //
				$this->update_race_details($post_id, $db_race); // update meta //
			else :
			 	$post_id=$race->ID;			 	
			endif;		

			// if no results, try and add //
			if (!uci_race_has_results($post_id))
				$this->migrate_results($post_id, $old_id);
			
			// if not exits - convert related race id //
			if (!get_post_meta($post_id, '_race_related', true))
				$this->convert_related_race_id($db_race->id, $post_id);
				
		endforeach;
		
		return true;
	}
	
	/**
	 * update_race_details function.
	 * 
	 * @access protected
	 * @param int $race_id (default: 0)
	 * @param string $race_data (default: '')
	 * @return void
	 */
	protected function update_race_details($race_id=0, $race_data='') {
		update_post_meta($race_id, '_race_date', $race_data->date);
		update_post_meta($race_id, '_race_winner', $race_data->winner);
		update_post_meta($race_id, '_race_week', $race_data->week);
		update_post_meta($race_id, '_race_link', $race_data->link);	
		update_post_meta($race_id, '_race_twitter', $race_data->twitter);		
	}
	
	/**
	 * update_race_terms function.
	 * 
	 * @access protected
	 * @param int $race_id (default: 0)
	 * @param string $race_data (default: '')
	 * @param string $series (default: '')
	 * @return void
	 */
	protected function update_race_terms($race_id=0, $race_data='', $series='') {
		wp_set_object_terms($race_id, $race_data->nat, 'country', false);
		wp_set_object_terms($race_id, $race_data->class, 'race_class', false);
		wp_set_object_terms($race_id, 'cyclocross', 'race_class', true);
		wp_set_object_terms($race_id, 'cyclocross', 'discipline', true);
		wp_set_object_terms($race_id, $race_data->season, 'season', false);
		wp_set_object_terms($race_id, 'cyclocross', 'season', true);		
		wp_set_object_terms($race_id, $series, 'series', false);
	}
	
	/**
	 * migrate_results function.
	 * 
	 * @access public
	 * @param int $post_id (default: 0)
	 * @param int $old_id (default: 0)
	 * @return void
	 */
	public function migrate_results($post_id=0, $old_id=0) {
		global $wpdb;
		
		if (!$post_id || !$old_id)
			return false;
		
		$race_results=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_results WHERE race_id = $old_id");
		
		if (!count($race_results))
			return;
				
		foreach ($race_results as $result) :
			$rider=get_page_by_title($result->name, OBJECT, 'riders');
	
			// check if we have a rider id, otherwise create one //
			if ($rider===null || empty($rider->ID)) :
				$rider_insert=array(
					'post_title' => $result->name,
					'post_content' => '',
					'post_status' => 'publish',	
					'post_type' => 'riders',
					'post_name' => sanitize_title_with_dashes($result->name)
				);
				$rider_id=wp_insert_post($rider_insert);
				wp_set_object_terms($rider_id, $result->nat, 'country', false);
			else :
				$rider_id=$rider->ID;
			endif;
	
			if (!isset($result->par) || empty($result->par) || is_null($result->par)) :
				$par=0;
			else :
				$par=$result->par;
			endif;
	
			if (!isset($result->pcr) || empty($result->pcr) || is_null($result->pcr)) :
				$pcr=0;
			else :
				$pcr=$result->pcr;
			endif;
	
			$meta_value=array(
				'place' => $result->place,
				'name' => $result->name,
				'nat' => $result->nat,
				'age' => $result->age,
				'result' => $result->result,
				'par' => $par,
				'pcr' => $pcr,
			);					
			update_post_meta($post_id, "_rider_$rider_id", $meta_value);
		endforeach;
		
		return true;
	}

	/**
	 * convert_series function.
	 * 
	 * @access public
	 * @param int $old_id (default: 0)
	 * @return void
	 */
	public function convert_series($old_id=0) {
		global $wpdb;
		
		if (!$old_id)
			return;
			
		$series=$wpdb->get_var("SELECT name FROM $wpdb->uci_results_series WHERE id = $old_id");
		
		return $series;
	}

	/**
	 * update_series_overall_table function.
	 * 
	 * @access public
	 * @return void
	 */
	public function update_series_overall_table() {
		global $wpdb;
		
		$db_series_overall=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_series_overall");
		
		foreach ($db_series_overall as $db_row) :
			// get "new" rider id //
			$name=$this->get_rider_name_from_old_id($db_row->rider_id);		
			$rider=get_page_by_title($name, OBJECT, 'riders');
			
			if ($rider===null) :
				$rider_id=0;
			else :
				$rider_id=$rider->ID;
			endif;
			
			// get "new" series id //
			$name=$this->get_series_name_from_old_id($db_row->series_id);		
			$term=get_term_by('name', $name, 'series');
	
			if ($term===null) :
				$series_id=0;
			else :
				$series_id=$term->term_id;
			endif;
			
			$wpdb->update($wpdb->uci_results_series_overall, array('rider_id' => $rider_id, 'series_id' => $series_id), array('id' => $db_row->id));	
		endforeach;
		
		return true;
	}

	/**
	 * get_rider_name_from_old_id function.
	 * 
	 * @access protected
	 * @param int $id (default: 0)
	 * @return void
	 */
	protected function get_rider_name_from_old_id($id=0) {
		global $wpdb;
		
		$name=$wpdb->get_var("SELECT name FROM ".$wpdb->prefix."uci_curl_riders WHERE id = $id");
		
		if ($name===null || is_wp_error($name))
			return '';
			
		return $name;
	}

	/**
	 * get_series_name_from_old_id function.
	 * 
	 * @access protected
	 * @param int $id (default: 0)
	 * @return void
	 */
	protected function get_series_name_from_old_id($id=0) {
		global $wpdb;
		
		$name=$wpdb->get_var("SELECT name FROM ".$wpdb->prefix."uci_curl_series WHERE id = $id");
		
		if ($name===null || is_wp_error($name))
			return '';
			
		return $name;
	}

	/**
	 * update_rider_rankings_table function.
	 * 
	 * @access public
	 * @return void
	 */
	public function update_rider_rankings_table() {
		global $wpdb;
		
		$db_rankings=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_rider_rankings");
		
		foreach ($db_rankings as $db_row) :
			// get "new" rider id //
			$name=$this->get_rider_name_from_old_id($db_row->rider_id);		
			$rider=get_page_by_title($name, OBJECT, 'riders');
			
			if ($rider===null) :
				$rider_id=0;
			else :
				$rider_id=$rider->ID;
			endif;
			
			$wpdb->update($wpdb->uci_results_rider_rankings, array('rider_id' => $rider_id), array('id' => $db_row->id));	
		endforeach;
		
		return true;
	}

	/**
	 * convert_related_race_id function.
	 * 
	 * @access public
	 * @param int $old_id (default: 0)
	 * @param int $new_id (default: 0)
	 * @return void
	 */
	public function convert_related_race_id($old_id=0, $new_id=0) {
		global $wpdb;
	
		if (!$old_id || !$new_id)
			return;

		$related_race_row=$wpdb->get_row("SELECT * FROM $wpdb->uci_results_related_races WHERE race_id = $old_id");	
		$wpdb->update($wpdb->uci_results_related_races, array('race_id' => $new_id), array('id' => $related_race_row->id));
	
		update_post_meta($new_id, '_race_related', $related_race_row->related_race_id);
	}

	/**
	 * update_version function.
	 * 
	 * @access protected
	 * @return void
	 */
	protected function update_version() {
		if (UCI_RESULTS_VERSION != get_option('uci_results_version'))
			update_option('uci_results_version', UCI_RESULTS_VERSION);
	}		
}	

new UCIResultsMigration100();
?>