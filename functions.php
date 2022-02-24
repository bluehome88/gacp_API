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

		$organization = isset( $profile['[Organization]'] ) ? $profile['[Organization]'] : '';
		// if non exists insert
		$call = "exhibitors/insert";
		$booths = isset($profile['Booth Assignment(s) STC'])?$profile['Booth Assignment(s) STC']:'';
		$booth_value = ''; $count= 0;
		if( is_array( $booths )){ 
			foreach( $booths as $booth ){
				if( $count == 0 )
					$booth_value = intval( $booth );
				else
					$booth_value .= ",". intval( $booth );
					
				$count++;
			}
		}
		else
			$booth_value = $booths;

		$fields = array(
			'key' 			=> MAP_API_KEY,
			'company'		=> $organization,
			'description'	=> isset($profile['org_info']['Corporate Company Description']) ? $profile['org_info']['Corporate Company Description'] : '',
			'website'		=> isset($profile['org_info']['Website']) ? $profile['org_info']['Website'] : '',
			'address_1'		=> isset($profile['org_info']['[Address | Primary | Line 1]']) ? $profile['org_info']['[Address | Primary | Line 1]'] : '',
			'address_2'		=> isset($profile['org_info']['[Address | Primary | Line 2]']) ? $profile['org_info']['[Address | Primary | Line 2]'] : '',
			'city'			=> isset($profile['org_info']['[Organization Address | City]']) ? $profile['org_info']['[Organization Address | City]'] : '',
			'state'			=> isset($profile['org_info']['[Organization Address | State]']) ? $profile['org_info']['[Organization Address | State]'] : '',
			'country'		=> isset($profile['org_info']['[Organization Address | Country]']) ? $profile['org_info']['[Organization Address | Country]'] : '',
			'zip'			=> isset($profile['org_info']['[Organization Address | Zip]']) ? $profile['org_info']['[Organization Address | Zip]'] : '',
			'first_name'	=> isset($profile['[Name | First]'])?$profile['[Name | First]']:'',
			'last_name'		=> isset($profile['[Name | Last]'])?$profile['[Name | Last]']:'',
			'title'			=> isset($profile['Current Title'])?$profile['Current Title']:'',
			'email'			=> isset($profile['org_info']['[Email | Primary]']) ? $profile['org_info']['[Email | Primary]'] : '',
			'phone'			=> isset($profile['org_info']['[Organization Phone]']) ? $profile['org_info']['[Organization Phone]'] : '',
			'fax'			=> '',
			'keywords'		=> '',
			'image'			=> '',
			'twitter_link'	=> '',
			'facebook_link'	=> '',
			'admin_first_name'	=> isset($profile['[Name | First]'])?$profile['[Name | First]']:'',
			'admin_last_name'	=> isset($profile['[Name | Last]'])?$profile['[Name | Last]']:'',
			'admin_title'	=> isset($profile['Current Title'])?$profile['Current Title']:'',
			'admin_email'	=> isset($profile['[Email | Primary]'])?$profile['[Email | Primary]']:'',
			'admin_phone'	=> isset($profile['[Phone | Primary]'])?$profile['[Phone | Primary]']:'',
			'internal_memo'	=> '',
			'booths'		=> $booth_value
		);

		if( $organization != '' )
		{
			$select_url = 'https://api.map-dynamics.com/services/exhibitors/select';
			$ex_fields = array(
				'key' 		=> MAP_API_KEY,
				'company'	=> $organization
			);

			$api_url = $select_url ."/?". http_build_query($ex_fields);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $api_url);
			curl_setopt($ch, CURLOPT_POST, 1);

			// params
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($ex_fields) );

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
				$ex_results = $responseArr->results;
				$fields['exhibitor_id'] = $ex_results[0]->ID;

				$call = "exhibitors/update";
			}
		}
		
		$access_hash = md5(MAP_API_SECRET.$call);
		$post_field = array(
			"key" 			=> MAP_API_KEY,
			"access_hash" 	=> $access_hash,
			"call" 			=> $call,
			"format"		=> "json"
		);
		
		$api_url = "https://api.map-dynamics.com/services/auth/";
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_POST, 1);
	
		// params
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_field) );
		
		// set header
		$header = array();
		$header[] = 'Cache-Control: no-cache';
		$header[] = 'Content-type: application/x-www-form-urlencoded';
	
		curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
	
		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
		$result = curl_exec($ch);
		curl_close($ch);
	
		$responseArr = json_decode( $result );
		$access_token = $responseArr->results->hash;
echo "<br>$call----$organization-----". $access_token;
		
		// Insert Exhibitors to map dynamics
		$fields['hash'] = $access_token;
/*
echo "<pre>";
print_r( $fields ); 
*/

// Log code for cron
error_log( date("m-d-Y H:i:s") .": ".$organization."\n", 3, "./cron_log.txt");
		
		$api_url = "https://api.map-dynamics.com/services/".$call."/?". http_build_query($fields);

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
	
		$response = json_decode( $result );
echo "<pre>";
print_r( $response );
		if( isset( $response->results ) )
			error_log( date("m-d-Y H:i:s") .": ".print_r( $response->results, true)."\n\r", 3, "./cron_log.txt");
		else
			error_log( date("m-d-Y H:i:s") .": ".print_r( $response->status_details, true)."\\rn", 3, "./cron_log.txt");

	}
?>