<?php
	// get Access Token
	function getAccessToken(){
		$auth_url = BASE_URL. "/oauth/v1/token";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $auth_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&scope=read");

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
		if( isset($responseArr->access_token ) ){
			$access_token 	= $responseArr->access_token;
			$token_type 	= $responseArr->token_type;
			$expires_in		= $responseArr->expires_in;
			$scope 			= $responseArr->scope;
			$serviceId 		= $responseArr->serviceId;
			$userId 		= $responseArr->userId;
			$jti 			= $responseArr->jti;
			
			if( isset($_GET['debug']) )
				echo "Access Token: ".$access_token."<br>";
				
			return $access_token;
		}
		else{
			if( isset($_GET['debug']) ){
				echo "<pre>";
				print_r( $responseArr );
			}

			return null;
		}
	}

	function createProfileSearch( $init_param = array('[Group]' => 'API Current STC Exhibitors')){
		global $access_token, $searchID;
		$search_url = BASE_URL. "/api/v1/profile/search";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $search_url);
		curl_setopt($ch, CURLOPT_POST, 1);

		// params
		$params = json_encode( array_merge( $init_param, array("[Member Status]" => "Active")) );
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
			
			if( isset($_GET['debug']) )
				echo "Search ID: ". $searchID."<br>";

			return $searchID;
		}
		else{
			if( isset($_GET['debug']) ){
				echo "<pre>";
				print_r( $responseArr );
			}
			return null;
		}
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
		if( isset($responseArr->totalCount) ){
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
		else{
			if( isset($_GET['debug']) ){
				echo "<pre>";
				print_r( $responseArr );
			}

			return null;
		}
	}
	
	function sendProfileToMap( $profile ){

		// check Exhibitors are existing
		$organization = isset( $profile['[Organization]'] ) ? $profile['[Organization]'] : '';
		if( $organization != '' )
		{
			$select_url = 'https://api.map-dynamics.com/services/exhibitors/select';
			$fields = array(
				'key' 		=> MAP_API_KEY,
				'company'	=> $organization
			);

			$api_url = $select_url ."/?". http_build_query($fields);

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

			$responseArr 	= json_decode( $result );
			
			// if already exists skip
			if( !empty($responseArr->results) ) 
			{
				return;
			}
		}
		
		// if non exists insert
		$fields = array(
			'key' 			=> MAP_API_KEY,
			'Company'		=> $organization,
			'Address_1' 	=> isset($profile['[Address | Primary | Line 1]']) ? $profile['[Address | Primary | Line 1]'] : '',
			'Address_2' 	=> isset($profile['[Address | Primary | Line 2]']) ? $profile['[Address | Primary | Line 2]'] : '',
			'City' 			=> isset($profile['[Address | Primary | City]']) ? $profile['[Address | Primary | City]'] : '',
			'State' 		=> isset($profile['[Address | Primary | State]']) ? $profile['[Address | Primary | State]'] : '',
			'Zip' 			=> isset($profile['[Address | Primary | Zip]']) ? $profile['[Address | Primary | Zip]'] : '',
			'Website' 		=> isset($profile['Website']) ? $profile['Website'] : '',
			'Twitter_Link' 	=> '',
			'Facebook_Link' => '',
			'First_Name' 	=> isset($profile['[Name | First]']) ? $profile['[Name | First]'] : '',
			'Last_Name' 	=> isset($profile['[Name | Last]']) ? $profile['[Name | Last]'] : '',
			'Title' 		=> isset($profile['Current Title']) ? $profile['Current Title'] : '',
			'Email' 		=> isset($profile['[Email | Primary]']) ? $profile['[Email | Primary]'] : '',
			'Phone' 		=> isset($profile['[Phone | Primary]']) ? $profile['[Phone | Primary]'] : '',
			'Fax' 			=> '',
			'Admin_First_Name' 	=> '',
			'Admin_Last_Name' 	=> '',
			'Admin_Title' 	=> '',
			'Admin_Email' 	=> '',
			'Admin_Phone' 	=> ''
		);
		echo $organization."<br>";
		
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