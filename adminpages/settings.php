<?php global $uci_results_pages;	?>

<div class="uci-results-settings">

	<section class="pages">
	  <h2>Pages</h2>

	  <p>Manage the WordPress pages assigned to each UCI Results page.</p>

		<form action="" method="post">
			<input type="hidden" name="save_settings" value="1" />

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

				</tbody>
			</table>

			<p class="submit">
				<input name="submit" type="submit" class="button button-primary" value="Save Settings">
			</p>
		</form>
	</section>

	<section class="admin-actions">
		<h2>Actions</h2>

		<div id="uci-results-actions-message"></div>

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