<div class="uci-results-api">

	<h2>API</h2>
	
	Coming soon
	
	<h2>Rider Class Testing</h2>

	<?php
	global $uci_riders;
	
	$rider=$uci_riders->get_riders(array(
		'rider_ids' => array(3544, 3),
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