<?php
/**
 * template for main page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

global $uci_results_post, $uci_rankings;

$selected_discipline=89;
$selected_date=$uci_rankings->recent_date($selected_discipline);

$riders=$uci_rankings->get_rankings(array(
	'order_by' => 'rank',
	'discipline' => $selected_discipline,
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
				
				<div class="row filters">
					<div class="em-col-md-3">
						
						<select name="discipline" id="uci-rankings-discipline">
							
							<option value="0">Select Discipline</option>
							
							<?php foreach ($uci_rankings->disciplines() as $discipline) : ?>
								<option value="<?php echo $discipline->id; ?>" <?php selected($selected_discipline, $discipline->id, true); ?>><?php echo $discipline->discipline; ?></option>
							<?php endforeach; ?>
						</select>
						
					</div>
					<div class="em-col-md-3">

						<select name="date" id="uci-rankings-date">
							
							<option value="0">Select Date</option>
							
							<?php foreach ($uci_rankings->get_rankings_dates($selected_discipline) as $date) : ?>
								<option value="<?php echo $date->date; ?>" <?php selected($selected_date, $date->date, true); ?>><?php echo $date->date; ?></option>
							<?php endforeach; ?>
						</select>

					</div>
				</div>

				<div class="em-row header">
					<div class="em-col-md-1 rider-rank">Rank</div>
					<div class="em-col-md-5 rider-name">Rider</div>
					<div class="em-col-md-1 rider-nat">Nat</div>
					<div class="em-col-md-2 rider-points">Points</div>
				</div>
		
				<div class="riders-list-wrap">
					<?php if (count($riders)) : foreach ($riders as $rider) : ?>
						<?php echo uci_get_template_part('uci-rankings-rider-row', $rider); ?>
					<?php endforeach; endif; ?>
				</div>
		
				<a class="view-all" href="#">View All Riders &raquo;</a>
			</div>
				
		</div>		
	</div>
	
</div>