<?php
global $uci_results_query, $uci_results_post;

$series=new UCI_Results_Query(array(
	'type' => 'series',
));
?>

<div class="uci-results-admin-series">
	<h2>Series</h2> <a href="<?php echo admin_url('admin.php?page=uci-curl&tab=series&action=update-series'); ?>" class="button add-series">Add Series</a>

	<div class="tablenav top">
		<div class="pagination">
			<?php uci_results_admin_pagination(); ?>
		</div>
	</div>

	<table class="wp-list-table widefat fixed striped uci-results-series">
		<thead>
			<tr>
				<th scope="col" class="series-id">ID</th>
				<th scope="col" class="series-name"><a href="<?php uci_results_admin_url(array('tab' => 'series')); ?>">Name</a></th>
				<th scope="col" class="series-season">Season</th>
				<th scope="col" class="series-actions">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php if ($series->have_posts()) : while ( $series->have_posts() ) : $series->the_post(); ?>
				<tr>
					<td class="series-id"><?php echo $uci_results_post->id; ?></td>
					<td class="series-name"><a href="<?php echo admin_url('admin.php?page=uci-curl&tab=series&action=update-series&series_id='.$uci_results_post->id); ?>"><?php echo $uci_results_post->name; ?></a></td>
					<td class="series-season"><?php echo $uci_results_post->season; ?></td>
					<td class="series-actions"><a href="" class="delete" data-id="<?php echo $uci_results_post->id; ?>"><span class="dashicons dashicons-trash"></span></a></td>
				</tr>
			<?php endwhile; endif; ?>
		</tbody>
	</table>

	<?php uci_results_admin_pagination(); ?>
</div>