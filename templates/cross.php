<?php
/**
 * Template Name: Cross (UCI)
 *
 * @since 	1.0.8
 */
?>

<?php
global $RiderStats,$RaceStats;

$season=get_query_var('season','2015/2016');
$current_week=uci_get_current_week($season);
$rider_results=$RiderStats->get_riders_from_weekly_rank(array(
	'per_page' => 10,
	'season' => $season,
	'week' => $current_week
));
$race_results=$RaceStats->get_races(array(
	'per_page' => 10
));
?>

<?php get_header(); ?>

<div class="container">
	<div class="row content">
		<div class="col-md-12">
			<?php the_title( '<h1 class="entry-title">', '</h1>' );	?>

				<div class="uci-curl-cross-rankings row">
					<div class="rider-rankings-list col-md-6">
						<h3>Rider Rankings</h3>
						<div class="header row">
							<div class="rank col-md-1">Rank</div>
							<div class="rider col-md-5">Rider</div>
							<div class="nat col-md-1">Nat</div>
							<div class="total col-md-1">Total</div>
						</div>

						<?php foreach ($rider_results as $rider) : ?>
							<div class="row">
								<div class="rank col-md-1"><?php echo $rider->rank; ?></div>
								<div class="rider col-md-5"><a href="<?php echo single_rider_link($rider->name,$season); ?>"><?php echo $rider->name; ?></a></div>
								<div class="nat col-md-1"><a href="<?php echo single_country_link($rider->nat); ?>"><?php echo get_country_flag($rider->nat); ?></a></div>
								<div class="total col-md-1"><?php echo number_format($rider->total,3); ?></div>
							</div>
						<?php endforeach; ?>
						<a class="view-all" href="/uci-cross-rankings/rider-rankings/">View All Riders &raquo;</a>

						<div class="row">
							<div class="col-md-4"><strong>Previous Seasons</strong></div>
							<div class="col-md-3">
								<select name="previous-rider-seasons" id="previous-rider-seasons" class="riders">
									<option value="">Select One</option>
									<?php foreach ($rider_seasons as $season) : ?>
										<option value="<?php echo $season; ?>"><?php echo $season; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
					<div class="race-rankings-list col-md-6">
						<h3>Race Rankings</h3>
						<div id="season-race-rankings" class="season-race-rankings">
							<div class="header row">
								<div class="name col-md-7">Name</div>
								<div class="date col-md-3">Date</div>
								<div class="nat col-md-1">Nat</div>
								<div class="class col-md-1">Class</div>
<!-- 								<div class="fq col-md-1">FQ</div> -->
							</div>

							<?php foreach ($race_results as $race) : ?>
								<div class="row">
									<div class="name col-md-7"><a href="<?php echo single_race_link($race->code); ?>"><?php echo $race->name; ?></a></div>
									<div class="date col-md-3"><?php echo $race->date; ?></div>
									<div class="nat col-md-1"><?php echo get_country_flag($race->nat); ?></div>
									<div class="class col-md-1"><?php echo $race->class; ?></div>
<!-- 									<div class="fq col-md-1"><?php echo $race->fq; ?></div> -->
								</div>
							<?php endforeach; ?>
							<a class="view-all" href="/uci-cross-rankings/race-rankings/">View All Races &raquo;</a>

							<div class="row">
								<div class="col-md-4"><strong>Previous Seasons</strong></div>
								<div class="col-md-3">
									<select name="previous-race-seasons" id="previous-race-seasons" class="races">
										<option value="">Select One</option>
										<?php foreach ($rider_seasons as $season) : ?>
											<option value="<?php echo $season; ?>"><?php echo $season; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div><!-- .uci-curl-rider-rankings -->
		</div>
	</div>
</div><!-- .container -->

<?php get_footer(); ?>