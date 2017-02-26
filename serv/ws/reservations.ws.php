<?php 
	require_once('../classes/auth.class.php');
	require_once('../classes/reservations.class.php');


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
				case 'getAllReservations':
					echo json_encode(reservations::getAll());
					return false;
					break;
				case 'deleteReservation':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing parameter to complete deleteReservation"));
						return false;
					}
					echo json_encode(reservations::deleteReservationById($postdata->id));
					return false;
					break;
				case 'saveReservation':
					if(!isset($postdata->reservation)){
						echo json_encode(returnResponse(true,"Missing parameter to complete saveReservation"));
						return false;
					}
					$reservation = json_decode($postdata->reservation);

					$myReservation = new reservations();

					foreach ($reservation as $key => $value) {
						if(isset($myReservation->$key)){
							$myReservation->$key = $value;
						}
					}
					if(isset($reservation->id)){
						$myReservation->dupdate = date('Y-m-d H:i:s');
						$myReservation->uupdate = $reservation->id;
					}
					echo json_encode($myReservation->save());
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