<?php 
	require_once('config.inc.php');
	require_once('error.inc.php');
	require_once('db.inc.php');

	class categories {
		var $id;
		var $name;
		var $dcreate;
		var $ucreate;
		var $dupdate;
		var $uupdate;
		var $customdata;

		function __construct(){
			$this->id = false ;
			$this->name = '' ;
			$this->dcreate = date('Y-m-d H:i:s') ;
			$this->ucreate =  0;
			$this->dupdate = date ('Y-m-d H:i:s');
			$this->uupdate = 0;
			$this->customdata = new stdClass();
		}

		function getCatById($id = false){
			if($id === false){
				return returnResponse(true , " Missing parameter id to execute getCatById");
			}

			$sql = "SELECT * from categories where id = " . pg_escape_string($id);
			return categories::execRequest($sql);
		}

		function getAll(){
			$sql = "SELECT * FROM categories";
			return categories::execRequest($sql);
		}

		function deleteCatById($id = false){
			if($id === false){
				return returnResponse(true,"Missing id parameter to execute deleteCatById");
			}

			$sql = "DELETE FROM categories WHERE id = ".pg_escape_string($id);
			return categories::execRequest($sql);
		}

		function save(){
			if(!isset($this)){
				return returnResponse(true,"Object not instancied. Cannot save it !");
			}

			if($this->id === false){
				$sql = "INSERT INTO categories VALUES (DEFAULT, "
							."'".pg_escape_string($this->name)."', "
							."'".pg_escape_string($this->dcreate)."', "
							."".pg_escape_string($this->ucreate).", "
							."'".pg_escape_string($this->dupdate)."', "
							."".pg_escape_string($this->uupdate).", "
							."'".json_encode($this->customdata)."' "
							.") RETURNING id";
			}else{
				$sql = "UPDATE categories SET "
					."name='".pg_escape_string($this->name)."', "
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
				$categorie = new categories();

				$categorie->id = trim($row['id']);
				$categorie->name = trim($row['name']);
				$categorie->dcreate = trim($row['dcreate']);
				$categorie->ucreate = trim($row['ucreate']);
				$categorie->dupdate = trim($row['dupdate']);
				$categorie->uupdate = trim($row['uupdate']);
				$categorie->customdata = json_decode($row['customdata']);

				return $categorie;

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
				$obj = categories::readRow($result['data'][$i]);
				if($obj !== false && $obj->id !== false)
					$results[] = $obj;
			}
			return returnResponse(false, $results);
		}

		function readJoinString($row = false){
			if($row === false){
				return false;
			}
			if(isset($row['categories_id'])){
				$obj = new categories();
				$vars = get_class_vars(get_class($obj));
				foreach ($vars as $key => $value) {
					if(isset($row['categories_'.strtolower($key)])){
						$obj->$key = $row['categories_'.strtolower($key)];
					}
				}
				return $obj;
			}
			return false;
		}

		function getJoinString(){
			$classVars = get_class_vars('categories');
			$joinString = '';
			foreach($classVars as $name => $value) {
			    $joinString .= 'COALESCE(categories.'.$name.',null) as categories_'.$name.", ";
			}
			$joinString = rtrim($joinString, ", ");
			return $joinString;
		} 
	}
?>