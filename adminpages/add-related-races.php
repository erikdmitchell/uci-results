<div class="add-related-race">

	<div class="search-form">
		<label for="search-related-races">Search Races</label>
		<input type="text" name="search-related-races" id="search-related-races" class="regular-text">

		<input type="hidden" id="race_id" name="race_id" value="">
	</div>

	<div class="races-list">

		<h3><?php echo $race->event; ?></h3>

		<form name="add-races" method="post" action="">
			<table class="wp-list-table widefat fixed striped pages">
				<thead>
					<tr>
						<td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>
						<th scope="col" class="race-date">Date</th>
						<th scope="col" class="race-name">Event</th>
						<th scope="col" class="race-nat">Nat.</th>
						<th scope="col" class="race-class">Class</th>
						<th scope="col" class="race-season">Season</th>
					</tr>
				</thead>
				<tbody>
					<?php if ($related_races) : foreach ($related_races as $race) : ?>
						<tr>
							<th scope="row" class="check-column"><input id="cb-select-<?php echo $race->id; ?>" type="checkbox" name="races[]" value="<?php echo $race->id; ?>" checked="checked"></th>
							<td class="race-date"><?php echo date(get_option('date_format'), strtotime($race->date)); ?></td>
							<td class="race-name"><?php echo $race->event; ?></td>
							<td class="race-nat"><?php echo $race->nat; ?></td>
							<td class="race-class"><?php echo $race->class; ?></td>
							<td class="race-season"><?php echo $race->season; ?></td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
				<tbody id="related-races-search-results"></tbody>
			</table>

			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="ADD Races"></p>
		</form>

	</div>
</div>