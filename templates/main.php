<?php
global $uci_results_post;

$riders=new UCI_Results_Query(array(
	'per_page' => 10,
	'type' => 'riders',
	'season' => '2014/2015',
	'rankings' => true
));

$races=new UCI_Results_Query(array(
	'per_page' => 10,
	'type' => 'races'
));
?>

<div class="ucicurl-main">
	<div class="ucicurl-riders">
		<h3>Rider Rankings</h3>

		<div class="ucicurl-list">
			<div class="header row">
				<div class="rank">Rank</div>
				<div class="rider">Rider</div>
				<div class="nat">Nat</div>
				<div class="points">Points</div>
			</div>

			<?php if ($riders->have_posts()) : while ( $riders->have_posts() ) : $riders->the_post(); ?>
				<div class="row">
					<div class="rank"><?php echo $uci_results_post->rank; ?></div>
					<div class="rider"><a href="<?php uci_results_rider_url($uci_results_post->slug); ?>"><?php echo $uci_results_post->name; ?></a></div>
					<div class="nat"><a href=""><?php echo ucicurl_get_country_flag($uci_results_post->nat); ?></a></div>
					<div class="points"><?php echo $uci_results_post->points; ?></div>
				</div>
			<?php endwhile; endif; ?>

			<a class="view-all" href="<?php uci_results_rider_rankings_url(); ?>">View All Riders &raquo;</a>
		</div>
	</div>

	<div class="ucicurl-races">
		<h3>Race Results</h3>

		<div class="ucicurl-list">
			<div class="header row">
				<div class="name">Name</div>
				<div class="date">Date</div>
				<div class="nat">Nat</div>
				<div class="class">Class</div>
			</div>

			<?php if ($races->have_posts()) : while ( $races->have_posts() ) : $races->the_post(); ?>
				<div class="row">
					<div class="name"><a href="<?php uci_results_race_url($uci_results_post->code); ?>"><?php echo $uci_results_post->name; ?></a></div>
					<div class="date"><?php echo $uci_results_post->date; ?></div>
					<div class="nat"><?php echo ucicurl_get_country_flag($uci_results_post->nat); ?></div>
					<div class="class"><?php echo $uci_results_post->class; ?></div>
				</div>
			<?php endwhile; endif; ?>

			<a class="view-all" href="<?php uci_results_races_url(); ?>">View All Races &raquo;</a>
		</div>
	</div>
</div>