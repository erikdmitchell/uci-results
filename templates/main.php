<?php
/**
 * template for main page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

global $uci_results_post;

$riders=uci_get_riders(array(
	'per_page' => 10,
	'ranking' => true
));

$races=uci_get_races(array(
	'per_page' => 10,
));
?>
<pre>
	<?php print_r($races); ?>
</pre>
<div class="uci-results uci-results-main">
	<div class="uci-results-riders">
		<h3>Rider Rankings</h3>

		<div class="em-row header">
			<div class="em-col-md-1 rider-rank">Rank</div>
			<div class="em-col-md-5 rider-name">Rider</div>
			<div class="em-col-md-1 rider-nat">Nat</div>
			<div class="em-col-md-2 rider-points">Points</div>
		</div>

		<?php if (count($riders)) : foreach ($riders as $rider) : ?>
			<div class="em-row">
				<div class="em-col-md-1 rider-rank"><?php echo $rider->rank->rank; ?></div>
				<div class="em-col-md-5 rider-name"><a href="<?php uci_results_rider_url($rider->post_name); ?>"><?php echo $rider->post_title; ?></a></div>
				<div class="em-col-md-1 rider-nat"><a href="<?php echo uci_results_country_url($rider->nat); ?>"><?php echo uci_results_get_country_flag($rider->nat); ?></a></div>
				<div class="em-col-md-2 rider-points"><?php echo $rider->rank->points; ?></div>
			</div>
		<?php endforeach; endif; ?>

		<a class="view-all" href="<?php uci_results_rider_rankings_url(); ?>">View All Riders &raquo;</a>
	</div>

	<div class="uci-results-races">
		<h3>Race Results</h3>

		<div class="em-row header">
			<div class="em-col-md-7 race-name">Name</div>
			<div class="em-col-md-3 race-date">Date</div>
			<div class="em-col-md-1 race-nat">Nat</div>
			<div class="em-col-md-1 race-class">Class</div>
		</div>

		<?php if (count($races)) : foreach ($races as $race) : ?>
			<div class="em-row">
				<div class="em-col-md-7 race-name"><a href="<?php uci_results_race_url($race->post_name); ?>"><?php echo $race->post_title; ?></a></div>
				<div class="em-col-md-3 race-date"><?php echo $race->race_date; ?></div>
				<div class="em-col-md-1 race-nat"><?php echo uci_results_get_country_flag($race->nat); ?></div>
				<div class="em-col-md-1 race-class"><?php echo $race->class; ?></div>
			</div>
		<?php endforeach; endif; ?>

		<a class="view-all" href="<?php uci_results_races_url(); ?>">View All Races &raquo;</a>
	</div>
</div>