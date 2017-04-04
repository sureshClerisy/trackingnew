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

		$startDate = date('Y-m-01');
		$endDate = date("Y-m-d"); 
		
		if(isset($_REQUEST["startDate"]) && $_REQUEST["startDate"] != ""){ $startDate = date("Y-m-d", strtotime($_REQUEST["startDate"])); }
		if(isset($_REQUEST["endDate"]) && $_REQUEST["endDate"] != ""){ $endDate = date("Y-m-d", strtotime($_REQUEST["endDate"])); }
	
		if(!empty($urlArgs) && isset($_REQUEST["userType"])){
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
	
	public function getRecords(){
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

		if(isset($params["filterArgs"]["filterType"])){
			$params["status"] = ($params['filterArgs']['filterType'] != 'reports' ) ? $params["filterArgs"]["filterType"] : '';
		}else{
			$params["status"] = "";
		}

		//if(isset($_COOKIE["_globalDropdown"]) && !empty($_COOKIE["_globalDropdown"])){
			//$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
			if (isset($params["filterArgs"]["userType"]) && $params["filterArgs"]["userType"] == "dispatcher") {  //A Dispatcher's All drivers
				$dispId = isset($params["filterArgs"]['userToken']) ? $params["filterArgs"]['userToken'] : false;
				$jobs = $this->getSingleVehicleLoads($this->userId,array(),"dispatcher", $dispId,false,false,$params["startDate"],$params["endDate"],$params); //Fetch Loads by vehicle id(s)
				$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"dispatcher",$dispId,false,false,$params["startDate"],$params["endDate"],$params); 
			}else if (isset($params["filterArgs"]["userType"]) && ($params["filterArgs"]["userType"] == "all" || $params["filterArgs"]["userType"] == "" )){  //A Dispatcher's All drivers
				$jobs = $this->getSingleVehicleLoads($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
				$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
			}else if (isset($params["filterArgs"]["userType"]) && ($params["filterArgs"]["userType"] == "driver" || $params["filterArgs"]["userType"] == "team" )){ 
				$driverInfo = $this->Driver->getInfoByDriverId($params["filterArgs"]["userToken"]);
				//pr($driverInfo);die;
				if(isset($driverInfo[0])){ $driverInfo = $driverInfo[0]; }
				$gVehicleId = isset($driverInfo["vehicleId"])  ? $driverInfo["vehicleId"] : '';
				$dispatcherId = isset($driverInfo["dispatcherId"])  ? $driverInfo["dispatcherId"] : '';
				$secondDriverId = isset($driverInfo["team_driver_id"])  ? $driverInfo["team_driver_id"] : '';
				$statesAddress = $this->Vehicle->get_vehicles_address($this->userId,$gVehicleId);
				$driverId = isset($params["filterArgs"]["userToken"]) ? $params["filterArgs"]["userToken"] : false ;
				$vehicleIdRepeat = $statesAddress[0]['id'];	
				$results = $this->Vehicle->getLastLoadRecord($statesAddress[0]['id'], $statesAddress[0]['driver_id']);

				if($params["filterArgs"]["userType"] == "team") {
					$jobs = $this->getSingleVehicleLoads($this->userId, $vehicleIdRepeat,"team", $dispatcherId, $driverId, $secondDriverId, $params["startDate"],$params["endDate"],$params);	
					$total = $this->Job->fetchSavedJobsTotal($this->userId, $vehicleIdRepeat,"team", $dispatcherId, $driverId, $secondDriverId, $params["startDate"],$params["endDate"],$params);	
				} else{
					$jobs = $this->getSingleVehicleLoads($this->userId, $vehicleIdRepeat, "driver", $dispatcherId, $driverId, $secondDriverId, $params["startDate"],$params["endDate"],$params);	
					$total = $this->Job->fetchSavedJobsTotal($this->userId, $vehicleIdRepeat,"driver", $dispatcherId, $driverId, $secondDriverId, $params["startDate"],$params["endDate"],$params);	
				}
			}else{
				$jobs = $this->getSingleVehicleLoads($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
				$total = $this->Job->fetchSavedJobsTotal($this->userId,array(),"all",false,false,false,$params["startDate"],$params["endDate"],$params); 
			}
		
		if(!$jobs){$jobs = array();}

		echo json_encode(array("data"=>$jobs,"total"=>$total));
	}
}



