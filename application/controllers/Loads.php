<?php

/**
* Loads Api Controller
*  
*/

class Loads extends Admin_Controller{

	public $rows;
	private $userId;
	private $orign_city;
	private $orign_state;
	private $saveCalPayment;
	public $finalArray;
	public $data;
	
	function __construct(){

		parent::__construct();
		
		$this->userRoleId = $this->session->role;
		$this->userId 		= $this->session->loggedUser_id;
		
		$this->load->model(array('Vehicle','Driver','Job','BrokersModel','Billing'));
		$this->load->helper('truckstop');
		
		$this->finalArray = array();
		$this->data = array();
	}
	
	public function index($urlArgs = '') {
		//List of all dispatchers with their drivers
		$this->load->model("Driver");
		$userId = false;
		$parentId = false;
		$tempUserId = $this->userId;
		if($this->userRoleId == _DISPATCHER ){
			$userId = $this->userId;
			$parentId =  $this->userId;
		} else if ( $this->userRoleId == 4 ) {
			$parentIdCheck = $this->session->userdata('loggedUser_parentId');
			if( isset($parentIdCheck) && $parentIdCheck != 0 ) {
				$userId = $parentIdCheck;
				$parentId = $parentIdCheck;
				$tempUserId = $parentIdCheck;
			}
		}
		
		$newDestlabel = array();
		$startDate = date('Y-m-d', strtotime('-29 days'));
		$endDate = date("Y-m-d"); 

		if(isset($_COOKIE["_gDateRange"]) && !empty($_COOKIE["_gDateRange"])){
			$gDateRange = json_decode($_COOKIE["_gDateRange"],true);
			$startDate = $gDateRange["startDate"]; $endDate = $gDateRange["endDate"];
		}

		

		if(!empty($urlArgs) && isset($_REQUEST["userType"])){
			$filterArgs = array("itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC","status"=>"");
			$filterArgs["status"] = isset($_REQUEST["filterType"]) ? $_REQUEST["filterType"] : "";
			switch ($_REQUEST["userType"]) {
				case ''	  : 
				case 'all': 
							$gVehicleId = false;
							$this->data['table_title'] = "All Groups";
							$jobs = $this->getSingleVehicleLoads($this->userId,array(),"all",false,false,$startDate,$endDate,$filterArgs); 
							$this->data["total"] = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,$startDate,$endDate,$filterArgs); 
							$this->data['vehicleIdRepeat'] = '';
				break;
				case 'dispatcher': 
							$jobs = $this->getSingleVehicleLoads($this->userId,array(),"dispatcher", $_REQUEST["userToken"],false,$startDate,$endDate,$filterArgs); //Fetch Loads by vehicle id(s)
							$this->data["total"] = $this->Job->fetchSavedJobsTotal($this->userId,array(),"dispatcher",$_REQUEST["userToken"],false,$startDate,$endDate,$filterArgs); 
							$this->data['table_title'] =  "Dispatcher : ".$urlArgs;
							$this->data['vehicleIdRepeat'] = '';

				break;
				case 'team': 
				case 'driver': 
							$driverInfo = $this->Driver->getInfoByDriverId($_REQUEST["userToken"]);
							//pr($driverInfo);die;
							if(isset($driverInfo[0])){ $driverInfo = $driverInfo[0]; }
							$gVehicleId = isset($driverInfo["vehicleId"])  ? $driverInfo["vehicleId"] : '';
							$dispatcherId = isset($driverInfo["dispatcherId"])  ? $driverInfo["dispatcherId"] : '';
							$statesAddress = $this->Vehicle->get_vehicles_address($tempUserId,$gVehicleId);
							$vehicleIdRepeat = (isset($statesAddress[0]['id']) && !empty($statesAddress[0]['id']) ) ? $statesAddress[0]['id'] : $gVehicleId;	
							$results = $this->Vehicle->getLastLoadRecord($gVehicleId, $_REQUEST["userToken"]);
							if ( !empty($results) ){
								$this->origin_state = $results['DestinationState'];
								$this->origin_city = $results['DestinationCity'];
							} else {
								$this->origin_state = $statesAddress['0']['state'];
								$this->origin_city = $statesAddress['0']['city'];
							}
							$this->data['table_title'] = 'Truck-'.@$statesAddress[0]['label'].' '.$this->origin_city.'-'.$this->origin_state;
							$this->data['vehicleIdRepeat'] = $vehicleIdRepeat;
							if($_REQUEST["userType"] == "team") {
								$jobs = $this->getSingleVehicleLoads($tempUserId, $vehicleIdRepeat,"team", $dispatcherId, $_REQUEST["userToken"] ,$startDate,$endDate, $filterArgs);	
								$this->data["total"] = $this->Job->fetchSavedJobsTotal($tempUserId,$vehicleIdRepeat,"team",$dispatcherId,$_REQUEST["userToken"],$startDate,$endDate, $filterArgs); 

							} else{
								//pr($gDropdown);die;
								$jobs = $this->getSingleVehicleLoads($tempUserId, $vehicleIdRepeat, "driver", $dispatcherId, $_REQUEST["userToken"],$startDate,$endDate,$filterArgs);	
								$this->data["total"] = $this->Job->fetchSavedJobsTotal($this->userId,$vehicleIdRepeat,"driver",$dispatcherId,$_REQUEST["userToken"],$startDate,$endDate, $filterArgs); 
							}
				break;
			}
		}


		$this->data['loadSource'] = 'truckstop.com';

		if ( $jobs ) {
			$this->data['assigned_loads'] = $jobs;
		} else {
			$this->data['assigned_loads'] = array();
			$this->data['vehicleIdRepeat'] = '';
		}
		$this->data['filterArgs'] = $_REQUEST;
		$this->data['filterArgs']["firstParam"] = $urlArgs;
		echo json_encode($this->data);
	}
	
	public function getChangeDriverLoads( $vehicleId = null, $driverId = null ) {
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
		
		$jobs = $this->getSingleVehicleLoads($this->userId,$objPost["scope"],$objPost["scopeType"], $dispatcherId, $driverId, $startDate, $endDate); //Fetch Loads by vehicle id(s)  // dispatcherId to get loads of dispatcher only
		$this->data['total'] = $this->Job->fetchSavedJobsTotal($this->userId,$objPost["scope"],$objPost["scopeType"], $dispatcherId, $driverId, $startDate, $endDate);  //Fetch Loads by vehicle id(s)  // dispatcherId to get loads of dispatcher only
		//~ $jobs = $this->getSingleVehicleLoads($this->userId,$objPost["scope"],$objPost["scopeType"]); //Fetch Loads by vehicle id(s)  // dispatcherId to get loads of dispatcher 
		
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
	 
	public function deleteAssignedLoad( $loadId = null ) {
		$result = $this->Job->deleteAssignedLoad( $loadId );
		if ( $result ) {
			echo json_encode(array('success' =>  true ));
		} else {
			echo json_encode(array('success' =>  false ));
		}
	}
	
	/**
	 * Generating the invoice 
	 */ 
	 
	public function generateInvoice( $loadId = null , $parameter = '' ) {
		$pathGen = str_replace('application/', '', APPPATH);
		$data = array();
		$extraStopsArray = array();
		$docPrimaryId = '';
		$data['jobRecord'] = $this->Job->FetchSingleJobForInvoice($loadId);
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
				
			//~ pr($data); 
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
				
				$this->mergingPdf($loadId);
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
	 
	public function mergingPdf( $loadId = null ) {
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
	 
	public function getBrokersList( $loadId = null ) {
		$brokersData = $this->BrokersModel->getBrokersList();
		if ( !empty($brokersData) ) 
			$this->data['brokersList'] = $brokersData;
			
		$getBrokerLoad = $this->BrokersModel->getBrokerDetail( $loadId );
		$this->data['brokerLoadDetail'] = $getBrokerLoad;
		echo json_encode($this->data);
	} 
	
	
	public function demo() {
		require_once("application/third_party/fpdf/fpdf.php");//http://www.fpdf.org/
		require_once("application/third_party/fpdi/FPDI.php");
		require_once("application/third_party/fpdi/FPDI_Protection.php");
		
		$files = array('/home/csolution/Downloads/rateSheetNew.pdf','/home/csolution/Downloads/podNew(1).pdf');
		$pdf = new FPDI();
		
		for ($i = 0; $i < count($files); $i++ )
		{
			$pagecount = $pdf->setSourceFile($files[$i]);
			for($j = 0; $j < $pagecount ; $j++)
			{
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

	public function getRecords(){
		$params = json_decode(file_get_contents('php://input'),true);
		$total = 0;
		$jobs = array();
		if($params["pageNo"] > 1){
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] + 1);	
		}else{
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] );	
		}

		if((isset($params["sortColumn"]) && empty($params["sortColumn"])) || !isset($params["sortColumn"])){ $params["sortColumn"] = "PickupDate"; }
		if((isset($params["sortType"]) && empty($params["sortType"])) || !isset($params["sortType"])){ $params["sortType"] = "ASC"; }
		if(!isset($params["startDate"])){ $params["startDate"] = ''; }
		if(!isset($params["endDate"])){ $params["endDate"] = ''; }
		if(isset($params["filterArgs"]["filterType"])){
			$params["status"] = $params["filterArgs"]["filterType"];
		}else{
			$params["status"] = "";
		}

		//if(isset($_COOKIE["_globalDropdown"]) && !empty($_COOKIE["_globalDropdown"])){
			//$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
			if (isset($params["filterArgs"]["userType"]) && $params["filterArgs"]["userType"] == "dispatcher") {  //A Dispatcher's All drivers
				$dispId = isset($params["filterArgs"]['userToken']) ? $params["filterArgs"]['userToken'] : false;
				$jobs = $this->getSingleVehicleLoads($this->userId,array(),"dispatcher", $dispId,false,$params["startDate"],$params["endDate"],$params); //Fetch Loads by vehicle id(s)
				$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"dispatcher",$dispId,false,$params["startDate"],$params["endDate"],$params); 
			}else if (isset($params["filterArgs"]["userType"]) && ($params["filterArgs"]["userType"] == "all" || $params["filterArgs"]["userType"] == "" )){  //A Dispatcher's All drivers
				$jobs = $this->getSingleVehicleLoads($this->userId,array(),"all",false,false,$params["startDate"],$params["endDate"],$params); 
				$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,$params["startDate"],$params["endDate"],$params); 
			}else if (isset($params["filterArgs"]["userType"]) && ($params["filterArgs"]["userType"] == "driver" || $params["filterArgs"]["userType"] == "team" )){ 
				$driverInfo = $this->Driver->getInfoByDriverId($params["filterArgs"]["userToken"]);
				//pr($driverInfo);die;
				if(isset($driverInfo[0])){ $driverInfo = $driverInfo[0]; }
				$gVehicleId = isset($driverInfo["vehicleId"])  ? $driverInfo["vehicleId"] : '';
				$dispatcherId = isset($driverInfo["dispatcherId"])  ? $driverInfo["dispatcherId"] : '';
				$statesAddress = $this->Vehicle->get_vehicles_address($this->userId,$gVehicleId);
				$driverId = isset($params["filterArgs"]["userToken"]) ? $params["filterArgs"]["userToken"] : false ;
				$vehicleIdRepeat = $statesAddress[0]['id'];	
				$results = $this->Vehicle->getLastLoadRecord($statesAddress[0]['id'], $statesAddress[0]['driver_id']);

				if($params["filterArgs"]["userType"] == "team") {
					$jobs = $this->getSingleVehicleLoads($this->userId, $vehicleIdRepeat,"team", $dispatcherId, $driverId,$params["startDate"],$params["endDate"],$params);	
					$total = $this->Job->fetchSavedJobsTotal($this->userId, $vehicleIdRepeat,"team", $dispatcherId, $driverId,$params["startDate"],$params["endDate"],$params);	
				} else{
					$jobs = $this->getSingleVehicleLoads($this->userId, $vehicleIdRepeat, "driver", $dispatcherId, $driverId,$params["startDate"],$params["endDate"],$params);	
					$total = $this->Job->fetchSavedJobsTotal($this->userId, $vehicleIdRepeat,"driver", $dispatcherId, $driverId,$params["startDate"],$params["endDate"],$params);	
				}
			}else{
				$jobs = $this->getSingleVehicleLoads($this->userId,array(),"all",false,false,$params["startDate"],$params["endDate"],$params); 
				$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,$params["startDate"],$params["endDate"],$params); 
			}
		
		if(!$jobs){$jobs = array();}

		echo json_encode(array("data"=>$jobs,"total"=>$total));
	}
}



