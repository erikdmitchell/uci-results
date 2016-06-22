<?php
/**
 * Races
 *
 * @since 2.0.0
 */

global $uci_results_post;

$races=new UCI_Results_Query(array(
	'per_page' => 10,
	'type' => 'races'
));
?>

<div class="uci-results-races">
	<table class="wp-list-table widefat fixed striped uci-results-races">
		<thead>
			<tr>
				<th scope="col" class="race-name">Name</th>
				<th scope="col" class="race-date">Date</th>
				<th scope="col" class="race-nat">Nat</th>
				<th scope="col" class="race-class">Class</th>
			</tr>
		</thead>
		<tbody>
			<?php if ($races->have_posts()) : while ( $races->have_posts() ) : $races->the_post(); ?>
				<tr>
					<td class="race-name"><a href="<?php uci_results_race_url($uci_results_post->code); ?>"><?php echo $uci_results_post->name; ?></a></td>
					<td class="race-date"><?php echo $uci_results_post->date; ?></td>
					<td class="race-nat"><?php echo ucicurl_get_country_flag($uci_results_post->nat); ?></td>
					<td class="race-class"><?php echo $uci_results_post->class; ?></td>
				</tr>
			<?php endwhile; endif; ?>
		</tbody>
	</table>

	<?php uci_results_pagination(); ?>
</div>