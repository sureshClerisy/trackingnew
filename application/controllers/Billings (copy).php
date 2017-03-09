<?php

/**
* Truck stop Api Controller
*  
*/

class Billings extends Admin_Controller{
	
	public $triuser;
    public $password;
    public $apikey;
    public $token;
	public $userId;
	public $finalArray;
	public $triumphToken;

	function __construct(){
		parent::__construct();
		
		$this->userId 		= $this->session->loggedUser_id;
		$this->load->model('Vehicle');
		$this->load->model('Job');
		$this->load->model('Billing');
		$this->load->helper('truckstop');
		$this->load->helper('download');
		
		$this->triuser  = $this->config->item('triumph_user');
        $this->password = $this->config->item('triumph_pass');
        $this->apikey   = $this->config->item('triumph_apik');
    }
	
	public function index() {
		$statesAddress = $this->Vehicle->get_vehicles_address();
		$vehicleIdRepeat = $statesAddress[0]['id'];	
		
		/*** Get All Label Array ****/
		$vehicleList = array();
		if(!empty($statesAddress) && is_array($statesAddress))
		{
			
			$vehicleList[0]['key'] = 'all';
			$vehicleList[0]['label'] = 'Select Driver';
			$i = 1;
			foreach($statesAddress as $state) {
				$vehicleList[$i]['key'] = trim($state['id']);
				$vehicleList[$i]['label'] = $state['driverName'].'-'.$state['label'];
				$i++;
			}
		}
	
		$data = array();
		/**Getting only In progress Loads*/
		$jobs = $this->Billing->getInProgressLoads();
		
		$data['loads'] = $jobs;
		$data['billType'] = 'billing';
		$data['statesAddress'] 	= $statesAddress;
		$data['vehicleIdRepeat'] = $vehicleIdRepeat;
		$data['labelArray'] = $vehicleList;
		echo json_encode($data);
	}
		
	public function fetchVehicleAddress( $vehicleId = null ) {
		$data = array();
		$result = $this->Job->fetchVehicleAddress( $vehicleId);
		if ( !empty($result) ) {
			$data = $result;
		}
		
		echo json_encode($data);
	}

	/**
	 * Fetching loads with generated invoice only for send payment page
	*/ 
	
	public function sendForPayment() {
		$data = $this->fetchingSentPaymentsInfo();
		$data['billType'] = 'sendForPayment';
		echo json_encode($data);
	}
	
	/**
	 * Fetching load info common function
	 */
	  
	public function fetchingSentPaymentsInfo() {
		$data = array();
		$data['loads'] = $this->Billing->fetchLoadsForPayment();
		$data['sentPaymentCount'] = $this->Billing->sentPaymentCount();
		$data['flaggedPaymentCount'] = $this->Billing->flaggedPaymentCount();
		return $data;
	}
	
	/**
	 * Fetching loads which are sent for payment
	 */
	
	public function fetchSentPaymentRecords() {
		$data['loads'] = $this->Billing->fetchSentPaymentLoads();
		echo json_encode($data);
	} 	
	
	/**
	 * Fetching loads whose flag is set for payment
	 */
	
	public function fetchFlaggedPaymentRecords() {
		$data['loads'] = $this->Billing->fetchFlaggedPaymentLoads();
		echo json_encode($data);
	} 	
	
	/**
	 * documentId = 2123533
	 * {"ConfirmationCode":"0F71C50E","Error":false,"ErrorMessage":""}
	 * creating schedule and send for payment
	 */ 
	public function creatingSchedule() {
		$objPost = json_decode(file_get_contents('php://input'),true);
		$objPost['selectedIds'] = array(10015,10016,10017);
		
		pr($objPost); 
		//~ $returnArray = $this->createSchedulePdfFile($objPost['selectedIds']);
		$genDocs = array();
		$createDocument = array();
		$createErrorFile = array();
		$inputIdsForFinal = array();
		$saveIds = array();
		$resultReturnedArray = $this->createMultipleInputs($objPost['selectedIds']);
		pr($resultReturnedArray);
		$resultReturnedIds = $resultReturnedArray[0];
		$this->triumphToken = $resultReturnedArray[1];
		
		//~ $resultReturnedIds = array(559885);
		$docTypesArray = $this->getApiMethodValue('/v1List/DocTypes','', $this->triumphToken);
		$docTypes = $docTypesArray[0];
		$this->triumphToken = $docTypesArray[1];
		
		$docArray = array();
		if( !empty($docTypes) ) {
			$i = 0;
			$documentType = '';
			foreach( $docTypes as $docs ) {
				
				if( $docs['name'] == 'Invoice' || $docs['name'] == 'Rate Confirmation' || $docs['name'] == 'Bill of Lading' ) {
					$documentType .= 'docType['.$i.']='.$docs['documentTypeId'].'&';
				} 
				$i++; 
			}
			$documentType = rtrim($documentType,'&');
		} else {
			$documentType = 'docType[0]=1&docType[1]=2&docType[2]=3';
		}
		
			
		for ( $i = 0; $i < count($resultReturnedIds); $i++ ) {
			$genDocs[$i]['inputId'] = $resultReturnedIds[$i];
			$genDocs[$i]['filename'] = $this->Billing->getBundleFileName( $objPost['selectedIds'][$i] );
			$genDocs[$i]['fileData'] = $this->convertByte( $genDocs[$i]['filename'] );
			$genDocs[$i]['docType'] = $documentType;
			
			$docsResultArray = $this->createDocument($genDocs[$i], $this->triumphToken);
			
			$docsResult = $docsResultArray[0];
			$this->triumphToken = $docsResultArray[1];
			
			if ( $docsResult[0] != '' && is_numeric($docsResult[0]) )
				$createDocument[$i]['documentId'] = $docsResult[0];
			else
				$createErrorFile[$i]['error'] = $docsResult[0];
			
			$inputIdsForFinal[$i] = $genDocs[$i]['inputId'];
			$saveIds[$i] = "'".$genDocs[$i]['inputId']."+".$objPost['selectedIds'][$i]."'";
			$this->Billing->updatePaymentSent( $objPost['selectedIds'][$i] );
		}
	
		$idsVar = '';
		foreach ( $saveIds as $saveId ) {
			$newIds = explode('+',$saveId);
			$idsVar .= $newIds[1].',';
		}
		$idsVar = rtrim($idsVar,',');
		
		$fundingOptionsArray = $this->getApiMethodValue('/v1List/FundingOptions','funding',$this->triumphToken);
		$fundingOptions = $fundingOptionsArray[0];
		$this->triumphToken = $fundingOptionsArray[1];
		$funding = 'Fund using WIRE *9995';
			
		if (!empty( $fundingOptions) ) {
			foreach( $fundingOptions as $funds ) {
				if ( $funds['isDefault'] == 1 ) 
					$funding = $funds['name'];
			}
		} 
	
		$errorMessage = '';
		if ( !empty($inputIdsForFinal) ) {
			$finalizeInputArray = $this->createFinalizeInputArray($inputIdsForFinal, $funding,$this->triumphToken);
			$finalizeInput = $finalizeInputArray[0];
			$this->triumphToken = $finalizeInputArray[1];
			if ( $finalizeInput != '' && strpos($finalizeInput, 'Id:') === false ) {
				$this->Billing->saveConfirmationCode($idsVar,$finalizeInput);
			} else {
				$invalidIdArray = explode('Id:',$finalizeInput);
				$invalidId = $invalidIdArray[1];
				
				foreach ( $saveIds as $saveId ) {
					if ( strpos($saveId, $invalidId) !== false ) {
						$loadIdArr = explode('+',$saveId);
						$laodId = $loadIdArr[1];
					}
				}				
				//~ echo $laodId;
				$errorMessage = 'Error !: Some error occured while submiting documents to triumph';
			}
		}
	
		$data = $this->fetchingSentPaymentsInfo();
		//~ echo json_encode(array('success' => true,'loadIds' => $returnArray[0],'uploadedSchedule' => $returnArray[1],'paymentLoads' => $loads));
		echo json_encode(array('success' => true,'loadsInfo' => $data, 'errorMessage' => $errorMessage));
	}
	
	/**
	 * Getting api method values
	 */
	
	public function getApiMethodValue( $url = '', $type = '', $token = '' ) {
		echo $token.'--';
		$postData = '';
		$result = $this->commonTriumphCurlRequest( $url, $postData, $token);
		
		if ( $result['Error'] == 1 && ( strpos($result['ErrorMessage'], 'No cookie named SessionToken was passed with the request or it was not in a valid format.') !== false) ) {
			$token = $this->get_sessionToken();
			$this->triumphToken = $token->SessionToken;
			$newResult = $this->commonTriumphCurlRequest( $url, $postData, $this->triumphToken);
			if( !empty($newResult) && $newResult['Error'] == '' ) {
				if ( $type == 'funding' ) {
					return array($newResult['fundingOptions'], $this->triumphToken);
				} else {
					return array($newResult['DocumentTypes'], $this->triumphToken);
				}
			}
		} else {
			if ( $type == 'funding' ) {
				return array($result['fundingOptions'], $token);
			} else {
				return array($result['DocumentTypes'], $token);
			}
		}
	}
	
	/**
	 * Converting file to byte array
	 */
	  
	public function convertByte( $fileName = '') {
		ini_set('max_execution_time', -1);
		$pathGen = str_replace('application/', '', APPPATH);
		$_PATH = $pathGen.'assets/uploads/documents/bundle/'.$fileName;
		$b64Doc = chunk_split(base64_encode(file_get_contents($_PATH)));
		return $b64Doc;
	}	
	
	/**
	 * Creating Document Triumph Api method 
	 */
	 
	public function createDocument( $loadDetail = array(), $token = '' ) {
		echo $token;
		$url = 'v1Submit/CreateDocument';
		//~ $postData = "inputId={$loadDetail['inputId']}&filename={$loadDetail['filename']}&fileData={$loadDetail['fileData']}&docType['documentTypeId'][]=array(1,2,3)}";
		$postData = "inputId={$loadDetail['inputId']}&filename={$loadDetail['filename']}&fileData={$loadDetail['fileData']}&{$loadDetail['docType']}"; 
		$postData1 = "inputId={$loadDetail['inputId']}&filename={$loadDetail['filename']}&{$loadDetail['docType']}"; 
		echo $postData1; 
		$result = $this->commonTriumphCurlRequest( $url, $postData, $token);
		pr($result);
		if ( $result['Error'] == 1 && ( strpos($result['ErrorMessage'], 'No cookie named SessionToken was passed with the request or it was not in a valid format.') !== false) ) {
			$token = $this->get_sessionToken();
			$this->triumphToken = $token->SessionToken;
			$newResult = $this->commonTriumphCurlRequest( $url, $postData, $this->triumphToken);
			pr($newResult);
			if( !empty($newResult) && $newResult['Error'] == '' ) {
				return array($newResult['documentId'], $this->triumphToken);
			}
		} else {
			pr($result);
			return array($result['documentId'], $token);
		}
	}
	 
	/**
	 * finalize input array
	 */
	
	public function createFinalizeInputArray($fianlInput = array(), $fundingOptions = '', $token) {
		$postData = '';
		$i = 0;
		foreach( $fianlInput as $input ) {
			$postData .= "inputIds[$i]={$input}&";
			$i++;
		}
		$postData = $postData."fundingInstructions={$fundingOptions}";
		echo $postData;
		$url = 'v1Submit/FinalizePendingInputArray';
		$result = $this->commonTriumphCurlRequest( $url, $postData, $token);
		pr($result);
		if ( $result['Error'] == 1 && ( strpos($result['ErrorMessage'], 'No cookie named SessionToken was passed with the request or it was not in a valid format.') !== false) ) {
			$token = $this->get_sessionToken();
			$this->triumphToken = $token->SessionToken;
			$newResult = $this->commonTriumphCurlRequest( $url, $postData, $this->triumphToken);
			if( !empty($newResult) && $newResult['Error'] == '' ) {
				return array($newResult['ConfirmationCode'], $this->triumphToken);
			}
		} else {
			return array($result['ConfirmationCode'], $token);
		}
	}
	
	/**
	 * Create  Multiple Inputs method 4426c8df-7530-4bc4-abe5-e7c8dc53cee7
	 */
	 
	public function createMultipleInputs( $loadIds = array() ) {
		$i = 0;
		$postData = '';
		foreach( $loadIds as $loadId ) {
			$jobRecord = $this->Job->FetchSingleJobCreateInput($loadId);
			$postData .= "[$i].referenceKey=''&[$i].invoiceNumber={$jobRecord['invoiceNo']}&[$i].invoiceDate={$jobRecord['invoicedDate']}&[$i].referenceNumber={$jobRecord['woRefno']}&[$i].grossAmount={$jobRecord['PaymentAmount']}&[$i].isMiscInvoice=true&[$i].customerName={$jobRecord['transportComp_name']}&[$i].customerId={$jobRecord['MCNumber']}&";
			$i++;
		}
		$postData = rtrim($postData,'&');
		echo $postData;
		if ( $this->triumphToken != '' && $this->triumphToken != null ) {
			
		} else {
			$token = $this->get_sessionToken();
			$this->triumphToken = $token->SessionToken;
		}

		$url = 'v1Submit/CreateInputsFromArray';
		$result = $this->commonTriumphCurlRequest( $url, $postData, $this->triumphToken);
		pr($result);
		if ( $result['Error'] == 1 && ( strpos($result['ErrorMessage'], 'No cookie named SessionToken was passed with the request or it was not in a valid format.') !== false) ) {
			$token = $this->get_sessionToken();
			$this->triumphToken = $token->SessionToken;
			$newResult = $this->commonTriumphCurlRequest( $url, $postData, $this->triumphToken);
			if( !empty($newResult) && $newResult['Error'] == '' ) {
				return array($newResult['InputId'], $this->triumphToken);
			}
		} else {
			return array($result['InputId'], $this->triumphToken);
		}
	} 
	 
	/**
	 * Creating common triumph curl method
	 */
	 
	public function commonTriumphCurlRequest( $url = '', $post = '', $triumphToken = '') {
		$c = curl_init('https://testapi.mytriumph.com/'.$url);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, $post);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_COOKIE, "SessionToken={$triumphToken}");
		$page = curl_exec($c);
		curl_close($c);
		
		$response = json_decode($page,TRUE);
		return $response;
	}
	 	
	/**
	 * Getting load detail for single record on send for payment
	 */
	 
	public function getLoadDetail( $loadId = null ) {
		$result = $this->Billing->getSingleLoadDetail( $loadId );
		if ( $result['doc_name'] != '' ) {
			$docNameArr = explode('.',$result['doc_name']);
			$result['thumbBundle'] = 'thumb_'.$docNameArr[0].'.jpg';
		} else {
			$result['thumbBundle'] = '';
		}
		echo json_encode($result);
	} 
	
	/**
	 * Flag or unflag the load for payment
	 */
	
	public function flagLoad( $status = '', $loadId = null ) {
		$result = $this->Billing->flagUnflagLoad( $status, $loadId );
		$data = $this->fetchingSentPaymentsInfo();
		//~ $loads = $this->Billing->fetchLoadsForPayment();
		$data['flaggedLoads'] = $this->Billing->fetchFlaggedPaymentLoads();
		echo json_encode(array('success' => true,'loadsInfo' => $data));	
	} 
	
	
	/**
	 * Create Inputs method
	 */
	 
	public function createInputs( $loadId = null ) {
		$jobRecord = $this->Job->FetchSingleJob($loadId);
		$token = $this->get_sessionToken();
				
		$c = curl_init('https://testapi.mytriumph.com/v1Submit/CreateInput');
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, "referenceKey=''&invoiceNumber={$jobRecord['invoiceNo']}&invoiceDate={$jobRecord['invoicedDate']}&referenceNumber={$jobRecord['woRefno']}&grossAmount={$jobRecord['PaymentAmount']}&isMiscInvoice=true&customerName={$jobRecord['transportComp_name']}&customerId={$jobRecord['MCNumber']}");
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_COOKIE, "SessionToken={$token->SessionToken}");
		$page = curl_exec($c);
		curl_close($c);
	   
		$datalist = json_decode($page,TRUE);
		if( !empty($datalist) && $datalist['Error'] == '' ) {
			return $datalist['InputId'];			
		}
	} 
	
	/**
	 * Generating session token for triumph
	 */
	 
	public function get_sessionToken(){
       $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->config->item('triumph_url'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "username={$this->triuser}&password={$this->password}&apiKey={$this->apikey}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        $data = (json_decode($server_output));
        curl_close ($ch);
        return $data;
    }
    
    /**
     * Fetching billing detail for custom added Load
     */
     
    public function getShippingInfo( $loadId = null ) {
		$result['billingDetail'] = $this->Billing->getShippingLoadInfo( $loadId );
		echo json_encode($result);
	}
	
	public function createSchedulePdf( $loadIdsArray = array()) {
		//load our new PHPExcel library
		$this->load->library('excel');
		
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		//activate worksheet number 1
		$this->excel->setActiveSheetIndex(0);
		//name the worksheet
		$this->excel->getActiveSheet()->setTitle('test worksheet');
		
		$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
		$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
		$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
		$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
		
		$this->excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('C1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('D1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
		//set cell A1 content with some text
		$this->excel->getActiveSheet()->setCellValue('A1', 'DTR_NAME');
		$this->excel->getActiveSheet()->setCellValue('B1', 'INVOICE');
		$this->excel->getActiveSheet()->setCellValue('C1', 'INVOICEDATE');
		$this->excel->getActiveSheet()->setCellValue('D1', 'PO');
		$this->excel->getActiveSheet()->setCellValue('E1', 'INV_AMT');
		//change the font size
		$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(10)->setBold(true);
		$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setSize(10)->setBold(true);
		$this->excel->getActiveSheet()->getStyle('C1')->getFont()->setSize(10)->setBold(true);
		$this->excel->getActiveSheet()->getStyle('D1')->getFont()->setSize(10)->setBold(true);
		$this->excel->getActiveSheet()->getStyle('E1')->getFont()->setSize(10)->setBold(true);
		//make the font become bold
		//~ $this->excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		//~ $objPost['selectedIds'] = array(33,34,36);
		$i = 2;
        $totalPaymentAmount = 0;
       
        if ( !empty($loadIdsArray) && count($loadIdsArray) > 0 ) {
			foreach( $loadIdsArray as $selectedId ) {
				$loadArray = $this->Billing->getLoadDataForSchedule($selectedId);
				$loadInfo = $loadArray[0];
				$loadDocs = $loadArray[1];
				$totalPaymentAmount += $loadInfo['PaymentAmount'];
				
				$this->excel->getActiveSheet()->setCellValue('A'.$i, $loadDocs[0]['doc_name']); 
				$this->excel->getActiveSheet()->setCellValue('B'.$i, $loadInfo['invoiceNo']); 
				$this->excel->getActiveSheet()->setCellValue('C'.$i, $loadInfo['invoicedDate']); 
				$this->excel->getActiveSheet()->setCellValue('D'.$i, $loadDocs[1]['doc_name']); 
				$this->excel->getActiveSheet()->setCellValue('E'.$i, $loadInfo['PaymentAmount']); 
				
				$this->excel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
			   
				$i++;
			}
			
			$i = $i + 2;
			$content = 'For valueable consideration, receipt of which is hereby acknowledged, the undersign hereby SELLS, assigns, sets over and transfers to Advanced Business Capital LLC d/b/a Triumph Business Capital ("Triumph"), its successors or assigns, all its rights, title and interest in and to the invoices listed above, including all monies due or to become due thereon, all in accordance with conditions, representations, warranties and agreements of which are made part of this SALE and assignment and incorporated herein by reference.';
			//merge cell A1 until D1
			$this->excel->getActiveSheet()->mergeCells('A'.$i.':D'.$i);
			$this->excel->getActiveSheet()->setCellValue('A'.$i, 'Invoices the schedule'); 
			$this->excel->getActiveSheet()->getStyle('A'.$i)->getFont()->setSize(10)->setBold(true);
			$this->excel->getActiveSheet()->setCellValue('E'.$i, $totalPaymentAmount); 
			
			$this->excel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
			
			/**Writing static content*/
			$i = $i + 2;
			$this->excel->getActiveSheet()->mergeCells('A'.$i.':E'.$i);
			$this->excel->getActiveSheet()->setCellValue('A'.$i, $content); 
			$this->excel->getActiveSheet()->getRowDimension($i)->setRowHeight(70);
			$this->excel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->setWrapText(true);
			
			/**Writing company name content*/
			$i = $i + 2;
			$this->excel->getActiveSheet()->mergeCells('A'.$i.':B'.$i);
			$this->excel->getActiveSheet()->setCellValue('A'.$i, 'Company Name'); 
			$this->excel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
			$this->excel->getActiveSheet()->getStyle('A'.$i)->getFont()->setSize(10)->setBold(true);
				
			$i = $i + 1;
			$this->excel->getActiveSheet()->mergeCells('A'.$i.':B'.$i);
			$this->excel->getActiveSheet()->setCellValue('A'.$i, 'Vika Logistics Corp c/o Triumph Buisness Capital LLC'); 
			$this->excel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
		
			/**showing signature image*/		
			$i = $i + 2;
			$this->excel->getActiveSheet()->mergeCells('A'.$i.':B'.$i);
			$this->excel->getActiveSheet()->setCellValue('A'.$i, 'Authorization Signature'); 
			$this->excel->getActiveSheet()->getStyle('A'.$i)->getFont()->setSize(10)->setBold(true);
			$this->excel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
			
			$this->excel->getActiveSheet()->mergeCells('D'.$i.':E'.$i);
			$this->excel->getActiveSheet()->setCellValue('D'.$i, 'Date of Assignment'); 
			$this->excel->getActiveSheet()->getStyle('D'.$i)->getFont()->setSize(10)->setBold(true);
			$this->excel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
			
			
			$i = $i + 1;
			$objDrawing->setPath('./assets/img/invoice/signature2x.png');
			$this->excel->getActiveSheet()->mergeCells('A'.$i.':B'.$i);
			$this->excel->getActiveSheet()->getRowDimension($i)->setRowHeight(50);
			$this->excel->getActiveSheet()->getStyle('A'.$i.':B'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objDrawing->setCoordinates('A'.$i);
			$objDrawing->setHeight(150);
			$objDrawing->setWidth(150);
			$objDrawing->setWorksheet($this->excel->getActiveSheet());
			
			$this->excel->getActiveSheet()->mergeCells('D'.$i.':E'.$i);
			$this->excel->getActiveSheet()->setCellValue('D'.$i, date('Y-m-d')); 
			
			$filename='schedule_'.time().'.xls'; //save our workbook as this file name
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
			header('Cache-Control: max-age=0'); //no cache
						 
			//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
			//if you want to save it as .XLSX Excel 2007 format
			$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
			$objWriter->save('php://output');
			//~ $pathGen = str_replace('application/', '', APPPATH);
			
			$objWriter->save($pathGen.'/assets/uploads/documents/schedule/'.$filename);
			
			$load_ids = implode(',',$loadIdsArray);
			$triumphSave =  array(
				'load_ids' => $load_ids,
				'schedule_name' => $filename,
				'user_id' => $this->userId,
			);
			
			/**Saving triumph Payments */
			$this->Billing->saveTriumphPaymentRequest( $triumphSave );
			
		}
		return array($load_ids,$filename);
		//~ echo json_encode(array('success' => true,'loadIds' => $load_ids,'uploadedSchedule' => $filename));
	}
	
	/*
	 * creating schedule pdf file
	 */
	 
	public function createSchedulePdfFile( $loadIdsArray = array()) {
		$data = array();
		$i = 0;
        $data['totalPaymentAmount'] = 0;
       
        if ( !empty($loadIdsArray) && count($loadIdsArray) > 0 ) {
			foreach( $loadIdsArray as $selectedId ) {
				$loadArray = $this->Billing->getLoadDataForSchedule($selectedId);
				$data['loadInfo'][$i] = $loadArray[0];
				$data['loadDocs'][$i] = $loadArray[1];
				$data['totalPaymentAmount'] += $data['loadInfo'][$i]['PaymentAmount'];
				$i++;
			}
			
		}
		
		$data['content'] = 'For valueable consideration, receipt of which is hereby acknowledged, the undersign hereby SELLS, assigns, sets over and transfers to Advanced Business Capital LLC d/b/a Triumph Business Capital ("Triumph"), its successors or assigns, all its rights, title and interest in and to the invoices listed above, including all monies due or to become due thereon, all in accordance with conditions, representations, warranties and agreements of which are made part of this SALE and assignment and incorporated herein by reference.';
		
		$html = $this->load->view('schedule', $data, true); 
		$fileName = 'schedule_'.time().'.pdf';
		$pathGen = str_replace('application/', '', APPPATH);
		$pdfFilePath = $pathGen."assets/uploads/documents/schedule/".$fileName;
 
		$this->load->library('m_pdf');
		$this->m_pdf->pdf->WriteHTML($html);
		//~ $this->m_pdf->pdf->Output($pdfFilePath, "D");    
		$this->m_pdf->pdf->Output($pdfFilePath, "F"); 
		
		$load_ids = implode(',',$loadIdsArray);
		$triumphSave =  array(
			'load_ids' => $load_ids,
			'schedule_name' => $fileName,
			'user_id' => $this->userId,
		);
			
			/**Saving triumph Payments */
		$this->Billing->saveTriumphPaymentRequest( $triumphSave );
			
		return array($load_ids,$filename);
	}
	
}



