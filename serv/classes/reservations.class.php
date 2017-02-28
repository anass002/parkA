<?php 
	require_once('config.inc.php');
	require_once('error.inc.php');
	require_once('db.inc.php');
	require_once('cars.class.php');

	class reservations {
		var $id;
		var $carid;
		var $dreservation;
		var $rate;
		var $files;
		var $dcreate;
		var $ucreate;
		var $dupdate;
		var $uupdate;
		var $customdata;

		function __construct(){
			$this->id = false;
			$this->carid = 0;
			$this->dreservation = date('Y-m-d H:i:s');
			$this->rate = '';
			$this->files = new stdClass();
			$this->dcreate = date('Y-m-d H:i:s');
			$this->ucreate = 0;
			$this->dupdate = date('Y-m-d H:i:s');
			$this->uupdate = 0 ;
			$this->customdata = new stdClass();
		}

		function getReservationById($id = false){
			if($id === false){
				return returnResponse(true , " Missing parameter id to execute getUserById");
			}

			$sql = "SELECT reservations.* , "
					.cars::getJoinString()." "
					."FROM reservations "
					."JOIN cars ON cars.id = reservations.carid "
					."WHERE reservations.id = ".pg_escape_string($id);
			return reservations::execRequest($sql);
		}

		function getReservationByCarId($carId = false){
			if($carId === false){
				return returnResponse(true , " Missing parameter id to execute getUserById");
			}

			$sql = "SELECT reservations.* , "
					.cars::getJoinString()." "
					."FROM reservations "
					."JOIN cars ON cars.id = reservations.carid "
					."WHERE reservations.carid = ".pg_escape_string($carId);
			return reservations::execRequest($sql);
		}

		function getAll(){
			$sql = "SELECT reservations.* , "
					.cars::getJoinString()." "
					."FROM reservations "
					."JOIN cars ON cars.id = reservations.carid ";
			return reservations::execRequest($sql);		
		}	

		function save(){
			if(!isset($this)){
				return returnResponse(true,"Object not instancied. Cannot save it !");
			}

			if($this->id === false){
				$sql = "INSERT INTO reservations VALUES (DEFAULT, "
					."".pg_escape_string($this->carid).", "
					."'".pg_escape_string($this->dreservation)."', "
					."'".pg_escape_string($this->rate)."', "
					."'".json_encode($this->files)."', "
					."'".pg_escape_string($this->dcreate)."', "
					."".pg_escape_string($this->ucreate).", "
					."'".pg_escape_string($this->dupdate)."', "
					."".pg_escape_string($this->uupdate).", "
					."'".json_encode($this->customdata)."' "
					.") RETURNING id";
			}else{
				$sql = "UPDATE reservations SET "
					."carid=".pg_escape_string($this->carid).", "
					."dreservation='".pg_escape_string($this->dreservation)."', "
					."rate='".pg_escape_string($this->rate)."', "
					."files='".json_encode($this->files)."', "
					."dcreate='".pg_escape_string($this->dcreate)."', "
					."ucreate=".pg_escape_string($this->ucreate).", "
					."dupdate='".pg_escape_string($this->dupdate)."', "
					."uupdate=".pg_escape_string($this->uupdate).", "
					."customdata='".json_encode($this->customdata)."' "
					."WHERE id = ".pg_escape_string($this->id);
			}
			$result = dbExecRequest($sql);
			if($result['error'] === true){
				return returnResponse(true,"Unable to execute ".$sql);
			}

			if($this->id === false){
				$notif = new notifications();
				$notif->carid = $this->carid;
				$notif->dsend = '2017-03-02 00:00:00';
				$notif->msg = "Une Réservation a ete enregister pour le vehicule " .$this.carid ." Avec le prix : " .$this->rate ." (MAD)";
				$notif->htmlmsg = 	'Une Réservation a ete enregister pour le vehicule ' .$this.carid .' Avec le prix : ' .$this->rate .' (MAD)';
				$notif->save();
			}

			return returnResponse(false,$result['data']);
		}

		function deleteReservationById($id = false){
			if($id === false){
				return returnResponse(true,"Missing id parameter to execute deleteReservationById");
			}

			$sql = "DELETE FROM reservations WHERE id = ".pg_escape_string($id);
			return reservations::execRequest($sql);
		}

		private function readRow($row = false){
			if($row === false){
				return false;
			}

			if(trim($row['id']) !== ''){
				$reservation = new reservations();

				$reservation->id = trim($row['id']);
				$reservation->carid = trim($row['carid']);
				$reservation->dreservation = trim($row['dreservation']);
				$reservation->rate = trim($row['rate']);
				$reservation->files = json_decode($row['files']);
				$reservation->dcreate = trim($row['dcreate']);
				$reservation->ucreate = trim($row['ucreate']);
				$reservation->dupdate = trim($row['dupdate']);
				$reservation->uupdate = trim($row['uupdate']);
				$reservation->customdata = json_decode($row['customdata']);

				$depend = cars::readJoinString($row);
				if($depend !== false)
					$reservation->dependencies->car = $depend;

				return $reservation;


			}
			return false;
		}

		private function execRequest($sql = false){
			if($sql === false){
				return returnResponse(true,"Missing sql parameter");
			}
			
			$result = dbExecRequest($sql);
			if($result['error'] === true){
				return returnResponse(true,$result['data']);
			}
			$cpt = count($result['data']);
			$results = Array();
			for($i=0 ; $i<$cpt ; $i++){
				$obj = reservations::readRow($result['data'][$i]);
				if($obj !== false && $obj->id !== false)
					$results[] = $obj;
			}
			return returnResponse(false, $results);
		}

		function readJoinString($row = false){
			if($row === false){
				return false;
			}
			if(isset($row['reservations_id'])){
				$obj = new reservations();
				$vars = get_class_vars(get_class($obj));
				foreach ($vars as $key => $value) {
					if(isset($row['reservations'.strtolower($key)])){
						$obj->$key = trim($row['reservations'.strtolower($key)]);
					}
				}
				return $obj;
			}
			return false;
		}

		function getJoinString(){
			$classVars = get_class_vars('reservations');
			$joinString = '';
			foreach($classVars as $name => $value) {
			    $joinString .= 'COALESCE(reservations.'.$name.',null) as reservations'.$name.", ";
			}
			$joinString = rtrim($joinString, ", ");
			return $joinString;
		}

	}

?>