<?php

/**
* Truck stop Api Controller
*  
*/

class Truckstop extends Admin_Controller{

	private $username;
	private $password;
	private $id;
	private $accountID;
	private $rows;
	private $pickupDate;
	private $userId;
	private $orign_state;
	private $Rpm_value;

	function __construct(){

		parent::__construct();
		
		$this->id 			= $this->config->item('truck_id');	
		$this->username 	= $this->config->item('truck_username');
		$this->password 	= $this->config->item('truck_password');
		$this->url 			= $this->config->item('truck_url');
		$this->pickupDate 	= date('Y-m-d');
		
		$this->load->model('Vehicle');

		if($this->session->role != 1){
			$this->userId 		= $this->session->loggedUser_id;
			$this->orign_state  = $this->Vehicle->get_vehicles_state($this->session->admin_id);
		}
		//$this->accountId 	= $this->config->item('truck_accountId');
		$this->load->model('Job');
		$this->load->library('Htmldom');
		$this->Rpm_value = 0;
		//$this->userId = 6;	
		
	}

	public function index(  $pickUpDate ='' ){
		//~ $newData = array();
			//~ echo json_encode(array('loadsData'=> $newData));
			//~ exit();
		$data['no_result'] 	= 'Search loads';
		$data['origin'] 	= '';
		$data['dest'] 		= '';
			
		$dest_city 		= '';
		$dest_state 	= '';
		$pickupDateDest = '';
		$currentDateTime = '';
		
		$newDest = array();
		$statesAddress = $this->Vehicle->get_vehicles_address($this->orign_state,$this->userId);
		
		if($statesAddress['0']['destination_address'] != '')
		{
			$newDest = explode(',', $statesAddress['0']['destination_address']);
			$this->orign_state = $newDest['0'];
			$this->orign_city = $newDest['1'];
			
			if ( isset($newDest[2]) && $newDest[2] != '' ) {
				$pickupDateDest = date('Y-m-d',strtotime($newDest[2]));
			} 	
		}
		else
		{
			array_push($newDest,$statesAddress['0']['state'],$statesAddress['0']['city']);
			$this->orign_state = $statesAddress['0']['state'];
			$this->orign_city = $statesAddress['0']['city'];
		}
		$newID = $statesAddress['0']['driverName'].'-'.$statesAddress['0']['label'].'-'.$newDest[0].'-'.$newDest[1];
		$vehicleIdRepeat = $statesAddress[0]['id'];		
		/*** Get All Label Array ****/
		$labelArray = array();
		$newDestlabel = array();
		if(!empty($statesAddress) && is_array($statesAddress))
		{
			foreach($statesAddress as $state)
			{
				if($state['destination_address'] != '')
				{
					$newDestlabel = explode(',', $state['destination_address']);
				}
				else
				{
					array_push($newDestlabel,$state['state'],$state['city']);
				}
				
				$index = $state['driverName'].'-'.$state['label'].'-'.$newDestlabel[0].'-'.$newDestlabel[1];
				$index = trim($index);
				$labelArray[$index] = $state['label'];
			}
		}
	
		if ( $pickUpDate != '' && $pickUpDate != '0000-00-00' ) {
			$pickupDate = $pickUpDate;
			$dateTime = array (
				'dateTime' => $pickupDate,
			);
			$currentDateTime = $pickUpDate;
		} else if ( $pickupDateDest != '' && strtotime($pickupDateDest) >= strtotime(date('Y-m-d')) ) {
			$dateTime = array (
					'dateTime' => $pickupDateDest,
				);
		} else {
			$pickupDate = date('Y-m-d');
				$dateTime = array (
				
			);
		}		
		
		$data['currentDateTime'] = $currentDateTime;
		//$wsdl_url = 'http://testws.truckstop.com:8080/V13/Searching/LoadSearch.svc?wsdl';//Test Mode APi
		$wsdl_url = 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl'; //Live Mode Api

		$client   = new SOAPClient($wsdl_url);

		$params   = array(
			'searchRequest' => array(
				'UserName' => $this->username, 'Password' => $this->password, 'IntegrationId' => $this->id,
				'Criteria' => array(
					'OriginCity' => $this->orign_city,
					'OriginState' =>$this->orign_state,
					'OriginCountry' => 'USA',
					'OriginRange' => '300',
					'OriginLatitude' => '',
					'OriginLongitude' => '',
					'DestinationCity' => '',
					'DestinationState' => '',
					'DestinationCountry' =>	'USA',
					'DestinationRange' => '300',
					'EquipmentType' => $statesAddress['0']['abbrevation'],
					'LoadType' => 'Full',
					'PickupDates' => $dateTime,
					'EquipmentOptions' => '',
					'HoursOld' => 9,
					)
				)
			);
		
		$return = $client->GetLoadSearchResults($params);
		
		//pr($return);
		
		if(empty($return->GetLoadSearchResultsResult->SearchResults)  || empty($return->GetLoadSearchResultsResult->SearchResults->LoadSearchItem)){
			$this->rows   = array();
		} else if(empty($return->GetLoadSearchResultsResult->Errors->Error)){
			$this->rows   = $return->GetLoadSearchResultsResult->SearchResults->LoadSearchItem;
		} else{			
			$data['no_result'] 	= $return->GetLoadSearchResultsResult->Errors->Error->ErrorMessage;
		}
		
		$data['rows'] 	  = json_decode(json_encode($this->rows),true);
				
		$price = array();

		
		$this->load->model('User');
		
		$destin = $this->orign_city.','.$this->orign_state.',USA';
		$loadsIdArray = array();
		
		$vehicle_fuel_consumption = $this->Vehicle->get_vehicles_fuel_consumption($this->orign_state,$this->userId);
        $truckAverage =(int)($vehicle_fuel_consumption['fuel_consumption']/100);
		$diesel_rate_per_gallon = 2.60;
		$driver_pay_miles_cargo = 0.45;
		$total_tax = 50;
		foreach ($data['rows'] as $key => $value) {
			$this->Rpm_value = 0;
			$loadWeight = (double)$value['Weight'];	
			if ( $value['Length'] > 48 || $loadWeight > 48000 || $value['Equipment'] == 'CONG') {
				unset($data['rows'][$key]);
				continue;
			}
			//----------------- PROFIT CALCULATION -------------------------------------------
			
			$origin = $value['OriginCity'].','.$value['OriginState'].',USA';
			$dataMiles = $this->User->getMiles($origin,$destin);
			if(!empty($dataMiles)){
				$data['rows'][$key]['deadmiles'] = $dataMiles;
				$deadMileDist = $dataMiles;
			} else {
				$miles  = $this->GetDrivingDistance($origin,$destin);
				$data['rows'][$key]['deadmiles'] = $miles['distance'];
				$deadMileDist = $miles['distance'];
			}
			$originToDestDist = $value['Miles'];
			$total_complete_distance = $originToDestDist + $deadMileDist;
			$gallon_needed =  ceil($total_complete_distance/$truckAverage);
			$total_diesel_cost = $diesel_rate_per_gallon*$gallon_needed;
			
			$total_driver_cost = $driver_pay_miles_cargo*$total_complete_distance;
			$total_cost = round(($total_diesel_cost+$total_driver_cost+$total_tax),2);
			if ( $value['Payment'] < $total_cost&&$value['Payment']!=null&&$value['Payment']!=0) {
				unset($data['rows'][$key]);
				continue;
			}
			
			//----------------- PROFIT CALCULATION -------------------------------------------								
			if ( $value['Payment'] != '' && $value['Payment'] != 0 && is_numeric($value['Payment']) && $value['Miles'] > 0 ) {
				$this->Rpm_value = round( $value['Payment'] / $value['Miles'], 2 );
			} 
		
			$data['rows'][$key]['RPM'] = $this->Rpm_value;
			$data['rows'][$key]['Age'] = '0'.$value['Age'].':00';
					
			
				
			$loadsIdArray[] = $value['ID'];
		
			$dataMiles = $this->User->getMiles($origin,$destin);
			if(!empty($dataMiles)){
				$data['rows'][$key]['deadmiles'] = $dataMiles;
			} else {
				$miles  = $this->GetDrivingDistance($origin,$destin);
				$data['rows'][$key]['deadmiles'] = $miles['distance'];
			}
		}
		
		$data['pickupDate'] 	= $this->pickupDate;
		$data['statesAddress'] 	= $statesAddress;
		
		$data['states_data'] = $this->Job->getAllStates();
		$data['equipmentTypes'] = $this->Job->fetchEquipmentTypes();
		$newData = array();
		//$newData['rows'][$newID] =  $data['rows'];
		
		
		$data['rows'] = array_values($data['rows']);
		
		if ( count($data['rows'] > 1) ) {
			foreach ($data['rows'] as $key => $row)
			{
				$price[$key] = $row['Miles'];
			}
			array_multisort($price, SORT_DESC, $data['rows']);
		}
		
		$newData['rows'] =  $data['rows'];
		$newData['table_title'] =  $newID;
		$newData['statesAddress'] 	= $statesAddress;
		$newData['labelArray'] 	= $labelArray;
		$newData['states_data'] = $data['states_data'];
		$newData['loadsIdArray'] = $loadsIdArray;
		$newData['vehicleIdRepeat'] = $vehicleIdRepeat;
		echo json_encode(array('loadsData'=> $newData),JSON_NUMERIC_CHECK);
	}
	
	public function newSearch(){
		
		$objPost = json_decode(file_get_contents('php://input'),true);
	
		$data['rows'] 		= array();
		$data['no_result'] 	= 'Search loads';
		$dataPost 	= $objPost;
			
			if ( isset($dataPost['destination_state']) && !empty($dataPost['destination_state']) ) {
				$destination_states = $dataPost['destination_state'];
			} else {
				$destination_states = '';
			}
			
			if ( isset($dataPost['multiDestinations']) && !empty($dataPost['multiDestinations']) ) {
				$destination_states = implode(',',$dataPost['multiDestinations']);
			} else {
				$destination_states = '';
			}
			
			$pickupDateDest = '';
			$array = explode(':',$dataPost['select_driver']);
			$equipment_type = trim($array[0]);
			$driverName = trim($array[4]);
			$truckLabel = trim($array[5]);
			
			$fetchDateResults = '';
			$newDest = array();
			
			if ( $array[1] != '' ) {
				$address_array = explode(',',$array[1]);
				$this->orign_city = $address_array[1];
				$this->orign_state = $address_array[0];
				if ( isset($address_array[2]) && $address_array[2] != '') {
					$fetchDateResults = date('Y-m-d',strtotime($address_array[2]));
				}
			} else {
				$address_array = explode(',',$array[2]);
				$this->orign_city = $address_array[1];
				$this->orign_state = $address_array[0];
				$fetchDateResults = '';
			}
			
			array_push($newDest,$this->orign_state,$this->orign_city);
			$newID = $driverName.'-'.$truckLabel.'-'.$newDest[0].'-'.$newDest[1];
			
			$origin_country = 'USA';
			$dest_country = 'USA';	
					
			$destination_range = ( $dataPost['destination_range'] != '' && is_numeric($dataPost['destination_range']) ) ? $dataPost['destination_range'] : 300;			
			$origin_range = ( $dataPost['origin_range'] != '' && is_numeric($dataPost['origin_range']) ) ? $dataPost['origin_range'] : 300;			
			$load_type = ( isset($dataPost['load_type']) && $dataPost['load_type'] != '' ) ? $dataPost['load_type'] : 'Full';			
			//~ $company_name = $dataPost['company_name'];			
			$min_payment = (isset($dataPost['min_payment']) && is_numeric($dataPost['min_payment']) ) ? $dataPost['min_payment'] : 0;			
			$posted_time = ( isset($dataPost['posted_time']) ) ? $dataPost['posted_time'] + 1 : '';			
			$max_weight = ( isset($dataPost['max_weight']) && is_numeric($dataPost['max_weight']) ) ? $dataPost['max_weight'] : 0;			
					
			
			if ( isset($dataPost['pickup_date']) && $dataPost['pickup_date'] != '' && $dataPost['pickup_date'] != '0000-00-00' ) {
				if ( strpos($dataPost['pickup_date'], ',') !== false ) {
					$datesNewArray = explode(',',$dataPost['pickup_date']);
					for ( $i = 0; $i < count($datesNewArray);  $i++ ) {
						$dateTime[] = $datesNewArray[$i];
					}
				} else {
				
					$pickupDate = $dataPost['pickup_date'];
					$dateTime = array (
						'dateTime' => $pickupDate,
					);
				}
			} else if ( $fetchDateResults != '' && strtotime($fetchDateResults) >= strtotime(date('Y-m-d')) ) {
				$dateTime = array (
					'dateTime' => $fetchDateResults,
				);
			} else {
				$pickupDate = date('Y-m-d');
				$dateTime = array (
					
				);
			}
			
			$wsdl_url = 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl'; //Live Mode Api

			$client   = new SOAPClient($wsdl_url);

			$params   = array(
				'searchRequest' => array(
					'UserName' => $this->username, 'Password' => $this->password, 'IntegrationId' => $this->id,
					'Criteria' => array(
						'OriginCity' => $this->orign_city,
						'OriginState' => $this->orign_state,
						'OriginCountry' => $origin_country,
						'OriginRange' => $origin_range,
						'OriginLatitude' => '',
						'OriginLongitude' => '',
						'DestinationCity' => '',
						//'DestinationState' => '',
						'DestinationState' => $destination_states,
						'DestinationCountry' =>	$dest_country,
						'DestinationRange' => '',
						'EquipmentType' => $equipment_type,
						'LoadType' => $load_type,
						'PickupDates' => $dateTime,
						//'PickupDates' => array('2016-09-16','2016-09-17','2016-09-18'),
						'EquipmentOptions' => '',
						'HoursOld' => $posted_time,
						
						)
					)
				);
		
			
			$return = $client->GetLoadSearchResults($params);

			if(empty($return->GetLoadSearchResultsResult->SearchResults)  || empty($return->GetLoadSearchResultsResult->SearchResults->LoadSearchItem)){
				$this->rows   = array();
			} else if(empty($return->GetLoadSearchResultsResult->Errors->Error)){
				$this->rows   = $return->GetLoadSearchResultsResult->SearchResults->LoadSearchItem;
			} else{			
				$data['no_result'] 	= $return->GetLoadSearchResultsResult->Errors->Error->ErrorMessage;
			}
			$data['rows'] 	  = json_decode(json_encode($this->rows),true);
			$data['pickupDate'] 	= $this->pickupDate;

			$price = array();


			if ( count($data['rows'] ) > 1 ) {
				foreach ($data['rows'] as $key => $row)
				{
					$price[$key] = $row['Miles'];
				}
				array_multisort($price, SORT_DESC, $data['rows']);
			}
			
			$this->load->model('User');
			$destin = $this->orign_city.','.$this->orign_state.',USA';

			if ( !empty($data['rows']) ) {
				foreach ($data['rows'] as $key => $value) {
					$this->Rpm_value = 0;
					$loadWeight = (double)$value['Weight'];	
					$loadPayment = (double)(str_replace(',','',$value['Payment']));	
					if ( $value['Length'] > 48 || $loadWeight > 48000 || $loadPayment < $min_payment || $loadWeight < $max_weight || $value['Equipment'] == 'CONG') {
						unset($data['rows'][$key]);
						continue;
					}
					
					if ( $value['Payment'] != '' && $value['Payment'] != 0 && is_numeric($value['Payment']) && $value['Miles'] > 0 ) {
						$this->Rpm_value = round( $value['Payment'] / $value['Miles'], 2 );
					} 
			
					$data['rows'][$key]['RPM'] = $this->Rpm_value;
					$data['rows'][$key]['Age'] = '0'.$value['Age'].':00';
				
					
					$origin = $value['OriginCity'].','.$value['OriginState'].',USA';

					$dataMiles = $this->User->getMiles($origin,$destin);
							
					if(!empty($dataMiles)){
						$data['rows'][$key]['deadmiles'] = $dataMiles;
					} else {
						$miles  = $this->GetDrivingDistance($origin,$destin);
						$data['rows'][$key]['deadmiles'] = $miles['distance'];
					}
					
				}
			}
		
		$data['new_search'] 	= 1;
		$newData = array();
		$newData['rows'] =  array_values($data['rows']);
		$newData['table_title'] =  $newID;
		
		echo json_encode($newData);
	}

	public function loadDetails($loadID=null){
		$wsdl_url = 'http://testws.truckstop.com:8080/V13/Searching/LoadSearch.svc?wsdl';
		//$wsdl_url = 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl';
		$client   = new SOAPClient($wsdl_url);
		$params   = array(
			'detailRequest' => array(
				'UserName' => $this->username,
				'Password' => $this->password,
				'IntegrationId' => $this->id,
				'LoadId' => $loadID,
				)
			);
		$return = $client->GetLoadSearchDetailResult($params);
		
		$results = $return->GetLoadSearchDetailResultResult->LoadDetail;
		$data['row'] 	= $results;

		$data['page_title'] 	= 'Load Details';
		$data['page_body'] 		= $this->load->view('admin/truckstop/details',$data,true);
		$data['page_sidebar'] 	= $this->load->view('admin/sidebar',$data,true);
		$this->load->view('admin/admin',$data);
		
	}
	
	public function matchLoadDetail(  $truckStopId = null, $loadId = null ) {
		
		if ( $loadId != '' && is_numeric($loadId) ) {
			$jobResult = $this->Job->FetchSingleJob($loadId);
			
			$origin = $jobResult['origin_city'].' '.$jobResult['origin_state'].' '.$jobResult['origin_country'];
			$destination = $jobResult['destination_city'].' '.$jobResult['destination_state'].' '.$jobResult['destination_country'];
			
			$jobRecord['OriginCity'] = $jobResult['origin_city'];
			$jobRecord['OriginState'] = $jobResult['origin_state'];
			$jobRecord['OriginCountry'] = $jobResult['origin_country'];
			$jobRecord['DestinationCity'] = $jobResult['destination_city'];
			$jobRecord['DestinationState'] = $jobResult['destination_state'];
			$jobRecord['DestinationCountry'] = $jobResult['destination_country'];
			$jobRecord['PickupDate'] = $jobResult['pickup_date'];
			$jobRecord['PickupTime'] = $jobResult['pickup_time'];
			$jobRecord['DeliveryDate'] = $jobResult['delivery_date'];
			$jobRecord['DeliveryTime'] = $jobResult['delivery_time'];
			$jobRecord['Quantity'] = $jobResult['load_quantity'];
			$jobRecord['LoadType'] = $jobResult['load_size'];
			$jobRecord['Weight'] = $jobResult['weight'];
			$jobRecord['Length'] = $jobResult['length'];
			$jobRecord['Width'] = $jobResult['width'];
			$jobRecord['Stops'] = $jobResult['stops'];
			$jobRecord['Bond'] = $jobResult['bond'];
			$jobRecord['TruckCompanyName'] = $jobResult['posting_name'];
			$jobRecord['PointOfContact'] = $jobResult['origin_city'];
			$jobRecord['PointOfContactPhone'] = $jobResult['posting_contact'];
			$jobRecord['TruckCompanyEmail'] = $jobResult['posting_email'];
			$jobRecord['TruckCompanyPhone'] = $jobResult['posting_phone'];
			$jobRecord['TruckCompanyFax'] = $jobResult['posting_fax'];
			$jobRecord['MCNumber'] = $jobResult['broker_mc'];
			$jobRecord['DOTNumber'] = $jobResult['us_dot'];
			$jobRecord['PaymentAmount'] = $jobResult['payment_amount'];
			$jobRecord['ID'] = $jobResult['truckstopID'];
			$jobRecord['Entered'] = $jobResult['posted_on'].'T00:00:00.000';
			$jobRecord['PostedOn'] = $jobResult['posted_on'];
			$jobRecord['EquipmentTypes']['Code'] = $jobResult['equipment_options'];
			$jobRecord['EquipmentTypes']['Description'] = $jobResult['equipment'];
			$jobRecord['JobStatus'] = $jobResult['status'];
			$jobRecord['brokerStatus'] = $jobResult['broker_status'];
			$jobRecord['Notes'] = $jobResult['notes'];
			$jobRecord['ExtraInfo'] = $jobResult['extra_information'];
			$jobRecord['Mileage'] = $jobResult['distance'];
			
			$brokerInfo = array($jobResult['posting_name'],$jobResult['broker_mc'],$jobResult['posting_address'],'','','',$jobResult['posting_phone'],$jobResult['broker_rating']);
			$jobRecord['postingAddress'] = $brokerInfo['2'];
			$jobRecord['broker_info'] = $brokerInfo;
						
			$jobRecord['timer_distance'] = $jobResult['distance'];
			$data['primaryLoadId'] = $loadId;
			
			if ( $jobResult['payment_amount'] != '' || $jobResult['payment_amount'] != 0 || $jobResult['payment_amount'] != null || $jobRecord['timer_distance'] != 0 )	{
				$jobRecord['overall_total_rate_mile'] = round($jobResult['payment_amount'] / $jobRecord['timer_distance'], 2);
			} else {
				$jobRecord['overall_total_rate_mile'] = '';
			}

		} else {
			//~ $wsdl_url = 'http://testws.truckstop.com:8080/V13/Searching/LoadSearch.svc?wsdl';
			$wsdl_url = 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl'; //Live Mode Api
			$client   = new SOAPClient($wsdl_url);
			$params   = array(
				'detailRequest' => array(
					'UserName' => $this->username,
					'Password' => $this->password,
					'IntegrationId' => $this->id,
					'LoadId' => $truckStopId,
					)
				);
			$return 	 = $client->GetLoadSearchDetailResult($params);
			$loadResults = $return->GetLoadSearchDetailResultResult->LoadDetail;

			$Detaildata = json_encode($loadResults);
			$jobRecord  = json_decode($Detaildata,true);
			
			$jobRecord['OriginCountry'] = 'USA';
			$jobRecord['DestinationCountry'] = 'USA';
			
			if ( $jobRecord['PickupDate'] != '' ) {
				$jobRecord['PickupDate'] = date('Y-m-d',strtotime($jobRecord['PickupDate']));
			}
			
			if ( $jobRecord['DeliveryDate'] != '' ) {
				$jobRecord['DeliveryDate'] = date('Y-m-d',strtotime($jobRecord['DeliveryDate']));
			}
			
			if ( $jobRecord['Entered'] != '' ) {
				$enteredArray = explode('T',$jobRecord['Entered']);
				$jobRecord['Entered'] = $enteredArray[0];
			}
			$jobRecord['PostedOn'] = $jobRecord['Entered'];
			
			//~ $origin = $jobRecord['OriginCity'].' '.$jobRecord['OriginState'].' '.$jobRecord['OriginCountry'];
			//~ $destination = $jobRecord['DestinationCity'].' '.$jobRecord['DestinationState'].' '.$jobRecord['DestinationCountry'];

			$brokerInfo = array();
			
			$jobRecord['broker_info'] = $brokerInfo;
			
			//~ if ( $jobRecord['PaymentAmount'] == '' || $jobRecord['PaymentAmount'] == null || $jobRecord['PaymentAmount'] == 0 ) 
				//~ $jobRecord['PaymentAmount'] = 4000;
				
			$data['primaryLoadId'] = '';
			
			
			$jobRecord['postingAddress'] =   $jobRecord['TruckCompanyCity'].', '.$jobRecord['TruckCompanyState'];
			$jobRecord['brokerStatus'] = '';
			
			$jobRecord['timer_distance'] = $jobRecord['Mileage'];
			
			if ( $jobRecord['PaymentAmount'] != '' || $jobRecord['PaymentAmount'] != 0 || $jobRecord['PaymentAmount'] != null || $jobRecord['timer_distance'] != 0 )	{
				$jobRecord['overall_total_rate_mile'] = round($jobRecord['PaymentAmount'] / $jobRecord['timer_distance'], 2);
			} else {
				$jobRecord['overall_total_rate_mile'] = '';
			}
			
			$jobRecord['JobStatus'] = '';
			$jobRecord['Notes'] = '';
			$jobRecord['ExtraInfo'] = $jobRecord['SpecInfo'];
			
		}
		
		$data['encodedJobRecord'] = $jobRecord;
		$data['page_title'] = 'Load Details';
		$data['broker_info'] = $brokerInfo;
		//$data['jobRecord'] = $jobRecord;
		$data['truckStopId'] = $truckStopId;
		$data['states_data'] = $this->Job->getAllStates();
		$data['color'] = 'match_color';
		
		echo json_encode($data);
		
	}
	
	public function fetch_matched_trucks( $truckStopId = null , $jobId = null )  {
		
		$fetchAssigedTruck = null;
		if( $jobId != '' && is_numeric($jobId) ) {
			$fetchAssignedTruckArray = $this->Job->fetchAssigedTruck( $jobId );
			if ( !empty($fetchAssignedTruckArray) ) {
				$fetchAssigedTruck = $fetchAssignedTruckArray['assigned_truck_id'];
			}
		}
		
		$jobRecord = $this->Job->FetchSingleJob($jobId);
				
		$jobSpec = $jobRecord['weight'];
		//$jobSpec = 4000;
		$jobCollect = $jobRecord['origin_city'].' '.$jobRecord['origin_state'];
		$jobDeliver = $jobRecord['destination_city'].' '.$jobRecord['destination_state'];
		$jobVehicle = $jobRecord['equipment'];
		$jobVehicleType = $jobRecord['equipment_options'];
		
		$jobWidth = $jobRecord['width'];
		$jobLength = $jobRecord['length'];
			if ( $jobWidth == '' || $jobWidth == null || $jobWidth == 0 ) 
				$jobWidth = 0;
				
			if ( $jobLength == '' || $jobLength == null || $jobLength == 0 ) 
				$jobLength = 0;
		
		
		$loadPaymentAmount = 0;
		if ($jobRecord['payment_amount'] != '' && $jobRecord['payment_amount'] != 'NA' && $jobRecord['payment_amount'] != '0') {
			$loadPaymentAmount = $jobRecord['payment_amount'];
		} 
				
		
		$dataN['vehicles_Available'] = $VehiclesArray = $this->Job->FindVehicles( $jobSpec, $jobCollect, $jobDeliver, $jobVehicle, $fetchAssigedTruck ,$jobVehicleType, $jobId, $this->userId, $jobWidth, $jobLength);

		//~ $dieselFuelPrice = $this->getFuelPrice();
	
		 $dieselFuelPrice = 2.60;
		if( !empty($VehiclesArray) ) {
			$i = 0;
			foreach( $dataN['vehicles_Available'] as $vehicle_Available ) {
				$destinationAddressArray = explode(',',$vehicle_Available['destination_address']);
				$destOriginCity = $destinationAddressArray[1];
				$destOriginState = $destinationAddressArray[0];
			
				$currentVehcileLocation = $destOriginCity.' '.$destOriginState.' USA';
				$nextJobOrigin = $jobRecord['origin_city'].' '.$jobRecord['origin_state'].' '.$jobRecord['origin_country'];
				
				$miles  = $this->GetDrivingDistance($currentVehcileLocation,$nextJobOrigin);
				$VehiclesArray[$i]['originToDestination'] = $jobRecord['distance'];
				
				$originToDestArray = explode('mi',$jobRecord['distance']);
				$originToDestDist = trim(str_replace(',','',$originToDestArray[0]));
				
							//~ $deadMileDist = str_replace(',','',$total_dis[0]);
					$deadMileDist = $miles['distance'];
				
				$VehiclesArray[$i]['deadMileDist'] = $deadMileDist;
				
				$VehiclesArray[$i]['total_complete_distance'] = $originToDestDist + $deadMileDist;
				$truckAverage = (int)($vehicle_Available['fuel_consumption'] / 100);
				$VehiclesArray[$i]['fuel_consumption'] = $truckAverage;
				
				$VehiclesArray[$i]['gallon_needed'] = ceil($VehiclesArray[$i]['total_complete_distance'] / $truckAverage);
				$VehiclesArray[$i]['diesel_rate_per_gallon'] = $dieselFuelPrice;
				$VehiclesArray[$i]['comp_diesel_cost'] = $VehiclesArray[$i]['diesel_rate_per_gallon'] * $VehiclesArray[$i]['gallon_needed'];
				
				$VehiclesArray[$i]['originToDestDistDriver'] = $originToDestDist;
				$VehiclesArray[$i]['driver_dead_mile'] = $deadMileDist;
				$VehiclesArray[$i]['driver_dead_miles_paid'] = 0;
				$VehiclesArray[$i]['driver_dead_miles_not_paid'] = 0;
				$VehiclesArray[$i]['driver_pay_for_dead_mile'] = 0.45;
				$VehiclesArray[$i]['driver_dead_mile_paid'] = (float)($VehiclesArray[$i]['driver_dead_miles_paid'] * $VehiclesArray[$i]['driver_pay_for_dead_mile']);
				$VehiclesArray[$i]['driver_pay_miles_cargo'] = 0.45;
				$VehiclesArray[$i]['driver_amount_cargo'] = (float)($VehiclesArray[$i]['driver_pay_miles_cargo'] * $VehiclesArray[$i]['total_complete_distance']);
				$VehiclesArray[$i]['driver_due_driver'] = (float)($VehiclesArray[$i]['driver_amount_cargo'] + $VehiclesArray[$i]['driver_dead_mile_paid']);
				
				$VehiclesArray[$i]['tax_ifta_tax'] = 50;
				$VehiclesArray[$i]['tax_extra_stop'] = 0;
				$VehiclesArray[$i]['tax_tarps'] = 0;
				$VehiclesArray[$i]['tax_det_time'] = 0;
				$VehiclesArray[$i]['tax_tolls'] = 0;
				$VehiclesArray[$i]['tax_total_charge'] = (float)($VehiclesArray[$i]['tax_ifta_tax'] + $VehiclesArray[$i]['tax_extra_stop'] + $VehiclesArray[$i]['tax_tarps'] + $VehiclesArray[$i]['tax_det_time'] + $VehiclesArray[$i]['tax_tolls']);
				
				$VehiclesArray[$i]['overall_total_payment_amount'] = $loadPaymentAmount;
				$VehiclesArray[$i]['overall_total_charge'] = round($VehiclesArray[$i]['comp_diesel_cost'] + $VehiclesArray[$i]['driver_due_driver'] + $VehiclesArray[$i]['tax_total_charge'], 2);
				print_r($VehiclesArray[$i]['comp_diesel_cost']);
				print_r($VehiclesArray[$i]['driver_due_driver']);
				print_r($VehiclesArray[$i]['tax_total_charge']);
				if ( $loadPaymentAmount != '' && $loadPaymentAmount != 0 && $loadPaymentAmount != null ) {
					$VehiclesArray[$i]['EditCalulations'] = true;
			
					
					$VehiclesArray[$i]['overall_total_profit'] = round($loadPaymentAmount - $VehiclesArray[$i]['overall_total_charge'], 2);
					$VehiclesArray[$i]['overall_total_profit_percent'] = round(($VehiclesArray[$i]['overall_total_profit'] / $loadPaymentAmount) * 100, 2);
					$VehiclesArray[$i]['overall_total_rate_mile'] = round($loadPaymentAmount / $originToDestDist, 2);
				} else {
					$VehiclesArray[$i]['EditCalulations'] = false;
					$VehiclesArray[$i]['overall_total_profit'] = '';
					$VehiclesArray[$i]['overall_total_profit_percent'] = '';
					$VehiclesArray[$i]['overall_total_rate_mile'] = '';				
				}
				$i++;
			}

			
			function sort_by_order($a, $b)
			{
				return $a['deadMileDist'] - $b['deadMileDist'];
			}
			usort($VehiclesArray, 'sort_by_order');
			
		}

		$data['vehicles_Available'] = $VehiclesArray;
		$data['total_records'] = count($data['vehicles_Available']);
		$data['jobRecord'] = $jobRecord;
		$data['truckStopId'] = $truckStopId;
		$data['fetchAssigedTruck'] = $fetchAssigedTruck;
		echo json_encode($data);
	}
	
	public function fetch_matched_trucks_live( $truckStopId = null , $jobId = null )  {
		
		$fetchAssigedTruck = null;
		if( $jobId != '' && is_numeric($jobId) ) {
			$fetchAssignedTruckArray = $this->Job->fetchAssigedTruck( $jobId );
			if ( !empty($fetchAssignedTruckArray) ) {
				$fetchAssigedTruck = $fetchAssignedTruckArray['assigned_truck_id'];
			}
		}
		$_POST = json_decode(file_get_contents('php://input'), true);
	
		$jobRecord = $_POST['jobRecords'];
				
		$jobSpec = $jobRecord['Weight'];
		$jobCollect = $jobRecord['OriginCity'].' '.$jobRecord['OriginState'];
		
		
		$jobDeliver = $jobRecord['DestinationCity'].' '.$jobRecord['DestinationState'];
		$jobVehicle = $jobRecord['EquipmentTypes']['Description'];
		$jobVehicleType = $jobRecord['EquipmentTypes']['Code'];
		$jobWidth = $jobRecord['Width'];
		$jobLength = $jobRecord['Length'];
			if ( $jobWidth == '' || $jobWidth == null || $jobWidth == 0 ) 
				$jobWidth = 0;
				
			if ( $jobLength == '' || $jobLength == null || $jobLength == 0 ) 
				$jobLength = 0;
				
		$loadPaymentAmount = 0;
		if ($jobRecord['PaymentAmount'] != '' && $jobRecord['PaymentAmount'] != 'NA' && $jobRecord['PaymentAmount'] != '0') {
			$loadPaymentAmount = $jobRecord['PaymentAmount'];
		}
	
		$dataN['vehicles_Available'] = $VehiclesArray = $this->Job->FindVehicles( $jobSpec, $jobCollect, $jobDeliver, $jobVehicle, $fetchAssigedTruck ,$jobVehicleType, $jobId, $this->userId, $jobWidth, $jobLength);

		//~ $dieselFuelPrice = $this->getFuelPrice();
		$dieselFuelPrice = 2.60;
		if( !empty($VehiclesArray) ) {
			$i = 0;
			foreach( $dataN['vehicles_Available'] as $vehicle_Available ) {
				$destinationAddressArray = explode(',',$vehicle_Available['destination_address']);
				$destOriginCity = $destinationAddressArray[1];
				$destOriginState = $destinationAddressArray[0];
			
			$currentVehcileLocation = $destOriginCity.' '.$destOriginState.' USA';
			$nextJobOrigin = $jobRecord['OriginCity'].' '.$jobRecord['OriginState'].' '.$jobRecord['OriginCountry'];
			//~ $miles  = $this->getDistance($currentVehcileLocation,$nextJobOrigin);
			$miles  = $this->GetDrivingDistance($currentVehcileLocation,$nextJobOrigin);
				//~ $VehiclesArray[$i]['originToDestination'] = $jobRecord['distance']['distance'];
				$VehiclesArray[$i]['originToDestination'] = $jobRecord['Mileage'];
				
				//~ $originToDestArray = explode('mi',$jobRecord['distance']['distance']);
				//~ $originToDestDist = trim(str_replace(',','',$originToDestArray[0]));
				$originToDestDist = $jobRecord['Mileage'];
			
					$deadMileDist = $miles['distance'];
						
				$VehiclesArray[$i]['deadMileDist'] = $deadMileDist;
				$VehiclesArray[$i]['total_complete_distance'] = $originToDestDist + $deadMileDist;
				$truckAverage = (int)($vehicle_Available['fuel_consumption'] / 100);
				$VehiclesArray[$i]['fuel_consumption'] = $truckAverage;
				
				$VehiclesArray[$i]['gallon_needed'] = ceil($VehiclesArray[$i]['total_complete_distance'] / $truckAverage);
				$VehiclesArray[$i]['diesel_rate_per_gallon'] = $dieselFuelPrice;
				$VehiclesArray[$i]['comp_diesel_cost'] = $VehiclesArray[$i]['diesel_rate_per_gallon'] * $VehiclesArray[$i]['gallon_needed'];
				
				$VehiclesArray[$i]['originToDestDistDriver'] = $originToDestDist;
				$VehiclesArray[$i]['driver_dead_mile'] = $deadMileDist;
				$VehiclesArray[$i]['driver_dead_miles_paid'] = 0;
				$VehiclesArray[$i]['driver_dead_miles_not_paid'] = 0;
				$VehiclesArray[$i]['driver_pay_for_dead_mile'] = 0.45;
				$VehiclesArray[$i]['driver_dead_mile_paid'] = (float)($VehiclesArray[$i]['driver_dead_miles_paid'] * $VehiclesArray[$i]['driver_pay_for_dead_mile']);
				$VehiclesArray[$i]['driver_pay_miles_cargo'] = 0.45;
				$VehiclesArray[$i]['driver_amount_cargo'] = (float)($VehiclesArray[$i]['driver_pay_miles_cargo'] * $VehiclesArray[$i]['total_complete_distance']);
				$VehiclesArray[$i]['driver_due_driver'] = (float)($VehiclesArray[$i]['driver_amount_cargo'] + $VehiclesArray[$i]['driver_dead_mile_paid']);
				
				$VehiclesArray[$i]['tax_ifta_tax'] = 50;
				$VehiclesArray[$i]['tax_extra_stop'] = 0;
				$VehiclesArray[$i]['tax_tarps'] = 0;
				$VehiclesArray[$i]['tax_det_time'] = 0;
				$VehiclesArray[$i]['tax_tolls'] = 0;
				$VehiclesArray[$i]['tax_total_charge'] = (float)($VehiclesArray[$i]['tax_ifta_tax'] + $VehiclesArray[$i]['tax_extra_stop'] + $VehiclesArray[$i]['tax_tarps'] + $VehiclesArray[$i]['tax_det_time'] + $VehiclesArray[$i]['tax_tolls']);
				
				$VehiclesArray[$i]['overall_total_payment_amount'] = $loadPaymentAmount;
				$VehiclesArray[$i]['overall_total_charge'] = round($VehiclesArray[$i]['comp_diesel_cost'] + $VehiclesArray[$i]['driver_due_driver'] + $VehiclesArray[$i]['tax_total_charge'], 2);
				
				if ( $loadPaymentAmount != '' && $loadPaymentAmount != 0 && $loadPaymentAmount != null ) {
					$VehiclesArray[$i]['EditCalulations'] = true;
			
					
				$VehiclesArray[$i]['overall_total_profit'] = round($loadPaymentAmount - $VehiclesArray[$i]['overall_total_charge'], 2);
				$VehiclesArray[$i]['overall_total_profit_percent'] = round(($VehiclesArray[$i]['overall_total_profit'] / $loadPaymentAmount) * 100, 2);
					$VehiclesArray[$i]['overall_total_rate_mile'] = round($loadPaymentAmount / $originToDestDist, 2);
				} else {
					$VehiclesArray[$i]['EditCalulations'] = false;
					$VehiclesArray[$i]['overall_total_profit'] = '';
					$VehiclesArray[$i]['overall_total_profit_percent'] = '';
					$VehiclesArray[$i]['overall_total_rate_mile'] = '';				
				}
				$i++;
			}

			function sort_by_order($a, $b)
			{
				return $a['deadMileDist'] - $b['deadMileDist'];
			}
			usort($VehiclesArray, 'sort_by_order');
		}

		$data['vehicles_Available'] = $VehiclesArray;
		$data['total_records'] = count($data['vehicles_Available']);
		$data['jobRecord'] = $jobRecord;
		$data['truckStopId'] = $truckStopId;
		$data['fetchAssigedTruck'] = $fetchAssigedTruck;
		echo json_encode($data);
	}
	
	/*
	* Method update job information
	* 

    */
	public function edit_load($pId = null){

		$data['row'] = $this->Job->FetchSingleJob($pId);
		
		$this->data['page_title'] = 'Update Job';
		$loadDetail = $this->input->post('loadDetail');
		$jobRecord = json_decode($loadDetail,true);
		$data['truckStopId'] = $this->input->post('loadTruckId');
		if ( empty($data['row']) ) {
			$data['apiRow'] = $jobRecord;
		}
		$data['states_data'] = $this->Job->getAllStates();
		$new_view = $this->load->view('admin/jobs/edit',$data,true);
		echo $new_view;
		
		//$this->load->view('admin/admin',$this->data);
	}
	
	function GetDrivingDistance($location_ori, $location_dest) {//  AIzaSyBsZPuC5cca9gr4qPZ-p1O9hPzp3serepk 
		$location1 = urlencode($location_ori);
		$location2 = urlencode($location_dest);
		//$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&language=pl-PL";
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=$location1&destinations=$location2&key=AIzaSyBsZPuC5cca9gr4qPZ-p1O9hPzp3serepk";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response, true);		
		
		$distance = explode('mi',$response_a['rows'][0]['elements'][0]['distance']['text']);
		$estimatedTime = $response_a['rows'][0]['elements'][0]['duration']['text'];
		
		
		$this->load->model('User');
		$dataArray = array('origin'=>str_replace(' ','~',$location_ori),'destination'=>str_replace(' ','~',$location_dest),	'miles'=>ceil($distance[0]), 'estimated_time' => $estimatedTime);

		$this->User->saveData($dataArray);
		
		return array('distance' => trim($distance[0]),'time' => $estimatedTime);
	}
		
	public function getFuelPrice() {
		$diesel = 2.75;
		$exe = new SimpleXMLElement('http://www.fueleconomy.gov/ws/rest/fuelprices', NULL, TRUE);
		
		if ( $exe->diesel ) {
			return $exe->diesel;
		} else {
			return $diesel;
		}
	}

	public function get_load_data( $pickUpDate = ''){

		$obj = json_decode(file_get_contents('php://input'));
		
		$vehicleID = $obj->stateID;
		$newDest = array();
		$pickupDateDest = '';
		
		$statesAddress 	= $this->Vehicle->get_vehicle_address($vehicleID);
		if($statesAddress['destination_address'] != '')
		{
			$newDest = explode(',', $statesAddress['destination_address']);
			$this->orign_state = $newDest['0'];
			$this->orign_city = $newDest['1'];
			
			if ( isset($newDest[2]) && $newDest[2] != '' ) {
				$pickupDateDest = date('Y-m-d',strtotime($newDest[2]));
			} 	
		}
		else
		{
			array_push($newDest,$statesAddress['state'],$statesAddress['city']);
			$this->orign_state = $statesAddress['state'];
			$this->orign_city = $statesAddress['city'];
		}
		
		if ( $pickupDateDest != '' && strtotime($pickupDateDest) >= strtotime(date('Y-m-d')) ) {
			$dateTime = array (
					'dateTime' => $pickupDateDest,
				);
		} else {
			$dateTime = array (
				
			);
		}
		
		$newlabel= $statesAddress['driverName'].'-'.$statesAddress['label'].'-'.@$newDest[0].'-'.@$newDest[1];
		//$newlabel = str_replace(' ','',$newlabel);
		
		$wsdl_url = 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl'; //Live Mode Api
		$client   = new SOAPClient($wsdl_url);
		$params   = array(
			'searchRequest' => array(
				'UserName' => $this->username, 'Password' => $this->password, 'IntegrationId' => $this->id,
				'Criteria' => array(
					'OriginCity' => $this->orign_city,
					'OriginState' => $this->orign_state,//Getting records of first state(Dispatcher)
					'OriginCountry' => 'USA',
					'OriginRange' => '300',
					'OriginLatitude' => '',
					'OriginLongitude' => '',
					'DestinationState' => '',
					'DestinationCountry' =>	'USA',
					'DestinationRange' => '300',
					'EquipmentType' => $statesAddress['abbrevation'],
					'LoadType' => 'Full',
					// 'PickUpDate' => '08-25-16T00:00:00-06:00',
				
					'PickupDates' => $dateTime,
					'EquipmentOptions' => '',
					'HoursOld' => 1,
					)
				)
			);
			
		$return = $client->GetLoadSearchResults($params);
		if(empty($return->GetLoadSearchResultsResult->SearchResults)  || empty($return->GetLoadSearchResultsResult->SearchResults->LoadSearchItem)){
			$this->rows   = array();
		} else if(empty($return->GetLoadSearchResultsResult->Errors->Error)){
			$this->rows   = $return->GetLoadSearchResultsResult->SearchResults->LoadSearchItem;
		} else{			
			$data['no_result'] 	= $return->GetLoadSearchResultsResult->Errors->Error->ErrorMessage;
		}
		
		// $data['rows'] = $this->rows;

		$data['rows'] 	  = json_decode(json_encode($this->rows),true);

		$price = array();

		if ( count($data['rows']) > 1 ) {
			foreach ($data['rows'] as $key => $row)
			{
				$price[$key] = $row['Miles'];
			}
			array_multisort($price, SORT_DESC, $data['rows']);
		}
		
		$this->load->model('User');

		if ( !empty($data['rows'])) {
			foreach ($data['rows'] as $key => $value) {
				if ( $value['Payment'] != '' && $value['Payment'] != 0 && $value['Miles'] > 0 ) {
					$this->Rpm_value = round( $value['Payment'] / $value['Miles'], 2 );
				} 
			
				$data['rows'][$key]['RPM'] = $this->Rpm_value;
				$data['rows'][$key]['Age'] = '0'.$value['Age'].':00';
				$data['rows'][$key]['Weight'] = (double)$value['Weight'];
				$data['rows'][$key]['Payment'] = (double)str_replace(',','',$value['Payment']);
				
				$origin = $value['OriginCity'].','.$value['OriginState'].',USA';

				$city   = str_replace('~', '', $this->input->get('city'));
				
				$destin = $city.','.$this->input->get('state').',USA';

				@$dataMiles = $this->User->getMiles($origin,$destin);
				if(!empty($dataMiles) || $value['Miles'] < 1000){
					$data['rows'][$key]['deadmiles'] = $dataMiles;
					continue;
				}

				$miles  = $this->getDistance($origin,$destin);
				$data['rows'][$key]['deadmiles'] = $miles;			
			}
		}
		
		$newdata['rows'] = $data['rows'];
		$newdata['table_title'] = $newlabel;
		//~ pr($data);
		echo json_encode($newdata);
	}

	public function fetchAjaxStates( $country = '' ) {
		$results = $this->Job->getAllStates( $country );

		$html = '';
		$html .= '<option value="">Select State</option>';

		if ( !empty($results) ) {
			foreach ( $results as $result ) {
				$html .= "<option value='{$result['code']}'>{$result['label']}</option>";
			}
		} else {
			$html .= "No Record Found";
		}
		echo $html;
	}

	public function getDistance($addressFrom, $addressTo, $unit=null){

			//Checking in Db for locations
		$formattedAddrFrom = str_replace(' ','+',$addressFrom);
		$formattedAddrTo = str_replace(' ','+',$addressTo);

			//AIzaSyDmsROnQ2pGULMKJhMssGBI1N6uGV0BGtY

			//AIzaSyB62sujBjnYX2EIlvE0fSc_6RJ5d3h_KGs

			//Send request and receive json data

		$geocodeFrom = file_get_contents('https://maps.google.com/maps/api/geocode/json?key=AIzaSyAKrWn-U9A9LUtJn4bvsIKvSvSTs6dCixY&address='.$formattedAddrFrom.'&sensor=false');

		$outputFrom = json_decode($geocodeFrom);
		$geocodeTo = file_get_contents('https://maps.google.com/maps/api/geocode/json?key=AIzaSyAKrWn-U9A9LUtJn4bvsIKvSvSTs6dCixY&address='.$formattedAddrTo.'&sensor=false');
		$outputTo = json_decode($geocodeTo);

			//Get latitude and longitude from geo data
		$latitudeFrom = $outputFrom->results[0]->geometry->location->lat;
		$longitudeFrom = $outputFrom->results[0]->geometry->location->lng;
		$latitudeTo = $outputTo->results[0]->geometry->location->lat;
		$longitudeTo = $outputTo->results[0]->geometry->location->lng;

		//Calculate distance from latitude and longitude
		$theta = $longitudeFrom - $longitudeTo;
		$dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		$this->load->model('User');
		$dataArray = array('origin'=>str_replace(' ','~',$addressFrom),'destination'=>str_replace(' ','~',$addressTo),	'miles'=>ceil($miles));

		$this->User->saveData($dataArray);

		if ($unit == "K") {
			return ($miles * 1.609344).' km';
		} else if ($unit == "N") {
			return ($miles * 0.8684).' nm';
		} else {
			//~ return ceil($miles).' mi';
			return ceil($miles);
		}
	}

	public function get_distance_ajax(){
		
		$data = json_decode(file_get_contents('php://input'));
		
		$origin = $data->origin;

		$destin = explode('-',$data->destin);
	
		$finalDestin = @$destin[4].','.@$destin[3];

		$jsondata = $this->getDistance($origin,$finalDestin);
		echo json_encode(array('deadmiles' => $jsondata));
	}

	function createDateRangeArray($strDateFrom,$strDateTo){
	    $aryRange = array();

	    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
	    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

	    if ($iDateTo>=$iDateFrom){
	        array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
	        while ($iDateFrom<$iDateTo){
	            $iDateFrom+=86400; // add 24 hours
	            array_push($aryRange,date('Y-m-d',$iDateFrom));
	        }
	    }
	    return $aryRange;
	}
	
	public function fetch_truckstop_special_note( $truckStopId = null ) {
		$wsdl_url = 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl'; //Live Mode Api
		$client   = new SOAPClient($wsdl_url);
		$params   = array(
			'detailRequest' => array(
				'UserName' => $this->username,
				'Password' => $this->password,
				'IntegrationId' => $this->id,
				'LoadId' => $truckStopId,
				)
			);
			
		$return 	 = $client->GetLoadSearchDetailResult($params);
		$loadResults = $return->GetLoadSearchDetailResultResult->LoadDetail;

		$jobRecord = json_decode(json_encode($loadResults),true);
		$specialInfo = $jobRecord['SpecInfo'];		
		
		echo json_encode(array('specialInfo' => $specialInfo));
	}
	
	
	public function get_load_data_repeat($loadsIdArray = array(), $vehicleIDRepeat = null ){
		$objPost = json_decode(file_get_contents('php://input'),true);
		$loadsIdArray = $objPost['loadsArray'];
		$pickupDateDest = '';
		
		$vehicleDetailArray = $this->Vehicle->get_vehicles_detail($objPost['vehicleIDRepeat']);
		if ( $vehicleDetailArray['destination_address'] != '' ) {
			$vehicleDestArray = explode(',',$vehicleDetailArray['destination_address']);
			$this->orign_city = $vehicleDestArray[1];
			$this->orign_state = $vehicleDestArray[0];
			
			if ( isset($vehicleDestArray[2]) && $vehicleDestArray[2] != '' ) {
				$pickupDateDest = date('Y-m-d',strtotime($vehicleDestArray[2]));
			}
		} else {
			$this->orign_city = $vehicleDetailArray['city'];
			$this->orign_state = $vehicleDetailArray['state'];
		}
		
		if ( $pickupDateDest != '' && strtotime($pickupDateDest) >= strtotime(date('Y-m-d')) ) {
			$dateTime = array (
					'dateTime' => $pickupDateDest,
				);
		} else {
			$pickupDate = date('Y-m-d');
				$dateTime = array (
				
			);
		}		
		
		$wsdl_url = 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl'; //Live Mode Api
		$client   = new SOAPClient($wsdl_url);
		$params   = array(
			'searchRequest' => array(
				'UserName' => $this->username, 'Password' => $this->password, 'IntegrationId' => $this->id,
				'Criteria' => array(
					'OriginCity' => $this->orign_city,
					'OriginState' =>$this->orign_state,
					'OriginCountry' => 'USA',
					'OriginRange' => '300',
					'OriginLatitude' => '',
					'OriginLongitude' => '',
					'DestinationCity' => '',
					'DestinationState' => '',
					'DestinationCountry' =>	'USA',
					'DestinationRange' => '300',
					'EquipmentType' => $vehicleDetailArray['abbrevation'],
					'LoadType' => 'Full',
					'PickupDates' => $dateTime,
					'EquipmentOptions' => '',
					'HoursOld' => 1,
					)
				)
			);
		
		$return = $client->GetLoadSearchResults($params);
		
		if(empty($return->GetLoadSearchResultsResult->SearchResults)  || empty($return->GetLoadSearchResultsResult->SearchResults->LoadSearchItem)){
			$this->rows   = array();
		} else if(empty($return->GetLoadSearchResultsResult->Errors->Error)){
			$this->rows   = $return->GetLoadSearchResultsResult->SearchResults->LoadSearchItem;
		} else{			
			$data['no_result'] 	= $return->GetLoadSearchResultsResult->Errors->Error->ErrorMessage;
		}
		
		$data['rows'] 	  = json_decode(json_encode($this->rows),true);
		
		$this->load->model('User');
		
		$destin = $this->orign_city.','.$this->orign_state.',USA';
		$vehicle_fuel_consumption = $this->Vehicle->get_vehicles_fuel_consumption($this->orign_state,$this->userId);
        $truckAverage =(int)($vehicle_fuel_consumption['fuel_consumption']/100);
		$diesel_rate_per_gallon = 2.60;
		$driver_pay_miles_cargo = 0.45;
		$total_tax = 50;
		
		if ( !empty($data['rows']) ) {
			foreach ($data['rows'] as $key => $value) {
				$this->Rpm_value = 0;
				$loadWeight=0;
				if (!empty($value['Weight']))
				{
				$loadWeight = (double)$value['Weight'];	
			    }
				
				if ( $value['Length'] > 48 || $loadWeight > 48000 || $value['Miles'] < 1000 || $value['Equipment'] == 'CONG' || (in_array($value['ID'], $loadsIdArray))) {
				//~ if ( $value['Length'] > 48 || $loadWeight > 48000) {
					unset($data['rows'][$key]);
					continue;
				}
				//----------------- PROFIT CALCULATION -------------------------------------------
			
				$origin = $value['OriginCity'].','.$value['OriginState'].',USA';
				$dataMiles = $this->User->getMiles($origin,$destin);
				if(!empty($dataMiles)){
					$data['rows'][$key]['deadmiles'] = $dataMiles;
					$deadMileDist = $dataMiles;
				} else {
					$miles  = $this->GetDrivingDistance($origin,$destin);
					$data['rows'][$key]['deadmiles'] = $miles['distance'];
					$deadMileDist = $miles['distance'];
				}
				$originToDestDist = $value['Miles'];
				$total_complete_distance = $originToDestDist + $deadMileDist;
				$gallon_needed =  ceil($total_complete_distance/$truckAverage);
				$total_diesel_cost = $diesel_rate_per_gallon*$gallon_needed;
				
				$total_driver_cost = $driver_pay_miles_cargo*$total_complete_distance;
				$total_cost = round(($total_diesel_cost+$total_driver_cost+$total_tax),2);
				if ( $value['Payment'] < $total_cost&&$value['Payment']!=null&&$value['Payment']!=0) {
				unset($data['rows'][$key]);
				continue;
				}
			
			//----------------- PROFIT CALCULATION -------------------------------------------									
				if ( $value['Payment'] != '' && $value['Payment'] != 0 && is_numeric($value['Payment']) && $value['Miles'] > 0 ) {
					$this->Rpm_value = round( $value['Payment'] / $value['Miles'], 2 );
				} 
			
				$data['rows'][$key]['RPM'] = $this->Rpm_value;
				$data['rows'][$key]['Age'] = '0'.$value['Age'].':00';
						
				$origin = $value['OriginCity'].','.$value['OriginState'].',USA';
					
				$loadsIdArray[] = $value['ID'];
			
				$dataMiles = $this->User->getMiles($origin,$destin);
				if(!empty($dataMiles)){
					$data['rows'][$key]['deadmiles'] = $dataMiles;
				} else {
					$miles  = $this->GetDrivingDistance($origin,$destin);
					$data['rows'][$key]['deadmiles'] = $miles['distance'];
				}
			}
		}
		
		$newData = array();
		$newData['rows'] = array_values($data['rows']);
		$newData['loadsIdArray'] = $loadsIdArray;
		echo json_encode($newData);
		
	}

	public function get_nearby_tstops(){
		$requset = json_decode(file_get_contents('php://input'),true);

		$coords = $requset["coords"];
		$radius = (float)$requset["radius"];
		$finalCoords = array();
		$i=0;
		foreach ($coords as $key => $value) {
			
			$cursor = $this->Job->getNearByTruckStops($value['lat'],$value['lng'],$radius);
			
			if($cursor){
				foreach ($cursor as $rkey => $rvalue) {
				 	if(!$this->in_array_r($rvalue["store_id"], $finalCoords)){
						array_push($finalCoords, $rvalue);
				 	}
				 } 
			}
		}
		echo json_encode($finalCoords);
	}

	public function in_array_r($needle, $haystack, $strict = false) {
	    foreach ($haystack as $item) {
	        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
	            return true;
	        }
	    }
	    return false;
	}

	

}

//~ http://103.35.120.110:8081/1604/
