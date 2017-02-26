<?php 
	require_once('../classes/auth.class.php');
	require_once('../classes/cars.class.php');


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
				case 'getAllCars':
					echo json_encode(cars::getAll());
					return false;
					break;
				case 'deleteCars':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing parameter to complete deleteCars"));
						return false;
					}
					echo json_encode(cars::deleteCarById($postdata->id));
					return false;
					break;
				case 'saveCar':
					if(!isset($postdata->car)){
						echo json_encode(returnResponse(true,"Missing parameter to complete saveCar"));
						return false;
					}
					$car = json_decode($postdata->car);

					$myCar = new cars();

					foreach ($car as $key => $value) {
						if(isset($myCar->$key)){
							$myCar->$key = $value;
						}
					}
					if(isset($car->id)){
						$myCar->dupdate = date('Y-m-d H:i:s');
						$myCar->uupdate = $car->id;
					}
					echo json_encode($myCar->save());
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