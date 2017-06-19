<?php global $uci_results_admin; ?>

<div class="uci-results">

	<h2>Add Results</h2>

	<form class="get-races" name="get-races" method="post">

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="season">Season</label>
					</th>
					<td>
						<?php $uci_results_admin->race_urls_dropdown(); ?>
					</td>
				</tr>
			</tbody>
		</table>

		<p>
			<input type="button" name="button" id="get-races-curl" class="button button-primary" value="Get Races" />
			<a href="<?php echo admin_url('admin.php?page=uci-results&subpage=results&action=add-csv'); ?>" id="add-results-csv" class="button button-secondary">Upload CSV</a>
		</p>
		
		<input type="hidden" name="discipline" value="cyclocross" />
	</form>

	<div id="get-race-data"></div>

</div>

<div class="loading-modal"></div>