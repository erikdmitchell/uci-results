<?php
require_once(UCICURL_PATH.'lib/twitteroauth/autoload.php');

use Abraham\TwitterOAuth\TwitterOAuth;

global $uci_results_twitter;


class UCIResultsTwitter {

	protected $connection;

	public function __construct() {
		$this->connection = new TwitterOAuth(
			get_option('uci_results_twitter_consumer_key', ''),
			get_option('uci_results_twitter_consumer_secret', ''),
			get_option('uci_results_twitter_access_token', ''),
			get_option('uci_results_twitter_access_token_secret', '')
		);
	}

	public function update_status($status='') {
		if (empty($status))
			return 'No status to update.';

		$msg='';

		// update status //
		$status_post=$this->connection->post("statuses/update", ["status" => $status]);

		// check if it worked or not //
		if ($this->connection->getLastHttpCode() == 200) :
			$msg="Twitter status updated.";
		else :
	  	$msg="Tweet failed to send: ";

			foreach ($status_post->errors as $error) :
				$msg.=$error->message;
			endforeach;
		endif;

		return $msg;
	}
}

$uci_results_twitter=new UCIResultsTwitter();


/**
 * uci_results_post_results_to_twitter function.
 *
 * @access public
 * @return void
 */
function uci_results_post_results_to_twitter() {
	if (get_option('uci_results_post_results_to_twitter', 0))
		return true;

	return false;
}
?>