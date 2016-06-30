<?php
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

<div class="uci-results main">

	<div class="uci-results-riders">
		<h3>Rider Rankings</h3>

		<div class="table rider-rankings">
			<div class="row header">
				<div class="rider-rank">Rank</div>
				<div class="rider-name">Rider</div>
				<div class="rider-nat">Nat</div>
				<div class="rider-points">Points</div>
			</div>

			<?php if ($riders->have_posts()) : while ( $riders->have_posts() ) : $riders->the_post(); ?>
				<div class="row">
					<div class="rider-rank"><?php echo $uci_results_post->rank; ?></div>
					<div class="rider-name"><a href="<?php uci_results_rider_url($uci_results_post->slug); ?>"><?php echo $uci_results_post->name; ?></a></div>
					<div class="rider-nat"><a href="<?php echo uci_results_country_url($uci_results_post->nat); ?>"><?php echo ucicurl_get_country_flag($uci_results_post->nat); ?></a></div>
					<div class="rider-points"><?php echo $uci_results_post->points; ?></div>
				</div>
			<?php endwhile; endif; ?>
		</div>

		<a class="view-all" href="<?php uci_results_rider_rankings_url(); ?>">View All Riders &raquo;</a>
	</div>


	<div class="uci-results-races">
		<h3>Race Results</h3>

		<div class="table uci-results-races">
			<div class="row header">
					<div class="race-name">Name</div>
					<div class="race-date">Date</div>
					<div class="race-nat">Nat</div>
					<div class="race-class">Class</div>
			</div>

			<?php if ($races->have_posts()) : while ( $races->have_posts() ) : $races->the_post(); ?>
				<div class="row">
					<div class="race-name"><a href="<?php uci_results_race_url($uci_results_post->code); ?>"><?php echo $uci_results_post->name; ?></a></div>
					<div class="race-date"><?php echo $uci_results_post->date; ?></div>
					<div class="race-nat"><?php echo ucicurl_get_country_flag($uci_results_post->nat); ?></div>
					<div class="race-class"><?php echo $uci_results_post->class; ?></div>
				</div>
			<?php endwhile; endif; ?>

		</div>

			<a class="view-all" href="<?php uci_results_races_url(); ?>">View All Races &raquo;</a>
		</div>
	</div>
</div>