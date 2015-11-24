<?php
/**
 @since Version 1.0.2
**/
class ViewDB {

	public $version='0.1.3';
	public $url='';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action('admin_enqueue_scripts',array($this,'viewdb_scripts_styles'));
		add_action('wp_ajax_race_search',array($this,'ajax_race_search'));
		add_action('wp_ajax_race_filter',array($this,'ajax_race_filter'));
		add_action('wp_ajax_rider_search',array($this,'ajax_rider_search'));
		add_action('wp_ajax_rider_filter',array($this,'ajax_rider_filter'));
		add_action('wp_ajax_add_rider_season_uci_points',array($this,'ajax_add_rider_season_uci_points'));
		add_action('wp_ajax_update_season_sos',array($this,'ajax_update_season_sos'));
		add_action('wp_ajax_update_season_wins',array($this,'ajax_update_season_wins'));

		$this->url=admin_url('admin.php?page=uci-view-db');
	}

	/**
	 * viewdb_scripts_styles function.
	 *
	 * @access public
	 * @param mixed $hook
	 * @return void
	 */
	public function viewdb_scripts_styles($hook) {
		if ($hook!='uci-cross_page_uci-view-db')
			return false;

		wp_enqueue_script('jquery-tablesorter-script',plugin_dir_url(basename(__FILE__)).'/uci-curl-wp-plugin/js/jquery.tablesorter.min.js',array('jquery'),'2.0.5');
		wp_enqueue_script('uci-view-db-script',plugin_dir_url(basename(__FILE__)).'/uci-curl-wp-plugin/js/view-db.js',array('jquery','jquery-tablesorter-script'));
	}

	/**
	 * display_view_db_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function display_view_db_page() {
		global $wpdb,$uci_curl;

		$seasons=$wpdb->get_col("SELECT season FROM $uci_curl->table GROUP BY season");
		$classes=$wpdb->get_col("SELECT class FROM $uci_curl->table GROUP BY class");
		$countries=$wpdb->get_col("SELECT nat FROM $uci_curl->table GROUP BY nat ORDER BY nat");

		if (isset($_POST['update-race']) && $_POST['update-race'])
			$this->update_race();
		?>

		<div class="wrap uci-view-db">
			<h1>UCI View DB</h1>

			<div class="view-db-filter">
				<div class="row">
					<div class="races col-md-6">
						<h3>Races</h3>
						<div class="filters">
							<form name="race_filters" id="race_filters">
								<div class="row">
									<div class="season col-md-4">
										<h4>Season</h4>
										<select name="season" class="season">
											<option value="0">-- Select One --</option>
											<?php foreach ($seasons as $season) : ?>
												<option value="<?php echo $season; ?>"><?php echo $season; ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="class col-md-4">
										<h4>Class</h4>
										<select name="class" class="class">
											<option value="0">-- Select One --</option>
											<?php foreach ($classes as $class) : ?>
												<option value="<?php echo $class; ?>"><?php echo $class; ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="country col-md-4">
										<h4>Country</h4>
										<select name="nat" class="nat">
											<option value="0">-- Select One --</option>
											<?php foreach ($countries as $country) : ?>
												<option value="<?php echo $country; ?>"><?php echo $country; ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div><!-- .row -->

								<div class="row">
									<div class="race-search-buttons col-md-12">
										<p>
											<button type="reset" id="form-reset" value="Reset">Reset</button>
										</p>
									</div>
								</div><!-- .row -->

							</form>
							<div class="row">
								<div class="col-md-12">
									<h4>Search by Name</h4>
								</div>
								<div class="race-search col-md-6">
									<input id="race-search" type="text" />
								</div>
								<div class="col-md-6">
									<button type="reset" id="clear-race-search" value="Clear">Clear</button>
								</div>
							</div><!-- .row -->
							<div class="row">
								<div class="race-search-results col-md-12">
									<div id="race-search-results-text">Search Races...</div>
								</div>
							</div><!-- .row -->
						</div><!-- .filters -->
					</div><!-- .races -->
					<div class="riders col-md-6">
						<h3>Riders</h3>
						<div class="filters">
							<form name="rider_filters" id="rider_filters">
								<div class="row">
									<div class="season col-md-4">
										<h4>Season</h4>
										<select name="season" class="season-dd">
											<option value="0">-- Select One --</option>
											<?php foreach ($seasons as $season) : ?>
												<option value="<?php echo $season; ?>"><?php echo $season; ?></option>
											<?php endforeach; ?>
										</select>
									</div>
<!--
									<div class="class col-md-4">
										<h4>Class</h4>
										<select name="class" class="class">
											<option value="0">-- Select One --</option>
											<?php foreach ($classes as $class) : ?>
												<option value="<?php echo $class; ?>"><?php echo $class; ?></option>
											<?php endforeach; ?>
										</select>
									</div>
-->
<!--
									<div class="country col-md-4">
										<h4>Country</h4>
										<select name="nat" class="nat">
											<option value="0">-- Select One --</option>
											<?php foreach ($countries as $country) : ?>
												<option value="<?php echo $country; ?>"><?php echo $country; ?></option>
											<?php endforeach; ?>
										</select>
									</div>
-->
								</div><!-- .row -->

								<div class="row">
									<div class="race-search-buttons col-md-12">
										<p>
											<button type="reset" id="form-reset" value="Reset">Reset</button>
										</p>
									</div>
								</div><!-- .row -->

							</form>
							<div class="row">
								<div class="col-md-12">
									<h4>Search by Name</h4>
								</div>
								<div class="rider-search col-md-6">
									<input id="rider-search" type="text" />
								</div>
								<div class="col-md-6">
									<button type="reset" id="clear-rider-search" value="Clear">Clear</button>
								</div>
							</div><!-- .row -->
							<div class="row">
								<div class="rider-search-results col-md-12">
									<div id="rider-search-results-text">Search Riders...</div>
								</div>
							</div><!-- .row -->
						</div><!-- .filters -->
					</div><!-- .riders -->
				</div>
				<div class="row data" id="get-race-rider">
					<?php if (isset($_GET['race_code'])) : ?>
						<?php echo $this->get_race_data($_GET['race_code']); ?>
					<?php endif; ?>
					<?php if (isset($_GET['rider'])) : ?>
						<?php echo $this->get_rider_data($_GET['rider']); ?>
					<?php endif; ?>
				</div>
			</div>
			<div id="loader">
				<div class="inner">
					<img src="<?php echo plugins_url('../images/ajax-loader.gif',__FILE__); ?>" />
				</div>
			</div>
		</div><!-- .wrap -->

		<?php
	}

	/**
	 * get_race_data function.
	 *
	 * @access protected
	 * @param mixed $race_code
	 * @return void
	 */
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

	/**
	 * ajax_race_search function.
	 *
	 * @access public
	 * @return void
	 */
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

	/**
	 * ajax_race_seasons function.
	 *
	 * @access public
	 * @return void
	 */
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

			$html.='<p class="submit">';
				$html.='<input type="button" name="button" id="add_rider_season_uci_points" class="button button-primary" value="Add Rider UCI Points" />';
			$html.='</p>';

		$html.='</div>';
		$html.='<script>jQuery(".tablesorter").tablesorter();</script>';

		echo $html;

		wp_die();
	}

	/**
	 * ajax_rider_search function.
	 *
	 * @access public
	 * @return void
	 */
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

	/**
	 * ajax_rider_filter function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_rider_filter() {
		global $RiderStats;

		parse_str($_POST['form'],$form);

		$html=null;
		$args=array(
			'pagination' => false,
			'order_by' => 'rider'
		);
		$args=array_merge($args,$form);
		$riders=$RiderStats->get_riders($args);

		$html.='<div class="view-db-riders col-md-12">';
			$html.='<h4>Riders</h4>';

			$html.='<div class="row">';
				$html.='<div class="submit col-md-2">';
					$html.='<input type="button" name="button" id="update_rider_sos" class="button button-primary" value="Update SOS" />';
				$html.='</div>';

				$html.='<div class="submit col-md-2">';
					$html.='<input type="button" name="button" id="update_rider_wins" class="button button-primary" value="Update Wins" />';
				$html.='</div>';
			$html.='</div>';

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
							$html.='<td class="name">'.$rider->rider.'</td>';
							$html.='<td class="nat">'.$rider->nat.'</td>';
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
	 * ajax_add_rider_season_uci_points function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_add_rider_season_uci_points() {
		global $uci_curl,$RaceStats;

		$race_codes=$_POST['value'];

		// get race/results //
		foreach ($race_codes as $code) :
			$race=$RaceStats->get_race($code);
			$results=$race->results;

			// cycle through results //
			foreach ($results as $result) :
				echo $uci_curl->add_rider_season_uci_points($result->rider,$result->nat,$race->details->season,$race->details->class,$result->points);
			endforeach;
		endforeach;

		wp_die();
	}

	public function ajax_update_season_sos() {
		global $uci_curl;

		if ($_POST['rider']!='') :
		// single
		else :
			echo $uci_curl->update_rider_season_sos(false,$_POST['season']);
		endif;

		wp_die();
	}

	public function ajax_update_season_wins() {
		global $uci_curl;

		if ($_POST['rider']!='') :
		// single
		else :
			echo $uci_curl->update_rider_wins(false,$_POST['season']);
		endif;

		wp_die();
	}

}

new ViewDB();
?>