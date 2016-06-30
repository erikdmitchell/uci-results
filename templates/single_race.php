<?php
/**
 * The template for the single race page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

get_header(); ?>

<?php
global $ucicurl_races;

$race=$ucicurl_races->get_race(get_query_var('race_code'));
?>

<div class="uci-results uci-results-race">

	<?php if (!$race) : ?>
		<div class="race-results-not-found">Race results not found.</div>
	<?php else : ?>
		<h1 class="page-title"><?php echo $race->event; ?><span class="flag"><?php echo ucicurl_get_country_flag($race->nat); ?></span></h1>

		<div class="race-details">
			<div class="race-date"><?php echo date(get_option('date_format'), strtotime($race->date)); ?></div>
			<div class="race-class">(<?php echo $race->class; ?>)</div>
		</div>

		<div class="table single-race">
			<div class="row header">
					<div class="rider-place">Place</div>
					<div class="rider-name">Rider</div>
					<div class="rider-points">Points</div>
					<div class="rider-nat">Nat</div>
					<div class="rider-age">Age</div>
					<div class="rider-time">Time</div>
			</div>

			<?php foreach ($race->results as $result) : ?>
				<div class="row rider-results">
					<div class="rider-place"><?php echo $result->place; ?></div>
					<div class="rider-name"><a href="<?php echo uci_results_rider_url($result->slug); ?>"><?php echo $result->name; ?></a></div>
					<div class="rider-points"><?php echo $result->points; ?></div>
					<div class="rider-nat"><a href="<?php echo uci_results_country_url($result->nat); ?>"><?php echo ucicurl_get_country_flag($result->nat); ?></a></div>
					<div class="rider-age"><?php echo $result->age; ?></div>
					<div class="rider-time"><?php echo $result->time; ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>