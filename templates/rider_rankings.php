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
$riders=new UCI_Results_Query(array(
	'per_page' => 15,
	'type' => 'riders',
	'rankings' => true
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

		<?php if ($riders->have_posts()) : while ( $riders->have_posts() ) : $riders->the_post(); ?>
			<div class="em-row">
				<div class="em-col-md-1 rider-rank"><?php echo $uci_results_post->rank; ?></div>
				<div class="em-col-md-4 rider-name"><a href="<?php uci_results_rider_url($uci_results_post->slug); ?>"><?php echo $uci_results_post->name; ?></a></div>
				<div class="em-col-md-1 rider-nat"><a href="<?php echo uci_results_country_url($uci_results_post->nat); ?>"><?php echo uci_results_get_country_flag($uci_results_post->nat); ?></a></div>
				<div class="em-col-md-2 rider-points"><?php echo $uci_results_post->points; ?></div>
			</div>
		<?php endwhile; endif; ?>

	</div>

	<?php uci_results_pagination(); ?>
</div>

<?php get_footer(); ?>