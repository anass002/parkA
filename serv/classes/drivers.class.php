<?php 
	require_once('config.inc.php');
	require_once('error.inc.php');
	require_once('db.inc.php');
	require_once('cars.class.php');

	class drivers {
		var $id;
		var $carid;
		var $firstname;
		var $lastname;
		var $email;
		var $cin;
		var $driverlicense;
		var $tel;
		var $files;
		var $dcreate;
		var $ucreate;
		var $dupdate;
		var $uupdate;
		var $customdata;


		function __construct(){
			$this->id = false ;
			$this->carid = 0 ;
			$this->firstname = '';
			$this->lastname = '' ;
			$this->email = '' ;
			$this->cin = '';
			$this->driverlicense = '';
			$this->tel = '';
			$this->files = new stdClass();
			$this->dcreate = date('Y-m-d H:i:s');
			$this->ucreate = 0 ;
			$this->dupdate = date('Y-m-d H:i:s');
			$this->uupdate =  0;
			$this->customdata =  new stdClass();
		}

		function getDriverById($id = false){
			if($id === false){
				return returnResponse(true , " Missing parameter id to execute getCatById");
			}

			$sql = "SELECT * from drivers where id = " . pg_escape_string($id);
			$sql = "SELECT drivers.* , "
						.cars::getJoinString()." "
						."FROM drivers "
						."JOIN cars ON cars.id = drivers.carid "
						."WHERE drivers.id = ".pg_escape_string($id);
			return drivers::execRequest($sql);
		}

		function getDriverByCarId($carId = false){
			if($carId === false){
				return returnResponse(true , " Missing parameter id to execute getCatById");
			}

			$sql = "SELECT drivers.* , "
					.cars::getJoinString()." "
					."FROM drivers "
					."JOIN cars ON cars.id = drivers.carid "
					."WHERE drivers.carid = ".pg_escape_string($carId);
			return drivers::execRequest($sql);
		}

		function getAll(){
			$sql = "SELECT drivers.* , "
						.cars::getJoinString(). " "
						."FROM drivers "
						."JOIN cars ON cars.id = drivers.carid ";

			return drivers::execRequest($sql);
		}

		function deleteDriverById($id = false){
			if($id === false){
				return returnResponse(true,"Missing id parameter to execute deleteDriverById");
			}
			$sql = "DELETE FROM drivers WHERE id = ".pg_escape_string($id);
			return drivers::execRequest($sql);
		}

		function save(){
			if(!isset($this)){
				return returnResponse(true,"Object not instancied. Cannot save it !");
			}

			if($this->id === false){
				$sql = "INSERT INTO drivers VALUES (DEFAULT, "
						."".pg_escape_string($this->carid).", "
						."'".pg_escape_string($this->firstname)."', "
						."'".pg_escape_string($this->lastname)."', "
						."'".pg_escape_string($this->email)."', "
						."'".pg_escape_string($this->cin)."', "
						."'".pg_escape_string($this->driverlicense)."', "
						."'".pg_escape_string($this->tel)."', "
						."'".json_encode($this->files)."', "
						."'".pg_escape_string($this->dcreate)."', "
						."".pg_escape_string($this->ucreate).", "
						."'".pg_escape_string($this->dupdate)."', "
						."".pg_escape_string($this->uupdate).", "
						."'".json_encode($this->customdata)."' "
						.") RETURNING id";
			}else{
				$sql = "UPDATE drivers SET "
						."carid=".pg_escape_string($this->carid).", "
						."firstname='".pg_escape_string($this->firstname)."', "
						."lastname='".pg_escape_string($this->lastname)."', "
						."email='".pg_escape_string($this->email)."', "
						."cin='".pg_escape_string($this->cin)."', "
						."driverlicense='".pg_escape_string($this->driverlicense)."', "
						."tel='".pg_escape_string($this->tel)."', "
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

			return returnResponse(false,$result['data']);
		}

		private function readRow($row = false){
			if($row === false){
				return false;
			}

			if(trim($row['id']) !== ''){
				$driver = new drivers();

				$driver->id = trim($row['id']);
				$driver->carid = trim($row['carid']);
				$driver->firstname = trim($row['firstname']);
				$driver->lastname = trim($row['lastname']);
				$driver->email = trim($row['email']);
				$driver->cin = trim($row['cin']);
				$driver->driverlicense = trim($row['driverlicense']);
				$driver->tel = trim($row['tel']);
				$driver->files = json_decode($row['files']);
				$driver->dcreate = trim($row['dcreate']);
				$driver->ucreate = trim($row['ucreate']);
				$driver->dupdate = trim($row['dupdate']);
				$driver->uupdate = trim($row['uupdate']);
				$driver->customdata = json_decode($row['customdata']);

				$depend = cars::readJoinString($row);
				if($depend !== false)
					$driver->dependencies->car = $depend;

				return $driver;

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
				$obj = drivers::readRow($result['data'][$i]);
				if($obj !== false && $obj->id !== false)
					$results[] = $obj;
			}
			return returnResponse(false, $results);
		}

		function readJoinString($row = false){
			if($row === false){
				return false;
			}
			if(isset($row['drivers_id'])){
				$obj = new drivers();
				$vars = get_class_vars(get_class($obj));
				foreach ($vars as $key => $value) {
					if(isset($row['drivers_'.strtolower($key)])){
						$obj->$key = trim($row['drivers_'.strtolower($key)]);
					}
				}
				return $obj;
			}
			return false;
		}

		function getJoinString(){
			$classVars = get_class_vars('drivers');
			$joinString = '';
			foreach($classVars as $name => $value) {
			    $joinString .= 'COALESCE(drivers.'.$name.',null) as drivers_'.$name.", ";
			}
			$joinString = rtrim($joinString, ", ");
			return $joinString;
		}
	}
?>