<?php
/**
 * template for main page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

global $uci_results_post;

$riders=new UCI_Results_Query(array(
	'per_page' => 10,
	'type' => 'riders',
	'rankings' => true
));

$races=new UCI_Results_Query(array(
	'per_page' => 10,
	'type' => 'races'
));
?>

<div class="uci-results">
	<div class="uci-results-riders">
		<h3>Rider Rankings</h3>

		<div class="em-row header">
			<div class="em-col-md-1 rider-rank">Rank</div>
			<div class="em-col-md-5 rider-name">Rider</div>
			<div class="em-col-md-1 rider-nat">Nat</div>
			<div class="em-col-md-2 rider-points">Points</div>
		</div>

		<?php if ($riders->have_posts()) : while ( $riders->have_posts() ) : $riders->the_post(); ?>
			<div class="em-row">
				<div class="em-col-md-1 rider-rank"><?php echo $uci_results_post->rank; ?></div>
				<div class="em-col-md-5 rider-name"><a href="<?php uci_results_rider_url($uci_results_post->slug); ?>"><?php echo $uci_results_post->name; ?></a></div>
				<div class="em-col-md-1 rider-nat"><a href="<?php echo uci_results_country_url($uci_results_post->nat); ?>"><?php echo ucicurl_get_country_flag($uci_results_post->nat); ?></a></div>
				<div class="em-col-md-2 rider-points"><?php echo $uci_results_post->points; ?></div>
			</div>
		<?php endwhile; endif; ?>

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

		<?php if ($races->have_posts()) : while ( $races->have_posts() ) : $races->the_post(); ?>
			<div class="em-row">
				<div class="em-col-md-7 race-name"><a href="<?php uci_results_race_url($uci_results_post->code); ?>"><?php echo $uci_results_post->name; ?></a></div>
				<div class="em-col-md-3 race-date"><?php echo $uci_results_post->date; ?></div>
				<div class="em-col-md-1 race-nat"><?php echo ucicurl_get_country_flag($uci_results_post->nat); ?></div>
				<div class="em-col-md-1 race-class"><?php echo $uci_results_post->class; ?></div>
			</div>
		<?php endwhile; endif; ?>

		<a class="view-all" href="<?php uci_results_races_url(); ?>">View All Races &raquo;</a>
	</div>
</div>