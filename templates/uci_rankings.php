<?php
/**
 * template for uci rankings page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */
 
global $uci_rankings;

get_header(); ?>

<?php
$rankings=$uci_rankings->get_rankings(array(
	'order_by' => 'rank',
	'date' => get_query_var('rankings_date'),
	'discipline' => get_query_var('rankings_discipline'),	
));
?>
<div class="em-container uci-results uci-rankings">
	<h1>UCI Rankings</h1>
	
	<div class="em-row header">
		<div class="em-col-sm-2">Rank</div>
		<div class="em-col-sm-7">Name</div>
		<div class="em-col-sm-3">Points</div>		
	</div>
		
	<?php foreach ($rankings as $rank) : ?>
		<div class="em-row">
			<div class="em-col-sm-2"><?php echo $rank->rank; ?></div>
			<div class="em-col-sm-7"><a href="<?php echo uci_results_rider_url($rank->rider_id); ?>"><?php echo $rank->name; ?></a></div>
			<div class="em-col-sm-3"><?php echo $rank->points; ?></div>			
		</div>
	<?php endforeach; ?>
</div>

<?php get_footer(); ?>