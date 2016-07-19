<?php
// Define api path
define('API_PATH', realpath(dirname(__FILE__)));

// Define path to data folder
define('DATA_PATH', realpath(dirname(__FILE__).'/data'));

//include our models
//include_once 'models/TodoItem.php';

//wrap the whole thing in a try-catch block to catch any wayward exceptions!
try {
    //get all of the parameters in the POST/GET request
    $params = $_REQUEST;

    //get the controller and format it correctly so the first
    //letter is always capitalized
    $controller = ucfirst(strtolower($params['controller']));

    //get the action and format it correctly so all the
    //letters are not capitalized, and append 'Action'
    $action = strtolower($params['action']).'Action';

    //check if the controller exists. if not, throw an exception
    if( file_exists(API_PATH."/controllers/{$controller}.php") ) {
        include_once API_PATH."/controllers/{$controller}.php";
    } else {
        throw new Exception('Controller is invalid.');
    }

    //create a new instance of the controller, and pass
    //it the parameters from the request
    $controller = new $controller($params);

    //check if the action exists in the controller. if not, throw an exception.
    if( method_exists($controller, $action) === false ) {
        throw new Exception('Action is invalid.');
    }

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