<?php 
	require_once('config.inc.php');
	require_once('error.inc.php');
	require_once('db.inc.php');
	require_once('cars.class.php');

	class papers {
		var $id; 
		var $carid; 
		var $name; 
		var $dbegin; 
		var $dend; 
		var $dcreate; 
		var $ucreate; 
		var $dupdate; 
		var $uupdate; 
		var $customdata; 


		function __construct(){
			$this->id = false ;
			$this->carid = 0 ;
			$this->name = '' ;
			$this->dbegin = date('Y-m-d H:i:s');
			$this->dend =  date('Y-m-d H:i:s');
			$this->dcreate =  date('Y-m-d H:i:s');
			$this->ucreate =  0;
			$this->dupdate =  date('Y-m-d H:i:s');
			$this->uupdate =  0;
			$this->customdata = new stdClass() ;
		}

		function getAllPapers(){
			$sql = "SELECT papers.* , "
						.cars::getJoinString()." "
						."FROM papers "
						."JOIN cars ON cars.id = papers.carid ";
			return papers::execRequest($sql);
		}

		function getPaperById($id = false){
			if($id === false){
				return returnResponse(true , " Missing parameter id to execute getPaperById");
			}

			$sql = "SELECT papers.* , "
						.cars::getJoinString()." "
						."FROM papers "
						."JOIN cars ON cars.id = papers.carid "
						."WHERE papers.id = " . pg_escape_string($id);
			return papers::execRequest($sql);
		}

		function getPapersByCarId($carId = false){
			if($id === false){
				return returnResponse(true , " Missing parameter id to execute getPapersByCarId");
			}

			$sql = "SELECT papers.* , "
						.cars::getJoinString()." "
						."FROM papers "
						."JOIN cars ON cars.id = papers.carid "
						."WHERE papers.carid = ". pg_escape_string($carId);
			return papers::execRequest($sql);
		}

		function deletePaperById($id = false){
			if($id === false){
				return returnResponse(true,"Missing id parameter to execute deletePaperById");
			}

			$sql = "DELETE FROM papers WHERE id = " . pg_escape_string($id);
			return papers::execRequest($sql);
		}

		function save(){
			if(!isset($this)){
				return returnResponse(true,"Object not instancied. Cannot save it !");
			}

			if($this->id === false){
				$sql = "INSERT INTO papers VALUES (DEFAULT, "
						."".pg_escape_string($this->carid).", "
						."'".pg_escape_string($this->name)."', "
						."'".pg_escape_string($this->dbegin)."', "
						."'".pg_escape_string($this->dend)."', "
						."'".pg_escape_string($this->dcreate)."', "
						."".pg_escape_string($this->ucreate).", "
						."'".pg_escape_string($this->dupdate)."', "
						."".pg_escape_string($this->uupdate).", "
						."'".json_encode($this->customdata)."' "
						.") RETURNING id";

			}else{
				$sql = "UPDATE papers SET "
						."carid=".pg_escape_string($this->carid).", "
						."name='".pg_escape_string($this->name)."', "
						."dbegin='".pg_escape_string($this->dbegin)."', "
						."dend='".pg_escape_string($this->dend)."', "
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
				$paper = new papers();

				$paper->id = trim($row['id']);
				$paper->carid = trim($row['carid']);
				$paper->name = trim($row['name']);
				$paper->dbegin = trim($row['dbegin']);
				$paper->dend = trim($row['dend']);
				$paper->dcreate = trim($row['dcreate']);
				$paper->ucreate = trim($row['ucreate']);
				$paper->dupdate = trim($row['dupdate']);
				$paper->uupdate = trim($row['uupdate']);
				$paper->customdata = json_decode($row['customdata']);

				$depend = cars::readJoinString($row);
				if($depend !== false)
					$paper->dependencies->car = $depend;

				return $paper;

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
				$obj = papers::readRow($result['data'][$i]);
				if($obj !== false && $obj->id !== false)
					$results[] = $obj;
			}
			return returnResponse(false, $results);
		}
	}
?>