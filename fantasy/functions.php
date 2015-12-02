<?php
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
	$teams=$wpdb->get_results("SELECT meta_value AS team FROM wp_usermeta WHERE user_id=$user_id AND meta_key='team_name'");

	if (!count($teams) || empty($teams[0]->team))
		return '<a href="'.get_edit_user_link().'">Please add a team name first.</a>';

	$html.='<ul class="fantasy-cycling-user-teams">';
		foreach ($teams as $team) :
			$html.='<li>';
				$html.='<a class="team-name" href="/fantasy/team?team='.urlencode($team->team).'">'.$team->team.'</a>';
				//$html.=fc_get_upcoming_race_add_riders_link($user_id,false,false,'[',']');
			$html.='</li>';
		endforeach;
	$html.='</ul>';

	return $html;
}

/**
 * fc_get_upcoming_race_add_riders_link function.
 *
 * @access public
 * @param int $user_id (default: 0)
 * @param bool $race_id (default: false)
 * @param bool $team (default: false)
 * @param string $before (default: '')
 * @param string $after (default: '')
 * @return void
 */
function fc_get_upcoming_race_add_riders_link($user_id=0,$race_id=false,$team=false,$before='',$after='') {
	global $wpdb;

	if (!$user_id)
		$user_id=get_current_user_id();

	if (!$race_id)
		$race_id=fc_get_next_race_id();

	if (!$team)
		$team=$wpdb->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id=$user_id AND meta_key='team_name'");

	$sql="
		SELECT
			data AS roster
		FROM wp_fc_teams
		WHERE wp_user_id={$user_id}
		AND race_id={$race_id}
	";

	$db_results=$wpdb->get_var($sql);

	if ($db_results) :
		$link=$before.'<a class="roster edit" href="/fantasy/create-team/?race_id='.$race_id.'">Edit Roster</a>'.$after;
	else :
		$link=$before.'<a class="roster add" href="/fantasy/create-team/?race_id='.$race_id.'">Add Roster</a>'.$after;
	endif;

	return $link;
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
 * fc_get_fantasy_riders function.
 *
 * @access public
 * @param array $args (default: array())
 * @return void
 */
function fc_get_fantasy_riders($args=array()) {
	global $wpdb,$uci_curl,$RiderStats;

	$season='2015/2016';
	$default_args=array(
		'race_id' => 1,
		'echo' => true
	);
	$args=array_merge($default_args,$args);

	extract($args);

	$race_db=$wpdb->get_row("SELECT * FROM wp_fc_races WHERE id={$race_id}");
	$riders_db=$RiderStats->get_riders(array(
		'season' => $season,
		'per_page' => -1,
		'week' => $RiderStats->get_latest_rankings_week($season),
	));
	$riders=unserialize($race_db->start_list);

	// if we have no start list //
	if (empty($riders))
		return false;

	// append rider data //
	foreach ($riders as $key => $rider) :
		foreach ($riders_db as $rider_db) :
			if ($rider_db->name==$rider) :
				$rider_details=$rider_db;
				break;
			endif;
		endforeach;

		$last_year=$wpdb->get_var("SELECT place FROM $uci_curl->results_table WHERE code=\"$race_db->last_year_code\" AND name='{$rider}'");
		$arr=array();
		$arr['name']=$rider;
		$arr['country']=$rider_db->country;

		if ($last_year) :
			$arr['last_year']=$last_year;
		else :
			$arr['last_year']='n/a';
		endif;

		// last week finish/race
		$last_week=$wpdb->get_var("SELECT place FROM $uci_curl->results_table WHERE code=\"$race_db->last_week_code\" AND name='{$rider}'");

		if ($last_week) :
			$arr['last_week']=$last_week;
		else :
			$arr['last_week']='n/a';
		endif;

		$arr['rank']=$rider_db->rank;
		$arr['points']=array(
			'c2' => $rider_db->c2,
			'c1' => $rider_db->c1,
			'cn' => $rider_db->cn,
			'cc' => $rider_db->cc,
			'cdm' => $rider_db->wcp_total,
			'cm' => $rider_db->cm
		);

		$riders[$key]=$arr;
	endforeach;

	// Sort the array by name //
	sort($riders);

	return $riders;
}

/**
 * fc_add_rider_modal_btn function.
 *
 * @access public
 * @return void
 */
function fc_add_rider_modal_btn() {
	$html=null;

	$html.='<button type="button" class="button button-getcode button-primary add-remove-btn" data-toggle="modal" data-target="#add-rider-modal">';
		$html.='<span class="add"><i class="fa fa-plus"></i><span class="text">Add Rider</span></span>';
		$html.='<span class="remove"><i class="fa fa-minus"></i><span class="text">Remove Rider</span></span>';
	$html.='</button>';

	echo $html;
}

/**
 * fc_add_rider_modal function.
 *
 * @access public
 * @param array $args (default: array())
 * @return void
 */
function fc_add_rider_modal($args=array()) {
	$html=null;
	$riders=fc_get_fantasy_riders($args);

	if (!$riders)
		return false;

	$html.='<div class="modal fade" id="add-rider-modal" tabindex="-1" role="dialog">';
	  $html.='<div class="modal-dialog">';
	    $html.='<div class="modal-content">';
	      $html.='<div class="modal-header">';
	        $html.='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
	        $html.='<h4 class="modal-title">Select a Rider</h4>';
	      $html.='</div>';
	      $html.='<div class="modal-body">';
	        $html.='<div class="rider-list">';
							$html.='<div class="rider header row">';
								$html.='<div class="name col-md-5">Name</div>';
								$html.='<div class="rank col-md-2">Rank</div>';
								$html.='<div class="last-year col-md-2">Last Year</div>';
								$html.='<div class="last-week col-md-3">Last Week</div>';
							$html.='</div>';
	        	foreach ($riders as $rider) :
							$html.='<div class="rider row">';
								$html.='<div class="name col-md-5"><a href="#" data-rider="'.htmlspecialchars(json_encode($rider),ENT_QUOTES,'UTF-8').'">'.$rider['name'].'</a></div>';
								$html.='<div class="rank col-md-2">'.$rider['rank'].'</div>';
								$html.='<div class="last-year col-md-2">'.$rider['last_year'].'</div>';
								$html.='<div class="last-week col-md-3">'.$rider['last_week'].'</div>';
							$html.='</div>';
	        	endforeach;
	        $html.='</div>';
	      $html.='</div>';
	      $html.='<div class="modal-footer">';
	        $html.='<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
	      $html.='</div>';
	    $html.='</div><!-- /.modal-content -->';
	  $html.='</div><!-- /.modal-dialog -->';
	$html.='</div><!-- /.modal -->';

	echo $html;
}

/**
 * fc_fantasy_get_last_week_race_name function.
 *
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function fc_fantasy_get_last_week_race_name($race_id=0) {
	global $wpdb;

	$race_db=$wpdb->get_row("SELECT * FROM wp_fc_races WHERE id={$race_id}");
	$name=$wpdb->get_var("SELECT name FROM wp_fc_races WHERE race_start BETWEEN DATE_SUB('{$race_db->race_start}', INTERVAL 7 DAY) AND '{$race_db->race_start}' AND id!={$race_id}");

	return $name;
}

/**
 * fc_race_has_start_list function.
 *
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function fc_race_has_start_list($race_id=0) {
	global $wpdb;

	if (!$race_id)
		return false;

	$riders=$wpdb->get_col("SELECT start_list FROM wp_fc_races WHERE id=$race_id");
	$riders=unserialize($riders[0]);

	// if we have no start list //
	if (empty($riders))
		return false;

	return true;
}

/**
 * fc_process_create_team function.
 *
 * @access public
 * @return void
 */
function fc_process_create_team() {
	global $wpdb;

	if (isset($_POST['create_team']) && $_POST['create_team']) :
		$table='wp_fc_teams';

		$data=array(
			'wp_user_id' => $_POST['wp_user_id'],
			'data' => implode('|',$_POST['riders']),
			'team' => $_POST['team_name'],
			'race_id' => $_POST['race'],
		);

		$wpdb->insert($table,$data);

		wp_redirect('/fantasy/team?team='.urlencode($_POST['team_name']));
		exit;
	endif;
}
add_action('init','fc_process_create_team');

/**
 * fc_get_team function.
 *
 * @access public
 * @param bool $team (default: false)
 * @return void
 */
function fc_get_team($team=false) {
	global $wpdb;

	if (!$team && isset($_GET['team']) && $_GET['team']!='')
		$team=$_GET['team'];

	if (!$team || $team=='')
		$team=$wpdb->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id=".get_current_user_id()." AND meta_key='team_name'");

	if (!$team || $team=='')
		return false;

	$html=null;
	$team_results=fc_get_teams_results($team);

	$html.='<div class="team">';
		$html.='<h3>'.stripslashes($team).'</h3>';

		$html.='<div class="results">';
			foreach ($team_results as $results) :
				$html.='<div class="race-results">';
					$html.='<div class="row">';
						$html.='<div class="race-name col-md-7"><a href="/fantasy/standings/?race_id='.$results->race_id.'">'.$results->race_name.'</a></div>';
						$html.='<div class="total-points col-md-2">'.$results->total.'</div>';
					$html.='</div>';

					$html.='<div class="riders">';
						$html.='<div class="header row">';
							$html.='<div class="name col-md-7">Rider</div>';
							$html.='<div class="place col-md-2">Place</div>';
							$html.='<div class="points col-md-2">Points</div>';
						$html.='</div>';
						foreach ($results->riders as $rider) :
							$html.='<div class="rider row">';
								$html.='<div class="name col-md-7">'.$rider->name.'<span class="nat">'.get_country_flag($rider->nat).'</span></div>';
								$html.='<div class="place col-md-2">'.$rider->place.'</div>';
								$html.='<div class="points col-md-2">'.$rider->points.'</div>';
							$html.='</div>';
						endforeach;
					$html.='</div>';
				$html.='</div>';
			endforeach;
		$html.='</div>';
	$html.='</div>';

	return $html;
}

/**
 * fc_get_race_standings function.
 *
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function fc_get_race_standings($race_id=0) {
	$html=null;
	$teams=fc_get_teams_results(false,$race_id);
	$place=1;

	$html.='<div class="fantasy-cycling-team-standings">';
		$html.='<h3>'.$teams->race_name.'</h3>';
		$html.='<div class="team-standings">';
			$html.='<div class="row header">';
				$html.='<div class="rank col-md-3">Rank</div>';
				$html.='<div class="name col-md-6">Team</div>';
				$html.='<div class="points col-md-3">Points</div>';
			$html.='</div>';
			foreach ($teams->teams as $team) :
				$html.='<div class="row">';
					$html.='<div class="rank col-md-3">'.$place.'</div>';
					$html.='<div class="name col-md-6"><a href="/fantasy/team?team='.urlencode($team->team_name).'">'.$team->team_name.'</a></div>';
					$html.='<div class="points col-md-3">'.$team->total.'</div>';
				$html.='</div>';
				$place++;
			endforeach;
		$html.='</div>';
	$html.='</div>';

	return $html;
}

/**
 * fc_get_teams_results function.
 *
 * @access public
 * @param bool $team_name (default: false)
 * @param int $race_id (default: 0)
 * @return void
 */
function fc_get_teams_results($team_name=false,$race_id=false) {
	global $wpdb;

	if (!$team_name && !$race_id)
		return false;

	$where=array();

	if ($team_name)
		$where[]="team='{$team_name}'";

	if ($race_id)
		$where[]="race_id={$race_id}";

	if (!empty($where)) :
		if (count($where)==1) :
			$where='WHERE '.implode('',$where);
		else :
			$where='WHERE '.implode(' AND ',$where);
		endif;
	endif;

	$html=null;
	$teams_final=new stdClass();
	$fc_data_sql="
		SELECT
			team AS team_name,
			data AS riders,
			race_id,
			races.code,
			races.name AS race_name
		FROM wp_fc_teams AS teams
		LEFT JOIN wp_fc_races AS races
		ON teams.race_id=races.id
		LEFT JOIN wp_uci_races AS uci_races
		ON races.code=uci_races.code
		$where
		ORDER BY races.race_start DESC
	";
	$teams=$wpdb->get_results($fc_data_sql);

	// split out riders into array and get points //
	foreach ($teams as $team) :
		$total=0;
		$team->riders=explode('|',$team->riders);
		foreach ($team->riders as $key => $rider) :
			$results=$wpdb->get_row("SELECT name, place, nat, par AS points FROM wp_uci_rider_data	WHERE code=\"{$team->code}\" AND name='{$rider}'");
			if (empty($results)) :
				$results=new stdClass();
				$results->name=$rider;
				$results->place=0;
				$results->nat=fc_find_rider_nat($rider);
				$results->points=0;
			endif;
			$team->riders[$key]=$results;
			$total=$total+$results->points;
		endforeach;
		$team->total=$total;
	endforeach;

	// if not a single team, order by points //
	if (!$team_name)
		usort($teams, function ($a, $b) {
			return strcmp($b->total,$a->total);
		});

	if ($team_name) :
		$teams_final=$teams;
	endif;

	if ($race_id) :
		$teams_final->race_name=$wpdb->get_var("SELECT event AS race_name FROM wp_fc_races AS fcraces LEFT JOIN wp_uci_races AS races ON fcraces.code=races.code WHERE fcraces.id={$race_id}");
		$teams_final->teams=$teams;
	endif;

	return $teams_final;
}

/**
 * fc_get_standings function.
 *
 * @access public
 * @return void
 */
function fc_get_standings() {
	if (isset($_GET['race_id'])) :
		return fc_get_race_standings($_GET['race_id']);
	else :
		return fc_get_final_standings();
	endif;
}

/**
 * fc_standings function.
 *
 * @access public
 * @return void
 */
function fc_standings() {
	echo fc_get_standings();
}

/**
 * fc_get_final_standings function.
 *
 * @access public
 * @param int $limt (default: 10)
 * @return void
 */
function fc_get_final_standings($limt=10) {
	global $wpdb;

	$html=null;
	$sql="
		SELECT
			name,
			races.id,
			COUNT(teams.id) AS total_teams
		FROM wp_fc_races AS races
		LEFT JOIN wp_fc_teams AS teams
		ON races.id=teams.race_id
		WHERE races.race_start < CURDATE()
		GROUP BY races.id
		ORDER BY races.race_start DESC
	";
	$races=$wpdb->get_results($sql);

	$html.='<div class="fantasy-cycling-final-standings">';
		$html.='<div class="final-standings">';
			//$html.='<div class="row header">';
				//$html.='<div class="name col-md-12">Race</div>';
				//$html.='<div class="points col-md-3">Teams</div>';
			//$html.='</div>';
			foreach ($races as $race) :
				$html.='<div class="row">';
					$html.='<div class="name col-md-12"><a href="/fantasy/standings?race_id='.$race->id.'">'.$race->name.'</a></div>';
					//$html.='<div class="points col-md-3">'.$race->total_teams.'</div>';
				$html.='</div>';
			endforeach;
		$html.='</div>';
		$html.='<a href="/fantasy/standings/" class="more">View All &raquo;</a>';
	$html.='</div>';

	return $html;
}

/**
 * fc_final_standings function.
 *
 * @access public
 * @param int $limit (default: 10)
 * @return void
 */
function fc_final_standings($limit=10) {
	echo fc_get_final_standings($limit);
}

function fc_get_fantasy_cycling_posts($limit=5) {
	$html=null;
	$featured_posts_id=0;
	$args=array(
		'posts_per_page' => 1,
		'post_type' => 'fantasy-cycling',
		'posttype' => 'featured'
	);
	$featured_posts=get_posts($args);

	if (isset($featured_posts[0]))
		$featured_posts_id=$featured_posts[0]->ID;

	$args=array(
		'posts_per_page' => $limit-1,
		'post_type' => 'fantasy-cycling',
		'post__not_in' => array($featured_posts_id),
	);
	$posts=get_posts($args);
	$posts=array_merge($featured_posts,$posts);

	if (!count($posts))
		return false;

	$html.='<ul class="fc-posts">';
		foreach ($posts as $post) :
			$class='';
			$html.='<li id="post-'.$post->ID.'" class="post '.$class.'">';
				$html.='<h4><a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a></h4>';
				$html.=get_the_post_thumbnail($post->ID,'thumbnail');
				$html.='<div class="excerpt">'.fc_excerpt_by_id($post->ID,100,'<a><em><strong>','...<a href="'.get_permalink($post->ID).'">more &raquo;</a>').'</div>';
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

/**
 * fc_get_upcoming_races function.
 *
 * @access public
 * @param int $limit (default: 3)
 * @return void
 */
function fc_get_upcoming_races($limit=3) {
	global $wpdb;

	$html=null;
	$races=$wpdb->get_results("SELECT * FROM wp_fc_races WHERE race_start > CURDATE() ORDER BY race_start ASC LIMIT $limit");
	$team=$wpdb->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id=".get_current_user_id()." AND meta_key='team_name'");

	$html.='<div class="fc-upcoming-races">';
		foreach ($races as $race) :
			if ($race->series!='single') :
				$series='<span class="series">('.$race->series.')</span>';
			else :
				$series='';
			endif;

			$html.='<div id="race-'.$race->id.'" class="">';
				$html.='<span class="date">'.date('M. j, Y',strtotime($race->race_start)).': </span>';
				$html.='<span class="race-name"><a href="/fantasy/create-team/?race_id='.$race->id.'">'.$race->name.'</a></span>';
				$html.=$series;

				if (is_user_logged_in())
					$html.=fc_get_upcoming_race_add_riders_link(0,$race->id,false,'[',']');

			$html.='</div>';
		endforeach;
	$html.='</div>';

	return $html;

}

/**
 * fc_upcoming_races function.
 *
 * @access public
 * @param int $limit (default: 3)
 * @return void
 */
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

/**
 * fc_get_template_html function.
 *
 * @access public
 * @param bool $template_name (default: false)
 * @return void
 */
function fc_get_template_html($template_name=false) {
	if (!$template_name)
		return false;

	ob_start();

	do_action('emcl_before_'.$template_name);

	require('templates/'.$template_name.'.php');

	do_action('emcl_after_'.$template_name);

	$html=ob_get_contents();

	ob_end_clean();

	return $html;
}

/**
 * fc_get_race function.
 *
 * @access public
 * @param mixed $race_id
 * @return void
 */
function fc_get_race($race_id) {
	global $wpdb;

	$race=$wpdb->get_row("SELECT * FROM wp_fc_races WHERE id={$race_id}");

	return $race;
}

/**
 * fc_get_user_team function.
 *
 * @access public
 * @param bool $user_id (default: false)
 * @return void
 */
function fc_get_user_team($user_id=false) {
	global $wpdb;

	if (!$user_id)
		$user_id=get_current_user_id();

	$team=$wpdb->get_var("SELECT DISTINCT team FROM wp_fc_teams WHERE wp_user_id={$user_id}");

	// not in fc, check user profile
	if (!$team) :
		$team=$wpdb->get_var("SELECT meta_value AS team FROM $wpdb->usermeta WHERE user_id={$user_id} AND meta_key='team_name'");
	endif;

	return $team;
}

/**
 * fc_get_race_id function.
 *
 * @access public
 * @return void
 */
function fc_get_race_id() {
	global $wpdb;

	if (isset($_GET['race_id']) && $_GET['race_id']!=0)
		return $_GET['race_id'];

	if ($race_id=fc_get_next_race_id())
		return $race_id;

	return false;
}

/**
 * fc_get_next_race_id function.
 *
 * @access public
 * @return void
 */
function fc_get_next_race_id() {
	global $wpdb;

	$race_id=$wpdb->get_var("SELECT races.id	FROM wp_fc_races AS races	WHERE races.race_start > CURDATE() LIMIT 1");

	if ($race_id!=NULL) :
		return $race_id;
	else :
		return false;
	endif;
}

/**
 * fc_addon_profile_fields function.
 *
 * @access public
 * @param mixed $user
 * @return void
 */
function fc_addon_profile_fields($user) {
	$html=null;

	$html.='<h3>Fantasy Cycling</h3>';
	$html.='<table class="form-table fantasy-cycling-profile-fields">';
		$html.='<tr>';
			$html.='<th><label for="team_name">Team Name</label></th>';
			$html.='<td><input type="text" name="team_name" value="'.esc_attr(get_the_author_meta('team_name',$user->ID)).'" class="regular-text" /></td>';
		$html.='</tr>';
		$html.='<tr>';
			$html.='<th><label for="team_country">Country</label></th>';
			$html.='<td><input type="text" name="team_country" value="'.esc_attr(get_the_author_meta('team_country',$user->ID)).'" class="regular-text" /></td>';
		$html.='</tr>';
	$html.='</table>';

	echo $html;
}
add_action('show_user_profile','fc_addon_profile_fields');
add_action('edit_user_profile','fc_addon_profile_fields');

/**
 * fc_save_addon_profile_fields function.
 *
 * @access public
 * @param mixed $user_id
 * @return void
 */
function fc_save_addon_profile_fields($user_id) {
	update_user_meta($user_id,'team_name',sanitize_text_field($_POST['team_name']));
	update_user_meta($user_id,'team_country',sanitize_text_field($_POST['team_country']));
}
add_action('personal_options_update','fc_save_addon_profile_fields');
add_action('edit_user_profile_update','fc_save_addon_profile_fields');

/**
 * fc_registration_proccess_addon_profile_fields function.
 *
 * @access public
 * @param mixed $user_id
 * @param mixed $form
 * @return void
 */
function fc_registration_proccess_addon_profile_fields($user_id,$form) {
	update_user_meta($user_id,'team_name',sanitize_text_field($_POST['team_name']));
	update_user_meta($user_id,'team_country',sanitize_text_field($_POST['team_country']));
}
add_action('emcl-after-user-registration','fc_registration_proccess_addon_profile_fields',10,2);

/**
 * fc_admin_body_class function.
 *
 * @access public
 * @param mixed $classes
 * @return void
 */
function fc_admin_body_class($classes) {
	if (!current_user_can('manage_options'))
		$classes.='user_not_admin';

	return $classes;
}
add_filter('admin_body_class','fc_admin_body_class');

/**
 * fc_scripts_styles function.
 *
 * @access public
 * @return void
 */
function fc_scripts_styles() {
	if (is_page(get_page_by_title('Create Team')->ID)) :
		wp_register_script('fc-create-team-script',plugins_url('/js/create-team.js',__FILE__),array('jquery'));

		wp_localize_script('fc-create-team-script','createTeamOptions',array(
			'roster'=> fc_check_if_roster_edit()
		));

		wp_enqueue_script('fc-create-team-script');

	endif;
}
add_action('wp_enqueue_scripts','fc_scripts_styles');

/**
 * fc_check_if_roster_edit function.
 *
 * @access public
 * @param bool $team (default: false)
 * @param bool $race_id (default: false)
 * @return void
 */
function fc_check_if_roster_edit($team=false,$race_id=false) {
	global $wpdb;

	if (!$team && isset($_GET['team']))
		$team=$_GET['team'];

	if (!$race_id && isset($_GET['race_id']))
		$race_id=$_GET['race_id'];

	if (!$team)
		$team=$wpdb->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id=".get_current_user_id()." AND meta_key='team_name'");

	if (!$race_id)
		$race_id=fc_get_next_race_id();

	$sql="
		SELECT
			data AS roster
		FROM wp_fc_teams
		WHERE team=\"{$team}\"
		AND race_id={$race_id}
	";

	$db_results=$wpdb->get_var($sql);

	if ($db_results)
		return $db_results;

	return false;
}

/**
 * fc_find_rider_nat function.
 *
 * @access public
 * @param bool $rider (default: false)
 * @return void
 */
function fc_find_rider_nat($rider=false) {
	global $wpdb;

	if (!$rider)
		return false;

	$nat=$wpdb->get_var("SELECT DISTINCT nat FROM wp_uci_rider_data	WHERE name='{$rider}'");

	return $nat;
}

/**
 * fc_excerpt_by_id function.
 *
 * @access public
 * @param mixed $post
 * @param int $length (default: 10)
 * @param string $tags (default: '<a><em><strong>')
 * @param string $extra (default: ' . . .')
 * @return void
 */
function fc_excerpt_by_id($post, $length = 10, $tags = '<a><em><strong>', $extra = ' . . .') {
	if (is_int($post)) {
		// get the post object of the passed ID
		$post = get_post($post);
	} elseif(!is_object($post)) {
		return false;
	}

	if(has_excerpt($post->ID)) {
		$the_excerpt = $post->post_excerpt;
		//return apply_filters('the_content', $the_excerpt);
	} else {
		$the_excerpt = $post->post_content;
	}

	$the_excerpt = strip_shortcodes(strip_tags($the_excerpt), $tags);
	$the_excerpt = preg_split('/\b/', $the_excerpt, $length * 2+1);
	$excerpt_waste = array_pop($the_excerpt);
	$the_excerpt = implode($the_excerpt);
	$the_excerpt .= $extra;

	return apply_filters('the_content', $the_excerpt);
}

/**
 * fc_get_race_results function.
 *
 * @access public
 * @param bool $race_code (default: false)
 * @param float $limit (default: -1)
 * @return void
 */
function fc_get_race_results($race_code=false,$limit=-1) {
	global $wpdb,$uci_curl;

	if (!$race_code)
		return false;

	if ($limit>0) :
		$limit="LIMIT $limit";
	else :
		$limit='';
	endif;

	$sql="
		SELECT
			*
		FROM $uci_curl->results_table AS results
		WHERE code=\"{$race_code}\"
		$limit
	";
	$results=$wpdb->get_results($sql);

	return $results;
}

/**
 * fc_get_last_years_code function.
 *
 * @access public
 * @param int $race_id (default: 0)
 * @return void
 */
function fc_get_last_years_code($race_id=0) {
	global $wpdb;

	$code=$wpdb->get_var("SELECT last_year_code FROM wp_fc_races WHERE id={$race_id}");

	return $code;
}

function fc_is_race_roster_edit_open($race_id=0) {
	global $wpdb;

	$race_start=$wpdb->get_var("SELECT race_start FROM wp_fc_races WHERE id={$race_id}");

	if (!$race_start)
		return false;

	if (strtotime($race_start)>strtotime(date('Y-m-d')))
		return true;

	return false;
}
?>