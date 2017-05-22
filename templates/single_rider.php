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
global $uci_riders;

$rider=$uci_riders->get_rider(array(
	'rider_id' => get_query_var('rider_slug'),
	'results' => true,
	'results_season' => '',
	'ranking' => true,
	'stats' => true
));
?>

<pre>
	<?php print_r($rider->stats); ?>
	<?php print_r($rider->rank); ?>
</pre>

<div class="em-container uci-results uci-results-rider">

	<?php if ($rider) : ?>
		<div id="rider-<?php echo $rider->ID; ?>" class="em-row rider-stats">
			<div class="em-col-md-4 general">
				<h1 class="page-title"><?php echo $rider->post_title; ?></h1>

				<div class="country"><span class="">Nationality:</span> <a href="<?php echo uci_results_country_url($rider->nat); ?>"><?php echo uci_results_get_country_flag($rider->nat); ?></a></div>
			</div>
			
			<?php foreach ($rider->stats as $slug => $stats) : ?>
				<?php echo uci_results_stats_info($slug)->name; ?>
				<div class="rank"><span class="">Ranking:</span> <?php echo $rider->rank; ?></div>
				<div class="em-col-md-4 championships">
					<h4>Championships</h4>
	
					<div class="world-titles"><span class="">World Titles:</span> <?php uci_results_display_total($stats->world_champs); ?></div>
					<div class="world-cup-titles"><span class="">World Cup Titles:</span> <?php uci_results_display_total($stats->world_cup_titles); ?></div>
					<div class="superprestige-titles"><span class="">Superprestige Titles:</span> <?php uci_results_display_total($stats->superprestige_titles); ?></div>
					<div class="bpost-bank-titles"><span class="">Gva/BPost Bank Titles:</span> <?php uci_results_display_total($stats->gva_bpost_bank_titles); ?></div>
				</div>
				<div class="em-col-md-4 top-results">
					<h4>Top Results</h4>
	
					<div class="wins"><span class="">Wins:</span> <?php uci_results_display_total($stats->wins); ?></div>
					<div class="podiums"><span class="">Podiums:</span> <?php uci_results_display_total($stats->podiums); ?></div>
					<div class="world-cup-wins"><span class="">World Cup Wins:</span> <?php uci_results_display_total($stats->world_cup_wins); ?></div>
					<div class="superprestige-wins"><span class="">Superprestige Wins:</span> <?php uci_results_display_total($stats->superprestige_wins); ?></div>
					<div class="bpost-bank-wins"><span class="">GvA/BPost Bank Wins:</span> <?php uci_results_display_total($stats->gva_bpost_bank_wins); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

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
						<div class="em-col-md-2 race-date"><?php echo date(get_option('date_format'), strtotime($result['race_date'])); ?></div>
						<div class="em-col-md-5 race-name"><a href="<?php uci_results_race_url($result['race_id']); ?>"><?php echo $result['race_name']; ?></a></div>
						<div class="em-col-md-1 rider-place"><?php echo $result['place']; ?></div>
						<div class="em-col-md-1 rider-points"><?php echo $result['par']; ?></div>
						<div class="em-col-md-1 race-class"><?php echo $result['race_class']; ?></div>
						<div class="em-col-md-2 race-season"><?php echo $result['race_season']; ?></div>
					</div>
				<?php endforeach; ?>

			</div>
		<?php else :?>
			<div class="none-found">No results.</div>
		<?php endif; ?>

	<?php else : ?>
		<div class="none-found">Rider not found.</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>