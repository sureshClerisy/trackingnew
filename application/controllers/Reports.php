<?php

/**
* Reports Controller
*  
*/

class Reports extends Admin_Controller{

	public $userId;
	
	function __construct(){
		parent::__construct();
		$this->userId 		= $this->session->loggedUser_id;
		$this->load->model('Report');
		$this->load->model('Driver');
	}
	
	public function index(){
		$vehicles = $this->Report->getAllVehicles();

		//List of all dispatchers with their drivers
		$allVehicles = $this->Driver->getDriversList(false,false,true);
		$teamList = $this->Driver->getDriversListAsTeam(false,false,true);
		$dispatcherList = $this->Driver->getDispatcherList();
		$vDriversList = array();
		if(!empty($allVehicles) && is_array($allVehicles)){	
			$vDriversList = $allVehicles;
			if(is_array($teamList) && count($teamList) > 0){
				foreach ($teamList as $key => $value) {
					$value["label"] = "_team";
					array_unshift($vDriversList, $value);
				}
			}
			$new = array("id"=>"","profile_image"=>"","driverName"=>"All Groups","label"=>"","username"=>"","latitude"=>"","longitude"=>"","vid"=>"","city"=>"","vehicle_address"=>"","state"=>"");
			foreach ($dispatcherList as $key => $value) {
				array_unshift($vDriversList, $value);
			}
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
		}
		
		$filterArgs = array();
		$filterArgs['customDate'] = date('Y-m-d');
		$filterArgs['includeLatLong'] = isset($filters['args']['includeLatLong']) ?$filters['args']['includeLatLong'] : false ;
		if(isset($filters['args']['customDate'])){
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
		$result = $this->Report->getBreadcrumbsDetail($filterArgs);
		$response['result'] = $result;
		$response['args'] = $filterArgs;
		$response['eventType'] = $filters['args']['vStatus'];
		if($args){
			return $response;
		}
		echo json_encode($response);

	}

	public function export_pdf_irp_breadcrumb_detail(){
		$filters = json_decode(file_get_contents('php://input'),true);
		$response = $this->irp_breadcrumb_detail($filters);
		$response['report'] = $filters['report'];
		$response['byuser'] = $this->session->loggedUser_username;
		$html = $this->load->view('report', $response, true); 
		$pathGen = str_replace('application/', '', APPPATH);
		$fileName = $filters['report']['name'].'.pdf';
		$pdfFilePath = $pathGen."assets/uploads/reports/".$fileName;
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
		$filters = $_REQUEST;
		$response = $this->irp_breadcrumb_detail($filters);
		$response['report'] = $filters['report'];
		$response['byuser'] = $this->session->loggedUser_username;
		//echo date('Y-m-d H:i:s'); 
		$html = $this->load->view('report', $response, true); 
		echo $html;
	}


	public function export_csv_irp_breadcrumb_detail(){
		$filters = json_decode(file_get_contents('php://input'),true);
		$response = $this->irp_breadcrumb_detail($filters);
		$response['report'] = $filters['report'];
		$response['byuser'] = $this->session->loggedUser_username;
		echo json_encode($response);
		
	}


	// Load Performance/Tracking Report Functions Begin
	public function irp_loads_performance($args = false,$csv=false){
		$result = array();
		if($args){
			$filters = $args;
		}else{
			$filters = json_decode(file_get_contents('php://input'),true);	
		}
		
		$filterArgs = array();

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
										$response['column_mappings'] = array( "LOAD #", "DISPATCHER", "DRIVER", "BROKER",  "INVOICED AMT", "TOTAL CHARGES", "PROFIT", "PROFIT %", "MILES", "DEAD MILES", "RPM", "PICKUP DATE", "ORIGIN", "DEL. DATE", "DESTINATION");	
										$result = $this->Report->getLoadsTrackingIndividual($filterArgs,'export');
									}else{
										$response['column_mappings'] = array( "LOAD #", "DISPATCHER", "DRIVER", "BROKER",  "INVOICED AMT", "TOTAL CHARGES", "PROFIT", "PROFIT %", "MILES", "DEAD MILES", "RPM", "PICKUP DATE", "ORIGIN", "DEL. DATE", "DESTINATION");		
										
										$result = $this->Report->getLoadsTrackingIndividual($filterArgs,$iscope);
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
			foreach ($result as $key => $value) {
				$dispatcher = $value["dispatcher"];
				unset($value["dispatcher"]);
				if(isset($value["rmile"])){
					$rmile = number_format((float)($value["invoice"] / $value["miles"]),2);
					$value["rmile"] = money_format('%.2n', (float)$rmile);
				}

				if(isset($value["profitPercent"])){
					$value["profitPercent"] = number_format((float)(($value["profit"]/$value["invoice"]) * 100),2);
				}

				$value["invoice"] = money_format("%.2n", $value["invoice"]);
				$value["charges"] = money_format("%.2n", $value["charges"]);
				$value["profit"] = money_format("%.2n", $value["profit"]);

				$result_f[$dispatcher][$value["driver"]][$key] = $value;
			}
			$response['result_t'] = $result_f;
			//pr($response);die;
			return $response;
		}

		
		
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
			$result[$key]["invoice"] = money_format("%.2n", $value["invoice"]);
			$result[$key]["charges"] = money_format("%.2n", $value["charges"]);
			$result[$key]["profit"] = money_format("%.2n", $value["profit"]);
		}
		

		
		$response['result'] = $result;
		$response['eventType'] = $filters['args']['vStatus'];
		echo json_encode($response);
		exit(0);

	}


	public function export_html_irp_loads_performance(){
		$filters = $_REQUEST;
		$response = $this->irp_loads_performance($filters);
		$response['report'] = $filters['report'];
		$response['byuser'] = $this->session->loggedUser_username;
		//echo date('Y-m-d H:i:s'); 
		$html = $this->load->view('report_load_tracking', $response, true); 
		echo $html;
	}

	public function export_pdf_irp_loads_performance(){
		$filters = json_decode(file_get_contents('php://input'),true);
		$response = $this->irp_loads_performance($filters);
		$response['report'] = $filters['report'];
		$response['byuser'] = $this->session->loggedUser_username;
		$html = $this->load->view('report_load_tracking_pdf', $response, true); 
		//echo $html;die;
		$pathGen = str_replace('application/', '', APPPATH);
		$fileName = $filters['report']['name'].'.pdf';
		$pdfFilePath = $pathGen."assets/uploads/reports/".$fileName;
		if(file_exists($pdfFilePath)){
			unlink($pdfFilePath);
		}
 		$pdf = $this->load->library('m_pdf');
		$pdf = new mPDF('', array(250,236),5, '', 5, 5, 5, 5, 5, 5);
		$pdf->shrink_tables_to_fit=1;
		$pdf->use_kwt = true;
		$pdf->cacheTables = true;
		$pdf->simpleTables=true;
		$pdf->packTableData=true;
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



		
}
