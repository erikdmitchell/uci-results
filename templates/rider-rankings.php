<?php
/**
 * Rider Rankings
 *
 * @since 	2.0.0
 */

global $uci_results_post;

$riders=new UCI_Results_Query(array(
	'per_page' => 10,
	'type' => 'riders',
	'rankings' => true
));
?>

<div class="uci-results-rider-rankings">
	<table class="wp-list-table widefat fixed striped uci-results-rider-rankings">
		<thead>
			<tr>
				<th scope="col" class="rider-rank">Rank</th>
				<th scope="col" class="rider-name">Rider</th>
				<th scope="col" class="rider-nat">Nat</th>
				<th scope="col" class="rider-points">Points</th>
			</tr>
		</thead>
		<tbody>
			<?php if ($riders->have_posts()) : while ( $riders->have_posts() ) : $riders->the_post(); ?>
				<tr>
					<td class="rider-rank"><?php echo $uci_results_post->rank; ?></td>
					<td class="rider-name"><a href="<?php uci_results_rider_url($uci_results_post->slug); ?>"><?php echo $uci_results_post->name; ?></a></td>
					<td class="rider-nat"><a href=""><?php echo ucicurl_get_country_flag($uci_results_post->nat); ?></a></td>
					<td class="rider-points"><?php echo $uci_results_post->points; ?></td>
				</tr>
			<?php endwhile; endif; ?>
		</tbody>
	</table>

	<?php uci_results_pagination(); ?>
</div>