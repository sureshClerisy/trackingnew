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
	public  $userName;
	public $data;

	function __construct(){
		parent::__construct();
		
		$this->userRoleId  = $this->session->role;
		$this->userId 		= $this->session->loggedUser_id;
		$this->userName   	= $this->session->loggedUser_username;

		$this->load->model(array('Vehicle','Job','Billing',"Report"));
		$this->load->helper('truckstop');
		$this->load->helper('download');
		$this->itemsPerPage = $this->config->item('limit_per_page');
		$this->itemsPerPage = 20;
		
		$this->data = array();
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
		if ( !in_array($this->userRoleId, $this->config->item('with_admin_role')) ) {
			echo json_encode($this->data);
			exit();
		}

		$filters = array("itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC");
		$filters["startDate"] = $filters["endDate"] = '';
		if(isset($_COOKIE["_gDateRangeBilling"]) && !empty($_COOKIE["_gDateRangeBilling"])){
			$gDateRange = json_decode($_COOKIE["_gDateRangeBilling"],true);
			$filters["startDate"] = $gDateRange["startDate"]; $filters["endDate"] = $gDateRange["endDate"];
		}

		$jobs = $this->Billing->getInProgressLoads( $parameter, false, $filters );
		$this->data['total'] = $this->Billing->getInProgressLoads( $parameter, true, $filters );
		
		$this->data['loads'] = $jobs;
		$this->data['billType'] = 'billing';
		$this->data['sentPaymentData'] = $this->fetchingSentPaymentsInfo('otherPage');
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
	 * Fetching loads with generated invoice only for send payment page
	*/ 
	
	public function sendForPayment() {
		if ( !in_array($this->userRoleId, $this->config->item('with_admin_role')) ) {
			echo json_encode($this->data);
			exit();
		}
		$this->data = $this->fetchingSentPaymentsInfo();
		$this->data['sendPaymentContType'] = 'sendForPayment';
		$this->data['loadsForPaymentCount'] = count($this->data['loads']);
		echo json_encode($this->data);
	}
	
	/**
	 * Fetching load info common function
	 */
	
	public function fetchingSentPaymentsInfo($parameter = '' ) {
		if( $parameter == 'otherPage')
			$this->data['loadsForPaymentCount'] = $this->Billing->fetchLoadsForPaymentCount();
		else
			$this->data['loads'] = $this->Billing->fetchLoadsForPayment();

		$this->data['sentPaymentCount'] = $this->Billing->sentPaymentCount();
		$this->data['flaggedPaymentCount'] = $this->Billing->flaggedPaymentCount();
		$this->data['factoredPaymentCount'] = $this->Billing->factoredPaymentCount();
		return $this->data;
	}
	
	/**
	 * Fetching loads which are sent for payment
	 */
	
	public function fetchSentPaymentRecords() {
		if ( !in_array($this->userRoleId, $this->config->item('with_admin_role')) ) {
			echo json_encode($this->data);
			exit();
		}
		$_POST = json_decode(file_get_contents('php://input'),true);
		if ( isset($_POST) && count($_POST) > 0 ) {
			$filters = $_POST;
			if($filters["pageNo"] < 1){
				$filters["limitStart"] = ($filters["pageNo"] * $this->itemsPerPage + 1);	
			}else{
				$filters["limitStart"] = ($filters["pageNo"] * $this->itemsPerPage );	
			}
			$filters['itemsPerPage'] = $this->itemsPerPage;
		} else {
			$filters = array(
				"itemsPerPage" => $this->itemsPerPage,
				"limitStart" => 1, 
				"sortColumn" => "DeliveryDate",
				"sortType" => "DESC"
			);
		}
	
		$this->data['loads'] = $this->Billing->fetchSentPaymentLoads( $filters, false );
		$this->data['total'] = $this->Billing->fetchSentPaymentLoads( $filters, true );
		$this->data['total'] = $this->data['total'][0]['totalRows'];
		$this->data['sendPaymentContType'] = 'outbox';
		$this->data = $this->fetchingSentPaymentsInfo('otherPage');
		echo json_encode($this->data);
	} 	
	
	/**
	 * Fetching loads whose flag is set for payment
	 */
	
	public function fetchFactoredPaymentRecords( $parameter = '') {
		$this->data['factoredLoads'] = $this->Billing->fetchFactoredPaymentRecords();
		$this->data['triumphIdsArray'] =  array();
		if ( !empty($this->data['factoredLoads']) ) {
			foreach( $this->data['factoredLoads'] as $loads ) {
				array_push($this->data['triumphIdsArray'], $loads['id']);
			}
		}

		if ( $parameter == 'return') {
			return $this->data;
			exit();
		}
		$this->data['sendPaymentContType'] = 'factoredLoads';
		$this->data = $this->fetchingSentPaymentsInfo('otherPage');
		echo json_encode($this->data);
	}

	/**
	* move flag loads for payment
	*/

	public function setFinalFlagLoads() {
		$data =  array();

		$this->data['loadFlaggedTemp'] = $this->Billing->fetchLoadsFlaggedTemp();
		if ( !empty($this->data['loadFlaggedTemp']) ) {
			foreach( $this->data['loadFlaggedTemp'] as $flagged ) {
				if ( $flagged['payment_type'] == 'manual' ) {
					$sentForPayment = 1;
				} else {
					$sentForPayment = 0;
				}

				$data[] = array(
					'id' => $flagged['id'],
					'flag' => 1,
					'flag_perm' => 1,
					'sent_for_payment' => $sentForPayment,
				);
			}
		}

		if ( !empty($data)) {
			$this->Billing->changeFlaggedStatusPerm($data);
		}
		$this->data = $this->fetchingSentPaymentsInfo();
		echo json_encode($this->data);
	}

	// public function fetchFlaggedPaymentRecords($paymentType = '') {
	// 	$data['loads'] = $this->Billing->fetchFlaggedPaymentLoads($paymentType);
	// 	echo json_encode($data);
	// } 	
	
	/**
	 * creating schedule and send for payment
	 */ 
	
	public function creatingSchedule() 
	{
		try{	
			$objPost = json_decode(file_get_contents('php://input'),true);
			// $objPost['selectedIds'] = array(12044,12159);	

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
					$ticketsThatHasBeenSent = explode(',', $idsVar);
					if(count($ticketsThatHasBeenSent) > 0){
						$loadsSent = array();
						$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> sent a batch of <i class="ticket-batch">'.count($ticketsThatHasBeenSent).'</i> job ticket(s)';
						foreach ($ticketsThatHasBeenSent as $key => $value) {
							$loadsSent[]  = '<a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$value.',\'\',\'\',\'\',\'\',0,\'\')">#'.$value.'</a>';
						}
						$message.=' ('.implode(",", $loadsSent).') to triumph with confirmation code #'.$finalizeInput.'.';
						logActivityEvent($finalizeInput, $this->entity["truimph"], $this->event["sent_for_payment"], $message, $this->Job,'Send For Payment');
					}
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
			
			$data = $this->fetchingSentPaymentsInfo('otherPage');
			$data = $this->fetchFactoredPaymentRecords('return');
			echo json_encode(array('success' => true,'loadsInfo' => $data, 'errorMessage' => $errorMessage));
		}catch(Exception $e){
			log_message("error","SENT_FOR_PAYMENT".$e->getMessage());
			echo json_encode(array('success' => false,'loadsInfo' => array(), 'errorMessage' => "Got an exception"));
		}
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
	
	/*
	* Request URI : http://siteurl/billings/flagLoad
	* Method : post
	* Params : status, loadId
	* Return : null
	* Comment: Used for flag a load for sent for payment
	*/
	
	public function flagLoad( $status = '', $loadId = null, $paymentType = '', $srcPage = '' ) {
		try{
			$result = $this->Billing->flagUnflagLoad( $status, $loadId, $paymentType );
			if($status == "flag"){
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> added the job ticket <a 	href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$loadId.',\'\',\'\',\'\',\'\',0,\'\')">#'.$loadId.'</a> to queue.';		
				logActivityEvent($loadId, $this->entity["ticket"], $this->event["add_to_queue"], $message, $this->Job,$srcPage);
			}else{
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> removed the job ticket <a 	href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$loadId.',\'\',\'\',\'\',\'\',0,\'\')">#'.$loadId.'</a> from queue.';	
				logActivityEvent($loadId, $this->entity["ticket"], $this->event["remove_from_queue"], $message, $this->Job,$srcPage);
			}
			
			$data = $this->fetchingSentPaymentsInfo();
			$data = $this->fetchFactoredPaymentRecords('return');
			echo json_encode(array('success' => true,'loadsInfo' => $data));	
		}catch(Exception $e){
			log_message("error","ADD_REMOVE_QUEUE".$e->getMessage());
			echo json_encode(array('success' => false,'loadsInfo' => array()));	
		}
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
		if($params["pageNo"] < 1){
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] + 1);	
		}else{
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] );	
		}

		if((isset($params["sortColumn"]) && empty($params["sortColumn"])) || !isset($params["sortColumn"])){ $params["sortColumn"] = "PickupDate"; }
		if((isset($params["sortType"]) && empty($params["sortType"])) || !isset($params["sortType"])){ $params["sortType"] = "ASC"; }
		if(!isset($params["startDate"])){ $params["startDate"] = ''; }
		if(!isset($params["endDate"])){ $params["endDate"] = ''; }

		// pr($params); die;
		$jobs = $this->Billing->getInProgressLoads( $parameter ,false, $params);
		$total = $this->Billing->getInProgressLoads( $parameter,true ,$params);
		
		echo json_encode(array("data"=>$jobs,"total"=>$total));
	}



	/*
	* Request URI : http://siteurl/billings/billingStats
	* Method : POST
	* Params : null
	* Return : null
	* Comment: get all stats for billing dashboard.
	*/

	public function billingStats(){
		
		$exportFlag = json_decode(file_get_contents('php://input'), true);
		
		$lastWeekStartDay   = date("Y-m-d", strtotime('monday last week'));
		$lastWeekEndDay     = date("Y-m-d", strtotime('sunday last week'));
		$thisWeekStartDay   = date("Y-m-d", strtotime('monday this week'));
		$thisWeekLastDay    = date("Y-m-d", strtotime('sunday this week'));
		$thisWeekToday      = date("Y-m-d");
		$yesterday          = date("Y-m-d", strtotime("yesterday"));
		$pieChart = $lastWeek = $thisWeek = $filters = array();
		if(isset($_COOKIE["_gDateRangeBillDash"])){
			$filters = json_decode($_COOKIE["_gDateRangeBillDash"],true);
			if(!empty($filters["startDate"])){
				$lastWeekStartDay   = $filters["startDate"];
				$lastWeekEndDay     = $filters["endDate"];	
			}
		}
		$goalFilterThisWeek = array("startDate"=>$thisWeekStartDay, "endDate"=> $thisWeekLastDay);
		$goalFilterLastWeek = array("startDate"=>$lastWeekStartDay, "endDate"=> $lastWeekEndDay);
		$goalValuesThisWeek = $this->getMonthDays($thisWeekStartDay ,$thisWeekLastDay);
		$goalValuesLastWeek = $this->getMonthDays($lastWeekStartDay ,$lastWeekEndDay);

		$dispatchersList    = $this->Report->getDispatchersListForGoals();
		$thisWeek["goal"]   = $this->getPerformanceLoadsForAllDispatchers($dispatchersList, $goalFilterThisWeek, $goalValuesThisWeek);
		$lastWeek["goal"]   = $this->getPerformanceLoadsForAllDispatchers($dispatchersList, $goalFilterLastWeek, $goalValuesLastWeek);


		$considerDate = $yesterday;
		$recentTransactions = $this->Billing->getRecentTransactions(date("Y-m-d"), 1);
		if(isset($recentTransactions[0]["date"])){ 
			$considerDate =  $recentTransactions[0]["date"]; 
		}
		$expectedBillingToday   = $this->Billing->expectedBillingOnDate($considerDate,$yesterday);	
		$expectedBillingToday   = is_null($expectedBillingToday) ? 0 : $expectedBillingToday;

		$lastWeek["sentToTriumph"]  = $this->Billing->sentForPaymentWithFilter( $lastWeekStartDay, $lastWeekEndDay );
		$lastWeek["sentToTriumph"]  = is_null($lastWeek["sentToTriumph"]) ? 0 : $lastWeek["sentToTriumph"];
		$lastWeek["goalCompleted"]  = ( $lastWeek["sentToTriumph"] / $lastWeek["goal"] ) * 100;


		$thisWeek["sentToTriumph"]  = $this->Billing->sentForPaymentWithFilter( $thisWeekStartDay, $thisWeekToday  );
		$thisWeek["sentToTriumph"]  = is_null($thisWeek["sentToTriumph"]) ? 0 : $thisWeek["sentToTriumph"];
		$thisWeek["goalCompleted"]  = ( $thisWeek["sentToTriumph"] / $thisWeek["goal"] ) * 100;
		$thisWeek["targetType"]     = $thisWeek["goalCompleted"] > 100 ? "ahead" : "behind";
		$thisWeek["target"]         = abs($thisWeek["goalCompleted"] - 100) ;


		$sentToTriumphToday     = $this->Billing->sentForPaymentWithFilter( $thisWeekToday,    $thisWeekToday  );
		$sentToTriumphToday     = is_null($sentToTriumphToday) ? 0 : $sentToTriumphToday;
		

		$jobStatusStats         = $this->Job->fetchLoadsSummary(null, $filters,"all");
		$pieChart["waitingPaperwork"] = (int)$this->Job->getWaitingPaperworkCount($filters,"all");
		$pieChart["inprogress"] = $pieChart["booked"] = $pieChart["delivered"] = $pieChart["hasValue"] = 0;
		if(isset($jobStatusStats[0])){
			foreach ($jobStatusStats as $key => $value) {
				if(empty(trim($value['JobStatus'])) )
					continue;
				$pieChart[$value['JobStatus']] = (int)$value['tnum'];
				$pieChart["hasValue"] = 1;
			}
		}

		$recentTransactions = $this->getRecentTransactions($filters);
		$sentPaymentData = $this->fetchingSentPaymentsInfo('otherPage');
		//pr($exportFlag);die;
		if(!empty($exportFlag["export"])){
			$this->exportStat($recentTransactions);die();
		}

		echo json_encode(array("sentToTriumphToday"=>$sentToTriumphToday, "lastWeek"=> $lastWeek, "thisWeek" => $thisWeek, "recentTransactions" => $recentTransactions, "expectedBillingToday"=>$expectedBillingToday,"pieChart" => $pieChart, "sentPaymentData" => $sentPaymentData));
	}	

	/**
	* Method exportStat
	* @param stat data
	* @return NULL
	* Description Export Recent Stat data
	*/

	/*public function exportStat($recentStats=NULL){
		
		$keys = [['DATE','CONFIRMATION NO','INV','EXPECTED','ACTUAL']];
		$filterData=[];
		foreach ($recentStats as $key => $recentStat) {
			$filterData[$key]['date'] 		= $recentStat['date'];
			$filterData[$key]['confirmationCode'] = $recentStat['confirmationCode'];
			$filterData[$key]['inv'] 		= $recentStat['inv'];
			$filterData[$key]['expected'] 	= '$'.number_format($recentStat['expected'], 2, '.', '');
			$filterData[$key]['amount'] 	= '$'.$recentStat['amount'];
		}
		$data 	= array_merge($keys,$filterData);
		echo json_encode(array('fileName'=>$this->createExcell('brokers',$data)));
	}*/

	public function exportStat($recentStats=NULL){
		
		$keys = [['DATE','CONFIRMATION NO','INV','EXPECTED','ACTUAL']];
		$filterData=[];
		foreach ($recentStats['truimphTxns'] as $key => $recentStat) {
			$filterData[$key]['date'] 		= $recentStat['date'];
			$filterData[$key]['confirmationCode'] = $recentStat['confirmationCode'];
			$filterData[$key]['inv'] 		= $recentStat['inv'];
			$filterData[$key]['expected'] 	= '$'.number_format($recentStat['expected'], 2, '.', '');
			$filterData[$key]['amount'] 	= '$'.$recentStat['amount'];
		}
		$filterData[] = ['Total','',$recentStats['totals']['inv'],'$'.number_format($recentStats['totals']['expected'],2),'$'.number_format($recentStats['totals']['actual'],2)];
		$data 	= array_merge($keys,$filterData);
		
		echo json_encode(array('fileName'=>$this->createExcell('brokers',$data,TRUE)));
	}

	/*
	* Request URI : http://siteurl/billings/getSpecificStat
	* Method : POST
	* Params : null
	* Return : null
	* Comment: get specific stats for billing dashboard on refresh.
	*/
	public function getSpecificStat(){
		$postObj  = json_decode(file_get_contents("php://input"),true);
		$lastWeekStartDay = date("Y-m-d", strtotime('monday last week'));
		$lastWeekEndDay   = date("Y-m-d", strtotime('sunday last week'));
		$thisWeekStartDay = date("Y-m-d", strtotime('monday this week'));
		$thisWeekLastDay    = date("Y-m-d", strtotime('sunday this week'));
		$thisWeekToday    = date("Y-m-d");
		$yesterday        = date("Y-m-d", strtotime("yesterday"));
		$pieChart = $lastWeek = $thisWeek = array();
		$filters = array();
		
		if(isset($postObj["dates"]["startDate"]) && !empty($postObj["dates"]["startDate"]) ){
			$lastWeekStartDay = date("Y-m-d", strtotime($postObj["dates"]["startDate"]));
			$lastWeekEndDay   = date("Y-m-d", strtotime($postObj["dates"]["endDate"]));	
			$filters = $postObj["dates"];
		}

		$goalFilterThisWeek = array("startDate"=>$thisWeekStartDay, "endDate"=> $thisWeekLastDay);
		$goalFilterLastWeek = array("startDate"=>$lastWeekStartDay, "endDate"=> $lastWeekEndDay);
		$goalValuesThisWeek = $this->getMonthDays($thisWeekStartDay ,$thisWeekLastDay);
		$goalValuesLastWeek = $this->getMonthDays($lastWeekStartDay ,$lastWeekEndDay);



		$response= array();
		if(isset($postObj["type"])){
			switch ($postObj["type"]) {
				case 'sent_today' : $response["sentToTriumphToday"] = $this->Billing->sentForPaymentWithFilter($thisWeekToday, $thisWeekToday); 
					$response["sentToTriumphToday"]     = is_null($response["sentToTriumphToday"]) ? 0 : $response["sentToTriumphToday"];
					$considerDate = $yesterday;
					$recentTransactions = $this->Billing->getRecentTransactions(date("Y-m-d"), 1);
					if(isset($recentTransactions[0]["date"])){ 
						$considerDate =  $recentTransactions[0]["date"]; 
					}
					$response["expectedBillingToday"]   = $this->Billing->expectedBillingOnDate($considerDate,$yesterday);	
					$response["expectedBillingToday"]   = is_null($response["expectedBillingToday"]) ? 0 : $response["expectedBillingToday"];
				break;
				
				case 'last_week_sale'        : $dispatchersList    = $this->Report->getDispatchersListForGoals();
					$response["goal"]    = $this->getPerformanceLoadsForAllDispatchers($dispatchersList, $goalFilterLastWeek, $goalValuesLastWeek);
					$response["sentToTriumph"] = $this->Billing->sentForPaymentWithFilter($lastWeekStartDay, $lastWeekEndDay); 
					$response["sentToTriumph"] = is_null($response["sentToTriumph"]) ? 0 : $response["sentToTriumph"];
					$response["goalCompleted"]  = ( $response["sentToTriumph"] / $response["goal"] ) * 100;
				break;
				
				case 'week_till_today_sale'  : $dispatchersList    = $this->Report->getDispatchersListForGoals();
					$response["goal"]    = $this->getPerformanceLoadsForAllDispatchers($dispatchersList, $goalFilterThisWeek, $goalValuesThisWeek);
					$response["sentToTriumph"] = $this->Billing->sentForPaymentWithFilter($thisWeekStartDay, $thisWeekToday); 
					$response["sentToTriumph"] = is_null($response["sentToTriumph"]) ? 0 : $response["sentToTriumph"];
					$response["goalCompleted"]  = ( $response["sentToTriumph"] / $response["goal"] ) * 100;
					$response["targetType"]     = $response["goalCompleted"] > 100 ? "ahead" : "behind";
					$response["target"]         = abs($response["goalCompleted"] - 100) ;
				break;

				case 'recent_transactions'   : $response["recentTransactions"]    = $this->getRecentTransactions($filters); break;
				case 'job_status'            : $jobStatusStats         = $this->Job->fetchLoadsSummary(null, $filters,"all");
					$response["waitingPaperwork"] = (int)$this->Job->getWaitingPaperworkCount($filters,"all");
					$response["inprogress"] = $response["booked"] = $response["delivered"] =  $response["hasValue"] = 0 ;
					if(isset($jobStatusStats[0])){
						foreach ($jobStatusStats as $key => $value) {
							if(empty(trim($value['JobStatus'])) )
								continue;
							$response[$value['JobStatus']] = (int)$value['tnum'];
							$response["hasValue"] = 1;
						}
					}
			}
		}
		echo json_encode($response);
	}


	/*
	* Request URI : http://siteurl/billings/updateBillingStats
	* Method : POST
	* Params : null
	* Return : null
	* Comment: update all stats for billing dashboard.
	*/

	public function updateBillingStats(){
		$postObj  = json_decode(file_get_contents("php://input"),true);
		$dateFrom = date("Y-m-d", strtotime($postObj["startDate"]));
		$dateto   = date("Y-m-d", strtotime($postObj["endDate"]));
		$pieChart = $forDate = array();
		$goalFilterForDate   = array("startDate"=>$postObj["startDate"], "endDate"=> $postObj["endDate"]);
		$goalValuesForDate   = $this->getMonthDays($postObj["startDate"] ,$postObj["endDate"]);
		$dispatchersList     = $this->Report->getDispatchersListForGoals();
		$forDate["goal"]     = $this->getPerformanceLoadsForAllDispatchers($dispatchersList, $goalFilterForDate, $goalValuesForDate);
		$jobStatusStats      = $this->Job->fetchLoadsSummary(null, $postObj, "all");
		$forDate["sentToTriumph"]  = $this->Billing->sentForPaymentWithFilter( $postObj["startDate"] ,$postObj["endDate"] );
		$forDate["sentToTriumph"]  = is_null($forDate["sentToTriumph"]) ? 0 : $forDate["sentToTriumph"];
		if($forDate["goal"] > 0 ){
			$forDate["goalCompleted"]  = ( $forDate["sentToTriumph"] / $forDate["goal"] ) * 100;	
		}else{
			$forDate["goalCompleted"]  = 0;
		}
		
		$pieChart["waitingPaperwork"] = (int)$this->Job->getWaitingPaperworkCount($postObj,"all");
		$pieChart["inprogress"] = $pieChart["booked"] = $pieChart["delivered"] = $pieChart["hasValue"] = 0;
		if(isset($jobStatusStats[0])){
			foreach ($jobStatusStats as $key => $value) {
				if(empty(trim($value['JobStatus'])) )
					continue;
				$pieChart[$value['JobStatus']] = (int)$value['tnum'];
				$pieChart["hasValue"] = 1;
			}
		}

		$recentTransactions = $this->getRecentTransactions($postObj );
		


		echo json_encode(array( "lastWeek"=> $forDate, "recentTransactions" => $recentTransactions, "pieChart" => $pieChart ));
	}


	public function getRecentTransactions($args = array()){
		$recentTransactions = $this->Billing->getRecentTransactions(false, 5 , $args);
		//pr($recentTransactions);die;
		$recentTransactions = array_reverse($recentTransactions);
		$totalRow = array("actual"=>0,"expected"=>0,"inv"=>0);
		$truimphTxns = $response = array(); 
		//pr($recentTransactions);

		$trackIndex = 0;
		foreach ($recentTransactions as $key => $value) {

			$toBeSubtract = 0;
			$fromDate = $toDate = date('Y-m-d', strtotime('-1 day', strtotime($value["date"]))); 
			$billingDay =  date("l",strtotime($value["date"])); 
			if($billingDay == "Monday"){
				$considerDate = date('Y-m-d', strtotime('-3 day', strtotime($value["date"]))); 
			}else{
				$considerDate = date('Y-m-d', strtotime('-1 day', strtotime($value["date"]))); 
			}

			//echo $considerDate."<br/>";
			if($trackIndex == 0){
				$getLastTransaction = $this->Billing->getRecentTransactions($value["date"], 1);
				if(isset($getLastTransaction[0]["date"])){ 
					$considerDate =  $getLastTransaction[0]["date"]; 
				}
				$totalRow["fromDate"] = $considerDate;
			}
			$totalRow["toDate"] = $toDate;
			//echo $considerDate;die;

			if(in_array_multi($value["date"], $truimphTxns)){
				foreach ($truimphTxns as $txKey => $txValue) {
					if($value["date"] == $txValue["date"]){
						$toBeSubtract += $txValue["amount"]	;
					}
				}
			}

			if($billingDay !== "Monday" && $key !=0 && !in_array_multi($considerDate, $recentTransactions) && !in_array_multi($value["date"], $truimphTxns)){
				while (strtotime($considerDate) <= strtotime($toDate) ) {
					$xdate = date ("Y-m-d", strtotime("-1 day", strtotime($considerDate)));
					$temp  = array("confirmationCode"=>"--", "date"=>$considerDate, "inv"=> 0, "amount"=>0);
					$temp["fromDate"] = $xdate;
					$temp["toDate"]   = $xdate;
					$temp["expected"] = $this->Billing->expectedBillingOnDate($xdate,$xdate,"past");	
			        $considerDate = date ("Y-m-d", strtotime("+1 day", strtotime($considerDate)));
			        $truimphTxns[] = $temp;
				}
				$considerDate = date('Y-m-d', strtotime('-1 day', strtotime($considerDate))); 
			}
			$row = $this->Billing->getRecentTransactions($value["date"], 1 );
			if(count($row) > 0){
				$considerDate = $row[0]["date"];
			}
			$value["expected"]  = $this->Billing->expectedBillingOnDate($considerDate,$toDate,"past");	
			$value["expected"] -= $toBeSubtract;
			$value["fromDate"]  = $considerDate;
			$value["toDate"]    = $toDate;
			$totalRow["actual"]+= $value["amount"];
			$totalRow["inv"]   += $value["inv"];
			$truimphTxns[] = $value;
			$trackIndex++;
		}
		if( count($recentTransactions) <= 0 ){
			if( isset($args["startDate"]) ){
				$fromDate = $args["startDate"];	
			}
		}else{
			$totalRow["expected"] = $this->Billing->expectedBillingOnDate($totalRow["fromDate"],$totalRow["toDate"],"past");		
			$fromDate = date('Y-m-d', strtotime('+2 day', strtotime($totalRow["toDate"]))); 
		}


		//Forecast cash flow
		if( isset($args["startDate"]) && !empty($args["startDate"]) && isset($args["endDate"]) && !empty($args["endDate"])){
			while ( strtotime($fromDate) <= strtotime($args["endDate"]) ) {
				$day =  date("l",strtotime($fromDate)); 
				if( $day == "Saturday" || $day == "Sunday" ){
					$fromDate = date ("Y-m-d", strtotime("+1 day", strtotime($fromDate)));
					continue;
				}
				$considerToDate = date ("Y-m-d", strtotime("-1 day", strtotime($fromDate)));
				if( $day == "Monday" ){
					$considerFromDate = date ("Y-m-d", strtotime("-3 day", strtotime($fromDate)));
				}else{
					$considerFromDate = date ("Y-m-d", strtotime("-1 day", strtotime($fromDate)));
				}


				$temp  = array("confirmationCode"=>"--", "date"=>$fromDate, "inv"=> 0, "amount"=>0);
				$temp["fromDate"]   = $considerFromDate;
				$temp["toDate"]     = $considerToDate;
				$expected           = $this->Billing->expectedBilling($considerFromDate, $considerToDate);
				$temp["expected"]   = !empty( $expected["billing"] ) ? $expected["billing"] : 0;
				$temp["inv"]        = $expected["inv"];
				$totalRow["actual"] += $temp["amount"];
				$totalRow["inv"]    += $temp["inv"];
				$truimphTxns[] = $temp;

				$fromDate = date ("Y-m-d", strtotime("+1 day", strtotime($fromDate)));
			}
			$totalRow["expected"] = $this->Billing->expectedBillingOnDate($args["startDate"],$args["endDate"],"past");
		}
		
		$truimphTxns = array_reverse($truimphTxns);
		$response["truimphTxns"] = $truimphTxns;
		$response["totals"] = $totalRow;
		return $response;
	}


	/**
	* get number of days in month
	*/

	public function getMonthDays($startDate = '', $endDate = '' ) {
		$data 	= array();
		$time  	  = strtotime($startDate);
		$endTime  = strtotime($endDate);
		$month    = date("m",$time);
		$year     = date("Y",$time);
		$endMonth = date("m",$endTime);
		$endYear  = date("Y",$endTime);

		$monthsRange = range($month,$endMonth);
		$days = 0;
		for($i = 0; $i < count($monthsRange); $i++) {
			$days += cal_days_in_month(CAL_GREGORIAN, $monthsRange[$i], $endYear);
		}

		$data['singleFinancial'] 	= round( $this->config->item('singleFinancialGoal') / $days ) * count($monthsRange);
		$data['teamFinancial']  	= round( $this->config->item('teamFinancialGoal') / $days ) * count($monthsRange);					
		$data['singleMiles'] 		= round( $this->config->item('singleMilesGoal') / $days ) * count($monthsRange);
		$data['teamMiles']          = round( $this->config->item('teamMilesGoal') / $days ) * count($monthsRange);
		
		return $data;
	}

	public function getPerformanceLoadsForAllDispatchers($dispatchersList = array(),$rparam, $goalValues) {
		$mainArray = array();
		$lPResult = array();

		$overallTotalFinancialGoal = $overallTotalMilesGoal = $overallPlusMinusFinancialGoal = $overallPlusMinusMilesGoal = $totalInvoices	= $totalMiles = $totalDeadMiles	= $totalCharges	= $totalProfit = 0;	
		$goalLogStartDate = '2017-03-29';			// static date to consider the logs which are started from 29th march
		
		foreach( $dispatchersList as $key => $dispatcher ) {
			$teamFinancialGoal 	 = 0;
			$teamMilesGoal 		 = 0;
			$singleFinancialGoal = 0;
			$singleMilesGoal	 = 0;
			$showDispatcher		 = 1;
			$value 			= $this->Report->getLoadsTrackingAggregateDashboard($rparam,"dispatchers","dashboard", $dispatcher['dispId']);
			$driversList    = $this->Report->getTotalTeamDrivers($dispatcher['dispId'], $rparam['startDate'], $rparam['endDate'] );
			$driverLastLog  = $this->Report->getDispatcherLastLog($dispatcher['dispId'], $rparam['startDate']);
			
			if( !empty($driversList)) {
				$driversList = $this->getUniqueDriverDate($driversList);
				$driversList = array_values($driversList);
				for( $i = 0; $i < count($driversList); $i++ ) {
					if ( $i == 0 ) {
						$daysdiff = 1;
						if ( $driversList[$i]['createdDate'] <= $goalLogStartDate ) {				// check if log is before 29 march
							$sDate 	 = new DateTime($rparam["startDate"]);
							$singleDrivers = $driversList[$i]['single'];
							$teamDrivers   = $driversList[$i]['team'];

							$eDate   		= new DateTime($driversList[$i]['createdDate']);
							$newEndDate 	= $driversList[$i]['createdDate'];
							$daysdiff 	    += $eDate->diff($sDate)->format("%a");
							$singleFinancialGoal 	+= $driversList[$i]['single'] * $goalValues['singleFinancial'] * $daysdiff;
							$teamFinancialGoal	+= $driversList[$i]['team'] * $goalValues['teamFinancial'] * $daysdiff;
						} else {
							if ( !empty($driverLastLog) && count($driverLastLog) > 0 ) {// check if previous log exist for dispatcher then calc from startdate
								$sDate 		= new DateTime($rparam['startDate']);
								$eDate   	= new DateTime($driversList[$i]['createdDate']);
								$newEndDate = $driversList[$i]['createdDate'];
								$daysdiff  += $eDate->diff($sDate)->format("%a");
								$daysdiff = $daysdiff - 1;
								
								$singleFinancialGoal 	+= $driverLastLog['single'] * $goalValues['singleFinancial'] * $daysdiff;
								$teamFinancialGoal	+= $driverLastLog['team'] * $goalValues['teamFinancial'] * $daysdiff;
								
								$singleFinancialGoal 	+= $driversList[$i]['single'] * $goalValues['singleFinancial'];
								$teamFinancialGoal	+= $driversList[$i]['team'] * $goalValues['teamFinancial'];
							}
							else {				// check if previous log does not exist for dispatcher then calc from logged start date
								
								$sDate 			= new DateTime($driversList[$i]['createdDate']);
								$eDate   		= new DateTime($driversList[$i]['createdDate']);
								$newEndDate 	= $driversList[$i]['createdDate'];
								$daysdiff 	   += $eDate->diff($sDate)->format("%a");

								$singleFinancialGoal 	+= $driversList[$i]['single'] * $goalValues['singleFinancial'] * $daysdiff;
								$teamFinancialGoal	+= $driversList[$i]['team'] * $goalValues['teamFinancial'] * $daysdiff;
							}
						}
					} else {
						$endCreated = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $driversList[$i]['createdDate']) ) ));
						$sDate 			= new DateTime($driversList[$i -1 ]['createdDate']);
						$eDate   		= new DateTime($endCreated);
						$newEndDate 	= $driversList[$i]['createdDate'];

						$daysdiff 			= $eDate->diff($sDate)->format("%a");
						$singleFinancialGoal += $driversList[$i-1]['single'] * $goalValues['singleFinancial'] * $daysdiff;
						$teamFinancialGoal   += $driversList[$i-1]['team'] * $goalValues['teamFinancial'] * $daysdiff;
						
						$singleFinancialGoal += $driversList[$i]['single'] * $goalValues['singleFinancial'];
						$teamFinancialGoal   += $driversList[$i]['team'] * $goalValues['teamFinancial'];
						
					}

					if ( $driversList[$i]['single'] <= 0 && $driversList[$i]['team'] <= 0 )
						$showDispatcher = 0;		
				}
				
				if( $newEndDate < $rparam['endDate'] ) {	
					$sDate 					= new DateTime($newEndDate);
					$eDate   				= new DateTime($rparam['endDate']);
					$daysdiff 				= $eDate->diff($sDate)->format("%a");

					$driverList = end($driversList);

					$singleFinancialGoal 	+= $driverList['single'] * $goalValues['singleFinancial'] * $daysdiff;
					$teamFinancialGoal 	+= $driverList['team'] * $goalValues['teamFinancial'] * $daysdiff;
					
				}
			} else {	
				$driversList = $this->Report->getTotalDriversList($dispatcher['dispId'], $rparam['startDate'], $rparam['endDate'] );
				$daysdiff = 0;
				if ( !empty($driversList)) {
					if ( !empty($driverLastLog) && count($driverLastLog) > 0 )
						$sDate 		= new DateTime($rparam["startDate"]);
					else 
						$sDate 		= new DateTime($driversList["createdDate"]);

					$eDate   	= new DateTime($rparam['endDate']);
					$daysdiff 	= $eDate->diff($sDate)->format("%a");
					$daysdiff 	= $daysdiff + 1;
				}

				$single = isset($driversList['single']) ? $driversList['single'] : 0;
				$team   = isset($driversList['team']) ? $driversList['team'] : 0;
				$singleFinancialGoal  += $single * $goalValues['singleFinancial'] * $daysdiff;
				$teamFinancialGoal	+= $team * $goalValues['teamFinancial'] * $daysdiff;
				
				if ( $driversList['createdDate'] > $rparam['endDate'] && empty($driverLastLog)) {
					$showDispatcher = 0;
				} else if( $driversList['single'] <= 0 && $driversList['team'] <= 0 ) {
					$showDispatcher = 0;
				}
			}									
			
			if ( empty($value) && $showDispatcher == 0)
				continue;

			$totalFinancialGoal 		= $singleFinancialGoal + $teamFinancialGoal; 		
			$overallTotalFinancialGoal += $totalFinancialGoal;
			
		}

		return $overallTotalFinancialGoal;
	}

	/**
	* get latest date from many date time of same day
	*/

	public function getUniqueDriverDate($driversList = array()) {
		$driverListlength  = count($driversList);
		for( $j = 0; $j < $driverListlength; $j++ ) {
			if ( isset($driversList[$j]['createdDate']) && isset($driversList[$j+1]['createdDate'])  && ($driversList[$j]['createdDate'] == $driversList[$j+1]['createdDate']) ) {

				if ( $driversList[$j]['createdTime'] > $driversList[$j+1]['createdTime']) {
					unset($driversList[$j+1]);
				}
				else {
					unset($driversList[$j]);
				}
			}
		}
		$driversList = array_values($driversList);
		return $driversList;
	}

	public function fetchDataForCsv(){

		if ( !in_array($this->userRoleId, $this->config->item('with_admin_role')) ) {
			echo json_encode($this->data);
			exit();
		}
		
		$postObj 	= json_decode(file_get_contents("php://input"),true);
		// $parameter 	= ($postObj['InvoiceLoads'])?'':'invoice';
		$parameter 	= 'invoice';

		$filters 	= ['searchQuery'=>$postObj['searchText'],"itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC"];

		$filters["startDate"] = $filters["endDate"] = '';
		
		if(isset($_COOKIE["_gDateRangeBilling"]) && !empty($_COOKIE["_gDateRangeBilling"])){
			$gDateRange = json_decode($_COOKIE["_gDateRangeBilling"],true);
			$filters["startDate"] = $gDateRange["startDate"]; $filters["endDate"] = $gDateRange["endDate"];
		}

		$jobs = $this->Billing->getInProgressLoads( $parameter, false, $filters ,true);
		
		$keys = [['DATE','CUSTOMER NAME','DRIVERS','INVOICE','CHARGES','PROFIT','%PROFIT','MILES','DEAD MILES','RATE/MILE','DATE P/U','PICK UP','DATE DE','DELIVERY','LOLAD ID','STATUS']];

		$todayReport = $this->buildExportLoadData($jobs);
		$data = array_merge($keys,$todayReport);
		echo json_encode(array('fileName'=>$this->createExcell('billing',$data,TRUE)));
	}

	/**
	* Method exporSendPayment
	* @param Payment type [inbex,fectoring,outbox]
	* @return Excell File Name
	*
	*/
	public function exportSendPayment($type=null) {

		$args  = json_decode(file_get_contents("php://input"),true);
		if ( !in_array($this->userRoleId, $this->config->item('with_admin_role')) ) {
			echo json_encode($this->data);
			exit();
		}

		$keys = [['DATE','CUSTOMER NAME','DRIVERS','INVOICE','CHARGES','PROFIT','%PROFIT','MILES','DEAD MILES','RATE/MILE','DATE P/U','PICK UP','DATE DE','DELIVERY','LOLAD ID','STATUS']];

		$args = array(
			'sortColumn' => 'DeliveryDate',
			'sortType' => 'DESC',
			'searchQuery'=>$args['searchQuery']
		);
		switch ($type) {

			case 'inbox':
				$loads 	= $this->Billing->fetchLoadsForPayment();
				break;
			case 'factoring':
				$loads = $this->Billing->fetchFactoredPaymentRecords();
				break;
			case 'outbox':
				$loads 	= $this->Billing->fetchSentPaymentLoads($args, false,TRUE);
				break;
		}
		
		$exportData = $this->buildExportLoadData($loads);
		$data 		= array_merge($keys,$exportData);
		echo json_encode(array('fileName'=>$this->createExcell('billing',$data,TRUE)));
	}
}