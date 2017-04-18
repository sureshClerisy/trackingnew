<?php

class Investors extends Admin_Controller {

	function __construct(){
		parent::__construct();	
		$this->load->library('user_agent');
		$this->load->model(array('Investor'));
		$this->load->helper(array('truckstop'));
		$this->roleId   = $this->session->role;	
		$this->userName = $this->session->loggedUser_username;
		if($this->session->role != 1){
			$this->userId = $this->session->loggedUser_id;
		}
	}


	/**
	* request : http://domain/investors/fetchVehiclesList
	* method  : POST
	* @param  : null
	* @return : array
	* comment : fetch vehicles listing assigned to user
	**/

	public function index(){

	}

	/**
	* request: http://domain/investors/fetchVehiclesList
	* method : POST
	* @param : null
	* @return: list array
	* comment: fetch vehicles listing assigned to user
	**/

	public function fetchVehiclesList(){
		$vehicles = $this->Investor->fetchVehiclesList($this->userId);
		array_unshift($vehicles, array("id"=>"all","vehicleName"=>"All Vehicles"));
		echo json_encode(array("list"=>$vehicles));
	}

	public function getPortletsData(){
		$vehicleId = "";
		if(isset($_POST["vehicle_id"]) && !empty($_POST["vehicle_id"])){
			$vehicleId = $_POST["vehicle_id"];
 		}
		$mapList = $this->getTrucksLocation($vehicleId);
		echo json_encode(array("mapList"=>$mapList));
	}



	/**
	* request: http://domain/investors/getTrucksLocation
	* method : POST
	* @param : null
	* @return: list array
	* comment: fetch vehicles listing with live location
	**/
	public function getTrucksLocation($vehicleId){
		$allVehicles = $this->Investor->vehiclesWithFilter($this->userId,$vehicleId);
		foreach ($allVehicles as $key => $value) {
			if(empty($value['latitude']) || empty($value['tracker_id']) /* || empty($allVehicles[$key]["loadDetail"])*/){
				unset($allVehicles[$key]);
				continue;
			}else{
				$tHeading = unserialize($value["telemetry"]);
				$headingType = (isset($tHeading["heading"]) && $tHeading["heading"] != "") ? floor($tHeading["heading"]/45) : "_EMPTY" ;
				switch ($headingType) {
					case "_EMPTY": $allVehicles[$key]["heading"] = "EMPTY";break;
					case 0: $allVehicles[$key]["heading"] = "N"; break;
					case 1: $allVehicles[$key]["heading"] = "NE"; break;
					case 2: $allVehicles[$key]["heading"] = "E"; break;
					case 3: $allVehicles[$key]["heading"] = "SE"; break;
					case 4: $allVehicles[$key]["heading"] = "S"; break;
					case 5: $allVehicles[$key]["heading"] = "SW"; break;
					case 6: $allVehicles[$key]["heading"] = "W"; break;
					case 7: $allVehicles[$key]["heading"] = "NW"; break;
				}
				$allVehicles[$key]["timestamp"] = toLocalTimezone($value["timestamp"]);
			}
		}
		$allVehicles =  array_values($allVehicles);
		return $allVehicles;
	} 




}
