<?php
global $ucicurl_riders, $ucicurl_races;

$riders=$ucicurl_riders->get_rider_rankings(array(
	'per_page' => 10,
	'season' => '2015/2016'
));
$races=$ucicurl_races->get_races(array(
	'per_page' => 10
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
					<div class="rank"></div>
					<div class="rider"><a href=""><?php echo $rider->name; ?></a></div>
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

			<?php foreach ($races as $race) : ?>
				<div class="row">
					<div class="name"><a href=""><?php echo $race->name; ?></a></div>
					<div class="date"><?php echo $race->date; ?></div>
					<div class="nat"><?php echo ucicurl_get_country_flag($race->nat); ?></div>
					<div class="class"><?php echo $race->class; ?></div>
				</div>
			<?php endforeach; ?>

			<a class="view-all" href="">View All Races &raquo;</a>
		</div>
	</div>
</div>