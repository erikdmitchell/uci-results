<?php global $uci_results_admin; ?>

<?php wp_enqueue_media(); ?>

<?php 
$race_id=isset($_POST['race_id']) ? $_POST['race_id'] : '';
$file=isset($_POST['file']) ? $_POST['file'] : '';

if (empty($race_id) && isset($_GET['race_id']))
	$race_id=$_GET['race_id'];
?>

<div class="uci-results">

	<h2>Results - Upload CSV</h2>

	<form class="process-results" name="process-results" method="post">
		<?php wp_nonce_field('add-race-csv', 'uci_results'); ?>
		
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="race-id">Race ID</label>
					</th>
					<td>
						<input type="text" name="race_id" id="race-id" class="small-text" value="<?php echo $race_id ?>" />
						<p>
							<input type="text" name="_race_search" id="race-search" class="regular-text" value="" placeholder="Enter Race Name to Find ID" />
							<div id="race-search-list"></div>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="file">File</label>
					</th>
					<td>
						<input type="text" name="file" id="file" class="regular-text code" value="<?php echo $file; ?>" />
						<input type="button" name="button" id="add-file" class="button button-secondary" value="Add File" />
					</td>
				</tr>				
				
			</tbody>
		</table>

		<p>
			<input type="button" name="button" id="process-results" class="button button-primary" value="Show Results" />
		</p>
	</form>

	<form id="csv-data" name="csv-data-upload" method="post" action="">
		<?php wp_nonce_field('add-csv-data', 'uci_results'); ?>
		<input type="hidden" name="race[race_id]" id="race_id" value="" />
				
		<span id="csv-data-form-table"></span>
		
		<p>
			<input type="submit" name="submit" id="add-results" class="button button-primary" value="Add Results" />
		</p>
	</form>

</div>