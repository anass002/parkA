<?php 
	require_once('../classes/auth.class.php');
	require_once('../classes/categories.class.php');

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
				case 'getAllCategories':
					echo json_encode(categories::getAll());
					return false;
					break;
				case 'deleteCategorie':
					echo json_encode(categories::deleteCatById($postdata->id));
					return false;
					break;
				case 'saveCategorie':
					if(!isset($postdata->categorie)){
						echo json_encode(returnResponse(true,"Missing paramete to complete saveCategorie"));
						return false;
					}

					$categorie = json_decode($postdata->categorie);

					$myCategorie = new categories();
					foreach ($categorie as $key => $value) {
						if(isset($myCategorie->$key)){
							$myCategorie->$key = $value;
						}
					}

					if(isset($categorie->id)){
						$myCategorie->dupdate = date('Y-m-d H:i:s');
						$myCategorie->uupdate = $myCategorie->id;
					}
					echo json_encode($myCategorie->save());
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