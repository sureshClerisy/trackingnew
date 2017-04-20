<?php

class Investors extends Admin_Controller {
	public $vehicles;
	public $lastWeekStartDay;
	public $lastWeekEndDay;
	public $thisWeekStartDay;
	public $thisWeekLastDay;
	public $thisWeekToday;
	public $yesterday;
	public $specifiedVehicles;

	function __construct(){
		parent::__construct();	
		$this->load->library('user_agent');
		$this->load->model(array('Investor'));
		$this->load->helper(array('truckstop'));

		$this->roleId   = $this->session->role;	
		$this->userName = $this->session->loggedUser_username;
		$this->userId   = $this->session->loggedUser_id;
		$this->vehicles = $this->Investor->fetchVehiclesList($this->userId);
		$this->lastWeekStartDay   = date("Y-m-d", strtotime('monday last week'));
		$this->lastWeekEndDay     = date("Y-m-d", strtotime('sunday last week'));
		$this->thisWeekStartDay   = date("Y-m-d", strtotime('monday this week'));
		$this->thisWeekLastDay    = date("Y-m-d", strtotime('sunday this week'));
		$this->thisWeekToday      = date("Y-m-d");
		$this->yesterday          = date("Y-m-d", strtotime("yesterday"));
		$this->specifiedVehicles  = array_column($this->vehicles, 'id');
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
		$vehicles = $this->vehicles;
		array_unshift($vehicles, array("id"=>"all","vehicleName"=>"All Vehicles"));
		$rssFeeds = $this->getRssFeeds();
		echo json_encode(array("rssFeeds"=> $rssFeeds, "list"=>$vehicles));
	}


	/**
	* request: http://domain/investors/getPortletsData
	* method : POST
	* @param : null
	* @return: list array
	* comment: fetch data for all portlets on investor dashboard
	**/
	public function getPortletsData(){
		$vehicleId          = ""; $vehiclesJobs = $filters = array();
		if(isset($_COOKIE["_gDateRangeInvDash"])){
			$filters = json_decode($_COOKIE["_gDateRangeInvDash"],true);
			if(!empty($filters["startDate"])){
				$this->lastWeekStartDay   = $filters["startDate"];
				$this->lastWeekEndDay     = $filters["endDate"];	
			}
		}
		if(isset($_POST["vehicle_id"]) && !empty($_POST["vehicle_id"]) && $_POST["vehicle_id"] != "all" ){
			$vehicleId = $_POST["vehicle_id"];
			$this->specifiedVehicles = array($vehicleId);
 		}

 		$lastWeek["sale"]  = $this->Investor->vehiclesRevenueWithFilter( $this->lastWeekStartDay, $this->lastWeekEndDay, $this->specifiedVehicles );
 		$lastWeek["sale"]  = !is_null($lastWeek["sale"]) ? $lastWeek["sale"] : 0;
 		
 		$thisWeek["sale"]  = $this->Investor->vehiclesRevenueWithFilter( $this->thisWeekStartDay, $this->thisWeekToday, $this->specifiedVehicles );
		$thisWeek["sale"]  = !is_null($thisWeek["sale"]) ? $thisWeek["sale"] : 0;
		
		$vehiclesWithoutDriver = $this->Investor->vehiclesWithoutDriver(array(), $this->specifiedVehicles , true);
		$mapList               = $this->getTrucksLocation( $vehicleId);
		$vehiclesJobs          = $this->getVehiclesJobs( $filters, $vehicleId );
		//$vehicles              = $this->getVehiclesJobs( $filters, $vehicleId, "traveling" );
		//pr($vehicles);die;
		echo json_encode(array("mapList"=>$mapList, "lastWeek" => $lastWeek, "thisWeek" => $thisWeek, "vehiclesWithoutDriver" => $vehiclesWithoutDriver, "vehiclesJobs" => $vehiclesJobs));
	}


	/**
	* request: http://domain/investors/getPortletsData
	* method : POST
	* @param : null
	* @return: list array
	* comment: fetch data for all portlets on investor dashboard
	**/
	public function getSpecificPortletData( $portlet ){
		$args = json_decode( file_get_contents("php://input"), true );
		$vehicleId = "";
		$response  = array();
		if(isset($_COOKIE["_gDateRangeInvDash"])){
			$filters = json_decode($_COOKIE["_gDateRangeInvDash"],true);
			if(!empty($filters["startDate"])){
				$this->lastWeekStartDay   = $filters["startDate"];
				$this->lastWeekEndDay     = $filters["endDate"];	
			}
		}
		if(isset($_POST["vehicle_id"]) && !empty($_POST["vehicle_id"]) && $_POST["vehicle_id"] != "all"){
			$vehicleId = $_POST["vehicle_id"];
			$this->specifiedVehicles = array($vehicleId);
 		}
		
		switch ( $portlet ) {
			case 'last_week_sale'            : $response["lastWeek"]["sale"]  = $this->Investor->vehiclesRevenueWithFilter( $this->lastWeekStartDay, $this->lastWeekEndDay, $this->specifiedVehicles );
 									           $response["lastWeek"]["sale"]  = !is_null($response["lastWeek"]["sale"]) ? $response["lastWeek"]["sale"] : 0;break;
			case 'this_week_till_today_sale' : $response["thisWeek"]["sale"]  = $this->Investor->vehiclesRevenueWithFilter( $this->thisWeekStartDay, $this->thisWeekToday, $this->specifiedVehicles );
									           $response["thisWeek"]["sale"]  = !is_null($response["thisWeek"]["sale"]) ? $response["thisWeek"]["sale"] : 0;break;
			case 'trucks_location'           : $response["mapList"] = $this->getTrucksLocation($vehicleId); break;
			case 'vechiles_without_driver'   : $response["vehiclesWithoutDriver"] = $this->Investor->vehiclesWithoutDriver(array(), $this->specifiedVehicles , true); break;
			case 'rss_feeds'                 : $response["rssFeeds"] = $this->getRssFeeds(); break;
			case 'vehicles_jobs'             : $response["vehiclesJobs"] =  $this->getVehiclesJobs( $vehicleId ); break;
		}
		echo json_encode($response);
	}



	/**
	* request: http://domain/investors/getRssFeeds
	* method : POST
	* @param : null
	* @return: list array
	* comment: fetch rss feeds from http://investors.ryder.com/rss/pressrelease.aspx
	**/
	public function getRssFeeds( ){
		$xml   = $this->config->item("investor_rss_feed_url");
		$feeds =  simplexml_load_file($xml);
		$feeds = json_decode( json_encode($feeds),true );
		$response   = array()  ;
		$response[] = array_slice($feeds["channel"]["item"], 0, 6); 
		$response[] = array_slice($feeds["channel"]["item"], 6, 4); 
		return $response;
	}


	/**
	* request: http://domain/investors/getVehiclesJobs
	* method : POST
	* @param : null
	* @return: array
	* comment: fetch all jobs done by vehicles
	**/
	public function getVehiclesJobs( $filters, $vehicleId = false, $purpose = "" ){
		if( $vehicleId ){
			$jobs = $this->Investor->getVehiclesJobs( $filters, array($vehicleId), $purpose );
		}else{ 
			$jobs = $this->Investor->getVehiclesJobs( $filters, $this->specifiedVehicles, $purpose );
		}
		return $jobs;
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
					case 0: $allVehicles[$key]["heading"] = "N" ; break;
					case 1: $allVehicles[$key]["heading"] = "NE"; break;
					case 2: $allVehicles[$key]["heading"] = "E" ; break;
					case 3: $allVehicles[$key]["heading"] = "SE"; break;
					case 4: $allVehicles[$key]["heading"] = "S" ; break;
					case 5: $allVehicles[$key]["heading"] = "SW"; break;
					case 6: $allVehicles[$key]["heading"] = "W" ; break;
					case 7: $allVehicles[$key]["heading"] = "NW"; break;
				}
				$allVehicles[$key]["timestamp"] = toLocalTimezone($value["timestamp"]);
			}
		}
		$allVehicles =  array_values($allVehicles);
		return $allVehicles;
	} 




}
