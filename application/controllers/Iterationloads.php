<?php


/**
* Iterationloads Controller
*  
*/

class Iterationloads extends Admin_Controller{

	public $id;
	public $rows;
	public $pickupDate;
	public $userId;
	public $origin_state;
	public $origin_city;
	public $dest_state;
	public $dest_city;
	public $Rpm_value;
	public $newOriginCity;
	public $newOriginState;
	public $loadPickTime;
	public $loadDropTime;
	public $previousDate;
	public $multiDestinations;
	private	$storeTempArray;
	private	$pickupDateDest;
	private	$todayDate;
	private	$hoursRemaining;
	private $drivingDayLimit;
	private $drivingDayLimitTeam;
	private $weeklyDrivingHours;
	private $loadsIdArray;
	public $diesel_rate_per_gallon;
	public $driver_pay_miles_cargo;
	public $driver_pay_miles_cargo_team;
	public $defaultTruckAvg;
	public $total_tax;
	public $calPickDate;					// pickdate passed for daily loads

	function __construct(){

		parent::__construct();
		$this->withAdminRole 	= $this->config->item('with_admin_role');
		$this->userRoleId 		= $this->session->role;
		$this->pickupDate 		= date('Y-m-d');
		$this->weekDays 		= $this->config->item('WEEKDAYS');	
		
		$this->load->model(array('Vehicle','Job','User','Driver'));
		$this->load->helper('truckstop');
		$this->userId 		= $this->session->loggedUser_id;
		$this->origin_state  = $this->Vehicle->get_vehicles_state($this->session->admin_id);
		
		$this->weeklyDrivingHours 	= 70;
		$this->drivingDayLimit 		= 8; 		//Hours in time format
		$this->drivingDayLimitTeam 	= 11; 	//Hours in time format
		$this->Rpm_value 	= 0;
		$this->loadPickTime = 2;
		$this->loadDropTime = 2;
		$this->multiDestinations = '';
		$this->storeTempArray 	= array();
		$this->pickupDateDest 	= '';
		$this->todayDate 		= date('m/d/y');
		
		$this->diesel_rate_per_gallon 	= 2.50;
		$this->driver_pay_miles_cargo 	= 0.45;		// For individual
		$this->driver_pay_miles_cargo_team = 0.58; //For drivers team
		$this->defaultTruckAvg 			= 6;
		$this->total_tax 				= 50;
		//~ $this->hoursRemaining = 0;
		$this->calPickDate 				= date('m/d/y');
		
	}	

	/**
	 * Fetching loads from vehicle last load
	 */ 
	
	public function index(){
		$currentDateTime = '';
		$pickupDateDest = '';
		
		$userId = false;
		$parentId = $this->userId;
		$childIds  = array();
		
		if ( $this->userRoleId == 4 ) {
			$parentIdCheck = $this->session->userdata('loggedUser_parentId');
			if( isset($parentIdCheck) && $parentIdCheck != 0 ) {
				$userId = $parentIdCheck;
				$parentId = $parentIdCheck;
			}
		}

		$childIds = $this->User->fetchDispatchersChilds($this->userId);

		$gVehicleId = false;
		$driverType = "driver";
		$gDropdown 	= array();
	 
		if(isset($_COOKIE["_globalDropdown"])){

			$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
			if ( isset($gDropdown["label"]) && $gDropdown["label"] == "_idispatcher" ) {
				$gVehicleId = false;
			} else if ( !isset($gDropdown["label"]) || empty(trim($gDropdown["label"])) ) {
				$gVehicleId = false;
			} else {
				$gVehicleId = $gDropdown["vid"];
			}
			$vehParentId = isset($gDropdown['dispId']) ? $gDropdown['dispId'] : '';
		 $driverType = isset($gDropdown["label"]) && ($gDropdown["label"] == "team" || $gDropdown["label"] == "_team") ? "team" : "driver";
		} else {
			$vehParentId = $parentId;
		}
	
		$statesAddress = $this->Vehicle->get_vehicles_address($vehParentId,$gVehicleId);
		if(!empty($statesAddress)){
			if(!empty($statesAddress[0]["teamDriverName"])){
				$driverType = "team";
			}
			$results = $this->Vehicle->getLastLoadRecord($statesAddress[0]['id']);
			if ( !empty($results) ){
				$pickupDateDest 	= $this->CalculateNextLoadDate($results);
				$this->origin_state = $results['DestinationState'];
				$this->origin_city 	= $results['DestinationCity'];
				$country 			= $results['DestinationCountry'];
			} else {
				$this->origin_state = $statesAddress['0']['state'];
				$this->origin_city 	= $statesAddress['0']['city'];
				$country = 'USA';
			}

			//-----------------------------------
			$userId = false;
			if($this->userRoleId == _DISPATCHER){
				$userId = $this->userId;
			} else if ( $this->userRoleId == 4 ) {
				$userId = $parentId;
			}
			
			$vehicleList 	= $this->Driver->getDriversList($userId,true);
			$teamList 		= $this->Driver->getDriversListAsTeam($userId);
			
			if ( !empty($childIds)) {
				foreach($childIds as $child ) {
					$childVehicles = $this->Driver->getDriversList($child['id'],true);
					$vehicleList = array_merge($vehicleList,$childVehicles);	
					
					$childVehicles = $this->Driver->getDriversListAsTeamNew($child['id']);
					$teamList = array_merge($teamList,$childVehicles);
				}
			}

			if(is_array($teamList) && count($teamList) > 0){
				foreach ($teamList as $key => $value) {
					$value["label"] = "_team";
					array_unshift($vehicleList, $value);
				}
			}

			if (isset($gDropdown["label"]) && ($gDropdown["label"] != "_idispatcher" && $gDropdown["label"] != "_iall" && $gDropdown["label"] != ""))    {
				$dName = $gDropdown["driverName"];
			}else{ 
				if(!empty($statesAddress[0]["teamDriverName"])){
					$dName = $statesAddress[0]["teamDriverName"].'-'.$statesAddress[0]['label'];
				}else{
					$dName = $statesAddress[0]['driverName'].'-'.$statesAddress[0]['label'];
				}
				
			}
			
			$hoursOld 		 = '';
			$vehicleIdRepeat = $statesAddress[0]['id'];
			
			$this->hoursRemaining 	= 0;
			$previousDate 			= date('m/d/y');
			$unFinishedChain 		= $this->getUnFinishedChain($vehicleIdRepeat);

			if(count($unFinishedChain) > 0){
				$savedChain 		= end($unFinishedChain[0]);
				$this->origin_city 	=  $savedChain['encodedJobRecord']['DestinationCity'];
				$this->origin_state = $savedChain['encodedJobRecord']['DestinationState'];
				$abbr 				= $savedChain['encodedJobRecord']['EquipmentTypes']['Code'];
				$pickupDateDest 	= $savedChain['valuesArray']['nextPickupDate1'];
				$this->hoursRemaining = $savedChain['valuesArray']['hoursRemainingNextDay'];
				$previousDate 		= date('m/d/y',strtotime($pickupDateDest)); 
				$hoursOld 			= '';
			}
			
			$newID = $dName.'-'.$this->origin_city.'-'.$this->origin_state;
			$dateTime = array ();
			if ( $pickupDateDest != ''  ) {
				if ( strpos($pickupDateDest, ',') !== false ) {
					$datesNewArray = explode(',',$pickupDateDest);
					$pickupDate = '';
					for ( $i = 0; $i < count($datesNewArray);  $i++ ) {
						$dateTime[] = $datesNewArray[$i];
						$pickupDate .= $datesNewArray[$i].',';
					}
					
					$this->todayDate = date('m/d/y',strtotime($datesNewArray[0]));
				} else {
					if ( strtotime($pickupDateDest) >= strtotime(date('Y-m-d')) )  {
						$dateTime = array (
							'dateTime' => $pickupDateDest,
						);
						$this->todayDate = date('m/d/y',strtotime($pickupDateDest));
					}
				}
			} else {
				$pickupDateDest = $pickupDate 	= date('Y-m-d');
			}

			$deadMilesOriginLocation = $this->origin_city.','.$this->origin_state.', '.$country; 
			$data['rows'] = $this->commonApiHits( $statesAddress['0']['vehicle_type'], $dateTime, $hoursOld);	
				
			$price = array();
			$loadsIdArray = array();
			
			$this->loadsIdArray = array();
			if ( !empty($data['rows'])) {
				$returnArray = $this->giveBestLoads( $data['rows'], $this->todayDate, $vehicleIdRepeat, $this->hoursRemaining,$this->loadsIdArray,$statesAddress['0']['vehicle_type'],$driverType);
				$this->finalArray = $returnArray[0];
				$loadsIdArray = $returnArray[1];
			}


			$newData = array();
			$newData['rows'] =  array_values($this->finalArray);
			$newData['table_title'] =  $newID;
			$newData['vehicleIdRepeat'] = $vehicleIdRepeat;
			$newData['unFinishedChain'] = $unFinishedChain;
			$newData['labelArray'] 	= $vehicleList;
			$newData['loadsIdArray'] = $loadsIdArray;
			$newData["selectedDriver"] = $dName;
			$newData['originCitySearch'] = $this->origin_city;
			$newData['originStateSearch'] = $this->origin_state;
			$newData['deadMilesOriginLocation'] =  $deadMilesOriginLocation;
			$newData['originDateSearch'] = $pickupDateDest;
			$newData['driverType'] = $driverType;
			
			echo json_encode(array('loadsData'=> $newData));
		} else {
			echo json_encode(array('loadsData'=> false));
		}
	}
	
	/**
	 * Calculate Loads next date 
	 */
	
	private function CalculateNextLoadDate( $result = array() ) {
		if ( $result['DeliveryDate'] != '' && $result['DeliveryDate'] != '0000-00-00' && $result['DeliveryDate'] != null ) {
			$dateSearch = $result['DeliveryDate'];
		} else {
			$dateSearch = date('Y-m-d', strtotime($result['pickday'] . ' +1 day'));
		} 
		
		if ( $dateSearch >= date('Y-m-d') ){
			$searchDate = $dateSearch;
			for ( $i = 1; $i < 3; $i++ ) {					// searching loads for next 5 days
				$searchDate .= ','.date('Y-m-d', strtotime($dateSearch . ' +'.$i.' day'));
			}
			
		} else {
			$searchDate = '';
		}
		
		return $searchDate;
	}

	private function getUnFinishedChain($driverID){
		$allChains = $this->Job->getUnFinishedChain($driverID);
		$userChain = array();
		
		if ( !empty($allChains) ) {
			foreach ($allChains as $key => $chain) {
				foreach ($chain as $laneKEy => $laneValue) {
					$loadDetail = getLoadDetail($laneValue, null, $this->username, $this->password, $this->id, $this->Job);
					if(empty($loadDetail['encodedJobRecord']['ID']) && $key == 0){
						$this->destroyLoadsChain($driverID);
					}else{
						$userChain[$key][] = $loadDetail;
					}
				}
			}
		}
		return $userChain;
	}

	private function giveBestLoads( $loads = array(),$todayDate = '', $vehicleId = null , $hoursRem = 0, $loadsIdArray = array(), $abbreviation = '',$driverType="driver") {	// abbreviation to filer records with particular search type

		$newLoads 			= array();
		$finalArray 		= array();
		$storeTempRecord 	= array();

		if ( !empty($loads) ) {
			
			$truckAverage 	= $this->defaultTruckAvg;
			$defaultWeight 	= 48000;
			$defaultLength 	= 45;

			if ( $vehicleId != '' && $vehicleId != null ) {
				$vehicle_details = $this->Vehicle->get_vehicles_fuel_consumption($this->userId,$vehicleId);
				if ( !empty($vehicle_details) ) {
					$truckAverage =round( 100/$vehicle_details[0]['fuel_consumption'],2);
					//$truckAverage =(int)($vehicle_details[0]['fuel_consumption']/100);
					$defaultWeight = ( $vehicle_details[0]['cargo_capacity'] != '' ) ? $vehicle_details[0]['cargo_capacity'] : $defaultWeight;
					$defaultLength = ( $vehicle_details[0]['cargo_bay_l'] != '' ) ? $vehicle_details[0]['cargo_bay_l'] : $defaultLength;
				}
			} 				
			
			if ( count($loads) > 1) {
				$this->load->model('BrokersModel');
				
				// $blackListedBrokers = $this->BrokersModel->getListOfBlacklistedBrokers();
				$blackListedBrokers = $this->BrokersModel->getListOfBlacklisted(); //Getting all blacklisted compnies array

				foreach ($loads as $key => $value) {
					
					$loadWeight = str_replace(',','',$value['Weight']);
					if ( strlen($loadWeight) == 2 ) {
						$loadWeight = $loadWeight * 1000;
					} else if ( strpos($loadWeight, 'K') !== false ){
						$loadWeight = str_replace('K','',$loadWeight);
						$loadWeight = trim($loadWeight) * 1000;
					} else if ( strpos($loadWeight, '.') !== false ) {
						$loadw = explode('.',$loadWeight);
						if ( strlen($loadw[0]) < 3 )
							$loadWeight = $loadWeight * 1000;
					} 
					
					$loadWeight = (double)$loadWeight;
					if ( strpos($value['Length'], 'FT') !== false ){
						$length = explode('FT',$value['Length']);
						$value['Length'] = $length[0];
					}
					
					// $cmpnumber 	= str_replace('-','',$val['PointOfContactPhone']);
					$compayName = strtolower($value['CompanyName']);
					
				   	if ( (double)$value['Length'] > $defaultLength || $loadWeight > $defaultWeight || (in_array($value['ID'], $loadsIdArray)) || (in_array($compayName, $blackListedBrokers)) ) {
				   		continue;
					}

					$want_to_see = explode(",", $abbreviation);
					$current_user_can_see = explode(",", $value['Equipment']);
					$show = array_intersect($want_to_see, $current_user_can_see);
					
					if ( empty($show) )  {					
						continue;
					}
					
					$value['Weight'] = $loadWeight;
			
					if ( $value['Miles'] >= 500 ) {
						$finalArray[$key] = $value;
						$this->Rpm_value = 0;
						$loadWeight = (double)$value['Weight'];	
						
						$finalArray[$key]['deadmiles'] = $value['OriginDistance'];
						$total_complete_distance = $value['Miles'] + $finalArray[$key]['deadmiles'];
						$gallon_needed =  ($total_complete_distance / $truckAverage);
						
						$total_diesel_cost = $this->diesel_rate_per_gallon * $gallon_needed;
						
						$driverPayMileCargo = $this->driver_pay_miles_cargo;
						if($driverType == "team"){
							$driverPayMileCargo = $this->driver_pay_miles_cargo_team;
						}
						
						$total_driver_cost = $driverPayMileCargo * $total_complete_distance;
						$total_cost = round(($total_diesel_cost + $total_driver_cost + $this->total_tax),2);
						if ( $value['Payment'] != 0 && $value['Payment'] != '' && $value['Payment'] != null ) {
							if ( ((double)$value['Payment'] < (double)$total_cost) && $value['Payment'] != null ) {
								unset($finalArray[$key]);
								continue;
							}

							if ( $value['Miles'] != 0 )
							$this->Rpm_value = round( $value['Payment'] / $value['Miles'], 2 );
							$finalArray[$key]['highlight'] = 0;
							$finalArray[$key]['profitAmount'] = round(($value['Payment'] - $total_cost),2); 
							$finalArray[$key]['percent'] = getProfitPercent($finalArray[$key]['profitAmount'], $value['Payment']);
							$finalArray[$key]['Payment'] = (float)$value["Payment"];
						} else {
							$calPayment = getPaymentFromProfitMargin($total_cost, 30);
							$finalArray[$key]['percent'] =  30;
							$finalArray[$key]['Payment'] = (float)$calPayment;
							if ( $value['Miles'] != 0 )
							$this->Rpm_value = round( $calPayment / $value['Miles'], 2 );
							$finalArray[$key]['highlight'] = 1;
							$finalArray[$key]['profitAmount'] =  round(($finalArray[$key]['Payment'] - $total_cost),2);
						} 
						
						$finalArray[$key]['RPM'] = $this->Rpm_value;
						$finalArray[$key]['FuelCost'] = trim(str_replace('$','',$value["FuelCost"]));
						$finalArray[$key]['Miles'] =(float)trim(str_replace(',','',$value["Miles"]));
						$finalArray[$key]['deadmiles'] = (float)trim(str_replace(',','',$finalArray[$key]['deadmiles']));
						
						if ( $value['Age'] != '9+' ) {
							$AGE = '0'.$value['Age'].':00';
						} else {
							$AGE = '9:00+';
						}

						$finalArray[$key]['Age'] = $AGE;
						$finalArray[$key]['TotalCost'] = $total_cost;
						$newDestin = $value['DestinationCity'].','.$value['DestinationState'].',USA';
						
						if ( strtolower($value['PickUpDate']) == 'daily' ) {
							$finalArray[$key]['pickDate'] = $todayDate;
							$finalArray[$key]['displayPickUpDate'] = $value['PickUpDate'];
						} else {
							$finalArray[$key]['pickDate'] = $value['PickUpDate'];
							$finalArray[$key]['displayPickUpDate'] = date('Y-m-d',strtotime($value['PickUpDate']));	
						}
						//------------------ Add class on job if open first time ---------------------------------
						$_COOKIE_NAME = 'VISIT_'.$value['ID'].'_'.str_replace("/", "_", $finalArray[$key]['pickDate']);
						if(isset($_COOKIE[$_COOKIE_NAME])) {
							$finalArray[$key]['visited']	= true;
						}else{
							$finalArray[$key]['visited'] = false;
						}
						//------------------ Add class on job if open first time ---------------------------------
						$finalArray[$key]['hoursRemaining'] = $hoursRem;
						$finalArray[$key]['hoursRemainingNextDay'] = $hoursRem;
						$this->loadsIdArray[] = $value['ID'];	
					} else {
						$storeTempRecord[] = $value;
					}
				}
				
				if ( count($finalArray) < 1 ) {
					if ( !empty($storeTempRecord) ) {
						$results = $this->calculateTempRecord($storeTempRecord,$todayDate,$vehicleId, $hoursRem, $loadsIdArray,$driverType, $truckAverage);
						if ( !empty($results) ) {
							$finalArray = array_merge($finalArray,$results[0]); 
							$this->loadsIdArray = array_merge($this->loadsIdArray,$results[1]);
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
			
			} else {
				$finalArray[] = $loads;
			}
		}
		
		return array($finalArray, $this->loadsIdArray);
	}
	
	private function calculateTempRecord( $loads = array(), $todayDate = '' ,$vehicleId = null, $hoursRem = 0, $loadIdArr = array(), $driverType = "driver", $truckAverage = 6 ) {
		$newLoads = array();
		$loadArray = array();
				
		if ( !empty($loads) ) {
			$newLoads = $loads;
			foreach( $newLoads as $key => $value ) {
				$this->Rpm_value = 0;
						
				//----------------- PROFIT CALCULATION -------------------------------------------

				$newLoads[$key]['deadmiles'] = $value['OriginDistance'];
				$total_complete_distance = $value['Miles'] + $newLoads[$key]['deadmiles'];
				$gallon_needed =  ($total_complete_distance / $truckAverage);
				$total_diesel_cost = $this->diesel_rate_per_gallon * $gallon_needed;
				
				//----------- Code for team task ---------------------------
				$driverPayMileCargo = $this->driver_pay_miles_cargo;
				if($driverType == "team"){
					$driverPayMileCargo = $this->driver_pay_miles_cargo_team;
				}
				//----------- Code for team task ---------------------------


				$total_driver_cost = $driverPayMileCargo * $total_complete_distance;
				$total_cost = round(($total_diesel_cost + $total_driver_cost + $this->total_tax),2);
				
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
					$newLoads[$key]['Payment'] = (float)$value["Payment"];
				//----------------- PROFIT CALCULATION -------------------------------------------	
				} else {
					$calPayment = getPaymentFromProfitMargin($total_cost, 30);
					$newLoads[$key]['Payment'] = (float)$calPayment;
					
					if ( $value['Miles'] != 0 )
					$this->Rpm_value = round( $calPayment / $value['Miles'], 2 );
					$newLoads[$key]['highlight'] = 1;
					$newLoads[$key]['profitAmount'] =  round(($newLoads[$key]['Payment']  - $total_cost),2);
					$newLoads[$key]['percent'] =  getProfitPercent($newLoads[$key]['profitAmount'], $newLoads[$key]['Payment']);
				} 
					
				$newLoads[$key]['RPM'] = $this->Rpm_value;
				$newLoads[$key]['FuelCost'] = trim(str_replace('$','',$value["FuelCost"]));
				$newLoads[$key]['Miles'] = (float)trim(str_replace(',','',$value["Miles"]));
				$newLoads[$key]['deadmiles'] = (float)trim(str_replace(',','',$newLoads[$key]['deadmiles']));
					


				if ( $value['Age'] != '9+' ) {
						$AGE = '0'.$value['Age'].':00';
					} else {
						$AGE = '9:00+';
					}
				$newLoads[$key]['Age'] = $AGE;
				$newLoads[$key]['TotalCost'] = $total_cost;			
				$newLoads[$key]['hoursRemaining'] = $hoursRem;
				$newLoads[$key]['hoursRemainingNextDay'] = $hoursRem;
				
				if ( strtolower($value['PickUpDate']) == 'daily' ) {
					$newLoads[$key]['pickDate'] = $todayDate;
					$newLoads[$key]['displayPickUpDate'] = $value['PickUpDate'];
				} else {
					$newLoads[$key]['pickDate'] = $value['PickUpDate'];
					$newLoads[$key]['displayPickUpDate'] = date('Y-m-d',strtotime($value['PickUpDate']));	
				}
				$loadArray[] = $value['ID'];
			}
		}		
			
		return array($newLoads, $loadArray);
	}
	
	public function getIterationLoadNextDate( $vehicleID = null, $hoursLimit = null ) {
		$objPost = json_decode(file_get_contents('php://input'),true);
		$driverType = isset($objPost["driverType"]) && ($objPost["driverType"] == "team" || $objPost["driverType"] == "_team") ? "team" : "driver";
		if ( $hoursLimit != '' && $hoursLimit != null ) {
			$loadInfo = $objPost['valueArray'];
			$this->origin_city = $loadInfo['OriginCity'];
			$this->origin_state = $loadInfo['OriginState'];
			$this->pickupDate = preg_replace('/\s+/', '',$loadInfo['OriginPickupDate']);
			//$this->drivingHours = $hoursLimit;
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
			
			
			if ( isset($loadInfo['PickupAddress']) && $loadInfo['PickupAddress'] != '' ) {
				$origin = $loadInfo['PickupAddress'];
			} else {
				$origin = $loadInfo['OriginCity'].','.$loadInfo['OriginState'].',USA';
			}
			
			if ( isset($loadInfo['DestinationAddress']) && $loadInfo['DestinationAddress'] != '' ) {
				$newDestin = $loadInfo['DestinationAddress'];
			} else {
				$newDestin = $loadInfo['DestinationCity'].','.$loadInfo['DestinationState'].',USA';
			}
			
			if ( isset( $loadInfo['deadmilesEstTime']) && $loadInfo['deadmilesEstTime'] != '' ) {
				$deadmilesEstTime = $loadInfo['deadmilesEstTime'];
			} else {
				$deadmilesEstTime = 0;
			}
			
			if ( isset($loadInfo['ID']) && $loadInfo['ID'] != '' && is_numeric($loadInfo['ID']) ) {
				
			} else {
				$loadInfo['ID'] = $loadInfo['truckstopID'];
			}
		}
		
		if ( isset($loadInfo['hoursRemaining']) && $loadInfo['hoursRemaining'] != '' ) 
			$this->hoursRemaining = $loadInfo['hoursRemaining'];
		else
		 	$this->hoursRemaining = 0;

		$HRND = 0;
		if ( isset($loadInfo['hoursRemainingNextDay']) && $loadInfo['hoursRemainingNextDay'] != ''  && (isset($objPost['xchain']) && !$objPost['xchain'])){ 
			$HRND = $loadInfo['hoursRemainingNextDay'];
		}else{
		 	$HRND = 0;
		}

		if(isset($objPost['xchain']) && $objPost['xchain'] && isset($loadInfo['hoursRemaining']) && $loadInfo['hoursRemaining'] != ''){
			$HRND = $loadInfo['hoursRemaining'];
		}



		$dataMileTime = $this->User->getTimeMiles($origin,$newDestin);

		if(!empty($dataMileTime)){
			$calEstimatedTime = $dataMileTime['estimated_time'];					
		} else {
			$totalTimeArray = $this->GetDrivingDistance($origin,$newDestin);
			$calEstimatedTime = $totalTimeArray['time'];
		}
		
		$previousDate = $objPost['previousDate'];
		if ( $this->pickupDate == '' || strtolower($this->pickupDate) == 'daily' || $this->pickupDate == '0000-00-00') {
			$this->pickupDate = $previousDate;
			$originFirstDate = date('Y-m-d');
		} else {
			$originFirstDate = date('Y-m-d',strtotime($this->pickupDate));
		}	

		//--------------- Code for team task -------------------
		$drivingDailyLimit = $this->drivingDayLimit;
		if( $driverType == "team"){
			$drivingDailyLimit = $this->drivingDayLimitTeam;
		}
		//--------------- Code for team task -------------------


		$pickupDateDestArray = $this->estimatedTime( $calEstimatedTime , $deadmilesEstTime, $this->pickupDate, $drivingDailyLimit, $vehicleID, $HRND);
	
		$nextData = array();
		$nextData['ID'] = $loadInfo['ID'];
			if ( isset($loadInfo['PickupDate']) && $loadInfo['PickupDate'] != '' ) 
				$loadInfo['PickUpDate'] = $loadInfo['PickupDate'];
				
		$nextData['PickUpDate'] = $loadInfo['PickUpDate'];
			if ( isset($loadInfo['OriginDistance']) && $loadInfo['OriginDistance'] != '' )
			$nextData['OriginDistance'] = $loadInfo['OriginDistance'];

		$previousDate = $pickupDateDestArray["newPickupDate"];

		$nextData['OriginCity'] 			= $this->origin_city;
		$nextData['OriginState'] 			= $this->origin_state;
		$nextData['OriginPickupDate'] 		= $originPickupDate;
		$nextData['nextPickupDate'] 		= $pickupDateDestArray["newPickupDate"];
		$nextData['nextPickupDate1'] 		= $pickupDateDestArray["newPickupDate"];
		$nextData['estimatedTime'] 			= $calEstimatedTime;
		$nextData['tEstimatedTimeInHours'] 	= $pickupDateDestArray["tEstimatedTimeInHours"];
		$nextData['estimatedTimeDeadMile'] 	= $deadmilesEstTime;
		if ( isset($loadInfo['Equipment']) && $loadInfo['Equipment'] != '' )
			$nextData['Equipment'] = $loadInfo['Equipment'];
			
		$nextData['driver_name'] 			= $driverName;
		$nextData['truck_number'] 			= isset($driverArray[1]) ? $driverArray[1] : '';
		$nextData['multiDestinations'] 		= '';
		$nextData['originFirstDate'] 		= $originFirstDate;
		$nextData['previousDate'] 			= $previousDate;
		$nextData['deadmilesEstTime'] 		= $deadmilesEstTime;
		$nextData['origin'] 				= $origin;
		$nextData['newDestin'] 				= $newDestin;
		$nextData['totalDrivingHour'] 		= $pickupDateDestArray["totalDrivingHour"];
		$nextData['compWorkingHours'] 		= $pickupDateDestArray["totalHours"];
		$nextData['hoursRemaining'] 		= $this->hoursRemaining;
		$nextData['hoursRemainingNextDay'] 	= $pickupDateDestArray["nextDaysHoursLeft"];
		//$nextData['totalWorkingDays'] 		= $pickupDateDestArray["nextPickupDays"];
		//$nextData['drivingHours'] 			= $this->drivingHours;
		//$nextData['dailyDriving'] 			= array(1,1);
		$nextData['holidayOccured'] 		= $pickupDateDestArray["holidayOccured"];
		$nextData['dmEstTime'] 				= $pickupDateDestArray["deadMileEstTime"];
		$nextData['skippedWeekdays']		= $pickupDateDestArray["skippedWeekdays"];
		if(!isset($objPost["skipSaveSession"])){
			$nextData["driverID"] = $vehicleID;
			$this->Job->saveSessionChain($nextData,$objPost);
		}
		
		if(isset($objPost["vehicleGPS"])){
			//$nextData['vehicleGPS'] = true;
			$args = array("vehicleID"=>$vehicleID,"pickupDate"=>$nextData['PickUpDate'],"dropDate"=>$nextData['nextPickupDate']);
			$jobLogs = $this->Job->getVehicleLogForJob($args);
			//echo "<pre>";
			//print_r($jobLogs);die;
			
			$i = 0;
			$logBuffer = array();
			$status = $lastTime = '';
			if($jobLogs){
				foreach ($jobLogs as $key => $value) {
					if($i == 0){
				 		$status = $value['eventType'];
				 		$i++;
				 		$logBuffer[$status.'_'.$i]['from'] = $value['GMTTime'];
				 	}
				 	if($value['eventType'] == $status){
				 		$logBuffer[$status.'_'.$i]['to'] = $value['GMTTime'];
				 	}else{
				 		$status = $value['eventType'];
				 		$logBuffer[$status.'_'.++$i]['from'] = $lastTime;
				 		$logBuffer[$status.'_'.$i]['to'] = $value['GMTTime'];

				 	}
				 	$lastTime = $value['GMTTime'];
				}
				/*if(!isset($logBuffer[$status.'_'.$i])){
					$logBuffer[$status.'_'.$i]['from'] = $lastTime;
				}*/
			}
			$nextData['vehicleLogForJob'] = $logBuffer;
			$nextData['jobLogOnMap'] = $this->Job->jobLogOnMap($args);;
		}

		//echo "<pre>"; print_r($nextData);die;
		echo json_encode($nextData);
		
	}
	
	public function getIterationLoad ( $vehicleId = null )  {
	
		$objPost = json_decode(file_get_contents('php://input'),true);
		$loadInfo = $objPost["loadInfo"];
		$driverType = isset($objPost["driverType"]) && ($objPost["driverType"] == "team" || $objPost["driverType"] == "_team") ? "team" : "driver";

		$this->origin_city = $loadInfo['OriginCity'];
		$this->origin_state = $loadInfo['OriginState'];
		$this->pickupDate = $loadInfo['nextPickupDate'];
		$this->hoursRemaining = $loadInfo['hoursRemaining'];
		$driverVehicleInfoArray = $this->getDriverVehicleInfo( $vehicleId);
		
		$HRND = 0;
		if ( isset($loadInfo['hoursRemainingNextDay']) && $loadInfo['hoursRemainingNextDay'] != '' ) 
			$HRND = $loadInfo['hoursRemainingNextDay'];
		else
		 	$HRND = 0;
		
		if ( $this->pickupDate != '' && strtotime($this->pickupDate) >= strtotime(date('Y-m-d')) ) {
			$dateTime = array (
					'dateTime' => $this->pickupDate,
				);
			$todayDate = date('m/d/y',strtotime($this->pickupDate));
		} else {
			$dateTime = array ();
			$todayDate = date('m/d/y');
		}		
		
		if ( isset($loadInfo['multiDestinations']) && $loadInfo['multiDestinations'] != '' ) {
			$this->multiDestinations = implode(',',$loadInfo['multiDestinations']);
			$destCity = ""; $destState = $this->multiDestinations; 
			$destCountry = isset($loadInfo["multiDestCountry"]) && !empty($loadInfo["multiDestCountry"]) ? $loadInfo["multiDestCountry"] : "USA";

		}else if(isset($loadInfo["singleDestination"]) && !empty($loadInfo["singleDestination"])){
			$searchBuffer = explode(",", $loadInfo["singleDestination"]);
			$destCity = $searchBuffer[0];
			$destState = $searchBuffer[1];
			$destCountry = $searchBuffer[2];
		}else{
			$destCity =  $destState =  $destCountry = "";
		}
		
		$tableTitle = $loadInfo['driver_name'].'-'.$loadInfo['truck_number'].'-'.$this->origin_city.'-'.$this->origin_state;
		$data['rows'] = $this->commonApiHits( $driverVehicleInfoArray[3], $dateTime, '', '',300 ,$destCity, $destState, 300, $destCountry);
		
		$deadMilesOriginLocation = $this->origin_city.','.$this->origin_state.',USA';
		
		$this->loadsIdArray = array();
		if ( !empty($data['rows'])) {
			$returnArray = $this->giveBestLoads( $data['rows'] , $todayDate, $vehicleId, $HRND,$this->loadsIdArray,$driverVehicleInfoArray[3],$driverType);
			$this->finalArray = $returnArray[0];
			$loadsIdArray = $returnArray[1];
		}
				
		$newData = array();
			
		$data['rows'] = array_values($this->finalArray);
		
		$newData['rows'] =  $data['rows'];
		$newData['loadsIdArray'] =  $this->loadsIdArray;
		$newData['currentPickupDate'] =  $loadInfo['nextPickupDate'];
			
		$newData['table_title'] = $tableTitle;	
		$newData['originCitySearch'] = $this->origin_city;
		$newData['originStateSearch'] = $this->origin_state;
		$newData['originDateSearch'] = $this->pickupDate;
		$newData['multiDestinationStateSearch'] = $this->multiDestinations;
		$newData['deadMilesOriginLocation'] =  $deadMilesOriginLocation;
		
		echo json_encode($newData);
	}
	
	private function estimatedTime ( $time = '' , $deadmilesEstTime = '' , $pickupDate = '0000-00-00', $hoursLimitInTimeFormat = 0 , $vehicleID = null , $hoursRem = 0,$availableHours = array() ) {
			
		$timeDay = $timeDayDead = $timeMin = $hoursRemaining = $deadMileTimeMins = $timeHourDead = $timeHour = 0;
		//Estimated Time
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
				$timeMin += $timeArrayNew[0];
			}else if ( strpos($timeArray[1], 'min') !== false ) { 
				$timeArrayNew = explode( 'min',$timeArray[1]);
				$timeMin += $timeArrayNew[0];
			}
		}else if ( strpos($time, 'hour') !==  false ) {
			$timeArray = explode ( 'hour', $time );
			$timeHour = trim($timeArray[0]);
			
			if ( strpos($timeArray[1], 'mins') !== false ) { 
				$timeArrayNew = explode( 'mins',$timeArray[1]);
				$timeMin += $timeArrayNew[0];
			}else if ( strpos($timeArray[1], 'min') !== false ) { 
				$timeArrayNew = explode( 'min',$timeArray[1]);
				$timeMin += $timeArrayNew[0];
			}
		}else if ( strpos($time, 'min') !== false ) { 
			$timeArrayNew = explode( 'min',$time);
			$timeMin += $timeArrayNew[0];
		}else {
			$timeArrayNew = explode( 'hour',$time);
			$timeHour = trim($timeArrayNew[0]);
		}
		$tEstimatedTimeInHours =  (24 * $timeDay) + $timeHour;
		if ( $timeMin >= 60 ) {
			$tempEst = floor( $timeMin / 60 );
			$tEstimatedTimeInHours += $tempEst;
			$timeMin = ($timeMin - (60 * floor($timeMin/60)));
			$minText = $timeMin == 1 ? "min" : "mins";
			$hrsText = $tEstimatedTimeInHours == 1 ? "hour" : "hours";
			$tEstimatedTimeInHours .= " ".$hrsText."  ".$timeMin." ".$minText;

		}else{
			$minText = $timeMin == 1 ? "min" : "mins";
			$hrsText = $tEstimatedTimeInHours == 1 ? "hour" : "hours";
			$tEstimatedTimeInHours .= " ".$hrsText." ".$timeMin." ".$minText;
		}

		//Estimated Dead Miles Time
		if ( strpos($deadmilesEstTime, 'day') !== false ) {
			$timeArray = explode( 'day',$deadmilesEstTime);
			$timeDayDead = trim($timeArray[0]);
			if ( strpos($timeArray[1], 'hour') !== false ) { 
				$timeArrayNew = explode( 'hour',$timeArray[1]);
				$timeHourDead = trim($timeArrayNew[0]);
			}
		}else if ( strpos($deadmilesEstTime, 'hours') !==  false ) {
			$timeArray = explode ( 'hours', $deadmilesEstTime );
			$timeHourDead = trim($timeArray[0]);
			if ( strpos($timeArray[1], 'mins') !== false ) { 
				$timeArrayNew = explode( 'mins',$timeArray[1]);
				$timeMin += $timeArrayNew[0];
				$deadMileTimeMins =  $timeArrayNew[0];
			}else if ( strpos($timeArray[1], 'min') !== false ) { 
				$timeArrayNew = explode( 'min',$timeArray[1]);
				$timeMin += $timeArrayNew[0];
				$deadMileTimeMins =  $timeArrayNew[0];
			}
		}else if ( strpos($deadmilesEstTime, 'hour') !==  false ) {
			$timeArray = explode ( 'hour', $deadmilesEstTime );
			$timeHourDead = trim($timeArray[0]);
			if ( strpos($timeArray[1], 'mins') !== false ) { 
				$timeArrayNew = explode( 'mins',$timeArray[1]);
				$timeMin += $timeArrayNew[0];
				$deadMileTimeMins =  $timeArrayNew[0];
			}else if ( strpos($timeArray[1], 'min') !== false ) { 
				$timeArrayNew = explode( 'min',$timeArray[1]);
				$timeMin += $timeArrayNew[0];
				$deadMileTimeMins =  $timeArrayNew[0];
			}

		} else {
			if ( strpos($deadmilesEstTime, 'mins') !== false ) { 
				$timeArrayNew = explode( 'mins',$deadmilesEstTime);

				$timeMin += $timeArrayNew[0];
				$deadMileTimeMins =  $timeArrayNew[0];
			}else if ( strpos($deadmilesEstTime, 'min') !== false ) { 
				$timeArrayNew = explode( 'min',$deadmilesEstTime);
				$timeMin += $timeArrayNew[0];
				$deadMileTimeMins =  $timeArrayNew[0];
			}
			if ( strpos($deadmilesEstTime, 'hour') !== false ) { 
				$timeArrayNew = explode( 'hour',$deadmilesEstTime);
				$timeHourDead = trim($timeArrayNew[0]);	
			}
			
		}
		$deadMileEstTime = array();
		$deadMileEstTime['hours']	= ($timeDayDead * 24 ) + $timeHourDead;
		$deadMileEstTime['mins']	= $deadMileTimeMins;
		//$this->hoursRemaining = $hoursRem;
		$totalDrivingHour = ( $timeDay * 24 ) + $timeHour + ($timeDayDead * 24 ) + $timeHourDead;
		if ( $timeMin >= 60 ) {
			$minHour = floor( $timeMin / 60 );
			$totalDrivingHour += $minHour;
			$timeMin = number_format(($timeMin - (60 * floor($timeMin/60)))/100,2) ;
			$totalDrivingHour += $timeMin;
		}else{
			$totalDrivingHour += ($timeMin/100);
		}

		//$hoursLimitInTimeFormat = 8;
		$totalHours	= ( $this->fromTime($totalDrivingHour) + $this->fromTime($this->loadDropTime) + $this->fromTime($this->loadPickTime) + $this->fromTime($hoursRem)) ;
		$totalHours = $this->toTime($totalHours);
		$nextPickupDays 	=  intval( ( $this->fromTime($totalHours)) / $this->fromTime($hoursLimitInTimeFormat));	
		$nextDaysHoursLeft 	=  (($this->fromTime($totalHours)) - (floor($nextPickupDays) * $this->fromTime($hoursLimitInTimeFormat)));
		$nextDaysHoursLeft =  $this->toTime($nextDaysHoursLeft);
		$date = strtotime("+".$nextPickupDays." days", strtotime($pickupDate));

		// Include holiday in Hours of Service
		$holiday = array("01/01","04/15","05/28","07/03","09/03","11/22","12/24","12/30"); //m-d-y format
		$currentYear = date('Y',strtotime($pickupDate));
		$holidayOccured = array();
		$h = 0;
		foreach ($holiday as $key => $value) {
			$value 	  .= "/".$currentYear;
			$value     = date('Y-m-d',strtotime($value));
			$rangeFrom = date('Y-m-d',strtotime($pickupDate));
			$rangeTo   = date('Y-m-d',$date);
			$valueX    = date('m/d',strtotime($value)) ."/".date('Y',strtotime($rangeTo));
			$valueX    = date('Y-m-d',strtotime($valueX));
			if(($value >= $rangeFrom && $value <= $rangeTo) || ($valueX >= $rangeFrom && $valueX <= $rangeTo)){
				$holidayOccured[$h]['key'] = $key; 
				$spos = 0;
				for ($iRange = strtotime($rangeFrom) ; $iRange <= strtotime($rangeTo); $spos++ ) { 
					if ($iRange == strtotime($value) ) {
						break;
					}
					$iRange = strtotime(date ("Y-m-d", strtotime("+1 day", $iRange)));
				}
				$holidayOccured[$h++]['hday'] = date('Y/m/d',strtotime($value));
			}
		}


		if($h > 0){
			$checkFrom = $date;
			for ($i=1; $i <=$h ; $i++) { 
				$checkFrom = strtotime("+1 days", $checkFrom);
				$dt = date('m/d',$checkFrom);
				if(in_array($dt, $holiday)){
					$h++;
				}
			}
		}

		$date = strtotime("+".$h." days", $date);

		$skippedWeekdays = array("drivingOnWeekDay" => 0,"isSkippedToMonday" => false, "skippedNoOfDays" => 0, "deliveryDayName"=>"","deliveryDate"=>"" );
		$nextPickupDays += $h;
		$newPickupDate 	= date("Y-m-d", $date);
		$deliveryDay 	= date("l",strtotime($newPickupDate)); 
		$deliveryDate 	= date("Y-m-d",strtotime($newPickupDate)); 
		if (in_array($deliveryDay, $this->weekDays)) { //skip date to next day if  delivery date is in weekdays
			$noOfDays 	= array_search($deliveryDay, $this->weekDays); 
			
			$skippedWeekdays["isSkippedToMonday"] 	= true;
			$skippedWeekdays["skippedNoOfDays"] 	= $noOfDays;
			$skippedWeekdays["deliveryDayName"] 	= $deliveryDay;
			$skippedWeekdays["deliveryDate"][] 		= $deliveryDate;
			if($noOfDays > 1){
				for($c = 1;$c < $noOfDays; $c++){
					$skippedWeekdays["deliveryDate"][] = date("Y-m-d", strtotime("+1 days", strtotime($deliveryDate)));
				}	
			}
			

			$newPickupDate = date("Y-m-d", strtotime("+".$noOfDays." days", strtotime($newPickupDate)));
			if( $this->fromTime($nextDaysHoursLeft) >= $this->fromTime(2) ){
				$drivingOnWeekDay = $this->fromTime($nextDaysHoursLeft) - $this->fromTime(2);
				$skippedWeekdays["drivingOnWeekDay"]  = $this->toTime($drivingOnWeekDay);
				$nextDaysHoursLeft = $this->fromTime(2);
				$nextDaysHoursLeft = $this->toTime($nextDaysHoursLeft);
			}

		}

		while (true) { //Skip date to next day if delivery date is in holiday or in weekdays
			$dt = date('m/d',strtotime($newPickupDate));
			$deliveryDay = date("l",strtotime($newPickupDate)); 
			if(in_array($dt, $holiday)){
				$skippedWeekdays["deliveryDate"][] = $newPickupDate;
				$newPickupDate = date("Y-m-d", strtotime("+1 days", strtotime($newPickupDate)));
			}else if (in_array($deliveryDay, $this->weekDays)) { 
				$noOfDays = array_search($deliveryDay, $this->weekDays); 
				$newPickupDate = date("Y-m-d", strtotime("+".$noOfDays." days", strtotime($newPickupDate)));
				$skippedWeekdays["deliveryDate"][] = $deliveryDay;
				if($noOfDays > 1){
					for($c = 1;$c < $noOfDays; $c++){
						$skippedWeekdays["deliveryDate"][] = date("Y-m-d", strtotime("+1 days", strtotime($deliveryDate)));
					}	
				}
			}else{
				break;
			}
		}

		return array("newPickupDate"=>$newPickupDate,"totalHours"=>$totalHours,"totalDrivingHour"=>$totalDrivingHour,"nextDaysHoursLeft"=>$nextDaysHoursLeft,"nextPickupDays"=>$nextPickupDays,"holidayOccured"=>$holidayOccured,"deadMileEstTime"=>$deadMileEstTime,"skippedWeekdays"=>$skippedWeekdays,"tEstimatedTimeInHours"=>$tEstimatedTimeInHours);	
	}
	
	private function fromTime($time) {
	    if($time > 0){
	        $time = number_format($time,2);
	        $timeArray = explode('.', $time);
	        $hours = intval($timeArray[0]);
	        $minutes = intval($timeArray[1]);
	        return ($hours * 60) + $minutes;    
	    }else
	    	return 0;
	}

	private function toTime($number) {
	    $hours = floor($number / 60);
	    $minutes = $number % 60;
	    return $hours.".".($minutes <= 9 ? "0" : "").$minutes;
	}

	public function destroyLoadsChain($driverID){
		$this->Job->destroyLoadsChain($driverID);
		$this->skipAcl_getChangeDriverChains($driverID);
	}	

	protected function removeFromChain(){
		$objPost = json_decode(file_get_contents('php://input'),true);
		$this->Job->removeElementFromChain($objPost['deletedRowIndex'],$objPost['ID'],$objPost["driverID"]);
		echo json_encode(array("success"=>TRUE));
	}
	
	public function skipAcl_getChangeDriverChains($driverID = false) {
		
		$driverType = "driver";
		if($driverID){
			$vehicleID = $driverID;
			if(isset($_COOKIE["_globalDropdown"])){
				$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
				$driverType = isset($gDropdown["label"]) && ($gDropdown["label"] == "team" || $gDropdown["label"] == "_team") ? "team" : "driver";
			}
		} else {
			$obj = json_decode(file_get_contents('php://input'),true);
			$vehicleID = $obj["driverInfo"];
			$driverType = isset($obj["driverType"]) && ($obj["driverType"] == "team" || $obj["driverType"] == "_team") ? "team" : "driver";
		}
		
		$pickupDateDest = '';
		$driverVehicleInfoArray = $this->getDriverVehicleInfo( $vehicleID);
		$result = $this->Vehicle->getLastLoadRecord( $vehicleID);
		if ( !empty($result) ) {
			$pickupDateDest = $this->CalculateNextLoadDate($result);
			$this->origin_city = $result['DestinationCity'];
			$this->origin_state = $result['DestinationState'];
			$country = $result['DestinationCountry'];
			$tableTitle = $driverVehicleInfoArray[5].'-'.$this->origin_city.'-'.$this->origin_state;
		} else {
			$this->origin_city = $driverVehicleInfoArray[0];
			$this->origin_state = $driverVehicleInfoArray[1];
			$country = 'USA';
			$tableTitle = $driverVehicleInfoArray[4];
		}
		
		$unFinishedChain = $this->getUnFinishedChain((int)$vehicleID);
		$this->hoursRemaining = 0;
		if(count($unFinishedChain) > 0){
			$savedChain = end($unFinishedChain[0]);
			$driverVehicleInfoArray[0] =  $savedChain['encodedJobRecord']['DestinationCity'];
			$this->origin_city =  $savedChain['encodedJobRecord']['DestinationCity'];
			$this->origin_state = $savedChain['encodedJobRecord']['DestinationState'];
			$country = $savedChain['encodedJobRecord']['DestinationCountry'];
			$driverVehicleInfoArray[1] = $savedChain['encodedJobRecord']['DestinationState'];
			$driverVehicleInfoArray[3] = $savedChain['encodedJobRecord']['EquipmentTypes']['Code'];
			$driverVehicleInfoArray[2] = $savedChain['valuesArray']['nextPickupDate1'];
			$this->hoursRemaining = $savedChain['valuesArray']['hoursRemainingNextDay'];
			$tableTitle = $driverVehicleInfoArray[5].'-'.$this->origin_city.'-'.$this->origin_state;
		}
		
		$dateTime = array ();
		if ( $pickupDateDest != ''  ) {
			if ( strpos($pickupDateDest, ',') !== false ) {
				$datesNewArray = explode(',',$pickupDateDest);
				$pickupDate = '';
				for ( $i = 0; $i < count($datesNewArray);  $i++ ) {
					$dateTime[] = $datesNewArray[$i];
					$pickupDate .= $datesNewArray[$i].',';
				}
				$this->todayDate = date('m/d/y',strtotime($datesNewArray[0]));
			} else {
				if ( strtotime($pickupDateDest) >= strtotime(date('Y-m-d')) )  {
					$dateTime = array (
						'dateTime' => $pickupDateDest,
					);
					$this->todayDate = date('m/d/y',strtotime($pickupDateDest));
				}
			}
		} 
			
		$deadMilesOriginLocation = $this->origin_city.','.$this->origin_state.', '.$country;
		
		$rows = $this->commonApiHits( $driverVehicleInfoArray[3], $dateTime, '');	
		
		$this->loadsIdArray = array();
		if ( !empty($rows) ) {
			$returnArray = $this->giveBestLoads( $rows, $this->todayDate, $vehicleID , $this->hoursRemaining, $this->loadsIdArray, $driverVehicleInfoArray[3],$driverType);
			$this->finalArray = $returnArray[0];
			$this->loadsIdArray = $returnArray[1];
		}
		
		$newdata = array();
		$newdata['rows'] = array_values($this->finalArray);
		$newdata['table_title'] = $tableTitle;
		$newdata['vehicleIdRepeat'] = $vehicleID;
		$newdata['chainWithDriver'] = $unFinishedChain;
		$newdata['originCitySearch'] = $this->origin_city;
		$newdata['originStateSearch'] = $this->origin_state;
		$newdata['originDateSearch'] = $this->todayDate;
		$newdata['loadsIdArray'] = $this->loadsIdArray;
		$newdata['deadMilesOriginLocation'] =  $deadMilesOriginLocation;
		
		echo json_encode(array('loadsData'=> $newdata),JSON_NUMERIC_CHECK);
	}


	public function customLocationSearch($vehicleID = false) { //When user search from custom location in plan page
		$driverType = "driver";
		if(!$vehicleID){
			$obj = json_decode(file_get_contents('php://input'),TRUE);
			$vehicleID = $obj['vehicleId'];
			$pickupDateDest = $obj['args']['date'];
			$driverType = isset($obj["driverType"]) && ($obj["driverType"] == "team" || $obj["driverType"] == "_team") ? "team" : "driver";
		}else{
			if(isset($_COOKIE["_globalDropdown"])){
				$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
				$driverType = isset($gDropdown["label"]) && ($gDropdown["label"] == "team" || $gDropdown["label"] == "_team") ? "team" : "driver";
			}
		}

		$driverVehicleInfoArray = $this->getDriverVehicleInfo( $vehicleID); //For Driver Name
		$result = $this->Vehicle->getLastLoadRecord( $vehicleID);

		$deadMilesOriginLocation =  '';
		if ( isset($obj['args']['multiOrigins']) && !empty($obj['args']['multiOrigins']) ) {
			$searchFromMultiOrigins = $obj['args']['multiOrigins'];
			$this->origin_city = '';
			$this->origin_state = implode(',',$searchFromMultiOrigins);
			$country = strtolower($obj['args']['origin_country']) == strtolower("Canada") ? "CAN" : $obj["args"]['origin_country'];
			if ( !empty($result) ) {
				$deadMilesOriginLocation = $result['DestinationCity'].','.$result['DestinationState'].','.$result['DestinationCountry'];
			} else {
				$deadMilesOriginLocation = $driverVehicleInfoArray[0].','.$driverVehicleInfoArray[1].',USA';
			}
		} else {
			$searchFrom = $obj['args']['searchFrom'];
			$searchBuffer = explode(",", $searchFrom);
			$this->origin_city = $searchBuffer[0];
			$this->origin_state = $searchBuffer[1];
			$country = $searchBuffer[2];
			$deadMilesOriginLocation = $obj['args']['searchFrom'];
		}
		
		$searchingFrom  = $this->origin_city.'-'.$this->origin_state;
		$searchingFrom  = trim($searchingFrom,'-');

		if ( !empty($result) ) {
			$tableTitle = $driverVehicleInfoArray[5].'-'.$result['DestinationCity'].'-'.$result['DestinationState'].' ( Search From : '.$searchingFrom.')';
		} else {
			$tableTitle = $driverVehicleInfoArray[4].' ( Search From : '.$searchingFrom.')';
		}

		$dateTime = array ();
		if ( $pickupDateDest != ''  ) {
			if ( strpos($pickupDateDest, ',') !== false ) {
				$datesNewArray = explode(',',$pickupDateDest);
				$pickupDate = '';
				for ( $i = 0; $i < count($datesNewArray);  $i++ ) {
					$dateTime[] = $datesNewArray[$i];
					$pickupDate .= $datesNewArray[$i].',';
				}
				$this->todayDate = date('m/d/y',strtotime($datesNewArray[0]));
			} else {
				if ( strtotime($pickupDateDest) >= strtotime(date('Y-m-d')) )  {
					$dateTime = array (
						'dateTime' => $pickupDateDest,
					);
					$this->todayDate = date('m/d/y',strtotime($pickupDateDest));
				}
			}
		} 
		
		$rows = $this->commonApiHits( $driverVehicleInfoArray[3], $dateTime, '',$country);	
		$this->hoursRemaining = 0;
		$this->loadsIdArray = array();
		if ( !empty($rows) ) {
			$returnArray = $this->giveBestLoads( $rows, $this->todayDate, $vehicleID , $this->hoursRemaining, $this->loadsIdArray, $driverVehicleInfoArray[3], $driverType);
			$this->finalArray = $returnArray[0];
			$this->loadsIdArray = $returnArray[1];
		}
		
		$newdata = array();
		$newdata['rows'] = array_values($this->finalArray);
		$newdata['table_title'] = $tableTitle;
		$newdata['vehicleIdRepeat'] = $vehicleID;
		
		$newdata['originCitySearch'] = $this->origin_city;
		$newdata['originStateSearch'] = $this->origin_state;
		$newdata['originDateSearch'] = $this->todayDate;
		$newdata['loadsIdArray'] = $this->loadsIdArray;
		$newdata['deadMileFrom'] = $deadMilesOriginLocation;
		$newdata['deadMilesOriginLocation'] =  $deadMilesOriginLocation;
		
		echo json_encode(array('loadsData'=> $newdata),JSON_NUMERIC_CHECK);
	}
	
	private function getDriverVehicleInfo( $vehicleID = null ) {
		$statesAddress 	= $this->Vehicle->get_vehicle_address($vehicleID);
		$newDest = array();
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
		
		$newlabel= $statesAddress['driverName'].'-'.$statesAddress['label'].'-'.@$newDest[1].'-'.@$newDest[0];
		$driverNameShow = $statesAddress['driverName'].'-'.$statesAddress['label'];
		return array( $this->orign_city, $this->orign_state, $this->pickupDateDest, $statesAddress['vehicle_type'], $newlabel , $driverNameShow);
	}
	

}


