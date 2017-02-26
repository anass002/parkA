<?php
	require_once('../classes/auth.class.php');
	require_once('../classes/purshase.class.php');

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
			switch ($postdata->action){
				case 'getAllPurshases':
					echo json_encode(purshase::getAll());
					return false;
					break;
				case 'deletePurshase':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing parameter to complete deletePaper"));
						return false;
					}
					echo json_encode(purshase::deletePurshaseById($postdata->id));
					return false;
					break;
				case 'savePurshase':
					if(!isset($postdata->achat)){
						echo json_encode(returnResponse(true,"Missing parameter to complete savePaper"));
						return false;
					}
					$achat = json_decode($postdata->achat);

					$myPurshase = new purshase();

					foreach ($achat as $key => $value) {
						if(isset($myPurshase->$key)){
							$myPurshase->$key = $value;
						}
					}
					if(isset($achat->id)){
						$myPurshase->dupdate = date('Y-m-d H:i:s');
						$myPurshase->uupdate = $achat->id;
					}
					echo json_encode($myPurshase->save());
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