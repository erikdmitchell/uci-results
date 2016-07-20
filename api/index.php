<?php
// include wordpress //
require_once( ABSPATH . 'wp-load.php' );

// Define api path
define('API_PATH', realpath(dirname(__FILE__)));

// wrap the whole thing in a try-catch block to catch any wayward exceptions! //
try {
    // get all of the parameters in the POST/GET request - check that query vars aren't passed either //
    $params = $_REQUEST;

    if (get_query_var('controller'))
    	$params['controller']=get_query_var('controller');

    if (get_query_var('action'))
    	$params['action']=get_query_var('action');

    // get the controller and format it correctly so the first letter is always capitalized //
    if (isset($params['controller'])) :
	    $controller = ucfirst(strtolower($params['controller']));
	  else :
	  	$controller='';
	  endif;

    // get the action //
    if (isset($params['action'])) :
	    $action = strtolower($params['action']);
	  else :
	  	$action='';
	  endif;

    //check if the controller exists. if not, throw an exception //
    if (file_exists(API_PATH."/controllers/{$controller}.php")) :
      include_once API_PATH."/controllers/{$controller}.php";
		else :
			throw new Exception('Controller is invalid.');
    endif;

    //create a new instance of the controller, and pass it the parameters from the request
    $controller = new $controller($params);

    //check if the action exists in the controller. if not, throw an exception.
    if (method_exists($controller, $action) === false) :
			throw new Exception('Action is invalid.');
    endif;

    //execute the action
    $result['data'] = $controller->$action();
    $result['success'] = true;

} catch( Exception $e ) {
    //catch any exceptions and report the problem
    $result = array();
    $result['success'] = false;
    $result['errormsg'] = $e->getMessage();
}

//echo the result of the API call
echo json_encode($result);
exit();
?>