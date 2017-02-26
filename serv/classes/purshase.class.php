<?php 
	require_once('config.inc.php');
	require_once('error.inc.php');
	require_once('db.inc.php');
	require_once('cars.class.php');

	class purshase {
		var $id;
		var $carid;
		var $name;
		var $nfacture;
		var $nbl;
		var $price;
		var $dcreate;
		var $ucreate;
		var $dupdate;
		var $uupdate;
		var $customdata;

		function __construct(){
			$this->id = false;
			$this->carid = 0;
			$this->name = '';
			$this->nfacture = '';
			$this->nbl = '';
			$this->price = '';
			$this->dcreate = date('Y-m-d H:i:s');
			$this->ucreate = 0;
			$this->dupdate = date('Y-m-d H:i:s');
			$this->uupdate = 0;
			$this->customdata = new stdClass();
		}

		function getPurshaseById($id = false){
			if($id === false){
				return returnResponse(true , " Missing parameter id to execute getUserById");
			}
			$sql = "SELECT purshase.* , "
						.cars::getJoinString()." "
						."FROM purshase "
						."JOIN cars ON cars.id = purshase.carid "
						."WHERE purshase.id = ".pg_escape_string($id);

			return purshase::execRequest($sql);
		}

		function getPurshaseByCarId($carId = false){
			if($id === false){
				return returnResponse(true , " Missing parameter id to execute getUserById");
			}

			$sql = "SELECT purshase.* , "
						.cars::getJoinString()." "
						."FROM purshase "
						."JOIN cars ON cars.id = purshase.carid "
						."WHERE purshase.carid = ".pg_escape_string($carId);
			return purshase::execRequest($sql);
		}

		function getAll(){
			$sql = "SELECT purshase.* , "
						.cars::getJoinString()." "
						."FROM purshase "
						."JOIN cars ON cars.id = purshase.carid";
			return purshase::execRequest($sql);			
		}

		function deletePurshaseById($id = false){
			if($id === false){
				return returnResponse(true,"Missing id parameter to execute deletePurshaseById");
			}

			$sql = "DELETE FROM purshase WHERE id =" . pg_escape_string($id);
			return purshase::execRequest($sql);
		}

		function save(){
			if(!isset($this)){
				return returnResponse(true,"Object not instancied. Cannot save it !");
			}
			if($this->id === false){
				$sql = "INSERT INTO purshase VALUES (DEFAULT, "
						."".pg_escape_string($this->carid).", "
						."'".pg_escape_string($this->name)."', "
						."'".pg_escape_string($this->nfacture)."', "
						."'".pg_escape_string($this->nbl)."', "
						."'".pg_escape_string($this->price)."', "
						."'".pg_escape_string($this->dcreate)."', "
						."".pg_escape_string($this->ucreate).", "
						."'".pg_escape_string($this->dupdate)."', "
						."".pg_escape_string($this->uupdate).", "
						."'".json_encode($this->customdata)."' "
						.") RETURNING id";
			}else{
				$sql = "UPDATE purshase SET "
						."carid=".pg_escape_string($this->carid).", "
						."name='".pg_escape_string($this->name)."', "
						."nfacture='".pg_escape_string($this->nfacture)."', "
						."nbl='".pg_escape_string($this->nbl)."', "
						."price='".pg_escape_string($this->price)."', "
						."dcreate='".pg_escape_string($this->dcreate)."', "
						."ucreate=".pg_escape_string($this->ucreate).", "
						."dupdate='".pg_escape_string($this->dupdate)."', "
						."uupdate=".pg_escape_string($this->uupdate).", "
						."customdata='".json_encode($this->customdata)."' "
						."WHERE id = ". pg_escape_string($this->id);
			}
			$result = dbExecRequest($sql);
			if($result['error'] === true){
				return returnResponse(true,"Unable to execute ".$sql);
			}

			return returnResponse(false,$result['data']);
		}

		private function readRow($row = false){
			if($row === false){
				return false;
			}

			if(trim($row['id']) !== ''){
				$purshase = new purshase();

				$purshase->id = trim($row['id']);
				$purshase->carid = trim($row['carid']);
				$purshase->name = trim($row['name']);
				$purshase->nfacture = trim($row['nfacture']);
				$purshase->nbl = trim($row['nbl']);
				$purshase->price = trim($row['price']);
				$purshase->dcreate = trim($row['dcreate']);
				$purshase->ucreate = trim($row['ucreate']);
				$purshase->dupdate = trim($row['dupdate']);
				$purshase->uupdate = trim($row['uupdate']);
				$purshase->customdata = json_decode($row['customdata']);

				$depend = cars::readJoinString($row);
				if($depend !== false)
					$purshase->dependencies->car = $depend;

				return $purshase;
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
				$obj = purshase::readRow($result['data'][$i]);
				if($obj !== false && $obj->id !== false)
					$results[] = $obj;
			}
			return returnResponse(false, $results);
		}


	}
?>