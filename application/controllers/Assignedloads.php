<?php

/**
* Assignedloads Controller
*  
*/

class Assignedloads extends Admin_Controller{

	
	private $userId;
	private $orign_city;
	private $orign_state;
	private $saveCalPayment;
	public  $rows;
	public  $finalArray;
	public  $data;
	public  $userName;
	
	function __construct(){

		parent::__construct();
		
		$this->userRoleId  = $this->session->role;
		$this->userId 	   = $this->session->loggedUser_id;
		$this->userName    = $this->session->loggedUser_username;
		$this->finalArray  = array();
		$this->data        = array();
		
		$this->load->model(array('Vehicle','BrokersModel','Job','Billing','Driver','User'));
		$this->load->helper('truckstop');
	}
	
	public function index() {
		$userId = false;
		$parentId = false;
		$tempUserId = $this->userId;
		$childIds  = array();				// dispatchers child list
		if($this->userRoleId == _DISPATCHER ){
			$userId = $this->userId;
			$parentId =  $this->userId;
			$childIds = $this->User->fetchDispatchersChilds($userId);
		} else if ( $this->userRoleId == 4 ) {
			$parentIdCheck = $this->session->userdata('loggedUser_parentId');
			if( isset($parentIdCheck) && $parentIdCheck != 0 ) {
				$userId = $parentIdCheck;
				$parentId = $parentIdCheck;
				$tempUserId = $parentIdCheck;
			}
		}
		
		$allVehicles = $this->Driver->getDriversListNew($userId);
		$dispatcherList = $this->Driver->getDispatcherList($parentId); 	//for add all drivers under every dispatcher
		$teamList = $this->Driver->getDriversListAsTeamNew($userId);

		if ( !empty($childIds)) {
			$childVehicles = array();
			foreach($childIds as $child ) {
				$childVehicles = $this->Driver->getDriversListNew($child['id']);
				$allVehicles = array_merge($allVehicles,$childVehicles);	
				
				$childVehicles = $this->Driver->getDriversListAsTeamNew($child['id']);
				$teamList = array_merge($teamList,$childVehicles);

				$childs = $this->Driver->getDispatcherList($child['id']);
				$dispatcherList = array_merge($dispatcherList,$childs);
			}
		}

		$vDriversList = array();
		if(!empty($allVehicles) && is_array($allVehicles)){	
			$vDriversList = $allVehicles;

			if(is_array($teamList) && count($teamList) > 0){
				foreach ($teamList as $key => $value) {
					$value["label"] = "_team";
					array_unshift($vDriversList, $value);
				}
			}
			foreach ($dispatcherList as $key => $value) {
				array_unshift($vDriversList, $value);
			}
			
			if($this->userRoleId != _DISPATCHER && $this->userRoleId != 4){
				$new = array("id"=>"","driverName"=>"All Groups","username"=>"","dispId"=>"","vid"=>"","dispId" => "");
				array_unshift($vDriversList, $new);
			}
		}

		// pr($vDriversList);
		$newDestlabel = array();
		$gVehicleId = false;
		$startDate = $endDate = '' ;
		if(isset($_COOKIE["_gDateRange"]) && !empty($_COOKIE["_gDateRange"])){
			$gDateRange = json_decode($_COOKIE["_gDateRange"],true);
			$startDate = $gDateRange["startDate"]; $endDate = $gDateRange["endDate"];
		}

		if(isset($_COOKIE["_globalDropdown"]) && !empty($_COOKIE["_globalDropdown"])){

			$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
			// pr($gDropdown); die;
			if (isset($gDropdown["label"]) && $gDropdown["label"] == "_idispatcher") {  //A Dispatcher's All drivers
				$jobs = $this->getSingleVehicleLoads($this->userId,array(),"dispatcher", $gDropdown['dispId'],false,false,$startDate,$endDate); //Fetch Loads by vehicle id(s)
				$this->data["total"] = $this->Job->fetchSavedJobsTotal($this->userId,array(),"dispatcher",$gDropdown['dispId'],false,false,$startDate,$endDate); 

				$this->data['table_title'] =  "Dispatcher : ".$gDropdown["username"];
				$this->data['vehicleIdRepeat'] = '';
			} else if ( !isset($gDropdown["label"]) || empty(trim($gDropdown["label"])) ) { //All Groups
				$gVehicleId = false;
				$this->data['table_title'] = "All Groups";
				$jobs = $this->getSingleVehicleLoads($this->userId,array(),"all",false,false,false,$startDate,$endDate); 
				$this->data["total"] = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,false,$startDate,$endDate); 
				$this->data['vehicleIdRepeat'] = '';
			} else {																//Single Driver
				$gVehicleId = $gDropdown["vid"];
				$statesAddress = $this->Vehicle->get_vehicles_address($tempUserId,$gVehicleId);
				$vehicleIdRepeat = (isset($statesAddress[0]['id']) && !empty($statesAddress[0]['id']) ) ? $statesAddress[0]['id'] : $gVehicleId;	
				$results = $this->Vehicle->getLastLoadRecord($gVehicleId, $gDropdown['id']);
				if ( !empty($results) ){
					$this->origin_state = $results['DestinationState'];
					$this->origin_city = $results['DestinationCity'];
				} else {
					$this->origin_state = $statesAddress['0']['state'];
					$this->origin_city = $statesAddress['0']['city'];
				}
				$this->data['table_title'] = 'Truck-'.@$statesAddress[0]['label'].' '.$this->origin_city.'-'.$this->origin_state;
				$this->data['vehicleIdRepeat'] = $vehicleIdRepeat;
				if($gDropdown["label"] == "_team") {

					$jobs = $this->getSingleVehicleLoads($tempUserId, $vehicleIdRepeat,"team", $gDropdown['dispId'], $gDropdown['id'], $gDropdown['team_driver_id'], $startDate,$endDate);	
					$this->data["total"] = $this->Job->fetchSavedJobsTotal($tempUserId,$vehicleIdRepeat,"team",$gDropdown['dispId'],$gDropdown['id'], $gDropdown['team_driver_id'], $startDate,$endDate); 

				} else{
					//pr($gDropdown);die;
					$jobs = $this->getSingleVehicleLoads($tempUserId, $vehicleIdRepeat, "driver", $gDropdown['dispId'], $gDropdown['id'], $gDropdown['team_driver_id'], $startDate,$endDate);	
					$this->data["total"] = $this->Job->fetchSavedJobsTotal($this->userId,$vehicleIdRepeat,"driver",$gDropdown['dispId'],$gDropdown['id'], $gDropdown['team_driver_id'], $startDate,$endDate); 
				}
			}
		} else {
			if($this->userRoleId == _DISPATCHER || $this->userRoleId == 4 ){
				$jobs = $this->getSingleVehicleLoads($userId,array(),"dispatcher",$userId,false,false,$startDate,$endDate); //Fetch Loads by vehicle id(s)
				$this->data["total"] = $this->Job->fetchSavedJobsTotal($userId,array(),"dispatcher",$userId,false,false,$startDate,$endDate); 
				$this->data['table_title'] =  "Dispatcher : ".$this->session->userdata('loggedUser_username');
				$this->data['vehicleIdRepeat'] = '';
			} else {
				$this->data['table_title'] = "All Groups";
				$jobs = $this->getSingleVehicleLoads($this->userId,array(),"all"); //Fetch Loads by vehicle id(s)
				$this->data["total"] = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,false,$startDate,$endDate); 
				$this->data['vehicleIdRepeat'] = '';
			}
		}

		$this->data['labelArray'] = $vDriversList;		
		$this->data['loadSource'] = 'truckstop.com';
		$this->data["selectedDriver"] = isset($gDropdown) ? $gDropdown : "";
		if ( $jobs ) {
			$this->data['assigned_loads'] = $jobs;
		} else {
			$this->data['assigned_loads'] = array();
			$this->data['vehicleIdRepeat'] = '';
		}
		echo json_encode($this->data);
	}
	
	public function getChangeDriverLoads( $vehicleId = null, $driverId = null, $secondDriverId = null ) {
		$objPost = json_decode(file_get_contents('php://input'),true);

		$loadSource = '';

		$startDate = (isset($objPost['startingDate']) && $objPost['startingDate'] != 'undefined' ) ? $objPost['startingDate'] : '';
		$endDate = (isset($objPost['endingDate']) && $objPost['endingDate'] != 'undefined' ) ? $objPost['endingDate'] : '';
		
		if(isset($objPost["scopeType"]) && ($objPost["scopeType"] == "driver" || $objPost["scopeType"] == "team" ) && count($objPost["scope"]) == 1){
			$statesAddress 	= $this->Vehicle->get_vehicle_address($vehicleId);	
			$results = $this->Vehicle->getLastLoadRecord($vehicleId, $driverId);
			if ( !empty($results) ){
				$this->origin_state = $results['DestinationState'];
				$this->origin_city = $results['DestinationCity'];
			} else {
				$this->origin_state = $statesAddress['state'];
				$this->origin_city = $statesAddress['city'];
			}
			
			$newlabel= 'Truck-'.$statesAddress['label'].'-'.$this->origin_city.'-'.$this->origin_state;
			$this->data['vehicleIdRepeat'] = $vehicleId;
		} else if(isset($objPost["scopeType"]) && $objPost["scopeType"] == "all"){
			$newlabel = "All Groups";
		} else {
			$newlabel = "Dispatcher";
		}
		
		if(isset($_COOKIE["_globalDropdown"]) && !empty($_COOKIE["_globalDropdown"])){
			$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
			$dispatcherId = isset($gDropdown['dispId']) ? $gDropdown['dispId'] : '';
		} else {
			$dispatcherId = '';
		}
		
		$jobs = $this->getSingleVehicleLoads($this->userId,$objPost["scope"],$objPost["scopeType"], $dispatcherId, $driverId, $secondDriverId, $startDate, $endDate); //Fetch Loads by vehicle id(s)  // dispatcherId to get loads of dispatcher only
		$this->data['total'] = $this->Job->fetchSavedJobsTotal($this->userId,$objPost["scope"],$objPost["scopeType"], $dispatcherId, $driverId, $secondDriverId, $startDate, $endDate);  //Fetch Loads by vehicle id(s)  // dispatcherId to get loads of dispatcher only
		
		
		$this->data['table_title'] =  $newlabel;
		if($jobs){
			$this->data['assigned_loads'] = $jobs;
		}else{
			$this->data['assigned_loads'] = array();
		}
		

		echo json_encode($this->data);
	}
	
	public function fetchVehicleAddress( $vehicleId = null ) {
		$result = $this->Job->fetchVehicleAddress( $vehicleId);
		if ( !empty($result) ) {
			$this->data = $result;
		}
		
		echo json_encode($this->data);
	}
	
	/**
	 *  Deleting the assigned load
	 */ 

	public function deleteAssignedLoad( $loadId = null, $srcPage = '' ) {
		try{
			$result = $this->Job->deleteAssignedLoad( $loadId );
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted the job ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$loadId.',\'\',\'\',\'\',\'\',0,\'\')">#'.$loadId.'</a>.';
			logActivityEvent($loadId, $this->entity["ticket"], $this->event["delete"], $message, $this->Job, $srcPage);
			if ( $result ) {
				echo json_encode(array('success' =>  true ));
			} else {
				echo json_encode(array('success' =>  false ));
			}
		}catch(Exception $e){
			log_message('error','DELETE_JOB_TICKET'.$e->getMessage());
		}
	}
	
	/**
	 * Generating the invoice 
	 */ 

	public function generateInvoice( $loadId = null , $parameter = '' ,$srcPage = "") {
		$pathGen = str_replace('application/', '', APPPATH);
		$data = array();
		$extraStopsArray = array();
		$docPrimaryId = '';
		$checkBillType = $this->Job->fetchLoadBillType($loadId);
		$checkBillType = (isset($checkBillType) && $checkBillType != '' ) ? $checkBillType : 'broker';
		$data['jobRecord'] = $this->Job->FetchSingleJobForInvoice($loadId, $checkBillType);
		$documents = $this->Billing->fetchDocToBundle($loadId);
		
		if ( isset($data['jobRecord']['Stops']) && $data['jobRecord']['Stops'] > 0 ) 
			$extraStopsArray = $this->Job->getExtraStops( $data['jobRecord']['id'] );
		
		$data['extraStops'] = $extraStopsArray;
		$cookieVariable = get_cookie('setLanguageGlobalVariable');
		$this->lang->load('loads',$cookieVariable);
		
		if ( empty($documents) || count($documents) < 2 ) {
			$errorMessage = $this->lang->line('errRateOrPod');
			echo json_encode(array('showError' => 1,'errorMessage' => $errorMessage));
		} else if( !isset($data['jobRecord']['PaymentAmount']) || $data['jobRecord']['PaymentAmount'] <= 0 || $data['jobRecord']['PaymentAmount'] == '' ) {
			$errorMessage = 'Error !: Invoice amount should be greater than 0.';
			echo json_encode(array('showError' => 1,'errorMessage' => $errorMessage));
		} else {			
			$errorMessage = $this->lang->line('errPlease').' ';
			$showError = 0;
			$billingErr = 0;
			$woRefNo = 0;
			$brokerInfoError = 0;
			
			$errMsg = array();
			if ( !isset($data['jobRecord']['shipper_name']) || $data['jobRecord']['shipper_name'] == '') 
				$errMsg[] = 'shipper name';

			if ( !isset($data['jobRecord']['consignee_name']) || $data['jobRecord']['consignee_name'] == '' )
				$errMsg[] = 'consignee name';

			if ( !isset($data['jobRecord']['PickupAddress']) || $data['jobRecord']['PickupAddress'] == '' || !isset($data['jobRecord']['OriginCity']) || $data['jobRecord']['OriginCity'] == '' || !isset($data['jobRecord']['OriginState']) || $data['jobRecord']['OriginState'] == '' || !isset($data['jobRecord']['OriginZip']) || $data['jobRecord']['OriginZip'] == '' || !isset($data['jobRecord']['OriginCountry']) || $data['jobRecord']['OriginCountry'] == '' )
				$errMsg[] = 'full origin address';

			if ( !isset($data['jobRecord']['DestinationAddress']) || $data['jobRecord']['DestinationAddress'] == '' || !isset($data['jobRecord']['DestinationCity']) || $data['jobRecord']['DestinationCity'] == '' || !isset($data['jobRecord']['DestinationState']) || $data['jobRecord']['DestinationState'] == '' || !isset($data['jobRecord']['DestinationZip']) || $data['jobRecord']['DestinationZip'] == '' || !isset($data['jobRecord']['DestinationCountry']) || $data['jobRecord']['DestinationCountry'] == ''  )
				$errMsg[] = 'full delivery address';

			if ( !empty($errMsg)) {
				$newMsg = implode(', ',$errMsg);
				$billingErr = 1;	
				$errorMessage .= 'enter '.$newMsg.'.';
				$showError = 1;
			}

			if ( $data['jobRecord']['woRefno'] == '' ) {
				if ( $billingErr == 1 ) {
					$errorMessage = str_replace('.','',$errorMessage);
					$errorMessage .= ' '.$this->lang->line('errAnd').' ';
				} else {
					$errorMessage .= ' '.$this->lang->line('errEnter').' ';
				}
				$showError = 1;
				$errorMessage .= $this->lang->line('errWorkOrdNo');
				$woRefNo = 1;
			}
			
			if ( $data['jobRecord']['TruckCompanyName'] == 'undefined' || $data['jobRecord']['TruckCompanyName'] == '' || $data['jobRecord']['postingAddress'] == '' || $data['jobRecord']['city'] == '' || $data['jobRecord']['state'] == '' || $data['jobRecord']['zipcode'] == '' ) {
				$errMsg = array();
				if ( $billingErr == 1  || $woRefNo == 1) {
					$errorMessage = str_replace('.','',$errorMessage);
					$errorMessage .= ' '.$this->lang->line('errAnd').' ';
				}

				if ( $data['jobRecord']['TruckCompanyName'] == '' ) {
					$errorMessage .= ' '.$this->lang->line('errEnter').' ';
					$errorMessage .= $this->lang->line('errGenerateBroker');
					$showError = 1;
				} else {
					if ( $data['jobRecord']['postingAddress'] == '' )
						$errMsg[] = 'address';
					
					if ( $data['jobRecord']['city'] == '' )
						$errMsg[] = 'city';

					if ( $data['jobRecord']['state'] == '' )
						$errMsg[] = 'state';
					
					if ( $data['jobRecord']['zipcode'] == '' )
						$errMsg[] = 'zip code';

					if ( !empty($errMsg)) {
						$newMsg = implode(', ',$errMsg);	
						$errorMessage .= 'enter broker '.$newMsg;
						$showError = 1;
					}
				}
				
				$brokerInfoError = 1;
			}
			
			if ( !empty($data['extraStops']) && count($data['extraStops']) > 0 ) {		// check if extra stop entity and name is given
				$errMsg = array();
				$extEntity = 0;
				$extname = 0;
				$extaddress = 0;
				for( $i = 0; $i < $data['jobRecord']['Stops']; $i++ ) {
					if( !isset($data['extraStops'][$i]['extraStopEntity']) || $data['extraStops'][$i]['extraStopEntity'] == '' ) {
						$extEntity = 1;						
					}	
					
					if( !isset($data['extraStops'][$i]['extraStopName']) || $data['extraStops'][$i]['extraStopName'] == '' ) {
						$extname = 1;				
					}
					
					if( !isset($data['extraStops'][$i]['extraStopAddress']) || $data['extraStops'][$i]['extraStopAddress'] == '' || !isset($data['extraStops'][$i]['extraStopCity']) || $data['extraStops'][$i]['extraStopCity'] == '' || !isset($data['extraStops'][$i]['extraStopState']) || $data['extraStops'][$i]['extraStopState'] == '' || !isset($data['extraStops'][$i]['extraStopZipCode']) || $data['extraStops'][$i]['extraStopZipCode'] == '' || !isset($data['extraStops'][$i]['extraStopCountry']) || $data['extraStops'][$i]['extraStopCountry'] == '' ) {					
						$extaddress = 1;				
					}				
				}
				
				if ( $extEntity == 1 )
					$errMsg[] = 'extra stop entity';

				if ( $extname == 1 )
					$errMsg[] = 'extra stop name';

				if ( $extaddress == 1 )
					$errMsg[] = 'extra stop address';
				
				if ( !empty($errMsg)) {
					$newMsg = implode(' and ',$errMsg);	
					if ( $billingErr == 1  || $woRefNo == 1 || $brokerInfoError == 1 ) {
						$errorMessage = str_replace('.','',$errorMessage);
						$errorMessage .= ' '.$this->lang->line('errAnd').' ';
					}
					$errorMessage .= ' '.$this->lang->line('errEnter').' ';
					$errorMessage .= $newMsg.'.';
					$showError = 1;
				}
			}

			if ( $showError == 0 ) {
				if ( $data['jobRecord']['invoiceNo'] == '' || $data['jobRecord']['invoiceNo'] == null  || $data['jobRecord']['invoiceNo'] == 'undefined' || $data['jobRecord']['invoiceNo'] == 0 ) {
					$invoicedNo = $this->Billing->generateInvoiceNumber($data['jobRecord']['id']);
					$data['jobRecord']['invoiceNo'] = $invoicedNo;
				}

				$invoicedDate = $this->Billing->addGenerateInvoiceDate($data['jobRecord']['id']);
				
				$data['jobRecord']['invoicedDate'] = $invoicedDate;

				$html = $this->load->view('invoice', $data, true); 
				$invoiceResult = $this->Billing->fetchUploadedDocs( $loadId, 'invoice');
				$fileName = "invoice_".time().".pdf";
				$pdfFilePath = $pathGen."assets/uploads/documents/invoice/".$fileName;

				$this->load->library('m_pdf');
				$this->m_pdf->pdf->WriteHTML($html);
				//~ $this->m_pdf->pdf->Output($pdfFilePath, "D");    
				$this->m_pdf->pdf->Output($pdfFilePath, "F"); 
				
				$response['error'] = false;
				//~ $response['data'] = $this->upload->data();

				if (substr(php_uname(), 0, 7) == "Windows"){ 
					//pclose(popen("start /B ". $cmd, "r"));  
					$response['data']['cmd'] = 'Windows';
				} 
				else { 					
					$thumbFolder = 'thumb_invoice';
					$raw_name = explode('.',$fileName);
					$cmd = 'cd '.$pathGen."assets/uploads/documents/invoice/";
					$cmd .= '; convert -thumbnail x600 '.$fileName.'[0] -flatten ../'.$thumbFolder.'/thumb_'.$raw_name[0].'.jpg';
					$response['data']['cmd'] = $cmd;
					exec($cmd . " > /dev/null &");   
				}

				if ( !empty($invoiceResult) && $invoiceResult['doc_name'] != '' ) {
					if(file_exists($pathGen."assets/uploads/documents/invoice/".$invoiceResult['doc_name'])){
						unlink($pathGen."assets/uploads/documents/invoice/".$invoiceResult['doc_name']);
					}
					
					$unlink_name = explode('.',$invoiceResult['doc_name']);
					if(file_exists($pathGen."assets/uploads/documents/thumb_invoice/thumb_".$unlink_name[0].'.jpg')){
						unlink($pathGen."assets/uploads/documents/thumb_invoice/thumb_".$unlink_name[0].'.jpg');
					}
					$docPrimaryId = $invoiceResult['id'];
				} 
				
				$this->Job->insertDocumentEntry($fileName, $loadId, 'invoice', $docPrimaryId);


				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> generated an invoice for job ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$loadId.',\'\',\'\',\'\',\'\',0,\'\')">#'.$loadId.'</a>.';
				logActivityEvent($loadId, $this->entity["ticket"], $this->event["generate_invoice"], $message, $this->Job,$srcPage);

				
				@$this->mergingPdf($loadId,$srcPage);
				if ( isset($parameter) && $parameter == 'readyForInvoice' ) 
					$billingLoads = $this->Billing->getInProgressLoads('invoice');
				else
					$billingLoads = $this->Billing->getInProgressLoads();

				echo json_encode(array('results' => $data, 'showError' => $showError, 'errorMessage' => $errorMessage, 'billingLoads' => $billingLoads));  
			} else {
				echo json_encode(array('showError' => $showError, 'errorMessage' => $errorMessage));  
			}
		}
	}
	
	/**
	 * Merging PDF files
	 */

	public function mergingPdf( $loadId = null, $srcPage = "" ) {
		$pdf = $this->load->library('m_pdf');
		$pdf = new mPDF();
		$pdf->SetImportUse();
		$pathGen = str_replace('application/', '', APPPATH);
		
		$documents = $this->Billing->fetchDocToBundle($loadId);
		$invoiceResult = $this->Billing->fetchUploadedDocs( $loadId, 'bundle');
		$files = array();
		if ( !empty($documents) ) {
			foreach( $documents as $docs ) {
				$files[] = "assets/uploads/documents/{$docs['doc_type']}/{$docs['doc_name']}";
			}			
		}
		
		$temp = $files[1];
		$files[1] = $files[2];
		$files[2] = $temp;
		
		if ( !empty($files) && (count($files) == 3) ) {
			for ($i = 0; $i < count($files); $i++ ) {
				$ext = explode('.',$files[$i]);
				if ( strtolower($ext[1]) == 'png' || strtolower($ext[1]) == 'jpg' || strtolower($ext[1]) == 'jpeg' ) {
					$pdf->AddPage('P','A4');
					$pdf->WriteHTML('<img src="'.$files[$i].'" />');
				} else if ( strtolower($ext[1]) == 'xlsx' ) {
					
				} else {
					$pagecount = $pdf->setSourceFile($pathGen.$files[$i]);	
					for($j = 1; $j <= $pagecount ; $j++)
					{
						$tplidx = $pdf->importPage(($j), '/MediaBox'); // template index.
						$pdf->addPage('P','A4');// orientation can be P|L
						$pdf->useTemplate($tplidx, 0, 0, 0, 0, TRUE);                   
					}
				}
			}
			
			$fileName = "bundle_".time().".pdf";
			$pdfFilePath = $pathGen."assets/uploads/documents/bundle/".$fileName;

			$pdf->Output($pdfFilePath, "F");
			
			if (substr(php_uname(), 0, 7) == "Windows"){ 
					//pclose(popen("start /B ". $cmd, "r"));  
				$response['data']['cmd'] = 'Windows';
			} 
			else { 					
				$thumbFolder = 'thumb_bundle';
				$raw_name = explode('.',$fileName);
				$cmd = 'cd '.$pathGen."assets/uploads/documents/bundle/";
				$cmd .= '; convert -thumbnail x600 '.$fileName.'[0] -flatten ../'.$thumbFolder.'/thumb_'.$raw_name[0].'.jpg';
				$response['data']['cmd'] = $cmd;
				exec($cmd . " > /dev/null &");   
			}
			
			$docPrimaryId = '';
			if ( !empty($invoiceResult) && $invoiceResult['doc_name'] != '' ) {
				if(file_exists($pathGen."assets/uploads/documents/bundle/".$invoiceResult['doc_name'])){
					unlink($pathGen."assets/uploads/documents/bundle/".$invoiceResult['doc_name']);
				}
				
				$unlink_name = explode('.',$invoiceResult['doc_name']);
				if(file_exists($pathGen."assets/uploads/documents/bundle_invoice/thumb_".$unlink_name[0].'.jpg')){
					unlink($pathGen."assets/uploads/documents/bundle_invoice/thumb_".$unlink_name[0].'.jpg');
				}
				$docPrimaryId = $invoiceResult['id'];
			} 
			
			$this->Job->insertDocumentEntry($fileName, $loadId, 'bundle', $docPrimaryId); 
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> created a bundle document for job ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$loadId.',\'\',\'\',\'\',\'\',0,\'\')">#'.$loadId.'</a>.';
			logActivityEvent($loadId, $this->entity["ticket"], $this->event["bundle_document"], $message, $this->Job, $srcPage);
		}
		return true;
	} 
	
	public function mergingPdf1( $loadId = null ) {
		$loadId = 33;
		$pdf = $this->load->library('m_pdf');
		$pdf = new mPDF();
		$pdf->SetImportUse();
		$pathGen = str_replace('application/', '', APPPATH);
		
		$documents = $this->Billing->fetchDocToBundle($loadId);
		$invoiceResult = $this->Billing->fetchUploadedDocs( $loadId, 'bundle');
		$files = array();
		if ( !empty($documents) ) {
			foreach( $documents as $docs ) {
				$files[] = "assets/uploads/documents/{$docs['doc_type']}/{$docs['doc_name']}";
			}			
		}
		
		for ($i = 0; $i < count($files); $i++ ) {
			$ext = explode('.',$files[$i]);
			if ( strtolower($ext[1]) == 'png' || strtolower($ext[1]) == 'jpg' || strtolower($ext[1]) == 'jpeg' ) {
				$pdf->AddPage('L','A4');
				$pdf->WriteHTML('<img src="'.$files[$i].'" />');
			} else if ( strtolower($ext[1]) == 'xlsx' ) {

			} else if ( $ext[1] == 'txt' ) {
				$result = file_get_contents($files[$i]);
				$pdf->AddPage('L','A4');
				$pdf->WriteHTML($result);
			} else {
				$pagecount = $pdf->setSourceFile($pathGen.$files[$i]);	
				for($j = 1; $j <= $pagecount ; $j++)
				{
						$tplidx = $pdf->importPage(($j), '/MediaBox'); // template index.
						$pdf->addPage('L','A4');// orientation can be P|L
						$pdf->useTemplate($tplidx, 0, 0, 0, 0, TRUE);                   
					}
				}
			}
			
			$fileName = "bundle_".time().".pdf";
			$pdfFilePath = $pathGen."assets/uploads/documents/bundle/".$fileName;

			$pdf->Output('tes.pdf', "D"); 


		} 

	/**
	 * Fetching Broker list on add load
	 */

	public function getBrokersList( $loadId = null, $type = '' ) {
		$this->load->model('Shipper');

		$brokersData = $this->BrokersModel->getBrokersList();
		$new = array("id" => "", "TruckCompanyName" => "Select Broker");
		array_unshift($brokersData, $new);

		$this->data['shippersList'] = $this->Shipper->fetchShipperList();
		$new = array("id" => "", "shipperCompanyName" => "Select Shipper");
		array_unshift($this->data['shippersList'], $new);
		if ( !empty($brokersData) ) 
			$this->data['brokersList'] = $brokersData;

		$getBrokerLoad = $this->BrokersModel->getBrokerDetail( $loadId, $type );
		$this->data['brokerLoadDetail'] = $getBrokerLoad;
		echo json_encode($this->data);
	} 
	
	
	public function demo() {

		require_once("application/third_party/fpdf/fpdf.php");//http://www.fpdf.org/
		require_once("application/third_party/fpdi/FPDI.php");
		require_once("application/third_party/fpdi/FPDI_Protection.php");

		$pathGen = str_replace('application/', '', APPPATH.'assets/');

		$files1 = $pathGen.'/compresspdf_demo/Compressed.pdf';
		$files2 = $pathGen.'/compresspdf_demo/PDFReference15_v5.pdf';
		$files 	= array($files1,$files2);
		$pdf 	= new FPDI();

		for ($i = 0; $i < count($files); $i++ ) {
			$pagecount = $pdf->setSourceFile($files[$i]);
			
			for($j = 0; $j < $pagecount ; $j++) {

				$tplidx = $pdf->importPage(($j +1), '/MediaBox'); // template index.
				$pdf->addPage('P','A4');// orientation can be P|L
				$pdf->useTemplate($tplidx, 0, 0, 0, 0, TRUE);                   
			}
		}

		// set the metadata.
		//~ $pdf->SetAuthor($data->user->user_name);
		$pdf->SetCreator('website name!');
		$pdf->SetSubject('PDF subject !');
		//~ $pdf->SetKeywords('website name!'.", keywords! ".$data->user->user_name);
		$output = $pdf->Output('', 'S');
		$name = 'test.pdf';
		$this->output->set_header("Content-Disposition: filename=$name;")->set_content_type('Application/pdf')->set_output($output);
	}


	public function gestScript(){
		
		$pathGen 	= str_replace('application/', '', APPPATH.'assets/');
		$gostscript = $pathGen.'ghostscript/gs-920-linux_x86_64';
		$files1 	= $pathGen.'/compresspdf_demo/Compressed.pdf';
		$files2 	= $pathGen.'/compresspdf_demo/Compressed2222.pdf';	

		try{
			shell_exec("{$gostscript} -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile={$files2} {$files1}"); 
		}catch(Exception $e){
			$e->getMessage();
		}
	}

	public function demoNew( $loadId = null ) {
		$pdf = $this->load->library('m_pdf');
		$pdf = new mPDF();
		$pdf->SetImportUse();
		

		$files = array('/home/csolution/Downloads/semail_(2) (1).pdf','/home/csolution/Downloads/podNew(1).pdf');
		// include('class.pdf2text.php');
		$this->load->library('PDF2text.php');
		$a = new PDF2Text();
$a->setFilename('/home/csolution/Downloads/semail_(2) (1).pdf'); //grab the test file at http://www.newyorklivearts.org/Videographer_RFP.pdf
$out = $a->decodePDF();
pr($out);
echo $a->output();

die;
		/*$resu = $this->pdf2string($files[0]);
		$pdf->SetProtection($resu);

		$new = $pdf->WriteHTML('allo World');
		// $pdf->Output('filename.pdf');
		$files[0] = $new;*/
		for ($i = 0; $i < count($files); $i++ ) {
			$pagecount = $pdf->setSourceFile($files[$i]);	
			
			for($j = 1; $j <= $pagecount ; $j++)
			{
				$tplidx = $pdf->importPage(($j), '/MediaBox'); // template index.
				$pdf->addPage('P','A4');// orientation can be P|L
				$pdf->useTemplate($tplidx, 0, 0, 0, 0, TRUE);                   
			}
		}		

		
		$pdf->Output(); 
	} 

	function pdf2string($sourcefile) { 

		$fp = fopen($sourcefile, 'rb'); 
		$content = fread($fp, filesize($sourcefile)); 
		fclose($fp); 

		$searchstart = 'stream'; 
		$searchend = 'endstream'; 
		$pdfText = ''; 
		$pos = 0; 
		$pos2 = 0; 
		$startpos = 0; 

		while ($pos !== false && $pos2 !== false) { 

			$pos = strpos($content, $searchstart, $startpos); 
			$pos2 = strpos($content, $searchend, $startpos + 1); 

			if ($pos !== false && $pos2 !== false){ 

				if ($content[$pos] == 0x0d && $content[$pos + 1] == 0x0a) { 
					$pos += 2; 
				} else if ($content[$pos] == 0x0a) { 
					$pos++; 
				} 

				if ($content[$pos2 - 2] == 0x0d && $content[$pos2 - 1] == 0x0a) { 
					$pos2 -= 2; 
				} else if ($content[$pos2 - 1] == 0x0a) { 
					$pos2--; 
				} 

				$textsection = substr( 
					$content, 
					$pos + strlen($searchstart) + 2, 
					$pos2 - $pos - strlen($searchstart) - 1 
					); 
				$data = @gzuncompress($textsection); 
				$pdfText .= $this->pdfExtractText($data); 
				$startpos = $pos2 + strlen($searchend) - 1; 

			} 
		} 

		return preg_replace('/(\s)+/', ' ', $pdfText); 

	} 

	function pdfExtractText($psData){ 

		if (!is_string($psData)) { 
			return ''; 
		} 

		$text = ''; 

    // Handle brackets in the text stream that could be mistaken for 
    // the end of a text field. I'm sure you can do this as part of the 
    // regular expression, but my skills aren't good enough yet. 
		$psData = str_replace('\)', '##ENDBRACKET##', $psData); 
		$psData = str_replace('\]', '##ENDSBRACKET##', $psData); 

		preg_match_all( 
			'/(T[wdcm*])[\s]*(\[([^\]]*)\]|\(([^\)]*)\))[\s]*Tj/si', 
			$psData, 
			$matches 
			); 

		for ($i = 0; $i < sizeof($matches[0]); $i++) { 
			if ($matches[3][$i] != '') { 
            // Run another match over the contents. 
				preg_match_all('/\(([^)]*)\)/si', $matches[3][$i], $subMatches); 
				foreach ($subMatches[1] as $subMatch) { 
					$text .= $subMatch; 
				} 
			} else if ($matches[4][$i] != '') { 
				$text .= ($matches[1][$i] == 'Tc' ? ' ' : '') . $matches[4][$i]; 
			} 
		} 

    	// Translate special characters and put back brackets. 
		$trans = array( 
			'...'                => '…', 
			'\205'                => '…', 
			'\221'                => chr(145), 
			'\222'                => chr(146), 
			'\223'                => chr(147), 
			'\224'                => chr(148), 
			'\226'                => '-', 
			'\267'                => '•', 
			'\('                => '(', 
			'\['                => '[', 
			'##ENDBRACKET##'    => ')', 
			'##ENDSBRACKET##'    => ']', 
			chr(133)            => '-', 
			chr(141)            => chr(147), 
			chr(142)            => chr(148), 
			chr(143)            => chr(145), 
			chr(144)            => chr(146), 
			); 

		$text = strtr($psData, $trans);
		return $text;
	}

	public function getRecords(){

		$params = json_decode(file_get_contents('php://input'),true);
		$total 	= 0;
		$jobs 	= array();

		if(empty($params["export"])){
			if($params["pageNo"] < 1){
				$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] + 1);	
			}else{
				$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] );	
			}
		}

		$params["export"] = (!empty($params["export"]))?$params["export"]:0;//For export request

		if((isset($params["sortColumn"]) && empty($params["sortColumn"])) || !isset($params["sortColumn"])){ $params["sortColumn"] = "DeliveryDate"; }
		if((isset($params["sortType"]) && empty($params["sortType"])) || !isset($params["sortType"])){ $params["sortType"] = "DESC"; }
		if(!isset($params["startDate"])){ $params["startDate"] = ''; }
		if(!isset($params["endDate"])){ $params["endDate"] = ''; }
		if(isset($_COOKIE["_globalDropdown"]) && !empty($_COOKIE["_globalDropdown"])){
			$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
			if (isset($gDropdown["label"]) && $gDropdown["label"] == "_idispatcher") {  //A Dispatcher's All drivers
				$jobs = $this->getSingleVehicleLoads($this->userId,array(),"dispatcher", $gDropdown['dispId'],false,false,$params["startDate"],$params["endDate"],$params); //Fetch Loads by vehicle id(s)
				$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"dispatcher",$gDropdown['dispId'],false,false,$params["startDate"],$params["endDate"],$params); 
			} else if ( !isset($gDropdown["label"]) || empty(trim($gDropdown["label"])) ) { //All Groups
				$jobs = $this->getSingleVehicleLoads($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); //Fetch Loads by vehicle id(s)
				$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
			} else {	//Single Driver
				$gVehicleId = $gDropdown["vid"];
				$statesAddress = $this->Vehicle->get_vehicles_address($this->userId,$gVehicleId);

				$vehicleIdRepeat = $statesAddress[0]['id'];	
				$results = $this->Vehicle->getLastLoadRecord($statesAddress[0]['id'], $statesAddress[0]['driver_id']);

				if($gDropdown["label"] == "_team") {
					$jobs = $this->getSingleVehicleLoads($this->userId, $vehicleIdRepeat,"team", $gDropdown['dispId'], $gDropdown['id'],$gDropdown['team_driver_id'],$params["startDate"],$params["endDate"],$params);	
					$total = $this->Job->fetchSavedJobsTotal($this->userId, $vehicleIdRepeat,"team", $gDropdown['dispId'], $gDropdown['id'],$gDropdown['team_driver_id'],$params["startDate"],$params["endDate"],$params);	
				} else{
					$jobs = $this->getSingleVehicleLoads($this->userId, $vehicleIdRepeat, "driver", $gDropdown['dispId'], $gDropdown['id'],$gDropdown['team_driver_id'],$params["startDate"],$params["endDate"],$params);	
					$total = $this->Job->fetchSavedJobsTotal($this->userId, $vehicleIdRepeat,"driver", $gDropdown['dispId'], $gDropdown['id'],$gDropdown['team_driver_id'],$params["startDate"],$params["endDate"],$params);	
				}
			}
		} else {
			if($this->userRoleId == _DISPATCHER || $this->userRoleId == 4 ){
				$jobs  = $this->getSingleVehicleLoads($this->userId,array(),"dispatcher",$this->userId,false,false,$params["startDate"],$params["endDate"],$params); //Fetch Loads by vehicle id(s)
				$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"dispatcher",$this->userId,false,false,$params["startDate"],$params["endDate"],$params); 
			} else {
				$jobs  = $this->getSingleVehicleLoads($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
				$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
			}
		}

		if(!empty($params["export"])){
			
			$content = '';
			$keys 	= [['DATE','CUSTOMER NAME','DRIVERS','INVOICE','CHARGES','PROFIT','%PROFIT','MILES','DEAD MILES','RATE/MILE','DATE P/U','PICK UP','DATE DE','DELIVERY','LOLAD ID','STATUS']];
			
			//Created a common function in my_controller for all load data for excell file
			$data = $this->buildExportLoadData($jobs);
			
			$data = array_merge($keys,$newArray);
			echo json_encode(array('fileName'=>$this->createExcell('myloads',$data)));
			die();
		}

		echo json_encode(array("data"=>$jobs,"total"=>$total));
	}
}