<?php 
	require_once('../classes/auth.class.php');
	require_once('../classes/missions.class.php');
	require_once('../vendor/phpoffice/phpexcel/Classes/PHPExcel.php');
	require_once('../vendor/autoload.php');


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
				case 'getAllMissions':
					echo json_encode(missions::getAll());
					return false;
					break;
				case 'getNextMissions':
					echo json_encode(missions::getNextMissions());
					return false;
					break;	
				case 'deleteMission':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing parameter to complete deleteMission"));
						return false;
					}
					echo json_encode(missions::deleteMissionById($postdata->id));
					return false;
					break;
				case 'saveMission':
					if(!isset($postdata->mission)){
						echo json_encode(returnResponse(true,"Missing parameter to complete saveMission"));
						return false;
					}
					$mission = json_decode($postdata->mission);

					$myMission = new missions();

					foreach ($mission as $key => $value) {
						if(isset($myMission->$key)){
							$myMission->$key = $value;
						}
					}
					if(isset($mission->id)){
						$myMission->dupdate = date('Y-m-d H:i:s');
						$myMission->uupdate = $mission->id;
					}
					echo json_encode($myMission->save());
					return false;
					break;
				case 'exportExcel':
					echo json_encode(returnResponse(false,exportExcel()));
					return false;
					break;
				case 'exportPDF':
					echo json_encode(returnResponse(false,exportPDF()));
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

	function exportExcel(){
		$objPHPExcel = new PHPExcel();
		$myActiveSheet = $objPHPExcel->getProperties()->setCreator("Hard Transport Personnel")
							 ->setLastModifiedBy("Hard Transport Personnel")
							 ->setTitle("Données Missions")
							 ->setSubject("Données Missions")
							 ->setDescription("Données Missions")
							 ->setKeywords("Missions")
							 ->setCategory("Missions");


		$myActiveSheet = $objPHPExcel->setActiveSheetIndex(0);
		$myActiveSheet->setTitle(_("Vehicules"));
		$myActiveSheet->setCellValueByColumnAndRow(0, 4, _('Départ'));
		$myActiveSheet->setCellValueByColumnAndRow(1, 4, _('Arrivée'));
		$myActiveSheet->setCellValueByColumnAndRow(2, 4, _('Date Départ'));
		$myActiveSheet->setCellValueByColumnAndRow(3, 4, _('Prix'));
		$myActiveSheet->setCellValueByColumnAndRow(4, 4, _('Voiture'));


		$myMissions = missions::getAll();

		$myMissions = $myMissions['data'];

		for ($i=0; $i < count($myMissions) ; $i++) { 
			$myActiveSheet->setCellValueExplicitByColumnAndRow(0, (5+$i), $myMissions[$i]->departure);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(1, (5+$i), $myMissions[$i]->destination);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(2, (5+$i), $myMissions[$i]->ddeparture);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(3, (5+$i), $myMissions[$i]->rate);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(4, (5+$i), $myMissions[$i]->dependencies->car->name .' (' . $myMissions[$i]->dependencies->car->registrationnumber . ')');
		}


		$styleCentered = array( /* Entête des tableaux */
			    'alignment' => array(
			        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			    ),
			    'fill' => array(
			            'type' => PHPExcel_Style_Fill::FILL_SOLID,
			            'color' => array('rgb' => '993333')
			    ),
			    'font'  => array(
			        'bold'  => true,
			        'color' => array('rgb' => 'FFFFFF'),
			        'size'  => 14,
			        'name'  => 'Calibri'
			    )
			);
			$styleBorder = array( /* Cadrillage des cellules */
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('rgb' => '000000'),
					),
				),
			);
			foreach(range('A','E') as $columnID) {
			    $myActiveSheet->getColumnDimension($columnID)->setAutoSize(true);
			}
			$myHighestRow = (int)$myActiveSheet->getHighestRow();
			$myActiveSheet->getStyle("A4:E".$myHighestRow)->applyFromArray($styleBorder);
			$myActiveSheet->getStyle("A4:E4")->applyFromArray($styleCentered);
			$styleTitle = array( /* Titre du tableau */
			    'alignment' => array(
			        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			    ),
			    'font'  => array(
			        'bold'  => true,
			        'color' => array('rgb' => '993333'),
			        'size'  => 20,
			        'name'  => 'Calibri'
			    )
			);
			$myActiveSheet->mergeCells('A1:K1');
			$myActiveSheet->mergeCells('A2:K2');
			$myActiveSheet->setCellValueByColumnAndRow(0, 1, 'Rapport d\'activités des Missions des vehicules du park');
			$myActiveSheet->getStyle("A1:A1")->applyFromArray($styleTitle);
			$myActiveSheet->getStyle("A2:A2")->applyFromArray($styleTitle);
			$myActiveSheet->getStyle('C1:C97')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$nameFile = uniqid();
			$file = '../../app/download/'.$nameFile.'.xls';
			$objWriter->save($file);

			return $nameFile.'.xls';
	}

	function exportPDF(){
		$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
		$rendererLibrary = 'mPDF5.4';
		$rendererLibraryPath = '../vendor/mpdf/mpdf';

		$objPHPExcel = new PHPExcel();
		$myActiveSheet = $objPHPExcel->getProperties()->setCreator("Hard Transport Personnel")
							 ->setLastModifiedBy("Hard Transport Personnel")
							 ->setTitle("Données Missions")
							 ->setSubject("Données Missions")
							 ->setDescription("Données Missions")
							 ->setKeywords("Missions")
							 ->setCategory("Missions");

							 

		$myActiveSheet = $objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setShowGridlines(false);
		
		$myActiveSheet->setTitle(_("Vehicules"));
		$myActiveSheet->setCellValueByColumnAndRow(0, 4, _('Départ'));
		$myActiveSheet->setCellValueByColumnAndRow(1, 4, _('Arrivée'));
		$myActiveSheet->setCellValueByColumnAndRow(2, 4, _('Date Départ'));
		$myActiveSheet->setCellValueByColumnAndRow(3, 4, _('Prix'));
		$myActiveSheet->setCellValueByColumnAndRow(4, 4, _('Voiture'));


		$myMissions = missions::getAll();

		$myMissions = $myMissions['data'];

		for ($i=0; $i < count($myMissions) ; $i++) { 
			$myActiveSheet->setCellValueExplicitByColumnAndRow(0, (5+$i), $myMissions[$i]->departure);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(1, (5+$i), $myMissions[$i]->destination);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(2, (5+$i), $myMissions[$i]->ddeparture);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(3, (5+$i), $myMissions[$i]->rate);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(4, (5+$i), $myMissions[$i]->dependencies->car->name .' (' . $myMissions[$i]->dependencies->car->registrationnumber . ')');
		}

		$styleCentered = array( /* Entête des tableaux */
			    'alignment' => array(
			        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			    ),
			    'fill' => array(
			            'type' => PHPExcel_Style_Fill::FILL_SOLID,
			            'color' => array('rgb' => '993333')
			    ),
			    'font'  => array(
			        'bold'  => true,
			        'color' => array('rgb' => 'FFFFFF'),
			        'size'  => 14,
			        'name'  => 'Calibri'
			    )
			);
			$styleBorder = array( /* Cadrillage des cellules */
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('rgb' => '000000'),
					),
				),
			);
			foreach(range('A','E') as $columnID) {
			    $myActiveSheet->getColumnDimension($columnID)->setAutoSize(true);
			}
			$myHighestRow = (int)$myActiveSheet->getHighestRow();
			$myActiveSheet->getStyle("A4:E".$myHighestRow)->applyFromArray($styleBorder);
			$myActiveSheet->getStyle("A4:E4")->applyFromArray($styleCentered);
			$styleTitle = array( /* Titre du tableau */
			    'alignment' => array(
			        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			    ),
			    'font'  => array(
			        'bold'  => true,
			        'color' => array('rgb' => '993333'),
			        'size'  => 20,
			        'name'  => 'Calibri'
			    )
			);
			$myActiveSheet->mergeCells('A1:K1');
			$myActiveSheet->mergeCells('A2:K2');
			$myActiveSheet->setCellValueByColumnAndRow(0, 1, 'Rapport d\'activités des Missions des vehicules du park');
			$myActiveSheet->getStyle("A1:A1")->applyFromArray($styleTitle);
			$myActiveSheet->getStyle("A2:A2")->applyFromArray($styleTitle);
			$myActiveSheet->getStyle('C1:C97')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

		if (!PHPExcel_Settings::setPdfRenderer(
		    $rendererName,
		    $rendererLibraryPath
		)) {
		    die('NOTICE: Please set the $rendererName and $rendererLibraryPath values' .
		        '<br />' .
		        'at the top of this script as appropriate for your directory structure'
		    );
		}

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
			

			$nameFile = uniqid();
			$file = '../../app/download/'.$nameFile.'.pdf';
			$objWriter->save($file);

			return $nameFile.'.pdf';
	}

?>