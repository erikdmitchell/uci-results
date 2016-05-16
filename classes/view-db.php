<?php

/**
 * ViewDB class.
 */
class ViewDB {

	public function __construct() {
		add_action('admin_enqueue_scripts',array($this,'viewdb_scripts_styles'));

		add_action('wp_ajax_race_search',array($this,'ajax_race_search'));
		add_action('wp_ajax_race_filter',array($this,'ajax_race_filter'));
		add_action('wp_ajax_rider_search',array($this,'ajax_rider_search'));
		add_action('wp_ajax_rider_filter',array($this,'ajax_rider_filter'));
	}

	public function viewdb_scripts_styles($hook) {
		if ($hook!='uci-cross_page_uci-view-db')
			return false;

		wp_enqueue_script('jquery-tablesorter-script',plugin_dir_url(basename(__FILE__)).'/uci-curl-wp-plugin/js/jquery.tablesorter.min.js',array('jquery'),'2.0.5');
		wp_enqueue_script('uci-view-db-script',plugin_dir_url(basename(__FILE__)).'/uci-curl-wp-plugin/js/view-db.js',array('jquery','jquery-tablesorter-script'));
	}

	protected function get_race_data($race_code) {
		global $RaceStats;

		$html=null;
		$race=$RaceStats->get_race($race_code);
		$race_classes=$RaceStats->get_race_classes();
		$CrossSeasons=new CrossSeasons();
		$counter=0;

		$html.='<div class="view-db-single-race col-md-12">';

			$html.='<h4>'.$race->details->race.'</h4>';

			$html.='<form name="edit-race" id="edit-race" method="post" action="'.$this->url.'">';
				$html.='<div class="row header">';
					$html.='<div class="date col-md-2">Date</div>';
					$html.='<div class="class col-md-2">Class</div>';
					$html.='<div class="nat col-md-2">Nat</div>';
					$html.='<div class="season col-md-2">Season</div>';
				$html.='</div>';
				$html.='<div class="row race-details">';
					$html.='<div class="date col-md-2"><input name="race[date]" id="race-date" value="'.$race->details->date.'" /></div>';
					$html.='<div class="class col-md-2">';
						$html.='<select name="race[class]" id="race-class">';
							foreach ($race_classes as $class) :
								$html.='<option value="'.$class.'" '.selected($race->details->class,$class,false).'>'.$class.'</option>';
							endforeach;
						$html.='</select>';
					$html.='</div>';
					$html.='<div class="nat col-md-2"><input name="race[date]" id="race-date" value="'.$race->details->nat.'" /></div>';
					$html.='<div class="season col-md-2">';
						$html.='<select name="race[season]" id="race-season">';
							foreach ($CrossSeasons->seasons as $season) :
								$html.='<option value="'.$season.'" '.selected($race->details->season,$season,false).'>'.$season.'</option>';
							endforeach;
						$html.='</select>';
					$html.='</div>';
				$html.='</div>';
				$html.='<div class="row header">';
					$html.='<div class="place col-md-1">Place</div>';
					$html.='<div class="rider col-md-3">Rider</div>';
					$html.='<div class="nat col-md-1">Nat</div>';
					$html.='<div class="age col-md-1">Age</div>';
					$html.='<div class="time col-md-1">Time</div>';
					$html.='<div class="points col-md-1">Points</div>';
				$html.='</div>';
				$html.='<div class="results">';
					foreach ($race->results as $result) :
						$html.='<div id="rider-'.$counter.'" class="row result">';
							$html.='<div class="place col-md-1"><input type="text" name="rider['.$counter.'][place]" id="rider-place" value="'.$result->place.'" /></div>';
							$html.='<div class="rider col-md-3"><input type="text" name="rider['.$counter.'][rider]" id="rider-rider" value="'.$result->rider.'" /></div>';
							$html.='<div class="nat col-md-1"><input type="text" name="rider['.$counter.'][nat]" id="rider-nat" value="'.$result->nat.'" /></div>';
							$html.='<div class="age col-md-1"><input type="text" name="rider['.$counter.'][age]" id="rider-age" value="'.$result->age.'" /></div>';
							$html.='<div class="time col-md-1"><input type="text" name="rider['.$counter.'][time]" id="rider-time" value="'.$result->time.'" /></div>';
							$html.='<div class="points col-md-1"><input type="text" name="rider['.$counter.'][points]" id="rider-points" value="'.$result->points.'" /></div>';
						$html.='</div>';
						$counter++;
					endforeach;
				$html.='</div>';
				$html.='<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>';
				$html.='<input type="hidden" name="race-code" value="'.$race_code.'" />';
				$html.='<input type="hidden" name="update-race" value="1" />';
			$html.='</form>';

		$html.='</div>';

		return $html;
	}

	protected function update_race() {
echo '<pre>';
print_r($_POST);
echo '</pre>';
	}

	protected function get_rider_data($rider_name) {
		global $RiderStats;

		$html=null;
		$results=$RiderStats->get_rider_results(array('name' => $rider_name));

		$html.='<div class="view-db-single-rider col-md-12">';
			$html.='<h4>'.$rider_name.' ('.$results[0]->nat.')</h4>';

			$html.='<table id="single-rider" class="single-rider tablesorter">';
				$html.='<thead>';
					$html.='<tr class="">';
						$html.='<th class="date">Date</th>';
						$html.='<th class="race">Race</th>';
						$html.='<th class="place">Place</th>';
						$html.='<th class="points">Points</th>';
						$html.='<th class="class">Class</th>';
						$html.='<th class="season">Season</th>';
						$html.='<th class="fq">FQ</th>';
					$html.='</tr>';
				$html.='</thead>';
				$html.='<tbody>';
					foreach ($results as $result) :
						$html.='<tr class="race-details">';
							$html.='<td class="date">'.$result->date.'</td>';
							$html.='<td class="race"><a href="'.$this->url.'&race_code='.urlencode($result->code).'">'.$result->event.' ('.$result->race_country.')</a></td>';
							$html.='<td class="place">'.$result->place.'</td>';
							$html.='<td class="points">'.$result->points.'</td>';
							$html.='<td class="class">'.$result->class.'</td>';
							$html.='<td class="season">'.$result->season.'</td>';
							$html.='<td class="fq">'.round($result->fq).'</td>';
						$html.='</tr>';
					endforeach;
				$html.='</tbody>';
			$html.='</table>';
		$html.='</div>';

		return $html;
	}

	public function ajax_race_search() {
		global $wpdb,$uci_curl;

		$html=null;
		$sql="
			SELECT
				code,
				season,
				event AS name,
				nat,
				class
			FROM $uci_curl->table
			WHERE event LIKE '%".$_POST['search']."%'
			ORDER BY date
		";
		$results=$wpdb->get_results($sql);

		if (!count($results)) :
			echo 'No races found.';
			return;
		endif;

		$html.='<div class="races">';
			foreach ($results as $race) :
				$html.='<div id="race-'.$race->code.'" class="row race">';
					$html.='<div class="name col-md-7"><a href="'.$this->url.'&race_code='.urlencode($race->code).'">'.stripslashes($race->name).'</a></div>';
					$html.='<div class="season col-md-2">'.$race->season.'</div>';
					$html.='<div class="class col-md-1">'.$race->class.'</div>';
					$html.='<div class="nat col-md-1">'.$race->nat.'</div>';
				$html.='</div>';
			endforeach;
		$html.='</div>';

		echo $html;

		wp_die();
	}

	public function ajax_race_filter() {
		global $RaceStats;

		parse_str($_POST['form'],$form);

		$html=null;
		$args=array(
			'pagination' => false,
			'order_by' => 'date'
		);
		$args=array_merge($args,$form);

		$races=$RaceStats->get_races($args);

		$html.='<div class="view-db-races col-md-12">';

			$html.='<h4>Races</h4>';

			$html.='<div class="check-all"><a href="" id="checkall">Select All</a></div>';
			$html.='<table id="race-filter" class="race-filter tablesorter">';
				$html.='<thead>';
					$html.='<tr class="">';
						$html.='<th class="checkbox"></th>';
						$html.='<th class="date">Date</th>';
						$html.='<th class="name">Name</th>';
						$html.='<th class="nat">Nat</th>';
						$html.='<th class="class">Class</th>';
						$html.='<th class="fq">FQ</th>';
					$html.='</tr>';
				$html.='</thead>';
				$html.='<tbody>';
					foreach ($races as $race) :
						$html.='<tr class="race-details">';
							$html.='<td class="checkbox"><input class="race-checkbox" type="checkbox" name="races[]" value="'.$race->code.'" /></td>';
							$html.='<td class="date">'.$race->date.'</td>';
							$html.='<td class="name"><a href="'.$this->url.'&race_code='.urlencode($race->code).'">'.$race->name.'</a></td>';
							$html.='<td class="nat">'.$race->nat.'</td>';
							$html.='<td class="class">'.$race->class.'</td>';
							$html.='<td class="fq">'.$race->fq.'</td>';
						$html.='</tr>';
					endforeach;
				$html.='</tbody>';
			$html.='</table>';
			$html.='<div class="check-all"><a href="" id="checkall">Select All</a></div>';

		$html.='</div>';
		$html.='<script>jQuery(".tablesorter").tablesorter();</script>';

		echo $html;

		wp_die();
	}

	public function ajax_rider_search() {
		global $wpdb,$uci_curl;

		$html=null;
		$sql="
			SELECT
				name,
				nat
			FROM $uci_curl->results_table
			WHERE name LIKE '%".$_POST['search']."%'
			GROUP BY name
			ORDER BY name
		";
		$riders=$wpdb->get_results($sql);

		if (!count($riders)) :
			echo 'No riders found.';
			return;
		endif;

		$html.='<div class="riders">';
			foreach ($riders as $rider) :
				$html.='<div id="rider-'.str_replace(' ','',$rider->name).'" class="row rider">';
					$html.='<div class="name col-md-5"><a href="'.$this->url.'&rider='.urlencode($rider->name).'">'.stripslashes($rider->name).'</a></div>';
					$html.='<div class="nat col-md-1">'.$rider->nat.'</div>';
				$html.='</div>';
			endforeach;
		$html.='</div>';

		echo $html;

		wp_die();
	}

	public function ajax_rider_filter() {
		global $RiderStats;

		parse_str($_POST['form'],$form);

		$html=null;
		$args=array(
			'per_page' => -1,
			'order_by' => 'name',
			'order' => 'ASC',
			'group' => 'GROUP BY name'
		);
		$args=array_merge($args,$form);
		$riders=$RiderStats->get_riders($args);

		$html.='<div class="view-db-riders col-md-12">';
			$html.='<h4>Riders</h4>';

			$html.='<table id="riders-filter" class="riders-filter tablesorter">';
				$html.='<thead>';
					$html.='<tr class="">';
						$html.='<th class="name">Name</th>';
						$html.='<th class="nat">Country</th>';
						$html.='<th class="rank">Rank</th>';
					$html.='</tr>';
				$html.='</thead>';
				$html.='<tbody>';
					foreach ($riders as $rider) :
						$html.='<tr class="rider-details">';
							$html.='<td class="name">'.$rider->name.'</td>';
							$html.='<td class="nat">'.$rider->country.'</td>';
							$html.='<td class="rank">'.$rider->rank.'</td>';
						$html.='</tr>';
					endforeach;
				$html.='</tbody>';
			$html.='</table>';
			$html.='<script>jQuery(".tablesorter").tablesorter();</script>';
		echo $html;

		wp_die();
	}

	/**
	 * seasons function.
	 *
	 * @access public
	 * @return void
	 */
	public function seasons() {
		global $wpdb;

		$seasons=$wpdb->get_col("SELECT season FROM $wpdb->ucicurl_races GROUP BY season");

		return $seasons;
	}

	public function classes() {
		global $wpdb;

		$classes=$wpdb->get_col("SELECT class FROM $wpdb->ucicurl_races GROUP BY class");

		return $classes;
	}

	public function nats() {
		global $wpdb;

		$countries=$wpdb->get_col("SELECT nat FROM $wpdb->ucicurl_races GROUP BY nat ORDER BY nat");

		return $countries;
	}

}

$ucicurl_viewdb=new ViewDB();
?>