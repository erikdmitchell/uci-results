<?php
global $ucicurl_races;

$series=$ucicurl_races->get_series_info();
?>

<?php uci_results_admin_notices(); ?>

<div class="uci-results-admin-update-series">
	<h2>Update Series</h2>

	<form name="post" action="" method="post">
		<?php wp_nonce_field('update_series', 'uci_results_admin'); ?>
		<input type="hidden" name="series_id" value="<?php echo $series['id']; ?>" />

		<div class="row">
			<label for="name">Name</label>
			<input type="text" name="name" id="name" class="regular-text" value="<?php echo $series['name']; ?>" />
		</div>
		<div class="row">
			<label for="season">Season</label>
			<?php uci_results_seasons_dropdown('season', $series['season']); ?>
		</div>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary button-large" value="Update">
		</p>
	</form>
</div>