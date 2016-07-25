<?php
require_once(UCICURL_PATH.'lib/twitteroauth/autoload.php');

use Abraham\TwitterOAuth\TwitterOAuth;

$connection = new TwitterOAuth(
	get_option('uci_results_twitter_consumer_key', ''),
	get_option('uci_results_twitter_consumer_secret', ''),
	get_option('uci_results_twitter_access_token', ''),
	get_option('uci_results_twitter_access_token_secret', '')
);
$content = $connection->get("account/verify_credentials");

print_r($content);
?>