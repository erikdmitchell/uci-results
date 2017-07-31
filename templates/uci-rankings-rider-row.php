<?php $country=uci_rider_country($atts->rider_id, false); ?>

<div class="em-row rider">
	<div class="em-col-md-1 rider-rank"><?php echo $atts->rank; ?></div>
	<div class="em-col-md-5 rider-name"><a href="<?php uci_results_rider_url($atts->rider_id); ?>"><?php echo $atts->name; ?></a></div>
	<div class="em-col-md-1 rider-nat"><a href="<?php echo uci_results_country_url($country); ?>"><?php echo $country; ?></a></div>
	<div class="em-col-md-2 rider-points"><?php echo $atts->points; ?></div>
</div>