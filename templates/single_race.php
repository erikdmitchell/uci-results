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
$race=uci_get_races(array(
	'id' => uci_get_race_id(get_query_var('race_code')),
	'results' => true
));
?>

<div class="em-container uci-results uci-results-race">

	<?php if (!$race) : ?>
		<div class="race-results-not-found">Race results not found.</div>
	<?php else : ?>
		<h1 class="page-title"><?php echo $race->post_title; ?><span class="flag"><?php echo uci_results_get_country_flag($race->nat); ?></span></h1>

		<div class="em-row race-details">
			<div class="em-col-md-2 race-date"><?php echo date(get_option('date_format'), strtotime($race->race_date)); ?></div>
			<div class="em-col-md-1 race-class">(<?php echo $race->class; ?>)</div>
		</div>

		<div class="single-race">
			<div class="em-row header">
					<div class="em-col-md-1 rider-place">Place</div>
					<div class="em-col-md-4 rider-name">Rider</div>
					<div class="em-col-md-1 rider-points">Points</div>
					<div class="em-col-md-1 rider-nat">Nat</div>
					<div class="em-col-md-1 rider-age">Age</div>
					<div class="em-col-md-2 rider-time">Time</div>
			</div>

			<?php foreach ($race->results as $result) : ?>
				<div class="em-row rider-results">
					<div class="em-col-md-1 rider-place"><?php echo $result['place']; ?></div>
					<div class="em-col-md-4 rider-name"><a href="<?php echo uci_results_rider_url($result['slug']); ?>"><?php echo $result['name']; ?></a></div>
					<div class="em-col-md-1 rider-points"><?php echo $result['par']; ?></div>
					<div class="em-col-md-1 rider-nat"><a href="<?php echo uci_results_country_url($result['nat']); ?>"><?php echo uci_results_get_country_flag($result['nat']); ?></a></div>
					<div class="em-col-md-1 rider-age"><?php echo $result['age']; ?></div>
					<div class="em-col-md-2 rider-time"><?php echo $result['result']; ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>