<?php
		global $RaceStats;

		$html=null;
		$races=$RaceStats->get_races(array(
			'season' => '2015/2016',
			'pagination' => true,
			'per_page' => 30,
			'order' => 'ASC'
		));

		$html.='<h3>UCI Cross</h3>';

		$html.='<p>Details coming soon on how to use this plugin and what to do.</p>';

		return $html;
?>