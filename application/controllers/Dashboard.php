<?php

ini_set('max_execution_time', 300); 

class Dashboard extends CI_Controller {

	public $userId;
	public $totalInvoices;
	public $totalMiles;
	public $totalDeadMiles;
	public $totalCharges;
	public $totalProfit;
	public $totalProfitPercent;
	public $overallTotalFinancialGoal;
	public $overallTotalMilesGoal;
	public $overallPlusMinusFinancialGoal;
	public $overallPlusMinusMilesGoal;
	public $teamFinancialGoal;
	public $teamMilesGoal;
	public $singleFinancialGoal;
	public $singleMilesGoal;

	function __construct() {
		parent::__construct();
		$this->load->library('Htmldom');
		$this->load->model('Vehicle');
		$this->load->model('Job');
		$this->load->model('Driver');
		$this->userId = $this->session->loggedUser_id;
		$this->userRoleId = $this->session->role;

		$this->totalInvoices	 				= 0;
		$this->totalMiles  	    				= 0;
		$this->totalDeadMiles 					= 0;
		$this->totalCharges 					= 0;
		$this->totalProfit 						= 0;
		$this->totalProfitPercent 				= 0;
		$this->overallTotalFinancialGoal		= 0;
		$this->overallTotalMilesGoal 			= 0;
		$this->overallPlusMinusFinancialGoal 	= 0;
		$this->overallPlusMinusMilesGoal 		= 0;
		$this->teamFinancialGoal 				= 0;
		$this->teamMilesGoal 					= 0;
		$this->singleFinancialGoal				= 0;
		$this->singleMilesGoal				 	= 0;

	}

	public function getTodayReport($type){
		$gDropdown = $vList = $rparam = array(); $lPerformance = '';
		$args = json_decode(file_get_contents('php://input'),true);
		if(isset($_COOKIE["_globalDropdown"])){
			$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
		}

		if(isset($args["did"]) && !empty($args["did"]) && isset($args["vtype"]) && $args["vtype"] == "_idispatcher"){
			$vList[] = $args["did"];
		}
		if(count($vList) <= 0){
			$vList = isset($args['vid']) && !empty(trim($args['vid'])) ? explode(',', $args['vid']) : false;	
		}
		
		if(isset($args['vtype']) && !empty(trim($args['vtype']))){
			$lPerformance = $args['vtype'];
		}else if(isset($gDropdown["label"])){
			if($gDropdown["label"] != "_iall" && $gDropdown["label"] != "_idispatcher" && $gDropdown["label"] != "_idriver" && $gDropdown["label"] != "_iteam" && $gDropdown["label"] != "_team" && $gDropdown["label"] != ""){
				$lPerformance = "_idriver";
			}else if($gDropdown["label"] == ""){
				$lPerformance = "_iall";
			} else if($gDropdown["label"] == "_team" || $gDropdown['label'] == '_iteam' ){ 
					$lPerformance = '_iteam';
			} else {
				$lPerformance = $gDropdown["label"];
			}
		}

		$rparam["selScope"] = $vList;
		$rparam["driverId"] = isset($gDropdown["id"]) ? $gDropdown["id"] : false; 
		$rparam["dispId"] = isset($gDropdown["dispId"]) ? $gDropdown["dispId"] : false;
		$rparam["secondDriverId"]   = isset($gDropdown["team_driver_id"]) ? $gDropdown["team_driver_id"] : false; 
		$totals = array("PaymentAmount"=>0, "Mileage"=>0, "deadmiles"=>0);
		if($type == "idle"){
			$todayReport = $this->Job->getIdleDrivers($rparam,$lPerformance);
		}else{
			$todayReport = $this->Job->getTodayReport($rparam,$lPerformance,$type);	
			foreach ($todayReport as $key => $value) {
				$totals["PaymentAmount"] += $value["PaymentAmount"];
				$totals["Mileage"]       += $value["Mileage"];
				$totals["deadmiles"]     += $value["deadmiles"];
			}
		}
		echo json_encode(array("success"=>true,"todayReport"=>$todayReport,"totals"=>$totals));
	}


	public function index($vehicleID = false){

		$latitude 	= "";
		$longitude 	= "";
		$curAddress = "";
		$gDropdown 	= array();$rparam = array();
		$lPerformance = '';
		$args = json_decode(file_get_contents('php://input'),true);

		if(isset($_COOKIE["_globalDropdown"])){
			$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
		}
		
		if($vehicleID){
			$vList 			= array($vehicleID);
			$lPerformance 	= "_idriver"; 
			$skip 			= false;
			if(isset($args["vid"]) && !empty($args["vid"])){
				$vList 						= array($args["vid"]);
				$lPerformance 				= $args["vtype"];
				$rparam["driverId"] 		= $args["driverId"];
				$rparam["dispId"] 			= $args["did"];
				$rparam["secondDriverId"]   = $args["team_driver_id"];
				$skip 						= true;

			}else {
				$rparam["driverId"] 		= isset($gDropdown["id"]) ? $gDropdown["id"] : false; 
				$rparam["dispId"] 			= isset($gDropdown["dispId"]) ? $gDropdown["dispId"] : false;
				$rparam["secondDriverId"]   = isset($gDropdown["team_driver_id"]) ? $gDropdown["team_driver_id"] : false; 
			}


			if( !$skip && isset($gDropdown["label"]) && ($gDropdown["label"] == "team" || $gDropdown["label"] == "_team") ){
				$lPerformance =  "_iteam" ;
				$vList = array($gDropdown["vid"]);	
			}

		}else{
			
			$vList = array();
			if(isset($args["did"]) && !empty($args["did"]) && isset($args["vtype"]) && $args["vtype"] == "_idispatcher"){
				$vList[] = $args["did"];
				$drivers = $this->Driver->getDriversList($args["did"],false,true);
				foreach ($drivers as $key => $value) {
					$vehicleID = $value["vid"];break;
				}
			}else{
				$vehicleID = isset($args['vid']) && !empty(trim($args['vid'])) ? $args['vid'] : end($vList);	
			}

			if(count($vList) <= 0){
				$vList = isset($args['vid']) && !empty(trim($args['vid'])) ? explode(',', $args['vid']) : false;	
			}
	
			if(isset($args['vtype']) && !empty(trim($args['vtype']))){
				$lPerformance = 	$args['vtype'];
			}else if(isset($gDropdown["label"])){
				
				if($gDropdown["label"] != "_iall" && $gDropdown["label"] != "_idispatcher" && $gDropdown["label"] != "_idriver" && $gDropdown["label"] != "_iteam" && $gDropdown["label"] != "_team" && $gDropdown["label"] != ""){
					$lPerformance = "_idriver";
				} else if($gDropdown["label"] == ""){
					$lPerformance = "_iall";
				} else if($gDropdown["label"] == "_team" || $gDropdown['label'] == '_iteam' ){ 
					$lPerformance = '_iteam';
				} else {
					$lPerformance = $gDropdown["label"];
				}
			}

			$rparam["driverId"] 		= isset($gDropdown["id"]) ? $gDropdown["id"] : false; 
			$rparam["dispId"] 			= isset($gDropdown["dispId"]) ? $gDropdown["dispId"] : false;
			$rparam["secondDriverId"]   = isset($gDropdown["team_driver_id"]) ? $gDropdown["team_driver_id"] : false; 
		}


		if(isset($args["startDate"]) && $args['startDate'] != '') 
			$rparam["startDate"] = $args["startDate"]; 
		else 
			$rparam["startDate"] = date('Y-m-01'); 
		
		if(isset($args["endDate"]) && $args['endDate'] != '') 
			$rparam["endDate"] = $args["endDate"]; 
		else 
			$rparam["endDate"] = date('Y-m-d');

		$rparam["selScope"] 		  = $vList;
		
		$chartStack = $this->fetchDashboardData( $rparam, $lPerformance);
	 		
		$vehicleInfo = $weatherNotFound = array();
		$lat = $lng = '';

		$vehicleLocation 	= $this->getTrucksLocation($rparam,$lPerformance);
		$loadsSummary 		= $this->Job->fetchLoadsSummary(null, $rparam,$lPerformance);
		$loadsChart['delivered'] = $loadsChart['assigned'] = $loadsChart['booked'] = $loadsChart['inprogress'] = 0;
		$gotStatus = 0;
		if(isset($loadsSummary[0])){
			foreach ($loadsSummary as $key => $value) {

				if(empty(trim($value['JobStatus'])) )
					continue;
				else
					$gotStatus = 1;
				$loadsChart[$value['JobStatus']] = (int)$value['tnum'];
			}
		}

		if(!$gotStatus){
			$loadsChart['noLoads'] = 1;
		}else{
			$loadsChart['noLoads'] = 0;
		}
			
		//--------------- Job Status ----------------

		$loadsChart["summary"]["invoiceCount"] 			= $this->Job->getInvoiceCount($rparam,$lPerformance);
		$loadsChart["summary"]["waitingPaperworkCount"] = $this->Job->getWaitingPaperworkCount($rparam,$lPerformance);
		$loadsChart["summary"]["sentForPaymentCount"] 	= $this->Job->getSentForPaymentCount($rparam,$lPerformance);
		$loadsChart["summary"]["paymentNotRecievedCount"] = 0;
		$todayReport = $this->Job->getTodayReport($rparam,$lPerformance, "booked");

		$capcityDaysStart = date("Y-m-d");
		$driversIdleVsActive = array();
		for ($caIndex=0; $caIndex < 7 ; $caIndex++) { 
			$driversIdleVsActive["xaxis"][] = date("l",strtotime($capcityDaysStart));
			$driversIdleVsActive["active"][]   = array("y"=>(int)$this->Job->getTodayReport($rparam,$lPerformance, "exceptIdle", $capcityDaysStart),"date"=> $capcityDaysStart,"type"=>"active");
			$driversIdleVsActive["idle"][] = array("y"=>(int)$this->Job->getIdleDrivers($rparam,$lPerformance,'idle',$capcityDaysStart),"date"=> $capcityDaysStart,"type"=>"idle");
			$capcityDaysStart = date("Y-m-d", strtotime("+1 days", strtotime($capcityDaysStart)));
		}

		$totals = array("PaymentAmount"=>0, "Mileage"=>0, "deadmiles"=>0);
		foreach ($todayReport as $key => $value) {
			$totals["PaymentAmount"] += $value["PaymentAmount"];
			$totals["Mileage"]       += $value["Mileage"];
			$totals["deadmiles"]     += $value["deadmiles"];
		}
		$userId = false;
		$parentId = false;
		if($this->userRoleId == _DISPATCHER){
			$userId = $this->userId;
		}  else if ( $this->userRoleId == 4 ) {
			$parentIdCheck = $this->session->userdata('loggedUser_parentId');
			if( isset($parentIdCheck) && $parentIdCheck != 0 ) {
				$userId = $parentIdCheck;
				$parentId = $parentIdCheck;
			}
		}
		
		$allVehicles = array();
		if($vehicleID){
			if($lPerformance == "_iteam"){
				$vehicleInfo = $this->Vehicle->get_vehicle_address($vehicleID,false,'team');	
				$vehicleInfo["dtype"] = "_team";
			}else{
				$vehicleInfo = $this->Vehicle->get_vehicle_address($vehicleID,false);
			}
			
			$vehicleLabel = $vehicleInfo['driverName'];
			$vehicleInfo['vehicle_address'] = urlencode($vehicleInfo['vehicle_address'].','.$vehicleInfo['city'].', '.$vehicleInfo['state']);
			$weatherNotFound['country'] = 'US';
			$weatherNotFound['name'] = $vehicleInfo['city'];
			$lat = $vehicleInfo['latitude'];
			$lng = $vehicleInfo['longitude'];
		}else{ 
			$allVehicles = $this->Driver->getDriversList($userId,true,true);
			foreach ($allVehicles as $key => $value) {
				if(!empty($value['latitude']) && !empty($value['longitude'])){
					$vehicleInfo['vehicle_address'] = urlencode($value['vehicle_address'].','.$value['city'].', '.$value['state']);
					$vehicleID 		= $value['vid'];
					$vehicleLabel 	= $value['driverName'];
					$lat 			= $value['latitude'];
					$lng 			= $value['longitude'];
					$latitude 		= $lat;
					$longitude 		= $lng;
					$curAddress 	= $vehicleInfo['vehicle_address'];
					$weatherNotFound['country'] = 'US';
					$weatherNotFound['name'] 	= $value['city'];	
					break;
				}
			}
		}

		if(count($allVehicles) <= 0)
			$allVehicles = $this->Driver->getDriversList($userId,true,true);
		
		$teamList 		= $this->Driver->getDriversListAsTeam($userId,true);
		$dispatcherList = $this->Driver->getDispatcherList($userId);
		
		$vehicleList 	= array();
		if(!empty($allVehicles) && is_array($allVehicles)){	
			$vehicleList = $allVehicles;
			if(is_array($teamList) && count($teamList) > 0){
				foreach ($teamList as $key => $value) {
					$value["label"] = "_team";
					array_unshift($vehicleList, $value);
				}
			}
			foreach ($dispatcherList as $key => $value) {
				array_unshift($vehicleList, $value);
			}

			if($this->userRoleId != _DISPATCHER && $this->userRoleId != 4){
				$new = array("id"=>"","profile_image"=>"","driverName"=>"All Groups","label"=>"","username"=>"","latitude"=>"","longitude"=>"","vid"=>"","city"=>"","vehicle_address"=>"","state"=>"");
				array_unshift($vehicleList, $new);
			}
		}

		$weatherNotFound['status'] = false;
		$latitude = $lat;
		$longitude = $lng;
		$curAddress = $vehicleInfo['vehicle_address'];


		/**
		* Getting avatar text and dynamic color of selected driver
		* Check if driver selected from driver list page if yes then priroty to it otherwise to cookie data
		*/
		
		$driverId = (!empty($rparam['driverId'])) ? $rparam['driverId'] : (isset($gDropdown['id']) ? $gDropdown["id"] : ""); 

		if(!empty($driverId)){
			
			$avatraInfo = $this->createAvatarText($driverId);
			$gDropdown['avtarText'] = $avatraInfo['text'];
			$gDropdown['color'] 	= $avatraInfo['color'];
		}

		echo json_encode(array('longitude'=>$longitude, 'latitude' =>$latitude , 'address' => $curAddress,  "vehicleList"=>$vehicleList,'vehicleID'=>$vehicleID,'vehicleLabel'=>$vehicleLabel, 'weatherNotFound'=>$weatherNotFound,"loadsChart"=>$loadsChart,"vehicleLocation"=>$vehicleLocation,'selectedDriver'=>$gDropdown, "chartStack"=>$chartStack, "todayReport"=>$todayReport, "totals"=>$totals, "driversIdleVsActive"=>$driversIdleVsActive, 'success'=>true));    

	}

	/**
	* method createAvatarText
	* @param Data
	* @return Avtar Text
	*/
	private function createAvatarText($driverId){
		
		$avatraInfo = '';
		$columns 				= ['color','first_name','last_name'];
		$data 					= $this->Job->getColumns($driverId,$columns,'drivers');;
		$avatraInfo['color'] 	= $data[0]['color'];
		$avatraInfo['text'] 	= $data[0]['first_name'][0].$data[0]['last_name'][0];

		return $avatraInfo;
	}

	public function fetchWidgetsOrder(){

		$widgetOrd 		= array();
		$widgetsOrder 	= $this->Job->getWidgetsOrder($this->userId);
		$widgetVisibility = $this->Job->getPortletVisibility();

		if ( !empty($widgetsOrder) ) {
			$widgetsOrder 		= json_decode($widgetsOrder['widget_order'],true);
			$widgetOrd['left'] 	= implode(',', $widgetsOrder['left']);
			$widgetOrd['right'] = implode(',', $widgetsOrder['right']);
		}

		$userDrivers = array();$vehicleInfo = array();
		if($this->userRoleId == _DISPATCHER || $this->userRoleId == 4 ){
			if( $this->session->userdata('loggedUser_parentId') != 0 )
				$userId = $this->session->userdata('loggedUser_parentId');
			else
				$userId = $this->userId;

			$result = $this->Driver->getDriversList($userId,false,true);
			if(!empty($result)){
				foreach ($result as $key => $value) {
					array_push($userDrivers, $value["vid"]);
				}
			}
			$vehicleInfo = $this->Driver->getDispatcherList($userId);
			if(isset($vehicleInfo[0])){
				$vehicleInfo = $vehicleInfo[0];
			}
		}
		echo json_encode(array('widgetsOrder'=>$widgetOrd,"selectedDriver"=>$vehicleInfo,"selDrivers"=>$userDrivers,"user_role"=>$this->userRoleId,'widgetVisibility'=>$widgetVisibility));
	}	

	/**
	* Method widgetVisibility
	* @param widget ID,visibility(0 OR 1)
	* @return NULL
	* 
	*/
	public function widgetVisibility(){
		
		$args = json_decode(file_get_contents('php://input'),true);
		$this->Job->widgetsVisibility($args);
		$visibility = $this->Job->getPortletVisibility();
		echo json_encode(array("visibility"=>$visibility,'success'=>true));
	}

	/**
	* Method widgetVisibility
	* @param widget ID,visibility(0 OR 1)
	* @return NULL
	* 
	*/
	public function getPortletVisibility(){
		$visibility = $this->Job->getPortletVisibility();
		echo json_encode(array("visibility"=>$visibility,'success'=>true));
	}

	
	public function updateDashboardOnLoadEdit(){
		$gDropdown = array();$rparam = array();
		$lPerformance = '';
		$args = json_decode(file_get_contents('php://input'),true);
		
		if(isset($_COOKIE["_globalDropdown"])){
			$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
		}
		
		$vList = array();
		if(isset($args["did"]) && !empty($args["did"]) && isset($args["vtype"]) && $args["vtype"] == "_idispatcher"){
			$vList[] = $args["did"];
			$drivers = $this->Driver->getDriversList($args["did"],false,true);
			foreach ($drivers as $key => $value) {
				$vehicleID = $value["vid"];break;
			}
		}else{
			$vehicleID = isset($args['vid']) && !empty(trim($args['vid'])) ? end($vList) : false;	
		}

		if(count($vList) <= 0){
			$vList = isset($args['vid']) && !empty(trim($args['vid'])) ? explode(',', $args['vid']) : false;	
		}
		
		if(isset($args['vtype']) && !empty(trim($args['vtype']))){
			$lPerformance = 	$args['vtype'];
		}else if(isset($gDropdown["label"])){
			if($gDropdown["label"] != "_iall" && $gDropdown["label"] != "_idispatcher" && $gDropdown["label"] != "_idriver" && $gDropdown["label"] != "_iteam" && $gDropdown["label"] != "_team" && $gDropdown["label"] != ""){
				$lPerformance = "_idriver";
			} else if($gDropdown["label"] == ""){
				$lPerformance = "_iall";
			} else if($gDropdown["label"] == "_team" || $gDropdown['label'] == '_iteam' ){ 
				$lPerformance = '_iteam';
			} else {
					$lPerformance = $gDropdown["label"];
			}
		}

		if(isset($args["startDate"]) && $args['startDate'] != '') 
			$rparam["startDate"] = $args["startDate"]; 
		else 
			$rparam["startDate"] = date('Y-m-01'); 
		
		if(isset($args["endDate"]) && $args['endDate'] != '') 
			$rparam["endDate"] = $args["endDate"]; 
		else 
			$rparam["endDate"] = date('Y-m-d');

		$rparam["selScope"] = $vList;
		$rparam["driverId"] = isset($gDropdown["id"]) ? $gDropdown["id"] : false; 
		$rparam["dispId"] = isset($gDropdown["dispId"]) ? $gDropdown["dispId"] : false;
		$rparam["secondDriverId"]   = isset($gDropdown["team_driver_id"]) ? $gDropdown["team_driver_id"] : false;

		$chartStack = $this->fetchDashboardData( $rparam, $lPerformance);
		$vehicleInfo = $weatherNotFound = array();
		$lat = $lng = '';
		//--------------- Job Status ----------------
		$loadsSummary 		= $this->Job->fetchLoadsSummary(null, $rparam,$lPerformance);
		$loadsChart['delivered'] = $loadsChart['assigned'] = $loadsChart['booked'] = $loadsChart['inprogress'] = 0;
		$gotStatus = 0;
		if(isset($loadsSummary[0])){
			foreach ($loadsSummary as $key => $value) {
				if(empty(trim($value['JobStatus'])) )
					continue;
				else
					$gotStatus = 1;
				$loadsChart[$value['JobStatus']] = (int)$value['tnum'];
			}
		}
		if(!$gotStatus){
			$loadsChart['noLoads'] = 1;
		}else{
			$loadsChart['noLoads'] = 0;
		}
		//--------------- Job Status ----------------

		$loadsChart["summary"]["invoiceCount"] = $this->Job->getInvoiceCount($rparam,$lPerformance);
		$loadsChart["summary"]["waitingPaperworkCount"] = $this->Job->getWaitingPaperworkCount($rparam,$lPerformance);
		$loadsChart["summary"]["sentForPaymentCount"] = $this->Job->getSentForPaymentCount($rparam,$lPerformance);
		$loadsChart["summary"]["paymentNotRecievedCount"] = 0;



		echo json_encode(array("loadsChart"=>$loadsChart, "chartStack"=>$chartStack, 'success'=>true));
	}



	public function getTrucksLocation($args,$type){
		$this->load->helper('truckstop');
		$vehicleID = array();
		if($type == "_idispatcher"){
			$drivers = $this->Driver->getDriversList($args["dispId"],false,true);
			foreach ($drivers as $key => $value) {
				array_push($vehicleID, $value["vid"]);
			}
		}else if ($type == "_idriver" || $type == "_iteam"){
			$vehicleID = $args["selScope"];
		}

		$fromAPI = 0 ; 
		if($vehicleID){
			$allVehicles = $this->Vehicle->get_vehicles_address(null, $vehicleID);
		}else{
			$allVehicles = $this->Driver->getDriversList(false,false,true);
		}
		foreach ($allVehicles as $key => $value) {
			
			if(empty($value['latitude']) || empty($value['tracker_id']) /* || empty($allVehicles[$key]["loadDetail"])*/){
				unset($allVehicles[$key]);
				continue;
			}else{
				$uid = isset($value['vid']) ? $value['vid'] : $value['id'];
				//$allVehicles[$key]["loadDetail"] = $this->Vehicle->get_current_load($uid);	
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
		return array("allVehicles"=>$allVehicles, "fromAPI"=>$fromAPI);
	}



	public function checkWeatherType($weather_type ,$weather_description, $time = 'day'){
		$weather = "partly-cloudy-day";
		if(strtolower($weather_type) == 'rain' && ($weather_description == 'light rain' || $weather_description == 'moderate rain') ){
			$weather = 'sleet';
		}
		elseif(strtolower($weather_type) == 'rain'){
			$weather = 'rain';
		}
		elseif(strtolower($weather_type) == 'clouds' && $weather_description == 'few clouds' && $time == "day"){
			$weather = 'partly-cloudy-day';
		}elseif(strtolower($weather_type) == 'clouds' && $weather_description == 'few clouds' && $time == "night"){
			$weather = 'partly-cloudy-night';
		}elseif(strtolower($weather_type) == 'clouds'){
			$weather = 'cloudy';
		}
		elseif(strtolower($weather_type) == 'snow'){
			$weather = 'snow';
		}
		elseif(strtolower($weather_type) == 'clear' && $time == "night"){
			$weather = 'clear-night';
		}elseif(strtolower($weather_type) == 'clear' && $time == "day"){
			$weather = 'clear-day';
		}else{
			$weather = 'partly-cloudy-day';
		}
		return $weather;

	}
	

	public function getRssFeeds(){
		$feeds = array(
			array(
				array(
					"title" => "Ryder Reports Third Quarter 2016 Results",
					"description"=> "",
					"link"=> "http://investors.ryder.com/newsroom/News-Release-Details/2016/Ryder-Reports-Third-Quarter-2016-Results/default.aspx",
					"pubDate"=>"Tue, 25 Oct 2016 07:55:00 -0400"
					),
				array(
					"title" => "Inbound Logistics Names Ryder a 2016 Top 100 Trucker for 20th Consecutive Year",
					"description"=> "",
					"link"=> "http://investors.ryder.com/newsroom/News-Release-Details/2016/Inbound-Logistics-Names-Ryder-a-2016-Top-100-Trucker-for-20th-Consecutive-Year/default.aspx",
					"pubDate"=>"Mon, 24 Oct 2016 16:39:00 -0400"
					),
				array(
					"title" => "Ryder Recognized as Finalist for the Fourth Annual Texas Oil & Gas Awards",
					"description"=> "",
					"link"=> "http://investors.ryder.com/newsroom/News-Release-Details/2016/Ryder-Recognized-as-Finalist-for-the-Fourth-Annual-Texas-Oil--Gas-Awards/default.aspx",
					"pubDate"=>"Mon, 24 Oct 2016 06:55:00 -0400"
					),
				array(
					"title" => "Ryder Introduces Industry’s Most Flexible Fueling Solution – Ryder Mobile Fuel",
					"description"=> "",
					"link"=> "http://investors.ryder.com/newsroom/News-Release-Details/2016/Ryder-Introduces-Industrys-Most-Flexible-Fueling-Solution--Ryder-Mobile-Fuel/default.aspx",
					"pubDate"=>"Thu, 20 Oct 2016 06:55:00 -0400"
					),
				array(
					"title" => "Ryder Executive to Participate on Panel at 3PL Value Creation Summit",
					"description"=> "",
					"link"=> "http://investors.ryder.com/newsroom/News-Release-Details/2016/Ryder-Executive-to-Participate-on-Panel-at-3PL-Value-Creation-Summit/default.aspx",
					"pubDate"=>"Wed, 12 Oct 2016 06:55:00 -0400"
					),

				array(
					"title" => "Ryder Declares Quarterly Cash Dividend",
					"description"=> "",
					"link"=> "http://investors.ryder.com/newsroom/News-Release-Details/2016/Ryder-Declares-Quarterly-Cash-Dividend-1072016/default.aspx",
					"pubDate"=>"Fri, 07 Oct 2016 18:30:00 -0400"
					)
				),
			array(
				array(
					"title" => "Ryder Donates $50,000 to Support Future Generation of Technicians in Canada",
					"description"=> "",
					"link"=> "http://investors.ryder.com/newsroom/News-Release-Details/2016/Ryder-Donates-50000-to-Support-Future-Generation-of-Technicians-in-Canada/default.aspx",
					"pubDate"=>"Wed, 05 Oct 2016 06:55:00 -0400"
					),
				array(
					"title" => "Ryder Third Quarter Conference Call Scheduled for October 25, 2016",
					"description"=> "",
					"link"=> "http://investors.ryder.com/newsroom/News-Release-Details/2016/Ryder-Third-Quarter-Conference-Call-Scheduled-for-October-25-2016/default.aspx",
					"pubDate"=>"Thu, 29 Sep 2016 06:55:00 -0400"
					),
				array(
					"title" => "Ryder Executives to Present at Global Supply Chain Conference",
					"description"=> "",
					"link"=> "http://investors.ryder.com/newsroom/News-Release-Details/2016/Ryder-Executives-to-Present-at-Global-Supply-Chain-Conference/default.aspx",
					"pubDate"=>"Mon, 19 Sep 2016 06:55:00 -0400"
					),
				array(
					"title" => "Ryder CEO to Deliver Keynote on Transportation as “Next Wave of Disruption” at Automotive Logistics Global Conference",
					"description"=> "",
					"link"=> "http://investors.ryder.com/newsroom/News-Release-Details/2016/Ryder-CEO-to-Deliver-Keynote-on-Transportation-as-Next-Wave-of-Disruption-at-Automotive-Logistics-Global-Conference/default.aspx",
					"pubDate"=>"Thu, 15 Sep 2016 16:55:00 -0400"
					)
				)

			);
	//$trucks = $this->getTrucksLocation();
	//echo json_encode(array("feeds"=>$feeds, "trucks"=>$trucks['allVehicles'], "fromAPI"=>$trucks["fromAPI"]));
		echo json_encode(array("feeds"=>$feeds));

	}

	public function updateWidgets(){
		$args = json_decode(file_get_contents('php://input'),true);
		$args = json_encode($args);
		$oldOrder = $this->Job->getWidgetsOrder($this->userId);
		if($oldOrder){
			$this->Job->updateWidgetsOrder($this->userId,$args,true);
		}else{
			$this->Job->updateWidgetsOrder($this->userId,$args);
		}
		echo json_encode(array("success"=>true));
	}

	function validateDate($date)
	{
		$d = DateTime::createFromFormat('Y-m-d', $date);
		return $d && $d->format('Y-m-d') === $date;
	}


	function weather_updates($vehicleID = false){

		$userId = $this->userId;
		$args = json_decode(file_get_contents('php://input'),true);
		//$vehicleID = $args['vehicleID'];
		$lat = $args['latitude'];
		$lng = $args['longitude'];
		$vehicleInfo['vehicle_address'] = $args['address'];

		$output  = '';
		if((empty($lat) || empty($lng)) ){
			$output = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$vehicleInfo['vehicle_address']."&key=AIzaSyBSPVGmxdOqe2yrxzla4iezs00zWe_p6j4"),true);
			if($output['status'] == 'OK'){
				$lat = $output['results'][0]['geometry']['location']['lat'];
				$lng = $output['results'][0]['geometry']['location']['lng'];
				$output = $output['results'][0]['geometry']['location'];
			}
		} 
		$currentWeather = array();
		$dailyForecast = array();
		$weatherNotFound['status'] = false;
		if(!empty($lat) && !empty($lng) ){

			try{
				$currentWeather = @json_decode(file_get_contents('http://api.openweathermap.org/data/2.5/weather?lat='.$lat.'&lon='.$lng.'&mode=json&units=imperial&appid=51b18a47eee105fcac60c7c2e832f587'),true);
				if(is_array($currentWeather) && count($currentWeather) > 0){
					$currentWeather['day'] = date('N',$currentWeather['dt']);
					$currentWeather['date'] = date('dS F , Y', $currentWeather['dt']);
					$currentWeather['today'] = date('l', $currentWeather['dt']);
					$currentWeather['sunrise'] = date('h:i', $currentWeather['sys']['sunrise']);
					$currentWeather['sunset'] = date('h:i', $currentWeather['sys']['sunset']);
					$currentWeather['weather_description'] = $currentWeather['weather'][0]['description'];
					$currentWeather['wind'] = $currentWeather['wind']['speed'];
					$currentWeather['humidity'] = $currentWeather['main']['humidity'];
					$currentWeather['current_temperature'] = $currentWeather['main']['temp'];
					$currentWeather['country'] = $currentWeather['sys']["country"];
					$currentWeather['name'] = $currentWeather["name"];
					$currentWeather['weather_class'] = $this->checkWeatherType($currentWeather['weather'][0]['main'],$currentWeather['weather'][0]['description'] );
					$dailyForecast = @json_decode(file_get_contents('http://api.openweathermap.org/data/2.5/forecast/daily?lat='.$lat.'&lon='.$lng.'&cnt=5&units=imperial&mode=json&appid=3401586ebfec8accfb4845bd016a0819'),true);
					if (!empty($dailyForecast)) {
						$count = 0;
						foreach ($dailyForecast['list'] as $key=>$day) { 
							if($count >= 2 ){
								break;
							}

							if( date('Ymd', $currentWeather['dt']) == date('Ymd', $day['dt']) || $currentWeather['dt'] >= $day['dt'] ){
								unset($dailyForecast['list'][$key]); continue;
							}
							$count++;
							$dailyForecast['list'][$key]['count'] = $count;

							$dailyForecast['list'][$key]['day'] = date('N',$day['dt']);
							$dailyForecast['list'][$key]['date'] = date('dS F , Y', $day['dt']);
							$dailyForecast['list'][$key]['today'] = date('l', $day['dt']);
							$dailyForecast['list'][$key]['weather_class'] = $this->checkWeatherType($day['weather'][0]['main'],$day['weather'][0]['description'], 'day' );	
							$dailyForecast['list'][$key]['weather_class_night'] = $this->checkWeatherType($day['weather'][0]['main'],$day['weather'][0]['description'], 'night' );	

							
							$dailyForecast['list'][$key]['weather_description'] = $day['weather'][0]['description'];

							$dailyForecast['list'][$key]['current_temperature'] = $day['temp']['day'];
							$dailyForecast['list'][$key]['noon'] = $day['temp']['day'];
							$dailyForecast['list'][$key]['night'] = $day['temp']['night'];
							$dailyForecast['list'][$key]['wind'] = $day['speed'];
						}
						$dailyForecast['list'] = array_values($dailyForecast['list']);
					}
				}else{
					$weatherNotFound['status'] = true;		
					$currentWeather = $dailyForecast = array();
				}
			}catch(Exception $e){
				$weatherNotFound['status'] = true;	
			}

		}else{
			$weatherNotFound['status'] = true;
		}

		if($dailyForecast == null || $currentWeather == null)
			$weatherNotFound['status'] = true;	



		 print_r(json_encode(array('currentWeather' =>$currentWeather , 'dailyForecast'=>$dailyForecast , 'weatherNotFound'=>$weatherNotFound)));
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

	public function fetchDashboardData( $rparam = array(), $lPerformance = '' ) {

		$this->load->model("Report");
		$lPResult = array();
	
		$chartStack = array();
		$chartStack["xaxis"] = array();
		$chartStack["charges"] = array();
		$chartStack["ppercent"] = array();
		$chartStack["profitAmount"] = array();
		$chartStack["goalsAchievement"] = array();
		$chartStack["type"] = $lPerformance;

		$goalValues 					= $this->getMonthDays($rparam['startDate'],$rparam['endDate']);	
		$startDate 						= new DateTime($rparam["startDate"]);
		$endDate   						= new DateTime($rparam["endDate"]);
		$daysdiff 						= $endDate->diff($startDate)->format("%a");
		$daysdiff 						= $daysdiff + 1;
	
		$goalValues = $this->getMonthDays($rparam['startDate'],$rparam['endDate']);
	 
		$startDate = new DateTime($rparam["startDate"]);
		$endDate   = new DateTime($rparam["endDate"]);

		$daysdiff = $endDate->diff($startDate)->format("%a");
		$daysdiff = $daysdiff + 1;

		$lPResult = array();

			switch ($lPerformance) {
				case '_iall'		:
					$dispatchersList = $this->Report->getDispatchersListForGoals($rparam);
					$loadResult = $this->getPerformanceLoadsForAllDispatchers($dispatchersList,$rparam,$goalValues);
					$lPResult 	= $loadResult['lPResult'];
					$chartStack = $loadResult['chartStack'];				
				break;
				case '_idispatcher'	: 
					// $rparam['startDate']		= '2017-04-01';
					// $rparam['endDate']		= '2017-04-30';
					$getDrivers = $this->Report->getDispatcherDriverDashboard($rparam);
					$currentDrivers = $this->Report->getTotalTeamDrivers($rparam['dispId'], $rparam['startDate'], $rparam['endDate'] );
					$driverLastLog  = $this->Report->getDispatcherLastLog($rparam['dispId'], $rparam['startDate']);
					$val = array();
					$arrSingle = array();
					$arrTeam = array();
					if ( !empty($currentDrivers)) {
						
						$driversList = $this->getUniqueDriverDate($currentDrivers);
						$newArrayTeam = array();
						$newArraySingle = array();
						for($i = 0; $i < count($driversList); $i++ )	 {
							if ( $driversList[$i]['team'] > 0) {
								$newArrayTeam[] = explode(',',$driversList[$i]['assigned_team']);
							} 

							if ( $driversList[$i]['single'] > 0) {
								$newArraySingle[] = explode(',',$driversList[$i]['assigned_drivers']);
							} 
						}

						for( $i = 0; $i < count($newArraySingle); $i++ ) {
							if( isset($newArraySingle[$i+1]))
								$result = array_unique(array_merge($newArraySingle[$i], $newArraySingle[$i+1]));
						}

						for( $i = 0; $i < count($newArrayTeam); $i++ ) {
							if( isset($newArrayTeam[$i+1]))
								$resultTeam = array_unique(array_merge($newArrayTeam[$i], $newArrayTeam[$i+1]));
						}
						
						if ( isset($result)) {
							$newArraySingle = array_values($result);
						} else if ( isset($newArraySingle[0] ) ){
							$newArraySingle = $newArraySingle[0];
						}

						if ( isset($resultTeam)) {
							$newArrayTeam = array_values($resultTeam);
						} else if ( isset($newArrayTeam[0] ) ){
							$newArrayTeam = $newArrayTeam[0];
						}

						$daysDriversList = $driversList;
					} else {

						$driversList = $this->Report->getTotalDriversList($rparam['dispId'], $rparam['startDate'], $rparam['endDate'] );
						$newArraySingle = ($driversList['assigned_drivers'] != '' ) ? explode(',',$driversList['assigned_drivers']) : array();
						$newArrayTeam = ($driversList['assigned_team'] != '' ) ? explode(',',$driversList['assigned_team']) : array();

						$daysDriversList = array();
					}
				
				if ( !empty($getDrivers)) {			
					$newArray = array();

					if ( isset($driversList[0]) && is_array($driversList[0])) {
			
					} else if ( !empty($driversList)) {
						$driversList = array($driversList);
					}
					$lastDate = end($driversList);
					$lastDate = isset($lastDate['createdDate']) ? $lastDate['createdDate'] : $rparam['endDate'];
					$driversListCount = count($driversList);
					
				foreach( $getDrivers as $key => $driver ) {
					$daysdiff = 1;	
					$notFind = 0;
					$find = 0;

					for ( $i = $driversListCount; $i > 0; $i-- ) {
						$j = $i - 1;
							if ( $driver['driver_type'] =='team' ) {
								$checkArray = array();
								if ( $driversList[$j]['assigned_team'] != '' )
									$checkArray = explode(',',$driversList[$j]['assigned_team']);
							
								$teamArray = array();
								if ( !empty($checkArray)) {
									for($k = 0; $k < count($checkArray); $k++ ) {
										$driverIds = explode(':',$checkArray[$k]);
										$teamArray['driversId'][$k] = $driverIds[0];
										$teamArray['teamsId'][$k] = $driverIds[1];
									}
								}
								
								if ( !empty($teamArray) && in_array($driver['driverId'],$teamArray['driversId']) && in_array($driver['second_driver_id'],$teamArray['teamsId'])) {

									if ( !empty($driverLastLog) && count($driverLastLog) > 0 ) {
										$startDate = new DateTime($rparam['startDate']);
									} else {	
										$startDate 			= new DateTime($driversList[0]['createdDate']);
									}

									
									if ( $driversList[$j]['createdDate'] < $rparam['startDate'] ) {		// check if start date is greater than logged date
										$endDate   = new DateTime($rparam['endDate']);
										$lastDate  = $rparam['endDate'];
									} else {
										if ( $i == $driversListCount ) {						// check if drivers is found in latest date log
											$endDate   = new DateTime($driversList[$j]["createdDate"]);
											$lastDate  = $driversList[$j]['createdDate'];
										} else {
											$endDate   = new DateTime($driversList[$i]["createdDate"]);
											$lastDate  = $driversList[$i]['createdDate'];
											$notFind  = 0;
										}
									}

									$daysdiff += $endDate->diff($startDate)->format("%a");
									$driver['days'][$key] = $daysdiff;
									$find = 1;
									$arrTeam[] = $driver['driverId'].':'.$driver['second_driver_id'];
									break;
								}
							} else {
								$checkSingleArray = explode(',',$driversList[$j]['assigned_drivers']);
								if ( in_array($driver['driverId'],$checkSingleArray)) {

									if ( !empty($driverLastLog) && count($driverLastLog) > 0 ) {
										$startDate = new DateTime($rparam['startDate']);
									} else {	
										$checkSingleArraynew = array();
										for( $n = 0; $n < count($driversList); $n++ ) {
											$checkSingleArraynew = explode(',',$driversList[$n]['assigned_drivers']);
											if ( in_array($driver['driverId'],$checkSingleArraynew)) {
												$newSDate = $driversList[$n]['createdDate'];
												break;
											}
										}
										$startDate 	= new DateTime($newSDate);
									}

									if ( $driversList[$j]['createdDate'] < $rparam['startDate'] ) {			// check if start date is greater than logged date
										$endDate   = new DateTime($rparam['endDate']);
										$lastDate  = $rparam['endDate'];
									} else {
										if ( $i == $driversListCount ) {									// check if drivers is found in latest date log
											$endDate   = new DateTime($driversList[$j]["createdDate"]);
											$lastDate  = $driversList[$j]['createdDate'];
										} else {
											$endDate   = new DateTime($driversList[$i]["createdDate"]);
											$lastDate  = $driversList[$i]['createdDate'];
											$notFind   = 0;
										}
									}

									$daysdiff += $endDate->diff($startDate)->format("%a");
									$driver['days'][$key] = $daysdiff;
									$find = 1;
									$arrSingle[] = $driver['driverId'];
									break;
								}
							}
						}
						
						if ($find == 0 ) {

							$startDate = new DateTime($rparam['startDate']);
							$setEndDate = (isset($driver['DeliveryDate']) && $driver['DeliveryDate'] != '0000-00-00' && $driver['DeliveryDate'] != '' ) ? $driver['DeliveryDate'] : $driversList[0]['createdDate'];
					
							$endDate   = new DateTime($setEndDate);
							$daysdiff += $endDate->diff($startDate)->format("%a");
							$driver['days'][$key] = $daysdiff;
							$notFind = 1;
						}
				
					if ( $lastDate < $rparam['endDate'] && $notFind == 0 ) {
						$startDate = new DateTime($lastDate);
						$endDate   = new DateTime($rparam["endDate"]);

						$daysdiff = $endDate->diff($startDate)->format("%a");						
						$driver['days'][$key] += $daysdiff;
					}

					if ( $driver['driver_type'] == 'team' ) {
						$rparam['driverId']			= $driver['driverId'];
						$rparam['secondDriverId'] 	= $driver['second_driver_id'];
						$type = 'team';
						$setDriverId = $driver['driverId'].':'.$driver['second_driver_id'];
					} else {
						$rparam['driverId']			= $driver['driverId'];
						$rparam['secondDriverId'] 	= 0;
						$type = 'driver';
						$setDriverId = $driver['driverId'];
					}

					$totalDays = $driver['days'][$key];
					$value = array();
					$value = $this->Report->getLoadsTrackingDriverDashboardNew($rparam,$type,$rparam['dispId']);
					
					$val[$key]['records'] = $value;
					$val[$key]['totalDays'] = $totalDays;
					$val[$key]['type'] = $type;
					$val[$key]['driverId'] = $setDriverId;
				}

			}

				$singleDiff = array_diff($newArraySingle,$arrSingle);
				$teamDiff   = array_diff($newArrayTeam,$arrTeam);
				if ( !empty($singleDiff)) {
					$key = count($val);
					$singleDiff = array_values($singleDiff);
					for( $i =0; $i < count($singleDiff); $i++ ) {
						$key = $key + $i;
						$type = 'driver';
					 	$totalDays = $this->getNumberOfDays($rparam, $daysDriversList, $singleDiff[$i], $type);
						$rparam['driverId'] = $singleDiff[$i];
						$value = $this->Report->getLoadsTrackingDriverDashboardNew($rparam,$type,$rparam['dispId']);
						$val[$key]['records'] = $value;
						$val[$key]['totalDays'] = $totalDays;
						$val[$key]['type'] = $type;
						$val[$key]['driverId'] = $singleDiff[$i];
					}
				}

				if ( !empty($teamDiff)) {
					$key = count($val);
					$teamDiff = array_values($teamDiff);
					for( $i =0; $i < count($teamDiff); $i++ ) {
						$key = $key + $i;
						$ids = explode(':',$teamDiff[$i]);
						$rparam['driverId'] = $ids[0];
						$rparam['secondDriverId'] = $ids[1];
						$type = 'team';
						$totalDays = $this->getNumberOfDays($rparam, $daysDriversList, $teamDiff[$i], $type);
						$value = $this->Report->getLoadsTrackingDriverDashboardNew($rparam,$type,$rparam['dispId']);
						$val[$key]['records'] = $value;
						$val[$key]['totalDays'] = $totalDays;
						$val[$key]['type'] = $type;
						$val[$key]['driverId'] = $teamDiff[$i];
					}
				}

					foreach($val as $key => $res ) {
						$this->teamFinancialGoal 		= 0;
						$this->teamMilesGoal 			= 0;
						$this->singleFinancialGoal		= 0;
						$this->singleMilesGoal			= 0;
						$value = $res['records'];
						$totalDays = $res['totalDays'];
						$type = $res['type'];
					
						$value["driver"]  = isset($value['driver']) ? $value['driver'] : '';
						$value["charges"]  = isset($value['charges']) ? $value['charges'] : '';
						$value["driverName"]  = isset($value['driverName']) ? $value['driverName'] : '';
						$value["driverId"]  = isset($value['driverId']) ? $value['driverId'] : '';
						$value["second_driver_id"]  = isset($value['second_driver_id']) ? $value['second_driver_id'] : '';
						$value["profit"]  = isset($value['profit']) ? $value['profit'] : '';
						$value["invoice"]  = isset($value['invoice']) ? $value['invoice'] : '';
						$value["miles"]  = isset($value['miles']) ? $value['miles'] : '';
						$value["deadmiles"]  = isset($value['deadmiles']) ? $value['deadmiles'] : '';

						$value['driverName'] = $this->Report->fetchDriverName( $res['driverId']);						
						
						if( isset($type) && $type == 'team') {
							$this->teamFinancialGoal 	 = $goalValues['teamFinancial'] * $totalDays;
							$this->teamMilesGoal 	 	 = $goalValues['teamMiles'] * $totalDays;
						} else {
							$this->singleFinancialGoal = $goalValues['singleFinancial'] * $totalDays;
							$this->singleMilesGoal     = $goalValues['singleMiles'] * $totalDays;
						}

						$this->totalFinancialGoal  					= $this->singleFinancialGoal + $this->teamFinancialGoal;
						$this->totalMilesGoal 						= $this->singleMilesGoal + $this->teamMilesGoal;
						$plusMinusFinancialGoal 					= $value['invoice'] - $this->totalFinancialGoal;
						$plusMinusMilesGoal 						= $value['miles'] - $this->totalMilesGoal;
						$lPResult[$key]["financialGoal"] 			= $this->totalFinancialGoal;
						$lPResult[$key]["plusMinusFinancialGoal"] 	= $plusMinusFinancialGoal;
						$lPResult[$key]["milesGoal"]	 			= $this->totalMilesGoal;
						$lPResult[$key]["plusMinusMilesGoal"]	 	= $plusMinusMilesGoal;

						$lPResult[$key]["fcolumn"] 					= $value["driverName"];
						$lPResult[$key]["dispatcherId"] 			= $value["driverId"];
						$lPResult[$key]["username"] 				= $value["driverName"];
						$lPResult[$key]["urlColumn"] 				= 'driver';
						$lPResult[$key]["second_driver_id"] 		= $value['second_driver_id'];

						$lPResult[$key]["miles"]      				= intval($value["miles"]);
						$lPResult[$key]["deadmiles"] 				= intval($value["deadmiles"]);
						$lPResult[$key]["invoice"]   				= floatval($value["invoice"]);
						$lPResult[$key]["charges"]    				= floatval($value["charges"]);
						$lPResult[$key]["profit"]    				= floatval($value["profit"]);

						if(isset($value["profitPercent"]) ){
							if($value["invoice"] > 0){
								$lPResult[$key]["ppercent"]  = number_format((float)(($value["profit"]/$value["invoice"]) * 100),2);	
							}else{
								$lPResult[$key]["ppercent"] =$value["profitPercent"];
							}
							
						}else if(isset($value["overallTotalProfitPercent"])){
							$lPResult[$key]["ppercent"] = number_format((float)$value["overallTotalProfitPercent"],2);
						}

						$this->overallTotalFinancialGoal 		+= $this->totalFinancialGoal;
						$this->overallTotalMilesGoal 			+= $this->totalMilesGoal;
						$this->overallPlusMinusFinancialGoal    += $plusMinusFinancialGoal;
						$this->overallPlusMinusMilesGoal  		+= $plusMinusMilesGoal;
						$this->totalInvoices		        	+= $lPResult[$key]["invoice"];
						$this->totalMiles 		        		+= $lPResult[$key]["miles"];
						$this->totalDeadMiles		        	+= $lPResult[$key]["deadmiles"];
						$this->totalCharges	            		+= $lPResult[$key]["charges"];
						$this->totalProfit                		+= $lPResult[$key]["profit"];
						
						array_push($chartStack["xaxis"], $value["driverName"]); 
						array_push($chartStack["charges"],  array("y"=>(float) $value["charges"], "clickType"=>"sendToDetail", "urlColumn"=>"driver","username"=>$value["driverName"],"dispatcherId"=>$value["driverId"],"second_driver_id"=>$value["second_driver_id"]));
						array_push($chartStack["profitAmount"],  array("y"=>(float) $value["profit"], "clickType"=>"sendToDetail", "urlColumn"=>"driver","username"=>$value["driverName"],"dispatcherId"=>$value["driverId"],"second_driver_id"=>$value["second_driver_id"]));
						
						array_push($chartStack["ppercent"], (float) $value["profit"]);
						array_push($chartStack["goalsAchievement"],  array("y"=>(float) $this->totalFinancialGoal, "clickType"=>"sendToDetail", "urlColumn"=>"driver","username"=>$value["driverName"],"dispatcherId"=>$value['driverId'],"second_driver_id"=>$value["second_driver_id"]));
					}

				break; 
				case '_iteam'		:
				case '_idriver'		: 

					if ($lPerformance == '_iteam' )
						$lPResult =	$this->Report->getLoadsTrackingIndividual($rparam,"team","dashboard");
					else
						$lPResult =	$this->Report->getLoadsTrackingIndividual($rparam,"loads","dashboard"); 

					foreach($lPResult as $key => $value ) {
						$delDate = $this->validateDate($value["DeliveryDate"]) ?  $value["DeliveryDate"] : 'N/A';
						array_push($chartStack["xaxis"], $delDate); 
						array_push($chartStack["ppercent"], (float) $value["overallTotalProfitPercent"]);
						array_push($chartStack["charges"],  array("y"=>(float) $value["charges"], "jobId"=> $value["loadid"], "clickType"=>"openTicket"));
						array_push($chartStack["profitAmount"],  array("y"=>(float) $value["profit"], "jobId"=> $value["loadid"], "clickType"=>"openTicket"));
						
						$lPResult[$key]["fcolumn"] = $value["loadid"];
						$lPResult[$key]["username"] = '';
						$lPResult[$key]["urlColumn"] = '';

						$lPResult[$key]["miles"]      = intval($value["miles"]);
						$lPResult[$key]["deadmiles"]  = intval($value["deadmiles"]);
						$lPResult[$key]["invoice"]    = floatval($value["invoice"]);
						$lPResult[$key]["charges"]    = floatval($value["charges"]);
						$lPResult[$key]["profit"]     = floatval($value["profit"]);

						if(isset($value["DeliveryDate"])){
							$lPResult[$key]["DeliveryDate"]  = $this->validateDate($value["DeliveryDate"]) ? $value["DeliveryDate"] : "N/A";	
						}

						if(isset($value["profitPercent"]) ){
							if($value["invoice"] > 0){
								$lPResult[$key]["ppercent"]  = number_format((float)(($value["profit"]/$value["invoice"]) * 100),2);	
							}else{
								$lPResult[$key]["ppercent"] =$value["profitPercent"];
							}
							
						}else if(isset($value["overallTotalProfitPercent"])){
							$lPResult[$key]["ppercent"] = number_format((float)$value["overallTotalProfitPercent"],2);
						}

						$this->totalInvoices		        += $lPResult[$key]["invoice"];
						$this->totalMiles 		        	+= $lPResult[$key]["miles"];
						$this->totalDeadMiles		       	+= $lPResult[$key]["deadmiles"];
						$this->totalCharges	            	+= $lPResult[$key]["charges"];
						$this->totalProfit                	+= $lPResult[$key]["profit"];
						
					}
				break;
				default				: 
					$dispatchersList = $this->Report->getDispatchersListForGoals($rparam);
					$loadResult = $this->getPerformanceLoadsForAllDispatchers($dispatchersList,$rparam,$goalValues);
					$lPResult 	= $loadResult['lPResult'];
					$chartStack = $loadResult['chartStack'];
				break;
			}

		$chartStack["trecords"] = $lPResult;
		
		if ( $this->totalInvoices != 0 )
			$totalProfitPercent = number_format((float)(($this->totalProfit / $this->totalInvoices) * 100),2);
		else
			$totalProfitPercent = 0;

		if ( $lPerformance != '_iall' && $lPerformance != '' && $lPerformance != 'all') {

			if(isset($this->totalInvoices) && $this->totalInvoices > 0 ) {
				$this->totalProfitPercent  = number_format((float)(($this->totalProfit/$this->totalInvoices) * 100),2);	
			} else {
				$this->totalProfitPercent  			= 0;
			}
			
			$chartStack['totals']['totInvoices']      				= $this->totalInvoices;
			$chartStack['totals']['totMiles']         				= $this->totalMiles;
			$chartStack['totals']['totDeadMiles']     				= $this->totalDeadMiles;
			$chartStack['totals']['totCharges']       				= $this->totalCharges;
			$chartStack['totals']['totProfit']        				= $this->totalProfit;
			$chartStack['totals']['totProfitPercent'] 				= $this->totalProfitPercent;
			$chartStack['totals']['overallTotalFinancialGoal'] 		= $this->overallTotalFinancialGoal;
			$chartStack['totals']['overallTotalMilesGoal'] 			= $this->overallTotalMilesGoal;
			$chartStack['totals']['overallPlusMinusFinancialGoal'] 	= $this->overallPlusMinusFinancialGoal;
			$chartStack['totals']['overallPlusMinusMilesGoal'] 		= $this->overallPlusMinusMilesGoal;
		}
		return $chartStack;

	}

	/**
	* Get number of days for drivers
	*/
	
	public function getNumberOfDays($rparam, $driversList, $driverId, $type) {
		$daysdiff = 1;
		$newEndDate = $rparam['startDate'];

		if ( isset($driversList[0]) && is_array($driversList[0])) {
			
		} else if ( !empty($driversList)) {
			$driversList = array($driversList);
		}
	
		if ( !empty($driversList)) {	
			for( $i = 0; $i < count($driversList); $i++ ) {
				if ( $type == 'team') {
					$driverIds = explode(',',$driversList[$i]['assigned_team']);
				} else {
					$driverIds = explode(',',$driversList[$i]['assigned_drivers']);
				}

				if ( $i == 0 && in_array($driverId,$driverIds)) {
					$sDate 			= new DateTime($rparam["startDate"]);
					$eDate   		= new DateTime($driversList[$i]['createdDate']);
					$newEndDate 	= $driversList[$i]['createdDate'];
					$daysdiff 	   += $eDate->diff($sDate)->format("%a");
					
				} else if ( in_array($driverId,$driverIds)) { 
					$endCreated = $driversList[$i]['createdDate'];
					$sDate 			= new DateTime($driversList[$i -1 ]['createdDate']);
					$eDate   		= new DateTime($endCreated);
					$newEndDate 	= $driversList[$i]['createdDate'];

					$daysdiff 		+= $eDate->diff($sDate)->format("%a");
					
				}		
			}
		}	

		if( $newEndDate < $rparam['endDate'] ) {	
			$sDate 					= new DateTime($newEndDate);
			$eDate   				= new DateTime($rparam['endDate']);
			$daysdiff 			   += $eDate->diff($sDate)->format("%a");
		}
		return $daysdiff; 
	}

	/**
	* returning data for dashboard in case of all groups selected
	*/

	public function getPerformanceLoadsForAllDispatchers($dispatchersList = array(),$rparam, $goalValues) {
		$mainArray = array();
		$lPResult = array();
		$chartStack = array();
		$chartStack["xaxis"] = array();
		$chartStack["charges"] = array();
		$chartStack["ppercent"] = array();
		$chartStack["profitAmount"] = array();
		$chartStack["goalsAchievement"] = array();	
//pr($dispatchersList);
		// $rparam['startDate'] = '2017-04-01';
		// $rparam['endDate'] = '2017-04-02';

		$goalLogStartDate = '2017-03-29';			// static date to consider the logs which are started from 29th march
		foreach( $dispatchersList as $key => $dispatcher ) {
			$this->teamFinancialGoal 				= 0;
			$this->teamMilesGoal 					= 0;
			$this->singleFinancialGoal				= 0;
			$this->singleMilesGoal					= 0;
			$showDispatcher							= 1;
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
							$this->singleFinancialGoal 	+= $driversList[$i]['single'] * $goalValues['singleFinancial'] * $daysdiff;
							$this->teamFinancialGoal	+= $driversList[$i]['team'] * $goalValues['teamFinancial'] * $daysdiff;
							$this->singleMilesGoal		+= $driversList[$i]['single'] * $goalValues['singleMiles'] * $daysdiff;
							$this->teamMilesGoal	 	+= $driversList[$i]['team'] * $goalValues['teamMiles'] * $daysdiff;
						} else {
							if ( !empty($driverLastLog) && count($driverLastLog) > 0 ) {	// check if previous log exist for dispatcher then calc from startdate
								$sDate 		= new DateTime($rparam['startDate']);
								$eDate   	= new DateTime($driversList[$i]['createdDate']);
								$newEndDate = $driversList[$i]['createdDate'];
								$daysdiff  += $eDate->diff($sDate)->format("%a");
								$daysdiff = $daysdiff - 1;
							
								$this->singleFinancialGoal 	+= $driverLastLog['single'] * $goalValues['singleFinancial'] * $daysdiff;
								$this->teamFinancialGoal	+= $driverLastLog['team'] * $goalValues['teamFinancial'] * $daysdiff;
								$this->singleMilesGoal		+= $driverLastLog['single'] * $goalValues['singleMiles'] * $daysdiff;
								$this->teamMilesGoal	 	+= $driverLastLog['team'] * $goalValues['teamMiles'] * $daysdiff;

								$this->singleFinancialGoal 	+= $driversList[$i]['single'] * $goalValues['singleFinancial'];
								$this->teamFinancialGoal	+= $driversList[$i]['team'] * $goalValues['teamFinancial'];
								$this->singleMilesGoal		+= $driversList[$i]['single'] * $goalValues['singleMiles'];
								$this->teamMilesGoal	 	+= $driversList[$i]['team'] * $goalValues['teamMiles'];
							}
							else {					// check if previous log does not exist for dispatcher then calc from logged start date
								$sDate 			= new DateTime($driversList[$i]['createdDate']);
								$eDate   		= new DateTime($driversList[$i]['createdDate']);
								$newEndDate 	= $driversList[$i]['createdDate'];
								$daysdiff 	   += $eDate->diff($sDate)->format("%a");

								$this->singleFinancialGoal 	+= $driversList[$i]['single'] * $goalValues['singleFinancial'] * $daysdiff;
								$this->teamFinancialGoal	+= $driversList[$i]['team'] * $goalValues['teamFinancial'] * $daysdiff;
								$this->singleMilesGoal		+= $driversList[$i]['single'] * $goalValues['singleMiles'] * $daysdiff;
								$this->teamMilesGoal	 	+= $driversList[$i]['team'] * $goalValues['teamMiles'] * $daysdiff;
							}

							
						}
					} else {
						$endCreated = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $driversList[$i]['createdDate']) ) ));
						$sDate 			= new DateTime($driversList[$i -1 ]['createdDate']);
						$eDate   		= new DateTime($endCreated);
						$newEndDate 	= $driversList[$i]['createdDate'];

						$daysdiff 			= $eDate->diff($sDate)->format("%a");
						$this->singleFinancialGoal += $driversList[$i-1]['single'] * $goalValues['singleFinancial'] * $daysdiff;
						$this->teamFinancialGoal   += $driversList[$i-1]['team'] * $goalValues['teamFinancial'] * $daysdiff;
						$this->singleMilesGoal	   += $driversList[$i -1]['single'] * $goalValues['singleMiles'] * $daysdiff;
						$this->teamMilesGoal	   += $driversList[$i -1]['team'] * $goalValues['teamMiles'] * $daysdiff;

						$this->singleFinancialGoal += $driversList[$i]['single'] * $goalValues['singleFinancial'];
						$this->teamFinancialGoal   += $driversList[$i]['team'] * $goalValues['teamFinancial'];
						$this->singleMilesGoal	   += $driversList[$i]['single'] * $goalValues['singleMiles'];
						$this->teamMilesGoal	   += $driversList[$i]['team'] * $goalValues['teamMiles'];
					}

					if ( $driversList[$i]['single'] <= 0 && $driversList[$i]['team'] <= 0 )
					$showDispatcher = 0;		
				}
							
				if( $newEndDate < $rparam['endDate'] ) {	
					$sDate 					= new DateTime($newEndDate);
					$eDate   				= new DateTime($rparam['endDate']);
					$daysdiff 				= $eDate->diff($sDate)->format("%a");

					$driverList = end($driversList);

					$this->singleFinancialGoal 	+= $driverList['single'] * $goalValues['singleFinancial'] * $daysdiff;
					$this->teamFinancialGoal 	+= $driverList['team'] * $goalValues['teamFinancial'] * $daysdiff;
					$this->singleMilesGoal	 	+= $driverList['single'] * $goalValues['singleMiles'] * $daysdiff;
					$this->teamMilesGoal	 	+= $driverList['team'] * $goalValues['teamMiles'] * $daysdiff;
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
				$this->singleFinancialGoal  += $single * $goalValues['singleFinancial'] * $daysdiff;
				$this->teamFinancialGoal	+= $team * $goalValues['teamFinancial'] * $daysdiff;
				$this->singleMilesGoal	    += $single * $goalValues['singleMiles'] * $daysdiff;
				$this->teamMilesGoal	 	+= $team * $goalValues['teamMiles'] * $daysdiff;

				if ( $driversList['createdDate'] > $rparam['endDate'] && empty($driverLastLog)) {
					$showDispatcher = 0;
				} else if( $driversList['single'] <= 0 && $driversList['team'] <= 0 ) {
					$showDispatcher = 0;
				}
			}									
			
			if ( empty($value) && $showDispatcher == 0)
				continue;

			$value['profit']     = isset($value['profit']) ? $value['profit'] : 0;
			$value['charges']    = isset($value['charges']) ? $value['charges'] : 0;
			$value['invoice'] 	 = isset($value['invoice']) ? $value['invoice'] : 0;
			$value['miles']    	 = isset($value['miles']) ? $value['miles'] : 0;
			$value['deadmiles']  = isset($value['deadmiles']) ? $value['deadmiles'] : 0;
			$value['username']   = isset($value['username']) ? $value['username'] : '';

			$this->totalFinancialGoal 				 	  = $this->singleFinancialGoal + $this->teamFinancialGoal; 		
			$this->totalMilesGoal 						  = $this->singleMilesGoal + $this->teamMilesGoal;
			$plusMinusFinancialGoal 				  = $value['invoice'] - $this->totalFinancialGoal;
			$plusMinusMilesGoal 					  = $value['miles'] - $this->totalMilesGoal;
			$lPResult[$key]["financialGoal"] 		  = $this->totalFinancialGoal;
			$lPResult[$key]["plusMinusFinancialGoal"] = $plusMinusFinancialGoal;
			$lPResult[$key]["milesGoal"]			  = $this->totalMilesGoal;
			$lPResult[$key]["plusMinusMilesGoal"]	  = $plusMinusMilesGoal;
			$lPResult[$key]["fcolumn"] 		 		  = $dispatcher["dispatcher"];
			$lPResult[$key]["urlColumn"] 	 		  = 'dispatcher';
			$lPResult[$key]["username"] 	 		  = $dispatcher['username'];
			$lPResult[$key]["dispatcherId"] 	 	  = $dispatcher['dispId'];
		
			$lPResult[$key]["miles"]      = intval($value["miles"]);
			$lPResult[$key]["deadmiles"]  = intval($value["deadmiles"]);
			$lPResult[$key]["invoice"]    = floatval($value["invoice"]);
			$lPResult[$key]["charges"]    = floatval($value["charges"]);
			$lPResult[$key]["profit"]     = floatval($value["profit"]);

			if(isset($value["profitPercent"]) ){
				if($value["invoice"] > 0){
					$lPResult[$key]["ppercent"]  = number_format((float)(($value["profit"]/$value["invoice"]) * 100),2);	
				}else{
					$lPResult[$key]["ppercent"] =$value["profitPercent"];
				}
				
			}else if(isset($value["overallTotalProfitPercent"])){
				$lPResult[$key]["ppercent"] = number_format((float)$value["overallTotalProfitPercent"],2);
			}

			$this->overallTotalFinancialGoal 		+= $this->totalFinancialGoal;
			$this->overallTotalMilesGoal 			+= $this->totalMilesGoal;
			$this->overallPlusMinusFinancialGoal  	+= $plusMinusFinancialGoal;
			$this->overallPlusMinusMilesGoal  		+= $plusMinusMilesGoal;
			$this->totalInvoices		        	+= $lPResult[$key]["invoice"];
			$this->totalMiles 		        		+= $lPResult[$key]["miles"];
			$this->totalDeadMiles		        	+= $lPResult[$key]["deadmiles"];
			$this->totalCharges	            		+= $lPResult[$key]["charges"];
			$this->totalProfit                		+= $lPResult[$key]["profit"];
			
			array_push($chartStack["xaxis"], $dispatcher["username"]); 
			array_push($chartStack["charges"],  array("y"=> (float) $value["charges"], "clickType"=>"sendToDetail", "urlColumn"=>"dispatcher","username"=>$dispatcher["username"],"dispatcherId"=>$dispatcher['dispId'],"second_driver_id"=>''));
			array_push($chartStack["ppercent"], (float) $value["profit"]);
			array_push($chartStack["profitAmount"],  array("y"=> (float) $value["profit"], "clickType"=>"sendToDetail", "urlColumn"=>"dispatcher","username"=>$dispatcher["username"],"dispatcherId"=>$dispatcher['dispId'],"second_driver_id"=>''));
			array_push($chartStack["goalsAchievement"],  array("y"=>(float) $this->totalFinancialGoal, "clickType"=>"sendToDetail", "urlColumn"=>"dispatcher","username"=>$dispatcher["username"],"dispatcherId"=>$dispatcher['dispId'],"second_driver_id"=>''));
	
		// die;
			}

		
		if(isset($this->totalInvoices) && $this->totalInvoices > 0 ) {
			$this->totalProfitPercent  = number_format((float)(($this->totalProfit/$this->totalInvoices) * 100),2);	
		} else {
			$this->totalProfitPercent  			= 0;
		}
		$chartStack['totals']['totInvoices']      				= $this->totalInvoices;
		$chartStack['totals']['totMiles']         				= $this->totalMiles;
		$chartStack['totals']['totDeadMiles']     				= $this->totalDeadMiles;
		$chartStack['totals']['totCharges']       				= $this->totalCharges;
		$chartStack['totals']['totProfit']        				= $this->totalProfit;
		$chartStack['totals']['totProfitPercent'] 				= $this->totalProfitPercent;
		$chartStack['totals']['overallTotalFinancialGoal'] 		= $this->overallTotalFinancialGoal;
		$chartStack['totals']['overallTotalMilesGoal'] 			= $this->overallTotalMilesGoal;
		$chartStack['totals']['overallPlusMinusFinancialGoal'] 	= $this->overallPlusMinusFinancialGoal;
		$chartStack['totals']['overallPlusMinusMilesGoal'] 		= $this->overallPlusMinusMilesGoal;
	
		$mainArray['lPResult'] 	 = $lPResult;
		$mainArray['chartStack'] = $chartStack;
		return $mainArray;
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

	/**
	* Fetch list of top 5 customers
	*/

	public function topFiveCustomer(){
		$data = array();
		$this->load->model('BrokersModel');
		$args = json_decode(file_get_contents('php://input'),true);
		$cmpName = $this->BrokersModel->getTopFiveCustomer($args);
		$data["paymentAmount"] = array();
		$data["cName"]['cmpName'] = array();
		$data["cName"]['brokerIds'] = array();
		$data["xAxis"] = array();
		$data["valueIds"] = array();
		$colors =  array('rgba(7, 126, 208, 1)','rgba(109,92,174,1)','rgba(52, 214, 199, 1)','rgba(245,87,83,1)','#626262');

		$dispatcherId = isset($args['dispatcherId']) ? $args['dispatcherId'] : '';
		$driverId = isset($args['driverId']) ? $args['driverId'] : '';
		$secondDriverId = isset($args['secondDriverId']) ? $args['secondDriverId'] : '';

		if ( !empty($cmpName)) {
			$i = 0;
			foreach( $cmpName as $cmp ) {
				array_push($data["paymentAmount"],  array("y"=> (float) $cmp["Total"], 'color' => $colors[$i], "urlColumn" => "broker" , "brokerId" => $cmp['broker_id'], "dispatcherId"=>$dispatcherId, 'driverId' => $driverId, 'second_driver_id' => $secondDriverId ));
				array_push($data['cName']["cmpName"],   $cmp["TruckCompanyName"]);
				array_push($data['cName']["brokerIds"],   $cmp["broker_id"]);
				array_push($data["xAxis"],   '' );
				$i++;
			}
		}
		$data['colors'] = $colors;
		$data['valueIds']['dispatcherId'] = $dispatcherId;
		$data['valueIds']['driverId'] = $driverId;
		$data['valueIds']['secondDriverId'] = $secondDriverId;

		echo json_encode($data); 				
	}

}
