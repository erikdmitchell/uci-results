<?php

/**
 * UCIResultsAPICaller class.
 */
class UCIResultsAPICaller {

	private $_app_id;
  private $_app_key;
  private $_api_url;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $app_id
	 * @param mixed $app_key
	 * @param mixed $api_url
	 * @return void
	 */
	public function __construct($app_id, $app_key, $api_url) {
		$this->_app_id = $app_id;
		$this->_app_key = $app_key;
		$this->_api_url = $api_url;
	}

	/**
	 * request function.
	 *
	 * @access public
	 * @param string $controller (default: '')
	 * @param string $action (default: '')
	 * @param array $request_params (default: array())
	 * @return void
	 */
	public function request($controller='', $action='', $request_params=array()) {
		// append our controller and action //
		$request_params['controller']=$controller;
		$request_params['action']=$action;

		// encrypt the request parameters
    $enc_request = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->_app_key, json_encode($request_params), MCRYPT_MODE_ECB));

    // create the params array, which will be the POST parameters
    $params = array();
    $params['enc_request'] = $enc_request;
    $params['app_id'] = $this->_app_id;

	  //initialize and setup the curl handler
	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_URL, $this->_api_url);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_POST, count($params));
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

	  //execute the request
	  $result = curl_exec($ch);

	  //json_decode the result
	  $result = json_decode($result);

		//check if we're able to json_decode the result correctly
		if( $result == false || isset($result->success) == false ) {
		  throw new Exception('Request was not correct');
		}

		//if there was an error in the request, throw an exception
		if( $result->success == false ) {
		  throw new Exception($result->errormsg);
		}

		//if everything went great, return the data
		return $result->data;
	}

}
?>