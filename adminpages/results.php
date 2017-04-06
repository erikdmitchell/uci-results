<?php global $uci_results_admin; ?>

<div class="uci-results">

	<h2>Results</h2>

	<form class="get-races" name="get-races" method="post">

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="season">Season</label>
					</th>
					<td>
						<select class="url-dd" id="season" name="season">
							<option value="0">Select Year</option>
							<?php foreach ($uci_results_admin->config->urls as $season => $s_url) : ?>
								<option value="<?php echo $season; ?>" data-url="<?php echo $s_url; ?>"><?php echo $season; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>

		<p>
			<input type="button" name="button" id="get-races-curl" class="button button-primary" value="Get Races" />
			<a href="<?php echo admin_url('admin.php?page=uci-results&subpage=results&action=add-csv'); ?>" id="add-results-csv" class="button button-secondary">Upload CSV</a>
		</p>
	</form>

	<div id="get-race-data"></div>

</div>

<div class="loading-modal"></div>