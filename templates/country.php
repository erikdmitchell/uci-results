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
	'nat' => get_query_var('country_slug'),
	'order_by' => 'name'
));

// build out three column setup //
$columns=3;
$all_riders=$riders->posts;
$riders_chunk=array_chunk($all_riders, ceil(count($all_riders)/$columns));
?>
<div class="uci-results uci-results-country">

	<h1 class="page-title"><?php echo get_query_var('country_slug'); ?><span class="flag"><?php echo ucicurl_get_country_flag(get_query_var('country_slug')); ?></span><span class="season"><?php echo uci_results_get_default_rider_ranking_season(); ?></span></h1>

	<?php if ($riders->have_posts()) : ?>
		<div class="table country-riders">
			<?php foreach ($riders_chunk as $key => $chunk) : ?>
			<div class="col col-<?php echo $key; ?> columns-<?php echo $columns; ?>">
				<?php foreach ($chunk as $arr) : ?>
				<div class="rider-name"><a href="<?php echo uci_results_rider_url($arr->slug); ?>"><?php echo $arr->name; ?></a></div>
				<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
		</div>
	<?php else :?>
		<div class="none-found">No riders from this country found.</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>