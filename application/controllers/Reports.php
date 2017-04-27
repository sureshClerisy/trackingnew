<?php

/**
* Reports Controller
*  
*/

class Reports extends Admin_Controller{

	public $userId;
	public $userRoleId;
	private $paginationLimit = 20;
	
	function __construct(){
		parent::__construct();
		$this->userId 		= $this->session->loggedUser_id;
		$this->userRoleId  = $this->session->role;
		$this->load->model(array('Report','Driver','User'));
	}
	
	public function index(){
		$vehicles = $this->Report->getAllVehicles();

		$userId = false;
		$parentId = false;
		$tempUserId = $this->userId;
		$childIds  = array();				// dispatchers child list
		if($this->userRoleId == _DISPATCHER ){
			$userId = $this->userId;
			$parentId =  $this->userId;
			$childIds = $this->User->fetchDispatchersChilds($userId);
		} else if ( $this->userRoleId == 4 ) {
			$parentIdCheck = $this->session->userdata('loggedUser_parentId');
			if( isset($parentIdCheck) && $parentIdCheck != 0 ) {
				$userId = $parentIdCheck;
				$parentId = $parentIdCheck;
				$tempUserId = $parentIdCheck;
			}
		}
		
		$allVehicles = $this->Driver->getDriversListNew($userId);
		$dispatcherList = $this->Driver->getDispatcherList($parentId); 	//for add all drivers under every dispatcher
		$teamList = $this->Driver->getDriversListAsTeamNew($userId);

		if ( !empty($childIds)) {
			$childVehicles = array();
			foreach($childIds as $child ) {
				$childVehicles = $this->Driver->getDriversListNew($child['id']);
				$allVehicles = array_merge($allVehicles,$childVehicles);	
				
				$childVehicles = $this->Driver->getDriversListAsTeamNew($child['id']);
				$teamList = array_merge($teamList,$childVehicles);

				$childs = $this->Driver->getDispatcherList($child['id']);
				$dispatcherList = array_merge($dispatcherList,$childs);
			}
		}

		$vDriversList 	= array();
		if(!empty($allVehicles) && is_array($allVehicles)){	
			$vDriversList = $allVehicles;
			if(is_array($teamList) && count($teamList) > 0){
				foreach ($teamList as $key => $value) {
					$value["label"] = "_team";
					array_unshift($vDriversList, $value);
				}
			}
			
			foreach ($dispatcherList as $key => $value) {
				array_unshift($vDriversList, $value);
			}

				$new = array("id"=>"","profile_image"=>"","driverName"=>"All Groups","label"=>"","username"=>"","latitude"=>"","longitude"=>"","vid"=>"","city"=>"","vehicle_address"=>"","state"=>"");
				array_unshift($vDriversList, $new);
		}	

		$response = array("vehicles"=>$vehicles,"vDriversList"=>$vDriversList);

		echo json_encode($response);
	}
	
	public function irp_breadcrumb_detail($args = false){
		
		if($args){
			$filters = $args;
		}else{
			$filters = json_decode(file_get_contents('php://input'),true);	
			if ( isset($filters['changeVariable']) && $filters['changeVariable'] == 1 ) {
				$filters['args'] = $filters['formValue'];
				unset($filters['formValue']);
			}
		}

		$filterArgs = array();
		if ( isset($filters['args']['startDate']) && $filters['args']['startDate']  != '' ) {
			$filterArgs['startDate'] = date('Y-m-d',strtotime($filters['args']['startDate']));
			$filterArgs['endDate']   = date('Y-m-d',strtotime($filters['args']['endDate']));
		} else {
			$filterArgs['startDate'] = '';
			$filterArgs['endDate']   = '';
		}

		// $filterArgs['customDate'] = date('Y-m-d');
		$filterArgs['includeLatLong'] = isset($filters['args']['includeLatLong']) ? $filters['args']['includeLatLong'] : false ;
		if(isset($filters['args']['customDate']) && $filters['args']['customDate'] != '' ){
			$filterArgs['customDate'] = !empty($filters['args']['customDate']) ? date('Y-m-d',strtotime($filters['args']['customDate'])) : $filterArgs['customDate']; 
		}

		if(isset($filters['args']['vStatus'])){	
			if( is_array($filters['args']['vStatus']) && count($filters['args']['vStatus']) > 0){
				$filterArgs['eventType']  = array();
				foreach ($filters['args']['vStatus'] as $key => $value) {
					switch ($value['key']) {
						case 'IDLE'		: 	$filterArgs['eventType'] = array_merge($filterArgs['eventType'], array('IGON','STOP')); break;
						case 'SPEED'	: 	$filterArgs['eventType'] = array_merge($filterArgs['eventType'], array('')); break;
						case 'TRAVEL'	: 	$filterArgs['eventType'] = array_merge($filterArgs['eventType'], array('START','MOVING')); break;
						case 'IGOFF'	: 	$filterArgs['eventType'] = array_merge($filterArgs['eventType'], array('IGOFF')); break;
						case 'PTO_OFF'	: 	$filterArgs['eventType'] = array_merge($filterArgs['eventType'], array('DEVICEIO')); break; //IN4HI, IN4LO
						case 'PTO_ON'	: 	$filterArgs['eventType'] = array_merge($filterArgs['eventType'], array('')); break;
					}
				}
			} 
		}

		if(isset($filters['args']['vehicles'])){
			if( is_array($filters['args']['vehicles']) && count($filters['args']['vehicles']) > 0){
				$filterArgs['deviceID']  = array();
				foreach ($filters['args']['vehicles'] as $key => $value) {
					array_push($filterArgs['deviceID'], $value['tracker_id']);
				}
			}
		}
		$response['column_mappings'] = array("Vehicle ID", "Truck No", "Driver Name", "Timestamp", "Event");
		
		if($filterArgs['includeLatLong']){
			$response['column_mappings'] = array_merge($response['column_mappings'], array("Latitude", "Longitude"));
		}

		$response['column_mappings'] = array_merge($response['column_mappings'], array("Location", "Speed (MPH)", "Odometer(Mi)"));
		
		if($args) {
			$filterArgs['sortColumn']   = "GMTTime";
			$filterArgs['sortType']     = "ASC";
			$result 					 = $this->Report->getBreadcrumbsDetail($filterArgs , false, 'others');		
		} else {
			
			$filterArgs['itemsPerPage'] = (isset($filters['itemsPerPage']) && !empty($filters['itemsPerPage']) ) ? $filters['itemsPerPage'] : $this->paginationLimit;
			$filterArgs['pageNo'] 		= (isset($filters['pageNo']) && !empty($filters['pageNo'])) ? $filters['pageNo'] : 0;
			$filterArgs['searchQuery'] 	= (isset($filters['searchQuery']) && !empty($filters['searchQuery']) ) ? $filters['searchQuery'] : '';
			$filterArgs['sortColumn'] 	= (isset($filters['sortColumn']) && !empty($filters['sortColumn']) ) ? $filters['sortColumn'] : 'GMTTime';
			$filterArgs['sortType'] 	= (isset($filters['sortType']) && !empty($filters['sortType']) ) ? $filters['sortType'] : 'ASC';
			$filterArgs["limitStart"] 	= ( $filterArgs["pageNo"] * $filterArgs["itemsPerPage"] + 1 );

			$result 					 = $this->Report->getBreadcrumbsDetail($filterArgs , false);		
			$totalRows 					 = $this->Report->getBreadcrumbsDetail($filterArgs , TRUE);
			$response['total'] 		 	 = $totalRows[0]['totalRows'];
		}
		
		
		$response['result'] 		 = $result;
		$response['args'] 			 = $filterArgs;
		$response['eventType'] 		 = $filters['args']['vStatus'];
		if($args){
			return $response;
		}
		echo json_encode($response);
	}

	public function export_pdf_irp_breadcrumb_detail(){
		
		$filters 			= json_decode(file_get_contents('php://input'),true);
		$response 			= $this->irp_breadcrumb_detail($filters);
		$response['report'] = $filters['report'];
		$response['byuser'] = $this->session->loggedUser_username;
		$html 				= $this->load->view('report', $response, true); 
		$pathGen 			= str_replace('application/', '', APPPATH);
		$fileName 			= $filters['report']['name'].'.pdf';
		$pdfFilePath 		= $pathGen."assets/uploads/reports/".$fileName;

		if(file_exists($pdfFilePath)){
			unlink($pdfFilePath);
		}
 		
 		$pdf = $this->load->library('m_pdf');
		$pdf = new mPDF('', array(250,236),5, '', 5, 5, 5, 5, 5, 5);
		
		$pdf->cacheTables = true;
		$pdf->simpleTables=true;
		$pdf->packTableData=true;
		$pdf->WriteHTML($html);
		$output = $pdf->Output($pdfFilePath, "F");    
		echo json_encode(array("output"=>$fileName));
	}


	public function export_html_irp_breadcrumb_detail(){
		$filters 			= $_REQUEST;
		$response 			= $this->irp_breadcrumb_detail($filters);
		$response['report'] = $filters['report'];
		$response['byuser'] = $this->session->loggedUser_username;
		//echo date('Y-m-d H:i:s'); 
		$html = $this->load->view('report', $response, true); 
		echo $html;
	}


	public function export_csv_irp_breadcrumb_detail(){
		$filters 			= json_decode(file_get_contents('php://input'),true);
		$response 			= $this->irp_breadcrumb_detail($filters);
		$response['report'] = $filters['report'];
		$response['byuser'] = $this->session->loggedUser_username;
		echo json_encode($response);
		
	}


	// Load Performance/Tracking Report Functions Begin
	public function irp_loads_performance( $args = false, $csv = false){

		$result = array();
		if($args){
			$filters = $args;
		}else{
			$filters = json_decode(file_get_contents('php://input'),true);	
		}

		$filterArgs = array();

		if ( isset($filters['args']['startDate']) && $filters['args']['startDate']  != '' ) {
			$filterArgs['startDate'] = date('Y-m-d',strtotime($filters['args']['startDate']));
			$filterArgs['endDate']   = date('Y-m-d',strtotime($filters['args']['endDate']));
		} else {
			$filterArgs['startDate'] = '';
			$filterArgs['endDate']   = '';
		}
		$filterArgs["selScope"] = false;
		if(isset($filters["args"]["selScope"]) && count($filters) > 0){
			$filterArgs["selScope"] = $filters["args"]["selScope"]; 
		}

		if(isset($filters["args"]["scope"])){
			$filterArgs["scope"] = $filters["args"]["scope"]; 	
		}

		if(isset($filters["args"]["dispId"])){
			$filterArgs["dispId"] = $filters["args"]["dispId"]; 		
		}

		if(isset($filters["args"]["driverId"])){
			$filterArgs["driverId"] = $filters["args"]["driverId"]; 		
		}

		$filterArgs["secondDriverId"] = isset($filters["args"]["secondDriverId"]) ? $filters['args']['secondDriverId'] : 0; 		
		
		if(isset($filters["args"]["reportType"])){
			$iscope = "all";
			switch ($filterArgs["scope"]) {
				case 'all'		 : $iscope = "dispatchers";break;
				case 'dispatcher':
				case 'dispatchers': $iscope = "drivers";break;
				case 'team'		 : $iscope = "team";break;
				case 'driver'	 : $iscope = "loads";break;
			}

			switch ($filters["args"]["reportType"]) {
				case 'individual': if($args){
									$response['column_mappings'] = array( "LOAD #", "DISPATCHER", "DRIVER", "BROKER",  "BOOKED", "TOTAL CHARGES", "PROFIT", "PROFIT %", "MILES", "DEAD MILES", "RPM", "PICKUP DATE", "ORIGIN", "DEL. DATE", "DESTINATION");	
										$result = $this->Report->getLoadsTrackingIndividual($filterArgs,'export');
									} else {
										$response['column_mappings'] = array( "LOAD #", "DISPATCHER", "DRIVER", "BROKER",  "INVOICED AMT", "TOTAL CHARGES", "PROFIT", "PROFIT %", "MILES", "DEAD MILES", "RPM", "PICKUP DATE", "ORIGIN", "DEL. DATE", "DESTINATION");		
									
										// $result = $this->Report->getLoadsTrackingIndividual($filterArgs,$iscope);
										$this->sortPaginationListing($filterArgs, $iscope);
									}
									
									 break;
				case 'performance': $response['column_mappings'] = array("DISPATCHER");

									if($filterArgs["scope"] != "all" && !empty($filterArgs["scope"])  ){
										array_push($response["column_mappings"],"DRIVER");
									}
									$response['column_mappings'] = array_merge($response['column_mappings'],array("INVOICED AMT", "TOTAL CHARGES", "MILES", "DEAD MILES", "PROFIT", "PROFIT %")); 

									$result = $this->Report->getLoadsTrackingAggregate($filterArgs,$iscope); break;

			}
			$filterArgs["reportType"] = $filters["args"]["reportType"];
		}


		$response['args'] = $filterArgs;
		if($args && !$csv){
			$result_f = array();
			if( !empty($result)) {
				foreach ($result as $key => $value) {
					$dispatcher = $value["dispatcher"];

					unset($value["dispatcher"]);
					
					if(isset($value["rmile"]) && $value["miles"] != 0){
						$rmile = number_format((float)($value["invoice"] / $value["miles"]),2);
						$value["rmile"] = money_format('%.2n', (float)$rmile);
					}

					if(isset($value["profitPercent"])){
						$value["profitPercent"] = number_format((float)(($value["profit"]/$value["invoice"]) * 100),2);
					}

					$value["invoice"] = money_format("%.2n", $value["invoice"]);
					$value["charges"] = money_format("%.2n", $value["charges"]);
					$value["profit"] = money_format("%.2n", $value["profit"]);

					unset($value['dispatcherId']);
					unset($value['driverId']);
					unset($value['second_driver_id']);
					unset($value['driverName']);
					unset($value['username']);
					unset($value['driver_type']);

					if ( isset($result[$key]['PickupAddress'])) {
						$value['PickupAddress']		 = trim($value['PickupAddress'].','.$value['OriginCity'].','.$value['OriginState'].','.$value['OriginCountry'],',');
						$value['DestinationAddress']  = trim($value['DestinationAddress'].','.$value['DestinationCity'].','.$value['DestinationState'].','.$value['DestinationCountry'],',');
						unset($value['OriginCity']);
						unset($value['OriginState']);
						unset($value['OriginCountry']);
						unset($value['DestinationCity']);
						unset($value['DestinationState']);
						unset($value['DestinationCountry']);
						$result_f[$dispatcher][$value["driver"]][$key] = $value;
					} else {
						
						if ( isset($args['args']['reportType']) && $args['args']['reportType'] == 'performance' && ($args['args']['scope'] == 'all' || $args['args']['scope'] == '' ) ) {
							$result_f[$dispatcher][] = $value;
						} else {
							$result_f[$dispatcher][$value["driver"]][$key] = $value;
						}						
					}
					
				}
			}
			$response['result_t'] = $result_f;
			return $response;
		}

		
		if ( !empty($result)) {
			$totalInvoices	 	= 0;
			$totalMiles  	    = 0;
			$totalDeadMiles 	= 0;
			$totalCharges 		= 0;
			$totalProfit 		= 0;
			$totalProfitPercent = 0;
			foreach ($result as $key => $value) {
				if(isset($value["rmile"])){
					$rmile = 0;
					if($value["miles"] > 0){
						$rmile = number_format(($value["invoice"] / $value["miles"]),2);
					}
					$result[$key]["rmile"] = money_format('%.2n', (float)$rmile);
				}
				if(isset($value["profitPercent"])){
					$result[$key]["profitPercent"] = number_format((float)(($value["profit"]/$value["invoice"]) * 100),2);
				}

				if ( isset($result[$key]['PickupAddress'])) {
					$result[$key]['PickupAddress']		 = trim($value['PickupAddress'].','.$value['OriginCity'].','.$value['OriginState'].','.$value['OriginCountry'],',');
					$result[$key]['DestinationAddress']  = trim($value['DestinationAddress'].','.$value['DestinationCity'].','.$value['DestinationState'].','.$value['DestinationCountry'],',');
					unset($result[$key]['OriginCity']);
					unset($result[$key]['OriginState']);
					unset($result[$key]['OriginCountry']);
					unset($result[$key]['DestinationCity']);
					unset($result[$key]['DestinationState']);
					unset($result[$key]['DestinationCountry']);
					unset($result[$key]['driver_type']);
				}
				
				unset($result[$key]['dispatcherId']);
				unset($result[$key]['driverId']);
				unset($result[$key]['second_driver_id']);
				unset($result[$key]['driverName']);
				unset($result[$key]['username']);
				unset($result[$key]['driver_type']);

				if ( $filters["args"]["reportType"] == 'performance') {
					$totalInvoices		 += $value["invoice"];
					$totalMiles 		 += $value["miles"];
					$totalDeadMiles		 += $value["deadmiles"];
					$totalCharges	     += $value["charges"];
					$totalProfit         += $value["profit"];
				}

				$result[$key]["invoice"] 	= money_format("%.2n", $value["invoice"]);
				$result[$key]["charges"] 	= money_format("%.2n", $value["charges"]);
				$result[$key]["profit"] 	= money_format("%.2n", $value["profit"]);

				
			}
		} else {
			$result = array();
		}

		if ( $filters["args"]["reportType"] == 'performance') {
			if ( $totalInvoices != 0 )
				$totalProfitPercent = number_format((float)(($totalProfit / $totalInvoices) * 100),2);
			else
				$totalProfitPercent = 0;
			
			$response['totals']['totInvoices']      = money_format("%.2n", $totalInvoices);
			$response['totals']['totMiles']         = $totalMiles;
			$response['totals']['totDeadMiles']     = $totalDeadMiles;
			$response['totals']['totCharges']       = money_format("%.2n", $totalCharges);
			$response['totals']['totProfit']        = money_format("%.2n", $totalProfit);
			$response['totals']['totProfitPercent'] = $totalProfitPercent;
		}

		$response['result'] 	= $result;
		$response['eventType'] 	= $filters['args']['vStatus'];
		echo json_encode($response);
		exit(0);

	}


	public function export_html_irp_loads_performance(){
		$filters 			= $_REQUEST;
		$response 			= $this->irp_loads_performance($filters);
		$response['report'] = $filters['report'];
		$response['byuser'] = $this->session->loggedUser_username;
		//echo date('Y-m-d H:i:s'); 
		$html 				= $this->load->view('report_load_tracking', $response, true); 
		echo $html;
	}

	public function export_pdf_irp_loads_performance(){
		set_time_limit(500);
		$filters 			= json_decode(file_get_contents('php://input'),true);
		$response 			= $this->irp_loads_performance($filters);
		$response['report'] = $filters['report'];
		$response['byuser'] = $this->session->loggedUser_username;
		$html 				= $this->load->view('report_load_tracking_pdf', $response, true); 
		$pathGen 			= str_replace('application/', '', APPPATH);
		$fileName 			= $filters['report']['name'].'.pdf';
		$pdfFilePath 		= $pathGen."assets/uploads/reports/".$fileName;

		if(file_exists($pdfFilePath)){
			unlink($pdfFilePath);
		}
 		$pdf = $this->load->library('m_pdf');
		$pdf = new mPDF('', array(250,236),5, '', 5, 5, 5, 5, 5, 5);
		$pdf->shrink_tables_to_fit=1;
		$pdf->use_kwt 		= true;
		$pdf->cacheTables 	= true;
		$pdf->simpleTables 	= true;
		$pdf->packTableData = true;
		$pdf->WriteHTML($html);
		$output = $pdf->Output($pdfFilePath, "F");    
		echo json_encode(array("output"=>$fileName));
	}

	public function export_csv_irp_loads_performance(){

		$filters = json_decode(file_get_contents('php://input'),true);
		$response = $this->irp_loads_performance($filters,true);
		$response['report'] = $filters['report'];
		$response['byuser'] = $this->session->loggedUser_username;
		echo json_encode($response);
		
	}
	// Load Performance/Tracking Report Functions End

	/*
	* method  : Get
	* Params  : parameter, columns
	* return  : results array
	* Comment : For implementing pagination on load perfromance rresults
	*/

	private function sortPaginationListing($filterArgs = array(), $iscope = '' , $pageNumber = 0, $search = '', $sortColumn = '', $lastSortType = '' ) {
		$data = array();
		
		$filterArgs['itemsPerPage'] = $this->paginationLimit;
		$filterArgs['limitStart']   = 1;
		$filterArgs['sortColumn']   = "DeliveryDate";
		$filterArgs['sortType']     = "DESC";

		$data['loads'] = $this->Report->getTrackingIndividualLoadsPagination( $filterArgs, $iscope, false );
		$total = $this->Report->getTrackingIndividualLoadsPagination( $filterArgs, $iscope, true );
		$data['total'] =  $total[0]['count'];

		echo json_encode(array('wPagination' => $data));
		exit();
	}

	public function skipAcl_getReportRecords() {
		$params = json_decode(file_get_contents('php://input'),true);
		$data 	= array();
		if($params["pageNo"] < 1){
			$filter["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] + 1);	
		}else{
			$filter["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] );	
		}
		
		$filter['sortColumn'] 	   = ((isset($params["sortColumn"]) && !empty($params["sortColumn"]))) ? $params["sortColumn"]  : "DeliveryDate"; 
		$filter['sortType']   	   = ((isset($params["sortType"]) && !empty($params["sortType"]))) ? $params["sortType"]  : "DESC"; 
		$filter['itemsPerPage']    = ((isset($params["itemsPerPage"]) && !empty($params["itemsPerPage"]))) ? $params["itemsPerPage"]  : "20"; 
		$filter['searchQuery']     = ((isset($params["searchQuery"]) && !empty($params["searchQuery"]))) ? $params["searchQuery"]  : ""; 
		$filter['scope']     	   = ((isset($params['formValue']['scope'])) && !empty($params['formValue']['scope']) ) ? $params['formValue']['scope'] : '';
		$filter['selScope']  	   = ((isset($params['formValue']['selScope'])) && !empty($params['formValue']['selScope']) ) ? $params['formValue']['selScope'] : '';
		$filter['dispId']     	   = ((isset($params['formValue']['dispId'])) && !empty($params['formValue']['dispId']) ) ? $params['formValue']['dispId'] : '';
		$filter['driverId']   	   = ((isset($params['formValue']['driverId'])) && !empty($params['formValue']['driverId']) ) ? $params['formValue']['driverId'] : '';
		$filter['secondDriverId']  = ((isset($params['formValue']['secondDriverId'])) && !empty($params['formValue']['secondDriverId']) ) ? $params['formValue']['secondDriverId'] : '';
		$filter['startDate']  	 = ((isset($params['formValue']["startDate"]) && !empty($params['formValue']["startDate"]))) ? $params['formValue']["startDate"]  : ""; 
		$filter['endDate']    	 = ((isset($params['formValue']["endDate"]) && !empty($params['formValue']["endDate"]))) ? $params['formValue']["endDate"]  : ""; 
		
		$data['loads'] = $this->Report->getTrackingIndividualLoadsPagination( $filter, 'report', false );
		$total = $this->Report->getTrackingIndividualLoadsPagination( $filter, 'report', true );
		$data['total'] =  $total[0]['count'];

		echo json_encode(array('wPagination' => $data));
	}

	
}
 