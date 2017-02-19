<?php 
	require_once('../classes/auth.class.php');

	$postdata = file_get_contents("php://input");
	$postdata = json_decode($postdata);

	if(isset($postdata->action)){
		switch ($postdata->action){
			case 'signIn':
				if(!isset($postdata->login) || !isset($postdata->password)){
					echo json_encode(returnResponse(true,"Missing parameter to signIn"));
					return false;
				}


				$result = auth::createAuthToken($postdata->login , $postdata->password);

				if($result['error'] === true){
					echo json_encode(returnResponse(true,$result['data']));
					return false;
				}

				$token = (string)$result['data'];
				echo json_encode(returnResponse(false,$token));
				return false;
				break;
			default:
				echo json_encode(returnResponse(true,'Invalid Action to WebService'));
				return false;
				break;	
		}
	}

	echo json_encode(returnResponse(true,'No Action provided to WebService'));
	return false;
?>