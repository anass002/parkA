<?php 
	require_once('../classes/auth.class.php');
	require_once('../classes/missions.class.php');


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
				case 'getAllMissions':
					echo json_encode(missions::getAll());
					return false;
					break;
				case 'getNextMissions':
					echo json_encode(missions::getNextMissions());
					return false;
					break;	
				case 'deleteMission':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing parameter to complete deleteMission"));
						return false;
					}
					echo json_encode(missions::deleteMissionById($postdata->id));
					return false;
					break;
				case 'saveMission':
					if(!isset($postdata->mission)){
						echo json_encode(returnResponse(true,"Missing parameter to complete saveMission"));
						return false;
					}
					$mission = json_decode($postdata->mission);

					$myMission = new missions();

					foreach ($mission as $key => $value) {
						if(isset($myMission->$key)){
							$myMission->$key = $value;
						}
					}
					if(isset($mission->id)){
						$myMission->dupdate = date('Y-m-d H:i:s');
						$myMission->uupdate = $mission->id;
					}
					echo json_encode($myMission->save());
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