<?php
	require_once('../classes/auth.class.php');
	require_once('../classes/papers.class.php');

	$postdata = file_get_contents("php://input");
	$postdata = json_decode($postdata);

	if(isset($_SERVER['HTTP_AUTHORIZATION'])){
		$tmp = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
		$token = $tmp[1];
		$verifToken = auth::vefifySignAuthToken($token);
		if($verifToken['error'] === true){
			echo json_encode(returnResponse(true,"AuthToken Not Valid !"));
			return false;
		}
		if(isset($postdata->action)){
			switch ($postdata->action){
				case 'getAllPapers':
					echo json_encode(papers::getAllPapers());
					return false;
					break;
				case 'deletePaper':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing parameter to complete deletePaper"));
						return false;
					}
					echo json_encode(papers::deletePaperById($postdata->id));
					return false;
					break;
				case 'savePaper':
					if(!isset($postdata->paper)){
						echo json_encode(returnResponse(true,"Missing parameter to complete savePaper"));
						return false;
					}
					$paper = json_decode($postdata->paper);

					$myPaper = new papers();

					foreach ($paper as $key => $value) {
						if(isset($myPaper->$key)){
							$myPaper->$key = $value;
						}
					}
					if(isset($paper->id)){
						$myPaper->dupdate = date('Y-m-d H:i:s');
						$myPaper->uupdate = $paper->id;
					}
					echo json_encode($myPaper->save());
					return false;
					break;			
				default:
					echo json_encode(returnResponse(true,"No Action Provided ! "));
					return false;
					break;	
			}
		}else{
			return false;
		}
	}
?>