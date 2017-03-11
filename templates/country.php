<?php
/**
 * The template for the single country page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

get_header(); ?>

<div class="uci-results uci-results-country">

	<?php if (empty(get_query_var('country_slug'))) : ?>
		<div class="none-found">No country found.</div>
	<?php else : ?>

		<?php
		$riders=uci_get_riders(array(
			'per_page' => -1,
			'type' => 'riders',
			'nat' => get_query_var('country_slug'),
		));

		// build out three column setup //
		$columns=3;
		$riders_chunk=array_chunk($riders, ceil(count($riders)/$columns));
		?>

		<h1 class="page-title"><?php echo get_query_var('country_slug'); ?><span class="flag"><?php echo uci_results_get_country_flag(get_query_var('country_slug')); ?></span></h1>

		<?php if (count($riders)) : ?>
			<div class="em-row country-riders">
				<?php foreach ($riders_chunk as $key => $chunk) : ?>
				<div class="em-col-md-4 col-<?php echo $key; ?> columns-<?php echo $columns; ?>">
					<?php foreach ($chunk as $arr) : ?>
					<div class="rider-name"><a href="<?php echo uci_results_rider_url($arr->post_name); ?>"><?php echo $arr->post_title; ?></a></div>
					<?php endforeach; ?>
				</div>
				<?php endforeach; ?>
			</div>
		<?php else :?>
			<div class="none-found">No riders from this country found.</div>
		<?php endif; ?>

	<?php endif; ?>

</div>

<?php get_footer(); ?>