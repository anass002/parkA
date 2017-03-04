<?php 
	require_once('config.inc.php');
	require_once('error.inc.php');
	require_once('db.inc.php');

	class notifications {
		var $id;
		var $carid;
		var $status;
		var $type;
		var $dsend;
		var $msg;
		var $htmlmsg;
		var $dcreate;
		var $ucreate;
		var $dupdate;
		var $uupdate;
		var $customdata;

		function __construct(){
			$this->id = false;
			$this->carid = 0;
			$this->status = '';
			$this->type = '';
			$this->dsend = date('Y-m-d H:i:s');
			$this->msg = '';
			$this->htmlmsg = '';
			$this->dcreate = date('Y-m-d H:i:s');
			$this->ucreate = 0;
			$this->dupdate = date('Y-m-d H:i:s');
			$this->uupdate = 0;
			$this->customdata = new stdClass();
		}


		function getAllNotifications(){
			$sql = "SELECT * FROM notifications";
			return notifications::execRequest($sql);
		}


		function save(){
			if(!isset($this)){
				return returnResponse(true,"Object not instancied. Cannot save it !");
			}

			if($this->id === false){
				$sql = "INSERT INTO notifications VALUES (DEFAULT, "
							."".pg_escape_string($this->carid).", "
							."'".pg_escape_string($this->status)."', "
							."'".pg_escape_string($this->type)."', "
							."'".pg_escape_string($this->dsend)."', "
							."'".pg_escape_string($this->msg)."', "
							."'".pg_escape_string($this->htmlmsg)."', "
							."'".pg_escape_string($this->dcreate)."', "
							."".pg_escape_string($this->ucreate).", "
							."'".pg_escape_string($this->dupdate)."', "
							."".pg_escape_string($this->uupdate).", "
							."'".json_encode($this->customdata)."' "
							.") RETURNING id";
			}else{
				$sql = "UPDATE notifications SET "
							."carid=".pg_escape_string($this->carid).", "
							."status='".pg_escape_string($this->status)."', "
							."type='".pg_escape_string($this->type)."', "
							."dsend='".pg_escape_string($this->dsend)."', "
							."msg='".pg_escape_string($this->msg)."', "
							."htmlmsg='".pg_escape_string($this->htmlmsg)."', "
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
				$notif = new notifications();

				$notif->id = trim($row['id']);
				$notif->carid = trim($row['carid']);
				$notif->status = trim($row['status']);
				$notif->type = trim($row['type']);
				$notif->dsend = trim($row['dsend']);
				$notif->msg = trim($row['msg']);
				$notif->htmlmsg = trim($row['htmlmsg']);
				$notif->dcreate = trim($row['dcreate']);
				$notif->ucreate = trim($row['ucreate']);
				$notif->dupdate = trim($row['dupdate']);
				$notif->uupdate = trim($row['uupdate']);
				$notif->customdata = json_decode($row['customdata']);

				return $notif;

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
				$obj = notifications::readRow($result['data'][$i]);
				if($obj !== false && $obj->id !== false)
					$results[] = $obj;
			}
			return returnResponse(false, $results);
		}
	} 

?>