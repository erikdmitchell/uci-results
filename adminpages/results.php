<?php
global $uci_results_admin_pages;

$season=null;
$url=null;
$limit=null;
?>
<div class="uci-results">

	<h2>Results (cURL)</h2>

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
							<?php foreach ($uci_results_admin_pages->config->urls as $season => $s_url) : ?>
								<option value="<?php echo $season; ?>" data-url="<?php echo $s_url; ?>"><?php echo $season; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="url">URL</label>
					</th>
					<td>
						<textarea class="url" id="url" name="url" rows="5" cols="100" readonly><?php echo $url; ?></textarea>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="limit">Limit</label>
					</th>
					<td>
						<input class="small-text" type="text" name="limit" id="limit" value="<?php echo $limit; ?>" />
						<span class="description">Optional</span>
					</td>
				</tr>

			</tbody>
		</table>

		<p>
			<input type="button" name="button" id="get-races-curl" class="button button-primary" value="Get Races" />
		</p>
	</form>

	<div id="get-race-data"></div>

</div>

<div class="loading-modal"></div>