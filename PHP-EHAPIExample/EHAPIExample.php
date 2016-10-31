<?php

require("OAuth.php");

// In order to make a request to the API, you will need to know the Base URI for your LiveEngage account. 
// To find the Base URI for your account, make a GET request to the following URL:
// https://api.liveperson.net/api/account/{Your Account Number}/service/engHistDomain/baseURI.json?version=1.0
// You should get a response that is similar to this:
// {
//  "service": "engHistDomain",
//  "account": "56072331",
//  "baseURI": "va-a.enghist.liveperson.net"
// }

// setup account
$account = [
	'consumerKey' => 'Your App Key',
	'consumerSecret' => 'Your Secret',
	'token' => 'Your Access Token',
	'tokenSecret' => 'Your Token Secret',
	'id' => 'Your Account ID',
	'baseuri' => 'Your Base URI'
];

// setup url
$url_no = "https://".$account['baseuri']."/interaction_history/api/account/".$account['id']."/interactions/search?offset=0&limit=10";
$url = "https://".$account['baseuri']."/interaction_history/api/account/".$account['id']."/interactions/search";
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
curl_setopt($ch, CURLOPT_POSTFIELDS, '{"start":{ "from":1477052344000,"to": 1477657144000 }}' ); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

// do request
$rsp = curl_exec($ch);

// decode json into objects/arrays
$result = json_decode($rsp);

// print objects/arrays
print_r($rsp);

?>
