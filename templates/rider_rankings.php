<?php
/**
 * div e template for riders page
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

	<h1 class="page-title">Riders</h1>

	<div class="table rider-rankings">
		<div class="row header">
			<div class="rider-rank">Rank</div>
			<div class="rider-name">Rider</div>
			<div class="rider-nat">Nat</div>
			<div class="rider-points">Points</div>
		</div>

		<?php if ($riders->have_posts()) : while ( $riders->have_posts() ) : $riders->the_post(); ?>
			<div class="row">
				<div class="rider-rank"><?php echo $uci_results_post->rank; ?></div>
				<div class="rider-name"><a href="<?php uci_results_rider_url($uci_results_post->slug); ?>"><?php echo $uci_results_post->name; ?></a></div>
				<div class="rider-nat"><a href="<?php echo uci_results_country_url($uci_results_post->nat); ?>"><?php echo ucicurl_get_country_flag($uci_results_post->nat); ?></a></div>
				<div class="rider-points"><?php echo $uci_results_post->points; ?></div>
			</div>
		<?php endwhile; endif; ?>

	</div>

	<?php uci_results_pagination(); ?>
</div>

<?php get_footer(); ?>