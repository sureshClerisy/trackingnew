<?php

/**
* Truck stop Api Controller
*  
*/

class Truckstop extends Admin_Controller{

	public $id;
	public $rows;
	private $pickupDate;
	public $userId;
	public $origin_city;
	public $origin_state;
	private $Rpm_value;
	private $saveDead;
	private $saveCalPayment;
	public $diesel_rate_per_gallon;
	public $driver_pay_miles_cargo;
	public $driver_pay_miles_cargo_team;
	public $total_tax;
	public $deadMilePaid;
	public $deadMileNotPaid;
	public $payForDeadMile;
	public $payForDeadMile_team;
	public $iftaTax;
	public $tarps;
	public $det_time;
	public $tollsTax;
	public $todayDate;
	public $extraStopsDataArray;
	public $extraStopCharge;
	public $extraStopPerStopCharge;
	public $extraStopPerStopChargeTeam;
	public $deadMilesActual;
	public $defaultTruckAvg;
	public $load_source;
	public  $userName;
	
	function __construct()
	{
		parent::__construct();
		
		$this->pickupDate 	= date('Y-m-d');
		$this->load->model(array('Vehicle','Driver','User','BrokersModel','Job'));
		$this->load->helper('truckstop');
				
		$this->userRoleId 	= $this->session->role;
		$this->userId 		= $this->session->loggedUser_id;
		$this->origin_state = $this->Vehicle->get_vehicles_state($this->session->admin_id);
		$this->userName   	= $this->session->loggedUser_username;
		
		$this->Rpm_value 	= 0;
		$this->saveDead 	= '';
		$this->diesel_rate_per_gallon = 2.50;
		$this->driver_pay_miles_cargo = 0.45;						// for single driver
		$this->driver_pay_miles_cargo_team = 0.58;					// for team of drivers
		$this->total_tax = 50;
		
		$this->deadMilePaid 	= 0;
		$this->deadMileNotPaid 	= 0;
		$this->payForDeadMile 	= 0.45;								// driver pay for deadmile for single
		$this->payForDeadMile_team = 0.58;							// driver pay for deadmile for team
		$this->iftaTax 			= 50;
		$this->tarps 			= 0;
		$this->det_time 		= 0;
		$this->tollsTax 		= 0;
		
		$this->extraStopsDataArray = array();
		$this->todayDate = date('m/d/y');
		$this->extraStopCharge = 0;
		$this->extraStopPerStopCharge = 25;
		$this->extraStopPerStopChargeTeam = 25;
		
		$this->deadMilesActual = 0;
		$this->defaultTruckAvg = 6;
		$this->load_source = 'truckstop.com';
	}
	
	public function skipAcl_index(  $parameter = null )
	{
		$newData = array();
		
		$admin_email = $this->session->userdata('loggedUser_email');
		$admin_loggedin = $this->session->userdata('loggedUser_username');
		if( (isset($admin_email) && $admin_email != '') && (isset($admin_loggedin) && $admin_loggedin != '') ) {
			$newData['shouldNotMoveFurther'] = true;
		} else {
			$newData['shouldNotMoveFurther'] = false;
		}
		
		if ( $parameter == 1 ) {
			$newData['notShowDataTable'] 	= true;
		} else {
			$newData['notShowDataTable'] 	= false;
		}
		
		$newData['parameter'] = $parameter;
		
		echo json_encode(array('loadsData'=> $newData),JSON_NUMERIC_CHECK);
	}
	
	private function giveBestLoads( $loads = array(), $loadsIdArray = array() , $vehicleId = null, $todayDate, $moreThanMiles = null, $addNewClass = '' , $dailyFilter = '', $max_weight = null, $max_length = null ) {

		$blackListedBrokers = $this->BrokersModel->getListOfBlacklisted(); //Getting all blacklisted compnies array
		
		if ( !empty($loads) ) {
			$truckAverage = $this->defaultTruckAvg;
			if ( $vehicleId != '' && $vehicleId != null ) {
				$vehicle_fuel_consumption = $this->Vehicle->get_vehicles_fuel_consumption($vehicleId);
				if ( !empty($vehicle_fuel_consumption) )
					$truckAverage =round( 100/$vehicle_fuel_consumption[0]['fuel_consumption'],2);
					// $truckAverage = (int)($vehicle_fuel_consumption[0]['fuel_consumption']/100);
			} 
			$havePayment = array();
			
			if ( count($loads) > 0) {
				
				// $blackListedBrokers = $this->BrokersModel->getListOfBlacklistedBrokers();

				foreach ($loads as $key => $value) {
					$this->Rpm_value = 0;
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
					
					$cmpnumber 	= str_replace('-','',$value['PointOfContactPhone']);
					$compayName = strtolower($value['CompanyName']);

				
					if( isset($value['Payment']) ) 
						$loadPayment = (double)(str_replace(',','',$value['Payment']));	
					
					if ( $moreThanMiles == 1 ) 	{				// for showing loads more than 500 miles only
						$loadsMiles = 500;
					} else {
						$loadsMiles = 0;
					}
					
					$value['Miles'] = str_replace(',','',$value['Miles']);
					
					if ( $dailyFilter == 'single' ) {	
						if ( $value['Length'] > $max_length || $loadWeight > $max_weight || strtolower($value['Equipment']) == 'cong' || strtolower($value['Equipment']) == 'fws' || $value['Miles'] == 0 || (in_array($value['ID'], $loadsIdArray)) || $value['Miles'] <= $loadsMiles || strtolower($value['PickUpDate']) == 'daily' || (in_array($compayName, $blackListedBrokers)) ) {
							unset($loads[$key]);
							continue;
						}
					} else if ( $dailyFilter == 'daily' ) {
						if ( $value['Length'] > $max_length || $loadWeight > $max_weight || strtolower($value['Equipment']) == 'cong' || strtolower($value['Equipment']) == 'fws' || $value['Miles'] == 0 || (in_array($value['ID'], $loadsIdArray)) || $value['Miles'] <= $loadsMiles || strtolower($value['PickUpDate']) != $dailyFilter || (in_array($compayName, $blackListedBrokers)) ) {
							unset($loads[$key]);
							continue;
						}
					} else {
						if ( $value['Length'] > $max_length || $loadWeight > $max_weight || strtolower($value['Equipment']) == 'cong' || strtolower($value['Equipment']) == 'fws' || $value['Miles'] == 0 || (in_array($value['ID'], $loadsIdArray)) || $value['Miles'] <= $loadsMiles || (in_array($compayName, $blackListedBrokers)) )  {
							unset($loads[$key]);
							continue;
						}
					}
					
					$loads[$key]['Weight'] = $loadWeight;
					if ( strtolower($value['PickUpDate']) == 'daily' ) {
						$loads[$key]['displayPickUpDate'] = $value['PickUpDate'];
					} else {
						$loads[$key]['displayPickUpDate'] = date('Y-m-d',strtotime($value['PickUpDate']));	
					}
									
					$loads[$key]['deadmiles'] = $value['OriginDistance'];
															
					$total_cost = $this->getCompleteCost($value['Miles'],$loads[$key]['deadmiles'],$truckAverage);
					
					if ( $loadPayment != 0 && $loadPayment != '' && $loadPayment != null ) {
						if ( $loadPayment < $total_cost && $loadPayment != null ) {
							unset($loads[$key]);
							continue;
						}
						
						if ( $value['Miles'] != 0 )
						$this->Rpm_value = round( $loadPayment / $value['Miles'], 2 );
						$loads[$key]['highlight'] = 0;
						$loads[$key]['Payment'] = $loadPayment;
						$loads[$key]['profitAmount'] = round(($loadPayment - $total_cost),2);
						$loads[$key]['percent'] = getProfitPercent($loads[$key]['profitAmount'], $loadPayment);
					} else {
						$calPayment = getPaymentFromProfitMargin($total_cost, 30);

						$loads[$key]['Payment'] = $calPayment;
						if ( $value['Miles'] != 0 )
						$this->Rpm_value = round( $calPayment / $value['Miles'], 2 );
						$loads[$key]['highlight'] = 1;
						$loads[$key]['profitAmount'] = round(($loads[$key]['Payment'] - $total_cost),2);
					
						$loads[$key]['percent'] = getProfitPercent($loads[$key]['profitAmount'], $loads[$key]['Payment']);
					} 
					
					$loads[$key]['RPM'] = $this->Rpm_value;
					$loads[$key]['FuelCost'] = trim(str_replace('$','',$value["FuelCost"]));
				
					if ( $value['Age'] != '9+' ) {
						$AGE = '0'.$value['Age'].':00';
					} else {
						$AGE = '9:00+';
					}
					$loads[$key]['Age'] = $AGE;
					
					if ( strtolower($value['PickUpDate']) == 'daily' ) {
						$loads[$key]['pickDate'] = $todayDate;
					} else {
						$loads[$key]['pickDate'] = $value['PickUpDate'];
					}
					
					$loads[$key]['totalCost'] = $total_cost;
					$loads[$key]['newClass'] = $addNewClass;
								
					//------------------ Add class on job if open first time ---------------------------------
				
					$_COOKIE_NAME = 'VISIT_'.$value['ID'].'_'.str_replace("/", "_", $loads[$key]['pickDate']);
					if(isset($_COOKIE[$_COOKIE_NAME])) {
						$loads[$key]['visited']	= true;
					}else{
						$loads[$key]['visited'] = false;
					}

					//------------------ Add class on job if open first time ---------------------------------
					$price[$key] = $loads[$key]['profitAmount'];
					$havePayment[$key] = $loads[$key]['highlight'];		
					$loadsIdArray[] = $value['ID'];				
				}
			}
		
			if ( !empty($loads) && count($loads) > 1 )  {
				array_multisort($havePayment, SORT_ASC, $price, SORT_DESC, $loads); //Loads with payment and have heighest profit will be on top
			}
		}

		return array($loads, $loadsIdArray);
	}
	
	/**
	* Method fetchSearchResults
	* @param GET Request
	* @return JSON
	* Reqeusting data from Truckstop Api
	*/
	public function fetchSearchResults($parameter = false) {

		$this->lang->load('loads',$_REQUEST['setLanguage']);
		$admin_email 	= $this->session->userdata('loggedUser_email');
		$admin_loggedin = $this->session->userdata('loggedUser_username');

		if( (isset($admin_email) && $admin_email != '') && (isset($admin_loggedin) && $admin_loggedin != '') ) {
			$newData['shouldNotMoveFurther'] = true;
		} else {
			$newData['shouldNotMoveFurther'] = false;
		}
		
		if(!isset($_REQUEST['multiOrigins']) || !isset($_REQUEST['origin_City'])){
			$newData = array();
			$newData['notShowDataTable'] 	= false;
			$newData['parameter'] = $parameter;
			echo json_encode(array('loadsData'=> $newData),JSON_NUMERIC_CHECK);
		} else {
			$data['rows'] 		= array();
			$data['no_result'] 	= 'Search loads';
			$dataPost 	= $_REQUEST;
		
			if ( isset($dataPost['multiDestinations']) && !empty($dataPost['multiDestinations']) ) {
				$destination_states = trim($dataPost['multiDestinations']);
				$destination_city = '';
			} else {
				$destination_city = $dataPost['dest_City'];
				$destination_states = $dataPost['dest_State'];
			}
			
			if ( isset($dataPost['multiOrigins']) && !empty($dataPost['multiOrigins']) ) {
				$this->origin_city = '';
				$state_enter = $dataPost['multiOrigins'][0];
				if(is_array($dataPost['multiOrigins'])){
					$this->origin_state = implode(',',trim($dataPost['multiOrigins']));
				} else {
					$this->origin_state = $dataPost['multiOrigins'];
				}
			} else {
				$this->origin_city = $dataPost['origin_City'];
				$this->origin_state = $dataPost['origin_State'];
				$state_enter = $dataPost['origin_State'];
			}
			
			$pickupDateDest = '';
			$abbreviationArray = array();
						
			if ( !empty($dataPost['trailerType']) ) {
				$equipment_type = $dataPost['trailerType'];
			} else {
				$equipment_type = 'F,FSD';
			}
											
			$newDest = array();
			
			array_push($newDest,$this->origin_state,$this->origin_city);
			$origin_country = isset($dataPost['origin_country']) ? $dataPost['origin_country'] : 'USA';
			$dest_country 	= isset($dataPost['destination_country']) ? $dataPost['destination_country'] : $dataPost['origin_country'];	
			$destination_range = ( $dataPost['destination_range'] != '' && is_numeric($dataPost['destination_range']) ) ? $dataPost['destination_range'] : 300;			
			$origin_range = ( $dataPost['origin_range'] != '' && is_numeric($dataPost['origin_range']) ) ? $dataPost['origin_range'] : 300;			
			$load_type 	= ( isset($dataPost['load_type']) && $dataPost['load_type'] != '' ) ? $dataPost['load_type'] : 'Full';			
			$min_payment = (isset($dataPost['min_payment']) && is_numeric($dataPost['min_payment']) ) ? $dataPost['min_payment'] : 0;			
			$posted_time = ( isset($dataPost['posted_time']) ) ? $dataPost['posted_time'] : '';			
			$max_weight = ( isset($dataPost['max_weight']) && is_numeric($dataPost['max_weight']) ) ? $dataPost['max_weight'] : 48000;			
			$max_length = ( isset($dataPost['max_length']) && is_numeric($dataPost['max_length']) ) ? $dataPost['max_length'] : 48;			
			$dailyFilter = ( isset($dataPost['dailyFilter']) && $dataPost['dailyFilter'] != '' ) ? $dataPost['dailyFilter'] : 'all';			
		
			if ( isset($dataPost['pickup_date']) && $dataPost['pickup_date'] != '' && $dataPost['pickup_date'] != '0000-00-00' && $dataPost['pickup_date'] != 'undefined' ) {
				if ( strpos($dataPost['pickup_date'], ',') !== false ) {
					$datesNewArray = explode(',',$dataPost['pickup_date']);
					$pickupDate = '';
					for ( $i = 0; $i < count($datesNewArray);  $i++ ) {
						$dateTime[] = $datesNewArray[$i];
						$pickupDate .= $datesNewArray[$i].',';
					}
					$this->todayDate = date('m/d/y',strtotime($datesNewArray[0]));
				} else {
					$pickupDate = $dataPost['pickup_date'];
					$dateTime = array (
						'dateTime' => $pickupDate,
					);

					$this->todayDate = date('m/d/y',strtotime($pickupDate));
				}
				
			} else {
				$pickupDate = $this->lang->line('anyTime');
				$dateTime = array ();
			}
		
			if ( $destination_states !=  '' ) {
				$showDestn = $destination_states;
			} else {
				$showDestn =  $this->lang->line('anyWhere');
			}
			
			$todest = !empty($destination_city) ? $destination_city.'-'.$showDestn :$showDestn;

			$newID = $newDest[1].'-'.$newDest[0].' ('.$this->lang->line('withIn').' '.$origin_range.' '.$this->lang->line('milesRadius').')'.$this->lang->line('pickup').' '.$pickupDate.' '.$this->lang->line('withIn').' '.$todest.' ('.$load_type.'/'.$this->lang->line('TLOnly').')';
			$newID = ltrim($newID,'-');
			
			$data['rows']  = $this->commonApiHits( $equipment_type, $dateTime, $posted_time, $origin_country, $origin_range, $destination_city, $destination_states,$destination_range, $dest_country, $load_type);
			
			$data['pickupDate'] 	= $this->pickupDate;

			$price = array();
			
			if( isset($_REQUEST['selectedSRPDriver']) && $_REQUEST['selectedSRPDriver'] != '' && $_REQUEST['selectedSRPDriver'] != 'undefined' ) {
				$lastRecord = $this->Vehicle->getLastLoadRecord($_REQUEST['selectedSRPDriver']);
				if ( !empty($lastRecord) ) {
					$destin = $lastRecord['DestinationCity'].','.$lastRecord['DestinationState'].','.$lastRecord['DestinationCountry'];
				}
			}
			
			if ( isset($dataPost['VehicleId']) && $dataPost['VehicleId'] != '' ) {
				$vehId = $dataPost['VehicleId'];
			} else {
				$vehId = '';
			}
		
			$addClass = '';				// 		no load class for searched loads
			if( isset($dataPost['multistateOriginCheck']) && $dataPost['multistateOriginCheck'] == 0 ) {
				$deadMilesOriginLocation = $this->origin_city.','.$this->origin_state.','.$dataPost['origin_country'];
			} else {
				$deadMilesOriginLocation = '';
			}
			
			$loadsIdArray = array();
			if ( !empty($data['rows'])) {
				$returnArray = $this->giveBestLoads( $data['rows'] ,$loadsIdArray, $vehId, $this->todayDate, $_REQUEST['moreMiles'], $addClass, $dailyFilter, $max_weight, $max_length);
				$this->finalArray = $returnArray[0];
				$loadsIdArray = $returnArray[1];
			}
				
			$newData = array();
			$finArray = array_values($this->finalArray);
			$newData['rows'] = $finArray;
			
			$newData['table_title'] =  $newID;
			$newData['searchLabel'] =  $vehId;
			$newData['tableCount'] =  count($finArray);
			$newData['loadsIdArray'] =  $loadsIdArray;
			$newData['deadMilesOriginLocation'] =  $deadMilesOriginLocation;
			$newData['notShowDataTable'] =  true;
			
			unset($this->finalArray);
			unset($finArray);
			unset($loadsIdArray);
			echo json_encode(array('loadsData'=> $newData),JSON_NUMERIC_CHECK);
		}	
	}

	public function matchLoadDetail(  $truckStopId = null, $vehicleId = null, $loadId = null ) 
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		
		// if ( $truckStopId != '' && $truckStopId != null && $truckStopId != 0 )
		// 	$loadIdArray = $this->Job->getTruckstopRelatedId( $truckStopId );
		
		// if ( !empty($loadIdArray)) {
		// 	$loadId = $loadIdArray['id'];
		// }
		
		$data['vehicleInfo'] = array();
		$data['brokerData'] = array();
		$data['rateSheetUploaded'] = 'no';
		$truckAverage 		= $this->defaultTruckAvg;
	
		if ( $loadId != '' && is_numeric($loadId) ) {
			$this->checkOrganisationIsValid($loadId,'loads');
			$checkBillType = $this->Job->fetchLoadBillType($loadId);
			$checkBillType = (isset($checkBillType) && $checkBillType != '' ) ? $checkBillType : 'broker';
			$jobRecord = $this->Job->FetchSingleJob($loadId, $checkBillType);

			// if ( isset($_POST['loadRequest']) && ($_POST['loadRequest'] == 'addRequest') ){
			// 	$data['brokerData'] = $this->Job->getBrokerForLoadDetail($loadId);
			// } else {
			// 	$data['brokerData'] = $this->Job->getBrokerForLoadDetail($loadId, $jobRecord['billType']);
			
			// 	if ( !empty($data['brokerData'])  ) {
			// 		$jobRecord['MCNumber'] = (isset($data['brokerData']['mc_number']) && $data['brokerData']['mc_number'] != '' ) ? $data['brokerData']['mc_number'] : $jobRecord['MCNumber'];
			// 		$jobRecord['CarrierMC'] = (isset($data['brokerData']['CarrierMC']) && $data['brokerData']['CarrierMC'] != '' ) ? $data['brokerData']['CarrierMC'] : $jobRecord['CarrierMC'];
			// 		$jobRecord['DOTNumber'] = (isset($data['brokerData']['dot_number']) && $data['brokerData']['dot_number'] != '') ? $data['brokerData']['dot_number'] : $jobRecord['DOTNumber'];

			// 		$jobRecord['TruckCompanyName'] = (isset($data['brokerData']['TruckCompanyName']) && $data['brokerData']['TruckCompanyName'] != '' ) ? $data['brokerData']['TruckCompanyName'] :  $jobRecord['TruckCompanyName'];
			// 	}
			// }

			$origin = $jobRecord['OriginCity'].' '.$jobRecord['OriginState'].' '.$jobRecord['OriginCountry'];
			$destination = $jobRecord['DestinationCity'].' '.$jobRecord['DestinationState'].' '.$jobRecord['DestinationCountry'];
			
			if($jobRecord['PickupDate'] == '0000-00-00') {
				$jobRecord['PickupDate']='';
			}
			
			if($jobRecord['DeliveryDate']=='0000-00-00') {
				$jobRecord['DeliveryDate']='';
			}
		
			$jobRecord['PaymentAmount'] = str_replace(',','',$jobRecord['PaymentAmount']);
			$jobRecord['ID'] = @$jobRecord['truckstopID'];
			
			$jobRecord['PaymentAmount1'] = $jobRecord['PaymentAmount'];
			$jobRecord['PostedOn'] = '';
			if( isset($jobRecord['postedDate']) && $jobRecord['postedDate'] != '0000-00-00 00:00:00') {
				$jobRecord['PostedOn'] = date('Y-m-d',strtotime($jobRecord['postedDate']));
			}
			
			$jobRecord['EquipmentTypes']['Code'] = $jobRecord['equipment_options'];
			$jobRecord['EquipmentTypes']['Description'] = $jobRecord['equipment'];
			if ( !isset($_POST['duplicated']) || $_POST['duplicated'] != 1)
				$data['primaryLoadId'] = $loadId;
			else
				$data['primaryLoadId'] = '';

			$jobRecord['totalMiles'] = $jobRecord['deadmiles'] + $jobRecord['Mileage'];
			
			if ( $jobRecord['Stops'] > 0 ) {
				$this->extraStopsDataArray = $this->Job->getExtraStops( $jobRecord['id']);
			} 
			
			if ( $jobRecord['vehicle_id'] != ''  && $jobRecord['vehicle_id'] != null && $jobRecord['vehicle_id'] != 0 && (!isset($_POST['duplicated']) || $_POST['duplicated'] != '1')) {
				if(isset($jobRecord["driver_type"]) && $jobRecord["driver_type"] == "team"){
					$data['vehicleInfo'] = $this->Vehicle->getTeamVehicleInfo( $jobRecord['id']);	
					$jobRecord['assignedDriverName'] = $data['vehicleInfo']['driverName'];
				}else{
					$data['vehicleInfo'] = $this->Vehicle->getVehicleInfo( $jobRecord['vehicle_id'] );	
					//~ $jobRecord['assignedDriverName'] = @$data['vehicleInfo']['first_name'].' '.@$data['vehicleInfo']['last_name'].' - '.$data['vehicleInfo']['label'];
				}
			}
		
			/**Check if rate sheet is uploaded for load or not */
			if ( !isset($_POST['duplicated']) || $_POST['duplicated'] != '1' )
				$data['rateSheetUploaded'] = $this->Job->checkRateSheetUploaded($loadId,'rateSheet');
		} else {
						
			$client   = new SOAPClient($this->wsdl_url);
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

			$jobRecord   = json_decode(json_encode($loadResults),true);
			
			$jobRecord['OriginCountry'] = (isset($jobRecord['OriginCountry']) && $jobRecord['OriginCountry'] != '' ) ? $jobRecord['OriginCountry'] : 'USA';
			$jobRecord['DestinationCountry'] = (isset($jobRecord['DestinationCountry']) && $jobRecord['DestinationCountry'] != '' ) ? $jobRecord['DestinationCountry'] : 'USA';
		
			if ( $jobRecord['PickupDate'] != '' && $jobRecord['PickupDate'] != 'DAILY') {
				$jobRecord['PickupDate'] = date('Y-m-d',strtotime($jobRecord['PickupDate']));
			} else {
				$jobRecord['PickupDate'] = '';
			}
			
			if ( $jobRecord['DeliveryDate'] != '' ) {
				$jobRecord['DeliveryDate'] = date('Y-m-d',strtotime($jobRecord['DeliveryDate']));
			}
		
			$jobRecord['postedDate'] = '';
			$jobRecord['PostedOn'] = '';
			if ( $jobRecord['Entered'] != '' ) {
				$jobRecord['postedDate'] = date('Y-m-d H:i:s',strtotime($jobRecord['Entered']));
				$jobRecord['PostedOn']   = date('Y-m-d',strtotime($jobRecord['Entered']));
			}
			unset($jobRecord['Entered']);

			$jobRecord['equipment'] = $jobRecord['EquipmentTypes']['Description'];
			$jobRecord['JobStatus'] = '';
			$jobRecord['commodity'] = '';
			
			if ( $jobRecord['PaymentAmount'] == '' || $jobRecord['PaymentAmount'] == null || $jobRecord['PaymentAmount'] == 'undefined' ) 
				$jobRecord['PaymentAmount'] = $_POST['calPayments'];
				
			$jobRecord['PaymentAmount'] = str_replace(',','',$jobRecord['PaymentAmount']);
			$jobRecord['PaymentAmount1'] = $jobRecord['PaymentAmount'];
			$data['primaryLoadId'] 		= '';
			$truckAverage = $this->defaultTruckAvg;
			$jobRecord['totalCost'] = $_POST['totalCost'];			
			$jobRecord['deadmiles'] = $_POST['deadmiles'];
	
			if ( isset($_POST['deadMilesLocation']) && $_POST['deadMilesLocation'] != '' ) {
				$deadMilesDestination 	=  $jobRecord['OriginCity'].','.$jobRecord['OriginState'].','.$jobRecord['OriginCountry'];
				$deadMilesDestination 	= trim($deadMilesDestination,',');
				
				$deadMilesArray = $this->User->getTimeMiles($_POST['deadMilesLocation'],$deadMilesDestination);
				if(!empty($deadMilesArray)){
					$jobRecord['deadmiles'] = $deadMilesArray['miles'];					
				} else {
					$deadMilesArray 		= $this->GetDrivingDistance($_POST['deadMilesLocation'],$deadMilesDestination);
					if ( !empty($deadMilesArray) ) {
						$jobRecord['deadmiles'] = ceil($deadMilesArray['distance']);
					}
				}
				
				$total_cost = $this->getCompleteCost($jobRecord['Mileage'],$jobRecord['deadmiles'],$truckAverage);
				if ( !empty($total_cost) )
					$jobRecord['totalCost'] = $total_cost;		
			}			
			
			$jobRecord['pickDate'] = $_POST['originPickDate'];
			$jobRecord['totalMiles'] = $jobRecord['deadmiles'] + $jobRecord['Mileage'];
			$jobRecord['driver_id'] = '';
			$jobRecord['assignedDriverName'] = '';
			
			$jobRecord['postingAddress'] = $jobRecord['TruckCompanyCity'].', '.$jobRecord['TruckCompanyState'];
			if ( isset($jobRecord['PointOfContactPhone']) && $jobRecord['PointOfContactPhone'] != '' ) {
				$jobRecord['PointOfContactPhone'] = $this->sanitize_phone($jobRecord['PointOfContactPhone']);
			}
			
			if ( isset($jobRecord['TruckCompanyPhone']) && $jobRecord['TruckCompanyPhone'] != '' ) {
				$jobRecord['TruckCompanyPhone'] = $this->sanitize_phone($jobRecord['TruckCompanyPhone']);
			}
			
			$jobRecord['PickupTime'] = '';
			$jobRecord['PickupTimeRangeEnd'] = '';
			$jobRecord['DeliveryTime'] = '';
			$jobRecord['DeliveryTimeRangeEnd'] = '';

			if ( $vehicleId != ''  && $vehicleId != null && $vehicleId != 0 ) {
				$jobRecord['vehicle_id'] = $vehicleId;			
				$data['vehicleInfo'] = $this->Vehicle->getVehicleInfoForIteration( $vehicleId );	
				
				$jobRecord['assignedDriverName']	= ($data['vehicleInfo']['driverName']) ? $data['vehicleInfo']['driverName'] : '';
				$jobRecord['assigedDriverFullName'] = ($data['vehicleInfo']['driverName']) ? $data['vehicleInfo']['driverName'] : '';
				$jobRecord['assignedTruckLabel'] 	= 'Truck - '.$data['vehicleInfo']['label'];
				$jobRecord['username'] 				= $data['vehicleInfo']['username'];
				$jobRecord['dispatcher_id'] 		= $data['vehicleInfo']['dispatcherId'];
				$jobRecord['driver_id'] 			= $data['vehicleInfo']['driver_id'];
			}
								
		}

		if ( !empty($data['vehicleInfo']) && $data['vehicleInfo']['fuel_consumption'] != '' ) {
			$truckAverage = (int)($data['vehicleInfo']['fuel_consumption'] / 100);
		} 
		$jobRecord['timer_distance'] = $jobRecord['Mileage'];
		
		$jobRecord['Stops'] = ( isset($jobRecord['Stops']) && $jobRecord['Stops'] != '' ) ? $jobRecord['Stops'] : 0;
		
		$jobRecord['overall_total_rate_mile'] = '';
		if($jobRecord['timer_distance'] > 0)
			$jobRecord['overall_total_rate_mile'] = round($jobRecord['PaymentAmount'] / $jobRecord['timer_distance'], 2);
		
		if ( $jobRecord['PaymentAmount'] != 0 && $jobRecord['PaymentAmount'] != '' && $jobRecord['PaymentAmount'] != null ) {
			$payment = $jobRecord['PaymentAmount'];
		} else {
			$payment = isset($_POST['calPayments']) ? $_POST['calPayments'] : 0;
		}
		
		$jobRecord['overallTotalProfit'] = ($payment - $jobRecord['totalCost']);
		if( isset($payment) && $payment != '' && $payment != 0 ) {	
			$jobRecord['overallTotalProfitPercent'] = round(( ($jobRecord['overallTotalProfit'] / $payment) * 100 ),2);
		} else {
			$jobRecord['overallTotalProfitPercent'] = 0;
		}
		//--------- Code for team task ----------------
		$driverAssignType = 'driver';
		if(isset($jobRecord["driver_type"]) && $jobRecord["driver_type"] == "team"){
			$driverAssignType = 'team';
		}else if(isset($_POST["driverType"]) && ($_POST["driverType"] == "team" || $_POST["driverType"] == "_team")){
			$driverAssignType = 'team';
		}
		//--------- Code for team task ----------------

		$jobRecord['loadedDistanceCost'] = $this->findDieselCosts( $truckAverage,$jobRecord['Mileage'],$this->diesel_rate_per_gallon , 'driverMiles', $driverAssignType );		// driverMiles for calulating fuel cost+ driver miles with cargo cost
		$jobRecord['deadMileDistCost'] = $this->findDieselCosts( $truckAverage,$jobRecord['deadmiles'],$this->diesel_rate_per_gallon, 'driverMiles', $driverAssignType );
		$jobRecord['estimatedFuelCost'] = $this->findDieselCosts( $truckAverage, ( $jobRecord['Mileage'] + $jobRecord['deadmiles'] ),$this->diesel_rate_per_gallon, '', $driverAssignType );
		
		$data['driversList'] = $this->Driver->getDriversList(false,true); 			//Skip driver if he is already in team
				
		//------ Add Drivers as team in drivers dropdown ------------
		$teamList = $this->Driver->getDriversListAsTeam();
		if(is_array($teamList) && count($teamList) > 0){
			foreach ($teamList as $key => $value) {
				$value["label"] = "_team";
				array_unshift($data['driversList'], $value);
			}
		}
		//------ Add Drivers as team in drivers dropdown end---------
		$new = array("id"=>"","profile_image"=>"","driverName"=>"Unassign","label"=>"","username"=>"","latitude"=>"","longitude"=>"","vid"=>"","city"=>"","vehicle_address"=>"","state"=>"");
		
		array_unshift($data['driversList'], $new);
		
		$data['driversListNew'] 	= $this->Driver->getDriversListNew(); 			//Skip driver if he is already in team
		$data['dispatchersList'] 	= $this->Driver->getDispatchersListForLoad();	// fetching list of dispatchers for dropdown
		$data['vehiclesList'] 		= $this->Vehicle->getVehiclesList(); 			// fetching list of vehicles for dropdown
		$teamList 					= $this->Driver->getDriversListAsTeamNew();

		if(is_array($teamList) && count($teamList) > 0){
			foreach ($teamList as $key => $value) {
				$value["label"] = "_team";
				array_unshift($data['driversListNew'], $value);
			}
		}
		
		if ( isset($_POST['duplicated']) && $_POST['duplicated'] == '1' ) {
			$jobRecord['JobStatus']  = '';
			$jobRecord['woRefno'] 	 = '';
			$jobRecord['vehicle_id'] = '';
			$jobRecord['driver_id']	 = '';
			$jobRecord['second_driver_id']	 = '';
			$jobRecord['driver_type'] = '';
			$jobRecord['trailer_id'] = '';
			$jobRecord['dispatcher_id'] = '';
			$jobRecord['user_id'] = '';
			$jobRecord['invoiceNo'] 	 = '';
			$jobRecord['id'] 		 = '';
		}

		$data['encodedJobRecord'] 	= $jobRecord;
		$data['truckStopId'] 		= $truckStopId;
		$originCode 				= $data['encodedJobRecord']['OriginState'];
		$destinationCode 			= $data['encodedJobRecord']['DestinationState'];
		$data['extra_stops_data'] 	= $this->extraStopsDataArray;
		$data['trailerTypes'] 		= $this->getTrailerTypes();
		$data['userRoleId'] 		= $this->userRoleId;

		echo json_encode($data);
	}
	
	/**
	 * method  : get
	 * params  : driverId
	 * return  : driver array or error
	 * comment :Assignment of truck to driver
	 */
	
	public function skipAcl_assignTruckToDriver( $driverId = null ) {
		
		$_POST = json_decode(file_get_contents('php://input'), true);
		$driverAssignType = isset($_POST["driverAssignType"]) && $_POST["driverAssignType"] == "team" ? $_POST["driverAssignType"] : "driver";
		$jobRecord = $_POST['allData'];

		$vehicleInfo = $this->Vehicle->assignTruckDriver( $driverId ,$driverAssignType);

		if(!empty($vehicleInfo['assignedTeamName'])){
			$nameChunk = explode('+',$vehicleInfo['assignedTeamName']);
			$vehicleInfo['avatarText'] = $nameChunk[0][0].$nameChunk[1][1];
		}else{
			

			$vehicleInfo['avatarText'] = $vehicleInfo['first_name'][0].$vehicleInfo['last_name'][0];
		}
			// pr($vehicleInfo);
		
		$error = '';
		$jobRecord['vehicle_id'] = $vehicleInfo['assignedVehicleId'];
		if ( isset($jobRecord['vehicle_id']) && @$jobRecord['vehicle_id'] != '' && isset($jobRecord['PickupDate']) && @$jobRecord['PickupDate'] != '' && @$jobRecord['PickupDate'] != '0000-00-00' ) {
			$result = $this->Job->checkLoadDateExist($jobRecord['PickupDate'], 'pick', $jobRecord['vehicle_id'],@$jobRecord['id']);		// check if load is already assigned for same date to this vehicle
			if ( empty($result) ) 
				$error = '';	
			else
				$error = 'alreadyBookedPickDate';
		}
		
		echo json_encode(array('vehicleDetail' => $vehicleInfo,'error' => $error));
	} 
	
	/**
	 * Unassign truck for job ticket
	 */
	  
	public function skipAcl_UnassignTruckToDriver() {
		$_POST = json_decode(file_get_contents('php://input'), true);
		$jobRecord = $_POST['allData'];
		$driverAssignType = isset($_POST["driverAssignType"]) && $_POST["driverAssignType"] == "team" ? $_POST["driverAssignType"] : "driver";

		$jobRecord['deadmiles'] = 0;
		
		if ( !isset($jobRecord['PaymentAmount']) || $jobRecord['PaymentAmount'] == null || $jobRecord['PaymentAmount'] == '' )
			$jobRecord['PaymentAmount'] = 0;
		else {
			$jobRecord['PaymentAmount'] = str_replace(',','',$jobRecord['PaymentAmount']);
			$jobRecord['PaymentAmount'] = str_replace('$','',$jobRecord['PaymentAmount']);
		}
		$tripDetailId = ''; 
		$newDistanceCal = $jobRecord['Mileage'];
		$truckAverage = 6;
		$xtraInfo = 0;
		//-------------- Code for team task ---------------
		$perExtraStopCharges = $this->extraStopPerStopCharge;
		if($driverAssignType == "team"){
		    $perExtraStopCharges = $this->extraStopPerStopChargeTeam;
		}
		//-------------- Code for team task ---------------
		$results = $this->reCalculateDistances($tripDetailId,$newDistanceCal, $jobRecord, $truckAverage, (count($xtraInfo) * $perExtraStopCharges ), $driverAssignType);
		$jobRecord['loadedDistanceCost'] = $this->findDieselCosts( $truckAverage,$newDistanceCal,$this->diesel_rate_per_gallon,'driverMiles', $driverAssignType);
		$jobRecord['deadMileDistCost'] = $this->findDieselCosts( $truckAverage,$jobRecord['deadmiles'],$this->diesel_rate_per_gallon, 'driverMiles', $driverAssignType);
		$jobRecord['estimatedFuelCost'] = $this->findDieselCosts( $truckAverage, ( $newDistanceCal + $jobRecord['deadmiles'] ),$this->diesel_rate_per_gallon, '', $driverAssignType );
		
		echo json_encode(array('distance' => $results[0], 'overall_total_charge_Cal' => $results[1], 'overall_total_rate_mile_Cal' => $results[2], 'overall_total_profit_Cal' => $results[3], 'overall_total_profit_percent_Cal' => $results[4], 'new_deadmiles_Cal' => $results[5],'loadedDistanceCost' => $jobRecord['loadedDistanceCost'], 'deadMileDistCost' => $jobRecord['deadMileDistCost'], 'estimatedFuelCost' => $jobRecord['estimatedFuelCost']));
	}
	
	/**
	 * Fetch vehicle detail on change from ticket dropdowm
	 */
	 
	public function skipAcl_fetchVehicleInfo( $vehicle_id = null ) {
		$data['vehicleInfo'] = $this->Vehicle->getVehicleInfo($vehicle_id);
		echo json_encode($data);
	} 
	
	/**
	* Fetching truck info for trip detail page on ticket
	*/

	public function skipAcl_fetch_matched_trucks_live( $truckStopId = null , $jobId = null , $itera = '' , $vehicle_id = null )  {

		$fetchAssigedTruck = null;
		$tripDetailPrId = '';
		$truckDetails = array();
		$data['podDocUploaded'] = 'no';
		$driverPayMilesCargo = $this->driver_pay_miles_cargo;
		$_POST = json_decode(file_get_contents('php://input'), true);
		
		$driverAssignType = isset($_POST["driverAssignType"]) && $_POST["driverAssignType"] == "team" ? $_POST["driverAssignType"] : "driver";

		if( $jobId != '' && is_numeric($jobId) ) {
			
			$jobRecord = $this->Job->FetchSingleJob($jobId);
			$this->saveDead = $jobRecord['deadmiles'];		
			$this->saveCalPayment = $jobRecord['PaymentAmount'];
		
			$truckDetails = $this->Job->FindTruckInfo( $jobRecord['id'], $jobRecord['truckstopID'] );
	
			if ( !empty($truckDetails) ) {
				$tripDetailPrId = $truckDetails['id'];
				$this->deadMilePaid = $truckDetails['dead_head_miles_paid'] ;
				$this->deadMileNotPaid = $truckDetails['dead_miles_not_paid'];
				
				if(isset($driverAssignType) && ($driverAssignType == "team") ){
					$this->payForDeadMile_team = $truckDetails['pay_for_dead_head_mile']; 
					$this->driver_pay_miles_cargo_team = $truckDetails['pay_for_miles_cargo']; 
				} else {
					$this->payForDeadMile = $truckDetails['pay_for_dead_head_mile']; 
					$this->driver_pay_miles_cargo = $truckDetails['pay_for_miles_cargo']; 	
				}
				$this->iftaTax = $truckDetails['ifta_taxes'];
				$this->tarps = $truckDetails['tarps'];
				$this->det_time = $truckDetails['detention_time'];
				$this->tollsTax = $truckDetails['tolls'];
			}

			
			/**Checking if proof of delivery uploaded*/
			$data['podDocUploaded'] = $this->Job->checkRateSheetUploaded($jobId,'pod');

		} else {
			$jobRecord = $_POST['jobRecords'];

			if ( $itera == 1 ) {
				$this->saveDead = $_POST['saveDeadMile'];		
				$this->saveCalPayment = $jobRecord['PaymentAmount'];
			}
		}	


		$driverPayMilesCargo = $this->driver_pay_miles_cargo;
		$driverPayForDeadMile = $this->payForDeadMile;
		//--------------- Code for Team task ----------------
		if($driverAssignType == "team"){
			$driverPayMilesCargo = $this->driver_pay_miles_cargo_team;
			$driverPayForDeadMile = $this->payForDeadMile_team;
		}		
		//--------------- Code for Team task end--------------	
		
		$jobSpec = $jobRecord['Weight'];
		$jobCollect = $jobRecord['OriginCity'].' '.$jobRecord['OriginState'];
		$jobDeliver = $jobRecord['DestinationCity'].' '.$jobRecord['DestinationState'];
		
		if( $jobId != '' && is_numeric($jobId) ) {
			$jobVehicle = $jobRecord['equipment'];
			$jobVehicleType = $jobRecord['equipment_options'];
		} else {
			$jobVehicle = $jobRecord['EquipmentTypes']['Description'];
			$jobVehicleType = $jobRecord['EquipmentTypes']['Code'];
		}
		
		$jobWidth = $jobRecord['Width'];
		$jobLength = $jobRecord['Length'];
		if ( $jobWidth == '' || $jobWidth == null || $jobWidth == 0 ) 
			$jobWidth = 0;
			
		if ( $jobLength == '' || $jobLength == null || $jobLength == 0 ) 
			$jobLength = 0;
				
		$loadPaymentAmount = 0;
		if ($jobRecord['PaymentAmount'] != '' && $jobRecord['PaymentAmount'] != 'NA' && $jobRecord['PaymentAmount'] != '0') {
			$loadPaymentAmount = $jobRecord['PaymentAmount'];
		} else {
			$loadPaymentAmount = $this->saveCalPayment;
		}
		
		if ( $vehicle_id == '' || $vehicle_id == 0 || $vehicle_id == null ) 
			$vehicle_id = $jobRecord['vehicle_id'];

		$dataN['vehicles_Available'] = $VehiclesArray = $this->Job->FindVehicles( $vehicle_id);
		// $dataN['vehicles_Available'] = $VehiclesArray = $this->Job->FindVehicles( $jobSpec, $jobCollect, $jobDeliver, $jobVehicle, $fetchAssigedTruck ,$jobVehicleType, $jobId, $this->userId, $jobWidth, $jobLength, $vehicle_id);
		
		//--------------- Code for Team task ----------------
		$perExtraStopCharges = $this->extraStopPerStopCharge;
		if($driverAssignType == "team"){
			$perExtraStopCharges = $this->extraStopPerStopChargeTeam;
		}
		//--------------- Code for Team task ----------------

		$xtraStopCharges = isset($jobRecord['Stops']) ? ($jobRecord['Stops'] * $perExtraStopCharges) : 0;

		if( !empty($VehiclesArray) ) {
			$VehiclesArray = $this->vehicleCalculations( $dataN['vehicles_Available'], $jobRecord['OriginCity'], $jobRecord['OriginState'], $jobRecord['OriginCountry'], $this->saveDead, $jobRecord['Mileage'], $loadPaymentAmount, $this->deadMilePaid, $this->deadMileNotPaid, $driverPayForDeadMile, $driverPayMilesCargo, $this->iftaTax, $this->tarps, $this->det_time, $this->tollsTax, $xtraStopCharges, $truckDetails, '', $driverAssignType);
		}

		$data['vehicles_Available'] = $VehiclesArray;
		//~ $data['vehicles_Available'][0]['xtraInfo']['stops'] = isset($jobRecord['Stops']) ? $jobRecord['Stops'] : 0;
		$data['vehicles_Available'][0]['xtraInfo']['charges'] = $jobRecord['Stops'] * $perExtraStopCharges;
		$data['jobRecord'] 			= $jobRecord;
		$data['truckStopId'] 		= $truckStopId;
		$data['fetchAssigedTruck'] 	= $fetchAssigedTruck;
		$data['tripDetailPrId'] 	= $tripDetailPrId;
		echo json_encode($data);
	}

	/**
	* Method printLoadDetails
	* @param Load ID
	* @return NULL
	*/

	public function skipAcl_printLoadDetails($truckstopId = null,$loadid = null){
	
		if ( $loadid != null && $loadid != 0 && $loadid != '' ) {
			$data 				= $this->matchLoadDetailNew($truckstopId,$loadid);
		} else {
			$loadDetail = $_COOKIE['printTicket'];
			$data['jobDetails'] = json_decode($loadDetail,true);
			$data['jobDetails']['postedDate'] = $data['jobDetails']['PostedOn'];
			$data['brokerData'] = array(
				'postingAddress' => $data['jobDetails']['postingAddress'],
				'city' => isset($data['jobDetails']['city']) ? $data['jobDetails']['city'] : '',
				'state' => isset($data['jobDetails']['state']) ? $data['jobDetails']['state'] : '',
				'zipcode' => isset($data['jobDetails']['zipcode']) ? $data['jobDetails']['zipcode'] : '',
				'MCNumber' => $data['jobDetails']['TMCNumber'],
				'CarrierMC' => isset($data['jobDetails']['CarrierMC']) ? $data['jobDetails']['CarrierMC'] : '',
				'DOTNumber' => $data['jobDetails']['DOTNumber'],
				'TruckCompanyName' => $data['jobDetails']['TruckCompanyName'],
				'brokerStatus' => isset($data['jobDetails']['brokerStatus']) ? $data['jobDetails']['brokerStatus'] : '',
				);
			$_COOKIE['printTicket'] = '';
			delete_cookie('printTicket');
		}
		// pr($data['jobDetails']); 
		$html = $this->load->view('printTemplates/loaddetail',$data); 	
	}

	/**
	* ticket loads detail for printing
	*/

	private function matchLoadDetailNew( $truckStopId = null,$loadId = null ) 
	{
		$data['vehicleInfo'] = array();
		$data['brokerData']  = array();
				
		if ( $loadId != '' && is_numeric($loadId) ) {
			$jobRecord = $this->Job->FetchSingleJobForPrint($loadId);

			if ($jobRecord['broker_id'] != 0 && $jobRecord['broker_id'] != '' ) {
				$data['brokerData'] = $this->Job->getBrokerForLoadDetailForPrint($jobRecord['broker_id'], $jobRecord['billType']);
			}			
			
			$origin = $jobRecord['OriginCity'].' '.$jobRecord['OriginState'].' '.$jobRecord['OriginCountry'];
			$destination = $jobRecord['DestinationCity'].' '.$jobRecord['DestinationState'].' '.$jobRecord['DestinationCountry'];
			
			if($jobRecord['PickupDate'] == '0000-00-00') {
				$jobRecord['PickupDate']='';
			}
			
			if($jobRecord['DeliveryDate']=='0000-00-00') {
				$jobRecord['DeliveryDate']='';
			}
		
			$jobRecord['PaymentAmount'] = str_replace(',','',$jobRecord['PaymentAmount']);
			$jobRecord['postedDate'] 	= ( isset($jobRecord['postedDate']) && $jobRecord['postedDate'] != '0000-00-00 00:00:00' ) ? date('Y-m-d',strtotime($jobRecord['postedDate'])) : '';
		
			$jobRecord['totalMiles'] = $jobRecord['deadmiles'] + $jobRecord['Mileage'];
			
			if ( $jobRecord['Stops'] > 0 ) {
				$this->extraStopsDataArray = $this->Job->getExtraStops( $jobRecord['id']);
			} 
			
			if ( $jobRecord['vehicle_id'] != ''  && $jobRecord['vehicle_id'] != null && $jobRecord['vehicle_id'] != 0 ) {
				if(isset($jobRecord["driver_type"]) && $jobRecord["driver_type"] == "team"){
					$data['vehicleInfo'] = $this->Vehicle->getTeamVehicleInfo( $jobRecord['id']);	
					$jobRecord['assignedDriverName'] = $data['vehicleInfo']['driverName'];
				}else{
					$data['vehicleInfo'] = $this->Vehicle->getVehicleInfo( $jobRecord['vehicle_id'] );	
				}
			}
		
			if ( !empty($data['vehicleInfo']) && $data['vehicleInfo']['fuel_consumption'] != '' ) {
				$truckAverage = (int)($data['vehicleInfo']['fuel_consumption'] / 100);
			} else {
				$truckAverage = $this->defaultTruckAvg;
			}
		}

		$jobRecord['Stops'] = ( isset($jobRecord['Stops']) && $jobRecord['Stops'] != '' ) ? $jobRecord['Stops'] : 0;
		
		$jobRecord['overall_total_rate_mile'] = '';
		if($jobRecord['Mileage'] > 0)
			$jobRecord['overall_total_rate_mile'] = round($jobRecord['PaymentAmount'] / $jobRecord['Mileage'], 2);
		
		$jobRecord['overallTotalProfit'] = ($jobRecord['PaymentAmount'] - $jobRecord['totalCost']);
		$jobRecord['overallTotalProfitPercent'] = 0;
		if( isset($jobRecord['PaymentAmount']) && $jobRecord['PaymentAmount'] != '' && $jobRecord['PaymentAmount'] != 0 ) {	
			$jobRecord['overallTotalProfitPercent'] = round(( ($jobRecord['overallTotalProfit'] / $jobRecord['PaymentAmount']) * 100 ),2);
		}

		$driverAssignType = (isset($jobRecord["driver_type"]) && $jobRecord["driver_type"] == "team") ? $jobRecord["driver_type"] : 'driver';

		$jobRecord['loadedDistanceCost']	= $this->findDieselCosts( $truckAverage,$jobRecord['Mileage'],$this->diesel_rate_per_gallon , 'driverMiles', $driverAssignType );	
		$jobRecord['deadMileDistCost'] 		= $this->findDieselCosts( $truckAverage,$jobRecord['deadmiles'],$this->diesel_rate_per_gallon, 'driverMiles', $driverAssignType );
		$jobRecord['estimatedFuelCost'] 	= $this->findDieselCosts( $truckAverage, ( $jobRecord['Mileage'] + $jobRecord['deadmiles'] ),$this->diesel_rate_per_gallon, '', $driverAssignType );
		
		$this->saveDead = $jobRecord['deadmiles'];		
		$truckDetails = $this->Job->FindTruckInfo( $jobRecord['id'] );
	
		if ( !empty($truckDetails) ) {
			$this->deadMilePaid = $truckDetails['dead_head_miles_paid'] ;
			$this->deadMileNotPaid = $truckDetails['dead_miles_not_paid'];
			
			if(isset($driverAssignType) && ($driverAssignType == "team") ){
				$this->payForDeadMile_team = $truckDetails['pay_for_dead_head_mile']; 
				$this->driver_pay_miles_cargo_team = $truckDetails['pay_for_miles_cargo']; 
			} else {
				$this->payForDeadMile = $truckDetails['pay_for_dead_head_mile']; 
				$this->driver_pay_miles_cargo = $truckDetails['pay_for_miles_cargo']; 	
			}
			$this->iftaTax = $truckDetails['ifta_taxes'];
			$this->tarps = $truckDetails['tarps'];
			$this->det_time = $truckDetails['detention_time'];
			$this->tollsTax = $truckDetails['tolls'];
		}

		
		$driverPayMilesCargo = $this->driver_pay_miles_cargo;
		$driverPayForDeadMile = $this->payForDeadMile;
		$vehicle_id = $jobRecord['vehicle_id'];

		$dataN['vehicles_Available'] = $VehiclesArray = $this->Job->FindVehicles($vehicle_id);
		
		$perExtraStopCharges = $this->extraStopPerStopCharge;
		if($driverAssignType == "team"){
			$perExtraStopCharges 	= $this->extraStopPerStopChargeTeam;
			$driverPayMilesCargo 	= $this->driver_pay_miles_cargo_team;
			$driverPayForDeadMile 	= $this->payForDeadMile_team;
		}
		
		$xtraStopCharges = isset($jobRecord['Stops']) ? ($jobRecord['Stops'] * $perExtraStopCharges) : 0;

		if( !empty($VehiclesArray) ) {
			$VehiclesArray = $this->vehicleCalculations( $dataN['vehicles_Available'], $jobRecord['OriginCity'], $jobRecord['OriginState'], $jobRecord['OriginCountry'], $this->saveDead, $jobRecord['Mileage'], $jobRecord['PaymentAmount'], $this->deadMilePaid, $this->deadMileNotPaid, $driverPayForDeadMile, $driverPayMilesCargo, $this->iftaTax, $this->tarps, $this->det_time, $this->tollsTax, $xtraStopCharges, $truckDetails, '', $driverAssignType);
		}
		$VehiclesArray	[0]['xtraStopCharges'] 	= $xtraStopCharges; 
		$data['tripDetails'] = $VehiclesArray;
		$data['jobDetails'] 	= $jobRecord;
		$data['extraStopsData'] 	= $this->extraStopsDataArray;
		
		return $data;
	} 
	
	public function save_trip_details( $truckstopId = null, $id = null, $tripDetailId = null ) {
		$_POST = json_decode(file_get_contents('php://input'), true);
		$id = $_POST['jobPrimary'];
		$vehicle_id = $_POST['vehicleId'];
		$extraStopsArray = $_POST['extraStops'];
		$loadRequest = @$_POST['loadRequest'];

		$driverAssignType = isset($_POST["driverAssignType"]) && $_POST["driverAssignType"] == "team" ? $_POST["driverAssignType"] : "driver";
		$_POST['jobRecords']['PaymentAmount'] = str_replace(',', '',$_POST['jobRecords']['PaymentAmount']);
		$_POST['jobRecords']['PaymentAmount'] = str_replace('$', '',$_POST['jobRecords']['PaymentAmount']);
		$_POST['jobRecords']['PaymentAmount'] = (double)$_POST['jobRecords']['PaymentAmount'];
		$saveData = $_POST['jobRecords']; 

		$dataN['vehicles_Available'] = $VehiclesArray = $this->Job->FindJobVehicles($_POST['vehicleId'], $this->userId);
		$truckInfo = $_POST['truckDetailsInfo'][0];
		if ( !empty($truckInfo)) {
			$tripDetailPrId = (isset($truckInfo['id']) && $truckInfo['id'] != '' ) ? $truckInfo['id'] : '';
			$this->deadMilePaid = $truckInfo['driver_dead_miles_paid'] ;
			$this->deadMileNotPaid = $truckInfo['driver_dead_miles_not_paid'];
			
			if($driverAssignType == "team"){
				$this->driver_pay_miles_cargo_team = $truckInfo['driver_pay_miles_cargo'];
				$this->payForDeadMile_team = str_replace("$", "", $truckInfo['driver_pay_for_dead_mile']);
			}else{
				$this->driver_pay_miles_cargo = $truckInfo['driver_pay_miles_cargo']; 	
				$this->payForDeadMile = str_replace("$", "", $truckInfo['driver_pay_for_dead_mile']);
			}

			$this->iftaTax 	= $truckInfo['tax_ifta_tax'];
			$this->tarps 	= $truckInfo['tax_tarps'];
			$this->det_time = $truckInfo['tax_det_time'];
			$this->tollsTax = $truckInfo['tax_tolls'];
		}
		
		//--------------- Code for Team task ----------------
		$driverPayMilesCargo = $this->driver_pay_miles_cargo;
		$driverPayForDeadMile = $this->payForDeadMile;
		$perExtraStopCharges = $this->extraStopPerStopCharge;
		if($driverAssignType == "team"){
			$perExtraStopCharges = $this->extraStopPerStopChargeTeam;
			$driverPayMilesCargo = $this->driver_pay_miles_cargo_team;
			$driverPayForDeadMile = $this->payForDeadMile_team;
		}else{
			$driverPayMilesCargo = $this->driver_pay_miles_cargo;
		}

		//--------------- Code for Team task ----------------
		if ( isset($saveData['Stops']) && $saveData['Stops'] != '' ) {
			$this->extraStopCharge = $saveData['Stops'] * $perExtraStopCharges;
		}
		
		if ( $truckInfo['driver_dead_mile'] == '' || $truckInfo['driver_dead_mile'] == null || $truckInfo['driver_dead_mile'] == 0 )
			$truckInfo['driver_dead_mile'] = $saveData['deadmiles'];
			
		if( !empty($VehiclesArray) ) {
			$VehiclesArray = $this->vehicleCalculations( $dataN['vehicles_Available'], @$saveData['OriginCity'], @$saveData['OriginState'], @$saveData['OriginCountry'], $truckInfo['driver_dead_mile'], $saveData['Mileage'], $saveData['PaymentAmount'], $this->deadMilePaid, $this->deadMileNotPaid, $driverPayForDeadMile, $driverPayMilesCargo, $this->iftaTax, $this->tarps, $this->det_time, $this->tollsTax, $this->extraStopCharge, $truckInfo, @$saveData['PickupAddress'],$driverAssignType);
		}
		$VehiclesArray[0]["xtraInfo"]["charges"] = $this->extraStopCharge;
		$VehiclesArray[0]["xtraInfo"]["stops"] = isset($saveData['Stops']) && $saveData['Stops'] != "" ? $saveData['Stops'] : 0;

		$saveData['deadmiles'] = $VehiclesArray[0]['deadMileDist'];
		$saveData['totalCost'] = $VehiclesArray[0]['overall_total_charge'];
		$saveData['overallTotalProfit'] = $VehiclesArray[0]['overall_total_profit'];
		$saveData['overallTotalProfitPercent'] = $VehiclesArray[0]['overall_total_profit_percent'];
		
		if ( $loadRequest == 'addRequest' ){				// for custom added loads
			$result = $this->Job->save_job( $saveData, $extraStopsArray, $id , $vehicle_id );
		}else{
			$result = $this->Job->update_job($id , $vehicle_id, $saveData, $extraStopsArray);
		}
			
			$foundId = $this->Job->findTripDetailsId($truckstopId,$result);
			if($foundId){
				$tripDetailId = $foundId;
			}
		if ( $result ) {
			
			if ( $tripDetailId  != null && $tripDetailId != '' && $tripDetailId  != 'undefined' ) {
				$tripDetailOldInfo = $this->Job->getTripDetailsById($tripDetailId);
				$truckResult = $this->Job->updateTripDetail($tripDetailId , $result, $truckstopId, $truckInfo);
				$tripDetailUpdatedInfo = $this->Job->getTripDetailsById($tripDetailId);
	
				$editedFields = array_diff_assoc($tripDetailUpdatedInfo,$tripDetailOldInfo);
				$totalsBuffer = array();
				if(count($editedFields) > 0){
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited the trip detail info for job ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$result.',\'\',\'\',\'\',\'\',0,\'\')">#'.$result.'</a>';
					foreach ($editedFields as $key => $value) {
						$prevField = isset($tripDetailOldInfo[$key]) ? $tripDetailOldInfo[$key] : "" ;
						
						$bufferedInfo = array();
						if(!empty(trim($prevField)) && !empty(trim($value))){
							$message.= "<br/> - Changed ".ucwords(str_replace("_"," ",$key))." from <i>".$prevField."</i> to <i>".$value."</i>";
						}else if(!empty(trim($value))){
							$message.= "<br/> - Added  <i>".$value."</i> to ".ucwords(str_replace("_"," ",$key));
						}else if (empty(trim($value))){
							$message.= "<br/> - Removed  <i>".$prevField."</i> from ".ucwords(str_replace("_"," ",$key));
						}
					}
					logActivityEvent($result, $this->entity["ticket"], $this->event["edit"], $message, $this->Job,$_POST["srcPage"]);
				}else{
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited the job ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$result.',\'\',\'\',\'\',\'\',0,\'\')">#'.$result.'</a>, but changed nothing.';
					logActivityEvent($result, $this->entity["ticket"], $this->event["edit"], $message, $this->Job,$_POST["srcPage"]);
				}
			}else{
				$truckResult = $this->Job->updateTripDetail($tripDetailId , $result, $truckstopId, $truckInfo);
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> added the trip detail info for job ticket <a 	href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$result.',\'\',\'\',\'\',\'\',0,\'\')">#'.$result.'</a>';	
				logActivityEvent($result, $this->entity["ticket"], $this->event["add"], $message, $this->Job,$_POST["srcPage"]);
			}



			
		}
				
		echo json_encode(array('loadid' => $result,'tripDetailId' => $truckResult,'vehicles_Available' => $VehiclesArray));
	}
	
	private function vehicleCalculations( $vehiclesAvailable = array() , $originCity ='', $originState = '', $originCountry = 'USA', $savedDeadMile = '', $compDistance = null , $loadPaymentAmount = null , $deadMilePaid = null , $deadMileNotPaid = null, $payForDeadMile = null, $payMilesCargo = null , $iftaTax = null, $tarps = null, $det_time = null, $tollsTax = null, $xtraStopCharges = 0, $truckDetails = array(), $pickupAddress = '',$driverAssignType) {
		
		$VehiclesArray = array();
		$dieselFuelPrice = $this->diesel_rate_per_gallon;

		if ( !empty($vehiclesAvailable )) {
			
			$i = 0;
			foreach( $vehiclesAvailable as $vehicle_Available ) {
					if ( $savedDeadMile != '' || $savedDeadMile == 0 ) {	
						$deadMileDist = $savedDeadMile;
					}  else {
						$destinationAddressArray = explode(',',$vehicle_Available['destination_address']);
						$destOriginCity = $destinationAddressArray[1];
						$destOriginState = $destinationAddressArray[0];
						$currentVehcileLocation = $destOriginCity.' '.$destOriginState.' USA';
						if ( $pickupAddress != '' )
							$nextJobOrigin = $pickupAddress;
						else
							$nextJobOrigin = $originCity.' '.$originState.' '.$originCountry;
							
						$miles  = $this->GetDrivingDistance($currentVehcileLocation,$nextJobOrigin);
						$deadMileDist = ceil($miles['distance']);
					}				
					
					$VehiclesArray[$i]['originToDestination'] = $compDistance;
					$originToDestDist = $compDistance;
										
					if ( isset($truckDetails['deadmiles_dist_actual']) ) {
						$this->deadMilesActual = $truckDetails['deadmiles_dist_actual'];
					} else if ( isset($truckDetails['driver_dead_mile_actual']) ) {
						$this->deadMilesActual = $truckDetails['driver_dead_mile_actual'];
					} 
					
					$VehiclesArray[$i]['deadMileDist'] = $deadMileDist;
					
					$VehiclesArray[$i]['total_complete_distance'] = $originToDestDist + $deadMileDist;
					$VehiclesArray[$i]['origin_to_dest_actual'] = @$truckDetails['origin_to_dest_actual'] + $this->deadMilesActual;
					if ( isset($vehicle_Available['fuel_consumption']) && $vehicle_Available['fuel_consumption'] != null ) {
						$truckAverage = (int)($vehicle_Available['fuel_consumption'] / 100);
					} else {
						$truckAverage = $this->defaultTruckAvg;
					}
					
					$truckAverage = ( $truckAverage != null && $truckAverage != '' && $truckAverage != 0 ) ? $truckAverage : 6;
					$VehiclesArray[$i]['fuel_consumption'] = $truckAverage;
					// $VehiclesArray[$i]['vehicle_average_actual'] = @$truckDetails['vehicle_average_actual'];
					
					$loadedDistanceCost = $this->findDieselCosts( $truckAverage,$originToDestDist,$this->diesel_rate_per_gallon , 'driverMiles', $driverAssignType );		
					$deadMileDistCost = $this->findDieselCosts( $truckAverage,$deadMileDist,$this->diesel_rate_per_gallon, 'driverMiles', $driverAssignType );

					//$VehiclesArray[$i]['gallon_needed'] = ceil($originToDestDist/$truckAverage);
					$VehiclesArray[$i]['gallon_needed'] = round(($originToDestDist + $deadMileDist)/$truckAverage,2);
					// $VehiclesArray[$i]['gallon_needed_actual'] = @$truckDetails['gallon_needed_actual'];
					
					$VehiclesArray[$i]['diesel_rate_per_gallon'] = $dieselFuelPrice;
					// $VehiclesArray[$i]['avg_cost_diesel_actual'] = @$truckDetails['avg_cost_diesel_actual'];
					
					$VehiclesArray[$i]['comp_diesel_cost'] = $VehiclesArray[$i]['diesel_rate_per_gallon'] * $VehiclesArray[$i]['gallon_needed'];
					// $VehiclesArray[$i]['comp_diesel_cost_actual'] = $VehiclesArray[$i]['avg_cost_diesel_actual'] * $VehiclesArray[$i]['gallon_needed_actual'];
					
					$VehiclesArray[$i]['originToDestDistDriver'] = $originToDestDist;
					// $VehiclesArray[$i]['originToDestDistDriver_actual'] = @$truckDetails['origin_to_dest_actual'];
					
					$VehiclesArray[$i]['driver_dead_mile'] = $deadMileDist;
					// $VehiclesArray[$i]['driver_dead_mile_actual'] = $this->deadMilesActual;
					
					$VehiclesArray[$i]['driver_dead_miles_paid'] = $deadMilePaid;
					// $VehiclesArray[$i]['dead_head_miles_paid_actual'] = @$truckDetails['dead_head_miles_paid_actual'];
					
					$VehiclesArray[$i]['driver_dead_miles_not_paid'] = $deadMileNotPaid;
					// $VehiclesArray[$i]['dead_miles_not_paid_actual'] = @$truckDetails['dead_miles_not_paid_actual'];
					
					$VehiclesArray[$i]['driver_pay_for_dead_mile'] = $payForDeadMile;
					// $VehiclesArray[$i]['pay_for_dead_head_mile_actual'] = @$truckDetails['pay_for_dead_head_mile_actual'];
					
					$VehiclesArray[$i]['driver_dead_mile_paid'] = (float)($VehiclesArray[$i]['driver_dead_miles_paid'] * $VehiclesArray[$i]['driver_pay_for_dead_mile']);
					// $VehiclesArray[$i]['driver_dead_mile_paid_actual'] = (float)($VehiclesArray[$i]['dead_head_miles_paid_actual'] * $VehiclesArray[$i]['pay_for_dead_head_mile_actual']);
					
					$VehiclesArray[$i]['driver_pay_miles_cargo'] = $payMilesCargo;
					// $VehiclesArray[$i]['pay_for_miles_cargo_actual'] = @$truckDetails['pay_for_miles_cargo_actual'];
					
					$VehiclesArray[$i]['driver_amount_cargo'] = (float)($VehiclesArray[$i]['driver_pay_miles_cargo'] * ($originToDestDist + $deadMileDist));
					// $VehiclesArray[$i]['driver_amount_cargo_actual'] = (float)($VehiclesArray[$i]['pay_for_miles_cargo_actual'] * $originToDestDist);
					
					$VehiclesArray[$i]['driver_due_driver'] = (float)($VehiclesArray[$i]['driver_amount_cargo'] + $VehiclesArray[$i]['driver_dead_mile_paid']) + $xtraStopCharges;
					// $VehiclesArray[$i]['driver_due_driver_actual'] = (float)($VehiclesArray[$i]['driver_amount_cargo_actual'] + $VehiclesArray[$i]['driver_dead_mile_paid_actual'])+$xtraStopCharges;
					
					$VehiclesArray[$i]['tax_ifta_tax'] = str_replace('$', '',$iftaTax);
					// $VehiclesArray[$i]['ifta_taxes_actual'] = str_replace('$', '',@$truckDetails['ifta_taxes_actual']);
					
					$VehiclesArray[$i]['tax_tarps'] = str_replace('$', '',$tarps);
					// $VehiclesArray[$i]['tarps_actual'] = str_replace('$', '',@$truckDetails['tarps_actual']);
					
					$VehiclesArray[$i]['tax_det_time'] = str_replace('$', '',$det_time);
					// $VehiclesArray[$i]['detention_time_actual'] = str_replace('$', '',@$truckDetails['detention_time_actual']);
					
					$VehiclesArray[$i]['tax_tolls'] = str_replace('$', '',$tollsTax);
					// $VehiclesArray[$i]['tolls_actual'] = str_replace('$', '',@$truckDetails['tolls_actual']);
					
					$VehiclesArray[$i]['tax_total_charge'] = (float)($VehiclesArray[$i]['tax_ifta_tax'] + $VehiclesArray[$i]['tax_tarps'] + $VehiclesArray[$i]['tax_det_time'] + $VehiclesArray[$i]['tax_tolls']);
					// $VehiclesArray[$i]['tax_total_charge_actual'] = (float)($VehiclesArray[$i]['ifta_taxes_actual'] + $VehiclesArray[$i]['tarps_actual'] + $VehiclesArray[$i]['detention_time_actual'] + $VehiclesArray[$i]['tolls_actual']);
					
					$VehiclesArray[$i]['overall_total_payment_amount'] = $loadPaymentAmount;
					
					$VehiclesArray[$i]['overall_total_charge'] = round($deadMileDistCost + $loadedDistanceCost + $xtraStopCharges + $VehiclesArray[$i]['tax_total_charge'], 2);
					if ( $loadPaymentAmount != '' && $loadPaymentAmount != 0 && $loadPaymentAmount != null ) {
						$VehiclesArray[$i]['EditCalulations'] = true;
						$VehiclesArray[$i]['overall_total_profit'] = round($loadPaymentAmount - $VehiclesArray[$i]['overall_total_charge'], 2);
						$VehiclesArray[$i]['overall_total_profit_percent'] = getProfitPercent($VehiclesArray[$i]['overall_total_profit'],$loadPaymentAmount );

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
		return $VehiclesArray;
	}
	
	private function getUnFinishedChain($driverID){
		$this->load->helper('truckstop');
		$allChains = $this->Job->getUnFinishedChain($driverID); //$driverID is a vehicleId
		$userChain = array();
		
		if ( !empty($allChains) ) {
			foreach ($allChains as $key => $chain) {
				
				foreach ($chain as $laneKEy => $laneValue) {
					$loadDetail = getLoadDetail($laneValue, null, $this->username, $this->password, $this->id, $this->Job);
					
					if(empty($loadDetail['encodedJobRecord']['ID']) && $key == 0){
						//~ $this->destroyLoadsChain($driverID);
						$this->Job->destroyLoadsChain($driverID);
						$userChain[] = array();
					}else{
						$userChain[] = $loadDetail;
					}
				}
			}
		}
		return $userChain;
	}
	
	/**
	* calculating distance b/w two points when address is changed in ticket
	*/

	public function skipAcl_calculateNewDistance( $loadId = null, $tripDetailId = null ) {
		
		$obj = json_decode(file_get_contents('php://input'),true);

		$vehicleId = (isset($obj['vehicleId']) && $obj['vehicleId'] != '' && $obj['vehicleId'] != null ) ? $obj['vehicleId'] : '';
		$jobRecord = $obj['allData'];
		
		$joinedAdresses = $this->joinAddress($jobRecord);
		$originLocation = $jobRecord["PickupAddress"] =  $joinedAdresses["PickupAddress"];
		$destinLocation = $jobRecord["DestinationAddress"] =  $joinedAdresses["DestinationAddress"];

		$searchType = $obj['searchType'];
		$extraStops = array();
		if ( !empty($obj['extraStopsAdded']) ) {
			$extraStops = $obj['extraStopsAdded'];
		}
		
		$driverAssignType = isset($obj["driverAssignType"]) && $obj["driverAssignType"] == "team" ? $obj["driverAssignType"] : "driver";
		$calculatePlanDeadmiles = ( isset($obj['deadMileCalculate']) && $obj['deadMileCalculate'] ) ? $obj['deadMileCalculate'] : '';
		$leftSideColumnPlanPage = ( isset($obj['leftSideColumnPlanPage']) && $obj['leftSideColumnPlanPage'] != '' ) ? $obj['leftSideColumnPlanPage'] : '';
		$setDeadMilePage = ( isset($obj['setDeadMilePage']) && $obj['setDeadMilePage'] ) ? $obj['setDeadMilePage'] : '';		

		if ( $vehicleId != '' && $vehicleId != null && $vehicleId != 0 ) {
			
			if ( $leftSideColumnPlanPage != null ) {				// calculating dead miles for load editing from left column on plan page
				if ( $leftSideColumnPlanPage == 0 ) {											// calculating from last deliverd saved load if value is 0
					$returnArray = $this->calculateDeadMiles($jobRecord,$vehicleId, $setDeadMilePage);
				} else {
					$unFinishedChain = $this->getUnFinishedChain($vehicleId);
					if(count($unFinishedChain) > 0) {							// calculating deadmiles if chain is built for vehicle
						$savedChain = $unFinishedChain[$leftSideColumnPlanPage - 1];						// caluulating dead miles from 1 location previous for left side columns
						$returnArray = $this->calucalateDeadMilesFromChain($savedChain,$vehicleId);
					} else {																		// Recalculating deadmiles if no chain is built for vehicle
						$returnArray = $this->calculateDeadMiles($jobRecord,$vehicleId, $setDeadMilePage);
					}
				}
			} else if ( $calculatePlanDeadmiles != '' && $calculatePlanDeadmiles != null ) {			// calculation check for plan page
				$returnArray = $this->calucalateDeadMilesFromChain($calculatePlanDeadmiles,$vehicleId,'arrayAvailable');			// ArrayAvailabe for the array available from plan  page
			} else { 
				if ( $setDeadMilePage == 1 ) {
					$unFinishedChain = $this->getUnFinishedChain($vehicleId);
					if(count($unFinishedChain) > 0) {							// calculating deadmiles if chain is built for vehicle
						$savedChain = end($unFinishedChain);
						$returnArray = $this->calucalateDeadMilesFromChain($savedChain,$vehicleId);
					} else {																		// Recalculating deadmiles if no chain is built for vehicle
						$returnArray = $this->calculateDeadMiles($jobRecord,$vehicleId,$setDeadMilePage);
					}
				} else {
					$returnArray = $this->calculateDeadMiles($jobRecord,$vehicleId); //done
				}
			}
			
			$newDestinLocation = $returnArray[0];
			$truckAverage = $returnArray[1];
		} else {
			$truckAverage = $this->defaultTruckAvg;
			$newDestinLocation = '';
		}
		//$resultDeadMile  = $this->GetDrivingDistance($originLocation,$newDestinLocation);
		if ( ($searchType == 'origin') || (@$obj['requestType'] == 'addRequest') || $searchType == 'extraStop' ) {
			/**
			 * Recalculating the dead miles
			 */
		  
			if ( $newDestinLocation != '' ) {
				$newDeadMiles = 0;
				$dataMiles = $this->User->getTimeMiles( $originLocation, $newDestinLocation);
				if(!empty($dataMiles)){
					$newDeadMiles = str_replace(',','',$dataMiles['miles']);
				} else {
					$resultDeadMile  = $this->GetDrivingDistance($originLocation,$newDestinLocation);
					if ( !empty($resultDeadMile) ) {
						$newDeadMiles = str_replace(',','',$resultDeadMile['distance']);
						$newDeadMiles = ceil($newDeadMiles);
					}
				}
				
				if ( $newDeadMiles == '1ft' || strpos($newDeadMiles,'ft') !== false ) 
					$newDeadMiles = 0;
					
				$jobRecord['deadmiles'] = $newDeadMiles;
			}
		}
	
			/**
			 * Recalculating the total miles
			 */

		if ( $searchType != 'extraStopss' ) {
		
			if ( isset($jobRecord['Stops']) && $jobRecord['Stops'] != '' && $jobRecord['Stops'] > 0 && !empty($extraStops) ) {
				$newDistance = 0;
				for( $i = 0; $i < $jobRecord['Stops']; $i++ ) {
					$street = isset($extraStops['extraStopAddress_'.$i]) ? trim($extraStops['extraStopAddress_'.$i]) : '';
					$city = isset($extraStops['extraStopCity_'.$i]) ? trim($extraStops['extraStopCity_'.$i]) : '';
					$state = isset($extraStops['extraStopState_'.$i]) ? trim($extraStops['extraStopState_'.$i]) : '';
					$country = isset($extraStops['extraStopCountry_'.$i]) ? trim($extraStops['extraStopCountry_'.$i]) : '';
					$place = $street.','.$city.','.$state.','.$country;
					$place = trim($place,',');
					
					if ( $i == 0 ) {
						$addDistance = $this->returnDistance($originLocation,$place);
						$originLocation = $place;
						$newDistance += $addDistance[0];
					} else {
						$addDistance = $this->returnDistance($originLocation,$place);
						$originLocation = $place;
						$newDistance += $addDistance[0];
					}
				}
			
				if ( isset($destinLocation) && $destinLocation != '' ) {
					$addDistance = $this->returnDistance($originLocation,$destinLocation);
					$newDistance += $addDistance[0];
					$extraStopTime = $addDistance[1];
				}
			} else {				
				$addDistance = $this->returnDistance($originLocation,$destinLocation);
				$newDistance = $addDistance[0];
				$extraStopTime = $addDistance[1];
			}
		} else {
			$newDistance = isset($obj['calculatedDist']) ? $obj['calculatedDist'] : 0;
			$extraStopTime = '';
		}
				
		if ( $newDistance == '1ft' || strpos($newDistance,'ft') !== false  ) 
			$newDistance = 0;
			
		$newDistanceCal = $newDistance;
		$exceedFlag = false;

		//-------------- Code for team task ---------------
		$perExtraStopCharges = $this->extraStopPerStopCharge;
		if($driverAssignType == "team"){
			$perExtraStopCharges = $this->extraStopPerStopChargeTeam;
		}
		//-------------- Code for team task ---------------
		
		if ( $vehicleId =='' || $vehicleId == null )
			$jobRecord['deadmiles'] = 0;
		
		if ( !isset($jobRecord['PaymentAmount']) || $jobRecord['PaymentAmount'] == null || $jobRecord['PaymentAmount'] == '' )
			$jobRecord['PaymentAmount'] = 0;
		else {
			$jobRecord['PaymentAmount'] = str_replace(',','',$jobRecord['PaymentAmount']);
			$jobRecord['PaymentAmount'] = str_replace('$','',$jobRecord['PaymentAmount']);
		}
				
		if ( isset($obj['requestType']) && $obj['requestType'] == 'addRequest' ) {    // calculation for add request
			$results = $this->reCalculateDistances($tripDetailId,$newDistanceCal, $jobRecord, $truckAverage, (@$jobRecord['Stops'] * $perExtraStopCharges ),$driverAssignType); 
			$jobRecord['loadedDistanceCost'] = $this->findDieselCosts( $truckAverage,$newDistanceCal,$this->diesel_rate_per_gallon,'driverMiles',$driverAssignType);
			$jobRecord['deadMileDistCost'] = $this->findDieselCosts( $truckAverage,$jobRecord['deadmiles'],$this->diesel_rate_per_gallon, 'driverMiles',$driverAssignType);
			$jobRecord['estimatedFuelCost'] = $this->findDieselCosts( $truckAverage, ( $newDistanceCal + $jobRecord['deadmiles'] ),$this->diesel_rate_per_gallon, '',$driverAssignType );
			
			echo json_encode(array('distance' => $results[0], 'overall_total_charge_Cal' => $results[1], 'overall_total_rate_mile_Cal' => $results[2], 'overall_total_profit_Cal' => $results[3], 'overall_total_profit_percent_Cal' => $results[4], 'new_deadmiles_Cal' => $results[5],'loadedDistanceCost' => $jobRecord['loadedDistanceCost'], 'deadMileDistCost' => $jobRecord['deadMileDistCost'], 'estimatedFuelCost' => $jobRecord['estimatedFuelCost']));
		} else if ( $newDistanceCal != '' && $newDistanceCal != 0 ) {

			$results = $this->reCalculateDistances($tripDetailId,$newDistanceCal, $jobRecord, $truckAverage, (@$jobRecord['Stops'] * $perExtraStopCharges ),$driverAssignType); //done (found 1 occurness)
			
			$jobRecord['loadedDistanceCost'] = $this->findDieselCosts( $truckAverage,$newDistanceCal,$this->diesel_rate_per_gallon, 'driverMiles', $driverAssignType); //done (found 1 occurness)
			$jobRecord['deadMileDistCost'] = $this->findDieselCosts( $truckAverage,$jobRecord['deadmiles'],$this->diesel_rate_per_gallon, 'driverMiles', $driverAssignType); //done (found 1 occurness)
			$jobRecord['estimatedFuelCost'] = $this->findDieselCosts( $truckAverage, ( $newDistanceCal + $jobRecord['deadmiles'] ),$this->diesel_rate_per_gallon, '',$driverAssignType ); 
			
			echo json_encode(array('distance' => $results[0], 'overall_total_charge_Cal' => $results[1], 'overall_total_rate_mile_Cal' => $results[2], 'overall_total_profit_Cal' => $results[3], 'overall_total_profit_percent_Cal' => $results[4], 'new_deadmiles_Cal' => $results[5], 'loadedDistanceCost' => $jobRecord['loadedDistanceCost'], 'deadMileDistCost' => $jobRecord['deadMileDistCost'], 'estimatedFuelCost' => $jobRecord['estimatedFuelCost']));
		}	
		else {
			echo json_encode(array('distance' => $newDistance));
		}	
	}
	
	private function returnDistance( $originLocation = '', $destinLocation = '' ) {				// return distance b/w two locations from api
		$newDistance = 0;
		$extraStopTime = '';
		$dataMiles = $this->User->getTimeMiles($originLocation,$destinLocation);
		if( !empty($dataMiles) ) {
			$newDistance = str_replace(',','',$dataMiles['miles']);
			$extraStopTime = $dataMiles['estimated_time'];
		} else {
			$result = $this->GetDrivingDistance( $originLocation, $destinLocation);
			if ( !empty($result) ) {
				$newDistance = ceil(str_replace(',','',$result['distance']));
				$extraStopTime = $result['time'];
			} else {
				$newDistance = 0;
				$extraStopTime = '';
			}
		}
		
		return array($newDistance,$extraStopTime);
	}
	
	/**
	 * Calculating the deadmiles on change of origin or destination and return deadmiles address
	 */
	
	private function calculateDeadMiles( $jobRecord = array(), $vehicleId = null , $planDeadmiles = null ) {
		$newDest = array();
		//~ $pickupDate = (isset($jobRecord['PickupDate']) && $jobRecord['PickupDate'] != '' ) ? date('m/d/y',strtotime($jobRecord['PickupDate'])) : '';
		$pickupDate = (isset($jobRecord['PickupDate']) && $jobRecord['PickupDate'] != '' ) ? $jobRecord['PickupDate'] : '';
		if ( $pickupDate == '' || $pickupDate == '01/01/70' || $pickupDate == '00/00/00' || $pickupDate == '0000-00-00' )
			$pickupDate = '';
		
		$updateLocations = $this->Vehicle->updateTruckDestination($vehicleId, $pickupDate, $planDeadmiles);
		$vehicle_fuel_consumption = $this->Vehicle->get_vehicles_fuel_consumption($vehicleId);

		if ( !empty($vehicle_fuel_consumption ) ) {
			$truckAverage =round( 100/$vehicle_fuel_consumption[0]['fuel_consumption'],2);
			// $truckAverage =(int)($vehicle_fuel_consumption[0]['fuel_consumption']/100);
		} else {
			$truckAverage = $this->defaultTruckAvg;
		}

		if ( !empty($updateLocations) ) {	
			array_push($newDest,$updateLocations['DestinationState'],$updateLocations['DestinationCity']);
			$country = $updateLocations['DestinationCountry'];	
		} else {
			if ( $vehicle_fuel_consumption[0]['destination_address'] != '' ) {
				$newDest = explode(',',$vehicle_fuel_consumption[0]['destination_address']);
			} else {
				array_push($newDest,$vehicle_fuel_consumption[0]['state'],$vehicle_fuel_consumption[0]['city']);
			}
			$country = 'USA';
		}
		
		$newDestinLocation = $newDest[1].', '.$newDest[0].', '.$country;

		return array($newDestinLocation,$truckAverage);
	}
	 
	/**
	 * Calulating dead miles from saved chain for plan page
	 */
	
	private function calucalateDeadMilesFromChain( $savedChain = array() , $vehicleId = null, $parameter = '' ) {
		$newDest = array();
		if ( $parameter == 'arrayAvailable' ) {
			$this->origin_city =  $savedChain['OriginCity'];
			$this->origin_state = $savedChain['OriginState'];
			$country = 'USA';
			if ( $vehicleId != $savedChain['driverID'] ) {					// if vehicle changed from dropdown is another from selected vehicle on plan page chain
				$planDeadmiles = 1;
				$updateLocations = $this->Vehicle->updateTruckDestination($vehicleId, $savedChain['nextPickupDate'], $planDeadmiles);
				if ( !empty($updateLocations) ) {
					$this->origin_city =  $updateLocations['DestinationCity'];
					$this->origin_state = $updateLocations['DestinationState'];
					$country = $updateLocations['DestinationCountry'];
				}
			}
		} else {
			$this->origin_city =  $savedChain['encodedJobRecord']['DestinationCity'];
			$this->origin_state = $savedChain['encodedJobRecord']['DestinationState'];
			$country = $savedChain['encodedJobRecord']['DestinationCountry'];
		}
		
		array_push($newDest,$this->origin_state,$this->origin_city);
		$vehicle_fuel_consumption = $this->Vehicle->get_vehicles_fuel_consumption($vehicleId);
		if ( !empty($vehicle_fuel_consumption ) ) {
			// $truckAverage =(int)($vehicle_fuel_consumption[0]['fuel_consumption']/100);
			$truckAverage =round( 100 / $vehicle_fuel_consumption[0]['fuel_consumption'],2);
		} else {
			$truckAverage = $this->defaultTruckAvg;
		}
		$newDestinLocation = $newDest[1].', '.$newDest[0].', '.$country;
		return array($newDestinLocation,$truckAverage);
	}
	 
	private function reCalculateDistances($tripDetailId = null ,$newDistanceCal = null , $jobRecord = array(), $truckAverage = 6, $xtraStopCharges = 0,$driverAssignType="driver") {
		$truckDetails = $this->Job->FindTruckInfoDetail( $tripDetailId, @$jobRecord['ID'] );

		if ( !empty($truckDetails) ) {
			$tripDetailPrId = $truckDetails['id'];
			$this->deadMilePaid = $truckDetails['dead_head_miles_paid'] ;
			$this->deadMileNotPaid = $truckDetails['dead_miles_not_paid'];
			if ( $driverAssignType == "team" ) {
				$this->payForDeadMile_team = $truckDetails['pay_for_dead_head_mile']; 
				$this->driver_pay_miles_cargo_team = $truckDetails['pay_for_miles_cargo']; 
			} else {
				$this->payForDeadMile = $truckDetails['pay_for_dead_head_mile']; 
				$this->driver_pay_miles_cargo = $truckDetails['pay_for_miles_cargo']; 
			}
			$this->iftaTax = $truckDetails['ifta_taxes'];
			$this->tarps = $truckDetails['tarps'];
			$this->det_time = $truckDetails['detention_time'];
			$this->tollsTax = $truckDetails['tolls'];
		}
		//--------------- Code for Team task ----------------
		$driverPayMilesCargo = $this->driver_pay_miles_cargo;
		$driverPayForDeadMile = $this->payForDeadMile;
		if($driverAssignType == "team"){
			$driverPayMilesCargo = $this->driver_pay_miles_cargo_team;
			$driverPayForDeadMile = $this->payForDeadMile_team;
		}		
		//--------------- Code for Team task end--------------
		//$gallon_needed_Cal = ceil(($newDistanceCal + $jobRecord['deadmiles']) / $truckAverage);
		$gallon_needed_Cal = ceil($newDistanceCal   / $truckAverage);

		$comp_diesel_cost_Cal = $this->diesel_rate_per_gallon * $gallon_needed_Cal;
		
		$driver_dead_mile_paid_Cal = (float)($this->deadMilePaid * $driverPayForDeadMile);

		//$driver_amount_cargo_Cal = (float)($driverPayMilesCargo * ($newDistanceCal + $jobRecord['deadmiles']));
		$driver_amount_cargo_Cal = (float)($driverPayMilesCargo * ($newDistanceCal ));

		$driver_due_driver_Cal = (float)($driver_amount_cargo_Cal + $driver_dead_mile_paid_Cal + $xtraStopCharges);
	
		$tax_total_charge_Cal = (float)($this->iftaTax + $this->tarps + $this->det_time + $this->tollsTax);
		
		$loadedDistanceCost = $this->findDieselCosts( $truckAverage,$newDistanceCal,$this->diesel_rate_per_gallon, 'driverMiles',$driverAssignType);
		$deadMileDistCost = $this->findDieselCosts( $truckAverage,$jobRecord['deadmiles'],$this->diesel_rate_per_gallon, 'driverMiles', $driverAssignType );
		

		$overall_total_charge_Cal = round( $deadMileDistCost + $loadedDistanceCost + $xtraStopCharges + $tax_total_charge_Cal, 2);
		$jobRecord['PaymentAmount'] = ( isset($jobRecord['PaymentAmount'])  && $jobRecord['PaymentAmount'] != '' ) ? str_replace(',','',$jobRecord['PaymentAmount']) : 0;
		$jobRecord['PaymentAmount'] = str_replace('$','',$jobRecord['PaymentAmount']);
		
		$overall_total_profit_Cal = round(($jobRecord['PaymentAmount'] - $overall_total_charge_Cal), 2);
		
		if ( $jobRecord['PaymentAmount'] == 0 || $jobRecord['PaymentAmount'] == null )
			$overall_total_profit_percent_Cal = 0;
		else
			$overall_total_profit_percent_Cal = getProfitPercent($overall_total_profit_Cal,$jobRecord['PaymentAmount']);
		$overall_total_rate_mile_Cal = 0;
		if($newDistanceCal > 0){
			$overall_total_rate_mile_Cal = round($jobRecord['PaymentAmount'] / $newDistanceCal, 2);	
		}
		
		
		return array($newDistanceCal,$overall_total_charge_Cal,$overall_total_rate_mile_Cal,$overall_total_profit_Cal,$overall_total_profit_percent_Cal,$jobRecord['deadmiles']);
	}
	
	/**
	 * 
	 *  Removing Extra stop
	 */ 
	
	public function skipAcl_removeExtraStopRecord( $loadId = null, $tripDetailId = null, $extraStopId = null ) {
		$obj = json_decode(file_get_contents('php://input'),true);

		$vehicleId = (isset($obj['vehicleId']) && $obj['vehicleId'] != '' && $obj['vehicleId'] != null ) ? $obj['vehicleId'] : 0;
		$jobRecord = $obj['allData'];
		$joinedAdresses = $this->joinAddress($jobRecord);
		$originLocation = $jobRecord["PickupAddress"] =  $joinedAdresses["PickupAddress"];
		$destinLocation = $jobRecord["DestinationAddress"] =  $joinedAdresses["DestinationAddress"];
		
		$driverAssignType = isset($obj["driverAssignType"]) && $obj["driverAssignType"] == "team" ? $obj["driverAssignType"] : "driver";
		$extraStops = array();
		if ( !empty($obj['extraStopsAdded']) ) {
			$extraStops = $obj['extraStopsAdded'];
		}
		
		$truckAverage = $this->defaultTruckAvg;
		if ( $vehicleId != '' && $vehicleId != null ) {
			$vehicle_fuel_consumption = $this->Vehicle->get_vehicles_fuel_consumption($vehicleId);
			$truckAverage =round( 100/$vehicle_fuel_consumption[0]['fuel_consumption'],2);
			// $truckAverage =(int)($vehicle_fuel_consumption[0]['fuel_consumption']/100);
		}
		
		$truckAverage = ( $truckAverage != 0 && $truckAverage != '' && $truckAverage != null ) ? $truckAverage : $this->defaultTruckAvg;
			
		if ( isset($jobRecord['Stops']) && $jobRecord['Stops'] != '' && $jobRecord['Stops'] > 0 && !empty($extraStops) ) {
			$newDistance = 0;
			
			for( $i = 0; $i < $jobRecord['Stops']; $i++ ) {
				$street = isset($extraStops['extraStopAddress_'.$i]) ? trim($extraStops['extraStopAddress_'.$i]) : '';
				$city = isset($extraStops['extraStopCity_'.$i]) ? trim($extraStops['extraStopCity_'.$i]) : '';
				$state = isset($extraStops['extraStopState_'.$i]) ? trim($extraStops['extraStopState_'.$i]) : '';
				$country = isset($extraStops['extraStopCountry_'.$i]) ? trim($extraStops['extraStopCountry_'.$i]) : '';
				$place = $street.','.$city.','.$state.','.$country;
				$place = trim($place,',');
				
				if ( $i == 0 ) {
					$addDistance = $this->returnDistance($originLocation,$place);
					$originLocation = $place;
					$newDistance += $addDistance[0];
				} else {
					$addDistance = $this->returnDistance($originLocation,$place);
					$originLocation = $place;
					$newDistance += $addDistance[0];
				}
				 
			}
				
			if ( isset($destinLocation) && $destinLocation != '' ) {
				$addDistance = $this->returnDistance($originLocation,$destinLocation);
				$newDistance += $addDistance[0];
				$extraStopTime = $addDistance[1];
			}
		} else {				
			$addDistance = $this->returnDistance($originLocation,$destinLocation);
			$newDistance = $addDistance[0];
			$extraStopTime = $addDistance[1];
		}
		
		//-------------- Code for team task ---------------
		$perExtraStopCharges = $this->extraStopPerStopCharge;
		if($driverAssignType == "Team"){
			$perExtraStopCharges = $this->extraStopPerStopChargeTeam;
		}
		//-------------- Code for team task ---------------
		
		if ( $newDistance != '' && $newDistance != 0 ) {
			$results = $this->reCalculateDistances($tripDetailId,$newDistance, $jobRecord, $truckAverage,($jobRecord['Stops'] * $perExtraStopCharges ),$driverAssignType);
			//~ if ( $extraStopId != '' && $extraStopId != null ) {
				//~ $this->Job->removeExtraStop( $extraStopId ,$jobRecord['id'], $results[0] , $jobRecord['Stops']);
			//~ }
			
			$jobRecord['loadedDistanceCost'] = $this->findDieselCosts( $truckAverage,$newDistance,$this->diesel_rate_per_gallon, 'driverMiles',$driverAssignType);
			$jobRecord['deadMileDistCost'] = $this->findDieselCosts( $truckAverage,$jobRecord['deadmiles'],$this->diesel_rate_per_gallon, 'driverMiles',$driverAssignType);
			$jobRecord['estimatedFuelCost'] = $this->findDieselCosts( $truckAverage, ( $newDistance + $jobRecord['deadmiles'] ),$this->diesel_rate_per_gallon, '', $driverAssignType );
			
			echo json_encode(array('distance' => $results[0], 'overall_total_charge_Cal' => $results[1], 'overall_total_rate_mile_Cal' => $results[2], 'overall_total_profit_Cal' => $results[3], 'overall_total_profit_percent_Cal' => $results[4], 'new_deadmiles_Cal' => $results[5], 'loadedDistanceCost' => $jobRecord['loadedDistanceCost'], 'deadMileDistCost' => $jobRecord['deadMileDistCost'], 'estimatedFuelCost' => $jobRecord['estimatedFuelCost']));
		}	
		else {
			echo json_encode(array('distance' => $newDistance));
		}	
	}
	
	public function skipAcl_get_load_data( $pickUpDate = ''){
	
		$obj = json_decode(file_get_contents('php://input'));
	
		$vehicleID = $obj->driverInfo;
		
		//~~~~~~~~~~~~~~~~~~ CalAmp Code ~~~~~~~~~~~~~~~~~~~~~~~
		//$response = $this->calAmp();
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		$statesAddress 	= $this->Vehicle->get_vehicle_address($vehicleID);
		if($statesAddress['destination_address'] != '')
		{
			$newDest = explode(',', $statesAddress['destination_address']);
			$this->origin_state = $newDest['0'];
			$this->origin_city = $newDest['1'];
			if ( isset($newDest[2]) && $newDest[2] != '' ) {
				$pickupDateDest = date('Y-m-d',strtotime($newDest[2]));
			} 	
		}
		else
		{
			array_push($newDest,$statesAddress['state'],$statesAddress['city']);
			$this->origin_state = $statesAddress['state'];
			$this->origin_city = $statesAddress['city'];
		}
		
		if ( $pickupDateDest != '' && strtotime($pickupDateDest) >= strtotime(date('Y-m-d')) ) {
			$dateTime = array (
					'dateTime' => date('Y-m-d',strtotime($pickupDateDest)),
				);
			$this->todayDate = date('m/d/y',strtotime($pickupDateDest));
		} else {
			$dateTime = array (
				
			);
		}
		
		$newlabel= $statesAddress['driverName'].'-'.$statesAddress['label'].'-'.@$newDest[0].'-'.@$newDest[1];
		
		$data['rows']  = $this->commonApiHits( $statesAddress['vehicle_type'], $dateTime, 9);
		
		
		$destin = $this->origin_city.','.$this->origin_state.',USA';
		
		$loadsIdArray = array();
		if ( !empty($data['rows'])) {
			$returnArray = $this->giveBestLoads( $data['rows'] , $loadsIdArray, $vehicleID, $this->todayDate,0,'','');
			$this->finalArray = $returnArray[0];
			$loadsIdArray = $returnArray[1];
		}
		
		$newdata['rows'] = $this->finalArray;
		$newdata['table_title'] = $newlabel;
		$newdata['loadsIdArray'] = $loadsIdArray;
		$newdata['vehicleIdRepeat'] = $vehicleID;
		
		$newdata['DriverName'] = $statesAddress['driverName'];
		$newdata['TruckLabel'] = $statesAddress['label'];
		$newdata['Abbrevation'] = $statesAddress['abbrevation'];
		
		echo json_encode(array('loadsData'=> $newdata),JSON_NUMERIC_CHECK);
	}

	public function fetch_truckstop_special_note( $truckStopId = null, $loadId = null ) {
		
		if ( $loadId != '' && $loadId != null && is_numeric($loadId) ) {
			$specialInfo = $this->Job->fetchLoadSingleFieldInfo( $loadId, 'SpecInfo');
		} else {
			$client   = new SOAPClient($this->wsdl_url);
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
		}
		echo json_encode(array('specialInfo' => $specialInfo));
	}

	/**
	 * Fetching loads after every 30 seconds
	 */
	  
	public function skipAcl_get_load_data_repeat($loadsIdArray = array(), $vehicleIDRepeat = null ){
		$objPost = json_decode(file_get_contents('php://input'),true);
	
		$loadsIdArray = $objPost['loadsArray'];
		$pickupDateDest = '';
		$formPost = $objPost['formPost'];
		$searchStatus = $objPost['searchStatus'];
		
		if ( $searchStatus == 'newSearch' || $searchStatus == 'planSearch' ) {
			$this->origin_city = $formPost['origin_City'];
			$this->origin_state = $formPost['origin_State'];
			
			if ( isset($formPost['multiDestinations']) && !empty($formPost['multiDestinations']) ) {
				if ( $searchStatus == 'newSearch' ) {
					$destination_states = implode(',',$formPost['multiDestinations']);
				} else {
					$destination_states = $formPost['multiDestinations'];
				}
				$destination_city = '';
			} else {
				$destination_city = @$formPost['dest_City'];
				$destination_states = @$formPost['dest_State'];
			}
				
			$origin_country = ( isset($formPost['origin_country']) && $formPost['origin_country'] != '' ) ? $formPost['origin_country'] : 'USA';
			$dest_country = ( isset($formPost['destination_country']) && $formPost['destination_country'] != '' ) ? $formPost['destination_country'] : 'USA';
			$destination_range = ( isset($formPost['destination_range']) && $formPost['destination_range'] != '' && is_numeric($formPost['destination_range']) ) ? $formPost['destination_range'] : 300;	
			$origin_range = ( isset($formPost['origin_range']) && $formPost['origin_range'] != '' && is_numeric($formPost['origin_range']) ) ? $formPost['origin_range'] : 300;			
					
			if ( isset($formPost['pickup_date']) && $formPost['pickup_date'] != '' && $formPost['pickup_date'] != '0000-00-00' ) {
				if ( strpos($formPost['pickup_date'], ',') !== false ) {
					$datesNewArray = explode(',',$formPost['pickup_date']);
					for ( $i = 0; $i < count($datesNewArray);  $i++ ) {
						$dateTime[] = $datesNewArray[$i];
					}
					$this->todayDate = date('m/d/y',strtotime($datesNewArray[0]));
				} else {
					$pickupDate = $formPost['pickup_date'];
					$dateTime 	= array (
						'dateTime' => date('Y-m-d',strtotime($pickupDate)),
					);
					$this->todayDate = date('m/d/y',strtotime($pickupDate));
				}
			} else {
				$pickupDate =  $dateTime = array ();
			}
			
			$equipmentType = ( isset($formPost['ttype']) && $formPost['ttype'] != '' ) ? $formPost['ttype'] : 'F,FSD';
			$data['rows']  = $this->commonApiHits( $equipmentType, $dateTime, '1', $origin_country, $origin_range, $destination_city, $destination_states,$destination_range, $dest_country, 'Full');
		} 	
			
		$destin 		= $this->origin_city.', '.$this->origin_state.', '.$origin_country;
		$moreThanMiles 	= $formPost['moreLoadCheck'];
		$dailyFilter 	= ( isset($formPost['dailyFilter']) && $formPost['dailyFilter'] != '' ) ? $formPost['dailyFilter'] : 'all';			
		
		$addNewClass 	= 'newRow'; 				// differentiating 30 seconds request from other

		if ( !empty($data['rows'])) {
			$returnArray = $this->giveBestLoads( $data['rows'] , $loadsIdArray, '', $this->todayDate, $moreThanMiles , $addNewClass, $dailyFilter);
			$this->finalArray = $returnArray[0];
			$loadsIdArray = $returnArray[1];
		}
		
		$newData = array();
		$newData['rows'] = array_values($this->finalArray);
		$newData['loadsIdArray'] = $loadsIdArray;
		echo json_encode($newData);
	}

	/**
	* fetching truckstop to show on route map
	*/

	public function skipAcl_get_nearby_tstops(){
		$requset = json_decode(file_get_contents('php://input'),true);

		$coords = $requset["coords"];
		$radius = (float)$requset["radius"];
		$finalCoords = array();
		$i=0;
		if ( !empty($coords)) {
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
		}
		echo json_encode($finalCoords);
	}

	/**
	* fetching fuel stops to show on route map
	*/

	public function skipAcl_get_nearby_fstops(){
		$requset = json_decode(file_get_contents('php://input'),true);

		$coords = $requset["coords"];
		$radius = (float)$requset["radius"];
		$finalCoords = array();
		$i=0;
		foreach ($coords as $key => $value) {
			
			$cursor = $this->Job->getNearByFuelStops($value['lat'],$value['lng'],$radius);
			
			if($cursor){
				foreach ($cursor as $rkey => $rvalue) {
				 	if(!$this->in_array_r($rvalue["id"], $finalCoords)){
						$rvalue["address"]=str_replace(",","",$rvalue["address"]);
						$rvalue["city"]=str_replace(",","",$rvalue["city"]);
						$rvalue["state"]=str_replace(",","",$rvalue["state"]);
						$rvalue["zip"]=str_replace(",","",$rvalue["zip"]);
						array_push($finalCoords, $rvalue);
				 	}
				 	
				 } 
			}
		}
		echo json_encode($finalCoords);
	}

	private function in_array_r($needle, $haystack, $strict = false) {
	    foreach ($haystack as $item) {
	        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
	            return true;
	        }
	    }
	    return false;
	}

	private function getCompleteCost( $miles = 0 , $deadmiles = 0, $truckAverage = 0 ) {
		
        $total_complete_distance = $miles + $deadmiles;
		$gallon_needed =  ($total_complete_distance / $truckAverage);
		$total_diesel_cost = $this->diesel_rate_per_gallon * $gallon_needed;
		
		$total_driver_cost = $this->driver_pay_miles_cargo * $total_complete_distance;
		$total_cost = round(($total_diesel_cost + $total_driver_cost + $this->total_tax),2);
			
		return $total_cost;	
	}

	public function skipAcl_searchCity() {
		$cityObj = json_decode(file_get_contents('php://input'));
		$city = $cityObj->city;
		$country = isset($cityObj->country) ? $cityObj->country : false;
		$result = array();
		if ( $city != '') {
			$result = $this->Job->getRelatedCity($city, $country);
		}
		
		echo json_encode(array('result' => $result));
	}

	//------------------ Upload Docs Functions --------------------------

	public function skipAcl_uploadDocs( $parameter = '' ){

		if ( $parameter != '' ) {
			$doc = $parameter;
		} else if (isset($_REQUEST['docType']) && $_REQUEST['docType'] != '' ) {
			$doc 	= $_REQUEST['docType'];
			$parameter = $_REQUEST['docType'];
		} else {
			$doc = 'DOC';
			$parameter = 'docs';
		}
		
		if ( $_REQUEST['loadId'] == '' || $_REQUEST['loadId'] == 'undefined' || $_REQUEST['loadId'] == null  || $_REQUEST['loadId'] == 0 ) {
			echo json_encode(array('loadIdNotExist' => 1));
			exit();
		}
	
		$response = $this->uploadFileToServer($_FILES, $doc, $parameter,$_REQUEST['loadId']);
		
		if(!$response['error']){
			
			if ( isset($response['compressionError']) && $response['compressionError'] == 1 ) {
				echo json_encode(array('fileCompressionIssue' => 1));
				exit();
			}
			
			if ( isset( $response['primary_ID'] ) && $response['primary_ID'] != '' ) {
				$docPrimaryId = $response['primary_ID'];
			} else {
				$docPrimaryId = '';
			}
		
			if ( $parameter == 'broker' || $parameter == 'shipper') {			// saving data to broker table
				try{
					if ($parameter == 'broker' )
						$brokerInfo = $this->BrokersModel->getBrokerInfo($_REQUEST['brokerId']);
					else {
						$this->load->model('Shipper');
						$brokerInfo = $this->Shipper->getShipperInfo($_REQUEST['brokerId']);
					}

					$this->BrokersModel->uploadBrokerDocument($response['data']['file_name'], $_REQUEST['brokerId'], $parameter);
					$this->skipAcl_fetchBrokerShipperDocuments($_REQUEST['brokerId'],$parameter);

					$companyName = ( $parameter == 'broker') ? $brokerInfo['TruckCompanyName'] : $brokerInfo['shipperCompanyName'];
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> uploaded a new document ('.$response['data']['file_name'].') for '.$parameter.' <a class="notify-link" href="'.$this->serverAddr.'#/edit'.$parameter.'/'.$brokerInfo["id"].'">'.ucfirst($companyName).'</a> from ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$_REQUEST['loadId'].',\'\',\'\',\'\',\'\',0,\'\')">#'.$_REQUEST['loadId'].'</a>';
					logActivityEvent($brokerInfo['id'], $this->entity[$parameter], $this->event["upload_doc"], $message, $this->Job, $_REQUEST['srcPage']);
				}catch(Exception $e){
					log_message('error','UPLOAD_BROKER_DOC_FROM_TICKET'.$e->getMessage());
				}
			} else {
				$this->updateDocumentTable($response['data']['file_name'],$_REQUEST['loadId'],$parameter, $docPrimaryId,$_REQUEST['srcPage']);
			}
		} else {
			if ( $parameter == 'broker' || $parameter == 'shipper' ) {
				$this->skipAcl_fetchBrokerShipperDocuments($_REQUEST['brokerId'], $parameter, $response );
			} else {
				$this->skipAcl_fetchDocuments($_REQUEST['loadId'],$response );
			}
		}
	}
	
	private function updateDocumentTable($fileName, $loadId, $parameter = '' ,$docPrimaryId = null,$srcPage = '' ){
		$metaKey = "";
		switch ($parameter) {
			case 'pod':  $metaKey = "POD"; break;
			case 'rateSheet': $metaKey = "Rate Sheet"; break;
			case 'docs': $metaKey = "Document"; break;
		}

		try{
			if($docPrimaryId){
				$docInfo = $this->Job->getDocDetail($docPrimaryId);
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted/overwrote the '.$metaKey.' ('.$docInfo["doc_name"].') from ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$loadId.',\'\',\'\',\'\',\'\',0,\'\')">#'.$loadId.'</a>';
				logActivityEvent($loadId, $this->entity["ticket"], $this->event["overwrite_doc"], $message, $this->Job, $srcPage);
			}

			$this->Job->insertDocumentEntry($fileName, $loadId, $parameter, $docPrimaryId);	
			
			
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> uploaded a new '.$metaKey.' ('.$fileName.') for ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$loadId.',\'\',\'\',\'\',\'\',0,\'\')">#'.$loadId.'</a>';
			logActivityEvent($loadId, $this->entity["ticket"], $this->event["upload_doc"], $message, $this->Job, $srcPage);
			$this->skipAcl_fetchDocuments($loadId);
		}catch(Exception $e){
			log_message('error',$metaKey.'-UPLOAD_DOCS_TICKET'.$e->getMessage());
		}
	}

	/**
	 * Fetching Broker Documents for job ticket
	 */
	 
	public function skipAcl_fetchBrokerShipperDocuments( $brokerId = null, $type = '', $errorResponse = array() ){
		$result = $this->BrokersModel->fetchContractDocuments($brokerId, $type);
		$docs_list = array();
		if ( !empty($result) ) {
			for( $i = 0; $i < count($result); $i++ ) {
				$fileNameArray = explode('.',$result[$i]['document_name']);
				$fileName = '';
				for ( $j = 0; $j < count($fileNameArray) - 1; $j++ ) {
					$fileName .= $fileNameArray[$j];
				}
				$fileName = 'thumb_'.$fileName.'.jpg';
				
				$docs_list['brokerDocuments'][$i]['doc_name'] = $result[$i]['document_name'];
				$docs_list['brokerDocuments'][$i]['thumb_doc_name'] = $fileName;
				$docs_list['brokerDocuments'][$i]['id'] = $result[$i]['id'];
				$docs_list['brokerDocuments'][$i]['BrokerId'] = $brokerId;
				$docs_list['brokerDocuments'][$i]['billType'] = $type;
			}
		}

		if ( !empty($errorResponse) && isset($errorResponse['error_exceed']) ){
			$exceedMsg = $errorResponse['error_exceed'];
		} else {
			$exceedMsg = '';
		}
		
		echo json_encode(array('result' => $docs_list, 'error_exceed' => $exceedMsg));
	}
	 
	public function skipAcl_fetchDocuments($loadId = FALSE, $errorResponse = array(), $from = "" ){
		if(!$loadId){
			$_POST = json_decode(file_get_contents('php://input'), true);
			$loadId = $_POST['loadId'];
		}
	
		$response = array();
		$response['dlist'] = $this->Job->getDocsList($loadId);
		
		$response['error'] = false;
		if ( !empty($errorResponse) && isset($errorResponse['error_exceed']) ){
			$exceedMsg = $errorResponse['error_exceed'];
		} else {
			$exceedMsg = '';
		}
		$haveInvoice = "";
		if($from == "invoice"){
			$haveInvoice = $this->Job->isInvoiceGenerated($loadId);
		}

		echo json_encode(array('result' => $response, 'error_exceed' => $exceedMsg,"haveInvoice" =>$haveInvoice));
	}

	private function uploadFileToServer($files, $prefix, $parameter = '', $loadId = null ) {
		$_FILES = $files;
		$config['_prefix'] 		= $prefix.'_';
		
		if(!empty($_FILES)){
			if ( $parameter == 'rateSheet' || $parameter == 'pod') {
				$this->load->model('Billing');
				$result = $this->Billing->fetchUploadedDocs( $loadId, $parameter);
			} else {
				$result = array();
			}
			
			if ( $parameter == '' ) 
				$parameter = 'docs';			
			
			if($_FILES['file']['error'] == 0 ){

				$extArr 						= explode('.',$_FILES['file']['name']);
				$extension 						= strtolower(end($extArr));				
			   	$config['file_name']   			= $config['_prefix'].time().'.'.$extension;
				$config['upload_path']          = 'assets/uploads/documents/'.$parameter.'/';
				$config['upload_thumb_path']    = 'assets/uploads/documents/';
				$config['allowed_types']        = 'pdf|gif|jpg|jpeg|png|docx|doc|xls|xlsx|txt|ico|bmp|svg';
				
				$this->load->library('upload', $config);

				if ( ($parameter == 'pod' || $parameter == 'rateSheet') &&  strtolower($extension) == 'pdf' ) {
					$podRateResult = $this->degradePdfVersion($_FILES,$config,$parameter,$result);
					return $podRateResult;
					exit();
				}

				if ( !$this->upload->do_upload('file'))	{
					$response['error'] 		= true;
					$response['error_desc'] = $this->upload->display_errors();
				} else {
					$response['error'] 		= false;
					$response['data'] 		= $this->upload->data();
								
					if (substr(php_uname(), 0, 7) == "Windows"){ 
				        $response['data']['cmd'] = 'Windows';
				    } 
				    else { 
						$thumbFolder = 'thumb_'.$parameter;
						$cmd = 'cd '.$response['data']['file_path'];
				    	$cmd .= '; convert -thumbnail x600 '.$response['data']['file_name'].'[0] -flatten ../'.$thumbFolder.'/thumb_'.$response['data']['raw_name'].'.jpg';
				    	$response['data']['cmd'] = $cmd;
				        exec($cmd . " > /dev/null &");
				    } 
				    
					if ( !empty($result) && $result['doc_name'] != '' ) {
						unlink("assets/uploads/documents/".$parameter."/".$result['doc_name']);
						$thumbReplace = explode('.',$result['doc_name']);
						$thumbRe = $thumbReplace[0].'.jpg';
						unlink("assets/uploads/documents/".$thumbFolder."/thumb_".$thumbRe);
						
						$response['primary_ID'] = $result['id'];
					}				    
				}
			}else{
				$response['error'] = true;
				$response['error_desc'] = $_FILES;
				if ( $_FILES['file']['error'] == 1 && $_FILES['file']['size'] == 0 ) {
					$response['error_exceed'] = 1;
				}
			}
		}else{
			$response['error'] = true;
			$response['data'] = 'Files array empty !!!';
		}
		return $response;
	}
	
	/**
	* Method degradePdfVersion
	* @param File Path
	* @return File Name
	* @package gs-920-linux_x86_64
	* Degrading PDF(1.5+) to 1.4 version
	*/

	private function degradePdfVersion( $data = NULL , $config = array(), $parameter = '', $result = array()){
		$fileName 	= $config['file_name'];
		$outputfile = $config['upload_path'].'/'.$fileName;
		$sourcefile = $data['file']['tmp_name'];

		$pathGen 	= str_replace('application/', '', APPPATH.'assets/'); 
		$gostscript = $pathGen.'ghostscript/gs-920-linux_x86_64';
	
		try{
			shell_exec("{$gostscript} -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -dAutoRotatePages=/None -sOutputFile={$outputfile} {$sourcefile}"); 
			
			$fileNameArray = explode('.',$fileName);
			$newfileName = '';
			for ( $i = 0; $i < count($fileNameArray) - 1; $i++ ) {
				$newfileName .= $fileNameArray[$i];
			}
			$newfileName = $newfileName.'.jpg';
			$outputfile  = $config['upload_thumb_path'].'/thumb_'.$parameter.'/thumb_'.$newfileName;
	
			shell_exec("{$gostscript} -dSAFER -dBATCH -sDEVICE=jpeg -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -r55 -sOutputFile={$outputfile} {$sourcefile}");

			$response = array();
			$response['error'] 		= false;
			$response['data'] 		= array();
			if ( !empty($result) && $result['doc_name'] != '' ) {
				unlink("assets/uploads/documents/".$parameter."/".$result['doc_name']);
				$thumbReplace = explode('.',$result['doc_name']);
				$thumbRe = $thumbReplace[0].'.jpg';
				unlink("assets/uploads/documents/thumb_".$parameter."/thumb_".$thumbRe);				
				$response['primary_ID'] = $result['id'];
			}	
			$response['data']['file_name']  = $fileName;
			return $response;
		}catch(Exception $e){
			$e->getMessage();
		}
	}


//------------------ Upload Docs Functions --------------------------

	public function skipAcl_deleteDocument(){
		
		$pathGen = str_replace('application/', '', APPPATH);
		
		$_POST = json_decode(file_get_contents('php://input'), true);
		
		$_PATH = $pathGen.'assets/uploads/documents/'.$_POST['doc_type'].'/';
		$thumb_PATH = $pathGen.'assets/uploads/documents/thumb_'.$_POST['doc_type'].'/';
		
		$file =  isset($_POST['filename']) ? $_PATH.$_POST['filename']: $_PATH.'test.pdf';
		
		$fileNameArray = explode('.',$_POST['filename']);
		$ext = end($fileNameArray);
		$extArray = array( 'pdf','xls','xlsx','txt', 'bmp', 'ico','jpeg' );
		$fileName = '';
		for ( $i = 0; $i < count($fileNameArray) - 1; $i++ ) {
			$fileName .= $fileNameArray[$i];
		}
		$fileName = $fileName.'.jpg';
		$thumbFile =  $thumb_PATH.'thumb_'.$fileName;
	
		if (file_exists($file)) {
			unlink($file);
		}
		
		if (file_exists($thumbFile) ) 
			unlink($thumbFile);
			
		if ( $_POST['doc_type'] == 'invoice' ) {
			$bundleDocArray = $this->Job->getBundleDocInfo($_POST['loadId']);
			if ( !empty($bundleDocArray) ) {
				$bundleFile = $bundleDocArray['doc_name'];
				$fileNameArray = explode('.',$bundleDocArray['doc_name']);
				$ext = end($fileNameArray);
				$fileName = '';
				for ( $i = 0; $i < count($fileNameArray) - 1; $i++ ) {
					$fileName .= $fileNameArray[$i];
				}
				
				$bundle_thumb_PATH = $pathGen.'assets/uploads/documents/thumb_'.$bundleDocArray['doc_type'].'/';
				$bundle_thumb_PATH =  $bundle_thumb_PATH.'thumb_'.$fileName.'.jpg';
				$bundleFile = $pathGen.'assets/uploads/documents/'.$bundleDocArray['doc_type'].'/'.$bundleDocArray['doc_name'];
				
				if (file_exists($bundle_thumb_PATH)) 
					unlink($bundle_thumb_PATH);
				
				if (file_exists($bundleFile) ) 
					unlink($bundleFile);
				$docInfo = $this->Job->getDocDetail($bundleDocArray['id']);
				$this->Job->deleteDocument($bundleDocArray['id']);
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted the bundle document ('.$docInfo["doc_name"].') from ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$_POST['loadId'].',\'\',\'\',\'\',\'\',0,\'\')">#'.$_POST['loadId'].'</a>';
				logActivityEvent($_POST['loadId'], $this->entity["ticket"], $this->event["remove_doc"], $message, $this->Job,$_POST["srcPage"]);
			}
		}
		
		if ( $_POST['doc_type'] == 'broker' || $_POST['doc_type'] == 'shipper' ) {
			try{
				$brokerId =  isset($_POST['assignedBrokeId']) ? $_POST['assignedBrokeId'] : 0;
				$brokerInfo = $this->BrokersModel->getEntityInfoByDocId($_POST['docId'], $this->entity[$_POST['doc_type']]);

				$this->BrokersModel->removeContractDocs($_POST['docId']);
				$this->skipAcl_fetchBrokerShipperDocuments($brokerId, $_POST['doc_type']);

				$companyName = ( $_POST['doc_type'] == 'broker') ? $brokerInfo['TruckCompanyName'] : $brokerInfo['shipperCompanyName'];
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted a document ('.$brokerInfo["document_name"].') of '.$_POST['doc_type'].' <a class="notify-link" href="'.$this->serverAddr.'#/edit'.$_POST['doc_type'].'/'.$brokerInfo["id"].'"> '.$companyName.'</a>  from ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$_POST['loadId'].',\'\',\'\',\'\',\'\',0,\'\')">#'.$_POST['loadId'].'</a>';
				logActivityEvent($brokerInfo['id'], $this->entity[$_POST['doc_type']], $this->event["remove_doc"], $message, $this->Job);	
			}catch(Exception $e){
				log_message('error','DELETE_BROKER_DOCS_TICKET'.$e->getMessage());
			}

		} else  {
			switch ($_POST['doc_type']) {
				case 'pod':  $metaKey = "POD"; break;
				case 'rateSheet': $metaKey = "Rate Sheet"; break;
				case 'docs': $metaKey = "Document"; break;
			}

			$metaKey = ucwords(str_replace("_"," ", $_POST['doc_type']));
			try{
				$docInfo = $this->Job->getDocDetail($_POST['docId']);
				$this->Job->deleteDocument($_POST['docId'], $_POST['loadId'], $_POST['doc_type']);
				
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted the '.$metaKey.' ('.$docInfo["doc_name"].') from ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$_POST['loadId'].',\'\',\'\',\'\',\'\',0,\'\')">#'.$_POST['loadId'].'</a>';
				logActivityEvent($_POST['loadId'], $this->entity["ticket"], $this->event["remove_doc"], $message, $this->Job,$_POST["srcPage"]);
				$this->skipAcl_fetchDocuments(false, array(), $_POST['doc_type']);
			}catch(Exception $e){
				log_message('error',$metaKey.'-DELETE_DOCS_TICKET'.$e->getMessage());
			}
		}	
	}

	private function joinAddress($args){
		$pickupAddress = array();
		$response = array();
		
		if ( isset($args['OriginCity'])) 
			$pickupAddress[] = trim($args['OriginCity']);

		if ( isset($args['OriginState'])) 
			$pickupAddress[] = trim($args['OriginState']);

		if ( isset($args['OriginCountry'])) 
			$pickupAddress[] = trim($args['OriginCountry']);
		
		$pickupAddress =  array_filter($pickupAddress);
		$response["PickupAddress"] = implode(',', $pickupAddress);	

		
		$dropAddress = array();
		
		if ( isset($args['DestinationCity'])) 
			$dropAddress[] = trim($args['DestinationCity']);

		if ( isset($args['DestinationState'])) 
			$dropAddress[] = trim($args['DestinationState']);

		if ( isset($args['DestinationCountry'])) 
			$dropAddress[] = trim($args['DestinationCountry']);
		$dropAddress =  array_filter($dropAddress);
		$response["DestinationAddress"] = implode(',', $dropAddress);

		return $response;

	}

	/**
	 * Saving load to database
	 */ 
	 
	public function SaveLoadRecord( $id = null , $loadFrom = ''){			
		$_POST = json_decode(file_get_contents('php://input'), true);
		$driverAssignType = isset($_POST["driverAssignType"]) && $_POST["driverAssignType"] == "team" ? $_POST["driverAssignType"] : "driver";
		$id = $_POST['jobPrimary'];
		$vehicle_id = isset($_POST['jobRecords']['vehicle_id'])  ? $_POST['jobRecords']['vehicle_id'] : 0;
		$extraStopsArray = $_POST['extraStops'];
		$vehicleDriverFlag = isset($_POST['vehicleDriverFlag']) ? $_POST['vehicleDriverFlag'] : 0;

		$woRefno = (isset($_POST['jobRecords']['woRefno']) && $_POST['jobRecords']['woRefno'] != '' ) ? $_POST['jobRecords']['woRefno'] : '';
		if ( isset($_POST['jobRecords']['PaymentAmount']) ) {
			$_POST['jobRecords']['PaymentAmount'] = str_replace(',', '',$_POST['jobRecords']['PaymentAmount']);
			$_POST['jobRecords']['PaymentAmount'] = str_replace('$', '',$_POST['jobRecords']['PaymentAmount']);
			$_POST['jobRecords']['PaymentAmount'] = (double)$_POST['jobRecords']['PaymentAmount'];
			$_POST['jobRecords']['PaymentAmount1'] = $_POST['jobRecords']['PaymentAmount'];
		} else {
			$_POST['jobRecords']['PaymentAmount'] = 0;
		}
			
		$errMsg = array();
		
		if ( isset($_POST['jobRecords']['JobStatus']) && $_POST['jobRecords']['JobStatus'] == 'completed' ) {		// check all required fields if status is set to completed
			if ( !isset($_POST['jobRecords']['PickupAddress']) || $_POST['jobRecords']['PickupAddress'] == '' || !isset($_POST['jobRecords']['OriginCity']) || $_POST['jobRecords']['OriginCity'] == '' || !isset($_POST['jobRecords']['OriginState']) || $_POST['jobRecords']['OriginState'] == '' || !isset($_POST['jobRecords']['OriginZip']) || $_POST['jobRecords']['OriginZip'] == '' || !isset($_POST['jobRecords']['OriginCountry']) || $_POST['jobRecords']['OriginCountry'] == '' )
				$errMsg[] = 'full origin address';
				
			if ( !isset($_POST['jobRecords']['DestinationAddress']) || $_POST['jobRecords']['DestinationAddress'] == '' || !isset($_POST['jobRecords']['DestinationCity']) || $_POST['jobRecords']['DestinationCity'] == '' || !isset($_POST['jobRecords']['DestinationState']) || $_POST['jobRecords']['DestinationState'] == '' || !isset($_POST['jobRecords']['DestinationZip']) || $_POST['jobRecords']['DestinationZip'] == '' || !isset($_POST['jobRecords']['DestinationCountry']) || $_POST['jobRecords']['DestinationCountry'] == ''  )
				$errMsg[] = 'full delivery address';
				
			if ( !isset($_POST['jobRecords']['vehicle_id']) || $_POST['jobRecords']['vehicle_id'] == '' || $_POST['jobRecords']['vehicle_id'] == 0 || $_POST['jobRecords']['vehicle_id'] == null )
				$errMsg[] = 'driver';
			
			if ( !isset($_POST['jobRecords']['shipper_name']) || $_POST['jobRecords']['shipper_name'] == '' )
				$errMsg[] = 'shipper name';
			
			if ( !isset($_POST['jobRecords']['consignee_name']) || $_POST['jobRecords']['consignee_name'] == '' )
				$errMsg[] = 'consignee name';
				
			if ( !isset($_POST['jobRecords']['woRefno']) || $_POST['jobRecords']['woRefno'] == '' )
				$errMsg[] = 'wo refrence number';
				
			if ( !isset($_POST['jobRecords']['broker_id']) || $_POST['jobRecords']['broker_id'] == '' ) 
				$errMsg[] = 'broker info';
			
			if( !isset($_POST['jobRecords']['PaymentAmount']) || $_POST['jobRecords']['PaymentAmount'] <= 0 || $_POST['jobRecords']['PaymentAmount'] == '' )
				$errMsg[] = 'Payment amount';
				
			if ( isset($_POST['jobRecords']['Stops']) && $_POST['jobRecords']['Stops'] > 0 ) {			// check if extra stop exist then check entity type selected
				$extEntity = 0;
				$extname = 0;
				$extaddress = 0;
				for( $i = 0; $i < $_POST['jobRecords']['Stops']; $i++ ) {
					if( !isset($extraStopsArray['extraStopEntity_'.$i]) || $extraStopsArray['extraStopEntity_'.$i] == '' ) {
						$extEntity = 1;						
					}	
					
					if( !isset($extraStopsArray['extraStopName_'.$i]) || $extraStopsArray['extraStopName_'.$i] == '' ) {
						$extname = 1;				
					}	
					
					if( !isset($extraStopsArray['extraStopAddress_'.$i]) || $extraStopsArray['extraStopAddress_'.$i] == '' || !isset($extraStopsArray['extraStopCity_'.$i]) || $extraStopsArray['extraStopCity_'.$i] == '' || !isset($extraStopsArray['extraStopState_'.$i]) || $extraStopsArray['extraStopState_'.$i] == '' || !isset($extraStopsArray['extraStopZipCode_'.$i]) || $extraStopsArray['extraStopZipCode_'.$i] == '' || !isset($extraStopsArray['extraStopCountry_'.$i]) || $extraStopsArray['extraStopCountry_'.$i] == '' ) {
						$extaddress = 1;				
					}			
				}
				
				if ( $extEntity == 1 )
					$errMsg[] = 'extra stop entity';
					
				if ( $extname == 1 )
					$errMsg[] = 'extra stop name';
					
				if ( $extaddress == 1 )
					$errMsg[] = 'extra stop address';
			}
				
			$fieldsResult = $this->Job->checkDocumentUploaded($id, 'pod');
			if( $fieldsResult == 'notexist' )
				$errMsg[] = 'proof of delivery';
			
			$fieldsResult = $this->Job->checkDocumentUploaded($id, 'rateSheet');
			if( $fieldsResult == 'notexist' ) 
				$errMsg[] = ' and ratesheet';
		}
		
		if ( !empty($errMsg)) {
			$newMsg = implode(', ',$errMsg);
			$newMsg = 'Woops! Looks like you are missing the following items - '.$newMsg.'.';
			echo json_encode(array('requiredFields' => 1, 'errorMessage' =>  $newMsg));
			exit();
		}
		
		if ( $woRefno != null && $woRefno != '' ) {
			$refNoResult = $this->Job->checkWoNumberExist( $woRefno, $id);
			if ( $refNoResult != 'true' ) {
				echo json_encode(array('refStatus' => false, 'loadIdNumber' => $refNoResult));
				exit();
			}
		} 
		
		if ( isset($_POST['timeStorage']) && !empty($_POST['timeStorage']) ) {
			$_POST['jobRecords']['PickupTime'] = @$_POST['timeStorage']['originTT'];
			$_POST['jobRecords']['DeliveryTime'] = isset($_POST['timeStorage']['deliveryTT']) ? $_POST['timeStorage']['deliveryTT'] : '';

			$_POST["jobRecords"]["PickupTimeRangeEnd"] 	= isset($_POST["timeStorage"]["originTT_range_end"]) ? $_POST["timeStorage"]["originTT_range_end"] : '';
			$_POST["jobRecords"]["DeliveryTimeRangeEnd"] 	= isset($_POST["timeStorage"]["deliveryTT_range_end"]) ? $_POST["timeStorage"]["deliveryTT_range_end"] : '' ;

		}
		
		$saveData = $_POST['jobRecords']; 
			
		if ( $vehicle_id != '' & $vehicle_id != null && $vehicle_id != 'undefined' ) {
			$dataN['vehicles_Available'] = $VehiclesArray = $this->Job->FindJobVehicles($vehicle_id, $this->userId, 'saveJob');
		} else {
			$dataN['vehicles_Available'] = $VehiclesArray = array('1');  /* Assigned 1 if no vehicle id is passed*/
		}
		
		$truckInfo = $this->Job->FindTruckInfo( $id, @$saveData['ID']);
		if ( !empty($truckInfo) ) {
			$tripDetailPrId = $truckInfo['id'];
			$this->deadMilePaid = $truckInfo['dead_head_miles_paid'] ;
			$this->deadMileNotPaid = $truckInfo['dead_miles_not_paid'];
			if($driverAssignType == "team"){
				$this->driver_pay_miles_cargo_team = $truckInfo['pay_for_miles_cargo']; 
				$this->payForDeadMile_team = $truckInfo['pay_for_dead_head_mile']; 
			}else{
				$this->driver_pay_miles_cargo = $truckInfo['pay_for_miles_cargo']; 
				$this->payForDeadMile = $truckInfo['pay_for_dead_head_mile']; 
			}
			
			$this->iftaTax = $truckInfo['ifta_taxes'];
			$this->tarps = $truckInfo['tarps'];
			$this->det_time = $truckInfo['detention_time'];
			$this->tollsTax = $truckInfo['tolls'];
		} 

		//--------------- Code for Team task ----------------
		$driverPayMilesCargo = $this->driver_pay_miles_cargo;
		$driverPayForDeadMile = $this->payForDeadMile;
		$perExtraStopCharges = $this->extraStopPerStopCharge;
		if($driverAssignType == "team"){
			$driverPayMilesCargo = $this->driver_pay_miles_cargo_team;
			$driverPayForDeadMile = $this->payForDeadMile_team;
			$perExtraStopCharges = $this->extraStopPerStopChargeTeam;
		}		
		//-------------- Code for team task ---------------
		
		if ( isset($saveData['Stops']) && $saveData['Stops'] != '' ) {
			$this->extraStopCharge = $saveData['Stops'] * $perExtraStopCharges;
		}
		
		$saveData['deadmiles'] = isset($saveData['deadmiles']) ? $saveData['deadmiles'] : 0;
		$saveData['Mileage'] = isset($saveData['Mileage']) ? $saveData['Mileage'] : 0;
		if( !empty($VehiclesArray) ) {
			$VehiclesArray = $this->vehicleCalculations( $dataN['vehicles_Available'], @$saveData['OriginCity'], @$saveData['OriginState'], @$saveData['OriginCountry'], $saveData['deadmiles'], $saveData['Mileage'], $saveData['PaymentAmount'], $this->deadMilePaid, $this->deadMileNotPaid, $driverPayForDeadMile, $driverPayMilesCargo, $this->iftaTax, $this->tarps, $this->det_time, $this->tollsTax, $this->extraStopCharge, $truckInfo, @$saveData['PickupAddress'], $driverAssignType);
		}
	
		$saveData['deadmiles'] = $VehiclesArray[0]['deadMileDist'];
		$saveData['totalCost'] = $VehiclesArray[0]['overall_total_charge'];
		$saveData['overallTotalProfit'] = $VehiclesArray[0]['overall_total_profit'];
		$saveData['overallTotalProfitPercent'] = $VehiclesArray[0]['overall_total_profit_percent'];
		
		if(!isset($saveData['LoadType']) || $saveData['LoadType'] == '' ) {
			$saveData['LoadType']="Full";
		}
		
		if ( isset($saveData['PickupDate']) && $saveData['PickupDate'] != '' )
			$saveData['pickDate'] = date('m/d/y',strtotime($saveData['PickupDate']));  //estimated time function and gantt chart pick the date from pickDate variable
		
		$saveData["driver_type"] = $driverAssignType;	
		if ( isset($saveData['JobStatus']) && $saveData['JobStatus'] == 'booked' ) {
			if ( !isset($saveData['bookedDate']) || $saveData['bookedDate'] == '' || $saveData['bookedDate'] == '0000-00-00 00:00:00') {
				$saveData['bookedDate'] = date('Y-m-d H:i:s');
			} 
		}

		try{

			if( $id != '' && $id != null && $id != 0 && $loadFrom != 'ourLoad' ) {
				if ( !isset($saveData['equipment_options']) || $saveData['equipment_options'] == '' ) {
					if ( $saveData['EquipmentTypes']['Code'] != '' ) {
						$equipmentResult = $this->Job->getRelatedEquipment( $saveData['EquipmentTypes']['Code']);
						if ( $equipmentResult != '' ) 
							$saveData['equipment'] = $equipmentResult;
						$saveData['equipment_options'] = $saveData['EquipmentTypes']['Code'];
					} else {
						$equipmentResult = $this->Job->getRelatedEquipment( $saveData['equipment'], 'changeType');
						if ( $equipmentResult != '' ) {
							$saveData['equipment_options'] 		= $equipmentResult;
							$saveData['EquipmentTypes']['Code'] = $equipmentResult;
						}
					}			
				}

				$jobOldData = $this->Job->getLoadDetailsById($id);

				$addedExtraStops 	= array();
				$updatedExtraStops  = array();
				if ( isset($jobOldData['Stops']) && $jobOldData['Stops'] > 0 ) {
					$addedExtraStops = $this->Job->getExtraStops( $jobOldData['id']);
				}

				$oldDriverName  = $this->Job->getEntityInfoById($id,"driver");

				$result = $this->Job->update_job($id , $vehicle_id, $saveData, $extraStopsArray);
				$jobUpdatedData = $this->Job->getLoadDetailsById($id);

				if( isset($jobUpdatedData['Stops']) && $jobUpdatedData['Stops'] > 0 ) {
					$updatedExtraStops = $this->Job->getExtraStops( $jobUpdatedData['id']);
				}

				$editedFields 		= array_diff_assoc($jobUpdatedData,$jobOldData);
				
				$totalsBuffer = array();
				$message = '';
				$logMessage = 0;
				if(count($editedFields) > 0) {
					
					$logMessage = 1;
					if(isset($editedFields["totalCost"])){
						$totalsBuffer["totalCost"] = $editedFields["totalCost"];
						unset($editedFields["totalCost"]);
					}
					
					if(isset($editedFields["overallTotalProfit"])){
						$totalsBuffer["overallTotalProfit"] = $editedFields["overallTotalProfit"];
						unset($editedFields["overallTotalProfit"]);
					}

					if(isset($editedFields["overallTotalProfitPercent"])){
						$totalsBuffer["overallTotalProfitPercent"] = $editedFields["overallTotalProfitPercent"];
						unset($editedFields["overallTotalProfitPercent"]);
					}

					$message .= '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited the job ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$result.',\'\',\'\',\'\',\'\',0,\'\')">#'.$result.'</a>';
					foreach ($editedFields as $key => $value) {
						$prevField = isset($jobOldData[$key]) ? $jobOldData[$key] : "" ;
						$bufferedInfo = array();
						switch ($key) {
							case 'dispatcher_id': $bufferedInfo  = $this->Job->getEntityInfoById($value,"dispatcher");	
												  $value = $bufferedInfo["first_name"]." ".$bufferedInfo["last_name"]; $key = "Dispatcher";

												  $bufferedInfo  = $this->Job->getEntityInfoById($prevField,"dispatcher");	
												  $prevField = $bufferedInfo["first_name"]." ".$bufferedInfo["last_name"]; 
												  break;
							
							case 'vehicle_id'   : $bufferedInfo  = $this->Job->getEntityInfoById($value,"truck");
												  if( !empty($bufferedInfo["label"])){
												  	$value =  "Truck - ".$bufferedInfo["label"];
												  }else{
												  	$value =  ""; 
												  }  $key = "vehicle";

												  $bufferedInfo  = $this->Job->getEntityInfoById($prevField,"truck");
												  if( !empty($bufferedInfo["label"])){
												  	$prevField =  "Truck - ".$bufferedInfo["label"];
												  }else{
												  	$prevField =  ""; $key = "ticket";
												  } break;

							case 'driver_id'    : $bufferedInfo  = $this->Job->getEntityInfoById($result,"driver");
												  $value = $bufferedInfo["driverName"]; $key="driver";
												  $prevField = $oldDriverName["driverName"];
												  break;

							case 'broker_id'    : $bufferedInfo  = $this->Job->getEntityInfoById($value,"broker");

												  $value = $bufferedInfo["TruckCompanyName"]; $key="broker";
												  $bufferedInfo  = $this->Job->getEntityInfoById($prevField,"broker");
												  $prevField = $bufferedInfo["TruckCompanyName"];
												  break;
						}

						if(in_array($key, array("second_driver_id","trailer_id","driver_type","PickupAddress","updated","created","PickDate","ready_for_invoice"))){continue;}

						if($key == "Stops"){
							$key = "extra stop(s)";
							if(!empty(trim($value)) && $value > $jobOldData['Stops'] ){
								$val = $value - $jobOldData['Stops'];
								$message.= "<br/> - Added  <i>".$val."</i> new ".ucwords(str_replace("_"," ",$key)).".";
							} else if(!empty(trim($value)) && $value < $jobOldData['Stops'] ){
								$val = $jobOldData['Stops'] - $value;
								$message.= "<br/> - removed  <i>".$val."</i> ".ucwords(str_replace("_"," ",$key)).".";
							}
							//  else if (empty(trim($value))){
							// 	$message.= "<br/> - Removed  <i>".$prevField."</i>  ".ucwords(str_replace("_"," ",$key)).".";
							// }
						}else 
						if($key == "driver"){
							if(empty($value)){
								$message.= "<br/> - Unassigned  driver ".$prevField ." from ticket.";	
							}else if(empty($prevField)){
								$message.= "<br/> - Assigned driver ".$value." to ticket.";	
							}else{
								$message.= "<br/> - Changed ".ucwords(str_replace("_"," ",$key))." from <i>".$prevField."</i> to <i>".$value."</i>";
							}
						}else{
							if(!empty(trim($prevField)) && !empty(trim($value))){
								$message.= "<br/> - Changed ".ucwords(str_replace("_"," ",$key))." from <i>".$prevField."</i> to <i>".$value."</i>";
							}else if(!empty(trim($value)) ){
								$message.= "<br/> - Added  <i>".$value."</i> to ".ucwords(str_replace("_"," ",$key));
							}else if (empty(trim($value))){
								$message.= "<br/> - Removed  <i>".$prevField."</i> from ".ucwords(str_replace("_"," ",$key));
							}
						}

					}

				}

				if ( count($addedExtraStops) > 0 ) {	
					for( $i = 0; $i < count($addedExtraStops);  $i++ ) {
						$stopsEditedFields = array();
						if ( isset($updatedExtraStops[$i]['id']) && $updatedExtraStops[$i]['id'] == $addedExtraStops[$i]['id'] ) {
							$stopsEditedFields 	= array_diff_assoc($updatedExtraStops[$i],$addedExtraStops[$i]);
							if ( !empty($stopsEditedFields)) {
								$logMessage = 1;
								if ( $message == '')  {
									$message .= '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited the job ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$result.',\'\',\'\',\'\',\'\',0,\'\')">#'.$result.'</a>';
								} 
								foreach( $stopsEditedFields as $stopKey => $stopValue) {
									$prevField = isset($addedExtraStops[$i][$stopKey]) ? $addedExtraStops[$i][$stopKey] : "" ;
									$j = $i+1;
									switch ($stopKey) {
										case 'extraStopEntity': 
											$prevField = ($prevField != '') ? $prevField : 'select entity';
											$message .= "<br/> - Changed extra stop entity from <i>".$prevField."</i> to <i>".$stopValue."</i> for <b>extra stop (". $j .")</b>" ;
														  break;
										case 'extraStopName': 
											$message .= "<br/> - Changed extra stop name from <i>".$prevField."</i> to <i>".$stopValue."</i> for <b>extra stop (". $j .")</b>" ;
														  break;
										case 'extraStopPhone': 
											$message .= "<br/> - Changed extra stop phone from <i>".$prevField."</i> to <i>".$stopValue."</i> for <b>extra stop (". $j .")</b>" ;
													  break;
													  
										case 'extraStopDate': 
											$message .= "<br/> - Changed extra stop date from <i>".$prevField."</i> to <i>".$stopValue."</i> for <b>extra stop (". $j .")</b>" ;
														  break;
										case 'extraStopTime': 
											$message .= "<br/> - Changed extra stop time from <i>".$prevField."</i> to <i>".$stopValue."</i> for <b>extra stop (". $j .")</b>" ;
														  break;
										case 'extraStopTimeRange': 
											$message .= "<br/> - Changed extra stop time range from <i>".$prevField."</i> to <i>".$stopValue."</i> for <b>extra stop (". $j .")</b>" ;
														  break;
										case 'extraStopAddress': 
											$message .= "<br/> - Changed extra stop address from <i>".$prevField."</i> to <i>".$stopValue."</i> for <b>extra stop (". $j .")</b>" ;
														  break;
										case 'extraStopCity': 
											$message .= "<br/> - Changed extra stop city from <i>".$prevField."</i> to <i>".$stopValue."</i> for <b>extra stop (". $j .")</b>" ;
														  break;
										case 'extraStopState': 
											$message .= "<br/> - Changed extra stop state from <i>".$prevField."</i> to <i>".$stopValue."</i> for <b>extra stop (". $j .")</b>" ;
														  break;
										case 'extraStopZipCode': 
											$message .= "<br/> - Changed extra stop zip code from <i>".$prevField."</i> to <i>".$stopValue."</i> <b>for extra stop (". $j .")</b>" ;
														  break;
										case 'extraStopCountry': 
											$message .= "<br/> - Changed extra stop country from <i>".$prevField."</i> to <i>".$stopValue."</i> <b>for extra stop (". $j .")</b>" ;
														  break;
									}
								}
							}
						}
					}
				
					
				} 

				if( $logMessage == 0 ) {
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited the job ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$result.',\'\',\'\',\'\',\'\',0,\'\')">#'.$result.'</a>, but changed nothing.';
				}
				/*if( count($totalsBuffer) > 0  ){
					$message.="<br> As a result";
					foreach ($totalsBuffer as $key => $value) {
						$prevField = isset($jobOldData[$key]) ? $jobOldData[$key] : "" ;
						if(!empty(trim($prevField))){
							$message.= "<br/> - ".ucwords(str_replace("_"," ",$key))." has been changed from <i>".$prevField."</i> to <i>".$value."</i>";
						}
					}
				}*/
				logActivityEvent($result, $this->entity["ticket"], $this->event["edit"], $message, $this->Job,$_POST["srcPage"]);
			} else {
				if ( $loadFrom == 'ourLoad' ){
					$result = $this->Job->save_Job($saveData,$extraStopsArray, $id);
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> added a new job ticket <a href="javascript:void(0);" class="notify-link" ng-click="clickMatchLoadDetail(0,'.$result.',\'\',\'\',\'\',\'\',0,\'\')">#'.$result.'</a>';
					logActivityEvent($result, $this->entity["ticket"], $this->event["add"], $message, $this->Job,$_POST["srcPage"]);
				}
				else{
					$result = $this->Job->update_job($id , $vehicle_id, $saveData, $extraStopsArray);
				}
			}
		} catch(Exception $e){
			log_message('error',$loadFrom.'ADD_EDIT_JOB_TICKET'.$e->getMessage());
		}

		$this->load->model('Billing');
		$newDestlabel = array();
		
		$table_title = '';
		if(isset($_COOKIE["_globalDropdown"]) && !empty($_COOKIE["_globalDropdown"]) && $loadFrom == 'assignedLoads' ){
			$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
			
			if (isset($gDropdown["label"]) && ( $gDropdown["label"] == "_team" || is_numeric($gDropdown["label"]) ) ) {  //A Dispatcher's All drivers
				
				if ( $vehicleDriverFlag == $saveData['vehicle_id'] ) 
					$vehicleDriverFlag = 1;
				else 
					$vehicleDriverFlag = 0;
					
				if ( $vehicleDriverFlag == 0 ) {
					$gVehicleId = $gDropdown["vid"];
					$statesAddress = $this->Vehicle->get_vehicles_address($this->userId,$gVehicleId);
					$results = $this->Vehicle->getLastLoadRecord($gVehicleId, $gDropdown['id']);
					if ( !empty($results) ){
						$this->origin_state = $results['DestinationState'];
						$this->origin_city = $results['DestinationCity'];
					} else {
						$this->origin_state = $statesAddress['0']['state'];
						$this->origin_city = $statesAddress['0']['city'];
					}
					$table_title = 'Truck-'.$statesAddress[0]['label'].' '.$this->origin_city.'-'.$this->origin_state;
				}
				
			} else {
				$vehicleDriverFlag = 1;
			}
		} else {
			$vehicleDriverFlag = 1;
		}
		
		
		if ( isset($saveData['driver_id']) && $saveData['driver_id'] != '' && $saveData['driver_id'] != 0 ) {
			$getDriverName = $this->Job->getDriverVehicleNames( $result, $saveData['driver_type'], $loadFrom );	
			if( !empty($getDriverName) ) {
				$saveData['driverName'] = $getDriverName['driverName'];
			}
		}
		
		$saveData['equipment_options'] = isset($saveData['EquipmentTypes']['Code']) ? $saveData['EquipmentTypes']['Code'] : '';
		$billType = (isset($saveData['billType']) && $saveData['billType'] != '' ) ? $saveData['billType'] : 'broker';
		if ( isset($saveData['broker_id']) && $saveData['broker_id'] != '') {
			$bufferedInfo  = $this->Job->getEntityInfoById($saveData["broker_id"],$billType);
			$saveData["companyName"] = $bufferedInfo["TruckCompanyName"];
		}

		if ( $loadFrom == 'assignedLoads') {
			echo json_encode(array('id' => $result,'savedData' => $saveData, 'checkVehicleDriverFlag' => $vehicleDriverFlag, 'table_title' => $table_title));	
		} else if ( $loadFrom == 'billingLoads' ) {
			echo json_encode(array('id' => $result,'savedData' => $saveData));
		}  else if ( $loadFrom == 'readyForInvoice' ) {				// check if load edited is from ready for invoice loads on billable page
			echo json_encode(array('id' => $result,'savedData' => $saveData));
		} else if ( $loadFrom == 'sendForPayment' ) {
			// $jobs = $this->Billing->fetchLoadsForPayment();
			// echo json_encode(array('id' => $result,'savedData' => $saveData, 'billingLoads' => $jobs));
			echo json_encode(array('id' => $result,'savedData' => $saveData));
		} else {
		 	echo json_encode(array('id' => $result,'savedData' => $saveData));
		}
	}
	
	/**
	 * saving broker and shipping info for custom added load
	 */ 

	public function skipAcl_saveBrokerShipperInfo( $loadId = null , $type = '' ) {
		$_POST = json_decode(file_get_contents('php://input'), true);
		
		$records = $_POST['loadRecords'];
		$result = $this->Job->saveBrokerShipperInfo( $records, $loadId, $type);
		echo json_encode(array('success' => true));
	}
	
	/**
	 *  finding diesel cost for loaded distance, deadmiles
	 */ 
	 
	private function findDieselCosts( $truckAverage = 6, $distance = null, $dieselFuelPrice =  null, $driverCal = '',$driverAssignType="driver") {
		$truckAverage = ( $truckAverage != null && $truckAverage != '' && $truckAverage != 0 ) ? $truckAverage : 6;
		$gallon_needed = ($distance / $truckAverage);
		$comp_diesel_cost = round($dieselFuelPrice * $gallon_needed,2);
		if ( $driverCal == 'driverMiles' ){
			//--------------- Code for Team task ----------------
			$driverPayMilesCargo = $this->driver_pay_miles_cargo;
			if($driverAssignType == "team"){
				$driverPayMilesCargo = $this->driver_pay_miles_cargo_team;
			}
			//--------------- Code for Team task end--------------
			$comp_diesel_cost = $comp_diesel_cost + round( $distance * $driverPayMilesCargo, 2 );
		} 
		return $comp_diesel_cost;
	}
	
	/** showing trailer type on main search page */
	public function skipAcl_fetchTrailerType(  $pickUpDate ='' ){
		$newData['trailerTypes'] = $this->getTrailerTypes();
		$newData['driversList'] = $this->Driver->getSearchDriversList();
		$new = array("id"=>"","driverName"=>"Unassign","username"=>"","vehicleId" => "");
		array_unshift($newData['driversList'], $new);
		echo json_encode($newData);
	}
	
	/**
	 * Getting trailer types availabele
	 */
	
	private function getTrailerTypes() {
		$trailerTypes = array();
		$trailerTypes = $this->Vehicle->fetchTrailerTypes();
		$i = 0;
		foreach($trailerTypes  as $trailer ) {
			$trailerTypes[$i]['name'] = str_replace(',',' ',$trailer['name']);
			$i++;
		}
		return $trailerTypes;
	} 
	
	/**
	 * Adding load manually
	 */
	 
	public function addNewLoad() {
		$data['driversList'] = $this->Driver->getDriversList(false,true); //Skip driver if he is already in team
		//------ Add Drivers as team in drivers dropdown ------------
		$teamList = $this->Driver->getDriversListAsTeam();
		if(is_array($teamList) && count($teamList) > 0){
			foreach ($teamList as $key => $value) {
				$value["label"] = "_team";
				array_unshift($data['driversList'], $value);
			}
		}
		//------ Add Drivers as team in drivers dropdown end---------

		$new = array("id"=>"","profile_image"=>"","driverName"=>"Unassign","label"=>"","username"=>"","latitude"=>"","longitude"=>"","vid"=>"","city"=>"","vehicle_address"=>"","state"=>"");
		array_unshift($data['driversList'], $new);
		
		$data['trailerTypes'] = $this->getTrailerTypes();
		echo json_encode($data);
	} 
	
	/**
	 * Changing Profit calculation on payment change
	 */
	
	public function skipAcl_changingProfitCalulations() {
		$_POST = json_decode(file_get_contents('php://input'), true);
		$jobRecord = $_POST['allData'];
		
		$jobRecord['PaymentAmount'] = str_replace(',','',$jobRecord['PaymentAmount']);
		$jobRecord['PaymentAmount'] = str_replace('$','',$jobRecord['PaymentAmount']);
		$overall_total_charge_Cal = $jobRecord['totalCost'];
		
		$data['overall_total_profit_Cal'] = round(($jobRecord['PaymentAmount'] - $overall_total_charge_Cal), 2);
		
		if ( $jobRecord['PaymentAmount'] == 0 || $jobRecord['PaymentAmount'] == null )
			$data['overall_total_profit_percent_Cal'] = 0;
		else
			$data['overall_total_profit_percent_Cal'] = getProfitPercent($data['overall_total_profit_Cal'],$jobRecord['PaymentAmount']);
			
		$data['overall_total_rate_mile_Cal'] = round($jobRecord['PaymentAmount'] / $jobRecord['Mileage'], 2);
		
		echo json_encode($data);
	}

	public function skipAcl_getCommodities(){
		$postObj = json_decode(file_get_contents('php://input'), true);
		if(isset($postObj["commodity"]) && !empty(trim($postObj["commodity"]))){
			$response = $this->Job->getMatchedCommodities($postObj["commodity"]);
		}
		echo json_encode(array("suggestions"=>$response));
	}

	/**
	 * Re calculating all things on miles or deadmiles change
	 */
	
	public function skipAcl_reCaluclationsOfmiles() {
		$_POST = json_decode(file_get_contents('php://input'), true);
		$jobRecord = $_POST['allData'];
		$driverAssignType = isset($_POST["driverAssignType"]) && $_POST["driverAssignType"] == "team" ? $_POST["driverAssignType"] : "driver";
		$this->recalculateDeadmiles($jobRecord,'',null,$driverAssignType);			
	} 

	/**
	 * Recalculating deadmiles
	 */
	
	private function recalculateDeadmiles( $jobRecord = array() , $parameter = '', $setDeadMilePage = null , $driverAssignType = "driver") {
		if ( !isset($jobRecord['PaymentAmount']) || $jobRecord['PaymentAmount'] == null || $jobRecord['PaymentAmount'] == '' )
			$jobRecord['PaymentAmount'] = 0;
		else {
			$jobRecord['PaymentAmount'] = str_replace(',','',$jobRecord['PaymentAmount']);
			$jobRecord['PaymentAmount'] = str_replace('$','',$jobRecord['PaymentAmount']);
		}
		
		$truckAverage = $this->defaultTruckAvg;
		$newDestinLocation = '';
		$vehicleId = (isset($jobRecord['vehicle_id']) && !$jobRecord['vehicle_id']) ? $jobRecord['vehicle_id'] : '';
		$originLocation = ( isset($jobRecord['PickupAddress']) && $jobRecord['PickupAddress'] != '' ) ? $jobRecord['PickupAddress'] : '';
		$destinLocation = ( isset($jobRecord['DestinationAddress']) && $jobRecord['DestinationAddress'] != '' ) ? $jobRecord['DestinationAddress'] : '';
		$newDest = array();
		
		if ( $parameter == 'date') {
			if ( $vehicleId != '' && $vehicleId != null && $vehicleId != 0 ) {
				
				//~ $pickupDate = (isset($jobRecord['PickupDate']) && $jobRecord['PickupDate'] != '' ) ? date('m/d/y',strtotime(@$jobRecord['PickupDate'])) : '';
				$pickupDate = (isset($jobRecord['PickupDate']) && $jobRecord['PickupDate'] != '' ) ? @$jobRecord['PickupDate'] : '';
				if ( $pickupDate == '' || $pickupDate == '01/01/70' || $pickupDate == '00/00/00' || $pickupDate == '0000-00-00' )
					$pickupDate = '';
				 	
				$updateLocations = $this->Vehicle->updateTruckDestination($vehicleId, $pickupDate, $setDeadMilePage);
				$vehicle_fuel_consumption = $this->Vehicle->get_vehicles_fuel_consumption($vehicleId);
				
				if ( !empty($vehicle_fuel_consumption ) )
					$truckAverage =round( 100/$vehicle_fuel_consumption[0]['fuel_consumption'],2);
				
				if ( !empty($updateLocations) ) {	
					array_push($newDest,$updateLocations['DestinationState'],$updateLocations['DestinationCity'], $updateLocations['DestinationCountry']);	
					$newDestinLocation = $updateLocations['DestinationCity'].', '.$updateLocations['DestinationState'].', '.$updateLocations['DestinationCountry'];
				} else {
					if ( $vehicle_fuel_consumption[0]['destination_address'] != '' ) {
						$newDest = explode(',',$vehicle_fuel_consumption[0]['destination_address']);
					} else {
						array_push($newDest,$vehicle_fuel_consumption[0]['state'],$vehicle_fuel_consumption[0]['city']);
					}
					$newDestinLocation = $newDest[1].', '.$newDest[0].', USA';
				}
			}
			
			if ( $newDestinLocation != '' ) {
				$resultDeadMile = $this->User->getTimeMiles($originLocation,$newDestinLocation);
				if ( !empty($resultDeadMile) ) {
					$newDeadMiles = str_replace(',','',$resultDeadMile['miles']);
				} else {
					$result = $this->GetDrivingDistance( $originLocation, $newDestinLocation );
					if ( !empty($result) ) {
						$newDeadMiles = ceil(str_replace(',','',$result['distance']));
					} else {
						$newDeadMiles = 0;
					}
				}
				
				if ( $newDeadMiles == '1ft' || strpos($newDeadMiles,'ft') !== false  ) 
					$newDeadMiles = 0;
				$jobRecord['deadmiles'] = $newDeadMiles; 
			}		
		} else {
					
			if ( $vehicleId != '' && @$vehicleId != null ) {
				$vehicle_fuel_consumption = $this->Vehicle->get_vehicles_fuel_consumption(@$vehicleId);
				if ( !empty($vehicle_fuel_consumption ) ) {
					$truckAverage =round( 100/$vehicle_fuel_consumption[0]['fuel_consumption'],2);
					// $truckAverage =(int)($vehicle_fuel_consumption[0]['fuel_consumption']/100);
				} 
			}
		}
	
		$jobRecord['totalCost'] = (isset($jobRecord['totalCost']) && $jobRecord['totalCost'] != '' ) ? $jobRecord['totalCost'] : 0;
		if ( isset($jobRecord['deadMileDistCost']) && $jobRecord['deadMileDistCost'] != ''  )		
			$jobRecord['totalCost'] = ($jobRecord['totalCost'] - $jobRecord['deadMileDistCost']);
				
		if ( isset($jobRecord['loadedDistanceCost']) && $jobRecord['loadedDistanceCost'] )	
			$jobRecord['totalCost'] = ($jobRecord['totalCost'] - $jobRecord['loadedDistanceCost']);
					
					
		$newDistanceCal = ( isset($jobRecord['Mileage']) && $jobRecord['Mileage'] != '' && $jobRecord['Mileage'] != null && $jobRecord['Mileage'] != '1ft') ? $jobRecord['Mileage'] : 0;

		$jobRecord['Mileage'] = isset($jobRecord['Mileage']) ? $jobRecord['Mileage'] : 0;
		$jobRecord['deadmiles'] = isset($jobRecord['deadmiles']) ? $jobRecord['deadmiles'] : 0;
		
		$jobRecord['loadedDistanceCost'] = $this->findDieselCosts( $truckAverage,$jobRecord['Mileage'],$this->diesel_rate_per_gallon, 'driverMiles',$driverAssignType);
		$jobRecord['deadMileDistCost'] = $this->findDieselCosts( $truckAverage,$jobRecord['deadmiles'],$this->diesel_rate_per_gallon, 'driverMiles',$driverAssignType);
		$jobRecord['estimatedFuelCost'] = $this->findDieselCosts( $truckAverage, ( $newDistanceCal + $jobRecord['deadmiles'] ),$this->diesel_rate_per_gallon, '',$driverAssignType );
		
		$jobRecord['totalCost'] = @$jobRecord['totalCost'] + $jobRecord['deadMileDistCost'] + $jobRecord['loadedDistanceCost'];
		$overall_total_charge_Cal = $jobRecord['totalCost'];		
		$data['overall_total_profit_Cal'] = round(($jobRecord['PaymentAmount'] - $overall_total_charge_Cal), 2);
		
		if ( $jobRecord['PaymentAmount'] == 0 || $jobRecord['PaymentAmount'] == null )
			$data['overall_total_profit_percent_Cal'] = 0;
		else
			$data['overall_total_profit_percent_Cal'] = getProfitPercent($data['overall_total_profit_Cal'],$jobRecord['PaymentAmount']);
			
		echo json_encode(array('overall_total_charge_Cal' => $jobRecord['totalCost'], 'overall_total_profit_Cal' => $data['overall_total_profit_Cal'], 'overall_total_profit_percent_Cal' => $data['overall_total_profit_percent_Cal'], 'loadedDistanceCost' => $jobRecord['loadedDistanceCost'], 'deadMileDistCost' => $jobRecord['deadMileDistCost'], 'estimatedFuelCost' => $jobRecord['estimatedFuelCost'], 'deadmilesDistance' => $jobRecord['deadmiles']));		
	}
	 
	/**
	 * Check if same pick date exist for another load
	 */
	
	public function skipAcl_checkDateLoadExist( $parameter = '' ) {
		$_POST = json_decode(file_get_contents('php://input'), true);
		$jobRecord = $_POST['allData'];
		$setDeadMilePage = @$_POST['setDeadMilePage'];
		$driverAssignType = isset($_POST["driverAssignType"]) && $_POST["driverAssignType"] == "team" ? $_POST["driverAssignType"] : "driver";
	
		$errorMsg = '';
		if ( $parameter == 'pick' ) {
			$date = $jobRecord['PickupDate'];
			if ( isset($jobRecord['Stops']) && $jobRecord['Stops'] > 0 && !empty($_POST['extraStopsDate']) && isset($_POST['extraStopsDate']['extraStopDate_0']) && $_POST['extraStopsDate']['extraStopDate_0'] != '' && $_POST['extraStopsDate']['extraStopDate_0'] != '0000-00-00'  ) { 				// check if pickup date is less than first extra Stop
				if ( strtotime($date) > strtotime($_POST['extraStopsDate']['extraStopDate_0']) ) {
					$errorMsg .= 'Caution: This pickup date should be less than '.$_POST['extraStopsDate']['extraStopDate_0'].' date';
					$error = 'extraStopDateIssue';
					echo json_encode(array('error' => $error,'errorMsg' => $errorMsg));
					exit();
				}
			}
		} else {
			$date = $jobRecord['DeliveryDate'];
			if ( isset($jobRecord['Stops']) && $jobRecord['Stops'] > 0 && !empty($_POST['extraStopsDate']) ) {
				$k = $jobRecord['Stops'] - 1;
				if ( $_POST['extraStopsDate']['extraStopDate_'.$k] != '' && $_POST['extraStopsDate']['extraStopDate_'.$k] != '0000-00-00' ) {  			// check if delivery date is greater than last extra Stop
					if ( strtotime($date) < strtotime($_POST['extraStopsDate']['extraStopDate_'.$k]) ) {
						$errorMsg .= 'Caution: This delivery date should be greater than '.$_POST['extraStopsDate']['extraStopDate_'.$k].' date';
						$error = 'extraStopDateIssue';
						echo json_encode(array('error' => $error,'errorMsg' => $errorMsg));
						exit();
					}
				}
			}
		}
		
		if ( isset($jobRecord['vehicle_id']) && @$jobRecord['vehicle_id'] != ''  ) {
			$result = $this->Job->checkLoadDateExist($date,$parameter, $jobRecord['vehicle_id'],@$jobRecord['id']);
		
			if ( empty($result) ) {
				if ( $parameter == 'pick' && $setDeadMilePage != 1)			// Set deadmile plan page not to change deadmiles with date change
					$this->recalculateDeadmiles($jobRecord,'date', $setDeadMilePage,$driverAssignType);			// date parameter if date is changed   $setDeadMilePage to check plan page calculations in deadmiles
				else
					echo json_encode(array('success' => true ));	
			} else {
				
				if ( $parameter == 'pick' )
					$error = 'alreadyBookedPickDate';
				else
					$error = 'alreadyBookedDeliveryDate';
					
				echo json_encode(array('error' => $error));
			}
		} else {
			echo json_encode(array('success' => true ));
		}	
	}
	
	/**
	 * Compare extra Stops date if they are greater than pickup date
	 */
	 
	public function skipAcl_compareExtraStopDates() {
		$_POST = json_decode(file_get_contents('php://input'), true);
		
		$extraStops = array();
		if ( !empty($_POST['extraStopsDate']) ) 
			$extraStops = $_POST['extraStopsDate'];
		
		$errorMsg = '';
		$j = 0;
		if ( !empty($extraStops) ) {
			for( $i = 0; $i < $_POST['allData']['Stops']; $i++ ) {
				if ( isset($extraStops['extraStopDate_'.$i]) && $extraStops['extraStopDate_'.$i] != '' && $extraStops['extraStopDate_'.$i] != '0000-00-00' ) {
					$cmpDate = $extraStops['extraStopDate_'.$i];
					$assDate = $cmpDate;
					if ( isset($_POST['allData']['PickupDate']) && $_POST['allData']['PickupDate'] != '' && $_POST['allData']['PickupDate'] != '0000-00-00' && isset($_POST['allData']['DeliveryDate']) && $_POST['allData']['DeliveryDate'] != '' && $_POST['allData']['DeliveryDate'] != '0000-00-00' ) {
						$res = $this->datesComparison($cmpDate,$_POST['allData']['PickupDate'],$_POST['allData']['DeliveryDate'], 'between');
						if ( $res == false ) {
							$errorMsg .= 'The date '.$cmpDate. ' does not lie b/w pickup and delivery date';
							break;
						}
					} else if ( isset($_POST['allData']['PickupDate']) && $_POST['allData']['PickupDate'] != '' && $_POST['allData']['PickupDate'] != '0000-00-00' ) {
						$res = $this->datesComparison($cmpDate,$_POST['allData']['PickupDate'],'', 'pick');
						if ( $res == false ) {
							$errorMsg .= 'The date '.$cmpDate. ' should be greater than pickup date';
							break;
						}
					} else if ( isset($_POST['allData']['DeliveryDate']) && $_POST['allData']['DeliveryDate'] != '' && $_POST['allData']['DeliveryDate'] != '0000-00-00' ) {
						$res = $this->datesComparison($cmpDate,'',$_POST['allData']['DeliveryDate'], 'deliver');
						if ( $res == false ) {
							$errorMsg .= 'The date '.$cmpDate. ' should be less than Delivery date';
							break;
						}
					} 
					
				
					if ( $i != 0 && $i != $_POST['allData']['Stops'] - 1 ) {
						$j = $i + 1;
						$k = $i - 1;
						$cmpDate = isset($extraStops['extraStopDate_'.$j]) ? $extraStops['extraStopDate_'.$j] : '';
						$befDate = isset($extraStops['extraStopDate_'.$k]) ? $extraStops['extraStopDate_'.$k] : '';
						
						$res = $this->datesComparison($assDate,$befDate,$cmpDate, 'between');				// check if extra stop date lies b/w extra stops records
						if ( $res == false ) {
							$errorMsg .= 'The date '.$cmpDate. ' does not lie b/w two extra stop dates';
							break;
						}
						
						if ( strtotime($assDate) <= strtotime($cmpDate) ) {
						
						} else {
							$errorMsg .= 'The date '.$assDate. ' should be smaller than '.$cmpDate.' date';
							break;
						}
					} else {
						if ( $i != 0 && $i ==  $_POST['allData']['Stops'] - 1) {			// check for last date in extra Stop
							$j = $i - 1;
							$cmpDate = isset($extraStops['extraStopDate_'.$j]) ? $extraStops['extraStopDate_'.$j] : '';
							if ( strtotime($cmpDate) > strtotime($assDate) ) {
								$errorMsg .= 'The date '.$assDate. ' should be greater than '.$cmpDate.' date';
								break;
							}
						}
					}
					
				}
			}
		}
		echo json_encode(array('error' => $errorMsg));
	}
	
	private function datesComparison( $cmpDate = '', $pickDate = '' , $delDate = '' , $parameter = '' ) {
		if ($parameter == 'between' ) {
			return ( strtotime($pickDate) <= strtotime($cmpDate) && strtotime($cmpDate) <= strtotime($delDate) ) ? true : false;
		} else {
			if ( $parameter == 'pick' )
				return ( strtotime($pickDate) <= strtotime($cmpDate)  ) ? true : false;
			if ( $parameter == 'deliver' )
				return ( strtotime($cmpDate) <= strtotime($delDate) ) ? true : false;
		}
		
	}
	 
	/**
	 * Fetch zip code from address using api
	 */
	
	public function skipAcl_getZipCode() {
		$_POST = json_decode(file_get_contents('php://input'), true);
		$zipCod = '';
		
		if ( isset( $_POST['type'] ) && $_POST['type'] == 'extraStop' ) {
			if ( !empty($_POST['stopArray']) ) {
				$index = $_POST['indexValue'];
				$address = trim($_POST['stopArray']['extraStopAddress_'.$index]).','.$_POST['stopArray']['extraStopCity_'.$index].','.$_POST['stopArray']['extraStopState_'.$index].','.trim($_POST['stopArray']['extraStopCountry_'.$index]);
			
				$address = trim($address,',');
				$zipCode = $this->getZipCodeFromApi($address);
				if ( $zipCode ) {
					$zipCod = $zipCode;
				}
			}
		} else {
			if ( !empty($_POST['address']) ) {
				$address = trim($_POST['address']);
				$address = trim($address,',');
				$zipCode = $this->getZipCodeFromApi($address);
				if ( $zipCode ) {
					$zipCod = $zipCode;
				}
			}
		}
		
		echo json_encode(array('zipcode' => $zipCod));
	} 
	
	/**
	 * Fetching zip code from api by passing address
	 */
	
	private function getZipCodeFromApi( $address = '' ) {
		$data = array();
		 if(!empty($address)) {
			 
			$zipResult = $this->Job->checkZipCodeExist($address);
			if ( !empty($zipResult) ) {
				$zipcode = $zipResult['zipcode'];
				return $zipcode;
			} else {
				$googleKeys = array("AIzaSyCUrQy4amWsY8cxSPaYCMNX6aY9CWWcAtk","AIzaSyBWhHYF6lq4yQ6Jt5DaAo9yS6Bn7-YuDrE","AIzaSyCVxoQq3PxPYechPY9tDyx6vhI8ty27tKk","AIzaSyDWrNBqjp0Ntb66QPa1CZe3UlGyofJDXJY","AIzaSyA1DxFGAg6k6YLw0B9o28cNPL77wHyxvDM","AIzaSyBmxRpO-qdJN5X8fG4_1jrkF_xwcWTKHw0","AIzaSyA-b6uwprb6K9i0SM8ehNapE0ETBEub1xQ", "AIzaSyAFnTukWwp_aQiSbJRaWmTv4k_6Kd3_O9M","AIzaSyBSDP3t4gPmtO-Tm-0bmDj73V3BrgRdJwo","AIzaSyBdFIw8EICyOqA0XNvv1rrMd1B2Int80FI","AIzaSyAc_zfJ9tOrU84jMxkl2QaUznVOOfRZ97M","AIzaSyAUi8ISK2bJ-5QClFr9ECvmE_Ypco4fK0U","AIzaSyAXlFcRW94HggsO8oTWLhO8GYpG1Wy60vg","AIzaSyBLiUcHYsofoyQEj3Z-YbNg_O10-bl1MAU","AIzaSyB8TGBHB7KUtxaMEarzZ39pwnX7evG9Fcc","AIzaSyBsZPuC5cca9gr4qPZ-p1O9hPzp3serepk", "AIzaSyBYpjbCLWTZGK-8Am7Z39_UIUiN88nz7EQ", "AIzaSyCJ04xh3kIVPFSO-MICsJELeab0AMoYWdM");
				
				$keyIndex = -1;
				$validKey = '';
				if(!$this->session->has_userdata('validGoogleKey')){
					$this->session->set_userdata('validGoogleKey', $googleKeys[0]);
				}
				
				while(true){
					//Formatted address
					$formattedAddr = str_replace(' ','+',$address);
					//Send request and receive json data by address
					$geocodeFromAddr = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddr.'&sensor=true_or_false&key='.$this->session->validGoogleKey); 
					$output1 = json_decode($geocodeFromAddr);
					$response_a = json_decode($geocodeFromAddr, true);
					if(in_array($response_a["status"], array("OVER_QUERY_LIMIT","REQUEST_DENIED"))){
						if($googleKeys[++$keyIndex] != $this->session->validGoogleKey){
							$this->session->set_userdata('validGoogleKey', $googleKeys[$keyIndex]);	
						}else{
							$this->session->set_userdata('validGoogleKey', $googleKeys[++$keyIndex]);	
						}
					} else if ($response_a["status"] == "OK"){
						$validKey = $this->session->validGoogleKey;
						break;
					} else {
						$keyIndex++;
					}
					
					if($keyIndex >= count($googleKeys)){
						break;
					}
				}
				
				if(isset($output1->status) && $output1->status == "ZERO_RESULTS"){
					return "";
				}
				//Get latitude and longitute from json data
				$latitude  = $output1->results[0]->geometry->location->lat; 
				$longitude = $output1->results[0]->geometry->location->lng;
				
				$data['latitude'] = $latitude;
				$data['longitude'] = $longitude;
					
				//Send request and receive json data by latitude longitute
				$geocodeFromLatlon = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$latitude.','.$longitude.'&sensor=true_or_false&key='.$validKey);
				$output2 = json_decode($geocodeFromLatlon);
				$response_a = json_decode($geocodeFromLatlon, true);
								
				if(!empty($output2)) {
					$addressComponents = $output2->results[0]->address_components;
					foreach($addressComponents as $addrComp){
						if($addrComp->types[0] == 'postal_code'){
							//Return the zipcode
							$data['zipcode'] = $addrComp->long_name;
							$data['address'] = str_replace(' ','~',$address);
					
							$this->Job->saveZipCode($data);
							return $addrComp->long_name;
						}
					}				
				} else {
					return false;
				}
			}
		} else {
			return false;   
		}
	}
	 
	
}
