<?php
/**
 * fc_output_buffer function.
 *
 * @access public
 * @return void
 */
function fc_output_buffer() {
	ob_start();
}
add_action('init','fc_output_buffer');

/**
 * fc_login_protect_page function.
 *
 * @access public
 * @return void
 */
function fc_login_protect_page() {
	if (!is_user_logged_in()) :
		wp_redirect('/login/');
		exit;
	endif;
}

/**
 * fc_fantasy_page_redirect function.
 *
 * @access public
 * @return void
 */
function fc_fantasy_page_redirect() {
	if (!is_user_logged_in())
		return false;

	wp_redirect('/fantasy/');
	exit;
}

/**
 * fc_template_redirect function.
 *
 * redirects specific pages to our properly templated pages
 *
 * @access public
 * @param mixed $original_template
 * @return void
 */
function fc_template_redirect($original_template) {
	global $post;

	if (isset($post->post_name) && $post->post_name=='fantasy') :
		return plugin_dir_path(__FILE__).'/templates/fantasy-main.php';
	else :
    return $original_template;
  endif;
}
add_filter('template_include','fc_template_redirect');

/**
 * fc_get_user_teams function.
 *
 * @access public
 * @param int $user_id (default: 0)
 * @return void
 */
function fc_get_user_teams($user_id=0) {
	global $wpdb;

	if (!$user_id)
		return 'No user found.';

	$html=null;
	$teams=$wpdb->get_results("SELECT team,id FROM wp_fc_teams WHERE wp_user_id=$user_id");

	if (!count($teams)) :
		$html.='No teams found. Click <a href="?action=create-team">here</a> to create one.';
		return $html;
	endif;

	$html.='<ul class="fantasy-cycling-user-teams">';
		foreach ($teams as $team) :
			$html.='<li id="team-'.$team->id.'"><a href="/fantasy/team?team='.urlencode($team->team).'">'.$team->team.'</a> (0 out of 0)</li>';
		endforeach;
	$html.='</ul>';

	return $html;
}

/**
 * fc_user_teams function.
 *
 * @access public
 * @param int $user_id (default: 0)
 * @return void
 */
function fc_user_teams($user_id=0) {
	echo fc_get_user_teams($user_id);
}

/**
 * fc_rider_list_dropdown function.
 *
 * @access public
 * @param bool $name (default: false)
 * @param int $min_rank (default: 0)
 * @param int $max_rank (default: 10)
 * @param string $select_title (default: 'Select a Rider')
 * @param bool $echo (default: true)
 * @return void
 */
function fc_rider_list_dropdown($name=false,$min_rank=0,$max_rank=10,$select_title='Select a Rider',$echo=true) {
	global $wpdb,$RiderStats;

	$html=null;
	$riders=$RiderStats->get_riders(array(
		'pagination' => false,
		'limit' => "$min_rank,$max_rank"
	));

	if (!$name)
		$name=generateRandomString();

	$html.='<select name="'.$name.'" id="'.$name.'">';
		$html.='<option value="0">'.$select_title.'</option>';
		foreach ($riders as $rider) :
			$html.='<option value="'.$rider->rider.'">'.$rider->rider.'</option>';
		endforeach;
	$html.='</select>';

	if ($echo) :
		echo $html;
	else :
		return $html;
	endif;
}

function fc_rider_list_dropdown_race($args=array()) {
	global $wpdb,$RiderStats;

	$default_args=array(
		'id' => 1,
		'name' => false,
		'min_rank' => 0,
		'max_rank' => 10,
		'select_title' => 'Select a Rider',
		'echo' => true
	);
	$args=array_merge($default_args,$args);
	$html=null;

	extract($args);

	$riders=$wpdb->get_col("SELECT start_list FROM wp_fc_races WHERE id=$id");
	$riders=unserialize($riders[0]);

	// Sort the array by name //
	sort($riders);

	if (!$name)
		$name=generateRandomString();

	$html.='<select name="'.$name.'" id="'.$name.'">';
		$html.='<option value="0">'.$select_title.'</option>';
		foreach ($riders as $rider) :
			$country=$wpdb->get_var("SELECT nat FROM wp_uci_rider_data WHERE name='$rider' GROUP BY nat");
			$html.='<option value="'.$rider.'">'.$rider.' ('.$country.')</option>';
		endforeach;
	$html.='</select>';

	if ($echo) :
		echo $html;
	else :
		return $html;
	endif;
}

/**
 * fc_get_create_team_page function.
 *
 * @access public
 * @return void
 */
function fc_get_create_team_page() {
	ob_start();
	include_once(plugin_dir_path(__FILE__).'templates/create-team.php');
	return ob_get_clean();
}

/**
 * fc_process_create_team function.
 *
 * @access public
 * @param mixed $form
 * @return void
 */
function fc_process_create_team($form) {
	global $wpdb;

	$table='wp_fc_teams';

	$data=array(
		'wp_user_id' => $form['wp_user_id'],
		'data' => serialize($form['riders']),
		'team' => $form['team_name'],
	);

	$wpdb->insert($table,$data);

	wp_redirect('/fantasy/team?team='.urlencode($form['team_name']));
	exit;
}

/**
 * fc_get_team function.
 *
 * @access public
 * @param bool $team (default: false)
 * @return void
 */
function fc_get_team($team=false) {
	global $wpdb;

	if (!$team)
		return false;

	$team=$wpdb->get_row("SELECT * FROM wp_fc_teams WHERE team='{$team}'");
	$team->data=unserialize($team->data);

	foreach ($team->data as $key => $rider) :
		$country=$wpdb->get_var("SELECT nat FROM wp_uci_rider_data WHERE name='$rider' GROUP BY nat");
		$team->data[$key]=$rider.' ('.$country.')';
	endforeach;

	return $team;
}

function fc_get_team_standings($limit=10) {
	global $wpdb;

	$html=null;
	$teams=$wpdb->get_results("SELECT team,data FROM wp_fc_teams");

	$html.='<div class="fantasy-cycling-team-standings">';
		$html.='<div class="team-standings">';
			$html.='<div class="row header">';
				$html.='<div class="rank col-md-3">Rank</div>';
				$html.='<div class="name col-md-6">Team</div>';
				$html.='<div class="points col-md-3">Points</div>';
			$html.='</div>';
			foreach ($teams as $team) :
				$html.='<div class="row">';
					$html.='<div class="rank col-md-3">1</div>';
					$html.='<div class="name col-md-6"><a href="">'.$team->team.'</a></div>';
					$html.='<div class="points col-md-3">0</div>';
				$html.='</div>';
			endforeach;
		$html.='</div>';
		$html.='<a href="" class="more">View All &raquo;</a>';
	$html.='</div>';

	return $html;
}

/**
 * fc_team_standings function.
 *
 * @access public
 * @param int $limit (default: 10)
 * @return void
 */
function fc_team_standings($limit=10) {
	echo fc_get_team_standings($limit);
}

function fc_get_fantasy_cycling_posts($limit=5) {
	$html=null;
	$args=array(
		'posts_per_page' => $limit+1,
		'post_type' => 'fantasy-cycling',
		'tax_query' => array(
			array(
				'taxonomy' => 'posttype',
				'field' => 'slug',
				'terms' => 'sticky',
				'operator' => 'NOT IN',
			),
		),
	);
	$posts=get_posts($args);
	$sticky_args=array(
		'posts_per_page' => 1,
		'post_type' => 'fantasy-cycling',
		'tax_query' => array(
			array(
				'taxonomy' => 'posttype',
				'field' => 'slug',
				'terms' => 'sticky'
			),
		),
	);
	$sticky_posts=get_posts($sticky_args);

	if (!count($posts))
		return false;

	// merge and slice posts //
	$posts=array_merge($sticky_posts,$posts);
	$posts=array_slice($posts,0,$limit);

	$html.='<ul class="fc-posts">';
		foreach ($posts as $post) :
			$terms=wp_get_post_terms($post->ID,'posttype',array('fields' => 'names'));
			$class='';
			$sticky=false;

			if (in_array('Sticky',$terms)) :
				$class.=' sticky';
				$sticky=true;
			endif;

			$html.='<li id="post-'.$post->ID.'" class="'.$class.'">';
				$html.='<a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a>';
				if ($sticky) :
					$html.=': '.$post->post_content;
				endif;
			$html.='</li>';
		endforeach;
	$html.='</ul>';

	return $html;
}

/**
 * fc_fantasy_cycling_posts function.
 *
 * @access public
 * @param int $limit (default: 5)
 * @return void
 */
function fc_fantasy_cycling_posts($limit=5) {
	echo fc_get_fantasy_cycling_posts($limit);
}

function fc_get_upcoming_races($limit=3) {
	global $wpdb;

	$html=null;
	$races=$wpdb->get_results("SELECT * FROM wp_fc_races WHERE race_start > CURDATE() ORDER BY race_start ASC LIMIT $limit");

	$html.='<ul class="fc-upcoming-races">';
		foreach ($races as $race) :
			$html.='<li id="race-'.$race->id.'">'.date('M. j, Y',strtotime($race->race_start)).': '.$race->name.' ('.$race->series.')</li>';
		endforeach;
	$html.='</ul>';

	return $html;
}

function fc_upcoming_races($limit=3) {
	echo fc_get_upcoming_races($limit);
}

/**
 * generateRandomString function.
 *
 * @access public
 * @param int $length (default: 10)
 * @return void
 */
function generateRandomString($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}
?>