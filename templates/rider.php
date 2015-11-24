<?php
/**
 * Template Name: Rider (Single)
 *
 * @since 	1.0.8
 */
?>

<?php
global $RiderStats,$wp_query;

$rider_name=get_query_var('rider',0);
$season=get_query_var('season','2015/2016');
$rider=$RiderStats->get_riders(array(
	'season' => $season,
	'name' => $rider_name
));
$results=$RiderStats->get_rider_results(array(
	'season' => $season,
	'name' => $rider_name
));
?>

<?php get_header(); ?>
<div class="container">
	<div class="row content">
		<div class="col-md-12">

			<?php if (!$results) : ?>
				Rider/results not found.
			<?php else : ?>
				<div class="uci-curl-rider-rankings">
					<h1 class="entry-title"><?php echo $rider_name; ?> <a href="<?php echo single_country_link($riders[0]->nat,$season); ?>"><?php echo get_country_flag($riders[0]->nat); ?></a></h1>
					<div class="row">
						<div class="col-md-4">
							<div class="row">
								<div id="season-rider-stats" class="col-md-12 season-rider-stats">
									<h4>Rider Rankings</h4>
									<div class="row">
										<div class="header col-md-7">Current Rank:</div>
										<div class="current-rank col-md-2"><?php echo $rider->rank; ?> <span class="total">(<?php echo $rider->total; ?>)</span></div>
									</div>
									<div class="row">
										<div class="header col-md-7">UCI Points:</div>
										<div class="uci-points col-md-2"><?php echo $rider->uci_total; ?> <span class="rank">(<?php echo $rider->uci_toal; ?>)</span></div>
									</div>
									<div class="row">
										<div class="header col-md-7">World Cup Points:</div>
										<div class="world-cup-points col-md-2"><?php echo $rider->wcp_total; ?> <span class="rank">(<?php echo $rider->wcp_total; ?>)</span></div>
									</div>
									<div class="row">
										<div class="header col-md-7">Winning Percentage:</div>
										<div class="winning-percent col-md-2"><?php echo $rider->win_perc; ?> <span class="rank">(<?php echo $rider->win_perc; ?>)</span></div>
									</div>
									<div class="row">
										<div class="header col-md-7">Strength of Schedule:</div>
										<div class="sos col-md-2"><?php echo $rider->sos_rank; ?> <span class="total">(<?php echo $rider->sos; ?>)</span></div>
									</div>
								</div>
							</div><!-- .season-rider-stats -->
							<div class="row">
								<div class="col-md-12 charts">
									<div id="uci-points-wrap" class="row">
										<div class="col-md-12">
											<div class="chart-title">UCI Points</div>
											<canvas id="uci-points"></canvas>
											<div id="uci-points-chart-legend" class=""></div>
										</div>
									</div>
								</div>
							</div>
						</div><!-- .col-md-4 -->
						<div id="rider-graphs" class="col-md-8 charts">
<!--
							<div id="weekly-rankings-wrap">
								<div class="chart-title">Weekly Rank</div>
								<canvas id="weekly-rankings"></canvas>
								<div id="chartjs-tooltip"></div>
							</div>
-->
						</div><!-- #rider-graphs -->
					</div><!-- .row -->
					<div id="season-rider-rankings" class="season-rider-rankings">
						<h3>Results</h3>
						<div class="header row">
							<div class="place col-md-1">Place</div>
							<div class="points col-md-1">Points</div>
							<div class="date col-md-2">Date</div>
							<div class="race col-md-4">Race</div>
							<div class="class col-md-1">Class</div>
							<div class="country col-md-1">Country</div>
							<div class="fq col-md-1">FQ</div>
						</div>

						<?php foreach ($results as $result) : ?>
							<div class="row">
								<div class="place col-md-1"><?php echo $result->place; ?></div>
								<div class="points col-md-1"><?php echo $result->points; ?></div>
								<div class="date col-md-2"><?php echo $result->date; ?></div>
								<div class="race col-md-4"><a href="<?php echo single_race_link($result->code); ?>"><?php echo $result->event; ?></a></div>
								<div class="class col-md-1"><?php echo $result->class; ?></div>
								<div class="country col-md-1"><?php echo get_country_flag($result->race_country); ?></div>
								<div class="fq col-md-1"><?php echo round($result->fq); ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div><!-- .uci-curl-rider-rankings -->
			<?php endif; ?>
		</div>
	</div>
</div><!-- .container -->

<?php get_footer(); ?>