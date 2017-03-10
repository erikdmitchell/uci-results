<div class="uci-results-api">

	<h2>API</h2>
	
	Coming soon
	
	<h2>Rider Class Testing</h2>
	// uci_results_get_rider_results
	// 3544
	// wout-van-aert
	<?php
	global $uci_riders;
	
	$rider=$uci_riders->get_rider(array(
		'rider_id' => 3544, //
		//'results' => true,
		//'last_result' => true,
		//'race_ids' => array(8243, 8173),
		//'results_season' => '20152016',
		//'ranking' => true,
		'stats' => true		
	));
	?>
	<pre>
		<?php print_r($rider); ?>
	</pre>
</div>