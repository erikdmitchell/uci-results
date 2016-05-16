<?php
global $ucicurl_admin;

$url=null;
$limit=null;
?>
		<div class="uci-curl">

			<h3>cURL</h3>

			<form class="get-races" name="get-races" method="post">

				<div class="row">
					<label for="url-dd" class="col-md-1">Season</label>
					<div class="col-md-2">
						<select class="url-dd" id="get-race-season" name="url">
							<option value="0">Select Year</option>
							<?php foreach ($ucicurl_admin->config->urls as $season => $s_url) : ?>
								<option value="<?php echo $s_url; ?>" <?php selected($url, $s_url); ?>><?php echo $season; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div><!-- .row -->

				<div class="row">
					<label for="url" class="col-md-1">URL</label>
					<div class="col-md-11">
						<textarea class="url" id="url" name="url" readonly><?php echo $url; ?></textarea>
					</div>
				</div><!-- .row -->

				<div class="row">
					<label for="limit" class="col-md-1">Limit</label>
					<div class="col-md-2">
						<input class="small-text" type="text" name="limit" id="limit" value="<?php echo $limit; ?>" />
						<span class="description">Optional</span>
					</div>
				</div><!-- .row -->
				<p>
					<input type="button" name="button" id="get-races-curl" class="button button-primary" value="Get Races" />
				</p>
			</form>

			<div id="get-race-data"></div>

		</div>

		<div class="loading-modal"></div>