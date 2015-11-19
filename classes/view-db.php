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

		wp_enqueue_script('uci-view-db-script',plugin_dir_url(basename(__FILE__)).'/uci-curl-wp-plugin/js/view-db.js',array('jquery'));
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
									<h4>Search</h4>
								</div>
								<div class="race-search col-md-6">
									<input id="race-search" type="text" />
								</div>
								<div class="race-search col-md-6">
									<button type="reset" id="clear-race-search" value="Clear">Clear</button>
								</div>
							</div><!-- .row -->
							<div class="row">
								<div class="race-search-results col-md-12">
									<div id="race-search-results-text">Search Results...</div>
								</div>
							</div><!-- .row -->
						</div><!-- .filters -->
					</div><!-- .races -->
					<div class="races col-md-6">
						<h3>Riders</h3>
					</div><!-- .riders -->
				</div>
				<div class="row data">
					<?php if (isset($_GET['race_code'])) : ?>
						<?php echo $this->get_race_data($_GET['race_code']); ?>
					<?php endif; ?>
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

			$html.='<div class="row header">';
				$html.='<div class="date col-md-2">Date</div>';
				$html.='<div class="name col-md-5">Name</div>';
				$html.='<div class="nat col-md-1">Nat</div>';
				$html.='<div class="class col-md-1">Class</div>';
				$html.='<div class="fq col-md-1">FQ</div>';
			$html.='</div>';

			foreach ($races as $race) :
				$html.='<div class="row race-details">';
					$html.='<div class="date col-md-2">'.$race->date.'</div>';
					$html.='<div class="name col-md-5"><a href="'.$this->url.'&race_code='.urlencode($race->code).'">'.$race->name.'</a></div>';
					$html.='<div class="nat col-md-1">'.$race->nat.'</div>';
					$html.='<div class="class col-md-1">'.$race->class.'</div>';
					$html.='<div class="fq col-md-1">'.$race->fq.'</div>';
				$html.='</div>';
			endforeach;
		$html.='</div>';

		echo $html;

		wp_die();
	}

}

new ViewDB();
?>