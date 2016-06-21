<?php global $uci_results_pages;	?>

<h2>Settings</h2>

<form action="" method="post">
	<input type="hidden" name="save_settings" value="1" />

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
					<br>
					<small class="p">Include the shortcode [uci_results_rider]</small>
				</td>
			</tr>
		</tbody>
	</table>

	<p class="submit">
		<input name="submit" type="submit" class="button button-primary" value="Save Settings">
	</p>
</form>