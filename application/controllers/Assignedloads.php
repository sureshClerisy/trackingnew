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
		
		$userId 	= false;
		$parentId 	= false;
		$tempUserId = $this->userID;
		$childIds  	= array();				// dispatchers child list

		if($this->userRoleId == _DISPATCHER ){
			$userId 	= $this->userID;
			$parentId 	= $this->userID;
			$childIds 	= $this->User->fetchDispatchersChilds($userId);
		} else if ( $this->userRoleId == 4 ) {
			$parentIdCheck = $this->session->userdata('dispCord_parent');			
			if( isset($parentIdCheck) && $parentIdCheck != 0 ) {
				$userId = $parentIdCheck;
				$parentId = $parentIdCheck;
				$tempUserId = $parentIdCheck;
			}
		}
		
		$allVehicles 	= $this->Driver->getDriversListNew($userId);
		$dispatcherList = $this->Driver->getDispatcherList($parentId); 	//for add all drivers under every dispatcher
		$teamList 		= $this->Driver->getDriversListAsTeamNew($userId);

		if ( !empty($childIds)) {
			$childVehicles 		= array();

			foreach($childIds as $child ) {
				$childVehicles 	= $this->Driver->getDriversListNew($child['id']);
				$allVehicles 	= array_merge($allVehicles,$childVehicles);	
				
				$childVehicles 	= $this->Driver->getDriversListAsTeamNew($child['id']);
				$teamList 		= array_merge($teamList,$childVehicles);
				$childs 		= $this->Driver->getDispatcherList($child['id']);
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
		}
		$new = array("id"=>"","driverName"=>"All Groups","username"=>"","dispId"=>"","vid"=>"","dispId" => "");
		array_unshift($vDriversList, $new);
			

		$newDestlabel 	= array();
		$gVehicleId 	= false;
		$startDate 		= $endDate = '' ;
		if(isset($_COOKIE["_gDateRange"]) && !empty($_COOKIE["_gDateRange"])){
			$gDateRange = json_decode($_COOKIE["_gDateRange"],true);
			$startDate 	= $gDateRange["startDate"]; $endDate = $gDateRange["endDate"];
		}

		if(isset($_COOKIE["_globalDropdown"]) && !empty($_COOKIE["_globalDropdown"])){
			$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
			
			if (isset($gDropdown["label"]) && $gDropdown["label"] == "_idispatcher") {  //A Dispatcher's All drivers
				$jobs = $this->getSingleVehicleLoads(array(),"dispatcher", $gDropdown['dispId'],false,false,$startDate,$endDate); //Fetch Loads by vehicle id(s)
				$this->data["total"] = $this->Job->fetchSavedJobsTotal(array(),"dispatcher",$gDropdown['dispId'],false,false,$startDate,$endDate); 
				$this->data['table_title'] =  "Dispatcher : ".$gDropdown["username"];
				$this->data['vehicleIdRepeat'] = '';
			} else if ( !isset($gDropdown["label"]) || empty(trim($gDropdown["label"])) ) { //All Groups
				$gVehicleId = false;
				$this->data['table_title'] = "All Groups";
				$jobs = $this->getSingleVehicleLoads(array(),"all",false,false,false,$startDate,$endDate); 
				$this->data["total"] = $this->Job->fetchSavedJobsTotal(array(),"all",false,false,false,$startDate,$endDate); 
				$this->data['vehicleIdRepeat'] = '';
			} else {																//Single Driver
				$gVehicleId = $gDropdown["vid"];
				$statesAddress = $this->Vehicle->get_vehicles_address($tempUserId,$gVehicleId);
				$vehicleIdRepeat = (isset($statesAddress[0]['id']) && !empty($statesAddress[0]['id']) ) ? $statesAddress[0]['id'] : $gVehicleId;	
				$results = $this->Vehicle->getLastLoadRecord($gVehicleId, $gDropdown['id']);
				if ( !empty($results) ){
					$this->origin_state = $results['DestinationState'];
					$this->origin_city 	= $results['DestinationCity'];
				} else {
					$this->origin_state = $statesAddress['0']['state'];
					$this->origin_city 	= $statesAddress['0']['city'];
				}
				$this->data['table_title'] = 'Truck-'.@$statesAddress[0]['label'].' '.$this->origin_city.'-'.$this->origin_state;
				$this->data['vehicleIdRepeat'] = $vehicleIdRepeat;
				if($gDropdown["label"] == "_team") {

					$jobs = $this->getSingleVehicleLoads($vehicleIdRepeat,"team", $gDropdown['dispId'], $gDropdown['id'], $gDropdown['team_driver_id'], $startDate,$endDate);	
					$this->data["total"] = $this->Job->fetchSavedJobsTotal($vehicleIdRepeat,"team",$gDropdown['dispId'],$gDropdown['id'], $gDropdown['team_driver_id'], $startDate,$endDate); 

				} else{
					
					$jobs = $this->getSingleVehicleLoads( $vehicleIdRepeat, "driver", $gDropdown['dispId'], $gDropdown['id'], $gDropdown['team_driver_id'], $startDate,$endDate);	
					$this->data["total"] = $this->Job->fetchSavedJobsTotal($vehicleIdRepeat,"driver",$gDropdown['dispId'],$gDropdown['id'], $gDropdown['team_driver_id'], $startDate,$endDate); 
				}
			}
		} else {
			if($this->userRoleId == _DISPATCHER || $this->userRoleId == 4 ){
				$jobs = $this->getSingleVehicleLoads(array(),"dispatcher",$userId,false,false,$startDate,$endDate); //Fetch Loads by vehicle id(s)
				$this->data["total"] = $this->Job->fetchSavedJobsTotal(array(),"dispatcher",$userId,false,false,$startDate,$endDate); 
				$this->data['table_title'] =  "Dispatcher : ".$this->session->userdata('loggedUser_username');
				$this->data['vehicleIdRepeat'] = '';
			} else {
				$this->data['table_title'] = "All Groups";
				$jobs = $this->getSingleVehicleLoads(array(),"all"); //Fetch Loads by vehicle id(s)
				$this->data["total"] = $this->Job->fetchSavedJobsTotal(array(),"all",false,false,false,$startDate,$endDate); 
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
	
	public function skipAcl_getChangeDriverLoads( $vehicleId = null, $driverId = null, $secondDriverId = null ) {
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
		
		$jobs = $this->getSingleVehicleLoads($objPost["scope"],$objPost["scopeType"], $dispatcherId, $driverId, $secondDriverId, $startDate, $endDate); //Fetch Loads by vehicle id(s)  // dispatcherId to get loads of dispatcher only
		$this->data['total'] = $this->Job->fetchSavedJobsTotal($objPost["scope"],$objPost["scopeType"], $dispatcherId, $driverId, $secondDriverId, $startDate, $endDate);  //Fetch Loads by vehicle id(s)  // dispatcherId to get loads of dispatcher only
		
		
		$this->data['table_title'] =  $newlabel;
		if($jobs){
			$this->data['assigned_loads'] = $jobs;
		}else{
			$this->data['assigned_loads'] = array();
		}
		

		echo json_encode($this->data);
	}
	
	public function skipAcl_fetchVehicleAddress( $vehicleId = null ) {
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

	private function mergingPdf( $loadId = null, $srcPage = "" ) {
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
	
	/**
	 * Fetching Broker list on add load
	 */

	public function skipAcl_getBrokersList( $loadId = null, $type = '' ) {
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

	public function skipAcl_getRecords(){

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
				$jobs = $this->getSingleVehicleLoads(array(),"dispatcher", $gDropdown['dispId'],false,false,$params["startDate"],$params["endDate"],$params); //Fetch Loads by vehicle id(s)
				$total = $this->Job->fetchSavedJobsTotal(array(),"dispatcher",$gDropdown['dispId'],false,false,$params["startDate"],$params["endDate"],$params); 
			} else if ( !isset($gDropdown["label"]) || empty(trim($gDropdown["label"])) ) { //All Groups
				$jobs = $this->getSingleVehicleLoads(array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); //Fetch Loads by vehicle id(s)
				$total = $this->Job->fetchSavedJobsTotal(array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
			} else {	//Single Driver 
				$gVehicleId = $gDropdown["vid"];
				$statesAddress = $this->Vehicle->get_vehicles_address($this->userId,$gVehicleId);

				$vehicleIdRepeat = $statesAddress[0]['id'];	
				$results = $this->Vehicle->getLastLoadRecord($statesAddress[0]['id'], $statesAddress[0]['driver_id']);

				if($gDropdown["label"] == "_team") {
					$jobs = $this->getSingleVehicleLoads($vehicleIdRepeat,"team", $gDropdown['dispId'], $gDropdown['id'],$gDropdown['team_driver_id'],$params["startDate"],$params["endDate"],$params);	
					$total = $this->Job->fetchSavedJobsTotal($vehicleIdRepeat,"team", $gDropdown['dispId'], $gDropdown['id'],$gDropdown['team_driver_id'],$params["startDate"],$params["endDate"],$params);	
				} else{
					$jobs = $this->getSingleVehicleLoads($vehicleIdRepeat, "driver", $gDropdown['dispId'], $gDropdown['id'],$gDropdown['team_driver_id'],$params["startDate"],$params["endDate"],$params);	
					$total = $this->Job->fetchSavedJobsTotal($vehicleIdRepeat,"driver", $gDropdown['dispId'], $gDropdown['id'],$gDropdown['team_driver_id'],$params["startDate"],$params["endDate"],$params);	
				}
			}
		} else {
			if($this->userRoleId == _DISPATCHER || $this->userRoleId == 4 ){
				$jobs  = $this->getSingleVehicleLoads(array(),"dispatcher",$this->userId,false,false,$params["startDate"],$params["endDate"],$params); //Fetch Loads by vehicle id(s)
				$total = $this->Job->fetchSavedJobsTotal(array(),"dispatcher",$this->userId,false,false,$params["startDate"],$params["endDate"],$params); 
			} else {
				$jobs  = $this->getSingleVehicleLoads(array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
				$total = $this->Job->fetchSavedJobsTotal(array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
			}
		}

		if(!empty($params["export"])){
			$data = $this->buildExportLoadData($jobs,'myloads');
		}

		echo json_encode(array("data"=>$jobs,"total"=>$total));
	}
}