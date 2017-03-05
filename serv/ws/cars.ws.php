<?php 
	require_once('../classes/auth.class.php');
	require_once('../classes/cars.class.php');
	require_once('../classes/missions.class.php');
	require_once('../classes/papers.class.php');
	require_once('../classes/drivers.class.php');
	require_once('../classes/reservations.class.php');
	require_once('../classes/purshase.class.php');
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
				case 'getAllCars':
					echo json_encode(cars::getAll());
					return false;
					break;
				case 'getNotAssignedCars':
					echo json_encode(cars::getNotAssignedCars());
					return false;
					break;
				case 'getAssignedCars':
					echo json_encode(cars::getAssignedCars());
					return false;
					break;		
				case 'deleteCars':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing parameter to complete deleteCars"));
						return false;
					}
					echo json_encode(cars::deleteCarById($postdata->id));
					return false;
					break;
				case 'saveCar':
					if(!isset($postdata->car)){
						echo json_encode(returnResponse(true,"Missing parameter to complete saveCar"));
						return false;
					}
					$car = json_decode($postdata->car);

					$myCar = new cars();

					foreach ($car as $key => $value) {
						if(isset($myCar->$key)){
							$myCar->$key = $value;
						}
					}
					if(isset($car->id)){
						$myCar->dupdate = date('Y-m-d H:i:s');
						$myCar->uupdate = $car->id;
					}
					echo json_encode($myCar->save());
					return false;
					break;
				case 'getAllInfosCar':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing parameter to complete getAllInfosCar"));
						return false;
					}
					$data = new stdClass();


					$myMissions = missions::getMissionsByCarId($postdata->id);

					$data->missions = $myMissions['data'];

					$myDriver = drivers::getDriverByCarId($postdata->id);

					$data->driver = $myDriver['data'];

					$myPapers = papers::getPapersByCarId($postdata->id);

					$data->papers = $myPapers['data'];

					$myReservations = reservations::getReservationByCarId($postdata->id);

					$data->reservations = $myReservations['data'];

					$myPurshases = purshase::getPurshaseByCarId($postdata->id);

					$data->purshases = $myPurshases['data'];

					echo json_encode(returnResponse(false,$data));
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
							 ->setTitle("Données Vehicules")
							 ->setSubject("Données Vehicules")
							 ->setDescription("Données Vehicules")
							 ->setKeywords("vehicule")
							 ->setCategory("vehicule");


		$myActiveSheet = $objPHPExcel->setActiveSheetIndex(0);
		$myActiveSheet->setTitle(_("Vehicules"));
		$myActiveSheet->setCellValueByColumnAndRow(0, 4, _('Code'));
		$myActiveSheet->setCellValueByColumnAndRow(1, 4, _('Modéle'));
		$myActiveSheet->setCellValueByColumnAndRow(2, 4, _('Marque'));
		$myActiveSheet->setCellValueByColumnAndRow(3, 4, _('Carte Grise'));
		$myActiveSheet->setCellValueByColumnAndRow(4, 4, _('Kilométrage'));
		$myActiveSheet->setCellValueByColumnAndRow(5, 4, _('N° Plaque'));
		$myActiveSheet->setCellValueByColumnAndRow(6, 4, _('Catégories'));


		$myCars = cars::getAll();

		$myCars = $myCars['data'];

		for ($i=0; $i < count($myCars) ; $i++) { 
			$myActiveSheet->setCellValueExplicitByColumnAndRow(0, (5+$i), $myCars[$i]->carcode);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(1, (5+$i), $myCars[$i]->name);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(2, (5+$i), $myCars[$i]->brand);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(3, (5+$i), $myCars[$i]->greycard);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(4, (5+$i), $myCars[$i]->km);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(5, (5+$i), $myCars[$i]->registrationnumber);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(6, (5+$i), $myCars[$i]->dependencies->categorie->name);
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
			foreach(range('A','G') as $columnID) {
			    $myActiveSheet->getColumnDimension($columnID)->setAutoSize(true);
			}
			$myHighestRow = (int)$myActiveSheet->getHighestRow();
			$myActiveSheet->getStyle("A4:G".$myHighestRow)->applyFromArray($styleBorder);
			$myActiveSheet->getStyle("A4:G4")->applyFromArray($styleCentered);
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
			$myActiveSheet->setCellValueByColumnAndRow(0, 1, 'Rapport d\'activités des Vehicules du park');
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
							 ->setTitle("Données Vehicules")
							 ->setSubject("Données Vehicules")
							 ->setDescription("Données Vehicules")
							 ->setKeywords("vehicule")
							 ->setCategory("vehicule");

							 

		$myActiveSheet = $objPHPExcel->setActiveSheetIndex(0);

		
		$myActiveSheet->setTitle(_("Vehicules"));
		$myActiveSheet->setCellValueByColumnAndRow(0, 4, _('Code'));
		$myActiveSheet->setCellValueByColumnAndRow(1, 4, _('Modéle'));
		$myActiveSheet->setCellValueByColumnAndRow(2, 4, _('Marque'));
		$myActiveSheet->setCellValueByColumnAndRow(3, 4, _('Carte Grise'));
		$myActiveSheet->setCellValueByColumnAndRow(4, 4, _('Kilométrage'));
		$myActiveSheet->setCellValueByColumnAndRow(5, 4, _('N° Plaque'));
		$myActiveSheet->setCellValueByColumnAndRow(6, 4, _('Catégories'));


		$myCars = cars::getAll();

		$myCars = $myCars['data'];

		for ($i=0; $i < count($myCars) ; $i++) { 
			$myActiveSheet->setCellValueExplicitByColumnAndRow(0, (5+$i), $myCars[$i]->carcode);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(1, (5+$i), $myCars[$i]->name);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(2, (5+$i), $myCars[$i]->brand);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(3, (5+$i), $myCars[$i]->greycard);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(4, (5+$i), $myCars[$i]->km);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(5, (5+$i), $myCars[$i]->registrationnumber);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(6, (5+$i), $myCars[$i]->dependencies->categorie->name);
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
			foreach(range('A','G') as $columnID) {
			    $myActiveSheet->getColumnDimension($columnID)->setAutoSize(true);
			}
			$myHighestRow = (int)$myActiveSheet->getHighestRow();
			$myActiveSheet->getStyle("A4:G".$myHighestRow)->applyFromArray($styleBorder);
			$myActiveSheet->getStyle("A4:G4")->applyFromArray($styleCentered);
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
			$myActiveSheet->setCellValueByColumnAndRow(0, 1, 'Rapport d\'activités des Vehicules du park');
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