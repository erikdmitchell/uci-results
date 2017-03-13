<?php
/**
 * The template for the "search" page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

get_header();

global $ucicurl_races;

$type='';
$rider_nat='';
$race_season='';
$race_class='';
$race_nat='';
$race_series_id='';
?>

<div class="em-container uci-results uci-results-search">

	<h1 class="page-title"><?php _e('UCI Results &amp; Rankings Search', 'uci-results'); ?></h1>

	<div class="em-row search-box">
		<div class="em-col-md-12">
			<input type="text" name="search" id="uci-results-search" class="search" value="" />
			<div class="search-icon"><a class="search-icon-go" href=""><i class="fa fa-search" aria-hidden="true"></i></a></div>
		</div>
	</div>

	<div class="em-row">
		<div class="em-col-md-3 search-filters">

			<div class="filter-section">
				<label>Type</label>
				<div class="search-type">
					<div class="type-row">
						<input type="radio" class="type" name="type" value="races" <?php checked($type, 'races'); ?>>
						<span>Races</span>
					</div>
					<div class="type-row">
						<input type="radio" class="type" name="type" value="riders" <?php checked($type, 'riders'); ?>>
						<span>Riders</span>
					</div>
				</div>
			</div>

			<div id="riders-search-filters" class="filter-section">
				<div class="rider-nat">
					<label for="rider-nat">Country</label>

				</div>
			</div>

			<div id="races-search-filters" class="filter-section">
				<div class="race-season">
					<label for="season">Season</label>

				</div>
				<div class="race-class">
					<label for="race-class">Class</label>

				</div>
				<div class="race-nat">
					<label for="race-nat">Country</label>

				</div>
				<div class="race-series-id">
					<label for="series_id">Series</label>

				</div>
			</div>

		</div>


		<div id="uci-results-search-results" class="em-col-md-9 search-results"></div>
	</div>

	<div id="search-loader"><div id="loader-gif"><img src="/wp-includes/images/wpspin-2x.gif"></div></div>

</div>

<?php get_footer(); ?>