<?php

require("/OAuth.php");

// setup account
$account = [
	'consumerKey' => 'key',
	'consumerSecret' => 'secret',
	'token' => 'token',
	'tokenSecret' => 'tokensecret',
	'id' => 'accountid'
];

// setup url
$url_no = "https://lo.enghist.liveperson.net/interaction_history/api/account/".$account['id']."/interactions/search?limit=100&offset=100";
$url = "https://lo.enghist.liveperson.net/interaction_history/api/account/".$account['id']."/interactions/search";
$args = array();

// create token + consumer set
$consumer = new OAuthConsumer($account['consumerKey'], $account['consumerSecret']);
$token = new OAuthToken($account['token'], $account['tokenSecret']);

// sign request
$request = OAuthRequest::from_consumer_and_token($consumer, $token,"POST", $url_no, $args);
$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $token);

// setup url
$url = sprintf("%s?%s", $url, OAuthUtil::build_http_query($args));  
$ch = curl_init();

// add headers for request
$headers = array($request->to_header());
$headers[] = "Content-Type: application/json";

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
curl_setopt($ch, CURLOPT_URL, $url_no);  
curl_setopt($ch, CURLOPT_POST, 1 );

// body
curl_setopt($ch, CURLOPT_POSTFIELDS, '{"start":{ "from":000000000,"to": 000000000 }}' ); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

// do request
$rsp = curl_exec($ch);

// decode json into objects/arrays
$result = json_decode($rsp);

// print objects/arrays
print_r($result);

?>
