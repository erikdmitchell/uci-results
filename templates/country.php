<?php
/**
 * Country (Single)
 *
 * @since 	2.0.0
 */

global $uci_results_post;

$riders=new UCI_Results_Query(array(
	'per_page' => -1,
	'type' => 'riders',
	'season' => uci_results_get_default_rider_ranking_season(),
	'rankings' => true,
	'nat' => get_query_var('country_slug'),
));
?>

<div class="uci-results-country">

	<div class="country-name"><?php echo get_query_var('country_slug'); ?><span class="flag"><?php echo ucicurl_get_country_flag(get_query_var('country_slug')); ?></span></div>

	<?php if ($riders->have_posts()) : ?>
		<table class="wp-list-table widefat fixed striped single-race">
			<thead>
				<tr>
					<th scope="col" class="rider-place">Rank</th>
					<th scope="col" class="rider-name">Name</th>
					<th scope="col" class="rider-points">Points</th>
				</tr>
			</thead>

			<tbody>
				<?php while ( $riders->have_posts() ) : $riders->the_post(); ?>
					<tr>
						<td class="rider-place"><?php echo $uci_results_post->rank; ?></td>
						<td class="rider-name"><a href="<?php echo uci_results_rider_url($uci_results_post->slug); ?>"><?php echo $uci_results_post->name; ?></a></td>
						<td class="rider-points"><?php echo $uci_results_post->points; ?></td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	<?php else :?>
		<div class="none-found">No riders from this country found.</div>
	<?php endif; ?>
</div>