<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

	require_once("config.php");

	/************* Step 1: Client Credentials Grant Type *************/
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

	/************* Step 2: Create a Profile Search *************/
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
	$profilesUrl 	= $responseArr->profilesUrl;

echo "Search ID: ". $searchID;

	/************* Step 3: Get a List of Profiles by Search ID *************/
	$list_url = BASE_URL. "/api/v1/profile?searchId=".$searchID;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $list_url);

	// set header
	$header = array();
	$header[] = 'Cache-Control: no-cache';
	$header[] = 'Content-type: application/json';
	$header[] = 'Authorization: Bearer'.$access_token;
	curl_setopt($ch, CURLOPT_HTTPHEADER,$header);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$list_result = curl_exec($ch);
	curl_close($ch);

	$responseArr 		= json_decode( $list_result );
	$totalCount 		= $responseArr->totalCount;
	$count 				= $responseArr->count;
	$pageNumber 		= $responseArr->pageNumber;
	$pageSize 			= $responseArr->pageSize;
	$totalPageCount 	= $responseArr->totalPageCount;
	$firstPageUrl 		= $responseArr->firstPageUrl;
	$previousPageUrl 	= $responseArr->previousPageUrl;
	$nextPageUrl 		= $responseArr->nextPageUrl;
	$lastPageUrl 		= $responseArr->lastPageUrl;
	$expireDate 		= $responseArr->expireDate;
	$profiles 			= $responseArr->profiles;

echo "<pre>";
print_r( $profiles );
?>