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

	$pageSize = $_GET['length'];
	$pageNum = intval($_GET['start'] / $pageSize) + 1;

	$response = getProfileList($pageNum, $pageSize);
	$profile_list 		= $response->profiles;
	$totalCount 		= $response->totalCount;

	$index = 1; $res_array = array();
	foreach( $profile_list as $profile){
	    $profile = (array) $profile;
	    
	    $item = array();
	    
		$item[] = $index++;
		$item[] = $profile['[Profile ID]'];
		$item[] = $profile['[Name | Last]'];
		$item[] = $profile['[Name | First]'];
		$item[] = $profile['Current Title'];
		$item[] = $profile['[Organization]'];
		$item[] = $profile['OKEYID'];
		$item[] = $profile['[Email | Primary]'];
		$item[] = $profile['[Expiration Date]'];
		$item[] = $profile['[Member Type]'];

		array_push( $res_array, $item );
	}

	$aj_res = array(
		"draw"=> $_GET['draw'],
		'recordsTotal'=> $totalCount,
		'recordsFiltered'=> $totalCount,
		"data"=> $res_array
	);

	echo json_encode( $aj_res );
?>

