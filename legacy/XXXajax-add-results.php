<?php
// Force a short-init since we just need core WP, not the entire framework stack
define( 'SHORTINIT', true );

// Build the wp-load.php path from a plugin/theme
$wp_root_path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );

// Require the wp-load.php file (which loads wp-config.php and bootstraps WordPress)
require( $wp_root_path . '/wp-load.php' );

// Include the now instantiated global $wpdb Class for use
global $wpdb;
 
// Do your PHP code for rapid AJAX calls with WP!
$response_arr=array(); // returns our success/error message(s)
$results=json_decode($_POST['info']); // stdClass Object
$table='uci_races';

// build data array ..
$data=array(
	'data' => serialize($results),
	'code' => build_race_code($results),
);

if (!check_for_dups($data['code'])) :
	if ($wpdb->insert($table,$data)) :
		$response_arr['type']='updated';
		$response_arr['value']='Added '.$data['code'].' to database.'; 
	else :
		$response_arr['type']='error';
		$response_arr['value']='Unable to insert '.$data['code'].' into the database.'; 
	endif;
else :
		$response_arr['type']='error';
		$response_arr['value']=$data['code']'. is already in the database.';
endif;

echo json_encode($response_arr);


/**
 * @param object $obj - race object
 * takes the race name and date to build a string which becomes our "code" to prevent dups
 * returns string
**/
function build_race_code($obj) {
	$code=$obj->name.$obj->date; // combine name and date
	$code=str_replace(' ','',$code); // remove spaces
	$code=strtolower($code); // make lowercase
	
	return $code;
}

/**
 * @param string $code - race code
 * compares race code to those in db; if true, there's a dup and we do not enter race
 * returns true/false
**/
function check_for_dups($code) {
	global $wpdb;
	$table='uci_races';
	
	$races_in_db=$wpdb->get_results("SELECT code FROM ".$table);
	
	if (count($races_in_db)!=0) :
		foreach ($races_in_db as $race) :
			if ($race->code==$code) :
				return true;
			endif;
		endforeach;
	endif;
	
	return false;
}
?>