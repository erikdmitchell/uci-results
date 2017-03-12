<?php //print_r($atts); ?>

<div class="em-row">
	<div class="em-col-md-4"><?php echo $atts->post_title; ?></div>
	<div class="em-col-md-2"><?php echo get_post_type($atts->ID); ?></div>
</div>