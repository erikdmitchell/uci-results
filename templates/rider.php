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
$results=$RiderStats->get_rider($rider_name);
$uci=$RiderStats->get_rider_uci_points($rider_name,$season);
$wcp=$RiderStats->get_rider_uci_points($rider_name,$season,'wcp');
$sos=$RiderStats->get_rider_sos($rider_name,$season);
$win_perc=$RiderStats->get_rider_winning_perc($rider_name,$season);
$rider_stats=$RiderStats->get_rider_total($rider_name,$season);
?>

<?php  ?>

<?php get_header(); ?>
<div class="container">
	<div class="row content">
		<div class="col-md-12">

			<?php if (!$results) : ?>
				Rider/results not found.
			<?php else : ?>
				<div class="uci-curl-rider-rankings">
					<h3><?php echo $rider_name; ?> <a href="<?php echo single_country_link($results[0]->country,$season); ?>"><?php echo get_country_flag($results[0]->country); ?></a></h3>

					<div id="season-rider-stats" class="season-rider-stats">
						<div class="row">
							<div class="header col-md-3">Current Rank:</div>
							<div class="current-rank col-md-2"><?php echo $rider_stats->rank; ?> (<?php echo number_format($rider_stats->total,3); ?>)</div>
						</div>
						<div class="row">
							<div class="header col-md-3">UCI Points:</div>
							<div class="current-rank col-md-2"><?php echo $uci->rank; ?> (<?php echo $uci->total; ?>)</div>
						</div>
						<div class="row">
							<div class="header col-md-3">World Cup Points:</div>
							<div class="current-rank col-md-2"><?php echo $wcp->rank; ?> (<?php echo $wcp->total; ?>)</div>
						</div>
						<div class="row">
							<div class="header col-md-3">Winning Percentage:</div>
							<div class="current-rank col-md-2"><?php echo $win_perc->rank; ?> (<?php echo number_format($win_perc->winning_perc,3); ?>)</div>
						</div>
						<div class="row">
							<div class="header col-md-3">Strength of Schedule:</div>
							<div class="current-rank col-md-2"><?php echo $sos->rank; ?> (<?php echo $sos->sos; ?>)</div>
						</div>
					</div><!-- .season-rider-stats -->

					<div id="season-rider-rankings" class="season-rider-rankings">
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
							<div class="points col-md-1"><?php echo $result->par; ?></div>
							<div class="date col-md-2"><?php echo $result->date; ?></div>
							<div class="race col-md-4"><a href="<?php echo single_race_link($result->code); ?>"><?php echo $result->race; ?></a></div>
							<div class="class col-md-1"><?php echo $result->class; ?></div>
							<div class="country col-md-1"><?php echo $result->race_country; ?></div>
							<div class="fq col-md-1"><?php echo $result->fq; ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div><!-- .uci-curl-rider-rankings -->
			<?php endif; ?>
		</div>
	</div>
</div><!-- .container -->

<?php get_footer(); ?>