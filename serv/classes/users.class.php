<?php 
	require_once('config.inc.php');
	require_once('error.inc.php');
	require_once('db.inc.php');


	class users {
		var $id;
		var $firstname;
		var $lastname;
		var $email;
		var $login;
		var $password;
		var $type;
		var $droits;
		var $dcreate;
		var $ucreate;
		var $dupdate;
		var $uupdate;
		var $customdata;


		function __construct(){
			$this->id = false;
			$this->firstname = '';
			$this->lastname = '';
			$this->email = '';
			$this->login = '';
			$this->password = '';
			$this->type = '';
			$this->droits = new stdClass();
			$this->dcreate = date('Y-m-d H:i:s');
			$this->ucreate = 0;
			$this->dupdate = date('Y-m-d H:i:s');;
			$this->uupdate = 0;
			$this->customdata = new stdClass();
		}

		function getUserById($id = false){
			if($id === false){
				return returnResponse(true , " Missing parameter id to execute getUserById");
			}

			$sql = "SELECT * from users where id = " . pg_escape_string($id);
			return users::execRequest($sql);
		}

		function getAll(){
			$sql = "SELECT * from users";
			return users::execRequest($sql);
		}

		function deleteUserById($id){
			if($id === false){
				return returnResponse(true , " Missing parameter id to execute getUserById");
			}

			$sql = "DELETE FROM users WHERE id = ". pg_escape_string($id);
			return users::execRequest($sql);
		}

		function logIn( $login = false , $password = false){
			if($login === false || $password === false){
				return returnResponse(true,"Missing parameter to execute logIn");
			}

			$sql = "SELECT * from users where login = '".pg_escape_string($login) ."' and password = '".pg_escape_string($password)."'";

			return users::execRequest($sql);
		}

		function save(){
			if(!isset($this)){
				return returnResponse(true,"Object not instancied. Cannot save it !");
			}

			if($this->id === false){
				$sql = "INSERT INTO users VALUES (DEFAULT, "
						."'".pg_escape_string($this->firstname)."', "
						."'".pg_escape_string($this->lastname)."', "
						."'".pg_escape_string($this->email)."', "
						."'".pg_escape_string($this->login)."', "
						."'".pg_escape_string($this->password)."', "
						."'".pg_escape_string($this->type)."', "
						."'".json_encode($this->droits)."', "
						."'".pg_escape_string($this->dcreate)."', "
						."".pg_escape_string($this->ucreate).", "
						."'".pg_escape_string($this->dupdate)."', "
						."".pg_escape_string($this->uupdate).", "
						."'".json_encode($this->customdata)."' "
						.") RETURNING id;";
			}else{
				$sql = "UPDATE users SET "
						."firstname='".pg_escape_string($this->firstname)."', "
						."lastname='".pg_escape_string($this->lastname)."', "
						."email='".pg_escape_string($this->email)."', "
						."login='".pg_escape_string($this->login)."', "
						."password='".pg_escape_string($this->password)."', "
						."type='".pg_escape_string($this->type)."', "
						."droits='".json_encode($this->droits)."', "
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
				$user = new users();

				$user->id = trim($row['id']);
				$user->firstname = trim($row['firstname']);
				$user->lastname = trim($row['lastname']);
				$user->email = trim($row['email']);
				$user->login = trim($row['login']);
				$user->password = trim($row['password']);
				$user->type = trim($row['type']);
				$user->droits = json_decode($row['droits']);
				$user->dcreate = trim($row['dcreate']);
				$user->ucreate = trim($row['ucreate']);
				$user->dupdate = trim($row['dupdate']);
				$user->uupdate = trim($row['uupdate']);
				$user->customdata = json_decode($row['customdata']);

				return $user;
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
				$obj = users::readRow($result['data'][$i]);
				if($obj !== false && $obj->id !== false)
					$results[] = $obj;
			}
			return returnResponse(false, $results);
		}


	}


?>