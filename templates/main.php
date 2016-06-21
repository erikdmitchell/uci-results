<?php
global $ucicurl_riders, $uci_results_post;

$riders=$ucicurl_riders->get_rider_rankings(array(
	'per_page' => 10
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

			<?php foreach ($riders as $rider) : ?>
				<div class="row">
					<div class="rank"><?php echo $rider->rank; ?></div>
					<div class="rider"><a href="<?php ucicurl_rider_url($rider->slug); ?>"><?php echo $rider->name; ?></a></div>
					<div class="nat"><a href=""><?php echo ucicurl_get_country_flag($rider->nat); ?></a></div>
					<div class="points"><?php echo $rider->points; ?></div>
				</div>
			<?php endforeach; ?>

			<a class="view-all" href="">View All Riders &raquo;</a>
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
					<div class="name"><a href="<?php ucicurl_race_url($uci_results_post->code); ?>"><?php echo $uci_results_post->name; ?></a></div>
					<div class="date"><?php echo $uci_results_post->date; ?></div>
					<div class="nat"><?php echo ucicurl_get_country_flag($uci_results_post->nat); ?></div>
					<div class="class"><?php echo $uci_results_post->class; ?></div>
				</div>
			<?php endwhile; endif; ?>

			<a class="view-all" href="">View All Races &raquo;</a>
		</div>
	</div>
</div>