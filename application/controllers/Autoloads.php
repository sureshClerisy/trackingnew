<?php


/**
* Iterationloads Controller
*  
*/

class Autoloads extends Admin_Controller{

	private $username;
	private $password;
	private $id;
	private $accountID;
	private $rows;
	private $pickupDate;
	private $userId;
	private $origin_state;
	private $origin_city;
	private $dest_state;
	private $dest_city;
	private $Rpm_value;
	private $newOriginCity;
	private $newOriginState;
	private $drivingHours;
	private $wsdl_url;
	private $loadPickTime;
	private $loadDropTime;
	private $previousDate;
	private $multiDestinations;
	private	$finalArray;
	private	$storeTempArray;
	private	$pickupDateDest;
	private	$todayDate;
	private	$hoursRemaining;

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
			$this->origin_state  = $this->Vehicle->get_vehicles_state($this->session->admin_id);
		}
		$this->load->model('Job');
		$this->load->model('User');
		
		$this->Rpm_value = 0;
		$this->drivingHours = 8;
		$this->loadPickTime = 3;
		$this->loadDropTime = 2;
		$this->multiDestinations = '';
		$this->wsdl_url = 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl'; //Live Mode Api
		$this->finalArray = array();
		$this->storeTempArray = array();
		$this->pickupDateDest = '';
		$this->todayDate = date('m/d/y');
	
	}	


	public function index1() {
		$output = '{"loadsData":{"rows":[{"Age":"09:00","Bond":75,"BondEnabled":true,"BondTypeID":1003,"CompanyName":"MODERN
 B","Days2Pay":"9","DestinationCity":"DENVER","DestinationCountry":"USA","DestinationDistance":0,"DestinationState"
:"CO","Equipment":"F","EquipmentOptions":[],"ExperienceFactor":"A","FuelCost":"$661.58","ID":1282595797
,"IsFriend":false,"Length":"45","LoadType":"Full","Miles":"1452","OriginCity":"EUREKA","OriginCountry"
:"USA","OriginDistance":209,"OriginState":"CA","Payment":"0","PickUpDate":"10\/06\/16","PointOfContactPhone"
:"503-630-5500","PricePerGall":"2.5060","Weight":"46000","deadmiles":"289","RPM":0,"estimatedMiles":"1"
,"estimatedTime":"21 hours 8 mins","pickDate":"10\/06\/16"},{"Age":"08:00","Bond":0,"BondEnabled":false
,"BondTypeID":9999,"CompanyName":"ADVANTAG","Days2Pay":"34","DestinationCity":"windsor","DestinationCountry"
:"USA","DestinationDistance":0,"DestinationState":"co","Equipment":"F","EquipmentOptions":[],"ExperienceFactor"
:"A","FuelCost":"$499.69","ID":1282301315,"IsFriend":false,"Length":"","LoadType":"Full","Miles":"1088"
,"OriginCity":"lakeview","OriginCountry":"USA","OriginDistance":255,"OriginState":"or","Payment":"","PickUpDate"
:"10\/06\/16","PointOfContactPhone":"775-673-1101","PricePerGall":"2.5260","Weight":"48000","deadmiles"
:"356","RPM":0,"estimatedMiles":"1","estimatedTime":"15 hours 12 mins","pickDate":"10\/06\/16"},{"Age"
:"08:00","Bond":0,"BondEnabled":false,"BondTypeID":9999,"CompanyName":"3 PEAKS ","Days2Pay":"33","DestinationCity"
:"durango","DestinationCountry":"USA","DestinationDistance":0,"DestinationState":"co","Equipment":"F"
,"EquipmentOptions":{"TrailerOptionType":"Tarps"},"ExperienceFactor":"A","FuelCost":"$489.66","ID":1277356692
,"IsFriend":false,"Length":"","LoadType":"Full","Miles":"1052","OriginCity":"lakeview","OriginCountry"
:"USA","OriginDistance":255,"OriginState":"or","Payment":"","PickUpDate":"10\/06\/16","PointOfContactPhone"
:"877-293-6326","PricePerGall":"2.5600","Weight":"48000","deadmiles":"356","RPM":0,"estimatedMiles":"953"
,"estimatedTime":"14 hours 32 mins","pickDate":"10\/06\/16"},{"Age":"08:00","Bond":0,"BondEnabled":false
,"BondTypeID":9999,"CompanyName":"ADVANTAG","Days2Pay":"34","DestinationCity":"durango","DestinationCountry"
:"USA","DestinationDistance":0,"DestinationState":"co","Equipment":"F","EquipmentOptions":[],"ExperienceFactor"
:"A","FuelCost":"$489.66","ID":1277291457,"IsFriend":false,"Length":"","LoadType":"Full","Miles":"1052"
,"OriginCity":"lakeview","OriginCountry":"USA","OriginDistance":255,"OriginState":"or","Payment":"","PickUpDate"
:"10\/06\/16","PointOfContactPhone":"775-673-1101","PricePerGall":"2.5600","Weight":"48000","deadmiles"
:"356","RPM":0,"estimatedMiles":"953","estimatedTime":"14 hours 32 mins","pickDate":"10\/06\/16"}],"table_title"
:"Raxiel Tamayo-Pineda-40-CA-Sacramento","vehicleIdRepeat":"50"}}'; 

 $output = json_decode($output);
 echo json_encode($output);

	}
	public function index(){
		$newDest = array();
		$statesAddress = $this->Vehicle->get_vehicles_address($this->origin_state,$this->userId);
		
		if($statesAddress['0']['destination_address'] != '')
		{
			$newDest = explode(',', $statesAddress['0']['destination_address']);
			$this->origin_state = $newDest['0'];
			$this->origin_city = $newDest['1'];
			
			if ( isset($newDest[2]) && $newDest[2] != '' ) {
				$pickupDateDest = date('Y-m-d',strtotime($newDest[2]));
			} 	
		}
		else
		{
			array_push($newDest,$statesAddress['0']['state'],$statesAddress['0']['city']);
			$this->origin_state = $statesAddress['0']['state'];
			$this->origin_city = $statesAddress['0']['city'];
		}
		
		$labelArray = array();
		if(!empty($statesAddress) && is_array($statesAddress))
		{
			foreach($statesAddress as $state)
			{
				if($state['destination_address'] != '')
				{
					$newDestlabel = explode(',', $state['destination_address']);
				} else {
					array_push($newDestlabel,$state['state'],$state['city'],date('Y-m-d'));
				}
				$index = $state['id'];
				$index = trim($index);
				$labelArray[$index] = $state['label'];
			}
		}
		
		$hoursOld = 2;
		$newID = $statesAddress['0']['driverName'].'-'.$statesAddress['0']['label'].'-'.$newDest[0].'-'.$newDest[1];
		$vehicleIdRepeat = $statesAddress[0]['id'];		
				
		if ( $pickupDateDest != '' && strtotime($pickupDateDest) >= strtotime(date('Y-m-d')) ) {
			$dateTime = array (
					'dateTime' => $pickupDateDest,
				);
		} else {
			$pickupDate = date('Y-m-d');
				$dateTime = array ();
		}		
		
		$rows = $this->commonApiHit( $this->origin_city, $this->origin_state, $statesAddress[0]['abbrevation'], $dateTime, $hoursOld);	
			
		$this->load->model('User');
		$destin = $this->origin_city.','.$this->origin_state.',USA';

		$this->hoursRemaining = 0;
		if ( !empty($rows)) {
			$this->finalArray = $this->giveBestLoads( $rows , $destin, date('m/d/y'), $vehicleIdRepeat, $this->hoursRemaining);
		}
		
		$newData = array();
			
		$newData['rows'] =  array_values($this->finalArray);
		$newData['table_title'] =  $newID;
	
		$newData['vehicleIdRepeat'] = $vehicleIdRepeat;
		$newData['labelArray'] 	= $labelArray;
		echo json_encode(array('loadsData'=> $newData));
	}

	public function giveBestLoads( $loads = array(), $destination = '', $todayDate = '', $vehicleId = null , $hoursRem = 0 ) {
		$newLoads = array();
		$newLoadsAbove20 = array();
		$finalArray = array();
		$allLoads = array();
		$singleLoad = array();
		
		$storeTempRecord = array();
		
		if ( !empty($loads) ) {
			$vehicle_fuel_consumption = $this->Vehicle->get_vehicles_fuel_consumption($this->userId,$vehicleId);
			$truckAverage =(int)($vehicle_fuel_consumption[0]['fuel_consumption']/100);
			$diesel_rate_per_gallon = 2.60;
			$driver_pay_miles_cargo = 0.45;
			$total_tax = 50;
		/****** Count != 28 for escaping special requests ****************/
			//~ if ( count($loads) > 1 && count($loads) != 28 ) {
				
				
			if ( count($loads) > 1) {
				foreach ($loads as $val) {
					$loadWeight = (double)$val['Weight'];	
				   	if ( (double)$val['Length'] > 48 || $loadWeight > 48000 || strpos($val['Equipment'],'F') === false )
						continue;
					if ( $val['Miles'] >= 1000 ) {
						$newLoadsAbove20[] = $val;
					} else {
						$newLoads[] = $val;
					}
				}
			} else {
				$singleLoad[] = $loads[0];
			}

			if ( !empty($newLoadsAbove20)) {
				$finalArray = $newLoadsAbove20;
			} else if ( !empty($newLoads) ) {
				$finalArray = $newLoads;
			} else {
				$finalArray = $singleLoad;
			}
			
			$storeTempRecord = $newLoads;
			$this->load->helper('truckstop');
			foreach( $finalArray as $key => $value ) {
				$this->Rpm_value = 0;
				
				//----------------- PROFIT CALCULATION -------------------------------------------
				$origin = $value['OriginCity'].','.$value['OriginState'].',USA';
				$dataMiles = $this->User->getTimeMiles($origin,$destination);
				if(!empty($dataMiles)){
					$finalArray[$key]['deadmiles'] = $dataMiles['miles'];
					$finalArray[$key]['deadmilesEstTime'] = $dataMiles['estimated_time'];
					$deadMileDist = $dataMiles['miles'];
				} else {
					$miles  = $this->GetDrivingDistance($origin,$destination);
					$finalArray[$key]['deadmiles'] = $miles['distance'];
					$finalArray[$key]['deadmilesEstTime'] = $dataMiles['time'];
					$deadMileDist = $miles['distance'];
				}
				
				$originToDestDist = $value['Miles'];
				$total_complete_distance = $originToDestDist + $deadMileDist;
				$gallon_needed =  ceil($total_complete_distance / $truckAverage);
				$total_diesel_cost = $diesel_rate_per_gallon * $gallon_needed;
				
				$total_driver_cost = $driver_pay_miles_cargo * $total_complete_distance;
				$total_cost = round(($total_diesel_cost + $total_driver_cost + $total_tax),2);

				if ( $value['Payment'] != 0 && $value['Payment'] != '' && $value['Payment'] != null ) {
					if ( ((double)$value['Payment'] < (double)$total_cost) && $value['Payment'] != null ) {
						unset($finalArray[$key]);
						continue;
					}

					if ( $value['Miles'] != 0 )
					$this->Rpm_value = round( $value['Payment'] / $value['Miles'], 2 );
					$finalArray[$key]['highlight'] = 0;
					//$finalArray[$key]['percent'] = round((($value['Payment'] - $total_cost ) / $total_cost) * 100, 2);
					$finalArray[$key]['profitAmount'] = round(($value['Payment'] - $total_cost),2); 
					$finalArray[$key]['percent'] = getProfitPercent($finalArray[$key]['profitAmount'], $value['Payment']);

				//----------------- PROFIT CALCULATION -------------------------------------------	
				
				} else {
					$calPayment = getPaymentFromProfitMargin($total_cost, 30);
					$finalArray[$key]['percent'] =  30;
					$finalArray[$key]['Payment'] = $calPayment;
					if ( $value['Miles'] != 0 )
					$this->Rpm_value = round( $calPayment / $value['Miles'], 2 );
					$finalArray[$key]['highlight'] = 1;
					$finalArray[$key]['profitAmount'] =  round(($finalArray[$key]['Payment'] - $total_cost),2);
				} 
				
				$finalArray[$key]['RPM'] = $this->Rpm_value;
				$finalArray[$key]['Age'] = '0'.$value['Age'].':00';
				$finalArray[$key]['TotalCost'] = $total_cost;
				$newDestin = $value['DestinationCity'].','.$value['DestinationState'].',USA';
				
				if ( strtolower($value['PickUpDate']) == 'daily' ) {
					$finalArray[$key]['pickDate'] = $todayDate;
				} else {
					$finalArray[$key]['pickDate'] = $value['PickUpDate'];
				}
				
				$finalArray[$key]['hoursRemaining'] = $hoursRem;
			}
		
		
			if ( count($finalArray) < 3 ) {
				if ( !empty($newLoadsAbove20) ) {
					$results = $this->calculateTempRecord($storeTempRecord,$destination,$todayDate,$vehicleId, $hoursRem, $truckAverage);
					if ( !empty($results) ) {
						$finalArray = array_merge($finalArray,$results); 
					}
				}
			} 

			if ( !empty($finalArray) && count($finalArray) > 1)  {
				$havePayment = array();
				foreach ($finalArray as $key => $row)
				{
					$price[$key] = $row['profitAmount'];
					$havePayment[$key] = $row['highlight'];
				}
				array_multisort($havePayment, SORT_ASC, $price, SORT_DESC, $finalArray); //Loads with payment and have heighest profit will be on top
			}
			
		}	
		return $finalArray;
	}
	
	public function calculateTempRecord( $newLoads = array(), $destination = '', $todayDate = '' ,$vehicleId = null, $hoursRem = 0 , $truckAverage = null ) {
				
		if ( !empty($newLoads) ) {
			$diesel_rate_per_gallon = 2.60;
			$driver_pay_miles_cargo = 0.45;
			$total_tax = 50;
		/****** Count != 28 for escaping special requests ****************/
		
			$this->load->helper('truckstop');
			foreach( $newLoads as $key => $value ) {
				$this->Rpm_value = 0;
				$loadWeight = (double)$value['Weight'];	
				
				
				if ( (double)$value['Length'] > 48 || $loadWeight > 48000 || strpos($value['Equipment'],'F') === false ) {
					unset($newLoads[$key]);
					continue;
				}
				
				//----------------- PROFIT CALCULATION -------------------------------------------
				$origin = $value['OriginCity'].','.$value['OriginState'].',USA';
				$dataMiles = $this->User->getTimeMiles($origin,$destination);
				if(!empty($dataMiles)){
					$newLoads[$key]['deadmiles'] = $dataMiles['miles'];
					$newLoads[$key]['deadmilesEstTime'] = $dataMiles['estimated_time'];
					$deadMileDist = $dataMiles['miles'];
				} else {
					$miles  = $this->GetDrivingDistance($origin,$destination);
					$newLoads[$key]['deadmiles'] = $miles['distance'];
					$newLoads[$key]['deadmilesEstTime'] = $miles['time'];
					$deadMileDist = $miles['distance'];
				}
				
				$originToDestDist = $value['Miles'];
				$total_complete_distance = $originToDestDist + $deadMileDist;
				$gallon_needed =  ceil($total_complete_distance / $truckAverage);
				$total_diesel_cost = $diesel_rate_per_gallon * $gallon_needed;
				
				$total_driver_cost = $driver_pay_miles_cargo * $total_complete_distance;
				$total_cost = round(($total_diesel_cost + $total_driver_cost + $total_tax),2);
				
				if ( $value['Payment'] != 0 && $value['Payment'] != '' && $value['Payment'] != null ) {
										
					if ( $value['Payment'] < $total_cost && $value['Payment'] != null ) {
						unset($newLoads[$key]);
						continue;
					}
					if ( $value['Miles'] != 0 )
					$this->Rpm_value = round( $value['Payment'] / $value['Miles'], 2 );
					$newLoads[$key]['highlight'] = 0;
					$newLoads[$key]['profitAmount'] = round(($value['Payment'] - $total_cost),2) ;
					$newLoads[$key]['percent'] =  getProfitPercent($newLoads[$key]['profitAmount'], $value['Payment'] );
				//----------------- PROFIT CALCULATION -------------------------------------------	
				} else {
					$calPayment = getPaymentFromProfitMargin($total_cost, 30);
					$newLoads[$key]['Payment'] = $calPayment;
					
					if ( $value['Miles'] != 0 )
					$this->Rpm_value = round( $calPayment / $value['Miles'], 2 );
					$newLoads[$key]['highlight'] = 1;
					$newLoads[$key]['profitAmount'] =  round(($newLoads[$key]['Payment']  - $total_cost),2);
					$newLoads[$key]['percent'] =  getProfitPercent($newLoads[$key]['profitAmount'], $newLoads[$key]['Payment']);
				} 
					
				$newLoads[$key]['RPM'] = $this->Rpm_value;
				$newLoads[$key]['Age'] = '0'.$value['Age'].':00';
				$newLoads[$key]['TotalCost'] = $total_cost;			
				if ( strtolower($value['PickUpDate']) == 'daily' ) {
					$newLoads[$key]['pickDate'] = $todayDate;
				} else {
					$newLoads[$key]['pickDate'] = $value['PickUpDate'];
				}
				$newLoads[$key]['hoursRemaining'] = $hoursRem;
			}
		}		
			
		return $newLoads;
	}
	
	public function getIterationLoadNextDate( $vehicleID = null, $hoursLimit = null ) {
		$objPost = json_decode(file_get_contents('php://input'),true);
		
		if ( $hoursLimit != '' && $hoursLimit != null ) {
			$loadInfo = $objPost['valueArray'];
			$this->origin_city = $loadInfo['OriginCity'];
			$this->origin_state = $loadInfo['OriginState'];
			$this->pickupDate = preg_replace('/\s+/', '',$loadInfo['OriginPickupDate']);
			$this->drivingHours = $hoursLimit;
			$driverName = $loadInfo['driver_name'];
			$originPickupDate = $loadInfo['OriginPickupDate'];
			$deadmilesEstTime = $loadInfo['deadmilesEstTime'];
			$origin = $loadInfo['origin'];
			$newDestin = $loadInfo['newDestin'];
		} else {
			$loadInfo = $objPost['valueArray'];
		
			$this->origin_city = $loadInfo['DestinationCity'];
			$this->origin_state = $loadInfo['DestinationState'];
			$this->pickupDate = preg_replace('/\s+/', '',$loadInfo['pickDate']);
			$driverArray = explode('-',$objPost['DriverName']);
			$driverName = trim($driverArray[0]);
			$originPickupDate = $loadInfo['pickDate'];
			$deadmilesEstTime = $loadInfo['deadmilesEstTime'];
			
			$origin = $loadInfo['OriginCity'].','.$loadInfo['OriginState'].',USA';
			$newDestin = $loadInfo['DestinationCity'].','.$loadInfo['DestinationState'].',USA';
		}
		
		$this->hoursRemaining = $loadInfo['hoursRemaining'];
				
		$dataMileTime = $this->User->getTimeMiles($origin,$newDestin);
		if(!empty($dataMileTime)){
			$calEstimatedTime = $dataMileTime['estimated_time'];					
		} else {
			$totalTimeArray = $this->GetDrivingDistance($origin,$newDestin);
			$calEstimatedTime = $totalTimeArray['time'];
		}
		
		$previousDate = $objPost['previousDate'];
		
		if ( $this->pickupDate == '' || strtolower($this->pickupDate) == 'daily' ) {
			$this->pickupDate = $previousDate;
			$originFirstDate = date('Y-m-d');
		} else {
			$originFirstDate = date('Y-m-d',strtotime($this->pickupDate));
		}	
		
		$pickupDateDestArray = $this->estimatedTime( $calEstimatedTime , $deadmilesEstTime, $this->pickupDate, $this->drivingHours, $vehicleID, $this->hoursRemaining);
		
		$previousDate = $pickupDateDestArray[0];
		
		$nextData = array();
		$nextData['ID'] = $loadInfo['ID'];
		$nextData['PickUpDate'] = $loadInfo['PickUpDate'];
		$nextData['OriginDistance'] = $loadInfo['OriginDistance'];
		$nextData['OriginCity'] = $this->origin_city;
		$nextData['OriginState'] = $this->origin_state;
		$nextData['OriginPickupDate'] = $originPickupDate;
		$nextData['nextPickupDate'] = $pickupDateDestArray[0];
		$nextData['nextPickupDate1'] = $pickupDateDestArray[0];
		$nextData['estimatedTime'] = $calEstimatedTime;
		$nextData['Equipment'] = $loadInfo['Equipment'];
		$nextData['driver_name'] = $driverName;
		$nextData['multiDestinations'] = '';
		$nextData['originFirstDate'] = $originFirstDate;
		$nextData['previousDate'] = $previousDate;
		$nextData['deadmilesEstTime'] = $deadmilesEstTime;
		$nextData['origin'] = $origin;
		$nextData['newDestin'] = $newDestin;
		$nextData['opreviousDate'] = $objPost['previousDate'];
		$nextData['compWorkingHours'] = $pickupDateDestArray[1];
		$nextData['hoursRemaining'] = $pickupDateDestArray[3];
		
		echo json_encode($nextData);
	}
	
	public function getIterationLoad ( $vehicleId = null )  {
		
		$loadInfo = json_decode(file_get_contents('php://input'),true);
		
		$this->origin_city = $loadInfo['OriginCity'];
		$this->origin_state = $loadInfo['OriginState'];
		$this->pickupDate = $loadInfo['nextPickupDate'];
		$this->hoursRemaining = $loadInfo['hoursRemaining'];
		
		if ( $this->pickupDate != '' && strtotime($this->pickupDate) >= strtotime(date('Y-m-d')) ) {
			$dateTime = array (
					'dateTime' => $this->pickupDate,
				);
			$todayDate = $this->pickupDate;
		} else {
			$dateTime = array ();
			$todayDate = date('m/d/y');
		}		
		
		if ( isset($loadInfo['multiDestinations']) && $loadInfo['multiDestinations'] != '' ) {
			$this->multiDestinations = implode(',',$loadInfo['multiDestinations']);
		} 
		
		
		$client   = new SOAPClient($this->wsdl_url);

		$params   = array(
			'searchRequest' => array(
				'UserName' => $this->username, 'Password' => $this->password, 'IntegrationId' => $this->id,
				'Criteria' => array(
					'OriginCity' => $this->origin_city,
					'OriginState' =>$this->origin_state,
					'OriginCountry' => 'USA',
					'OriginRange' => '300',
					'OriginLatitude' => '',
					'OriginLongitude' => '',
					'DestinationCity' => '',
					'DestinationState' => $this->multiDestinations,
					'DestinationCountry' =>	'USA',
					'DestinationRange' => '300',
					'EquipmentType' => $loadInfo['Equipment'],
					'LoadType' => 'Full',
					'PickupDates' => $dateTime,
					'EquipmentOptions' => '',
					//~ 'HoursOld' => 9
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
		
		if(count($this->rows) == 1){
			$data['rows'][] 	  = json_decode(json_encode($this->rows),true);
		}else{
			$data['rows'] 	  = json_decode(json_encode($this->rows),true);
		}
		
		$destin = $this->origin_city.','.$this->origin_state.',USA';
		
		if ( !empty($data['rows'])) {
			$this->finalArray = $this->giveBestLoads( $data['rows'] , $destin, $todayDate, $vehicleId, $this->hoursRemaining);
		}
				
		$newData = array();
			
		$data['rows'] = array_values($this->finalArray);
		
		$newData['rows'] =  $data['rows'];
		$newData['currentPickupDate'] =  $loadInfo['nextPickupDate'];
		
		echo json_encode($newData);
	}
	
	public function estimatedTime ( $time = '' , $deadmilesEstTime = '' , $pickupDate = '0000-00-00', $hoursLimit = null , $vehicleID = null , $hoursRem = 0 ) {
		
		$timeDay = 0;
		$timeDayDead = 0;
		$timeMin = 0;
		$hoursRemaining = 0;
		
		if ( strpos($time, 'day') !== false ) {
			$timeArray = explode( 'day',$time);
			$timeDay = trim($timeArray[0]);
			
			if ( strpos($timeArray[1], 'hour') !== false ) { 
				$timeArrayNew = explode( 'hour',$timeArray[1]);
				$timeHour = trim($timeArrayNew[0]);
			}
		} else if ( strpos($time, 'days' ) !== false ) {
			$timeArray = explode ( 'days', $time );
			$timeDay = trim($timeArray[0]);
			
			if ( strpos($timeArray[1], 'hour') !== false ) { 
				$timeArrayNew = explode( 'hour',$timeArray[1]);
				$timeHour = trim($timeArrayNew[0]);
			}
		} else if ( strpos($time, 'hours') !==  false ) {
			$timeArray = explode ( 'hours', $time );
			$timeHour = trim($timeArray[0]);
			
			if ( strpos($timeArray[1], 'mins') !== false ) { 
				$timeArrayNew = explode( 'mins',$timeArray[1]);
				//~ if ( trim($timeArrayNew[0] > 20 ) ) {
					//~ $timeHour = $timeHour + 1;
				//~ } else {
					$timeMin += $timeArrayNew[0];
				//~ }
			}
		} else {
			$timeArrayNew = explode( 'hour',$time);
			$timeHour = trim($timeArrayNew[0]);
		}
		
		
		if ( strpos($deadmilesEstTime, 'day') !== false ) {
			$timeArray = explode( 'day',$deadmilesEstTime);
			$timeDayDead = trim($timeArray[0]);
			
			if ( strpos($timeArray[1], 'hour') !== false ) { 
				$timeArrayNew = explode( 'hour',$timeArray[1]);
				$timeHourDead = trim($timeArrayNew[0]);
			}
		} else if ( strpos($deadmilesEstTime, 'hours') !==  false ) {
			$timeArray = explode ( 'hours', $deadmilesEstTime );
			$timeHourDead = trim($timeArray[0]);
			
			if ( strpos($timeArray[1], 'mins') !== false ) { 
				$timeArrayNew = explode( 'mins',$timeArray[1]);
				//~ if ( trim($timeArrayNew[0] > 20 ) ) {
					//~ $timeHourDead = $timeHourDead + 1;
				//~ } else {
					$timeMin += $timeArrayNew[0];
				//~ }
			}
		} else {
			$timeArrayNew = explode( 'hour',$deadmilesEstTime);
			$timeHourDead = trim($timeArrayNew[0]);
		}
	
		//~ if ( $getNextDateFor == 1 ) 
			//~ $hoursRemaining = $this->User->getHoursRemaining( $this->userId, $vehicleID );
		
		//$this->hoursRemaining = $hoursRem;
		$totalDrivingHour = ( $timeDay * 24 ) + $timeHour + ($timeDayDead * 24 ) + $timeHourDead;
		if ( $timeMin > 0 && $timeMin != '' ) {
			$minHour = round( $timeMin / 60 );
			$totalDrivingHour += $minHour;
		}
		
		$totalHours = $totalDrivingHour + $this->loadDropTime + $this->loadPickTime;
		//~ $nextPickupDays =  (int)($totalHours / $hoursLimit);
		$nextPickupDays =  round($totalHours / $hoursLimit);
		$nextDaysHoursLeft =  ($totalHours % $hoursLimit);
		
		//~ if ( $getNextDateFor == 1 ) 
			//~ $this->User->saveRemainingHours( $this->userId, $vehicleID , $nextDaysHoursLeft );
				
		$date = strtotime("+".$nextPickupDays." days", strtotime($pickupDate));
		$newPickupDate = date("Y-m-d", $date);
		
		return array($newPickupDate,$totalHours,$totalDrivingHour,$nextDaysHoursLeft);	
	}
	
	function GetDrivingDistance($location_ori, $location_dest) {//  AIzaSyBsZPuC5cca9gr4qPZ-p1O9hPzp3serepk 
		$location1 = urlencode($location_ori);
		$location2 = urlencode($location_dest);
		//$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&language=pl-PL";
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=$location1&destinations=$location2&key=AIzaSyDiojAPmOWusjvasUhm5wKswtCRtkEKyi8";
		
		
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
		
	public function destroyLoadsChain(){
		$this->Job->destroyLoadsChain();
		echo json_encode(array("success"=>TRUE));
	}	

	public function removeFromChain(){
		$objPost = json_decode(file_get_contents('php://input'),true);
		$this->Job->removeElementFromChain($objPost['deletedRowIndex'],$objPost['ID']);
		echo json_encode(array("success"=>TRUE));
	}
	
	public function getChangeDriverChains() {
	
		$obj = json_decode(file_get_contents('php://input'));

		$vehicleID = $obj->driverInfo;
		$driverVehicleInfoArray = $this->getDriverVehicleInfo( $vehicleID);
		
		if ( $driverVehicleInfoArray[2] != '' && strtotime($driverVehicleInfoArray[2]) >= strtotime(date('Y-m-d')) ) {
			$dateTime = array (
					'dateTime' => date('Y-m-d',strtotime($driverVehicleInfoArray[2])),
				);
			$this->todayDate = $driverVehicleInfoArray[2];
		} else {
			$dateTime = array (
				
			);
		}
			
		$rows = $this->commonApiHit( $driverVehicleInfoArray[0], $driverVehicleInfoArray[1], $driverVehicleInfoArray[3], $dateTime, '');	
		$this->load->model('User');
		$destin = $driverVehicleInfoArray[0].','.$driverVehicleInfoArray[1].',USA';
		
		$this->hoursRemaining = 0;
		if ( !empty($rows) ) {
			$this->finalArray = $this->giveBestLoads( $rows , $destin, $this->todayDate, $vehicleID , $this->hoursRemaining);
		}
		
		$saveHours = $this->User->saveRemainingHours( $this->userId, $vehicleID , 0 );
		$newdata = array();
		$newdata['rows'] = array_values($this->finalArray);
		$newdata['table_title'] = $driverVehicleInfoArray[4];
		$newdata['vehicleIdRepeat'] = $vehicleID;
		
		echo json_encode(array('loadsData'=> $newdata),JSON_NUMERIC_CHECK);
	}
	
	public function getDriverVehicleInfo( $vehicleID = null ) {
		$statesAddress 	= $this->Vehicle->get_vehicle_address($vehicleID);
		if($statesAddress['destination_address'] != '')
		{
			$newDest = explode(',', $statesAddress['destination_address']);
			$this->orign_state = $newDest['0'];
			$this->orign_city = $newDest['1'];
			
			if ( isset($newDest[2]) && $newDest[2] != '' ) {
				$this->pickupDateDest = date('Y-m-d',strtotime($newDest[2]));
			} 	
		}
		else
		{
			array_push($newDest,$statesAddress['state'],$statesAddress['city']);
			$this->orign_state = $statesAddress['state'];
			$this->orign_city = $statesAddress['city'];
		}	
		
		$newlabel= $statesAddress['driverName'].'-'.$statesAddress['label'].'-'.@$newDest[0].'-'.@$newDest[1];
		
		return array( $this->orign_city, $this->orign_state, $this->pickupDateDest, $statesAddress['abbrevation'], $newlabel );
	}
	
	public function commonApiHit( $orign_city = '' , $orign_state = '', $abbreviation = '', $dateTime = array() , $hoursOld = '') {
		$client   = new SOAPClient($this->wsdl_url);
		$params   = array(
			'searchRequest' => array(
				'UserName' => $this->username, 'Password' => $this->password, 'IntegrationId' => $this->id,
				'Criteria' => array(
					'OriginCity' => $orign_city,
					'OriginState' => $orign_state,//Getting records of first state(Dispatcher)
					'OriginCountry' => 'USA',
					'OriginRange' => '300',
					'OriginLatitude' => '',
					'OriginLongitude' => '',
					'DestinationState' => '',
					'DestinationCountry' =>	'USA',
					'DestinationRange' => '300',
					'EquipmentType' => $abbreviation,
					'LoadType' => 'Full',
					'PickupDates' => $dateTime,
					'EquipmentOptions' => '',
					'HoursOld' => $hoursOld,
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
		
		$this->finalArray  = json_decode(json_encode($this->rows),true);
		return $this->finalArray;
	}

}


