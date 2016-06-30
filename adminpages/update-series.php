<?php
global $ucicurl_races;

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
</div>

<?php
$races=new UCI_Results_Query(array(
	'type' => 'races',
	'meta' => array(
		array(
			'table' => 'races',
			'field' => 'series_id',
			'value' => $series['id']
		)
	)
));

print_r($races);
?>