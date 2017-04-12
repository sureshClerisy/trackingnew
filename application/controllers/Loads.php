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
		if(isset($_REQUEST["requestFrom"]) && $_REQUEST["requestFrom"] == "billings"){
			$startDate = $endDate = "";
		}else{
			$startDate = date('Y-m-01');
			$endDate   = date("Y-m-d"); 	
		}
		
		
		if(isset($_REQUEST["startDate"]) && $_REQUEST["startDate"] != ""){ $startDate = date("Y-m-d", strtotime($_REQUEST["startDate"])); }
		if(isset($_REQUEST["endDate"]) && $_REQUEST["endDate"] != ""){ $endDate = date("Y-m-d", strtotime($_REQUEST["endDate"])); }

		if(!empty($urlArgs) && isset($_REQUEST["userType"]) && $urlArgs != "drivers"){
			$filterArgs = array("itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC","status"=>"");
			$filterArgs["status"] = (isset($_REQUEST["filterType"]) && $_REQUEST['filterType'] != 'reports') ? $_REQUEST["filterType"] : "";
			switch ($_REQUEST["userType"]) {
				case ''	  : 
				case 'all': 
							$gVehicleId = false;
							$this->data['table_title'] = "All Groups";
							$jobs = $this->getSingleVehicleLoads($this->userId,array(),"all",false,false,false,$startDate,$endDate,$filterArgs); 
							$this->data["total"] = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,false,$startDate,$endDate,$filterArgs); 
							$this->data['vehicleIdRepeat'] = '';
				break;
				case 'dispatcher': 
							$jobs = $this->getSingleVehicleLoads($this->userId,array(),"dispatcher", $_REQUEST["userToken"],false,false,$startDate,$endDate,$filterArgs); //Fetch Loads by vehicle id(s)
							$this->data["total"] = $this->Job->fetchSavedJobsTotal($this->userId,array(),"dispatcher",$_REQUEST["userToken"],false,false,$startDate,$endDate,$filterArgs); 
							$this->data['table_title'] =  "Dispatcher : ".$urlArgs;
							$this->data['vehicleIdRepeat'] = '';

				break;
				case 'team': 
				case 'driver':

							$driverInfo = $this->Driver->getInfoByDriverId($_REQUEST["userToken"]);
							if(isset($driverInfo[0])){ $driverInfo = $driverInfo[0]; }
							$gVehicleId = isset($driverInfo["vehicleId"])  ? $driverInfo["vehicleId"] : '';
							$dispatcherId = isset($driverInfo["dispatcherId"])  ? $driverInfo["dispatcherId"] : '';
							$secondDriverId = isset($_REQUEST['secondDriverId']) ? $_REQUEST['secondDriverId'] : ( isset($driverInfo["team_driver_id"])  ? $driverInfo["team_driver_id"] : '');
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
								$jobs = $this->getSingleVehicleLoads($tempUserId, $vehicleIdRepeat,"team", $dispatcherId, $_REQUEST["userToken"], $secondDriverId, $startDate,$endDate, $filterArgs);	
								$this->data["total"] = $this->Job->fetchSavedJobsTotal($tempUserId,$vehicleIdRepeat,"team",$dispatcherId,$_REQUEST["userToken"], $secondDriverId,$startDate,$endDate, $filterArgs); 

							} else{
								//pr($gDropdown);die;
								$jobs = $this->getSingleVehicleLoads($tempUserId, $vehicleIdRepeat, "driver", $dispatcherId, $_REQUEST["userToken"], $secondDriverId,$startDate,$endDate,$filterArgs);	
								$this->data["total"] = $this->Job->fetchSavedJobsTotal($this->userId,$vehicleIdRepeat,"driver",$dispatcherId,$_REQUEST["userToken"],$secondDriverId, $startDate,$endDate, $filterArgs); 
							}
				break;
				case 'broker':
					$filterArgs['dispatcherId']   = $_REQUEST['dispatcherId'];
					$filterArgs['driverId']       = $_REQUEST['driverId'];
					$filterArgs['secondDriverId'] = $_REQUEST['secondDriverId'];
					$jobs = $this->BrokersModel->getBrokerRelatedLoads($_REQUEST["userToken"], $startDate,$endDate, $filterArgs, false);	
					$this->data["total"] = $this->BrokersModel->getBrokerRelatedLoads($_REQUEST["userToken"], $startDate, $endDate, $filterArgs, true); 
					$this->data["total"] = $this->data["total"][0]['count'];
					$this->data['table_title'] = isset($jobs[0]['companyName']) ? $jobs[0]['companyName'] : '';
				break;

			}
		}else if(isset($urlArgs) && $urlArgs == "drivers"){
				$filters = $_REQUEST;
				$filters["itemsPerPage"] =20; 
				$filters["limitStart"] =1; 
				$filters["sortColumn"] ="DeliveryDate"; 
				$filters["sortType"] ="DESC"; 
				$filters["status"] =""; 
				if($filters["filterType"] == "idle" || $filters["filterType"] == "withoutTruck") {
					$jobs                = $this->Job->getIdleDriversLoads($filters,$filters["userType"]);
					$this->data["total"] = $this->Job->getIdleDriversLoads($filters,$filters["userType"],true);	
					$filters["cilckedEntity"] = preg_replace("/ {2,}/", " ", $filters["cilckedEntity"]);
					if($filters["userType"] == "team" || (isset($filters["secondDriverId"]) && $filters["secondDriverId"] != "")){ $filters["cilckedEntity"] = str_replace(" ", "+", $filters["cilckedEntity"]); }
					$this->data['table_title'] = $filters["cilckedEntity"];
				}else if($filters["filterType"] == "active"){
					$jobs = $this->Job->getActiveDrivers($filters,$filters["userType"],  $filters["fromDate"]);
					$this->data["total"] = $this->Job->getActiveDrivers($filters,$filters["userType"],  $filters["fromDate"],true);
					$this->data['table_title'] = "Active Drivers";
				}	
				$_REQUEST["requestFrom"] = 'capacity_analysis';
				
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

	
	
	
	public function getRecords(){
		
		$params = json_decode(file_get_contents('php://input'),true);
		$total 	= 0;
		$jobs 	= array();
		

		if($params["pageNo"] < 1){
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] + 1);	
		}else{
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] );	
		}

		if(isset($params["filterArgs"]["requestFrom"]) && $params["filterArgs"]["requestFrom"] == "billings"){
			$params["startDate"] = $params["endDate"] = "";
		}

		if((isset($params["sortColumn"]) && empty($params["sortColumn"])) || !isset($params["sortColumn"])){ $params["sortColumn"] = "DeliveryDate"; }
		if((isset($params["sortType"]) && empty($params["sortType"])) || !isset($params["sortType"])){ $params["sortType"] = "DESC"; }
		if(!isset($params["startDate"])){ $params["startDate"] = ''; }
		if(!isset($params["endDate"])){ $params["endDate"] = ''; }

		if(isset($params["filterArgs"]["filterType"])){
			$params["status"] = ($params['filterArgs']['filterType'] != 'reports' ) ? $params["filterArgs"]["filterType"] : '';
		}else{
			$params["status"] = "";
		}

		if(isset($params["filterArgs"]["requestFrom"]) && $params["filterArgs"]["requestFrom"] == "capacity_analysis"){

			if($params["filterArgs"]["filterType"] == "idle"){
				$params["driverId"] = $params["filterArgs"]["driverId"];
				$params["secondDriverId"] = $params["filterArgs"]["secondDriverId"];
				$jobs  = $this->Job->getIdleDriversLoads($params,$params["filterArgs"]["userType"]);
				$total = $this->Job->getIdleDriversLoads($params,$params["filterArgs"]["userType"],true);	
			}else if($params["filterArgs"]["filterType"] == "active"){
				$jobs = $this->Job->getActiveDrivers($params,$params["filterArgs"]["userType"],  $params["filterArgs"]["fromDate"]);
				$total = $this->Job->getActiveDrivers($params,$params["filterArgs"]["userType"],  $params["filterArgs"]["fromDate"],true);
			}	

		} else if (isset($params["filterArgs"]["userType"]) && $params["filterArgs"]["userType"] == "dispatcher") {  //A Dispatcher's All drivers
			$dispId = isset($params["filterArgs"]['userToken']) ? $params["filterArgs"]['userToken'] : false;
			$jobs = $this->getSingleVehicleLoads($this->userId,array(),"dispatcher", $dispId,false,false,$params["startDate"],$params["endDate"],$params);
			$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"dispatcher",$dispId,false,false,$params["startDate"],$params["endDate"],$params); 
		} else if (isset($params["filterArgs"]["userType"]) && ($params["filterArgs"]["userType"] == "all" || $params["filterArgs"]["userType"] == "" )){  
			$jobs = $this->getSingleVehicleLoads($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
			$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
		} else if (isset($params["filterArgs"]["userType"]) && ($params["filterArgs"]["userType"] == "driver" || $params["filterArgs"]["userType"] == "team" )){ 
			
			$driverInfo = $this->Driver->getInfoByDriverId($params["filterArgs"]["userToken"]);
			if(isset($driverInfo[0])){
				$driverInfo = $driverInfo[0];
			}
			$gVehicleId 		= isset($driverInfo["vehicleId"])  ? $driverInfo["vehicleId"] : '';
			$dispatcherId 		= isset($driverInfo["dispatcherId"])  ? $driverInfo["dispatcherId"] : '';
			$secondDriverId 	= isset($driverInfo["team_driver_id"])  ? $driverInfo["team_driver_id"] : '';
			$statesAddress 		= $this->Vehicle->get_vehicles_address($this->userId,$gVehicleId);
			$driverId 			= isset($params["filterArgs"]["userToken"]) ? $params["filterArgs"]["userToken"] : false ;
			$vehicleIdRepeat 	= $statesAddress[0]['id'];	
			$results 			= $this->Vehicle->getLastLoadRecord($statesAddress[0]['id'], $statesAddress[0]['driver_id']);

			if($params["filterArgs"]["userType"] == "team") {
				$jobs = $this->getSingleVehicleLoads($this->userId, $vehicleIdRepeat,"team", $dispatcherId, $driverId, $secondDriverId, $params["startDate"],$params["endDate"],$params);	
				$total = $this->Job->fetchSavedJobsTotal($this->userId, $vehicleIdRepeat,"team", $dispatcherId, $driverId, $secondDriverId, $params["startDate"],$params["endDate"],$params);	
			} else{
				$jobs = $this->getSingleVehicleLoads($this->userId, $vehicleIdRepeat, "driver", $dispatcherId, $driverId, $secondDriverId, $params["startDate"],$params["endDate"],$params);	
				$total = $this->Job->fetchSavedJobsTotal($this->userId, $vehicleIdRepeat,"driver", $dispatcherId, $driverId, $secondDriverId, $params["startDate"],$params["endDate"],$params);	
			}
		} else if (isset($params["filterArgs"]["userType"]) && $params["filterArgs"]["userType"] == "broker") {
			$params['dispatcherId']   = $params['filterArgs']['dispatcherId'];
			$params['driverId']       = $params['filterArgs']['driverId'];
			$params['secondDriverId'] = $params['filterArgs']['secondDriverId'];
			unset($params['filterArgs']['dispatcherId']);
			unset($params['filterArgs']['driverId']);
			unset($params['filterArgs']['secondDriverId']);
			
			$jobs = $this->BrokersModel->getBrokerRelatedLoads($params["filterArgs"]["userToken"], $params['startDate'], $params['endDate'], $params, false);	
			$this->data["total"] = $this->BrokersModel->getBrokerRelatedLoads($params["filterArgs"]["userToken"], $params['startDate'], $params['endDate'], $params, true); 
			$total = $this->data["total"][0]['count'];
		} else{

			$jobs = $this->getSingleVehicleLoads($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
			$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
		}

		if(!$jobs){$jobs = array();}

		//Export loads to excell file Start
		if(!empty($params['export'])){
			$keys 	= [['DATE','CUSTOMER NAME','DRIVERS','INVOICE','CHARGES','PROFIT','%PROFIT','MILES','DEAD MILES','RATE/MILE','DATE P/U','PICK UP','DATE DE','DELIVERY','LOLAD ID','STATUS']];
			$todayReport = $this->buildExportLoadData($jobs);
			$data = array_merge($keys,$todayReport);
			echo json_encode(array('fileName'=>$this->createExcell('loads',$data)));die();
		}
		//Export loads to excell file End

		echo json_encode(array("data"=>$jobs,"total"=>$total));
	}

	/**
	* Method driversInsights
	* @param GET Request
	* @return JSON
	* Getting list of drivers with requested params
	*/
	public function driversInsights($urlArgs = '') 
	{
		$filters = $_REQUEST;
		$filters["itemsPerPage"] =20; 
		$filters["limitStart"] =1; 
		$filters["sortColumn"] ="DeliveryDate"; 
		$filters["sortType"] ="DESC"; 
		$filters["status"] =""; 
		$filters["requestFrom"] = 'capacity_analysis';

		if(isset($filters["filterType"]) && $filters["filterType"] == "trucksWithoutDriver"){
			$response["driversList"] = $this->Driver->fetchTrucksWithoutDriver($filters);
			$response["total"] = $this->Driver->fetchTrucksWithoutDriver($filters,true);
		}else if(isset($filters["filterType"]) && $filters["filterType"] == "withoutTruck"){
			$response["driversList"] = $this->Driver->fetchDriversWithoutTruck($filters);
			$response["total"] = $this->Driver->fetchDriversWithoutTruck($filters,true);

		}else if(isset($filters["filterType"]) && $filters["filterType"] == "trucksReporting"){
			$response["driversList"] = $this->Driver->trucksReporting($filters);
			$response["total"] = $this->Driver->trucksReporting($filters,true);
		}else{
			$response["driversList"] = $this->Job->getIdleDrivers($filters,$filters["userType"],'',$filters["fromDate"]);	
		}
		
		$response['filterArgs'] = $filters;
		$response['filterArgs']["firstParam"] = $urlArgs;
		echo json_encode($response);
	}
	public function getDriversInsightsRecords(){
		$params = json_decode(file_get_contents('php://input'),true);

		$total = 0;
		$jobs = array();
		if($params["pageNo"] < 1){
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] + 1);	
		}else{
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] );	
		}

		if((isset($params["sortColumn"]) && empty($params["sortColumn"])) || !isset($params["sortColumn"])){ $params["sortColumn"] = "DeliveryDate"; }
		if((isset($params["sortType"]) && empty($params["sortType"])) || !isset($params["sortType"])){ $params["sortType"] = "ASC"; }


		if(isset($params["filterArgs"]["filterType"]) && $params["filterArgs"]["filterType"] == "withoutTruck"){
			$jobs  = $this->Driver->fetchDriversWithoutTruck($params);
			$total = $this->Driver->fetchDriversWithoutTruck($params,true);
		}if(isset($params["filterArgs"]["filterType"]) && $params["filterArgs"]["filterType"] == "trucksReporting"){
			$jobs  = $this->Driver->trucksReporting($params);
			$total = $this->Driver->trucksReporting($params,true);
		}else if(isset($params["filterArgs"]["requestFrom"]) && $params["filterArgs"]["requestFrom"] == "capacity_analysis"){
			if($params["filterArgs"]["filterType"] == "idle"){
				if($params["filterArgs"]["userType"] != "all"){
					$params["userToken"] = $params["filterArgs"]["userToken"];	
					$params["dispId"] = $params["filterArgs"]["dispId"];
				}
				
				if($params["filterArgs"]["userType"] == "team" || $params["filterArgs"]["userType"] == "iteam"){
					$params["secondDriverId"] = $params["filterArgs"]["secondDriverId"];
				}

				$jobs  = $this->Job->getDriversWithFilter($params,$params["filterArgs"]["userType"],'' ,$params['filterArgs']["fromDate"]);
				$total = $this->Job->getDriversWithFilter($params,$params["filterArgs"]["userType"],'idle',$params['filterArgs']["fromDate"],true);	
			}
		} 
		
		//Export data to excell file START
		if(!empty($params["export"])){
			$this->exportData($jobs);//Exporting Data here. Terminate script also
		}
		//Export data to excell file END

		if(!$jobs){$jobs = array();}

		echo json_encode(array("data"=>$jobs,"total"=>$total));
	}

	/**
	* Method exportData
	* @param GET Request
	* @return NULL
	*/
	public function exportData($dataArray,$keys=NULL){
		
		$defaultKeys=[['DRIVER NAME','TRUCK','DISPATCHER']];

		$exportData =[];
		if(!$keys){
			foreach ($dataArray as $key => $value) {
				$exportData[$key]['driverName'] = $value['driverName'];
				$exportData[$key]['truckName']  = $value['truckName'];
				$exportData[$key]['dispatcher'] = $value['dispatcher'];
			}
		}else{
			$defaultKeys = $keys;
			foreach ($dataArray as $key => $value) {
				$exportData[$key]['vehicleId'] 	= $value['vehicleId']	;
				$exportData[$key]['vin']  		= $value['vin'];		
				$exportData[$key]['model'] 		= $value['model'];
			}
		}

		$data 	= array_merge($defaultKeys,$exportData);
		echo json_encode(array('fileName'=>$this->createExcell('insights',$data)));die();
	}

	/**
	* Method truckInsights
	* @param GET Request
	* @return JSON
	* Getting list of trucks with requested params
	*/
	public function truckInsights($urlArgs = '') 
	{
		$filters = $_REQUEST;
		$filters["itemsPerPage"] =20; 
		$filters["limitStart"] =1; 
		$filters["sortColumn"] ="DeliveryDate"; 
		$filters["sortType"] ="ASC"; 
		$filters["status"] =""; 
		$filters["requestFrom"] = 'capacity_analysis';

		if(isset($filters["filterType"]) && $filters["filterType"] == "trucksWithoutDriver"){
			$response["trucksList"] = $this->Driver->fetchTrucksWithoutDriver($filters);
			$response["total"] = $this->Driver->fetchTrucksWithoutDriver($filters,true);
		}else if(isset($filters["filterType"]) && $filters["filterType"] == "trucksReporting"){
			$response["trucksList"] = $this->Driver->trucksReporting($filters);
			$response["total"] = $this->Driver->trucksReporting($filters,true);
		}
		
		$response['filterArgs'] = $filters;
		$response['filterArgs']["firstParam"] = $urlArgs;
		echo json_encode($response);
	}


	/**
	* Method getTruckInsightsRecords
	* @param POST Request
	* @return JSON
	* Getting list of trucks with requested params
	*/
	public function getTruckInsightsRecords()
	{
		$params = json_decode(file_get_contents('php://input'),true);
		
		$total = 0;
		$jobs = array();

		if($params["pageNo"] < 1){
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] + 1);	
		}else{
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] );	
		}

		if((isset($params["sortColumn"]) && empty($params["sortColumn"])) || !isset($params["sortColumn"])){ 
			$params["sortColumn"] = "DeliveryDate"; 
		}

		if((isset($params["sortType"]) && empty($params["sortType"])) || !isset($params["sortType"])){ 
			$params["sortType"] = "ASC"; 
		}

		if(isset($params["filterArgs"]["filterType"]) && $params["filterArgs"]["filterType"] == "trucksWithoutDriver"){
			$jobs  	= $this->Driver->fetchTrucksWithoutDriver($params);
			$total 	= $this->Driver->fetchTrucksWithoutDriver($params,true);
			$keys 	= [['TRUCK NUMBER','VIN','MODEL']];
			if(!empty($params["export"])){$this->exportData($jobs,$keys);}
		}

		if(isset($params["filterArgs"]["filterType"]) && $params["filterArgs"]["filterType"] == "trucksReporting"){
			$jobs  = $this->Driver->trucksReporting($params);
			$total = $this->Driver->trucksReporting($params,true);

			if(!empty($params["export"])){$this->exportData($jobs);}
		}
		

		if(!$jobs){$jobs = array();}

		echo json_encode(array("data"=>$jobs,"total"=>$total));
	}
}



