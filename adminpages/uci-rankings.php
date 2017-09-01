<?php global $uci_rankings; ?>

<div class="uci-results-admin uci-rankings">
	<h2>UCI Rankings</h2>
	
	<div id="uci-admin-message"></div>

	<form name="add-uci-rankings" action="" method="post">
		<table class="form-table">
			<tbody>
				
				<tr>
					<th scope="row">
						<label form="add-rankings">Add New Rankings</label>
					</th>
					<td>
						 <?php $uci_rankings->file_input(); ?>

						<div class="custom-date">
							<label form="custom-date">Custom Date</label>
							<input type="text" name="custom-date" id="custom-date" class="date uci-results-datepicker" value="<?php echo date('Y-m-d'); ?>">
							<p class="description">If empty, current date will be used. <i>Format: YYYY-MM-DD</i></p>
						</div>
						
						<div class="discipline">
							<label form="discipline">Discipline</label>
							<?php wp_dropdown_categories(array(
								'hide_empty' => 0,
								'show_option_none'   => 'Select One',
								'option_none_value'  => '0',
								'orderby'            => 'name',
								'name'               => 'discipline',
								'id' => 'discipline',
								'taxonomy'           => 'discipline',
							)); ?>
						</div>
						
						<?php $uci_rankings->process_button(); ?>
					</td>
				</tr>
				
				<tr>
					<th scope="row">View Rankings</th>
					<td>
						<?php $dates=$uci_rankings->get_rankings_dates(); ?>
						<?php foreach ($dates as $date) : ?>
							<a href="<?php uci_results_uci_rankings_url($date->discipline, $date->date); ?>"><?php echo $date->date; ?> (<?php echo $date->discipline; ?>)</a><br />
						<?php endforeach; ?>
					</td>
				</tr>
				
			</tbody>
		</table>

	</form>

</div>