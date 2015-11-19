<?php
/**
 * Template Name: Rider Rankings
 *
 * @since 	1.0.8
 */
?>

<?php
global $RiderStats,$wp_query;

$paged=get_query_var('paged',1);
$season=get_query_var('season','2015/2016');
$current_week=uci_get_current_week($season);
$riders=$RiderStats->get_riders_from_weekly_rank(array(
	'season' => $season,
	'paged' => $paged,
	'per_page' => 15,
	'week' => $current_week
));
?>

<?php get_header(); ?>

<div class="container">
	<div class="row content">
		<div class="col-md-12">

			<div class="uci-curl-rider-rankings">
				<h1 class="entry-title">Rider Rankings (<?php echo $season; ?>)</h1>

				<div id="season-rider-rankings" class="season-rider-rankings">
					<div class="header row">
						<div class="rank col-md-1">Rank</div>
						<div class="rider col-md-4">Rider</div>
						<div class="nat col-md-1">Nat</div>
						<div class="uci col-md-1">UCI</div>
						<div class="wcp col-md-1">WCP</div>
						<div class="winning col-md-1">Win %</div>
						<div class="sos col-md-1">SOS</div>
						<div class="total col-md-1">Total</div>
					</div>

					<?php foreach ($riders as $rider) : ?>
						<div class="row">
							<div class="rank col-md-1"><?php echo $rider->rank; ?></div>
							<div class="rider col-md-4"><a href="<?php echo single_rider_link($rider->name,$season); ?>"><?php echo $rider->name; ?></a></div>
							<div class="nat col-md-1"><a href="<?php echo single_country_link($rider->nat,$season); ?>"><?php echo get_country_flag($rider->nat); ?></a></div>
							<div class="uci col-md-1"><?php echo $rider->uci; ?></div>
							<div class="wcp col-md-1"><?php echo $rider->wcp; ?></div>
							<div class="winning col-md-1"><?php echo number_format($rider->win_perc,3); ?></div>
							<div class="sos col-md-1"><?php echo $rider->sos; ?></div>
							<div class="total col-md-1"><?php echo number_format($rider->total,3); ?></div>
						</div>
					<?php endforeach; ?>
				</div>

				<?php uci_curl_pagination(); ?>

			</div><!-- .uci-curl-rider-rankings -->

		</div>
	</div>
</div><!-- .container -->

<?php get_footer(); ?>