<?php
	require_once('config.inc.php');
	require_once('error.inc.php');

	function dbConnect(){
		if(DB_ON_POSTGRESQL){
			$dbId = pg_Connect("host=".DB_HOST." port=".DB_PORT." dbname=".DB_DB." user=".DB_LOGIN." password=".DB_PASS);
		}else{

		}
		return $dbId;
	}

	function dbAsteriskConnect(){
		$dbId = pg_Connect("host=".DB_AST_HOST." port=".DB_AST_PORT." dbname=".DB_AST_DB." user=".DB_AST_LOGIN." password=".DB_AST_PASS);
		return $dbId;
	}

	function dbDisconnect($dbId = false){
		if($dbId === false)
			return false;

		if(DB_ON_POSTGRESQL){
			pgsqlDisconnect($dbId);
		}else{

		}
	}

	function dbExecRequest($sql = false, $dbId = false){ 
		if($sql === false)
			return returnResponse(true,"Missing Parameter");

		if($dbId === false)
			$dbId = dbConnect();
		if($dbId === false)
			return returnResponse(true,"Unable to connect to PostgreSQL database");

		/* Detection du type de requete pour le traitement du retour */
		$requestType = "SELECT";
		if(strpos(strtolower($sql),"select ") !== false && strpos(strtolower($sql),"count(") !== false)
			$requestType = "COUNT";
		if(strpos(strtolower($sql),"select ") !== false && strpos(strtolower($sql),"sum(") !== false)
			$requestType = "SUM";
		if(strpos(strtolower($sql),"insert into ") !== false)
			$requestType = "INSERT";
		if(strpos(strtolower($sql),"delete ") !== false && strpos(strtolower($sql),"where") !== false)
			$requestType = "DELETE";
		if(strpos(strtolower($sql),"update ") !== false && strpos(strtolower($sql),"set ") !== false)
			$requestType = "UPDATE";

		if(DB_ON_POSTGRESQL){
			if(DEBUG_MODE === true)
				error_log($sql);
			$result = pg_query($dbId, $sql);
			if($result === false)
				return returnResponse(true,"Error in SQL request");

			/* Traitement du retour suivant le type de requete */
			switch($requestType){
				case "SELECT":
					return returnResponse(false,pg_fetch_all($result));
					break;
				case "COUNT":
					//return returnResponse(false,pg_fetch_all($result));
					return returnResponse(false,pg_fetch_array($result));
					break;
				case "SUM":
					return returnResponse(false,pg_fetch_array($result));
					break;
				case "INSERT":
					return returnResponse(false,pg_fetch_array($result));

					break;
				case "DELETE":
					return returnResponse(false,pg_affected_rows($result));
					break;
				case "UPDATE":
					return returnResponse(false,pg_affected_rows($result));
					break;

			}

			return returnResponse(true,"Unexpected requestType condition");
		}else{
			// Traitement pour MySQL
		}
	}

	function cleanJson($json = false, $array = false){
		if($json === false){
			return '';
		}
		$cleanedJson = '';
		if($json != ''){			
			$json = stripslashes($json);
			if(substr($json,0,1) == '"'){
				$json = substr($json,1);
				$json = rtrim($json,'"');
			}
			if($array === false)
				$cleanedJson = json_decode($json);
			else
				$cleanedJson = json_decode($json,true);

		}

		if(is_object($cleanedJson)){
			$cpt = 0;
			foreach ($cleanedJson as $key => $value){
    			$cpt++;
			}
			if($cpt == 0)
				$cleanJson->date = date('Y-m-d H:i:s');
		}
		if($cleanedJson == null){
			$vide = Array();
			$vide['date'] = date('Y-m-d H:i:s');
			$cleanedJson = $vide;
		}

		return $cleanedJson;
	}
?>