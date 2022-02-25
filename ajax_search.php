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

	$pageSize = isset($_GET['length']) ? $_GET['length'] : 10;
	$page_start = isset($_GET['start']) ? $_GET['start'] : 1;
	$draw = isset($_GET['draw']) ? $_GET['draw'] : 1;

	$pageNum = intval( $page_start / $pageSize ) + 1;

	$response = getProfileList($pageNum, $pageSize);
	if( !$response ){
		$aj_res = array(
			"draw"=> $_GET['draw'],
			'recordsTotal'=> 0,
			'recordsFiltered'=> 0,
			"data"=> array()
		);
	
		echo json_encode( $aj_res );
		exit;		
	}
		
	$profile_list 		= $response->profiles;
	$totalCount 		= $response->totalCount;

	$index = $pageSize * ($pageNum -1) + 1; $res_array = array();
	foreach( $profile_list as $profile){
	    $profile = (array) $profile;
	    $item = array();

		$item[] = $index++;
		$item[] = isset($profile['[Profile ID]'])?$profile['[Profile ID]']:'';
		$item[] = isset($profile['[Name | Last]'])?$profile['[Name | Last]']:'';
		$item[] = isset($profile['[Name | First]'])?$profile['[Name | First]']:'';
		$item[] = isset($profile['[Expiration Date]'])?$profile['[Expiration Date]']:'';
		$item[] = isset($profile['[Group]'])?$profile['[Group]']:'';
		$item[] = isset($profile['[Organization]'])?$profile['[Organization]']:'';

		$item[] = '';
		$item[] = '';
		$item[] = '';
		$item[] = '';
		$item[] = '';
		$item[] = '';
		$item[] = '';
		$item[] = '';

    
	    // Organization info
	    $orgName = isset($profile['[Organization]'])?$profile['[Organization]']:'';
	    if( $orgName ){
	    	$searchID = createProfileSearch( array('[Organization]' => $orgName ) );
	    	$response = getProfileList();
	    	$org_profiles = $response->profiles;
	    	
	    	foreach( $org_profiles as $org_profile ){
		    	$org_profile = (array) $org_profile;
		    	if( !isset($org_profile['[Organization Email]']) )
			    	continue;
			    	
				$item[7] = isset($org_profile['[Profile ID]'])?$org_profile['[Profile ID]']:'';
				$item[8] = isset($org_profile['[Organization Email]'])?$org_profile['[Organization Email]']:'';
				$item[9] = isset($org_profile['[Organization Phone]'])?$org_profile['[Organization Phone]']:'';
				$corp_desc = isset($org_profile['Corporate Company Description'])?$org_profile['Corporate Company Description']:'';
				if (strlen($corp_desc) > 60)
				   $corp_desc = substr($corp_desc, 0, 57) . '...';
				$item[10] = $corp_desc;
				$item[11] = isset($org_profile['Corporate Services'])?$org_profile['Corporate Services']:'';
				$item[12] = isset($org_profile['Website'])?$org_profile['Website']:'';
				$item[13] = isset($profile['Booth Assignment(s) STC'])?$profile['Booth Assignment(s) STC']:'';
				$item[14] = isset($profile['Booth Type - STC'])?$profile['Booth Type - STC']:'';
	
				break;
	    	}
	    }

		array_push( $res_array, $item );
	}
	
	if( isset($_GET['debug']) ){
		echo "<pre>";
		print_r( $res_array );
		exit;
	}

	$aj_res = array(
		"draw"=> $draw,
		'recordsTotal'=> $totalCount,
		'recordsFiltered'=> $totalCount,
		"data"=> $res_array
	);

	echo json_encode( $aj_res );
?>

