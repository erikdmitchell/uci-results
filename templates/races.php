<?php
/**
 * div e template for races page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

get_header(); ?>

<?php
$races=new WP_Query(array(
	'posts_per_page' => 15,
	'post_type' => 'races',
	'paged' => get_query_var('paged'),
));
?>

<div class="em-container uci-results uci-results-races">

	<h1 class="page-title">Races</h1>

	<div class="uci-results-races">
		<div class="em-row header">
				<div class="em-col-sm-6 race-name">Name</div>
				<div class="em-col-sm-2 race-date">Date</div>
				<div class="em-col-sm-1 race-nat">Nat</div>
				<div class="em-col-sm-1 race-class">Class</div>
		</div>

		<?php if ($races->posts) : while ($races->have_posts() ) : $races->the_post(); ?>
			<div class="em-row">
				<div class="em-col-sm-6 race-name"><a href="<?php uci_results_race_url($post->post_name); ?>"><?php the_title(); ?></a></div>
				<div class="em-col-sm-2 race-date"><?php echo $post->race_date; ?></div>
				<div class="em-col-sm-1 race-nat"><?php echo uci_results_get_country_flag($post->nat); ?></div>
				<div class="em-col-sm-1 race-class"><?php echo $post->class; ?></div>
			</div>
		<?php endwhile; endif; ?>

	</div>

	<?php uci_pagination($races->max_num_pages); ?>
</div>

<?php
$response = wp_remote_get('http://uci.dev/wp-json/uci/v1/races');

	if( is_wp_error( $response ) ) {
		return;
	}

	$posts = json_decode( wp_remote_retrieve_body( $response ) );

echo '<pre>';
print_R($posts);
echo '</pre>';
?>

<?php get_footer(); ?>