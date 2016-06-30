<?php
/**
 * The template for the single country page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

get_header(); ?>

<?php
$riders=new UCI_Results_Query(array(
	'per_page' => -1,
	'type' => 'riders',
	'season' => uci_results_get_default_rider_ranking_season(),
	'rankings' => true,
	'nat' => get_query_var('country_slug'),
));
?>

<div class="uci-results uci-results-country">

	<h1 class="page-title"><?php echo get_query_var('country_slug'); ?><span class="flag"><?php echo ucicurl_get_country_flag(get_query_var('country_slug')); ?></span><span class="season"><?php echo uci_results_get_default_rider_ranking_season(); ?></span></h1>

	<?php if ($riders->have_posts()) : ?>
		<div class="table country-riders">
			<div class="row header">
				<div class="rider-place">Rank</div>
				<div class="rider-name">Name</div>
				<div class="rider-points">Points</div>
			</div>

			<?php while ( $riders->have_posts() ) : $riders->the_post(); ?>
				<div class="row">
					<div class="rider-place"><?php echo $uci_results_post->rank; ?></div>
					<div class="rider-name"><a href="<?php echo uci_results_rider_url($uci_results_post->slug); ?>"><?php echo $uci_results_post->name; ?></a></div>
					<div class="rider-points"><?php echo $uci_results_post->points; ?></div>
				</div>
			<?php endwhile; ?>

		</div>
	<?php else :?>
		<div class="none-found">No riders from this country found.</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>