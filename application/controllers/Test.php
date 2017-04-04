<?php

/**
* Truck stop Api Controller
*  
*/

class Test extends Admin_Controller {

	public $username;
	public $password;
	public $id;
	public $accountID;
	public $rows;
	private $pickupDate;
	public $userId;
	public $origin_city;
	public $origin_state;
	private $Rpm_value;
	private $saveDead;
	private $saveCalPayment;
	public $finalArray;
	public $wsdl_url;
	public $diesel_rate_per_gallon;
	public $driver_pay_miles_cargo;
	public $total_tax;
	public $deadMilePaid;
	public $deadMileNotPaid;
	public $payForDeadMile;
	public $iftaTax;
	public $tarps;
	public $det_time;
	public $tollsTax;
	public $todayDate;
	public $extraStopsDataArray;
	public $extraStopCharge;
	public $extraStopPerStopCharge;
	public $deadMilesActual;
	public $triumphToken;
	public $defaultTruckAvg;
	
	function __construct(){

		parent::__construct();
		
		$this->pickupDate 	= date('Y-m-d');
		
		$this->load->model('Vehicle');
		$this->load->model('Driver');
		$this->load->model('User');
		$this->load->model('Billing');

		if($this->session->role != 1){
			$this->userId 		= $this->session->loggedUser_id;
			$this->origin_state  = $this->Vehicle->get_vehicles_state($this->session->admin_id);
		}
		//$this->accountId 	= $this->config->item('truck_accountId');
		$this->load->model('Job');
		$this->load->library('Htmldom');
		$this->load->helper('truckstop');
		
		$this->Rpm_value = 0;
		//$this->userId = 6;	
		$this->saveDead = '';
		$this->finalArray = array();
		$this->wsdl_url = 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl'; //Live Mode Api
		$this->diesel_rate_per_gallon = 2.50;
		$this->driver_pay_miles_cargo = 0.58;
		$this->total_tax = 50;
		
		$this->deadMilePaid = 0;
		$this->deadMileNotPaid = 0;
		$this->payForDeadMile = 0.58;
		$this->iftaTax = 50;
		$this->tarps = 0;
		$this->det_time = 0;
		$this->tollsTax = 0;
		
		$this->extraStopsDataArray = array();
		$this->todayDate = date('m/d/y');
		$this->extraStopCharge = 0;
		$this->extraStopPerStopCharge = 25;
		
		$this->deadMilesActual = 0;
		$this->defaultTruckAvg = 6;
		
		if ( $this->config->item('triumph_environment') == 'production' ) {
			$this->triuser  			= $this->config->item('triumph_user');
			$this->passwordTriumph		= $this->config->item('triumph_pass');
			$this->apikey   			= $this->config->item('triumph_apik');
			$this->triumphUrl  	 		= $this->config->item('triumph_url');
			$this->triumphUrlRequest   	= $this->config->item('triumph_url_request');
		} else {
			$this->triuser  			= $this->config->item('triumph_user_test');
			$this->passwordTriumph		= $this->config->item('triumph_pass_test');
			$this->apikey   			= $this->config->item('triumph_apik_test');
			$this->triumphUrl  	 		= $this->config->item('triumph_url_test');
			$this->triumphUrlRequest   	= $this->config->item('triumph_url_request_test');
		}
	}
	
	public function index(  $parameter = null ){

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
		
		//~ $newData['trailerTypes'] = $trailerTypes;
		$newData['parameter'] = $parameter;
		
		echo json_encode(array('loadsData'=> $newData),JSON_NUMERIC_CHECK);
	}
	
	public function giveBestLoads( $loads = array(), $destination = '', $loadsIdArray = array() , $vehicleId = null, $todayDate, $extSearch = '' ) {
		if ( !empty($loads) ) {
			
			if ( $vehicleId != '' && $vehicleId != null ) {
				$vehicle_fuel_consumption = $this->Vehicle->get_vehicles_fuel_consumption($this->userId, $vehicleId);
				$truckAverage =round( 100/$vehicle_fuel_consumption[0]['fuel_consumption'],2);
			} else {
				$truckAverage = $this->defaultTruckAvg;
			}
		
			if ( count($loads) > 0) {
				
				foreach ($loads as $key => $value) {
					$this->Rpm_value = 0;
					$loadWeight = (double)$value['Weight'];	
					
					if ( isset($value['PaymentAmount']) ) 
						$loadPayment = (double)(str_replace(',','',$value['PaymentAmount']));	
					else
						$loadPayment = (double)(str_replace(',','',$value['Payment']));	
						
					if ( $extSearch != 'extSearch' ) {
						if ( $value['Length'] > 48 || $loadWeight > 48000 || strtolower($value['Equipment']) == 'cong' || strtolower($value['Equipment']) == 'fws' || $value['Miles'] == 0 || (in_array($value['ID'], $loadsIdArray)) ) {
							unset($loads[$key]);
							continue;
						}
					}

					if ( strtolower($value['PickUpDate']) == 'daily' ) {
						$loads[$key]['displayPickUpDate'] = $value['PickUpDate'];
					} else {
						$loads[$key]['displayPickUpDate'] = date('Y-m-d',strtotime($value['PickUpDate']));	
					}
									
					$origin = $value['OriginCity'].','.$value['OriginState'].','.$value['OriginCountry'];
					//~ $dataMiles = $this->User->getMiles($origin,$destination);
					//~ if(!empty($dataMiles)){
						//~ $loads[$key]['deadmiles'] = $dataMiles;
						//~ $deadMileDist = $dataMiles;
					//~ } else {
						//~ $miles  = $this->GetDrivingDistance($origin,$destination);
						//~ $loads[$key]['deadmiles'] = $miles['distance'];
						//~ $deadMileDist = $miles['distance'];
					//~ }
					$deadMileDist = $value['OriginDistance'];
					$loads[$key]['deadmiles'] = $deadMileDist;
					$Miles = $value['Miles'];
					
					$total_cost = $this->getCompleteCost($Miles,$deadMileDist,$truckAverage);
					
					if ( $loadPayment != 0 && $loadPayment != '' && $loadPayment != null ) {
						if ( $loadPayment < $total_cost && $loadPayment != null ) {
							unset($loads[$key]);
							continue;
						}
						
						if ( $Miles != 0 )
						$this->Rpm_value = round( $loadPayment / $Miles, 2 );
						$loads[$key]['highlight'] = 0;
						
						$loads[$key]['profitAmount'] = round(($loadPayment - $total_cost),2);
						$loads[$key]['percent'] = getProfitPercent($loads[$key]['profitAmount'], $loadPayment);
					} else {
						$calPayment = getPaymentFromProfitMargin($total_cost, 30);

						$loads[$key]['Payment'] = $calPayment;
						if ( $Miles != 0 )
						$this->Rpm_value = round( $calPayment / $Miles, 2 );
						$loads[$key]['highlight'] = 1;
						$loads[$key]['profitAmount'] = round(($loads[$key]['Payment'] - $total_cost),2);
					
						$loads[$key]['percent'] = getProfitPercent($loads[$key]['profitAmount'], $loads[$key]['Payment']);
					} 
					
					$loads[$key]['RPM'] = $this->Rpm_value;
				
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
				
					//------------------ Add class on job if open first time ---------------------------------
				
					$_COOKIE_NAME = 'VISIT_'.$value['ID'].'_'.str_replace("/", "_", $loads[$key]['pickDate']);
					if(isset($_COOKIE[$_COOKIE_NAME])) {
						$loads[$key]['visited']	= true;
					}else{
						$loads[$key]['visited'] = false;
					}

					//------------------ Add class on job if open first time ---------------------------------
								
					$loadsIdArray[] = $value['ID'];				
				}
			}
		
			if ( !empty($loads) && count($loads) > 1 && $extSearch != 'extSearch')  {
				$havePayment = array();
				foreach ($loads as $key => $row)
				{
					//$price[$key] = $row['Miles'];
					$price[$key] = $row['profitAmount'];
					$havePayment[$key] = $row['highlight'];
				}
				//array_multisort($price, SORT_DESC, $loads);
				array_multisort($havePayment, SORT_ASC, $price, SORT_DESC, $loads); //Loads with payment and have heighest profit will be on top
			}
			
		}	
		return array($loads, $loadsIdArray);
	}
	
	private function getCompleteCost( $miles = 0 , $deadmiles = 0, $truckAverage = 0 ) {
		
        $total_complete_distance = $miles + $deadmiles;
		$gallon_needed =  ($total_complete_distance / $truckAverage);
		$total_diesel_cost = $this->diesel_rate_per_gallon * $gallon_needed;
		
		$total_driver_cost = $this->driver_pay_miles_cargo * $total_complete_distance;
		$total_cost = round(($total_diesel_cost + $total_driver_cost + $this->total_tax),2);
			
		return $total_cost;	
	}
	
	public function fetchSearchResults($parameter = false){
			
		
		$this->lang->load('loads',$_REQUEST['setLanguage']);
		$admin_email = $this->session->userdata('loggedUser_email');
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
				if(is_array($dataPost['multiOrigins'])){
					$this->origin_state = implode(',',trim($dataPost['multiOrigins']));
				}else{
					$this->origin_state = $dataPost['multiOrigins'];
				}
			} else {
				$this->origin_city = $dataPost['origin_City'];
				$this->origin_state = $dataPost['origin_State'];
			}
			
			$pickupDateDest = '';
								
			if ( !empty($postData['trailerType']) ) {
				$equipment_type = $dataPost['trailerType'];
			} else {
				$equipment_type = 'F,FSD';
			}
											
			
			$origin_country = isset($dataPost['origin_country']) ? $dataPost['origin_country'] : 'USA';
			$dest_country = isset($dataPost['destination_country']) ? $dataPost['destination_country'] : $dataPost['origin_country'];	
			$destination_range = ( $dataPost['destination_range'] != '' ) ? $dataPost['destination_range'] : 300;			
			$origin_range = ( $dataPost['origin_range'] != '' ) ? $dataPost['origin_range'] : 300;			
			$load_type = ( $dataPost['load_type'] != '' ) ? $dataPost['load_type'] : 'Full';			
			$posted_time = ( isset($dataPost['posted_time']) ) ? $dataPost['posted_time'] : '';			
			$dailyFilter = ( isset($dataPost['dailyFilter']) && $dataPost['dailyFilter'] != '' ) ? $dataPost['dailyFilter'] : 'all';			
					
			if ( isset($dataPost['pickup_date']) && $dataPost['pickup_date'] != '' && $dataPost['pickup_date'] != '0000-00-00' && $dataPost['pickup_date'] != 'undefined' ) {
				if ( strpos($dataPost['pickup_date'], ',') !== false ) {
					$datesNewArray = explode(',',$dataPost['pickup_date']);
					$pickupDate = '';
					for ( $i = 0; $i < count($datesNewArray);  $i++ ) {
						$dateTime[] = $datesNewArray[$i];
						$pickupDate .= $datesNewArray[$i].',';
					}
					$this->todayDate = $datesNewArray[0];
				} else {
				
					$pickupDate = $dataPost['pickup_date'];
					$dateTime = array (
						'dateTime' => $pickupDate,
					);
					$this->todayDate = $pickupDate;
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

			//~ $newID = $newDest[1].'-'.$newDest[0].' ('.$this->lang->line('withIn').' '.$origin_range.' '.$this->lang->line('milesRadius').')'.$this->lang->line('pickup').' '.$pickupDate.' '.$this->lang->line('withIn').' '.$todest.' ('.$load_type.'/'.$this->lang->line('TLOnly').')';
			//~ $newID = ltrim($newID,'-');
		
<<<<<<< .mine
			$data['rows']  = $this->commonApiHitsTest( $this->orign_city, $this->orign_state, $equipment_type, $dateTime, $posted_time, $origin_country, $origin_range, $destination_city, $destination_states,$destination_range, $dest_country, $load_type);
=======
			$data['rows']  = $this->commonApiHits( $equipment_type, $dateTime, $posted_time, $origin_country, $origin_range, $destination_city, $destination_states,$destination_range, $dest_country, $load_type);
>>>>>>> .r123
			
			echo 'Total number of records are '.count($data['rows']);
			pr($data['rows']); 
			$data['pickupDate'] 	= $this->pickupDate;

			$price = array();
			
			$destin = $this->origin_city.','.$this->origin_state.',USA';
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
			$loadsIdArray = array();
			if ( !empty($data['rows'])) {
				$returnArray = $this->giveBestLoads( $data['rows'] , $destin, $loadsIdArray, $vehId, $this->todayDate, $_REQUEST['moreMiles'], $addClass, $dailyFilter);
				$this->finalArray = $returnArray[0];
				$loadsIdArray = $returnArray[1];
			}
				
			$newData = array();
			$finArray = array_values($this->finalArray);
			$newData['rows'] = $finArray;
			
			//~ $newData['table_title'] =  $newID;
			$newData['searchLabel'] =  $vehId;
			$newData['tableCount'] =  count($finArray);
			$newData['loadsIdArray'] =  $loadsIdArray;
			$newData['notShowDataTable'] =  true;
			
			echo json_encode(array('loadsData'=> $newData),JSON_NUMERIC_CHECK);
		}

		
	}
	
	public function fetchTestSearchResults($parameter = false){
		
		$requset = json_decode(file_get_contents('php://input'),true);
		$postData = $requset['post'];
		
		$admin_email = $this->session->userdata('loggedUser_email');
		$admin_loggedin = $this->session->userdata('loggedUser_username');
		if( (isset($admin_email) && $admin_email != '') && (isset($admin_loggedin) && $admin_loggedin != '') ) {
			$newData['shouldNotMoveFurther'] = true;
		} else {
			$newData['shouldNotMoveFurther'] = false;
		}
		
		if(!isset($postData['multiOrigins']) && !isset($postData['origin_City'])){
			$newData = array();
			$newData['notShowDataTable'] 	= false;
			$newData['parameter'] = $parameter;
			echo json_encode(array('loadsData'=> $newData),JSON_NUMERIC_CHECK);
		} else {
			$data['rows'] 		= array();
			$data['no_result'] 	= 'Search loads';
			
			if ( isset($postData['multiDestinations']) && !empty($postData['multiDestinations']) ) {
			
				$destination_states = implode(',',$postData['multiDestinations']);
				$destination_city = '';
			} else {
				$destination_city = @$postData['dest_City'];
				$destination_states = @$postData['dest_State'];
			}
			
			if ( isset($postData['multiOrigins']) && !empty($postData['multiOrigins']) ) {
				$this->origin_city = '';
				if(is_array($postData['multiOrigins'])){
					$this->origin_state = implode(',',$postData['multiOrigins']);
				}else{
					$this->origin_state = $postData['multiOrigins'];
				}
			} else {
				$this->origin_city = $postData['origin_City'];
				$this->origin_state = $postData['origin_State'];
			}
			
			$pickupDateDest = '';
			$abbreviationArray = array();
						
			if ( !empty($postData['trailerType']) ) {
				$equipment_type = $postData['trailerType'];
			} else {
				$equipment_type = 'F,FSD';
			}
											
			$origin_country = isset($postData['origin_country']) ? $postData['origin_country'] : 'USA';
			$dest_country = isset($postData['destination_country']) ? $postData['destination_country'] : $postData['origin_country'];	
			$destination_range = ( $postData['destination_range'] != '' ) ? $postData['destination_range'] : 300;			
			$origin_range = ( $postData['origin_range'] != '' ) ? $postData['origin_range'] : 300;			
			$load_type = ( $postData['loadType'] != '' ) ? $postData['loadType'] : 'Full';			
			$posted_time = ( isset($postData['posted_time']) ) ? $postData['posted_time'] : '';			
			$dailyFilter = ( isset($postData['dailyFilter']) && $postData['dailyFilter'] != '' ) ? $postData['dailyFilter'] : 'all';			
			
			$this->todayDate = date('m/d/y');		
			if ( isset($postData['pickup_date']) && $postData['pickup_date'] != '' && $postData['pickup_date'] != '0000-00-00' && $postData['pickup_date'] != 'undefined' ) {
				if ( strpos($postData['pickup_date'], ',') !== false ) {
					$datesNewArray = explode(',',$postData['pickup_date']);
					$pickupDate = '';
					for ( $i = 0; $i < count($datesNewArray);  $i++ ) {
						$dateTime[] = trim($datesNewArray[$i]);
						$pickupDate .= $datesNewArray[$i].',';
					}
					$this->todayDate = $datesNewArray[0];
				} else {
				
					$pickupDate = $postData['pickup_date'];
					$dateTime = array (
						'dateTime' => $pickupDate,
					);
					$this->todayDate = $pickupDate;
				}
			} else {
				$dateTime = array ();
			}

			$data['rows'] = $this->commonApiHitsTestNew( $this->orign_city, $this->orign_state, $equipment_type, $dateTime, $posted_time, $origin_country, $origin_range, $destination_city, $destination_states,$destination_range, $dest_country, $load_type);

			
			//~ echo 'Total number of records are '.count($data['rows']);
			
			$data['pickupDate'] 	= $this->pickupDate;
			$destin = $this->origin_city.','.$this->origin_state.',USA';
			$loadsIdArray = array();
			$vehId = '';
			if ( !empty($data['rows'])) {
				$returnArray = $this->giveBestLoads( $data['rows'] , $destin, $loadsIdArray, $vehId, $this->todayDate, 0, '', '');
				$this->finalArray = $returnArray[0];
				$loadsIdArray = $returnArray[1];
			}
			//~ $this->finalArray = $data['rows'];	
			$newData = array();
			$finArray = array_values($this->finalArray);
			$newData['rows'] = $finArray;
			
			$newData['tableCount'] =  count($finArray);
			$newData['notShowDataTable'] =  true;
			
			echo json_encode(array('loadsData'=> $newData),JSON_NUMERIC_CHECK);
		}

		
	}

	public function commonApiHitsTestNew( $orign_city = '' , $orign_state = '', $abbreviation = 'F', $dateTime = array() , $hoursOld = '', $origin_country = 'USA', $origin_range = 300 ,$destination_city = '', $destination_states = '', $destination_range = 300, $dest_country = 'USA', $load_type = 'Full') {
		if ( strpos($orign_state,',') !== false ) {
			$states = explode(',',$orign_state);
			$statesCount = count($states);
		} else {
			$statesCount = 1;
		} 
		
		$tsid 			= $this->config->item('truck_id');	
		$username 	= $this->config->item('truck_username');
		$password 	= $this->config->item('truck_password');
		$wsdl_url		= 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl'; //Live Mode Api 
			
			$pageNo = 1;
			$dat = array();
			while(true) {
				$client   = new SOAPClient($wsdl_url);
				$params   = array(
					'searchRequest' => array(
						'UserName' => $username, 'Password' => $password, 'IntegrationId' => $tsid,
						'Criteria' => array(
							'OriginCity' => $orign_city,
							'OriginState' => $orign_state,//Getting records of first state(Dispatcher)
							'OriginCountry' => $origin_country,
							'OriginRange' => $origin_range,
							'OriginLatitude' => '',
							'OriginLongitude' => '',
							'DestinationCity' => $destination_city,
							'DestinationState' => $destination_states,
							'DestinationCountry' =>	$dest_country,
							'DestinationRange' => $destination_range,
							'EquipmentType' => $abbreviation,
							'LoadType' => 'Full',
							'PickupDates' => $dateTime,
							'HoursOld' => $hoursOld,
							'EquipmentOptions' => '',
							'PageNumber'   => $pageNo,
							'PageSize' => 200,
							'SortBy' => 'Miles',
							'SortDescending' => true
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

				$data['rows'] = array();
				if(count($this->rows) == 1){
					$data['rows'][]   = json_decode(json_encode($this->rows),true);
				}else{
					$data['rows'] 	  = json_decode(json_encode($this->rows),true);
				}
				
				$dat = array_merge($dat,$data['rows']);				
				
				if( count($data['rows']) < 200 )  {
					break;
				}
				$pageNo++;
				
				
			}
			
			$this->finalArray  = $dat;
			unset($dat);
			unset($data['rows']);
			return $this->finalArray;
		}
		
	/**
	 *  finding diesel cost for loaded distance, deadmiles
	 */ 
	 
	public function findDieselCosts( $truckAverage = 6, $distance = null, $dieselFuelPrice =  null ) {
		$gallon_needed = ceil($distance / $truckAverage);
		$comp_diesel_cost = round($dieselFuelPrice * $gallon_needed,2);
		return $comp_diesel_cost;
	}
	
	public function testTriumph() {
		echo '<div style="border:1px solid black;width:48%;float:left;margin-right: 20px"><H1>Testing with 34 invoices</H1>';
		$objPost['selectedIds'] = array(10554,10572,10575,10591,10594,10595,10596,10601,10602,10606,10608,10611,10613,10621,10622,10623,10630,10631,10634,10636,10640,10642,10643,10644,10646,10648,10650,10652,10654,10655,10661,10674,10676,10677);
		//~ ,10678,10680,10682,10683,10684,10685,10687,10688,10689,10696,10699,10700,10702,10703,10717);	
		pr($objPost['selectedIds']); 
			
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
	
		echo "</div>";
		
		
		echo '<div style="border:1px solid black;width:50%;float:left;"><H1>Testing with 35 invoices</H1>';
		$objPost['selectedIds'] = array(10554,10572,10575,10591,10594,10595,10596,10601,10602,10606,10608,10611,10613,10621,10622,10623,10630,10631,10634,10636,10640,10642,10643,10644,10646,10648,10650,10652,10654,10655,10661,10674,10676,10677,10678);
		//~ ,10678,10680,10682,10683,10684,10685,10687,10688,10689,10696,10699,10700,10702,10703,10717);	
		
		pr($objPost['selectedIds']); 
				
		$genDocs = array();
		$createDocument = array();
		$createErrorFile = array();
		$inputIdsForFinal = array();
		$saveIds = array();
		$resultReturnedArray = $this->createMultipleInputs($objPost['selectedIds'],'second');
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
		
		echo "</div>";
	}
	
	public function req_res() {
	echo '<div style="border:1px solid black;width:98%;float:left;"><H1>Testing with 35 invoices </H1><h2>Request is:</h2>';
		$objPost['selectedIds'] = array(10554,10572,10575,10591,10594,10595,10596,10601,10602,10606,10608,10611,10613,10621,10622,10623,10630,10631,10634,10636,10640,10642,10643,10644,10646,10648,10650,10652,10654,10655,10661,10674,10676,10677,10678,10680,10682,10683,10684,10685,10687,10688,10689,10696,10699,10700,10702,10703,10717);	
		
		$genDocs = array();
		$createDocument = array();
		$createErrorFile = array();
		$inputIdsForFinal = array();
		$saveIds = array();
		$resultReturnedArray = $this->createMultipleInputs($objPost['selectedIds'],'request');
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
		
		echo "</div>";
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
		echo "<h3>Correct response with confirmation code</h3>";
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
	 * Create  Multiple Inputs method
	 */
	 
	public function createMultipleInputs( $loadIds = array() , $parameter = '' ) {
		$i = 0;
		$postData = '';
		
		foreach( $loadIds as $loadId ) {
			$jobRecord = $this->Job->FetchSingleJobCreateInput($loadId);
			$pickupdate = date('Y-m-d',strtotime($jobRecord['PickDate']));
			$deliveryDate = ( $jobRecord['DeliveryDate'] != '' &&  $jobRecord['DeliveryDate'] != '0000-00-00' ) ?  $jobRecord['DeliveryDate'] : '';
			$postData .= "[$i].referenceKey=''&[$i].invoiceNumber={$jobRecord['invoiceNo']}&[$i].invoiceDate={$jobRecord['invoicedDate']}&[$i].referenceNumber={$jobRecord['woRefno']}&[$i].grossAmount={$jobRecord['PaymentAmount']}&[$i].isMiscInvoice=true&[$i].customerName=TEST-{$jobRecord['TruckCompanyName']}&[$i].customerId={$jobRecord['MCNumber']}&[$i].originCity={$jobRecord['OriginCity']}&[$i].originState={$jobRecord['OriginState']}&[$i].originZip={$jobRecord['OriginZip']}&[$i].originPickupDate={$pickupdate}&[$i].destinationCity={$jobRecord['DestinationCity']}&[$i].destinationState={$jobRecord['DestinationState']}&[$i].destinationZip={$jobRecord['DestinationZip']}&[$i].deliveryDate={$deliveryDate}&";

			$i++;
		}
		
		$postData = rtrim($postData,'&');
		if ( $parameter == 'request' )
			echo $postData;
			
		if ( $this->triumphToken != '' && $this->triumphToken != null ) {
			
		} else {
			$token = $this->get_sessionToken();
			$this->triumphToken = $token->SessionToken;
		}

		$url = 'v1Submit/CreateInputsFromArray';
		$result = $this->commonTriumphCurlRequest( $url, $postData, $this->triumphToken);
		if ( $parameter == 'second' ){
			
			echo "<h3>Incorrect Response with error</h3>";
			pr($result);
		}	else if ( $parameter == 'request' ) {
			echo "<h3>We have sent it to this url: ".$this->triumphUrlRequest.$url."</h3>";
			echo "<h3>Incorrect Response with error</h3>";
			pr($result);
		}
		
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
	 * Generating session token for triumph
	 */
	 
	public function get_sessionToken(){
       $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->triumphUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "username={$this->triuser}&password={$this->passwordTriumph}&apiKey={$this->apikey}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        $data = (json_decode($server_output));
        curl_close ($ch);
        return $data;
    }
	
	
}
