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

$rider_nat='';
$race_season='';
$race_class='';
$race_nat='';
$race_series_id='';
?>

<div class="uci-results uci-results-search">

	<h1 class="page-title"><?php _e('UCI Results &amp; Rankings Search','uci-results'); ?></h1>

	<div class="em-row search-box">
		<div class="em-col-md-3 search-details">
			Search Details
		</div>

		<div class="em-col-md-9">
			<input type="text" name="search" id="search" class="search" value="" />
			<div class="search-icon"><a href=""><span class="dashicons dashicons-search"></span></a></div>
		</div>
	</div>

	<div class="em-row">
		<div class="em-col-md-3 search-filters">

			<div class="filter-section">
				<label>Type</label>
				<div class="search-type">
					<div class="type-row">
						<input type="radio" name="type" value="races">
						<span>Races</span>
					</div>
					<div class="type-row">
						<input type="radio" name="type" value="riders">
						<span>Riders</span>
					</div>
				</div>
			</div>

			<div id="riders-search-filters" class="filter-section">
				<div class="rider-nat">
					<label for="rider-nat">Country</label>
					<select id="rider-nat" name="nat">
						<option value="0">-- Select Country --</option>
						<?php foreach ($ucicurl_riders->nats() as $country) : ?>
							<option value="<?php echo $country; ?>" <?php selected($rider_nat, $country); ?>><?php echo $country; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div id="races-search-filters" class="filter-section">
				<div class="race-season">
					<label for="season">Season</label>
					<select id="season" name="season">
						<option value="0">-- Select Season --</option>
						<?php foreach ($ucicurl_races->seasons() as $season) : ?>
							<option value="<?php echo $season; ?>" <?php selected($race_season, $season); ?>><?php echo $season; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="race-class">
					<label for="race-class">Class</label>
					<select id="race-class" name="class">
						<option value="0">-- Select Class --</option>
						<?php foreach ($ucicurl_races->classes() as $class) : ?>
							<option value="<?php echo $class; ?>" <?php selected($race_class, $class); ?>><?php echo $class; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="race-nat">
					<label for="race-nat">Country</label>
					<select id="race-nat" name="nat">
						<option value="0">-- Select Country --</option>
						<?php foreach ($ucicurl_races->nats() as $country) : ?>
							<option value="<?php echo $country; ?>" <?php selected($race_nat, $country); ?>><?php echo $country; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="race-series-id">
					<label for="series_id">Series</label>
					<?php $ucicurl_races->series_dropdown('series_id', $race_series_id); ?>
				</div>
			</div>

		</div>


		<div id="uci-results-search-results" class="em-col-md-9 search-results">CONTENT</div>
	</div>

</div>

<?php get_footer(); ?>