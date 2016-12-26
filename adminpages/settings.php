<?php global $uci_results_pages;	?>

<div class="uci-results-settings">

	<form action="" method="post">
		<input type="hidden" name="save_settings" value="1" />

		<section class="general">
			<h2>General</h2>

			<table class="form-table">
				<tbody>

					<tr>
						<th scope="row">
							<label form="current_season">Current Season</label>
						</th>
						<td>
							<?php uci_results_seasons_dropdown('current_season', get_option('uci_results_current_season', 0)); ?>
						</td>
					</tr>

				</tbody>
			</table>
		</section>

		<section class="pages">
		  <h2>Pages</h2>

		  <p>Manage the WordPress pages assigned to each UCI Results page.</p>

		  <table class="form-table">
		  	<tbody>

		      <tr>
		      	<th scope="row" valign="top">
							<label for="single_rider_page_id">Single Rider Page:</label>
						</th>
						<td>
							<?php wp_dropdown_pages(array(
								'name' => 'single_rider_page_id',
								'show_option_none' => '-- '.__('Select One', 'ucicurl').' --',
								'selected' => $uci_results_pages['single_rider']
							)); ?>
							<a target="_blank" href="<?php echo admin_url('post.php?post='.$uci_results_pages['single_rider'].'&action=edit'); ?>" class="button button-secondary">Edit Page</a>
							&nbsp;
							<a target="_blank" href="<?php echo get_permalink($uci_results_pages['single_rider']); ?>" class="button button-secondary">View Page</a>
						</td>
					</tr>

		      <tr>
		      	<th scope="row" valign="top">
							<label for="single_race_page_id">Single Race Page:</label>
						</th>
						<td>
							<?php wp_dropdown_pages(array(
								'name' => 'single_race_page_id',
								'show_option_none' => '-- '.__('Select One', 'ucicurl').' --',
								'selected' => $uci_results_pages['single_race']
							)); ?>
							<a target="_blank" href="<?php echo admin_url('post.php?post='.$uci_results_pages['single_race'].'&action=edit'); ?>" class="button button-secondary">Edit Page</a>
							&nbsp;
							<a target="_blank" href="<?php echo get_permalink($uci_results_pages['single_race']); ?>" class="button button-secondary">View Page</a>
						</td>
					</tr>

		      <tr>
		      	<th scope="row" valign="top">
							<label for="country_page_id">Country Page:</label>
						</th>
						<td>
							<?php wp_dropdown_pages(array(
								'name' => 'country_page_id',
								'show_option_none' => '-- '.__('Select One', 'ucicurl').' --',
								'selected' => $uci_results_pages['country']
							)); ?>
							<a target="_blank" href="<?php echo admin_url('post.php?post='.$uci_results_pages['country'].'&action=edit'); ?>" class="button button-secondary">Edit Page</a>
							&nbsp;
							<a target="_blank" href="<?php echo get_permalink($uci_results_pages['country']); ?>" class="button button-secondary">View Page</a>
						</td>
					</tr>

		      <tr>
		      	<th scope="row" valign="top">
							<label for="rider_rankings_page_id">Rider Rankings Page:</label>
						</th>
						<td>
							<?php wp_dropdown_pages(array(
								'name' => 'rider_rankings_page_id',
								'show_option_none' => '-- '.__('Select One', 'ucicurl').' --',
								'selected' => $uci_results_pages['rider_rankings']
							)); ?>
							<a target="_blank" href="<?php echo admin_url('post.php?post='.$uci_results_pages['rider_rankings'].'&action=edit'); ?>" class="button button-secondary">Edit Page</a>
							&nbsp;
							<a target="_blank" href="<?php echo get_permalink($uci_results_pages['rider_rankings']); ?>" class="button button-secondary">View Page</a>
						</td>
					</tr>

		      <tr>
		      	<th scope="row" valign="top">
							<label for="races_page_id">Races Page:</label>
						</th>
						<td>
							<?php wp_dropdown_pages(array(
								'name' => 'races_page_id',
								'show_option_none' => '-- '.__('Select One', 'ucicurl').' --',
								'selected' => $uci_results_pages['races']
							)); ?>
							<a target="_blank" href="<?php echo admin_url('post.php?post='.$uci_results_pages['races'].'&action=edit'); ?>" class="button button-secondary">Edit Page</a>
							&nbsp;
							<a target="_blank" href="<?php echo get_permalink($uci_results_pages['races']); ?>" class="button button-secondary">View Page</a>
						</td>
					</tr>

		      <tr>
		      	<th scope="row" valign="top">
							<label for="uci_results_search_page_id">Search Page:</label>
						</th>
						<td>
							<?php wp_dropdown_pages(array(
								'name' => 'uci_results_search_page_id',
								'show_option_none' => '-- '.__('Select One', 'uci-results').' --',
								'selected' => $uci_results_pages['search']
							)); ?>
							<a target="_blank" href="<?php echo admin_url('post.php?post='.$uci_results_pages['search'].'&action=edit'); ?>" class="button button-secondary">Edit Page</a>
							&nbsp;
							<a target="_blank" href="<?php echo get_permalink($uci_results_pages['search']); ?>" class="button button-secondary">View Page</a>
						</td>
					</tr>
					
					<tr>
						<th scope="row" valign="top">
							<label for="template_disable">Disable Templates</label>
						</th>
						<td>
							<input type="checkbox" name="template_disable" id="template_disable" value="1" <?php checked(get_option('uci_results_template_disable', 0), 1, true); ?> />
							<p class="description">
								When logged in as an admin, the default templates will be shown. Custom templates will be ignored.
							</p>
						</td>
					</tr>

				</tbody>
			</table>
		</section>

		<section class="general">
			<h2>Updates to Twitter</h2>

			<table class="form-table">
				<tbody>

					<tr>
						<th scope="row">
							<label form="post_results_to_twitter">Post Results</label>
						</th>
						<td>
							<input type="checkbox" name="post_results_to_twitter" id="post_results_to_twitter" value="1" <?php checked(get_option('uci_results_post_results_to_twitter', ''), 1); ?>>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label form="post_rankings_to_twitter">Post Rankings Updates</label>
						</th>
						<td>
							<input type="checkbox" name="post_rankings_to_twitter" id="post_rankings_to_twitter" value="1" <?php checked(get_option('uci_results_post_rankings_to_twitter', ''), 1); ?>>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label form="twitter_consumer_key">Consumer Key</label>
						</th>
						<td>
							<input type="text" name="twitter_consumer_key" id="twitter_consumer_key" class="regular-text code" value="<?php echo get_option('uci_results_twitter_consumer_key', ''); ?>" />
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label form="twitter_consumer_secret">Consumer Secret</label>
						</th>
						<td>
							<input type="text" name="twitter_consumer_secret" id="twitter_consumer_secret" class="regular-text code" value="<?php echo get_option('uci_results_twitter_consumer_secret', ''); ?>" />
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label form="twitter_access_token">Access Token</label>
						</th>
						<td>
							<input type="text" name="twitter_access_token" id="twitter_access_token" class="regular-text code" value="<?php echo get_option('uci_results_twitter_access_token', ''); ?>" />
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label form="twitter_access_token_secret">Access Token Secret</label>
						</th>
						<td>
							<input type="text" name="twitter_access_token_secret" id="twitter_access_token_secret" class="regular-text code" value="<?php echo get_option('uci_results_twitter_access_token_secret', ''); ?>" />
						</td>
					</tr>

				</tbody>
			</table>
		</section>

		<p class="submit">
			<input name="submit" type="submit" class="button button-primary" value="Save Settings">
		</p>

	</form>

	<section class="admin-actions">
		<h2>Actions</h2>

		<div id="uci-results-actions-message"></div>

		<table class="form-table">
			<tbody>

				<tr>
					<th scope="row">
						<label form="build-season-weeks">Build Season Weeks</label>
					</th>
					<td>
						<button class="button button-primary" id="build-season-weeks">Build</button>
						<p class="description">This operation will remove all data from the databases created by this plugin.</p>
					</td>
				</tr>

			</tbody>
		</table>

		<div class="empty-db warning message">
			<input type="hidden" id="uci-results-empty-db-nonce" value="<?php echo wp_create_nonce('uci-results-empty-db-nonce'); ?>" />
			<p>This operation will remove all data from the databases created by this plugin.</p>
			<button class="button button-primary warning-button" class="empty-db" id="uci-results-empty-db">Remove Data</button>
		</div>

		<div class="remove-db warning message">
			<input type="hidden" id="uci-results-remove-db-nonce" value="<?php echo wp_create_nonce('uci-results-remove-db-nonce'); ?>" />
			<p>This operation will remove all data and the database tables from the databases created by this plugin. Call it the <strong>Ultimate Uninstall</strong></p>
			<button class="button button-primary warning-button" class="remove-db" id="uci-results-remove-db">Remove Database Tables</button>
		</div>
	</section>

</div>