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
				case 'AddNewUser':
					if(!isset($postdata->user)){
						echo json_encode(returnResponse(true,"Missing Parameter to complete AddNewUser"));
						return false;
					}

					$user = json_decode($postdata->user);

					$newUser = new users();
					foreach ($user as $key => $value) {
						if(isset($newUser->$key)){
							$newUser->$key = $value;
						}
					}
					if(isset($user->id)){
						$newUser->dupdate = date('Y-m-d H:i:s');
						$newUser->uupdate = $user->id;
					}
					echo json_encode($newUser->save());
					return false;
					break;	
				case 'deleteUser':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing Parameter to complete deleteUser"));
						return false;
					}

					echo json_encode(users::deleteUserById($postdata->id));
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