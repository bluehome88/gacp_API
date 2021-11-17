<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

	require_once("config.php");

	/************* Step 1: Authorization *************/
	$auth_url = BASE_URL. "/oauth/v1/token";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $auth_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

	// Set Header
	$header = array();
	$header[] = 'Cache-Control: no-cache';
	$header[] = 'Content-type: application/x-www-form-urlencoded';
	$header[] = 'Authorization: ' . clientCredentials;
	curl_setopt($ch, CURLOPT_HTTPHEADER,$header);

	// Receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	curl_close($ch);

	$responseArr 	= json_decode( $response );
	$access_token 	= $responseArr->access_token;
	$token_type 	= $responseArr->token_type;
	$expires_in		= $responseArr->expires_in;
	$scope 			= $responseArr->scope;
	$serviceId 		= $responseArr->serviceId;
	$userId 		= $responseArr->userId;
	$jti 			= $responseArr->jti;

echo "Access Token: ".$access_token."<br><br>";

	/************* Step 2: Create Search and get search ID *************/
	$search_url = BASE_URL. "/api/v1/profile/search";
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $search_url);
	curl_setopt($ch, CURLOPT_POST, 1);

	// params
	$params = json_encode(array(
				'[Name | Last]' => 'Abel', 
				'[Member Status]' => 'Active'
			));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params );
	
	// set header
	$header = array();
	$header[] = 'Cache-Control: no-cache';
	$header[] = 'Content-type: application/json';
	$header[] = 'Content-Length: '.strlen($params);
	$header[] = 'Authorization: Bearer'.$access_token;
	curl_setopt($ch, CURLOPT_HTTPHEADER,$header);

	// Receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$search_result = curl_exec($ch);
	curl_close($ch);

	$responseArr 	= json_decode( $search_result );
	$timestamp 		= $responseArr->timestamp;
	$status 		= $responseArr->status;
	$message		= $responseArr->message;
	$searchID 		= $responseArr->id;
	$url 			= $responseArr->url;
	$profileUrl 	= $responseArr->profileUrl;

echo "Search ID: ". $searchID;

?>