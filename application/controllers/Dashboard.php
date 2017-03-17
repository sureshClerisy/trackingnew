<?php

ini_set('max_execution_time', 300); 

class Dashboard extends CI_Controller {

	public $userId;
	function __construct() {
		parent::__construct();
		$this->load->library('Htmldom');
		$this->load->model('Vehicle');
		$this->load->model('Job');
		$this->load->model('Driver');
		$this->userId = $this->session->loggedUser_id;
		$this->userRoleId = $this->session->role;

	}

	public function index($vehicleID = false){
		$gDropdown = array();$rparam = array();
		$lPerformance = '';
		$args = json_decode(file_get_contents('php://input'),true);
		if(isset($_COOKIE["_globalDropdown"])){
			$gDropdown = json_decode($_COOKIE["_globalDropdown"],true);
		}
		if($vehicleID){
			$vList = array($vehicleID);
			$lPerformance = "_idriver";
			if( isset($gDropdown["label"]) && ($gDropdown["label"] == "team" || $gDropdown["label"] == "_team") ){
				$lPerformance =  "_iteam" ;
				$vList = array($gDropdown["vid"]);	
			}
			//echo $lPerformance;die;
		}else{
			$vList = array();
			if(isset($args["did"]) && !empty($args["did"]) && isset($args["vtype"]) && $args["vtype"] == "_idispatcher"){
				$vList[] = $args["did"];
				$drivers = $this->Driver->getDriversList($args["did"],false,true);
				foreach ($drivers as $key => $value) {
					//array_push($vList, $value["vid"]);
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
				if($gDropdown["label"] != "_iall" && $gDropdown["label"] != "_idispatcher" && $gDropdown["label"] != "_idriver" && $gDropdown["label"] != "_iteam" && $gDropdown["label"] != ""){
					$lPerformance = "_idriver";
				}else if($gDropdown["label"] == ""){
					$lPerformance = "_iall";
				}
			}

		}


		if(isset($args["startDate"]))	{ $rparam["startDate"] = $args["startDate"]; }
		if(isset($args["endDate"]))		{ $rparam["endDate"] = $args["endDate"]; }
		$rparam["selScope"] = $vList;
		$rparam["driverId"] = isset($gDropdown["id"]) ? $gDropdown["id"] : false; $rparam["dispId"] = isset($gDropdown["dispId"]) ? $gDropdown["dispId"] : false;
		$this->load->model("Report");
		$lPResult = array();
		switch ($lPerformance) {
			case '_iall'		: $lPResult = 	$this->Report->getLoadsTrackingAggregate($rparam,"dispatchers","dashboard"); break;
			case '_idispatcher'	: $lPResult =	$this->Report->getLoadsTrackingAggregate($rparam,"drivers","dashboard"); break; 
			case '_idriver'		: $lPResult =	$this->Report->getLoadsTrackingIndividual($rparam,"loads","dashboard"); break;
			case '_iteam'		: $lPResult =	$this->Report->getLoadsTrackingIndividual($rparam,"team","dashboard"); break;
			default				: $lPResult =	$this->Report->getLoadsTrackingAggregate($rparam,"dispatchers","dashboard"); break;
		}

		$chartStack = array();
		$chartStack["xaxis"] = array();
		$chartStack["invoiced"] = array();
		$chartStack["charges"] = array();
		$chartStack["ppercent"] = array();
		$chartStack["profitAmount"] = array();
		$chartStack["type"] = $lPerformance;

		$lPResult = empty($lPResult) ? array() : $lPResult; 
		$totalInvoices	 	= 0;
		$totalMiles  	    = 0;
		$totalDeadMiles 	= 0;
		$totalCharges 		= 0;
		$totalProfit 		= 0;
		$totalProfitPercent = 0;

		foreach ($lPResult as $key => $value) {
			switch ($lPerformance) {
				case '_iall'		: array_push($chartStack["xaxis"], $value["dispatcher"]); 
									  array_push($chartStack["invoiced"], (float) $value["invoice"]);
									  array_push($chartStack["charges"],  (float) $value["charges"]);
									  array_push($chartStack["ppercent"], (float) $value["profit"]);
									  array_push($chartStack["profitAmount"], (float) $value["profit"]);
									  $lPResult[$key]["fcolumn"] = $value["dispatcher"];
									 break;
				case '_idispatcher'	: array_push($chartStack["xaxis"], $value["driver"]); 
									  array_push($chartStack["invoiced"], (float) $value["invoice"]);
									  array_push($chartStack["charges"],  (float) $value["charges"]);
									  array_push($chartStack["ppercent"], (float) $value["profit"]);
									  array_push($chartStack["profitAmount"], (float) $value["profit"]);
									  $lPResult[$key]["fcolumn"] = $value["driver"];
									 break; 
				case '_iteam'		:
				case '_idriver'		: //array_push($chartStack["xaxis"], "Load ".$value["loadid"]); 
									  $delDate = $this->validateDate($value["DeliveryDate"]) ?  $value["DeliveryDate"] : 'N/A';
									  array_push($chartStack["xaxis"], $delDate); 
									  array_push($chartStack["invoiced"], (float) $value["invoice"]);
									  array_push($chartStack["charges"],  (float) $value["charges"]);
									  array_push($chartStack["ppercent"], (float) $value["overallTotalProfitPercent"]);
									  array_push($chartStack["profitAmount"], (float) $value["profit"]);
									  $lPResult[$key]["fcolumn"] = $value["loadid"];
									break;
				default				: array_push($chartStack["xaxis"], $value["dispatcher"]); 
									  array_push($chartStack["invoiced"], (float) $value["invoice"]);
									  array_push($chartStack["charges"],  (float) $value["charges"]);
									  array_push($chartStack["ppercent"], (float) $value["profit"]);
									  array_push($chartStack["profitAmount"], (float) $value["profit"]);
									  $lPResult[$key]["fcolumn"] = $value["dispatcher"];
								  	break;
			}

			if(isset($value["DeliveryDate"])){
				$lPResult[$key]["DeliveryDate"]  = $this->validateDate($value["DeliveryDate"]) ? $value["DeliveryDate"] : "N/A";	
			}
			
			/*$lPResult[$key]["invoice"]  = money_format('%.2n', (float)$value["invoice"]);
			$lPResult[$key]["charges"]  = money_format('%.2n', (float)$value["charges"]);
			$lPResult[$key]["profit"]  	= money_format('%.2n', (float)$value["profit"]);*/
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

			$totalInvoices		 += $lPResult[$key]["invoice"];
			$totalMiles 		 += $lPResult[$key]["miles"];
			$totalDeadMiles		 += $lPResult[$key]["deadmiles"];
			$totalCharges	     += $lPResult[$key]["charges"];
			$totalProfit         += $lPResult[$key]["profit"];
		}

		$chartStack["trecords"] = $lPResult;
		if ( $totalInvoices != 0 )
			$totalProfitPercent = number_format((float)(($totalProfit / $totalInvoices) * 100),2);
		else
			$totalProfitPercent = 0;

		$chartStack['totals']['totInvoices']      = $totalInvoices;
		$chartStack['totals']['totMiles']         = $totalMiles;
		$chartStack['totals']['totDeadMiles']     = $totalDeadMiles;
		$chartStack['totals']['totCharges']       = $totalCharges;
		$chartStack['totals']['totProfit']        = $totalProfit;
		$chartStack['totals']['totProfitPercent'] = $totalProfitPercent;
	
		$vehicleInfo = $weatherNotFound = array();
		$lat = $lng = '';
		//--------------- Job Status ----------------
			//$loadOnTheRoad 		= $this->Job->fetchSavedJobs(null, $vList, 'inprogress');
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

			//pr($loadsChart);die;
		//--------------- Job Status ----------------

		$loadsChart["summary"]["invoiceCount"] = $this->Job->getInvoiceCount($rparam,$lPerformance);
		$loadsChart["summary"]["waitingPaperworkCount"] = $this->Job->getWaitingPaperworkCount($rparam,$lPerformance);
		$loadsChart["summary"]["sentForPaymentCount"] = $this->Job->getSentForPaymentCount($rparam,$lPerformance);
		$loadsChart["summary"]["paymentNotRecievedCount"] = 0;


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
					$vehicleID = $value['vid'];
					$vehicleLabel = $value['driverName'];
					$lat = $value['latitude'];
					$lng = $value['longitude'];
					$weatherNotFound['country'] = 'US';
					$weatherNotFound['name'] = $value['driverName'];	
					break;
				}
			}
		}

		/*if(isset($vehicleInfo["driverName"])){
			$vehicleInfo["driverName"] .=  " - ".$vehicleInfo["label"];
		}*/

		if(count($allVehicles) <= 0)
			$allVehicles = $this->Driver->getDriversList($userId,true,true);
		
			$teamList = $this->Driver->getDriversListAsTeam($userId,true);
			$dispatcherList = $this->Driver->getDispatcherList($userId);
		
		$vehicleList = array();
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
			//$vehicleList = array_merge($vehicleList, $dispatcherList);
			//array_multisort($vehicleList);
		}

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
							$dailyForecast['list'][$key]['weather_class'] = $this->checkWeatherType($day['weather'][0]['main'],$day['weather'][0]['description'] );
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

		echo json_encode(array("currentWeather"=>$currentWeather, "dailyForecast"=>$dailyForecast,"vehicleList"=>$vehicleList,'vehicleID'=>$vehicleID,'vehicleLabel'=>$vehicleLabel,'output'=>$output,'weatherNotFound'=>$weatherNotFound,"loadsChart"=>$loadsChart,"vehicleLocation"=>$vehicleLocation,'selectedDriver'=>$gDropdown, "chartStack"=>$chartStack, 'success'=>true));
	}

		
	public function fetchWidgetsOrder(){
		$widgetOrd = array();
		$widgetsOrder = $this->Job->getWidgetsOrder($this->userId);
		
		if ( !empty($widgetsOrder) ) {
			$widgetsOrder = json_decode($widgetsOrder['widget_order'],true);
			$widgetOrd['left'] = implode(',', $widgetsOrder['left']);
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
		echo json_encode(array('widgetsOrder'=>$widgetOrd,"selectedDriver"=>$vehicleInfo,"selDrivers"=>$userDrivers,"user_role"=>$this->userRoleId));
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
			if($gDropdown["label"] != "_iall" && $gDropdown["label"] != "_idispatcher" && $gDropdown["label"] != "_idriver" && $gDropdown["label"] != "_iteam" && $gDropdown["label"] != ""){
				$lPerformance = "_idriver";
			}else if($gDropdown["label"] == ""){
				$lPerformance = "_iall";
			}
		}




		if(isset($args["startDate"]))	{ $rparam["startDate"] = $args["startDate"]; }
		if(isset($args["endDate"]))		{ $rparam["endDate"] = $args["endDate"]; }
		$rparam["selScope"] = $vList;
		$rparam["driverId"] = isset($gDropdown["id"]) ? $gDropdown["id"] : false; $rparam["dispId"] = isset($gDropdown["dispId"]) ? $gDropdown["dispId"] : false;
		$this->load->model("Report");
		$lPResult = array();
		switch ($lPerformance) {
			case '_iall'		: $lPResult = 	$this->Report->getLoadsTrackingAggregate($rparam,"dispatchers","dashboard"); break;
			case '_idispatcher'	: $lPResult =	$this->Report->getLoadsTrackingAggregate($rparam,"drivers","dashboard"); break; 
			case '_idriver'		: $lPResult =	$this->Report->getLoadsTrackingIndividual($rparam,"loads","dashboard"); break;
			case '_iteam'		: $lPResult =	$this->Report->getLoadsTrackingIndividual($rparam,"team","dashboard"); break;
			default				: $lPResult =	$this->Report->getLoadsTrackingAggregate($rparam,"dispatchers","dashboard"); break;
		}

		$chartStack = array();
		$chartStack["xaxis"] = array();
		$chartStack["invoiced"] = array();
		$chartStack["charges"] = array();
		$chartStack["ppercent"] = array();
		$chartStack["profitAmount"] = array();
		$chartStack["type"] = $lPerformance;

		$lPResult = empty($lPResult) ? array() : $lPResult; 
		$totalInvoices	 	= 0;
		$totalMiles  	    = 0;
		$totalDeadMiles 	= 0;
		$totalCharges 		= 0;
		$totalProfit 		= 0;
		$totalProfitPercent = 0;

		foreach ($lPResult as $key => $value) {
			switch ($lPerformance) {
				case '_iall'		: array_push($chartStack["xaxis"], $value["dispatcher"]); 
									  array_push($chartStack["invoiced"], (float) $value["invoice"]);
									  array_push($chartStack["charges"],  (float) $value["charges"]);
									  array_push($chartStack["ppercent"], (float) $value["profit"]);
									  array_push($chartStack["profitAmount"], (float) $value["profit"]);
									  $lPResult[$key]["fcolumn"] = $value["dispatcher"];
									 break;
				case '_idispatcher'	: array_push($chartStack["xaxis"], $value["driver"]); 
									  array_push($chartStack["invoiced"], (float) $value["invoice"]);
									  array_push($chartStack["charges"],  (float) $value["charges"]);
									  array_push($chartStack["ppercent"], (float) $value["profit"]);
									  array_push($chartStack["profitAmount"], (float) $value["profit"]);
									  $lPResult[$key]["fcolumn"] = $value["driver"];
									 break; 
				case '_iteam'		:
				case '_idriver'		: //array_push($chartStack["xaxis"], "Load ".$value["loadid"]); 
									  $delDate = $this->validateDate($value["DeliveryDate"]) ?  $value["DeliveryDate"] : 'N/A';
									  array_push($chartStack["xaxis"], $delDate); 
									  array_push($chartStack["invoiced"], (float) $value["invoice"]);
									  array_push($chartStack["charges"],  (float) $value["charges"]);
									  array_push($chartStack["ppercent"], (float) $value["overallTotalProfitPercent"]);
									  array_push($chartStack["profitAmount"], (float) $value["profit"]);
									  $lPResult[$key]["fcolumn"] = $value["loadid"];
									break;
				default				: array_push($chartStack["xaxis"], $value["dispatcher"]); 
									  array_push($chartStack["invoiced"], (float) $value["invoice"]);
									  array_push($chartStack["charges"],  (float) $value["charges"]);
									  array_push($chartStack["ppercent"], (float) $value["profit"]);
									  array_push($chartStack["profitAmount"], (float) $value["profit"]);
									  $lPResult[$key]["fcolumn"] = $value["dispatcher"];
								  	break;
			}

			if(isset($value["DeliveryDate"])){
				$lPResult[$key]["DeliveryDate"]  = $this->validateDate($value["DeliveryDate"]) ? $value["DeliveryDate"] : "N/A";	
			}
			
			/*$lPResult[$key]["invoice"]  = money_format('%.2n', (float)$value["invoice"]);
			$lPResult[$key]["charges"]  = money_format('%.2n', (float)$value["charges"]);
			$lPResult[$key]["profit"]  	= money_format('%.2n', (float)$value["profit"]);*/
			$lPResult[$key]["miles"]      = intval($value["miles"]);
			$lPResult[$key]["deadmiles"]  = intval($value["deadmiles"]);
			$lPResult[$key]["invoice"] 	  = floatval($value["invoice"]);
			$lPResult[$key]["charges"]    = floatval($value["charges"]);
			$lPResult[$key]["profit"]  	  = floatval($value["profit"]);
			if(isset($value["profitPercent"])){
				$lPResult[$key]["ppercent"]  = number_format((float)(($value["profit"]/$value["invoice"]) * 100),2);
			}else if(isset($value["overallTotalProfitPercent"])){
				$lPResult[$key]["ppercent"] = number_format((float)$value["overallTotalProfitPercent"],2);
			}

			$totalInvoices		 += $lPResult[$key]["invoice"];
			$totalMiles 		 += $lPResult[$key]["miles"];
			$totalDeadMiles		 += $lPResult[$key]["deadmiles"];
			$totalCharges	     += $lPResult[$key]["charges"];
			$totalProfit         += $lPResult[$key]["profit"];
		}

		$chartStack["trecords"] = $lPResult;
		if ( $totalInvoices != 0 )
			$totalProfitPercent = number_format((float)(($totalProfit / $totalInvoices) * 100),2);
		else
			$totalProfitPercent = 0;
		
		$chartStack['totals']['totInvoices']      = $totalInvoices;
		$chartStack['totals']['totMiles']         = $totalMiles;
		$chartStack['totals']['totDeadMiles']     = $totalDeadMiles;
		$chartStack['totals']['totCharges']       = $totalCharges;
		$chartStack['totals']['totProfit']        = $totalProfit;
		$chartStack['totals']['totProfitPercent'] = $totalProfitPercent;

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
				$allVehicles[$key]["loadDetail"] = $this->Vehicle->get_current_load($uid);	
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
			}
		}
		$allVehicles =  array_values($allVehicles);
		return array("allVehicles"=>$allVehicles, "fromAPI"=>$fromAPI);
	}



	public function checkWeatherType($weather_type ,$weather_description){
		$weather = "partly-cloudy-day";
		if(strtolower($weather_type) == 'rain' && ($weather_description == 'light rain' || $weather_description == 'moderate rain') ){
            $weather = 'sleet';
        }
        elseif(strtolower($weather_type) == 'rain'){
        	$weather = 'rain';
        }
        elseif(strtolower($weather_type) == 'clouds' && $weather_description == 'few clouds'){
            $weather = 'partly-cloudy-day';
        }
        elseif(strtolower($weather_type) == 'clouds'){
            $weather = 'cloudy';
        }
        elseif(strtolower($weather_type) == 'snow'){
            $weather = 'snow';
        }
        elseif(strtolower($weather_type) == 'clear'){
            $weather = 'clear-day';
        }
        else{
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



}
