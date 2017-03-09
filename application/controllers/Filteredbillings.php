<?php

/**
* Filteredbillings Api Controller
*  
*/

class Filteredbillings extends Admin_Controller{
	
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
		$this->load->model('Driver');
		$this->load->helper('truckstop');
		$this->load->helper('download');
		
        if ( $this->config->item('triumph_environment') == 'production' ) {
			$this->triuser  			= $this->config->item('triumph_user');
			$this->password				= $this->config->item('triumph_pass');
			$this->apikey   			= $this->config->item('triumph_apik');
			$this->triumphUrl  	 		= $this->config->item('triumph_url');
			$this->triumphUrlRequest   	= $this->config->item('triumph_url_request');
		} else {
			$this->triuser  			= $this->config->item('triumph_user_test');
			$this->password				= $this->config->item('triumph_pass_test');
			$this->apikey   			= $this->config->item('triumph_apik_test');
			$this->triumphUrl  	 		= $this->config->item('triumph_url_test');
			$this->triumphUrlRequest   	= $this->config->item('triumph_url_request_test');
		}
		
				
    }
	
	public function index( $parameter = '' ) {
		$data = array();
		$filters = array("itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC");
		$filters["filterType"] = isset($_REQUEST["filterType"]) ? $_REQUEST["filterType"] : 'all';
		$filters["userType"] = isset($_REQUEST["userType"]) ? $_REQUEST["userType"] : 'all';
		$filters["userId"] = isset($_REQUEST["userToken"]) ? $_REQUEST["userToken"] : false;

		if(isset($_COOKIE["_gDateRange"]) && !empty($_COOKIE["_gDateRange"])){
			$gDateRange = json_decode($_COOKIE["_gDateRange"],true);
			$filters["startDate"] = $gDateRange["startDate"]; $filters["endDate"] = $gDateRange["endDate"];
		}

		if($filters["userType"] == "team" || $filters["userType"] == "driver"){

			$driverInfo = $this->Driver->getInfoByDriverId($filters["userId"]);
			if(isset($driverInfo[0])){ $driverInfo = $driverInfo[0]; }
			$filters["vehicleId"] = isset($driverInfo["vehicleId"])  ? $driverInfo["vehicleId"] : '';
			$filters["dispatcherId"] = isset($driverInfo["dispatcherId"])  ? $driverInfo["dispatcherId"] : '';
		}


		$jobs = $this->Billing->getFilteredLoads( $filters);
		$data['total'] = $this->Billing->getFilteredLoads( $filters,true);
		
		$data['loads'] = $jobs;
		$data['billType'] = 'billing';
		$data['filterArgs'] = $_REQUEST;
		$data['filterArgs']["firstParam"] = $parameter;

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
	 * creating schedule and send for payment
	 */ 
	 
	public function creatingSchedule() {
	
		$objPost = json_decode(file_get_contents('php://input'),true);
		//~ $objPost['selectedIds'] = array(10554,10572,10575,10591,10594,10595,10596,10596,10601,10602,10606,10608,10611,10613,10621,10622,10623,10630,10631,10634,10636,10640,10642,10643,10644,10646,10648,10650,10652,10654,10655,10661,10674,10676,10677,10678,10680,10682,10683,10684,10685,10687,10688,10689,10696,10699,10700,10702,10703,10717);	
		//~ pr($objPost); die; 
		$genDocs = array();
		$createDocument = array();
		$createErrorFile = array();
		$inputIdsForFinal = array();
		$saveIds = array();
		$resultReturnedArray = $this->createMultipleInputs($objPost['selectedIds']);
		$resultReturnedIds = $resultReturnedArray[0];
		$this->triumphToken = $resultReturnedArray[1];
		
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
			$genDocs[$i]['inputId'] = urlencode($resultReturnedIds[$i]);
			$genDocs[$i]['filename'] = urlencode($this->Billing->getBundleFileName( $objPost['selectedIds'][$i] ));
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
			$saveIds[$i] = "'".$genDocs[$i]['inputId']."+".$objPost['selectedIds'][$i];
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
				$errorMessage = 'Error !: Some error occured while submiting documents to triumph';
			}
		}
	
		$data = $this->fetchingSentPaymentsInfo();
		echo json_encode(array('success' => true,'loadsInfo' => $data, 'errorMessage' => $errorMessage));
	}
	
	/**
	 * Getting api method values
	 */
	
	public function getApiMethodValue( $url = '', $type = '', $token = '' ) {
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
		$b64Doc = urlencode(base64_encode(file_get_contents($_PATH)));
		return $b64Doc;
	}	
	
	/**
	 * Creating Document Triumph Api method 
	 */
	 
	public function createDocument( $loadDetail = array(), $token = '' ) {
		$url = 'v1Submit/CreateDocument';
		$postData = "inputId={$loadDetail['inputId']}&filename={$loadDetail['filename']}&fileData={$loadDetail['fileData']}&{$loadDetail['docType']}"; 
			
		$result = $this->commonTriumphCurlRequest( $url, $postData, $token);
		if ( $result['Error'] == 1 && ( strpos($result['ErrorMessage'], 'No cookie named SessionToken was passed with the request or it was not in a valid format.') !== false) ) {
			$token = $this->get_sessionToken();
			$this->triumphToken = $token->SessionToken;
			$newResult = $this->commonTriumphCurlRequest( $url, $postData, $this->triumphToken);
			if( !empty($newResult) && $newResult['Error'] == '' ) {
				return array($newResult['documentId'], $this->triumphToken);
			}
		} else {
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
		$url = 'v1Submit/FinalizePendingInputArray';
		$result = $this->commonTriumphCurlRequest( $url, $postData, $token);
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
	 * Create  Multiple Inputs method
	 */
	 
	public function createMultipleInputs( $loadIds = array() ) {
		$i = 0;
		$postData = '';
		
		foreach( $loadIds as $loadId ) {
			$jobRecord = $this->Job->FetchSingleJobCreateInput($loadId);
			$pickupdate = date('Y-m-d',strtotime($jobRecord['PickDate']));
			$deliveryDate = ( $jobRecord['DeliveryDate'] != '' &&  $jobRecord['DeliveryDate'] != '0000-00-00' ) ?  $jobRecord['DeliveryDate'] : '';
			$postData .= "[$i].referenceKey=''&[$i].invoiceNumber={$jobRecord['invoiceNo']}&[$i].invoiceDate={$jobRecord['invoicedDate']}&[$i].referenceNumber={$jobRecord['woRefno']}&[$i].grossAmount={$jobRecord['PaymentAmount']}&[$i].isMiscInvoice=true&[$i].customerName={$jobRecord['TruckCompanyName']}&[$i].customerId={$jobRecord['MCNumber']}&[$i].originCity={$jobRecord['OriginCity']}&[$i].originState={$jobRecord['OriginState']}&[$i].originZip={$jobRecord['OriginZip']}&[$i].originPickupDate={$pickupdate}&[$i].destinationCity={$jobRecord['DestinationCity']}&[$i].destinationState={$jobRecord['DestinationState']}&[$i].destinationZip={$jobRecord['DestinationZip']}&[$i].deliveryDate={$deliveryDate}&";

			$i++;
		}
		
		$postData = rtrim($postData,'&');
		if ( $this->triumphToken != '' && $this->triumphToken != null ) {
			
		} else {
			$token = $this->get_sessionToken();
			$this->triumphToken = $token->SessionToken;
		}

		$url = 'v1Submit/CreateInputsFromArray';
		$result = $this->commonTriumphCurlRequest( $url, $postData, $this->triumphToken);
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
		
		$c = curl_init($this->triumphUrlRequest.$url);
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
		if ( $result['doc_name'] != '' && $result['doc_type'] == 'bundle' ) {
			$docNameArr = explode('.',$result['doc_name']);
			$result['thumbBundle'] = 'thumb_'.$docNameArr[0].'.jpg';
		} else {
			$result['thumbBundle'] = '';
			$result['doc_name'] = '';
		}
		echo json_encode($result);
	} 
	
	/**
	 * Flag or unflag the load for payment
	 */
	
	public function flagLoad( $status = '', $loadId = null ) {
		$result = $this->Billing->flagUnflagLoad( $status, $loadId );
		$data = $this->fetchingSentPaymentsInfo();
		$data['flaggedLoads'] = $this->Billing->fetchFlaggedPaymentLoads();
		echo json_encode(array('success' => true,'loadsInfo' => $data));	
	} 
	
	
	/**
	 * Generating session token for triumph
	 */
	 
	public function get_sessionToken(){
       $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->triumphUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "username={$this->triuser}&password={$this->password}&apiKey={$this->apikey}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        $data = (json_decode($server_output));
        curl_close ($ch);
        return $data;
    }
    
   	public function updateReadyForInvoice() {
		$this->Job->readyInvoice();
	}

	public function getRecords($parameter = ''){
		$params = json_decode(file_get_contents('php://input'),true);
		$total = 0;
		$jobs = array();
		if($params["pageNo"] <= 1){
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] + 1);	
		}else{
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] );	
		}
		

		if((isset($params["sortColumn"]) && empty($params["sortColumn"])) || !isset($params["sortColumn"])){ $params["sortColumn"] = "PickupDate"; }
		if((isset($params["sortType"]) && empty($params["sortType"])) || !isset($params["sortType"])){ $params["sortType"] = "DESC"; }
		if(!isset($params["startDate"])){ $params["startDate"] = ''; }
		if(!isset($params["endDate"])){ $params["endDate"] = ''; }

		

		
		$params["filterType"] = isset($params["filterArgs"]["filterType"]) ? $params["filterArgs"]["filterType"] : 'all';
		$params["userType"] = isset($params["filterArgs"]["userType"]) ? $params["filterArgs"]["userType"] : 'all';
		$params["userId"] = isset($params["filterArgs"]["userToken"]) ? $params["filterArgs"]["userToken"] : false;

		

		if($params["userType"] == "team" || $params["userType"] == "driver"){

			$driverInfo = $this->Driver->getInfoByDriverId($params["userId"]);
			if(isset($driverInfo[0])){ $driverInfo = $driverInfo[0]; }
			$params["vehicleId"] = isset($driverInfo["vehicleId"])  ? $driverInfo["vehicleId"] : '';
			$params["dispatcherId"] = isset($driverInfo["dispatcherId"])  ? $driverInfo["dispatcherId"] : '';
		}


		$jobs = $this->Billing->getFilteredLoads( $params);
		$total = $this->Billing->getFilteredLoads( $params,true);

		echo json_encode(array("data"=>$jobs,"total"=>$total));
	}	
	
}



