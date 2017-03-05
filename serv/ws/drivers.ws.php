<?php
	require_once('../classes/auth.class.php');
	require_once('../classes/drivers.class.php');
	require_once('../classes/cars.class.php');
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
				case 'getAllDrivers':
					echo json_encode(drivers::getAll());
					return false;
					break;
				case 'deleteDriver':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing parameter to complete deleteCars"));
						return false;
					}
					echo json_encode(drivers::deleteDriverById($postdata->id));
					return false;
					break;
				case 'saveDriver':
					if(!isset($postdata->driver)){
						echo json_encode(returnResponse(true,"Missing parameter to complete saveCar"));
						return false;
					}
					$driver = json_decode($postdata->driver);

					$myDriver = new drivers();

					foreach ($driver as $key => $value) {
						if(isset($myDriver->$key)){
							$myDriver->$key = $value;
						}
					}
					if(isset($driver->id)){
						$myDriver->dupdate = date('Y-m-d H:i:s');
						$myDriver->uupdate = $driver->id;
					}

					$myCar = cars::getCarById($myDriver->carid);

					$myCar = $myCar['data'][0];

					$myCar->flag = 'true';

					$savedCar = $myCar->save();

					if($savedCar['error'] === true){
						echo json_encode(returnResponse(true,"unable to save Car"));
						return false;
					}

					echo json_encode($myDriver->save());
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
		$myActiveSheet->setTitle(_("Chauffeurs"));
		$myActiveSheet->setCellValueByColumnAndRow(0, 4, _('Nom'));
		$myActiveSheet->setCellValueByColumnAndRow(1, 4, _('Prénom'));
		$myActiveSheet->setCellValueByColumnAndRow(2, 4, _('Téléphone'));
		$myActiveSheet->setCellValueByColumnAndRow(3, 4, _('Email'));
		$myActiveSheet->setCellValueByColumnAndRow(4, 4, _('Permis'));
		$myActiveSheet->setCellValueByColumnAndRow(5, 4, _('Cin'));
		$myActiveSheet->setCellValueByColumnAndRow(6, 4, _('Voiture'));


		$myCars = drivers::getAll();

		$myCars = $myCars['data'];

		for ($i=0; $i < count($myCars) ; $i++) { 
			$myActiveSheet->setCellValueExplicitByColumnAndRow(0, (5+$i), $myCars[$i]->lastname);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(1, (5+$i), $myCars[$i]->firstname);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(2, (5+$i), $myCars[$i]->tel);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(3, (5+$i), $myCars[$i]->email);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(4, (5+$i), $myCars[$i]->driverlicense);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(5, (5+$i), $myCars[$i]->cin);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(6, (5+$i), $myCars[$i]->dependencies->car->name .' (' . $myCars[$i]->dependencies->car->registrationnumber . ')');
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

		
		$myActiveSheet->setTitle(_("Chauffeurs"));
		$myActiveSheet->setCellValueByColumnAndRow(0, 4, _('Nom'));
		$myActiveSheet->setCellValueByColumnAndRow(1, 4, _('Prénom'));
		$myActiveSheet->setCellValueByColumnAndRow(2, 4, _('Téléphone'));
		$myActiveSheet->setCellValueByColumnAndRow(3, 4, _('Email'));
		$myActiveSheet->setCellValueByColumnAndRow(4, 4, _('Permis'));
		$myActiveSheet->setCellValueByColumnAndRow(5, 4, _('Cin'));
		$myActiveSheet->setCellValueByColumnAndRow(6, 4, _('Voiture'));


		$myCars = drivers::getAll();

		$myCars = $myCars['data'];

		for ($i=0; $i < count($myCars) ; $i++) { 
			$myActiveSheet->setCellValueExplicitByColumnAndRow(0, (5+$i), $myCars[$i]->lastname);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(1, (5+$i), $myCars[$i]->firstname);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(2, (5+$i), $myCars[$i]->tel);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(3, (5+$i), $myCars[$i]->email);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(4, (5+$i), $myCars[$i]->driverlicense);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(5, (5+$i), $myCars[$i]->cin);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(6, (5+$i), $myCars[$i]->dependencies->car->name .' (' . $myCars[$i]->dependencies->car->registrationnumber . ')');
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