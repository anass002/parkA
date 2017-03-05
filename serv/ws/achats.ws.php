<?php
	require_once('../classes/auth.class.php');
	require_once('../classes/purshase.class.php');

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
				case 'getAllPurshases':
					echo json_encode(purshase::getAll());
					return false;
					break;
				case 'deletePurshase':
					if(!isset($postdata->id)){
						echo json_encode(returnResponse(true,"Missing parameter to complete deletePaper"));
						return false;
					}
					echo json_encode(purshase::deletePurshaseById($postdata->id));
					return false;
					break;
				case 'savePurshase':
					if(!isset($postdata->achat)){
						echo json_encode(returnResponse(true,"Missing parameter to complete savePaper"));
						return false;
					}
					$achat = json_decode($postdata->achat);

					$myPurshase = new purshase();

					foreach ($achat as $key => $value) {
						if(isset($myPurshase->$key)){
							$myPurshase->$key = $value;
						}
					}
					if(isset($achat->id)){
						$myPurshase->dupdate = date('Y-m-d H:i:s');
						$myPurshase->uupdate = $achat->id;
					}
					echo json_encode($myPurshase->save());
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
							 ->setTitle("Données Achats")
							 ->setSubject("Données Achats")
							 ->setDescription("Données Achats")
							 ->setKeywords("Achats")
							 ->setCategory("Achats");


		$myActiveSheet = $objPHPExcel->setActiveSheetIndex(0);
		$myActiveSheet->setTitle(_("Achats"));
		$myActiveSheet->setCellValueByColumnAndRow(0, 4, _('Nom'));
		$myActiveSheet->setCellValueByColumnAndRow(1, 4, _('N° Facture'));
		$myActiveSheet->setCellValueByColumnAndRow(2, 4, _('N° Bon Commande'));
		$myActiveSheet->setCellValueByColumnAndRow(3, 4, _('Prix'));
		$myActiveSheet->setCellValueByColumnAndRow(4, 4, _('Voiture'));


		$myCars = purshase::getAll();

		$myCars = $myCars['data'];

		for ($i=0; $i < count($myCars) ; $i++) { 
			$myActiveSheet->setCellValueExplicitByColumnAndRow(0, (5+$i), $myCars[$i]->name);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(1, (5+$i), $myCars[$i]->nfacture);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(2, (5+$i), $myCars[$i]->nbl);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(3, (5+$i), $myCars[$i]->price);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(4, (5+$i), $myCars[$i]->dependencies->car->name);
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
			$myActiveSheet->setCellValueByColumnAndRow(0, 1, 'Rapport d\'activités des Achats pour les vehicules');
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
							 ->setTitle("Données Achats")
							 ->setSubject("Données Achats")
							 ->setDescription("Données Achats")
							 ->setKeywords("Achats")
							 ->setCategory("Achats");

							 

		$myActiveSheet = $objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setShowGridlines(false);

		
		$myActiveSheet->setTitle(_("Achats"));
		$myActiveSheet->setCellValueByColumnAndRow(0, 4, _('Nom'));
		$myActiveSheet->setCellValueByColumnAndRow(1, 4, _('N° Facture'));
		$myActiveSheet->setCellValueByColumnAndRow(2, 4, _('N° Bon Commande'));
		$myActiveSheet->setCellValueByColumnAndRow(3, 4, _('Prix'));
		$myActiveSheet->setCellValueByColumnAndRow(4, 4, _('Voiture'));


		$myCars = purshase::getAll();

		$myCars = $myCars['data'];

		for ($i=0; $i < count($myCars) ; $i++) { 
			$myActiveSheet->setCellValueExplicitByColumnAndRow(0, (5+$i), $myCars[$i]->name);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(1, (5+$i), $myCars[$i]->nfacture);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(2, (5+$i), $myCars[$i]->nbl);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(3, (5+$i), $myCars[$i]->price);
			$myActiveSheet->setCellValueExplicitByColumnAndRow(4, (5+$i), $myCars[$i]->dependencies->car->name);
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
			$myActiveSheet->setCellValueByColumnAndRow(0, 1, 'Rapport d\'activités des Achats pour les vehicules');
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