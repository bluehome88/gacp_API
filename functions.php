<?php
	// get Access Token
	function getAccessToken(){

// $access_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZSI6WyJyZWFkIiwid3JpdGUiXSwiZXhwIjoxNjM3MzMxOTIyLCJzZXJ2aWNlSWQiOjkyMzgsInVzZXJJZCI6MjAwMTYwODI3MCwiYXV0aG9yaXRpZXMiOlsiUk9MRV9QUk9GSUxFX0FETUlOIiwiUk9MRV9GT1JNU19BRE1JTiIsIlJPTEVfTUVNQkVSU0hJUF9BRE1JTiIsIlJPTEVfQ01TX0FETUlOIiwiUk9MRV9VU0VSIiwiUk9MRV9GSU5BTkNJQUxfQURNSU4iLCJST0xFX1JFUE9SVElOR19BRE1JTiIsIlJPTEVfQURNSU4iLCJST0xFX0NPTU1VTklUWV9BRE1JTiIsIlJPTEVfUFJPRklMRV9JTVBPUlRfQURNSU4iXSwianRpIjoiZTYyYzAyMDUtMzAzZS00ZmQwLWE3ZWEtZDdjNjI2YTFhZTFhIiwiY2xpZW50X2lkIjoiSVp5b1d6ZFVPVjU3WlBiZzJNaUIifQ.EykbPgrcoeR1KF5BQ8ZUavYC2H75_gLJqO3oC2-LNJQ";

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

		return $access_token;
	}

	function createProfileSearch(){
		global $access_token, $searchID;
//$searchID = "ca28a1de-5c9e-497b-b4d1-9e1c113ccbbb";
		$search_url = BASE_URL. "/api/v1/profile/search";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $search_url);
		curl_setopt($ch, CURLOPT_POST, 1);

		// params
		$params = json_encode(array(
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
		return $searchID;
	}

	function getProfileList( $pageNumber = 1, $pageSize = 10 ){
		global $searchID, $access_token;

		$list_url = BASE_URL. "/api/v1/profile?searchId=".$searchID."&pageNumber=".$pageNumber."&pageSize=".$pageSize;

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

		return $responseArr;
	}
	
	function sendProfileToMap( $profile ){
		
	}
?>