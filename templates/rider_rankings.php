<?php
/**
 * template for riders page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

get_header(); ?>

<?php
$riders=uci_get_riders(array(
	'per_page' => 15,
	'ranking' => true,
));
?>

<div class="uci-results uci-results-rider-rankings">

	<h1 class="page-title">Rider Rankings</h1>

	<div class="rider-rankings">
		<div class="em-row header">
			<div class="em-col-md-1 rider-rank">Rank</div>
			<div class="em-col-md-4 rider-name">Rider</div>
			<div class="em-col-md-1 rider-nat">Nat</div>
			<div class="em-col-md-2 rider-points">Points</div>
		</div>

		<?php if ($riders) : foreach ($riders as $rider) : ?>
			<div class="em-row">
				<div class="em-col-md-1 rider-rank"><?php echo $rider->rank->rank; ?></div>
				<div class="em-col-md-4 rider-name"><a href="<?php uci_results_rider_url($rider->post_name); ?>"><?php echo $rider->post_title; ?></a></div>
				<div class="em-col-md-1 rider-nat"><a href="<?php echo uci_results_country_url($rider->nat); ?>"><?php echo uci_results_get_country_flag($rider->nat); ?></a></div>
				<div class="em-col-md-2 rider-points"><?php echo $rider->rank->points; ?></div>
			</div>
		<?php endforeach; endif; ?>

	</div>

	<?php uci_results_pagination(); ?>
</div>

<?php get_footer(); ?>