<?php 
	require_once('../classes/auth.class.php');
	require_once('../classes/users.class.php');

	$postdata = file_get_contents("php://input");
	$postdata = json_decode($postdata);

	if(isset($_SERVER['HTTP_AUTHORIZATION'])){
		$tmp = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
		$token = $tmp[1];
		$verifToken = auth::vefifySignAuthToken($token);
		if($verifToken['error'] === true){
			echo json_encode(returnResponse(true,"AuthToken Not Valid !"));
			return false;
		}
		if(isset($postdata->action)){
			switch ($postdata->action) {
				case 'getAllUsers':
					echo json_encode(users::getAll());
					return false;
					break;
				default:
					echo json_encode(returnResponse(true,"No Action Provided ! "));
					return false;
					break;
			}
		}else{
			return false;
		}
	}
?>