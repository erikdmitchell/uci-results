<?php global $hcmcul_pages;	?>

		<div class="wrap">
			<h2>UCI Results Settings</h2>

			<form action="" method="post">
				<input type="hidden" name="save_settings" value="1" />

        <h2>Pages</h2>
        <p>Manage the WordPress pages assigned to each required custom login page.</p>
        <table class="form-table">
        	<tbody>
            <tr>
            	<th scope="row" valign="top">
								<label for="login_page_id">Login Page:</label>
							</th>
							<td>
								<?php wp_dropdown_pages(array(
									'name' => 'login_page_id',
									'show_option_none' => '-- '.__('Select One', 'hcmcul').' --',
									'selected' => $hcmcul_pages['login']
								)); ?>
								<a target="_blank" href="<?php echo admin_url('post.php?post='.$hcmcul_pages['login'].'&action=edit'); ?>" class="button button-secondary">Edit Page</a>
								&nbsp;
								<a target="_blank" href="<?php echo get_permalink($hcmcul_pages['login']); ?>" class="button button-secondary">View Page</a>
								<br>
								<small class="p">Include the shortcode [hcmcul_login]</small>
							</td>
						</tr>

            <tr>
            	<th scope="row" valign="top">
								<label for="hcmcul_reset_page_id">Password Reset:</label>
							</th>
							<td>
								<?php wp_dropdown_pages(array(
									'name' => 'hcmcul_reset_page_id',
									'show_option_none' => '-- '.__('Select One', 'hcmcul').' --',
									'selected' => $hcmcul_pages['reset']
								)); ?>
								<a target="_blank" href="<?php echo admin_url('post.php?post='.$hcmcul_pages['reset'].'&action=edit'); ?>" class="button button-secondary">Edit Page</a>
								&nbsp;
								<a target="_blank" href="<?php echo get_permalink($hcmcul_pages['reset']); ?>" class="button button-secondary">View Page</a>
								<br>
								<small class="p">Include the shortcode [hcmcul_reset]</small>
							</td>
						</tr>

            <tr>
            	<th scope="row" valign="top">
								<label for="hcmcul_password_reset_page_id">(Forced) Password Reset:</label>
							</th>
							<td>
								<?php wp_dropdown_pages(array(
									'name' => 'hcmcul_password_reset_page_id',
									'show_option_none' => '-- '.__('Select One', 'hcmcul').' --',
									'selected' => $hcmcul_pages['password-reset']
								)); ?>
								<a target="_blank" href="<?php echo admin_url('post.php?post='.$hcmcul_pages['password-reset'].'&action=edit'); ?>" class="button button-secondary">Edit Page</a>
								&nbsp;
								<a target="_blank" href="<?php echo get_permalink($hcmcul_pages['password-reset']); ?>" class="button button-secondary">View Page</a>
								<br>
								<small class="p">Include the shortcode [hcmcul_password-reset]</small>
							</td>
						</tr>


            <tr>
            	<th scope="row" valign="top">
								<label for="hcmcul_edit_profile_page_id">Edit Profile:</label>
							</th>
							<td>
								<?php wp_dropdown_pages(array(
									'name' => 'hcmcul_edit_profile_page_id',
									'show_option_none' => '-- '.__('Select One', 'hcmcul').' --',
									'selected' => $hcmcul_pages['edit-profile']
								)); ?>
								<a target="_blank" href="<?php echo admin_url('post.php?post='.$hcmcul_pages['edit-profile'].'&action=edit'); ?>" class="button button-secondary">Edit Page</a>
								&nbsp;
								<a target="_blank" href="<?php echo get_permalink($hcmcul_pages['edit-profile']); ?>" class="button button-secondary">View Page</a>
								<br>
								<small class="p">Include the shortcode [hcmcul_edit-profile]</small>
							</td>
						</tr>
					</tbody>
				</table>

				<p class="submit">
					<input name="submit" type="submit" class="button button-primary" value="Save Settings">
				</p>
			</form>

		</div>