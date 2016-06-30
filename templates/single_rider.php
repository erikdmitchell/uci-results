<?php
/**
 * The template for the single rider page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

get_header(); ?>

<?php
global $ucicurl_riders;

$rider=$ucicurl_riders->get_rider(get_query_var('rider_slug'));
?>

<div class="uci-results uci-results-rider">

	<h1 class="page-title"><?php echo ucicurl_rider_slug_to_name(get_query_var('rider_slug')); ?><span class="flag"><a href="<?php echo uci_results_country_url($rider->nat); ?>"><?php echo ucicurl_get_country_flag($rider->nat); ?></a></span></h1>

	<?php if (count($rider->results)) : ?>
		<div class="table single-rider">
			<div class="row header">
				<div class="race-date">Date</div>
				<div class="race-name">Event</div>
				<div class="rider-place">Place</div>
				<div class="rider-points">Points</div>
				<div class="race-class">Class</div>
				<div class="race-season">Season</div>
			</div>

			<?php foreach ($rider->results as $result) : ?>
				<div class="row">
					<div class="race-date"><?php echo date(get_option('date_format'), strtotime($result->date)); ?></div>
					<div class="race-name"><a href="<?php uci_results_race_url($result->race_id); ?>"><?php echo $result->event; ?></a></div>
					<div class="rider-place"><?php echo $result->place; ?></div>
					<div class="rider-points"><?php echo $result->par; ?></div>
					<div class="race-class"><?php echo $result->class; ?></div>
					<div class="race-season"><?php echo $result->season; ?></div>
				</div>
			<?php endforeach; ?>

		</div>
	<?php else :?>
		<div class="none-found">No results.</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>