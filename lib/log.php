<?php

/**
 * _log function.
 *
 * @access private
 * @param string $msg (default: '')
 * @return void
 */
function _log($msg='') {
	ucicurl_log_to_file(UCICURL_PATH.'log.txt', $msg);
}

/**
 * ucicurl_log_to_file function.
 *
 * @access public
 * @param string $filename (default: 'log.txt')
 * @param string $msg (default: '')
 * @return void
 */
function ucicurl_log_to_file($filename='log.txt', $msg='') {
	// open file
	$fd = fopen($filename, "a");

	// check message for output
	if (is_array($msg) || is_object($msg))
		$msg=print_r($msg, true);

	// append date/time to message
	$str = "[" . date("Y/m/d h:i:s", time()) . "] " . $msg;

	// write string
	fwrite($fd, $str . "\n");

	// close file
	fclose($fd);
}
?>