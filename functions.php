<?php
	// get Access Token
	function getAccessToken(){
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
		$search_url = BASE_URL. "/api/v1/profile/search";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $search_url);
		curl_setopt($ch, CURLOPT_POST, 1);

		// params
		$params = json_encode(array(
					'"[Organization Phone]"' => "",
					'[Group]' => 'API Current STC Exhibitors',
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
		if( isset($responseArr->id) ){
			$timestamp 		= $responseArr->timestamp;
			$status 		= $responseArr->status;
			$message		= $responseArr->message;
			$searchID 		= $responseArr->id;
			$url 			= $responseArr->url;
			$profilesUrl 	= $responseArr->profilesUrl;

			return $searchID;
		}
		else
			return null;		
		
	}

	function getProfileList( $pageNumber = 1, $pageSize = 10 ){
		global $searchID, $access_token;
		
		if( !$searchID )
			return null;

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
	
	function sendProfileToMap(){
		$fields = array(
			'key' 			=> MAP_API_KEY,
			'Company'		=> 'Test Company',
			'Address_1' 	=> '',
			'Address_2' 	=> '',
			'City' 			=> '',
			'State' 		=> '',
			'Zip' 			=> '',
			'Website' 		=> '',
			'Twitter_Link' 	=> '',
			'Facebook_Link' => '',
			'First_Name' 	=> '',
			'Last_Name' 	=> '',
			'Title' 		=> '',
			'Email' 		=> '',
			'Phone' 		=> '',
			'Fax' 			=> '',
			'Admin_First_Name' 	=> '',
			'Admin_Last_Name' 	=> '',
			'Admin_Title' 	=> '',
			'Admin_Email' 	=> '',
			'Admin_Phone' 	=> ''
		);
	
		$api_url = MAP_BASE_URL ."/?". http_build_query($fields);
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_POST, 1);
	
		// params
		$params = json_encode( $fields );
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields) );
		
		// set header
		$header = array();
		$header[] = 'Cache-Control: no-cache';
		$header[] = 'Content-type: application/x-www-form-urlencoded';
	
		curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
	
		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
		$result = curl_exec($ch);
		curl_close($ch);
	}
?>