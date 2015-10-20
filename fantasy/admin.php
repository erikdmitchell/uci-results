<?php
class FantasyCyclingAdmin {

	public function __construct() {
		add_action('admin_enqueue_scripts',array($this,'admin_scripts_styles'));
		add_action('wp_ajax_load_start_list',array($this,'ajax_load_start_list'));
	}

	public function admin_scripts_styles($hook) {
		if ($hook!='uci-cross_page_fantasy-cycling')
			return false;

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('fantasy-cycling-admin',plugins_url('/js/admin.js',__FILE__),array('jquery','jquery-ui-datepicker'));

		wp_enqueue_style('fantasy-cycling-admin',plugins_url('/css/admin.css',__FILE__));
	}

	public function admin_page() {
		global $uci_curl,$RiderStats;

		$html=null;
		$years_in_db=array_reverse($uci_curl->get_years_in_db());

		$html.='<div class="fantasy-cycling-admin">';
			$html.='<h2>Fantasy Cycling</h2>';

			if (isset($_POST['add-race']) && $_POST['add-race'])
				$html.=$this->add_race_to_db($_POST);

			if (isset($_POST['add-start-list']) && $_POST['add-start-list'])
				$html.=$this->add_start_list_to_db($_POST);

			$html.='<div class="row">';
				$html.='<div class="col-md-12">';
					$html.='<ul class="admin-nav">';
						$html.='<li><a href="">Add Race</a></li>';
						$html.='<li><a href="">Add Start List</a></li>';
					$html.='</ul>';
				$html.='</div>';
			$html.='</div>';

			$html.='<form name="add-race" id="add-race" class="add-race" method="post">';
				$html.='<h3>Add Race</h3>';
				$html.='<div class="row">';
					$html.='<div class="col-md-1">';
						$html.='<label for="name">Name</label>';
					$html.='</div>';
					$html.='<div class="col-md-6">';
						$html.='<input type="text" name="name" id="name" class="longtext" value="" />';
					$html.='</div>';
				$html.='</div>';

				$html.='<div class="row">';
					$html.='<div class="col-md-1">';
						$html.='<label for="season">Season</label>';
					$html.='</div>';
					$html.='<div class="col-md-6">';
						$html.='<select name="season" id="season">';
							foreach ($years_in_db as $year) :
								$html.='<option value="'.$year.'">'.$year.'</option>';
							endforeach;
						$html.='</select>';
					$html.='</div>';
				$html.='</div>';

				$html.='<div class="row">';
					$html.='<div class="col-md-1">';
						$html.='<label for="type">Type</label>';
					$html.='</div>';
					$html.='<div class="col-md-6">';
						$html.='<select name="type" id="type">';
							$html.='<option value="cdm">CDM</option>';
							$html.='<option value="cn">CN</option>';
							$html.='<option value="c1">C1</option>';
							$html.='<option value="c2">C2</option>';
						$html.='</select>';
					$html.='</div>';
				$html.='</div>';

				$html.='<div class="row">';
					$html.='<div class="col-md-1">';
						$html.='<label for="date">Date</label>';
					$html.='</div>';
					$html.='<div class="col-md-6">';
						$html.='<input type="text" name="date" id="date" class="date" value="" />';
					$html.='</div>';
				$html.='</div>';

				$html.='<div class="row">';
					$html.='<div class="col-md-1">';
						$html.='<label for="series">Series</label>';
					$html.='</div>';
					$html.='<div class="col-md-6">';
						$html.='<select name="series" id="series">';
							$html.='<option value="single">Single</option>';
							$html.='<option value="Superprestige">Superprestige</option>';
							$html.='<option value="bPost Bank">bPost Bank</option>';
							$html.='<option value="World Cup">World Cup</option>';
						$html.='</select>';
					$html.='</div>';
				$html.='</div>';

				$html.='<div class="row">';
					$html.='<div class="col-md-1">';
						$html.='<label for="code">Code</label>';
					$html.='</div>';
					$html.='<div class="col-md-6">';
						$html.='<input type="text" name="code" id="code" class="longtext" value="" />';
					$html.='</div>';
				$html.='</div>';

				$html.='<input type="hidden" name="add-race" value="1" />';

				$html.='<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Add Race"></p>';
			$html.='</form>';

			$races=$this->get_races_from_db();
			$riders=$RiderStats->get_riders(array(
				'pagination' => false,
				'order_by' => 'nat ASC, name'
			));

			// split into 3 "chunks" //
			$cols=array_chunk($riders,ceil(count($riders)/3),true);
			$left_col=$cols[0];
			$center_col=$cols[1];
			$right_col=$cols[2];

			$html.='<form name="add-start-list" id="add-start-list" class="add-start-list" method="post">';
				$html.='<h3>Add Start List</h3>';
				$html.='<div class="row">';
					$html.='<div class="col-md-1">';
						$html.='<label for="race">Race</label>';
					$html.='</div>';
					$html.='<div class="col-md-6">';
						$html.='<select name="race" id="race">';
								$html.='<option value="0">Select Race</option>';
							foreach ($races as $race) :
								$html.='<option value="'.$race->id.'">'.$race->name.'</option>';
							endforeach;
						$html.='</select>';
					$html.='</div>';
				$html.='</div>';

				$html.='<div class="row start-list">';
					$html.='<div class="col-md-1">';
						$html.='<label for="riders">Riders</label>';
					$html.='</div>';
					$html.='<div class="col-md-3">';
						foreach ($left_col as $rider) :
							$html.='<input type="checkbox" name="riders[]" class="sl-riders" value="'.$rider->rider.'" /> '.$rider->rider.' ('.$rider->nat.')<br />';
						endforeach;
					$html.='</div>';
					$html.='<div class="col-md-3">';
						foreach ($center_col as $rider) :
							$html.='<input type="checkbox" name="riders[]" class="sl-riders" value="'.$rider->rider.'" /> '.$rider->rider.' ('.$rider->nat.')<br />';
						endforeach;
					$html.='</div>';
					$html.='<div class="col-md-3 last-col">';
						foreach ($right_col as $rider) :
							$html.='<input type="checkbox" name="riders[]" class="sl-riders" value="'.$rider->rider.'" /> '.$rider->rider.' ('.$rider->nat.')<br />';
						endforeach;
					$html.='</div>';
				$html.='</div>';

				$html.='<div class="row">';
					$html.='<div class="col-md-1">';
						$html.='<label for="addon-riders">Addon Riders</label>';
					$html.='</div>';
					$html.='<div class="col-md-6">';
						$html.='<input type="text" name="riders[]" class="mediumtext" value="" /><br />';
						$html.='<input type="text" name="riders[]" class="mediumtext" value="" /><br />';
						$html.='<input type="text" name="riders[]" class="mediumtext" value="" /><br />';
						$html.='<input type="text" name="riders[]" class="mediumtext" value="" /><br />';
						$html.='<input type="text" name="riders[]" class="mediumtext" value="" /><br />';
					$html.='</div>';
				$html.='</div>';

				$html.='<input type="hidden" name="add-start-list" value="1" />';
				$html.='<input type="hidden" name="id" id="race-id" value="0" />';

				$html.='<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Update Start List"></p>';
			$html.='</form>';

		$html.='</div>';

		echo $html;
	}

	/**
	 * add_race_to_db function.
	 *
	 * @access protected
	 * @param mixed $form
	 * @return void
	 */
	protected function add_race_to_db($form) {
		global $wpdb;

		if ($form['name']=='')
			return '<div class="error">No name entered.</div>';

		$data=array(
			'name' => $form['name'],
			'season' => $form['season'],
			'type' => $form['type'],
			'code' => $form['code'],
			'series' => $form['series'],
			'race_start' => date('Y-m-d H:i:s', strtotime($form['date'])),
		);

		$wpdb->insert('wp_fc_races',$data);

		return '<div class="updated">Race added.</div>';
	}

	/**
	 * get_races_from_db function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_races_from_db() {
		global $wpdb;

		$races=$wpdb->get_results("SELECT * FROM wp_fc_races");

		return $races;
	}

	/**
	 * add_start_list_to_db function.
	 *
	 * @access protected
	 * @param mixed $form
	 * @return void
	 */
	protected function add_start_list_to_db($form) {
		global $wpdb;

		if (!$form['id'])
			return '<div class="error">No race entered.</div>';

		// clean empties //
		foreach ($form['riders'] as $key => $value) :
			if ($value=='')
				unset($form['riders'][$key]);
		endforeach;

		$data=array(
			'start_list' => serialize($form['riders'])
		);
		$where=array('id' => $form['id']);

 		$wpdb->update('wp_fc_races',$data,$where);

		return '<div class="updated">Start list updated.</div>';
	}

	public function ajax_load_start_list() {
		global $wpdb;

		$race=$wpdb->get_row("SELECT * FROM wp_fc_races WHERE id=".$_POST['id']);

		$race->start_list=unserialize($race->start_list);

		echo json_encode($race);

		wp_die();
	}

}

new FantasyCyclingAdmin();
?>