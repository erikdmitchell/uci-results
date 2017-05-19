<?php global $uci_rankings; ?>

<div class="uci-results uci-rankings">
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
						<?php $uci_rankings->add_button(); ?>

						<span class="custom-date">
							<label form="custom-date">Custom Date</label>
							<input type="text" name="custom-date" id="custom-date" class="date" value="">
							<p class="description">If empty, current date will be used. <i>Format: YYYY-MM-DD</i></p>
						</span>
					</td>
				</tr>
				
			</tbody>
		</table>

	</form>

</div>