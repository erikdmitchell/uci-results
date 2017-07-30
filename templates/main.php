<?php
/**
 * template for main page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

global $uci_results_post, $uci_rankings;

$riders=$uci_rankings->get_rankings(array(
	//'fields' => 'all',
	//'order' => 'ASC',
	'order_by' => 'rank',
	//'group_by' => '',
	//'date' => '',
	'discipline' => 'road',
	'limit' => 10,
));

$races=uci_get_races(array(
	'per_page' => 10,
));
?>

<div class="uci-results uci-results-main em-container">
	
	<div class="em-row">
		<div class="em-col-md-6">
		
		<div class="uci-results-races">
			<h3>Race Results</h3>
	Filters for Discipline / Date
			<div class="em-row header">
				<div class="em-col-md-6 race-name">Name</div>
				<div class="em-col-md-2 race-date">Date</div>
				<div class="em-col-md-2 race-nat">Nat</div>
				<div class="em-col-md-2 race-class">Class</div>
			</div>
	
			<?php if (count($races)) : foreach ($races as $race) : ?>
				<div class="em-row">
					<div class="em-col-md-6 race-name"><a href="<?php uci_results_race_url($race->post_name); ?>"><?php echo $race->post_title; ?></a></div>
					<div class="em-col-md-2 race-date"><?php echo $race->race_date; ?></div>
					<div class="em-col-md-2 race-nat"><?php echo uci_results_get_country_flag($race->nat); ?></div>
					<div class="em-col-md-2 race-class"><?php echo $race->class; ?></div>
				</div>
			<?php endforeach; endif; ?>
	
			<a class="view-all" href="<?php uci_results_races_url(); ?>">View All Races &raquo;</a>
		</div>
	
		</div>
		
		<div class="em-col-md-6">
			
			<div class="uci-rankings">
				<h3>UCI Rankings</h3>
		Discipline / Date
				<div class="em-row header">
					<div class="em-col-md-1 rider-rank">Rank</div>
					<div class="em-col-md-5 rider-name">Rider</div>
					<div class="em-col-md-1 rider-nat">Nat</div>
					<div class="em-col-md-2 rider-points">Points</div>
				</div>
		
				<?php if (count($riders)) : foreach ($riders as $rider) : ?>
					<div class="em-row">
						<div class="em-col-md-1 rider-rank"><?php echo $rider->rank; ?></div>
						<div class="em-col-md-5 rider-name"><a href="<?php uci_results_rider_url($rider->name); ?>"><?php echo $rider->name; ?></a></div>
						<div class="em-col-md-1 rider-nat"><a href="<?php echo uci_results_country_url($rider->nat); ?>"></a></div>
						<div class="em-col-md-2 rider-points"><?php echo $rider->points; ?></div>
					</div>
				<?php endforeach; endif; ?>
		
				<a class="view-all" href="#">View All Riders &raquo;</a>
			</div>
				
		</div>		
	</div>
	
</div>