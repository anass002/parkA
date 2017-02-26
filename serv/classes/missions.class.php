<?php 
	require_once('config.inc.php');
	require_once('error.inc.php');
	require_once('db.inc.php');
	require_once('cars.class.php');

	class missions {
		var $id;
		var $carid;
		var $destination;
		var $departure;
		var $ddeparture;
		var $rate;
		var $dcreate;
		var $ucreate;
		var $dupdate;
		var $uupdate;
		var $customdata;

		function __construct(){
			$this->id = false;
			$this->carid = 0;
			$this->destination = ''; 
			$this->departure = '';
			$this->ddeparture = date('Y-m-d H:i:s');
			$this->rate = '';
			$this->dcreate = date('Y-m-d H:i:s');
			$this->ucreate = 0;
			$this->dupdate = date('Y-m-d H:i:s');
			$this->uupdate = 0;
			$this->customdata = new stdClass(); 
		}

		function getMissionById($id = false){
			if($id === false){
				return returnResponse(true , " Missing parameter id to execute getUserById");
			}

			$sql ="SELECT missions.* , "
					.cars::getJoinString()." "
					."FROM missions "
					."JOIN cars ON cars.id = missions.carid "
					."WHERE missions.id = " . pg_escape_string($id);
			return missions::execRequest($sql);
		}

		function getMissionsByCarId($carId = false){
			if($carId === false){
				return returnResponse(true , " Missing parameter id to execute getUserById");
			}

			$sql = "SELECT missions.* , "
						.cars::getJoinString()." "
						."FROM missions "
						."JOIN cars ON cars.id = missions.carid "
						."WHERE missions.carid = ".pg_escape_string($carId);

			return missions::execRequest($sql);
		}

		function getAll(){
			$sql = "SELECT missions.* , "
					.cars::getJoinString()." "
					."FROM missions "
					."JOIN cars ON cars.id = missions.carid";
							
			return missions::execRequest($sql);		
		}

		function save(){
			if(!isset($this)){
				return returnResponse(true,"Object not instancied. Cannot save it !");
			}

			if($this->id === false){
				$sql = "INSERT INTO missions VALUES (DEFAULT, "
					."".pg_escape_string($this->carid).", "
					."'".pg_escape_string($this->destination)."', "
					."'".pg_escape_string($this->departure)."', "
					."'".pg_escape_string($this->ddeparture)."', "
					."'".pg_escape_string($this->rate)."', "
					."'".pg_escape_string($this->dcreate)."', "
					."".pg_escape_string($this->ucreate).", "
					."'".pg_escape_string($this->dupdate)."', "
					."".pg_escape_string($this->uupdate).", "
					."'".json_encode($this->customdata)."' "
					.") RETURNING id";
			}else{
				$sql = "UPDATE missions SET "
						."carid=".pg_escape_string($this->carid).", "
						."destination='".pg_escape_string($this->destination)."', "
						."departure='".pg_escape_string($this->departure)."', "
						."ddeparture='".pg_escape_string($this->ddeparture)."', "
						."rate='".pg_escape_string($this->rate)."', "
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

			return returnResponse(false,$result['data']);
		}

		function deleteMissionById($id = false){
			if($id === false){
				return returnResponse(true,"Missing id parameter to execute deleteMissionById");
			}

			$sql = "DELETE FROM missions WHERE id = ".pg_escape_string($id);
			return missions::execRequest($sql);
		}

		private function readRow($row = false){
			if($row === false){
				return false;
			}

			if(trim($row['id']) !== ''){
				$mission = new missions();

				$mission->id = trim($row['id']);
				$mission->carid = trim($row['carid']);
				$mission->destination = trim($row['destination']);
				$mission->departure = trim($row['departure']);
				$mission->ddeparture = trim($row['ddeparture']);
				$mission->rate = trim($row['rate']);
				$mission->dcreate = trim($row['dcreate']);
				$mission->ucreate = trim($row['ucreate']);
				$mission->dupdate = trim($row['dupdate']);
				$mission->uupdate = trim($row['uupdate']);
				$mission->customdata = json_decode($row['customdata']);

				$depend = cars::readJoinString($row);
				if($depend !== false)
					$mission->dependencies->car = $depend;

				return $mission;


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
				$obj = missions::readRow($result['data'][$i]);
				if($obj !== false && $obj->id !== false)
					$results[] = $obj;
			}
			return returnResponse(false, $results);
		}

	}	

?>