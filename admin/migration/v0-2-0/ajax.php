<?php
class UCIResultsMigration020 {
	
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
	
	
	public function ajax_migrate_races() {
		//uci_results_migrate_races();
		
		echo json_encode(array(
			'step' => 4,
			'success' => true,
			'percent' => 70
		));
		
		wp_die();
	}
	
	
	public function ajax_update_series_overall_table() {
		//uci_results_update_series_overall_table();
		
		echo json_encode(array(
			'step' => 5,
			'success' => true,
			'percent' => 80
		));
		
		wp_die();
	}
	
	
	public function ajax_update_rider_rankings_table() {
		//uci_results_update_rider_rankings_table();
		
		echo json_encode(array(
			'step' => 6,
			'success' => true,
			'percent' => 90
		));
		
		wp_die();
	}
	
	
	public function ajax_run_clean_up() {
		global $wpdb;
		
		$related_races_table=$wpdb->prefix.'uci_curl_related_races';
		
		// remove tables //
		//$wpdb->query("DROP TABLE IF EXISTS $wpdb->uci_results_races, $wpdb->uci_results_results, $wpdb->uci_results_riders, $wpdb->uci_results_series;");
		
		// remove race ids col from related races //
		//$wpdb->query("ALTER TABLE $wpdb->uci_results_related_races DROP COLUMN race_ids");
		
		//update_uci_results_version();
		
		//update_option('ucicurl_db_version', '0.2.0');
		
		echo json_encode(array(
			'step' => 7,
			'success' => true,
			'percent' => 100
		));
		
		wp_die();
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
			$rider=get_page_by_title($db_rider->name, OBJECT, 'riders');
	
			if ($rider === null) :
				$arr=array(
					'post_title' => $db_rider->name,
					'post_content' => '',
					'post_status' => 'publish',	
					'post_type' => 'riders',
					'post_name' => $db_rider->slug,
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

	function uci_results_migrate_races() {
		global $wpdb;
	
		$races_table=$wpdb->prefix.'uci_curl_races';	
		$db_races=$wpdb->get_results("SELECT * FROM $races_table");
		
		if (!count($db_races))
			return;
			
		foreach ($db_races as $db_race) :
			$old_id=$db_race->id;
			$series=uci_results_convert_series($db_race->series_id);
			$race=get_page_by_path($db_race->code, OBJECT, 'races');
			$race_data=array(
				'post_title' => $db_race->event,
				'post_content' => '',
				'post_status' => 'publish',	
				'post_type' => 'races',
				'post_name' => $db_race->code,		
			);
	
			// if race is null, add it, else update it //
			if ($race === null) :
				$post_id=wp_insert_post($race_data);
			else :
				$race_data['ID']=$race->ID;
			 	$post_id=wp_update_post($race_data);
			endif;		
			
			// check for error //
			if (is_wp_error($post_id))
				return false;
				
			// update taxonomies //
			wp_set_object_terms($post_id, $db_race->nat, 'country', false);
			wp_set_object_terms($post_id, $db_race->class, 'race_class', false);
			wp_set_object_terms($post_id, $db_race->season, 'season', false);
			wp_set_object_terms($post_id, $series, 'series', false);
			
			// update meta //
			update_post_meta($post_id, '_race_date', $db_race->date);
			update_post_meta($post_id, '_race_winner', $db_race->winner);
			update_post_meta($post_id, '_race_week', $db_race->week);
			update_post_meta($post_id, '_race_link', $db_race->link);	
			update_post_meta($post_id, '_race_twitter', $db_race->twitter);	
		
			uci_results_migrate_results($post_id, $old_id);
			
			uci_results_convert_related_race_id($db_race->id, $db_race->related_races_id, $db_race->code);
		endforeach;
		
	}

	function uci_results_migrate_results($post_id=0, $old_id=0) {
		global $wpdb;
		
		$table=$wpdb->prefix.'uci_curl_results';
		
		if (!$post_id || !$old_id)
			return false;
		
		$race_results=$wpdb->get_results("SELECT * FROM $table WHERE race_id = $old_id");
		
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
	}

	function uci_results_convert_series($old_id=0) {
		global $wpdb;
		
		if (!$old_id)
			return;
			
		$series=$wpdb->get_var("SELECT name FROM ".$wpdb->prefix."uci_curl_series WHERE id = $old_id");
		
		return $series;
	}

	function uci_results_update_series_overall_table() {
		global $wpdb;
		
		$db_series_overall=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_series_overall");
		
		foreach ($db_series_overall as $db_row) :
			// get "new" rider id //
			$name=uci_results_get_rider_name_from_old_id($db_row->rider_id);		
			$rider=get_page_by_title($name, OBJECT, 'riders');
			
			if ($rider===null) :
				$rider_id=0;
			else :
				$rider_id=$rider->ID;
			endif;
			
			// get "new" series id //
			$name=uci_results_get_series_name_from_old_id($db_row->series_id);		
			$term=get_term_by('name', $name, 'series');
	
			if ($term===null) :
				$series_id=0;
			else :
				$series_id=$term->term_id;
			endif;
			
			$wpdb->update($wpdb->uci_results_series_overall, array('rider_id' => $rider_id, 'series_id' => $series_id), array('id' => $db_row->id));	
		endforeach;
	}

	function uci_results_get_rider_name_from_old_id($id=0) {
		global $wpdb;
		
		$name=$wpdb->get_var("SELECT name FROM ".$wpdb->prefix."uci_curl_riders WHERE id = $id");
		
		if ($name===null || is_wp_error($name))
			return '';
			
		return $name;
	}

	function uci_results_get_series_name_from_old_id($id=0) {
		global $wpdb;
		
		$name=$wpdb->get_var("SELECT name FROM ".$wpdb->prefix."uci_curl_series WHERE id = $id");
		
		if ($name===null || is_wp_error($name))
			return '';
			
		return $name;
	}

	function uci_results_update_rider_rankings_table() {
		global $wpdb;
		
		$db_rankings=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_rider_rankings");
		
		foreach ($db_rankings as $db_row) :
			// get "new" rider id //
			$name=uci_results_get_rider_name_from_old_id($db_row->rider_id);		
			$rider=get_page_by_title($name, OBJECT, 'riders');
			
			if ($rider===null) :
				$rider_id=0;
			else :
				$rider_id=$rider->ID;
			endif;
			
			$wpdb->update($wpdb->uci_results_rider_rankings, array('rider_id' => $rider_id), array('id' => $db_row->id));	
		endforeach;
	}

	function uci_results_convert_related_race_id($old_id=0, $old_related_races_id=0, $slug='') {
		global $wpdb;
		
		$related_races_table=$wpdb->prefix.'uci_curl_related_races';
	
		if (!$old_id || !$old_related_races_id)
			return;
			
		$related_race_row=$wpdb->get_row("SELECT * FROM $related_races_table WHERE related_race_id = $old_related_races_id AND race_id = $old_id");
		$new_race_id=$wpdb->get_var("SELECT ID from $wpdb->posts WHERE post_name = '$slug' AND post_type = 'races'");
		$wpdb->update($related_races_table, array('race_id' => $new_race_id), array('id' => $related_race_row->id));
	
		update_post_meta($new_race_id, '_race_related', $related_race_row->related_race_id);
	}

	function update_uci_results_version() {
		if (UCI_RESULTS_VERSION != get_option('uci_results_version'))
			update_option('uci_results_version', UCI_RESULTS_VERSION);
	}		
}	

new UCIResultsMigration020();
?>