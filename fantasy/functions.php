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

function fc_get_user_teams($user_id=0) {
	global $wpdb;

	if (!$user_id)
		return 'No user found.';

	$html=null;
	$teams=$wpdb->get_results("SELECT team,id FROM wp_fc_teams WHERE wp_user_id=$user_id");

	$html.='<h3>Teams</h3>';

	if (!count($teams)) :
		$html.='No teams found. Click <a href="?action=create-team">here</a> to create one.';
		return $html;
	endif;

	return $html;
}

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
	// find teams by user id

	$team=$wpdb->get_row("SELECT * FROM wp_fc_teams WHERE team='{$team}'");
	$team->data=unserialize($team->data);

	foreach ($team->data as $key => $rider) :
		$country=$wpdb->get_var("SELECT nat FROM wp_uci_rider_data WHERE name='$rider' GROUP BY nat");
		$team->data[$key]=$rider.' ('.$country.')';
	endforeach;

	return $team;
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