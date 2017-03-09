<?php

ini_set('max_execution_time', 300); 

class Jobs extends Admin_Controller {

	public $data = array();
	public $rows = array();
	public $cols = array();
	public $url;
	public $userId;

	function __construct() {
		parent::__construct();
		$this->load->model('User');
		$this->load->model('Job');
		$this->load->library('Htmldom');
		if($this->session->role != 1){
			$this->userId = $this->session->loggedUser_id;
		}
	}

	public function delete($jobId = Null) {
		$result = $this->Job->deleteJob($jobId);
		if ($result == true) {
			echo json_encode(array('success' => true));
		} else {
			echo json_encode(array('success' => true));
		}
	}

	public function matched_jobs( $jobId = null ) {
		$jobRecord = $this->Job->FetchSingleJob( $jobId );
		
		$jobSpec = $jobRecord['weight'];
		$jobCollect = $jobRecord['origin_city'].' '.$jobRecord['origin_state'];
		$jobDeliver = $jobRecord['destination_city'].' '.$jobRecord['destination_state'];
		$jobVehicle = $jobRecord['equipment'];
		
		
		$jobRecord['origin_country'] = 'USA';
		$jobRecord['destination_country'] = 'USA';
		$data['distance'] = $this->GetDrivingDistance($jobRecord['origin_city'].' '.$jobRecord['origin_state'].' '.$jobRecord['origin_country'], $jobRecord['destination_city'].' '.$jobRecord['destination_state'].' '.$jobRecord['destination_country']);
		
		$data['page_title'] = 'Load Details';
		
		$data['jobRecord'] = $jobRecord;
		$data['loadId'] = $jobId;
		$data['color'] = 'match_color';
		$data['page_body'] = $this->load->view('admin/matched_jobs',$data,true);
	
		$new_view = $this->load->view('admin/load_detail',$data,true);
		echo $new_view;
	}
	
	public function fetch_matched_trucks( $jobId = null )  {
		$jobRecord = $this->Job->FetchSingleJobTruckMatch( $jobId );
		
		$jobSpec = $jobRecord['weight'];
		$jobCollect = $jobRecord['origin_city'].' '.$jobRecord['origin_state'];
		$jobDeliver = $jobRecord['destination_city'].' '.$jobRecord['destination_state'];
		$jobVehicle = $jobRecord['equipment'];
		
		
		$data['page_title'] = 'Matching Trucks';
		$data['vehicles_Available'] = $VehiclesArray = $this->Job->FindVehicles( $jobSpec, $jobCollect, $jobDeliver, $jobVehicle );
		
		$i = 0;
		foreach( $data['vehicles_Available'] as $vehicle_Available ) {
			$dest = $this->GetDrivingDistance($vehicle_Available['vehicle_address'].' '.$vehicle_Available['city'].' '.$vehicle_Available['state'], $jobRecord['origin_city'].' '.$jobRecord['origin_state'].' '.$jobRecord['destination_country']);
			
			$VehiclesArray[$i]['distance_array'] = $dest;
			trim($dest['distance']);
			$total_dis = explode(' ',$dest['distance']);
			$VehiclesArray[$i]['distance'] = str_replace(',','',$total_dis[0]);
			
			$i++;
		}
	
		function sort_by_order($a, $b)
		{
			return $a['distance'] - $b['distance'];
		}
		uasort($VehiclesArray, 'sort_by_order');

		$data['vehicles_Available'] = $VehiclesArray;
		$data['total_records'] = count($data['vehicles_Available']);
		$data['jobRecord'] = $jobRecord;
		$data['loadId'] = $jobId;
		$data['color'] = 'match_color';
		$new_view = $this->load->view('admin/matched_trucks',$data,true);
	
		echo $new_view;
	}
	
	/*
	* Method update job information
	* 
	*/
	public function edit( $id = null ){			
		$_POST = json_decode(file_get_contents('php://input'), true);
		$id = $_POST['jobPrimary'];
		$saveData = $_POST['jobRecords'];
		
			$result = $this->Job->update_job($id ,  $saveData);
			if( $result ) {
				echo json_encode(array('success' => true));
			} else {
				echo json_encode(array('success' => false));
			}
	}
	
	/**
	 * Saving load to database
	 * 
	 */ 
	public function edit_live( $id = null ){			
		$_POST = json_decode(file_get_contents('php://input'), true);
		$id = $_POST['jobPrimary'];
		$vehicle_id = $_POST['vehicleId'];
		$extraStopsArray = $_POST['extraStops'];
		$_POST['jobRecords']['PaymentAmount'] = str_replace(',', '',$_POST['jobRecords']['PaymentAmount']);
		$_POST['jobRecords']['PaymentAmount'] = str_replace('$', '',$_POST['jobRecords']['PaymentAmount']);
		$_POST['jobRecords']['PaymentAmount'] = (double)$_POST['jobRecords']['PaymentAmount'];
		
		$_POST['jobRecords']['PaymentAmount1'] = $_POST['jobRecords']['PaymentAmount'];
		$saveData = $_POST['jobRecords']; 
		if(isset($saveData['JobStatus1'])){
			unset($saveData['JobStatus1']);
		}
		if(isset($saveData['Stops1'])){
			unset($saveData['Stops1']);
		}
		$dataN['vehicles_Available'] = $VehiclesArray = $this->Job->FindJobVehicles($_POST['vehicleId'], $this->userId);
			
		if ( isset($saveData['Stops']) && $saveData['Stops'] != '' ) {
			$this->extraStopCharge = $saveData['Stops'] * 25;
		}
		
		
		pr ( $dataN['vehicles_Available']);
		
		$truckInfo = $this->Job->FindTruckInfo( $id, $saveData['ID']);
		pr($truckInfo);
		
		if ( !empty($truckInfo) ) {
			$tripDetailPrId = $truckInfo['id'];
			$this->deadMilePaid = $truckInfo['dead_head_miles_paid'] ;
			$this->deadMileNotPaid = $truckInfo['dead_miles_not_paid'];
			$this->payForDeadMile = $truckInfo['pay_for_dead_head_mile']; 
			$this->driver_pay_miles_cargo = $truckInfo['pay_for_miles_cargo']; 
			$this->iftaTax = $truckInfo['ifta_taxes'];
			$this->tarps = $truckInfo['tarps'];
			$this->det_time = $truckInfo['detention_time'];
			$this->tollsTax = $truckInfo['tolls'];
		} 
				
		if( !empty($VehiclesArray) ) {
			$VehiclesArray = $this->vehicleCalculations( $dataN['vehicles_Available'], $jobRecord['OriginCity'], $jobRecord['OriginState'], $jobRecord['OriginCountry'], $saveData['deadmiles'], $saveData['Mileage'], $saveData['PaymentAmount'], $this->deadMilePaid, $this->deadMileNotPaid, $this->payForDeadMile, $this->driver_pay_miles_cargo, $this->iftaTax, $this->tarps, $this->det_time, $this->tollsTax, $this->extraStopCharge, $truckInfo);
		}
		
		$saveData['deadmiles'] = $VehiclesArray[0]['deadMileDist'];
		$saveData['totalCost'] = $VehiclesArray[0]['overall_total_charge'];
		$saveData['overallTotalProfit'] = $VehiclesArray[0]['overall_total_profit'];
		$saveData['overallTotalProfitPercent'] = $VehiclesArray[0]['overall_total_profit_percent'];
		
		pr($saveData);die;
		$result = $this->Job->update_job($id , $vehicle_id, $saveData, $extraStopsArray);
		echo json_encode(array('id' => $result,'savedData' => $saveData));
	}
	
	public function fetch_job_map( $truckStopId = null , $loadId = null ) {
		$jobRecord = $this->Job->FetchSingleJobMap($loadId);
		
		if (!empty($jobRecord) ) {
			$originCountry = ( $jobRecord['origin_country'] != '' ) ? $jobRecord['origin_country'] : 'USA';
			$destCountry = ( $jobRecord['destination_country'] != '' ) ? $jobRecord['destination_country'] : 'USA';
			
			$data['origin_value'] = $jobRecord['origin_city'].', '.$jobRecord['origin_state'];
			$data['dest_value'] = $jobRecord['destination_city'].', '.$jobRecord['destination_state']; 
		}
		
		echo json_encode($data);
	}
	
	
	

	
}
