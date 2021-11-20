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
		$item[] = isset($profile['[Organization Email]'])?$profile['[Organization Email]']:'';
		$item[] = isset($profile['[Organization Phone]'])?$profile['[Organization Phone]']:'';
		$item[] = isset($profile['Corporate Company Description'])?$profile['Corporate Company Description']:'';
		$item[] = isset($profile['Corporate Services'])?$profile['Corporate Services']:'';
		$item[] = isset($profile['Website'])?$profile['Website']:'';
		$item[] = isset($profile['Booth Assignment(s) STC'])?$profile['Booth Assignment(s) STC']:'';
		$item[] = isset($profile['Sponsorship Type - STC '])?$profile['Sponsorship Type - STC ']:'';

		array_push( $res_array, $item );
	}

	$aj_res = array(
		"draw"=> $draw,
		'recordsTotal'=> $totalCount,
		'recordsFiltered'=> $totalCount,
		"data"=> $res_array
	);

	echo json_encode( $aj_res );
?>

