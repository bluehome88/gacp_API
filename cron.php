<?php
	
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');

	global $access_token, $searchID;

	require_once("config.php");
	require_once("functions.php");

	/************* Step 1: Client Credentials Grant Type *************/
	$access_token = getAccessToken();

	/************* Step 2: Create a Profile Search *************/
	$searchID = createProfileSearch();

	// set pageSize and pageNum
	$pageNum = 1; $pageSize = 10;

	do{
		$response = getProfileList($pageNum, $pageSize);
		if( !$response ){
			echo '<br>--------------finished--------------------';
			exit;		
		}
			
		$profile_list 		= $response->profiles;
		foreach( $profile_list as $profile){
		    $profile = (array) $profile;

			$orgName = isset($profile['[Organization]'])?$profile['[Organization]']:'';
		    if( $orgName ){
		    	$searchID = createProfileSearch( array('[Organization]' => $orgName ) );
		    	$response = getProfileList();
		    	$org_profiles = $response->profiles;

		    	foreach( $org_profiles as $org_profile ){
			    	$org_profile = (array) $org_profile;
			    	if( !isset($org_profile['[Organization Email]']) )
				    	continue;
				    	
					$profile['org_info'] = $org_profile;
		
					break;
		    	}
		    }


		    sendProfileToMap( $profile );
		}
		$pageNum++;

	} while( !empty( $profile_list ) );

	echo "End!";
	exit;
?>