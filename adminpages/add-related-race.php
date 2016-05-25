<?php
global $ucicurl_races;

$race=$ucicurl_races->get_race($_GET['race_id']);
$related_races=$ucicurl_races->get_related_races($_GET['race_id']);
?>

<h2>Add Related Race</h2>

<input type="text" name="search-related-races" id="search-related-races">
