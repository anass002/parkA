<?php
	require_once('../classes/auth.class.php');
	require_once('../classes/drivers.class.php');

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
				case 'getAllDrivers':
					echo json_encode(drivers::getAll());
					return false;
					break;
				case 'deleteDriver':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing parameter to complete deleteCars"));
						return false;
					}
					echo json_encode(drivers::deleteDriverById($postdata->id));
					return false;
					break;
				case 'saveDriver':
					if(!isset($postdata->driver)){
						echo json_encode(returnResponse(true,"Missing parameter to complete saveCar"));
						return false;
					}
					$driver = json_decode($postdata->driver);

					$myDriver = new drivers();

					foreach ($driver as $key => $value) {
						if(isset($myDriver->$key)){
							$myDriver->$key = $value;
						}
					}
					if(isset($driver->id)){
						$myDriver->dupdate = date('Y-m-d H:i:s');
						$myDriver->uupdate = $driver->id;
					}
					echo json_encode($myDriver->save());
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