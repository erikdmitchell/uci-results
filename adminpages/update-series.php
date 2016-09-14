<?php
global $ucicurl_races, $uci_results_post;

$series=$ucicurl_races->get_series_info();
?>

<?php uci_results_admin_notices(); ?>

<div class="uci-results-admin-update-series">
	<h2>Update Series</h2>

	<a href="<?php uci_results_admin_url(array('tab' => 'series', 'action' => 'update-series')); ?>" class="button add-series">Add Series</a>

	<form name="post" action="" method="post">
		<?php wp_nonce_field('update_series', 'uci_results_admin'); ?>
		<input type="hidden" name="series_id" value="<?php echo $series['id']; ?>" />

		<div class="row">
			<label for="name">Name</label>
			<input type="text" name="name" id="name" class="regular-text" value="<?php echo $series['name']; ?>" />
		</div>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary button-large" value="Update">
		</p>
	</form>

	<?php if (!empty($series['id'])) : ?>
		<?php
		$races=new UCI_Results_Query(array(
			'type' => 'races',
			'meta' => array(
				array(
					//'table' => 'races',
					'field' => 'series_id',
					'value' => $series['id']
				)
			)
		));
		?>

		<div class="races-in-series">
			<h2>Races in Series</h2>

			<table class="wp-list-table widefat fixed striped races">
				<thead>
					<tr>
						<th scope="col" class="race-date">Date</th>
						<th scope="col" class="race-name">Event</th>
						<th scope="col" class="race-nat">Nat.</th>
						<th scope="col" class="race-class">Class</th>
						<th scope="col" class="race-season">Season</th>
					</tr>
				</thead>
				<tbody>
					<?php if ($races->have_posts()) : while ( $races->have_posts() ) : $races->the_post(); ?>
						<tr>
							<td class="race-date"><?php echo $uci_results_post->date; ?></td>
							<td class="race-name"><a href="<?php echo admin_url('admin.php?page=uci-results&tab=races&race_id='.$uci_results_post->id); ?>"><?php echo $uci_results_post->event; ?></a></td>
							<td class="race-nat"><?php echo $uci_results_post->nat; ?></td>
							<td class="race-class"><?php echo $uci_results_post->class; ?></td>
							<td class="race-season"><?php echo $uci_results_post->season; ?></td>
						</tr>
					<?php endwhile; endif; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

</div>