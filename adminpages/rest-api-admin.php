<?php
global $uci_results_admin_pages;
global $race_data;
global $results_data;
global $uci_results_add_races;

/*
echo '<pre>';
print_r($race_data);
print_r($results_data);
echo '</pre>';
*/
?>

<div class="uci-results">

	<h2>Results (cURL)</h2>

	<p>Admin Testing</p>
	
	<?php
	$uci_results_add_races->add_race_to_db($race_data);
	?>

</div>