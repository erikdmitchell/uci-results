<?php
/**
 * Template Name: Rider Rankings
 *
 * @since 	1.0.0
 * @version	1.0.0
 */
?>

<?php
global $RiderStats;
$riders=$RiderStats->get_riders(); // pass season here if need be
?>

<?php get_header(); ?>


<div class="uci-curl-rider-rankings">
	<h3>Rider Rankings</h3>

	<div id="season-rider-rankings" class="season-rider-rankings">
		<div class="header row">
			<div class="rank col-md-1">Rank</div>
			<div class="rider col-md-3">Rider</div>
			<div class="uci col-md-1">UCI</div>
			<div class="wcp col-md-1">WCP</div>
			<div class="winning col-md-1">Win %*</div>
			<div class="sos col-md-1">SOS</div>
			<div class="total col-md-1">Total</div>
		</div>

		<?php foreach ($riders as $rider) : ?>
			<div class="row">
				<div class="rank col-md-1"><?php echo $rider->rank; ?></div>
				<div class="rider col-md-3"><a href=""><?php echo $rider->rider; ?></a></div>
				<div class="uci col-md-1"><?php echo $rider->uci; ?></div>
				<div class="wcp col-md-1"><?php echo $rider->wcp; ?></div>
				<div class="winning col-md-1"><?php echo number_format($rider->weighted_win_perc,3); ?></div>
				<div class="sos col-md-1"><?php echo $rider->sos; ?></div>
				<div class="total col-md-1"><?php echo number_format($rider->total,3); ?></div>
			</div>
		<?php endforeach; ?>
	</div>

	PAGINATION

</div><!-- .uci-curl-rider-rankings -->

<?php get_footer(); ?>