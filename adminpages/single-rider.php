<?php
global $ucicurl_riders;

$rider=$ucicurl_riders->get_rider(array('rider_id' => $attributes['rider_id'], 'results' => true));
?>

<div class="uci-results-admin-single-rider two-col">
	<h2><?php echo $rider->name; ?></h2>

	<form id="update-race-information" method="post" action="">

		<div class="single-race col-right">
			<div class="rider-details postbox uci-results-sidebox">
				<div class="upper-box">
					<h2>Rider Details</h2>
				</div>

				<div class="inner-box">
					<?php wp_nonce_field('update_single_rider_info', 'uci_results_admin'); ?>

					<input type="hidden" name="id" value="<?php echo $attributes['rider_id']; ?>" />

					<div class="row">
						<label for="id">ID</label>
						<?php echo $attributes['rider_id']; ?>
					</div>

					<div class="row">
						<label for="slug">Slug</label>
						<input type="text" name="slug" id="slug" class="slug" value="<?php echo $rider->slug; ?>" />
					</div>

					<div class="row">
						<label for="nat">Nat.</label>
						<input type="text" name="nat" id="nat" class="nat" value="<?php echo $rider->nat; ?>" />
					</div>

					<div class="row">
						<label for="twitter">Twitter</label>
						<input type="text" name="twitter" id="twitter" class="twitter" value="<?php echo $rider->twitter; ?>" />
					</div>

					<div class="action-buttons">
						<input type="submit" id="doaction" class="button action button-primary" value="Update">
					</div>
				</div>
			</div>
		</div>

	</form>

	<div class="col-left">
		<table class="wp-list-table widefat fixed striped pages">
			<thead>
				<tr>
					<th scope="col" class="race-date">Date</th>
					<th scope="col" class="race-name">Event</th>
					<th scope="col" class="rider-place">Place</th>
					<th scope="col" class="rider-points">Points</th>
					<th scope="col" class="race-class">Class</th>
					<th scope="col" class="race-season">Season</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($rider->results as $result) : ?>
					<tr>
						<td class="race-date"><?php echo date(get_option('date_format'), strtotime($result->date)); ?></td>
						<td class="race-name"><a href="<?php echo admin_url('admin.php?page=uci-results&tab=races&race_id='.$result->race_id); ?>"><?php echo $result->event; ?></a></td>
						<td class="rider-place"><?php echo $result->place; ?></td>
						<td class="rider-points"><?php echo $result->par; ?></td>
						<td class="race-class"><?php echo $result->class; ?></td>
						<td class="race-season"><?php echo $result->season; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>