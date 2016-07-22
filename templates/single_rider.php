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

$rider=$ucicurl_riders->get_rider(get_query_var('rider_slug'), true, true);
?>

<div class="uci-results uci-results-rider">

	<?php if ($rider) : ?>

		<h1 class="page-title"><?php echo ucicurl_rider_slug_to_name(get_query_var('rider_slug')); ?><span class="flag"><a href="<?php echo uci_results_country_url($rider->nat); ?>"><?php echo ucicurl_get_country_flag($rider->nat); ?></a></span></h1>

		<?php if (isset($rider->results) && count($rider->results)) : ?>
			<div class="single-rider-results">
				<h3>Results</h3>

				<div class="em-row header">
					<div class="em-col-md-2 race-date">Date</div>
					<div class="em-col-md-5 race-name">Event</div>
					<div class="em-col-md-1 rider-place">Place</div>
					<div class="em-col-md-1 rider-points">Points</div>
					<div class="em-col-md-1 race-class">Class</div>
					<div class="em-col-md-2 race-season">Season</div>
				</div>

				<?php foreach ($rider->results as $result) : ?>
					<div class="em-row">
						<div class="em-col-md-2 race-date"><?php echo date(get_option('date_format'), strtotime($result->date)); ?></div>
						<div class="em-col-md-5 race-name"><a href="<?php uci_results_race_url($result->race_id); ?>"><?php echo $result->event; ?></a></div>
						<div class="em-col-md-1 rider-place"><?php echo $result->place; ?></div>
						<div class="em-col-md-1 rider-points"><?php echo $result->par; ?></div>
						<div class="em-col-md-1 race-class"><?php echo $result->class; ?></div>
						<div class="em-col-md-2 race-season"><?php echo $result->season; ?></div>
					</div>
				<?php endforeach; ?>

			</div>
		<?php else :?>
			<div class="none-found">No results.</div>
		<?php endif; ?>

		<?php if (isset($rider->rankings) && count($rider->rankings)) : ?>
			<div class="single-rider-rankings">
				<h3>Rankings</h3>

				<div class="em-row header">
					<div class="em-col-md-1 rank-week">Week</div>
					<div class="em-col-md-1 rank-points">Points</div>
					<div class="em-col-md-1 rank-place">Place</div>
					<div class="em-col-md-2 rank-season">Season</div>
				</div>

				<?php foreach ($rider->rankings as $rank) : ?>
					<div class="em-row">
						<div class="em-col-md-1 rank-week"><?php echo $rank->week; ?></div>
						<div class="em-col-md-1 rank-points"><?php echo $rank->points; ?></div>
						<div class="em-col-md-1 rank-place"><?php echo $rank->rank; ?></div>
						<div class="em-col-md-2 rank-season"><?php echo $rank->season; ?></div>
					</div>
				<?php endforeach; ?>

			</div>
		<?php else :?>
			<div class="none-found">No rankings.</div>
		<?php endif; ?>

	<?php else : ?>
		<div class="none-found">Rider not found.</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>