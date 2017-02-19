<?php 
	require_once('config.inc.php');
	require_once('error.inc.php');
	require_once('db.inc.php');
	require_once('categories.class.php');

	class cars {
		var $id;
		var $catid;
		var $name;
		var $brand;
		var $drelease;
		var $registrationnumber;
		var $km;
		var $carcode;
		var $greycard;
		var $dcreate;
		var $ucreate;
		var $dupdate;
		var $uupdate;
		var $customdata;

		function __construct(){
			$this->id = false ;
			$this->catid = 0 ;
			$this->name = '' ;
			$this->brand = '' ;
			$this->drelease = date('Y-m-d H:i:s') ;
			$this->registrationnumber = '';
			$this->km = 0;
			$this->carcode = 0;
			$this->greycard = '';
			$this->dcreate = date('Y-m-d H:i:s');
			$this->ucreate = 0 ;
			$this->dupdate = date('Y-m-d H:i:s') ;
			$this->uupdate = 0 ;
			$this->customdata =  new stdClass();
		}


		function getCarById($id = false){
			if($id === false){
				return returnResponse(true , " Missing parameter id to execute getCatById");
			}
			$sql = "SELECT cars.* , "
						.categories::getJoinString()." "
						."FROM cars "
						."JOIN categories ON categories.id = cars.catid "
						."WHERE cars.id = " . pg_escape_string($id);
			return cars::execRequest($sql);
		}

		function getCarByCatId($catId = false){
			if($catId === false){
				return returnResponse(true , " Missing parameter id to execute getCatById");
			}

			$sql = "SELECT * from cars where catid = " . pg_escape_string($catId);

			$sql = "SELECT cars.* , "
						.categories::getJoinString(). " "
						."FROM cars "
						."JOIN categories ON categories.id = cars.catid "
						."WHERE cars.catid = ".pg_escape_string($catId);
			return cars::execRequest($sql);
		}

		function getAll(){
			$sql = "SELECT cars.*, "
						.categories::getJoinString()." "
						."FROM cars "
						."JOIN categories ON categories.id = cars.catid ";
			return cars::execRequest($sql);			
		}

		function save(){
			if(!isset($this)){
				return returnResponse(true,"Object not instancied. Cannot save it !");
			}

			if($this->id === false){
				$sql = "INSERT INTO cars VALUES (DEFAULT, "
						."".pg_escape_string($this->catid).", "
						."'".pg_escape_string($this->name)."', "
						."'".pg_escape_string($this->brand)."', "
						."'".pg_escape_string($this->drelease)."', "
						."'".pg_escape_string($this->registrationnumber)."', "
						."".pg_escape_string($this->km).", "
						."".pg_escape_string($this->carcode).", "
						."'".pg_escape_string($this->greycard)."', "
						."'".pg_escape_string($this->dcreate)."', "
						."".pg_escape_string($this->ucreate).", "
						."'".pg_escape_string($this->dupdate)."', "
						."".pg_escape_string($this->uupdate).", "
						."'".pg_escape_string(json_encode($this->customdata))."' "
						.") RETURNING id";
			}else{
				$sql = "UPDATE cars SET "
					."catid=".pg_escape_string($this->catid).", "
					."name='".pg_escape_string($this->name)."', "
					."brand='".pg_escape_string($this->brand)."', "
					."drelease='".pg_escape_string($this->drelease)."', "
					."registrationnumber='".pg_escape_string($this->registrationnumber)."', "
					."km=".pg_escape_string($this->km).", "
					."carcode=".pg_escape_string($this->carcode).", "
					."greycard='".pg_escape_string($this->greycard)."', "
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

		private function readRow($row = false){
			if($row === false){
				return false;
			}

			if(trim($row['id']) !== ''){
				$car = new cars();

				$car->id = trim($row['id']);
				$car->catid = trim($row['catid']);
				$car->name = trim($row['name']);
				$car->brand = trim($row['brand']);
				$car->drelease = trim($row['drelease']);
				$car->registrationnumber = trim($row['registrationnumber']);
				$car->km = trim($row['km']);
				$car->carcode = trim($row['carcode']);
				$car->greycard = trim($row['greycard']);
				$car->dcreate = trim($row['dcreate']);
				$car->ucreate = trim($row['ucreate']);
				$car->dupdate = trim($row['dupdate']);
				$car->uupdate = trim($row['uupdate']);
				$car->customdata = json_decode($row['customdata']);

				$depend = categories::readJoinString($row);
				if($depend !== false)
					$car->dependencies->categorie = $depend;

				return $car;
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
				$obj = cars::readRow($result['data'][$i]);
				if($obj !== false && $obj->id !== false)
					$results[] = $obj;
			}
			return returnResponse(false, $results);
		}

		function readJoinString($row = false){
			if($row === false){
				return false;
			}
			if(isset($row['cars_id'])){
				$obj = new cars();
				$vars = get_class_vars(get_class($obj));
				foreach ($vars as $key => $value) {
					if(isset($row['cars_'.strtolower($key)])){
						$obj->$key = trim($row['cars_'.strtolower($key)]);
					}
				}
				return $obj;
			}
			return false;
		}

		function getJoinString(){
			$classVars = get_class_vars('cars');
			$joinString = '';
			foreach($classVars as $name => $value) {
			    $joinString .= 'COALESCE(cars.'.$name.',null) as cars_'.$name.", ";
			}
			$joinString = rtrim($joinString, ", ");
			return $joinString;
		}
	}	

?>