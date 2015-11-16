<?php
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
		$html.='No teams found. Click <a href="/fantasy/create-team/">here</a> to create one.';
		return $html;
	endif;

	$html.='<ul class="fantasy-cycling-user-teams">';
		foreach ($teams as $team) :
			$html.='<li id="team-'.$team->id.'"><a href="/fantasy/team?team='.urlencode($team->team).'">'.$team->team.'</a></li>';
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

/**
 * fc_rider_list_dropdown_race function.
 *
 * @access public
 * @param array $args (default: array())
 * @return void
 */
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

	if (!$team)
		return false;

	$team_db=$wpdb->get_row("SELECT race_id,team FROM wp_fc_teams WHERE team='{$team}'");
	$team=fc_get_teams_results($team_db->race_id,$team_db->team);

	return $team;
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
	$teams=fc_get_teams_results($race_id);
	$place=1;

	$html.='<div class="fantasy-cycling-team-standings">';
		$html.='<h2>'.$teams->race_name.'</h2>';
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
 * @param int $race_id (default: 0)
 * @param bool $team_name (default: false)
 * @return void
 */
function fc_get_teams_results($race_id=0,$team_name=false) {
	global $wpdb;

	if (!$race_id)
		return false;

	if ($team_name) :
		$where="WHERE race_id={$race_id} AND team='{$team_name}'";
	else :
		$where="WHERE race_id={$race_id}";
	endif;

	$html=null;
	$race=$wpdb->get_row("SELECT name,code FROM wp_fc_races WHERE id={$race_id}");
	$teams=$wpdb->get_results("SELECT team AS team_name,data AS riders FROM wp_fc_teams $where");
	$results=$wpdb->get_results("SELECT name,place,nat,par AS points FROM wp_uci_rider_data WHERE code=\"{$race->code}\"");
	$teams_final=new stdClass();

	// split out riders into array and get points //
	foreach ($teams as $team) :
		$total=0;
		$team->riders=explode('|',$team->riders);
		foreach ($team->riders as $key => $rider) :
			foreach ($results as $result) :
				if ($rider==$result->name) :
					$team->riders[$key]=$result;
					$total=$total+$result->points;
				endif;
			endforeach;
		endforeach;
		$team->total=$total;
	endforeach;

	// order by points //
	usort($teams, function ($a, $b) {
		return strcmp($b->total,$a->total);
	});

	$teams_final->race_name=$race->name;
	$teams_final->teams=$teams;

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
		ORDER BY races.race_start
	";
	$races=$wpdb->get_results($sql);

	$html.='<div class="fantasy-cycling-final-standings">';
		$html.='<div class="final-standings">';
			$html.='<div class="row header">';
				$html.='<div class="name col-md-9">Race</div>';
				$html.='<div class="points col-md-3">Teams</div>';
			$html.='</div>';
			foreach ($races as $race) :
				$html.='<div class="row">';
					$html.='<div class="name col-md-9"><a href="/fantasy/standings?race_id='.$race->id.'">'.$race->name.'</a></div>';
					$html.='<div class="points col-md-3">'.$race->total_teams.'</div>';
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

function fc_get_add_rosters($limit=3) {
	global $wpdb;

	$html=null;
	//$races=$wpdb->get_results("SELECT * FROM wp_fc_races WHERE race_start > CURDATE() ORDER BY race_start ASC LIMIT $limit");
	$races=$wpdb->get_results("SELECT * FROM wp_fc_races ORDER BY race_start ASC LIMIT $limit");

	$html.='<div class="fc-upcoming-races">';
		foreach ($races as $race) :
			if ($race->series!='single') :
				$series='<div class="series">('.$race->series.')';
			else :
				$series='';
			endif;

			$html.='<div id="race-'.$race->id.'" class="row">';
				$html.='<div class="date col-md-4">'.date('M. j, Y',strtotime($race->race_start)).': </div>';
				$html.='<div class="race-name col-md-8"><a href="/fantasy/create-team/?team='.urlencode($_GET['team']).'&race_id='.$race->id.'">'.$race->name.'</a></div>';
				$html.=$series;
			$html.='</div>';
		endforeach;
	$html.='</div>';

	return $html;
}

function fc_add_rosters($limit=3) {
	echo fc_get_add_rosters($limit);
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
 * fc_login_template_redirect function.
 *
 * redirects the 'login' page to our custom template
 *
 * @access public
 * @param mixed $original_template
 * @return void
 */
function fc_login_template_redirect($original_template) {
	global $post;

	if (isset($post->post_name) && $post->post_name=='login') :
		return plugin_dir_path(__FILE__).'/templates/login-page.php';
	else :
    return $original_template;
  endif;
}
//add_filter('template_include','fc_login_template_redirect');

/**
 * fc_redirect_login_page function.
 *
 * redirects the default wp login page to our login page (template done in fc_login_template_redirect())
 *
 * @access public
 * @return void
 */
function fc_redirect_login_page() {
	$login_page  = home_url( '/login/' );
	$page_viewed = basename($_SERVER['REQUEST_URI']);

	if( $page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
		wp_redirect($login_page);
		exit;
	}
}
//add_action('init','fc_redirect_login_page');

/**
 * fc_login_failed function.
 *
 * redirects failed login to our page
 *
 * @access public
 * @return void
 */
function fc_login_failed() {
    $login_page  = home_url( '/login/' );
    wp_redirect( $login_page . '?login=failed' );
    exit;
}
//add_action( 'wp_login_failed', 'fc_login_failed' );

/**
 * fc_verify_username_password function.
 *
 * redirects login errors to our page
 *
 * @access public
 * @param mixed $user
 * @param mixed $username
 * @param mixed $password
 * @return void
 */
function fc_verify_username_password( $user, $username, $password ) {
    $login_page  = home_url( '/login/' );
    if( $username == "" || $password == "" ) {
        wp_redirect( $login_page . "?login=empty" );
        exit;
    }
}
//add_filter( 'authenticate', 'fc_verify_username_password', 1, 3);

/**
 * logout_page function.
 *
 * redirects logout to our page
 *
 * @access public
 * @return void
 */
function fc_logout_page() {
    $login_page  = home_url( '/login/' );
    wp_redirect( $login_page . "?login=false" );
    exit;
}
//add_action('wp_logout','fc_logout_page');


// login form fields
function fc_login_form_fields() {
	fc_login_member('/fantasy/');
	?>
	<?php
	// show any error messages after form submission
	fc_login_show_error_messages(); ?>

	<form id="fc_login_form"  class="fc_form"action="" method="post">
		<fieldset>
			<p>
				<label for="fc_user_Login">Username</label>
				<input name="fc_user_login" id="fc_user_login" class="required" type="text"/>
			</p>
			<p>
				<label for="fc_user_pass">Password</label>
				<input name="fc_user_pass" id="fc_user_pass" class="required" type="password"/>
			</p>
			<p>
				<input type="hidden" name="fc_login_nonce" value="<?php echo wp_create_nonce('fc-login-nonce'); ?>"/>
				<input id="fc_login_submit" type="submit" value="Login"/>
			</p>
		</fieldset>
	</form>
	<?php
}

// logs a member in after submitting a form
function fc_login_member($redirect='') {

	if(isset($_POST['fc_user_login']) && wp_verify_nonce($_POST['fc_login_nonce'], 'fc-login-nonce')) {

		// this returns the user ID and other info from the user name
		$user = get_user_by('login',$_POST['fc_user_login']);

		if(!$user) {
			// if the user name doesn't exist
			fc_login_errors()->add('empty_username', __('Invalid username'));
		}

		if(!isset($_POST['fc_user_pass']) || $_POST['fc_user_pass'] == '') {
			// if no password was entered
			fc_login_errors()->add('empty_password', __('Please enter a password'));
		}

		// check the user's login with their password
		if(!isset($user->user_pass) || !wp_check_password($_POST['fc_user_pass'], $user->user_pass, $user->ID)) {
			// if the password is incorrect for the specified user
			fc_login_errors()->add('empty_password', __('Incorrect password'));
		}

		// retrieve all error messages
		$errors = fc_login_errors()->get_error_messages();

		// only log the user in if there are no errors
		if(empty($errors)) {

			//wp_setcookie($_POST['fc_user_login'], $_POST['fc_user_pass'], true);
			// wp_setcookie( $username, $password, $already_md5, $home, $siteurl, $remember )
			wp_set_auth_cookie($user->ID);
			wp_set_current_user($user->ID, $_POST['fc_user_login']);
			do_action('wp_login', $_POST['fc_user_login']);

			wp_redirect(home_url($redirect));
			exit;
		}
	}
}


// registration form fields
function fc_registration_form_fields() {
	fc_add_new_user();
//print_r($_POST);
	?>
		<?php
		// show any error messages after form submission
		fc_register_show_error_messages(); ?>

	<form id="fc_registration_form" class="fc_form" action="" method="POST">
		<fieldset>
			<p>
				<label for="fc_user_login_reg"><?php _e('Username'); ?></label>
				<input name="fc_user_login_reg" id="fc_user_login_reg" class="required" type="text"/>
			</p>
			<p>
				<label for="fc_user_email"><?php _e('Email'); ?></label>
				<input name="fc_user_email" id="fc_user_email" class="required" type="email"/>
			</p>
			<p>
				<label for="fc_user_first"><?php _e('First Name'); ?></label>
				<input name="fc_user_first" id="fc_user_first" type="text"/>
			</p>
			<p>
				<label for="fc_user_last"><?php _e('Last Name'); ?></label>
				<input name="fc_user_last" id="fc_user_last" type="text"/>
			</p>
			<p>
				<label for="password"><?php _e('Password'); ?></label>
				<input name="fc_user_pass" id="password" class="required" type="password"/>
			</p>
			<p>
				<label for="password_again"><?php _e('Password Again'); ?></label>
				<input name="fc_user_pass_confirm" id="password_again" class="required" type="password"/>
			</p>
			<p>
				<input type="hidden" name="fc_register_nonce" value="<?php echo wp_create_nonce('fc-register-nonce'); ?>"/>
				<input type="submit" value="<?php _e('Register'); ?>"/>
			</p>
		</fieldset>
	</form>
	<?php
}

function fc_add_new_user() {
  if (isset($_POST["fc_user_login_reg"]) && wp_verify_nonce($_POST['fc_register_nonce'],'fc-register-nonce')) :
		$user_login=$_POST["fc_user_login_reg"];
		$user_email=$_POST["fc_user_email"];
		$user_first=$_POST["fc_user_first"];
		$user_last=$_POST["fc_user_last"];
		$user_pass=$_POST["fc_user_pass"];
		$pass_confirm=$_POST["fc_user_pass_confirm"];

		// this is required for username checks - not as of 3.1
		//require_once(ABSPATH . WPINC . '/registration.php');

		if(username_exists($user_login)) {
			// Username already registered
			fc_register_errors()->add('username_unavailable', __('Username already taken'));
		}
		if(!validate_username($user_login)) {
			// invalid username
			fc_register_errors()->add('username_invalid', __('Invalid username'));
		}
		if($user_login == '') {
			// empty username
			fc_login_errors()->add('username_empty', __('Please enter a username'));
		}
		if(!is_email($user_email)) {
			//invalid email
			fc_register_errors()->add('email_invalid', __('Invalid email'));
		}
		if(email_exists($user_email)) {
			//Email address already registered
			fc_login_errors()->add('email_used', __('Email already registered'));
		}
		if($user_pass == '') {
			// passwords do not match
			fc_register_errors()->add('password_empty', __('Please enter a password'));
		}
		if($user_pass != $pass_confirm) {
			// passwords do not match
			fc_register_errors()->add('password_mismatch', __('Passwords do not match'));
		}

		$errors = fc_register_errors()->get_error_messages();

		// only create the user in if there are no errors
		if(empty($errors)) {

			$new_user_id = wp_insert_user(array(
					'user_login'		=> $user_login,
					'user_pass'	 		=> $user_pass,
					'user_email'		=> $user_email,
					'first_name'		=> $user_first,
					'last_name'			=> $user_last,
					'user_registered'	=> date('Y-m-d H:i:s'),
					'role'				=> 'subscriber'
				)
			);
			if($new_user_id) {
				// send an email to the admin alerting them of the registration
				wp_new_user_notification($new_user_id);

				// log the new user in
				wp_setcookie($user_login, $user_pass, true);
				wp_set_current_user($new_user_id, $user_login);
				do_action('wp_login', $user_login);

				// send the newly created user to the home page after logging them in
				wp_redirect(home_url()); exit;
			}

		}

	endif;
}

// used for tracking error messages
function fc_login_errors() {
	static $wp_error; // Will hold global variable safely
	return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}

// used for tracking error messages
function fc_register_errors() {
	static $wp_error; // Will hold global variable safely
	return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}

// displays error messages from form submissions
function fc_login_show_error_messages() {
	if($codes = fc_login_errors()->get_error_codes()) {
		echo '<div class="fc_errors">';
		    // Loop error codes and display errors
		   foreach($codes as $code){
		        $message = fc_login_errors()->get_error_message($code);
		        echo '<span class="error"><strong>' . __('Error') . '</strong>: ' . $message . '</span><br/>';
		    }
		echo '</div>';
	}
}

function fc_register_show_error_messages() {
	if($codes = fc_register_errors()->get_error_codes()) {
		echo '<div class="fc_errors">';
		    // Loop error codes and display errors
		   foreach($codes as $code){
		        $message = fc_register_errors()->get_error_message($code);
		        echo '<span class="error"><strong>' . __('Error') . '</strong>: ' . $message . '</span><br/>';
		    }
		echo '</div>';
	}
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
?>