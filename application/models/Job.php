<?php

class Job extends Parent_Model {

	public $limit_per_page;
	
	function __construct() {
		parent::__construct();
		$this->load->library('email');
		$this->limit_per_page = $this->config->item('limit_per_page');  
		
	}

	public function count_all_records( $loggedUserId = null, $vehicleId = null ) {
		if( !empty($loggedUserId) ) {
			$condition = array( 'loads.user_id' => $loggedUserId, 'loads.vehicle_id' => $vehicleId );
			$this->db->where($condition);
		}
		$num_rows = $this->db->count_all_results('loads');
		return $num_rows;
	}

	/**
	 * Fetching loads for listing
	 */
	 
    public function fetchSavedJobs( $loggedUserId = null, $vehicleId = null, $scopeType = '') {				// source for custom added vika dispatch
		
        $data =  $condition = array();
        $this->db->select('CONCAT( d.first_name ," + " ,team.first_name) AS teamdriverName, CONCAT(d.first_name," ", d.last_name) as driverName, loads.driver_type, loads.id, loads.invoiceNo, loads.vehicle_id, loads.truckstopID,loads.Bond,loads.PointOfContactPhone,loads.equipment_options,loads.LoadType,loads.PickupDate,loads.DeliveryDate,loads.OriginCity,loads.OriginState,loads.DestinationCity,loads.DestinationState,loads.PickupAddress,loads.DestinationAddress,loads.PaymentAmount,loads.Mileage,loads.deadmiles,loads.Weight,loads.Length,loads.JobStatus,loads.totalCost,loads.pickDate,loads.load_source,broker_info.TruckCompanyName as companyName');
		
		$this->db->join("vehicles as v", "loads.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "v.driver_id = d.id","Left");
		$this->db->join("users as u", "d.user_id = u.id","Left");
		$this->db->join('broker_info', 'broker_info.id = loads.broker_id','Left');
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		if($vehicleId){
			$this->db->where_in('loads.vehicle_id',$vehicleId);
		}
		if($scopeType == "team"){
			$this->db->where('loads.driver_type',"team");	
		}else if($scopeType == "driver"){
			$this->db->where("(loads.driver_type = '' OR loads.driver_type IS NULL || loads.driver_type = 'driver')");	
		}
		$this->db->where('delete_status',0);
		
		if ( $this->session->userdata('role') != 3 && $this->session->userdata('role') != 1 ) {
			if( !empty($loggedUserId) && $scopeType == 'dispatcher') 
				$this->db->or_where('loads.user_id',$loggedUserId);
				$this->db->where('delete_status',0);	
		}
		
		$this->db->order_by("loads.PickupDate",'ASC');
		$query = $this->db->get('loads');
		//echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}
	
	public function fetchSavedJobsNew( $loggedUserId = null, $vehicleId = null, $scopeType = '', $dispatcherId = null, $driverId = null, $startDate = '', $endDate = '',$filters = array() ) {
		
		if(count($filters) <= 0){
			$filters = array("itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC","status"=>"");
		}


		$data =  $condition = array();
        $this->db->select('CONCAT( d.first_name ," + " ,team.first_name) AS teamdriverName, 
        					CASE loads.driver_type 
        						WHEN "team" THEN CONCAT(d.first_name," + ",team.first_name) 
        									ELSE concat(d.first_name," ",d.last_name) 
        									END AS driverName, loads.driver_type, loads.id, loads.invoiceNo, loads.vehicle_id, loads.truckstopID,loads.Bond,broker_info.PointOfContactPhone,loads.equipment_options,loads.LoadType,loads.PickupDate,loads.DeliveryDate,loads.OriginCity,loads.OriginState,loads.DestinationCity,loads.DestinationState,loads.PickupAddress,loads.DestinationAddress,loads.PaymentAmount,loads.Mileage,loads.deadmiles,loads.Weight,loads.Length,loads.JobStatus,loads.totalCost,loads.pickDate,loads.load_source,broker_info.TruckCompanyName as companyName');
		
		$this->db->join("vehicles as v", "loads.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "loads.driver_id = d.id","Left");
		$this->db->join('broker_info', 'broker_info.id = loads.broker_id','Left');
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		
		if($scopeType == "team") {
			if($vehicleId){
				$this->db->where_in('loads.vehicle_id',$vehicleId);
			}			
			$this->db->where('loads.driver_type',"team");	
			$this->db->where('loads.dispatcher_id',$dispatcherId);
		} else if ($scopeType == "driver"){
			if($vehicleId){
				$this->db->where_in('loads.vehicle_id',$vehicleId);
			}
			$this->db->where("(`loads`.`driver_type` = '' OR `loads`.`driver_type` IS NULL || `loads`.`driver_type` = 'driver')");	
			$this->db->where('loads.dispatcher_id',$dispatcherId);
			$this->db->where('loads.driver_id',$driverId);
		} else if ( $scopeType == 'dispatcher' ) {
			$this->db->where('loads.dispatcher_id',$dispatcherId);
		}
		
		if ( $startDate != '' && $startDate != 'undefined' ) {
			$startDate = date('Y-m-d',strtotime($startDate)); 
			$endDate = date('Y-m-d',strtotime($endDate)); 
			$string = " (`loads`.`PickupDate` >='".$startDate."' AND `loads`.`PickupDate` <= '".$endDate."')";
			$this->db->where($string);
		}

		if(!empty($filters["status"])){
			$this->db->where('loads.JobStatus',$filters["status"]);
		}
		
		//~ if ( $this->session->userdata('role') != 3 && $this->session->userdata('role') != 1 ) {
			//~ if( !empty($loggedUserId) && $scopeType == 'dispatcher') 
				//~ $this->db->or_where('loads.user_id',$loggedUserId);
		//~ }
		
		$this->db->where('delete_status',0);

		if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){

            $this->db->group_start();
            $this->db->like('LOWER(CONCAT(d.first_name," ", d.last_name))', 		strtolower($filters['searchQuery']));
            $this->db->or_like('loads.id', $filters['searchQuery'] );
            $this->db->or_like('loads.invoiceNo', $filters['searchQuery'] );
            $this->db->or_like('broker_info.PointOfContactPhone', $filters['searchQuery'] );
            $this->db->or_like('LOWER(loads.equipment_options)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.LoadType)', strtolower($filters['searchQuery']) );
            $this->db->or_like('loads.PickupDate', $filters['searchQuery'] );
            $this->db->or_like('loads.DeliveryDate', $filters['searchQuery'] );
            $this->db->or_like('LOWER(loads.OriginCity)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.OriginState)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationCity)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationState)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.PickupAddress)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationAddress)', strtolower($filters['searchQuery']) );
            $this->db->or_like('loads.PaymentAmount', $filters['searchQuery']);
            $this->db->or_like('loads.Mileage', $filters['searchQuery']);
            $this->db->or_like('loads.deadmiles', $filters['searchQuery']);
            $this->db->or_like('loads.Weight', $filters['searchQuery']);
            $this->db->or_like('loads.Length', $filters['searchQuery']);
            $this->db->or_like('LOWER(loads.JobStatus)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.load_source)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($filters['searchQuery']) );
            //$this->db->or_like('LOWER(CONCAT( d.first_name ," + " ,team.first_name))', strtolower($filters['searchQuery']));
            $this->db->group_end();
		}
		$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
		$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);

		$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);


		$query = $this->db->get('loads');
		//~ echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	public function fetchSavedJobsTotal( $loggedUserId = null, $vehicleId = null, $scopeType = '', $dispatcherId = null, $driverId = null, $startDate = '', $endDate = '',$filters = array() ) {
		if(count($filters) <= 0){
			$filters = array("status"=>"");
		}		
		$data =  $condition = array();
        $this->db->select('count(loads.id) as total');
		$this->db->join("vehicles as v", "loads.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "loads.driver_id = d.id","Left");
		$this->db->join('broker_info', 'broker_info.id = loads.broker_id','Left');
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		
		if($scopeType == "team") {
			if($vehicleId){
				$this->db->where_in('loads.vehicle_id',$vehicleId);
			}			
			$this->db->where('loads.driver_type',"team");	
			$this->db->where('loads.dispatcher_id',$dispatcherId);
		} else if ($scopeType == "driver"){
			if($vehicleId){
				$this->db->where_in('loads.vehicle_id',$vehicleId);
			}
			$this->db->where("(loads.driver_type = '' OR loads.driver_type IS NULL || loads.driver_type = 'driver')");	
			$this->db->where('loads.dispatcher_id',$dispatcherId);
			$this->db->where('loads.driver_id',$driverId);
		} else if ( $scopeType == 'dispatcher' ) {
			$this->db->where('loads.dispatcher_id',$dispatcherId);
		}
		
		if ( $startDate != '' && $startDate != 'undefined' ) {
			$startDate = date('Y-m-d',strtotime($startDate)); 
			$endDate = date('Y-m-d',strtotime($endDate)); 
			$string = " (`PickupDate` >='".$startDate."' AND `PickupDate` <= '".$endDate."')";
			$this->db->where($string);
		}
		
		if(!empty($filters["status"])){
			$this->db->where('loads.JobStatus',$filters["status"]);
		}

		$this->db->where('delete_status',0);
		if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){

            $this->db->group_start();
            $this->db->like('LOWER(CONCAT( d.first_name ," + " ,team.first_name))', strtolower($filters['searchQuery']));
            $this->db->or_like('LOWER(CONCAT(d.first_name," ", d.last_name))', 		strtolower($filters['searchQuery']));
            $this->db->or_like('loads.id', $filters['searchQuery'] );
            $this->db->or_like('loads.id', $filters['searchQuery'] );
            $this->db->or_like('loads.invoiceNo', $filters['searchQuery'] );
            $this->db->or_like('broker_info.PointOfContactPhone', $filters['searchQuery'] );
            $this->db->or_like('LOWER(loads.equipment_options)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.LoadType)', strtolower($filters['searchQuery']) );
            $this->db->or_like('loads.PickupDate', $filters['searchQuery'] );
            $this->db->or_like('loads.DeliveryDate', $filters['searchQuery'] );
            $this->db->or_like('LOWER(loads.OriginCity)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.OriginState)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationCity)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationState)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.PickupAddress)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationAddress)', strtolower($filters['searchQuery']) );
            $this->db->or_like('loads.PaymentAmount', $filters['searchQuery']);
            $this->db->or_like('loads.Mileage', $filters['searchQuery']);
            $this->db->or_like('loads.deadmiles', $filters['searchQuery']);
            $this->db->or_like('loads.Weight', $filters['searchQuery']);
            $this->db->or_like('loads.Length', $filters['searchQuery']);
            $this->db->or_like('LOWER(loads.JobStatus)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.load_source)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($filters['searchQuery']) );
            $this->db->group_end();
		}
		
		$query = $this->db->get('loads');
		if ($query->num_rows() > 0) {
			return $query->row_array()["total"];
		} else {
			return 0;
		}
	}

	public function fetchLoadsSummary( $loggedUserId = null, $args = null, $driverType) {
        $data = array();
        $this->db->select('JobStatus, COUNT( JobStatus ) as tnum');
  		if( !empty($loggedUserId) ) {
			$condition['loads.user_id'] =  $loggedUserId ;
			$this->db->where($condition);
		}
		if($driverType == "_idispatcher"){
			$this->db->where_in('loads.dispatcher_id',$args["dispId"]);
		}else if($driverType == "_idriver" || $driverType == "driver" || $driverType == "_iteam" || $driverType == "team"){
			if(isset($args["selScope"]) && is_array($args["selScope"])){
				$this->db->where_in('loads.vehicle_id',$args["selScope"]);	
			}else if(!empty($args["selScope"])){
				$this->db->where_in('loads.vehicle_id',$args);	
			}
			$this->db->where_in('loads.dispatcher_id',$args["dispId"]);
			$this->db->where_in('loads.driver_id',$args["driverId"]);

		}


		if(isset($args["startDate"]) && !empty($args["startDate"])){
			$this->db->where("DATE(loads.PickupDate) >= ",date("Y-m-d",strtotime($args["startDate"])));
		}

		if(isset($args["endDate"]) && !empty($args["endDate"])){
			$this->db->where("DATE(loads.PickupDate) <= ",date("Y-m-d",strtotime($args["endDate"])));
		}


		$this->db->where_in('loads.JobStatus',array("inprogress","booked","delivered"));
		if($driverType == "team" || $driverType == "_team" || $driverType == "_iteam"){
			$this->db->where('loads.driver_type = ',"team");
		}
		$this->db->where('delete_status',0);
		
		$this->db->group_by('JobStatus');

		$query = $this->db->get('loads');
		//echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}


	/**
	 * Fetching loads count that have generated their invoice
	 */

	public function getInvoiceCount($args = null, $driverType) {
        $data = array();
        
		$inarray = $this->db->distinct()->select('documents.load_id')->where('doc_type','invoice')->get('documents')->result_array();
		$in_aray = array('');
		if ( !empty($inarray) ) {
			$in_aray = array();
			foreach( $inarray as $inarr ) {
				array_push($in_aray, $inarr['load_id']);
			}
		}
		
		$this->db->select('COUNT( loads.invoiceNo ) as invoices'); 
  		
		if($driverType == "_idispatcher"){
			$this->db->where_in('loads.dispatcher_id',$args["dispId"]);
		}else if($driverType == "_idriver" || $driverType == "driver" || $driverType == "_iteam" || $driverType == "team"){
			if(isset($args["selScope"]) && is_array($args["selScope"])){
				$this->db->where_in('loads.vehicle_id',$args["selScope"]);	
			}else if(!empty($args["selScope"])){
				$this->db->where_in('loads.vehicle_id',$args);	
			}
			$this->db->where_in('loads.dispatcher_id',$args["dispId"]);
			$this->db->where_in('loads.driver_id',$args["driverId"]);
		}

		if(isset($args["startDate"]) && !empty($args["startDate"])){
			$this->db->where("DATE(loads.PickupDate) >= ",date("Y-m-d",strtotime($args["startDate"])));
		}

		if(isset($args["endDate"]) && !empty($args["endDate"])){
			$this->db->where("DATE(loads.PickupDate) <= ",date("Y-m-d",strtotime($args["endDate"])));
		}

		if($driverType == "team" || $driverType == "_team" || $driverType == "_iteam"){
			$this->db->where('loads.driver_type = ',"team");
		}
		
		$this->db->where_in('loads.id', $in_aray);
		$this->db->where(array('loads.sent_for_payment' => 0, 'loads.delete_status' => 0));
		$this->db->order_by('loads.invoicedDate','DESC');

		$query = $this->db->get('loads');
		//echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			return $query->row_array()["invoices"];
		} else {
			return false;
		}
	}


	/**
	 * Fetching loads count that have not uploaded pod or ratesheet documnets
	 */

	public function getWaitingPaperworkCount($args = null, $driverType){
		$this->db->select('count(loads.id) as waitingPaperwork'); 
		$this->db->join('documents',"documents.load_id = loads.id and documents.doc_type NOT IN ('pod','rateSheet') ",'LEFT');
		
 		
		if($driverType == "_idispatcher"){
			$this->db->where_in('loads.dispatcher_id',$args["dispId"]);
		}else if($driverType == "_idriver" || $driverType == "driver" || $driverType == "_iteam" || $driverType == "team"){
			if(isset($args["selScope"]) && is_array($args["selScope"])){
				$this->db->where_in('loads.vehicle_id',$args["selScope"]);	
			}else if(!empty($args["selScope"])){
				$this->db->where_in('loads.vehicle_id',$args);	
			}
			$this->db->where_in('loads.dispatcher_id',$args["dispId"]);
			$this->db->where_in('loads.driver_id',$args["driverId"]);
		}

		if(isset($args["startDate"]) && !empty($args["startDate"])){
			$this->db->where("DATE(loads.PickupDate) >= ",date("Y-m-d",strtotime($args["startDate"])));
		}

		if(isset($args["endDate"]) && !empty($args["endDate"])){
			$this->db->where("DATE(loads.PickupDate) <= ",date("Y-m-d",strtotime($args["endDate"])));
		}

		if($driverType == "team" || $driverType == "_team" || $driverType == "_iteam"){
			$this->db->where('loads.driver_type = ',"team");
		}
		
		$this->db->where(array('loads.sent_for_payment' => 0, 'loads.delete_status' => 0,'loads.ready_for_invoice' => 0));
		
		$query = $this->db->get('loads');
		//echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			return $query->row_array()["waitingPaperwork"];
		} else {
			return false;
		}

	}

	/**
	 * Fetching loads count already sent for payment
	 */
	 
	public function getSentForPaymentCount($args = null, $driverType) {
		$this->db->select('count(loads.id) as sentForPaymentCount');
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
 		
		if($driverType == "_idispatcher"){
			$this->db->where_in('loads.dispatcher_id',$args["dispId"]);
		}else if($driverType == "_idriver" || $driverType == "driver" || $driverType == "_iteam" || $driverType == "team"){
			if(isset($args["selScope"]) && is_array($args["selScope"])){
				$this->db->where_in('loads.vehicle_id',$args["selScope"]);	
			}else if(!empty($args["selScope"])){
				$this->db->where_in('loads.vehicle_id',$args);	
			}
			$this->db->where_in('loads.dispatcher_id',$args["dispId"]);
			$this->db->where_in('loads.driver_id',$args["driverId"]);
		}

		if(isset($args["startDate"]) && !empty($args["startDate"])){
			$this->db->where("DATE(loads.PickupDate) >= ",date("Y-m-d",strtotime($args["startDate"])));
		}

		if(isset($args["endDate"]) && !empty($args["endDate"])){
			$this->db->where("DATE(loads.PickupDate) <= ",date("Y-m-d",strtotime($args["endDate"])));
		}

		if($driverType == "team" || $driverType == "_team" || $driverType == "_iteam"){
			$this->db->where('loads.driver_type = ',"team");
		}
		
		$this->db->where(array('loads.sent_for_payment' => 1, 'loads.delete_status' => 0 ));
		
		$query = $this->db->get('loads');
		//echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			return $query->row_array()["sentForPaymentCount"];
		} else {
			return false;
		}

	}


	/**
	 * Removing Assinged Load from table
	 * 
	 */ 
	
	public function deleteAssignedLoad($loadId = null ) {
		$this->db->where('id', $loadId);
		$data = array(
			'delete_status' => 1
		);
		$this->db->update('loads',$data);
		return true;
	}

	public function FetchSingleJob( $jobId ) {
		$this->db->select('loads.*,concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label) as assignedDriverName, users.username, concat("Truck - ",vehicles.label) as assignedTruckLabel,concat(drivers.first_name," ",drivers.last_name) as assigedDriverFullName, broker_info.MCNumber,broker_info.DOTNumber,broker_info.TruckCompanyName,broker_info.postingAddress,broker_info.CarrierMC,broker_info.city,broker_info.state,broker_info.zipcode');
		$this->db->join('drivers', 'drivers.id = loads.driver_id','Left');
		$this->db->join('vehicles', 'vehicles.id = loads.vehicle_id','Left');
		$this->db->join('broker_info', 'broker_info.id = loads.broker_id','Left');
		$this->db->join('users', 'users.id = loads.dispatcher_id','Left');
		$this->db->where('loads.id', $jobId);
		
		return $this->db->get('loads')->row_array();
	}
	
	/**
	 * Fetching single load details for generating invoice
	 */
	  
	public function FetchSingleJobForInvoice( $jobId ) {
		$this->db->select('loads.invoiceNo,loads.invoicedDate,loads.id,loads.PickupDate,loads.DeliveryDate,loads.woRefno,loads.shipper_name,loads.LoadType,loads.PickupAddress, loads.OriginStreet,loads.OriginCity,loads.OriginState,loads.OriginCountry,loads.OriginZip,loads.shipper_phone,loads.Quantity,loads.PickupDate,loads.Weight,loads.consignee_name, loads.DestinationAddress,loads.DestinationStreet, loads.DestinationCity, loads.DestinationState, loads.DestinationCountry, loads.DestinationZip, loads.consignee_phone, loads.DeliveryDate,loads.PaymentAmount,loads.Stops,broker_info.TruckCompanyName,broker_info.postingAddress,broker_info.city,broker_info.state,broker_info.zipcode');
		$this->db->join('broker_info', 'broker_info.id = loads.broker_id','Left');
		$this->db->where('loads.id', $jobId);
		
		return $this->db->get('loads')->row_array();
	}
	
	/**
	 * Getting broker table mc number for add request show
	 */
	
	public function getBrokerForLoadDetail( $loadId ) {
		$this->db->select('broker_info.MCNumber as mc_number,broker_info.DOTNumber as dot_number,broker_info.TruckCompanyName,broker_info.postingAddress,broker_info.CarrierMC');
		$this->db->join('broker_info', 'broker_info.id = loads.broker_id','LEFT');
		$this->db->where('loads.id', $loadId);		
		$result = $this->db->get('loads');
		if( $result->num_rows() > 0 ) 
			return $result->row_array();
		else
			return array();
	} 
	
	/**
	 * Fetching load detail fields for triumph create Input
	 */
	 
	public function FetchSingleJobCreateInput( $jobId = null ) {
		$this->db->select('loads.invoiceNo,loads.invoicedDate,loads.woRefno,loads.PaymentAmount,loads.OriginCity,loads.OriginState,loads.OriginZip,loads.PickupDate,loads.PickDate,loads.DestinationCity,loads.DestinationState,loads.DestinationZip,loads.DeliveryDate,broker_info.MCNumber,broker_info.TruckCompanyName');
		$this->db->join('broker_info', 'broker_info.id = loads.broker_id','LEFT');
		$this->db->where('loads.id', $jobId);
		
		return $this->db->get('loads')->row_array();
	} 
	
	/**
	 * Fetching only single field from load 
	 */
	 
	public function fetchLoadSingleFieldInfo( $loadId = null , $column = '') {
		if ( $column != '' ) {
			$this->db->where('id', $loadId);
			return $this->db->get('loads')->row()->$column;
		} else {
			return false;
		}
	}
	
	/**
	 * find trip detail info
	 */ 
	 
	public function FindTruckInfo( $tripId = null, $truckstopID = null ) {
		$condition = array('load_id' => $tripId);
		$this->db->where($condition);
		$this->db->order_by('id','desc');
		$result = $this->db->get('trip_details');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	}	
	
	public function FindTruckInfoDetail( $tripId = null, $truckstopID = null ) {
		$condition = array('id' => $tripId, 'truckstopID' => $truckstopID);
		$this->db->where($condition);
		$this->db->order_by('id','desc');
		$result = $this->db->get('trip_details');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	}
			
	public function FetchSingleJobTruckMatch( $jobId ) {
		$this->db->select('id,weight,origin_city,origin_state,origin_country,destination_city,destination_state,destination_country,equipment');
		$this->db->where('id', $jobId);
		$result = $this->db->get('loads');
		if( $result) {
			return $result->row_array();
		} else {
			return false;
		}
	}
	
	public function FetchAllVehicles() {
		return $this->db->get('vehicles')->result_array();
	}
	
	public function FindVehicles( $jobSpec = '', $jobCollect = '', $jobDeliver = '', $jobVehicle = '' ,$fetchAssignedTruck = null , $jobVehicleType = '' ,$jobId = null ,$loggedUserId = null, $jobWidth = 0, $jobLength = 0, $vehicleId = null) {
		
		$jobLength = (int)$jobLength;
		$fianlJobVehicle = array();
		$finalJobSpec = '';
		$jobSpec = trim($jobSpec);
		$truckStatus = 0;
		$vehicleMaxWidth = 8.5;
		if ( $jobId != '' && is_numeric($jobId) ) {
			if ( strpos($jobVehicle, ',') !== false ) {
				$vehicleArray = explode(',',$jobVehicle);
				$vehicleArrayLength = count($vehicleArray);
				for ($i = 0; $i < $vehicleArrayLength; $i++) {
					$fianlJobVehicle[] = $vehicleArray[$i];
				}
			} else {
				$fianlJobVehicle[0] = $jobVehicle;
			}
		} else {
				$equipmentOption = $jobVehicleType;
				$equipmentResult = $this->getRelatedEquipment( $equipmentOption);
				if ( strpos($equipmentResult, ',') !== false ) {
					$vehicleArray = explode(',',$equipmentResult);
					$vehicleArrayLength = count($vehicleArray);
					for ($i = 0; $i < $vehicleArrayLength; $i++) {
						$fianlJobVehicle[] = $vehicleArray[$i];
					}
				} else {
					$fianlJobVehicle[0] = $equipmentResult;
				}
		}
				
		if ( strpos($jobSpec, 'TL ') !== false && strpos($jobSpec, 'Klbs') !== false) {
			preg_match_all('!\d+!', $jobSpec, $specMatches);
			$finalJobSpec = round($specMatches[0][0] * 1000);
		} else if ( is_numeric($jobSpec) ) {
			if( strlen($jobSpec) <= 2 ) {
				$jobSpec = $jobSpec * 1000;
			}
			$finalJobSpec = $jobSpec;
		} else if ( strpos($jobSpec, 'lbs') !== false ) {
			$jobSpec = str_replace(' ','',$jobSpec);
			preg_match_all('!\d+!', $jobSpec, $specMatches);
			$finalJobSpec = round($specMatches[0][0]);
		}

		$finalJobSpec = (int)$finalJobSpec;
		$finalJobCollect = $jobCollect;
	
		$this->db->select('vehicles.id,vehicles.fuel_consumption,vehicles.destination_address');
		$this->db->join('drivers', 'drivers.id = vehicles.driver_id','LEFT');
			
		$i = 1;
		$string = '';
	
		//~ $string .= " (`cargo_capacity` >= ".$finalJobSpec.")";
		//~ $string .= " (`cargo_bay_l` >= ".$jobLength.")";
		//~ $string .= " AND (`cargo_bay_w` <= ".$vehicleMaxWidth.")";
		
		if ( $vehicleId != '' && $vehicleId != null ) {
			$string .= "`vehicles.id` = ".$vehicleId;	
		}
		
		$this->db->where($string);

		$result = $this->db->get('vehicles');
		//~ echo $this->db->last_query();die;
		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	
	public function FindJobVehicles( $vehicleId = null, $userId = null, $thirdParameter = '' ) {			// third parameter to save job from dispatcher having no truck assigned
		
		$this->db->select('vehicles.id,fuel_consumption,destination_address');
		$this->db->join('drivers', 'drivers.id = vehicles.driver_id','LEFT');
		$condition = array('vehicles.id' => $vehicleId);
		$this->db->where($condition);

		$result = $this->db->get('vehicles');
		//~ echo $this->db->last_query();die;
		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	
	public function assignJob( $vehicleId, $jobId, $saveData = array() ) {
		if ( $jobId != '' ) {
			$this->db->where('id',$jobId);
			$data = array(
				'assigned_truck' => 1,
				'assigned_truck_id' => $vehicleId,
				);
			$res = $this->db->update('loads',$data);
			$last_id = $jobId;
			
			$this->db->where('vehicles.id',$vehicleId);
			$vehData = array(
				'assigned_load_id' => $jobId,
				);
			$this->db->update('vehicles',$vehData);
		} else {
			$this->db->select('id');
			$this->db->where('truckstopID',$saveData['truckstopID']);
			$result = $this->db->get('loads');
			if( $result->num_rows() > 0 ) {
				$finalResult = $result->row_array();
				$primary_key = $finalResult['id'];
				$this->db->where('id',$primary_key);
				$res = $this->db->update('loads',$saveData);
				$last_id = $primary_key;
			} else {
				$res = $this->db->insert('loads',$saveData);
				$last_id = $this->db->insert_id();
			}
			
			$this->db->where('vehicles.id',$vehicleId);
			$vehData = array(
				'assigned_load_id' => $last_id,
				);
			$this->db->update('vehicles',$vehData);
			
		}
		if ($res) {
			return $last_id;
		} else {
			return false;
		}
	}

	public function fetchAssigedTruck ( $jobId = null ) {
		$this->db->select('assigned_truck_id,assigned_truck');
		$whereArray = array('id' => $jobId, 'assigned_truck' => 1 );
		$this->db->where($whereArray);
		$result = $this->db->get('loads');
		if( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}	
	}
	
	/**
	 * Saving Load to database
	 */
	  
	public function update_job( $id=null, $vehicle_id = null, $saveData = array(), $extraStopsArray = array(), $posting_address = '', $posting_phone = '', $broker_rating = '' ){
		$assigned_truckId = '';
		$loggedUserId = $this->session->loggedUser_id;
	
		$saveData['truckstopID'] = $saveData['ID'];
		$saveData['vehicle_id'] = $vehicle_id;
		$equipmentOption = $saveData['EquipmentTypes']['Code'];
		$equipmentResult = $this->getRelatedEquipment( $equipmentOption);
		if ( $equipmentResult != '' ) {
			$saveData['equipment'] = $equipmentResult;
		}
		$saveData['equipment_options'] = $saveData['EquipmentTypes']['Code'];
		if ( strpos($saveData['postingAddress'], ',') !== false ) {
			$postAddArray = explode(',',$saveData['postingAddress']);
		} else {
			$postAddArray = '';
		}
		
		if (is_array($postAddArray) && !empty($postAddArray) ) {
			$saveData['TruckCompanyCity'] = $postAddArray[0];
			$saveData['TruckCompanyState'] = trim($postAddArray[1]);
		} else {
			$saveData['TruckCompanyCity'] = '';
			$saveData['TruckCompanyState'] = '';
		}
			
		/** saving broker info to broker table */
		if ( $saveData['MCNumber'] != '' && $saveData['MCNumber'] != null ) {
			$brokerData = array(
				'TruckCompanyName' => $saveData['TruckCompanyName'],
				'postingAddress' => $saveData['postingAddress'],
				'city' => isset($saveData['city']) ? $saveData['city'] : '',
				'state' => isset($saveData['state']) ? $saveData['state'] : '',
				'zipcode' => isset($saveData['zipcode']) ? $saveData['zipcode'] : '',
				'MCNumber' => $saveData['MCNumber'],
				'CarrierMC' => '',
				'DOTNumber' => $saveData['DOTNumber'],
				'brokerStatus' => @$saveData['brokerStatus'],
				'DebtorKey' => isset($saveData['DebtorKey']) ? $saveData['DebtorKey'] : '',
			);
			
			$this->db->select('id');
			$this->db->where('MCNumber',$saveData['MCNumber']);
			$brokerRes = $this->db->get('broker_info');
			if ( $brokerRes->num_rows() > 0 ) {
				$finalResult = $brokerRes->row_array();
				$broker_id = $finalResult['id'];
				$this->db->where('id',$broker_id);
				$res = $this->db->update('broker_info',$brokerData);
				$brokerLastId = $broker_id;
			} else {
				$this->db->insert('broker_info',$brokerData);
				$brokerLastId = $this->db->insert_id();
			}
		} else {
			$brokerLastId = null;
		}
		
		unset($saveData['HasBonding']);
		unset($saveData['IsDeleted']);
		unset($saveData['TMCNumber']);
		unset($saveData['PostedOn']);
		unset($saveData['postingAddress']);
		unset($saveData['broker_info']);
		unset($saveData['DeletedId']);
		unset($saveData['Distance']);
		unset($saveData['ID']);
		unset($saveData['totalMiles']);
		unset($saveData['timer_distance']);
		unset($saveData['overall_total_rate_mile']);
		unset($saveData['EquipmentOptions']);
		unset($saveData['EquipmentTypes']);
		unset($saveData['PaymentAmount1']);
		unset($saveData['deadMileDistCost']);
		unset($saveData['loadedDistanceCost']);	
		unset($saveData['estimatedFuelCost']);	
		unset($saveData['assignedDriverName']);	
		
		unset($saveData['TruckCompanyName']);
		unset($saveData['MCNumber']);
		unset($saveData['CarrierMC']);
		unset($saveData['DOTNumber']);	
		unset($saveData['brokerStatus']);	
		unset($saveData['TruckCompanyCity']);	
		unset($saveData['TruckCompanyState']);	
		unset($saveData['DebtorKey']);	
		unset($saveData['city']);	
		unset($saveData['state']);	
		unset($saveData['zipcode']);	
		unset($saveData['driverName']);	
		unset($saveData['username']);
		unset($saveData['assignedTruckLabel']);
		unset($saveData['assigedDriverFullName']);
				
		$saveData['broker_id'] = $brokerLastId;
				
		if ($id != '' ) {
			$saveData['updated_record'] = 1;
			$this->db->where('loads.id',$id);
			$res = $this->db->update('loads',$saveData);
			$last_id = $id;
		} else {
			$saveData['load_source'] = 'truckstop.com';
			$this->db->select('id,invoiceNo');
			$where = array('truckstopID' => $saveData['truckstopID'], 'pickDate' => $saveData['pickDate'] );
			$this->db->where($where);
			$result = $this->db->get('loads');
			if( $result->num_rows() > 0 ) {
				$finalResult = $result->row_array();
				$primary_key = $finalResult['id'];
				$this->db->where('loads.id',$primary_key);
				$res = $this->db->update('loads',$saveData);
				$last_id = $primary_key;
			} else {
				$saveData['user_id'] = $loggedUserId;
				$saveData['created'] = date('Y-m-d H:i:s');
				$res = $this->db->insert('loads',$saveData);
				$last_id = $this->db->insert_id();
			}
		}
		
		$extraStopIds = array();
		$this->db->select('extra_stops.id');
		$this->db->where('load_id',$last_id);
		$result = $this->db->get('extra_stops');
		if ( $result->num_rows() > 0 ) {
			foreach( $result->result_array()  as $res ){
				$extraStopIds[] = $res['id'];
			}
		} 
			
		$stopsNo = 0;
		if ( isset($saveData['Stops']) && $saveData['Stops'] > 0 ) {
			if ( !empty($extraStopsArray) ) {
				for( $i = 0; $i < $saveData['Stops']; $i++ ) {
					if ($extraStopsArray['extraStopAddress_'.$i] != '' || $extraStopsArray['extraStopCity_'.$i] != '' || $extraStopsArray['extraStopState_'.$i] != '' || $extraStopsArray['extraStopCountry_'.$i] != '' || $extraStopsArray['extraStopZipCode_'.$i] != '') {
						$extraStopEntity = ''; 
						if( isset($extraStopsArray['extraStopEntity_'.$i]) && !empty($extraStopsArray['extraStopEntity_'.$i]) ) {
							$extraStopEntity = $extraStopsArray['extraStopEntity_'.$i]['key'];
						}	
						
						$data = array(
								'extraStopAddress' => isset($extraStopsArray['extraStopAddress_'.$i]) ? $extraStopsArray['extraStopAddress_'.$i] : '',
								'extraStopCity' => isset($extraStopsArray['extraStopCity_'.$i]) ? $extraStopsArray['extraStopCity_'.$i] : '',
								'extraStopState' => isset($extraStopsArray['extraStopState_'.$i]) ? $extraStopsArray['extraStopState_'.$i] : '',
								'extraStopCountry' => isset($extraStopsArray['extraStopCountry_'.$i]) ? $extraStopsArray['extraStopCountry_'.$i] : '',
								'extraStopDate' => isset($extraStopsArray['extraStopDate_'.$i]) ? $extraStopsArray['extraStopDate_'.$i] : '',
								'extraStopEntity' => $extraStopEntity,
								'extraStopName' => isset($extraStopsArray['extraStopName_'.$i]) ? $extraStopsArray['extraStopName_'.$i] : '',
								'extraStopPhone' => isset($extraStopsArray['extraStopPhone_'.$i]) ? $extraStopsArray['extraStopPhone_'.$i] : '',
								'extraStopTime' => isset($extraStopsArray['extraStopTime_'.$i]) ? $extraStopsArray['extraStopTime_'.$i] : '',
								'extraStopTimeRange' => isset($extraStopsArray['extraStopTimeRange_'.$i]) ? $extraStopsArray['extraStopTimeRange_'.$i] : '',
								'extraStopZipCode' => isset($extraStopsArray['extraStopZipCode_'.$i]) ? $extraStopsArray['extraStopZipCode_'.$i] : '',
								'load_id' => $last_id,
							);
					
						if ( isset($extraStopsArray['id_'.$i]) && $extraStopsArray['id_'.$i] != '' ) {
							$pos = array_search($extraStopsArray['id_'.$i], $extraStopIds);
							unset($extraStopIds[$pos]);
							$this->db->where('extra_stops.id',$extraStopsArray['id_'.$i]);
							$this->db->update('extra_stops',$data);
						} else {
							$this->db->insert('extra_stops',$data);
						}
						$stopsNo++;
					} 
				}
			}
		}
		
		if ( !empty($extraStopIds) ) {
			$extraStopIds = array_values($extraStopIds);
			for( $k = 0; $k < count($extraStopIds); $k++ ) {
				$this->db->where('extra_stops.id',$extraStopIds[$k]);
				$this->db->delete('extra_stops');
			} 
		}
		
		if ( $saveData['Stops'] != $stopsNo ) {
			$stopData = array(
				'Stops' => $stopsNo,
			);
			$this->db->where('id',$last_id);
			$this->db->update('loads',$stopData);
		}
		
		$checkForInvoice = $this->checkRequiredFeildsForInvoice($last_id);				// checking all required fields to generate invoice in parent model
			
		if ($res) {
			return $last_id;
		} else {
			return false;
		}
	}
	
	/**
	 * Fetching dispatcher id to which driver is assigned
	 */
	 
	public function getAssignedDispatcherId($driver_id = null) {
		$dispatcherId = $this->db->where('drivers.id',$driver_id)->get('drivers')->row()->user_id;
		if ( $dispatcherId != '' && $dispatcherId != null )
			return $dispatcherId;
		else
			return 0;
	}
	
	 /**
	 * Updating job status only 
	 */
	
	public function updateStatusOnly( $id =null, $saveData = array() ) {
				
		$data = array();
		if ( $id != '' && $id != null ) {
			$data['JobStatus'] = $saveData['JobStatus'];
			$this->db->where('id',$id);
			$this->db->update('loads',$data);
		return $id;
		} else {
			return true;
		}
	} 
	
	/**
	 * Adding the added load
	 */
	 
	public function save_Job( $saveData = array() , $extraStopsArray = array(), $loadId = null ){
		
		$loggedUserId = $this->session->loggedUser_id;
		
		$saveData['load_source'] = 'Vika Dispatch';
		if ( isset($saveData['equipment_options']) )
			$saveData['equipment'] = $this->getRelatedEquipment( $saveData['equipment_options']);   // for returning equipment name from abbreviation
			
		unset($saveData['totalMiles']);
		unset($saveData['timer_distance']);
		unset($saveData['overall_total_rate_mile']);
		unset($saveData['EquipmentTypes']);
		unset($saveData['PaymentAmount1']);
		unset($saveData['deadMileDistCost']);
		unset($saveData['loadedDistanceCost']);	
		unset($saveData['estimatedFuelCost']);	
		unset($saveData['assignedDriverName']);
		
		unset($saveData['postingAddress']);
		unset($saveData['ID']);
		
		unset($saveData['TruckCompanyName']);
		unset($saveData['MCNumber']);
		unset($saveData['CarrierMC']);
		unset($saveData['DOTNumber']);	
		unset($saveData['brokerStatus']);	
		unset($saveData['TruckCompanyCity']);	
		unset($saveData['TruckCompanyState']);
		unset($saveData['DebtorKey']);
		unset($saveData['city']);
		unset($saveData['state']);
		unset($saveData['zipcode']);
		unset($saveData['driverName']);	
		
		unset($saveData['username']);
		unset($saveData['assignedTruckLabel']);
		unset($saveData['assigedDriverFullName']);
		
		if ( isset($saveData['driver_id']) && $saveData['driver_id'] != '' && $saveData['driver_id'] != 0 && $saveData['vehicle_id'] != '' && $saveData['vehicle_id'] != 0 ) {
			$saveData['dispatcher_id'] = $this->getAssignedDispatcherId($saveData['driver_id']);
		}
					
		if ( $loadId != '' && $loadId != null ) {
			$this->db->select('id,driver_id,dispatcher_id');
			$where = array('loads.driver_id' => $saveData['driver_id'], 'loads.id' => $loadId);
			$this->db->where($where);
			$result = $this->db->get('loads');
			if( $result->num_rows() > 0 ) {
				$finalResult = $result->row_array();
				if ( $finalResult['driver_id'] == $saveData['driver_id'] ) 
					unset($saveData['dispatcher_id']);				
			}			
			$this->db->where('loads.id',$loadId);
			$res = $this->db->update('loads',$saveData);
			$last_id = $loadId;
		} else {
			if ( isset( $saveData['id']) && $saveData['id'] != '' ) {
				$this->db->select('id,driver_id,dispatcher_id');
				$where = array('loads.driver_id' => $saveData['driver_id'], 'loads.id' => $saveData['id']);
				$this->db->where($where);
				$result = $this->db->get('loads');
				if( $result->num_rows() > 0 ) {
					$finalResult = $result->row_array();
					if ( $finalResult['driver_id'] == $saveData['driver_id'] ) 
						unset($saveData['dispatcher_id']);				
				}
				$this->db->where('loads.id',$saveData['id']);
				$res = $this->db->update('loads',$saveData);
				$last_id = $saveData['id'];
			} else {
				$saveData['user_id'] = $loggedUserId;
				$saveData['Entered'] = date('Y-m-d'); 
				$saveData['created'] = date('Y-m-d H:i:s');
				$res = $this->db->insert('loads',$saveData);
				$last_id = $this->db->insert_id();
			}
		}
		
		$extraStopIds = array();
		$this->db->select('extra_stops.id');
		$this->db->where('load_id',$last_id);
		$result = $this->db->get('extra_stops');
		if ( $result->num_rows() > 0 ) {
			foreach( $result->result_array()  as $res ){
				$extraStopIds[] = $res['id'];
			}
		} 
			
		$stopsNo = 0;
		if ( isset($saveData['Stops']) && $saveData['Stops'] > 0 ) {
			if ( !empty($extraStopsArray) ) {
				for( $i = 0; $i < $saveData['Stops']; $i++ ) {
					if ($extraStopsArray['extraStopAddress_'.$i] != '' || $extraStopsArray['extraStopCity_'.$i] != '' || $extraStopsArray['extraStopState_'.$i] != '' || $extraStopsArray['extraStopCountry_'.$i] != '' || $extraStopsArray['extraStopZipCode_'.$i] != '') {
						$extraStopEntity = ''; 
						if( isset($extraStopsArray['extraStopEntity_'.$i]) && !empty($extraStopsArray['extraStopEntity_'.$i]) ) {
							$extraStopEntity = $extraStopsArray['extraStopEntity_'.$i]['key'];
						}	
						
						$data = array(
								'extraStopAddress' => isset($extraStopsArray['extraStopAddress_'.$i]) ? $extraStopsArray['extraStopAddress_'.$i] : '',
								'extraStopCity' => isset($extraStopsArray['extraStopCity_'.$i]) ? $extraStopsArray['extraStopCity_'.$i] : '',
								'extraStopState' => isset($extraStopsArray['extraStopState_'.$i]) ? $extraStopsArray['extraStopState_'.$i] : '',
								'extraStopCountry' => isset($extraStopsArray['extraStopCountry_'.$i]) ? $extraStopsArray['extraStopCountry_'.$i] : '',
								'extraStopDate' => isset($extraStopsArray['extraStopDate_'.$i]) ? $extraStopsArray['extraStopDate_'.$i] : '',
								'extraStopEntity' => $extraStopEntity,
								'extraStopName' => isset($extraStopsArray['extraStopName_'.$i]) ? $extraStopsArray['extraStopName_'.$i] : '',
								'extraStopPhone' => isset($extraStopsArray['extraStopPhone_'.$i]) ? $extraStopsArray['extraStopPhone_'.$i] : '',
								'extraStopTime' => isset($extraStopsArray['extraStopTime_'.$i]) ? $extraStopsArray['extraStopTime_'.$i] : '',
								'extraStopTimeRange' => isset($extraStopsArray['extraStopTimeRange_'.$i]) ? $extraStopsArray['extraStopTimeRange_'.$i] : '',
								'extraStopZipCode' => isset($extraStopsArray['extraStopZipCode_'.$i]) ? $extraStopsArray['extraStopZipCode_'.$i] : '',
								'load_id' => $last_id,
							);
					
						if ( isset($extraStopsArray['id_'.$i]) && $extraStopsArray['id_'.$i] != '' ) {
							$pos = array_search($extraStopsArray['id_'.$i], $extraStopIds);
							unset($extraStopIds[$pos]);
							$this->db->where('extra_stops.id',$extraStopsArray['id_'.$i]);
							$this->db->update('extra_stops',$data);
						} else {
							$this->db->insert('extra_stops',$data);
						}
						$stopsNo++;
					}
				}
			}
		}
		
		if ( !empty($extraStopIds) ) {
			$extraStopIds = array_values($extraStopIds);
			for( $k = 0; $k < count($extraStopIds); $k++ ) {
				$this->db->where('extra_stops.id',$extraStopIds[$k]);
				$this->db->delete('extra_stops');
			} 
		}
		
		if ( @$saveData['Stops'] != $stopsNo ) {
			$stopData = array(
				'Stops' => $stopsNo,
			);
			$this->db->where('id',$last_id);
			$this->db->update('loads',$stopData);
		}
		
		$checkForInvoice = $this->checkRequiredFeildsForInvoice($last_id);				// checking all required fields to generate invoice in parent model
		
		if ($res) {
			return $last_id;
		} else {
			return false;
		}
	} 
	
	/**
	 * Saving broker and shipper information for custom added load
	 */
	
	public function saveBrokerShipperInfo( $brokerData = array(), $loadId = null, $type = null ) {

		if ( $type == 'broker' ) {
			$data = array(
				'postingAddress' => isset($brokerData['postingAddress']) ? $brokerData['postingAddress'] : '',
				'city' => isset($brokerData['city']) ? $brokerData['city'] : '',
				'state' => isset($brokerData['state']) ? $brokerData['state'] : '',
				'zipcode' => isset($brokerData['zipcode']) ? $brokerData['zipcode'] : '',
				'MCNumber' => isset($brokerData['MCNumber']) ? $brokerData['MCNumber'] : 0,
				'CarrierMC' => isset($brokerData['CarrierMC']) ? $brokerData['CarrierMC'] : 0,
				'DOTNumber' => isset($brokerData['DOTNumber']) ? $brokerData['DOTNumber'] : 0,
				'brokerStatus' => $brokerData['brokerStatus'],
			);
			
			$this->db->where('broker_info.id',$brokerData['id']);
			$this->db->update('broker_info',$data);
						
			$loadsData = array(
				'PointOfContact' => isset($brokerData['PointOfContact']) ? $brokerData['PointOfContact'] : '',
				'PointOfContactPhone' => isset($brokerData['PointOfContactPhone']) ? $brokerData['PointOfContactPhone'] : '',
				'TruckCompanyEmail' => isset($brokerData['TruckCompanyEmail']) ? $brokerData['TruckCompanyEmail'] : '',
				'TruckCompanyPhone' => isset($brokerData['TruckCompanyPhone']) ? $brokerData['TruckCompanyPhone'] : '',
				'TruckCompanyFax' => isset($brokerData['TruckCompanyFax']) ? $brokerData['TruckCompanyFax'] : '',
				'broker_id' => $brokerData['id'],
			);
			
			$this->db->where('loads.id',$loadId);
			$this->db->update('loads',$loadsData);
		}
		
		$checkForInvoice = $this->checkRequiredFeildsForInvoice($loadId);				// checking all required fields to generate invoice in parent model
		
		return true;
	}
	 
	/**
	 * Finding Job Invoice Assigned no
	 */
	
	public function findJobInvoiceNo() {
		$this->db->select_max('invoiceNo');
		$result = $this->db->get('loads')->row();  
		return $result->invoiceNo;
	} 
	
	/**
	 * Saving load trip details data to trip_detail table
	 */ 
	public function updateTripDetail($tripDetailId = null , $result = null, $truckstopID = null, $data = array()) {
		$savedata['vehicle_average'] = $data['fuel_consumption'];
		$savedata['diesel_needed'] = $data['gallon_needed'];
		$savedata['avg_cost_diesel'] = str_replace('$','',$data['diesel_rate_per_gallon']);
		$savedata['origin_to_dest'] = $data['originToDestDistDriver'];
		$savedata['deadmiles_dist'] = $data['driver_dead_mile'];
		$savedata['dead_miles_not_paid'] = $data['driver_dead_miles_not_paid'];
		$savedata['dead_head_miles_paid'] = $data['driver_dead_miles_paid'];
		$savedata['pay_for_dead_head_mile'] = $data['driver_pay_for_dead_mile'];
		$savedata['pay_for_miles_cargo'] = str_replace('$','',$data['driver_pay_miles_cargo']);
		$savedata['ifta_taxes'] = str_replace('$','',$data['tax_ifta_tax']);
		$savedata['tarps'] = str_replace('$','',$data['tax_tarps']);
		$savedata['detention_time'] = str_replace('$','',$data['tax_det_time']);
		$savedata['tolls'] = str_replace('$','',$data['tax_tolls']);
		$savedata['truckstopID'] = $truckstopID;
		$savedata['load_id'] = $result;
		
		$savedata['vehicle_average_actual'] = @$data['vehicle_average_actual'];
		$savedata['gallon_needed_actual'] = @$data['gallon_needed_actual'];
		$savedata['avg_cost_diesel_actual'] = str_replace('$','',@$data['avg_cost_diesel_actual']);
		$savedata['origin_to_dest_actual'] = @$data['origin_to_dest_actual'];
		$savedata['deadmiles_dist_actual'] = @$data['driver_dead_mile_actual'];
		$savedata['dead_miles_not_paid_actual'] = @$data['dead_miles_not_paid_actual'];
		$savedata['dead_head_miles_paid_actual'] = @$data['dead_head_miles_paid_actual'];
		$savedata['pay_for_dead_head_mile_actual'] = @$data['pay_for_dead_head_mile_actual'];
		$savedata['pay_for_miles_cargo_actual'] = str_replace('$','',@$data['pay_for_miles_cargo_actual']);
		$savedata['ifta_taxes_actual'] = str_replace('$','',@$data['ifta_taxes_actual']);
		$savedata['tarps_actual'] = str_replace('$','',@$data['tarps_actual']);
		$savedata['detention_time_actual'] = str_replace('$','',@$data['detention_time_actual']);
		$savedata['tolls_actual'] = str_replace('$','',@$data['tolls_actual']);
		
		if ( $tripDetailId  != null && $tripDetailId != '' && $tripDetailId  != 'undefined' ) {
			$this->db->where('id', $tripDetailId);
			$res = $this->db->update('trip_details',$savedata);
			$last_id = $tripDetailId;
		} else {
			$this->db->select('id');
			if ( $truckstopID != '' && $truckstopID != null && $truckstopID != 'undefined' )
				$condition = array('truckstopID' => $truckstopID,'load_id' => $result);
			else
				$condition = array('load_id' => $result);
				
			$this->db->where($condition);
			$result = $this->db->get('trip_details');
			if( $result->num_rows() > 0 ) {
				$finalResult = $result->row_array();
				$primary_key = $finalResult['id'];
				$this->db->where('id',$primary_key);
				$res = $this->db->update('trip_details',$savedata);
				$last_id = $primary_key;
			} else {
				$res = $this->db->insert('trip_details',$savedata);
				$last_id = $this->db->insert_id();
			}
			
		}
		
		if ( $res ) {
			return $last_id;
		} else {
			return false;
		}
			
	}
	
	
	public function getAllStates( $country = '' ) {
		
		$this->db->select('id,code,label');
		if ( $country != '' ) {
			$this->db->where('country',$country);
		} else {
			$this->db->where('country','USA');
		}
		$this->db->order_by('id', 'Desc');
		$result = $this->db->get('states');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	public function getStateLabel( $code = '' ) {
		
		$this->db->select('label');
		$this->db->where('code',$code);
		$result = $this->db->get('states');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	}
	
	public function fetchEquipmentTypes() {
		$this->db->select('id,abbrevation,name');
		$result = $this->db->get('equipment_types');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	
	public function getRelatedEquipment( $equipmentOption = '' ) {
		$this->db->where('abbrevation',$equipmentOption);
		$result = $this->db->get('equipment_types');
		if ($result->num_rows() > 0) {
			return $result->row()->name;
		} else {
			return false;
		}
	}
	
	public function FetchSingleJobMap( $loadId = null ) {
		$this->db->select('origin_city,origin_state,origin_country,destination_city,destination_state,destination_country');
		$this->db->where('id',$loadId);
		$result = $this->db->get('loads');
		if ($result->num_rows() > 0) {
			return $result->row_array();
		} else {
			return false;
		}
		
	}
	
	public function saveTriumphData( $data = array() , $us_dot = null, $mc_number = null ) {
		$savedata['creditResultTypeId'] = $data->creditResultTypeId->creditResultTypeId;
		$savedata['credit_limit'] = $data->creditResultTypeId->creditLimit;
		$savedata['credit_status'] = $data->creditResultTypeId->name;		
		$savedata['company_name'] = $data->companyName;
		$savedata['phone'] = $data->phone;
		$savedata['state'] = $data->state;
		$savedata['city'] = $data->city;
		$savedata['us_dot'] = $us_dot;
		$savedata['mc_number'] = $mc_number;
		
		$result = $this->db->insert('triumph_table',$savedata);
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * check if job exist in db
	 */
	
	public function checkJobExist($truckStopId = null ) {
		$this->db->select('*');
		$where = array('truckStopId' => $truckStopId);
		$this->db->where($where);
		$result = $this->db->get('loads');
		if  ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	}
	 
	public function getNearByTruckStops( $lat,$long,$radius = 5.0 ) {
		//$result = $this->db->query('SELECT store_id, name, address, city, state, zip, phone, fax, tRestaurants, parking_spaces, diesel_lanes, showers, latitude, longitude, distance
		$query = 'SELECT store_id, name, address, city, state, zip, phone, fax, tRestaurants, parking_spaces, diesel_lanes, showers, latitude, longitude, distance
					        FROM (
					             SELECT loc.store_id,
					                    loc.name,
					                    loc.address,
					                    loc.city,
					                    loc.state,
					                    loc.zip,
					                    loc.phone,
					                    loc.fax,
					                    (LENGTH(loc.facilities) - LENGTH(REPLACE(loc.facilities,",","")) + 1) AS tRestaurants,
					                    loc.parking_spaces,
					                    loc.diesel_lanes,
					                    loc.showers,
					                    loc.latitude, loc.longitude,
					                    p.radius,
					                    p.distance_unit
					                             * DEGREES(ACOS(COS(RADIANS(p.latpoint))
					                             * COS(RADIANS(loc.latitude))
					                             * COS(RADIANS(p.longpoint - loc.longitude))
					                             + SIN(RADIANS(p.latpoint))
					                             * SIN(RADIANS(loc.latitude)))) AS distance
					              FROM locations AS loc
					              JOIN (   /* these are the query parameters */
					                    SELECT  '.$lat.'  AS latpoint,  '.$long.' AS longpoint,
					                            '.$radius.' AS radius,      69.0 AS distance_unit
					                ) AS p ON 1=1
					              WHERE loc.latitude
					                 BETWEEN p.latpoint  - (p.radius / p.distance_unit)
					                     AND p.latpoint  + (p.radius / p.distance_unit)
					                AND loc.longitude
					                 BETWEEN p.longpoint - (p.radius / (p.distance_unit * COS(RADIANS(p.latpoint))))
					                     AND p.longpoint + (p.radius / (p.distance_unit * COS(RADIANS(p.latpoint))))
					             ) AS d
					       WHERE distance <= radius
					       ORDER BY distance';
					       $result = $this->db->query($query);
				//echo $query;
		
		if( $result ) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	
	public function getNearByFuelStops( $lat,$long,$radius = 5.0 ) {
		//$result = $this->db->query('SELECT store_id, name, address, city, state, zip, phone, fax, tRestaurants, parking_spaces, diesel_lanes, showers, latitude, longitude, distance
		$query = 'SELECT id, name, address, city, state, zip, phone,   parking_spaces,  showers, atm, repair, store, security, internet, latitude, longitude, distance,fuel_per_gallon_usa
					        FROM (
					             SELECT loc.id,
					                    loc.name,
					                    loc.address,
					                    loc.city,
					                    loc.state,
					                    loc.zip,
					                    loc.phone,
					                    loc.parking_spaces,
					                    loc.showers,
					                    loc.atm,
					                    loc.repair,
					                    loc.store,
					                    loc.security,
					                    loc.internet,

					                    loc.latitude, loc.longitude,
					                    p.radius,
					                    p.distance_unit
					                             * DEGREES(ACOS(COS(RADIANS(p.latpoint))
					                             * COS(RADIANS(loc.latitude))
					                             * COS(RADIANS(p.longpoint - loc.longitude))
					                             + SIN(RADIANS(p.latpoint))
					                             * SIN(RADIANS(loc.latitude)))) AS distance,
					                    loc.fuel_per_gallon_usa
					              FROM  fuel_stops_download AS loc
					              JOIN (   /* these are the query parameters */
					                    SELECT  '.$lat.'  AS latpoint,  '.$long.' AS longpoint,
					                            '.$radius.' AS radius,      69.0 AS distance_unit
					                ) AS p ON 1=1
					              WHERE loc.latitude
					                 BETWEEN p.latpoint  - (p.radius / p.distance_unit)
					                     AND p.latpoint  + (p.radius / p.distance_unit)
					                AND loc.longitude
					                 BETWEEN p.longpoint - (p.radius / (p.distance_unit * COS(RADIANS(p.latpoint))))
					                     AND p.longpoint + (p.radius / (p.distance_unit * COS(RADIANS(p.latpoint))))
					             ) AS d
					       WHERE distance <= radius
					       ORDER BY distance';
					       $result = $this->db->query($query);
				//echo $query;
		
		if( $result ) {
			return $result->result_array();
		} else {
			return false;
		}
	}

	public function saveSessionChain($data,$objPost, $target = false){
		$chainData = array();
		$chainData['truckstopID'] = $data['ID'];
		$chainData['pickup_dates'] = strtolower($data['PickUpDate']) == 'daily' ? date('Y-m-d',strtotime($data['previousDate'])) : date('Y-m-d',strtotime($data['OriginPickupDate']));
		//$chainData['pickup_dates'] = date('Y-m-d',strtotime($data['OriginPickupDate']));
		$chainData['user_id'] = $this->session->loggedUser_id;
		//$chainData['parent'] = $lastParent;
		$objPost['valueArray']['driver_name'] = $data['driver_name'];
		$objPost['valueArray']['nextPickupDate1'] = isset($data['nextPickupDate1']) ? $data['nextPickupDate1'] : '' ;
		$objPost['valueArray']['estimatedTime'] = $data['estimatedTime'];
		$objPost['valueArray']['previousDate'] = $data['previousDate'];
		$objPost['valueArray']['workingHour'] = $data['compWorkingHours'];
		$objPost['valueArray']['OriginDistance'] = isset($data['OriginDistance']) ? $data['OriginDistance'] : '';
		$objPost['valueArray']['driverID'] = $data['driverID'];
		$objPost['valueArray']['totalDrivingHour'] = $data['totalDrivingHour'];
		$objPost['valueArray']['hoursRemaining'] = $data['hoursRemaining'];
		$objPost['valueArray']['hoursRemainingNextDay'] = $data['hoursRemainingNextDay'];
		//$objPost['valueArray']['totalWorkingDays'] = $data['totalWorkingDays'];
		//$objPost['valueArray']['dailyDriving'] = $data['dailyDriving'];
		$objPost['valueArray']['holidayOccured'] = $data['holidayOccured'];
		$objPost['valueArray']['compWorkingHours'] = $data['compWorkingHours'];
		$objPost['valueArray']['dmEstTime'] = $data['dmEstTime'];
		$objPost['valueArray']['skippedWeekdays'] = $data['skippedWeekdays'];
		$objPost['valueArray']['tEstimatedTimeInHours'] = $data['tEstimatedTimeInHours'];
		$chainData['deleted'] = false;

		$chainData['varr'] = $objPost['valueArray'];
		
		$filter = array('user_id'=> $chainData['user_id'],"valid"=>1,"driver_id"=>$data['driverID']);

		if($target){
			$filter['id'] = $target;
		}
		$this->db->select(array('id','chain_data'));
		$gresult = $this->db->get_where('save_chains', $filter);
		$found = $gresult->row_array();
		//echo $this->db->last_query();die;
		if($found){
			$chainExistingData = unserialize($found['chain_data']);
			if(!$this->in_array_r($chainData['truckstopID'], $chainExistingData, true) || (isset($chainExistingData[$objPost['divIndex']]['deleted']) && $chainExistingData[$objPost['divIndex']]['deleted'] == true)){
				if(isset($objPost['divIndex']) && (int)$objPost['divIndex'] >= 0){
					if(isset($chainExistingData[$objPost['divIndex']]['deleted']) && $chainExistingData[$objPost['divIndex']]['deleted']){
						array_splice( $chainExistingData, $objPost['divIndex'], 1, array($chainData));	
					}else{
						array_splice( $chainExistingData, $objPost['divIndex'], 0, array($chainData));
					}
				}else{
					array_push($chainExistingData, $chainData);
				}
				$cdata = array('chain_data' => serialize($chainExistingData));
				$this->db->where('id', $found['id']);
				$this->db->update('save_chains', $cdata);
			}else{

				if(count($chainExistingData) > 0){
					foreach( $chainExistingData as $ichain ) {
						if ($ichain['varr']['ID'] == $chainData['truckstopID'] &&  $ichain['varr']['driverID'] == $chainData['varr']["driverID"] && $ichain['deleted'] == false) {  
						//if ($ichain['varr']['ID'] == $chainData['truckstopID'] && strtolower($ichain['varr']['PickUpDate']) == strtolower($chainData['varr']['PickUpDate']) && $ichain['varr']['OriginDistance'] == $data['OriginDistance'] && $ichain['varr']['driverID'] == $chainData['varr']["driverID"]) {  
							return false;
						}
					}	
				}
				if(isset($objPost['divIndex']) && (int)$objPost['divIndex'] >= 0){
					array_splice( $chainExistingData, $objPost['divIndex'], 0, array($chainData));
				}else{
					array_push($chainExistingData, $chainData);
				}

				$cdata = array('chain_data' => serialize($chainExistingData));
				$this->db->where(array('id'=> $found['id'], "driver_id" => $chainData['varr']["driverID"]));
				$this->db->update('save_chains', $cdata);
			}
		}else{
			$data = array('chain_data' => serialize(array($chainData)),"valid"=>1,"user_id"=>$chainData['user_id'],"driver_id" => $chainData['varr']["driverID"]);
			$this->db->insert('save_chains',$data);
		}
	}

	public function getUnFinishedChain($driverID){
		$this->db->select('*');
		$this->db->where(array('user_id'=>$this->session->loggedUser_id,"valid"=>1,"driver_id"=>$driverID));
		$result = $this->db->get('save_chains');
		if ($result->num_rows() > 0 ) {
			$resultSet = $result->result_array();
			foreach ($resultSet as $key => $value) {
				$resultSet[$key] = unserialize($value['chain_data']);
			}
			return $resultSet;
		} else {
			return false;
		}		
	}

	public function destroyLoadsChain($driverID){
		$this->db->where(array('user_id'=>$this->session->loggedUser_id, "driver_id"=>$driverID));
		$result = $this->db->delete('save_chains');
		if ($result) {
			return true;
		} else {
			return false;
		}
	}

	public function removeElementFromChain($index,$tstopid, $driverID,$chainid=0,$target=false){
		$filter = array('user_id'=>$this->session->loggedUser_id ,"valid"=>1,"driver_id"=>$driverID);
		if($target){
			$filter['id'] = $target;
		}
		$this->db->select(array('id','chain_data'));
		$gresult = $this->db->get_where('save_chains', $filter);
		$found = $gresult->row_array();
		//echo $this->db->last_query();die;
		if($found){
			$chainExistingData = unserialize($found['chain_data']);
			if($this->in_array_r($tstopid ,$chainExistingData)){
				//array_splice($chainExistingData, $index, 1);
				$chainExistingData[$index]['deleted'] = true;
				$cdata = array('chain_data' => serialize($chainExistingData));
				$this->db->where('id', $found['id']);
				$this->db->update('save_chains', $cdata);
			}
		}
	}

	public function in_array_r($needle, $haystack, $strict = false) {
	    foreach ($haystack as $item) {
	        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
	            return true;
	        }
	    }
	    return false;
	}

	public function getRelatedCity( $city = '' ,$country) {
		$this->db->select('*');
		$cnt = "";
		if(!empty($country)){
			$cnt = "country='".$country."' AND ";
		}
		$where = $cnt." (LOWER(city) LIKE LOWER('".$city."%') OR LOWER(state_code) LIKE LOWER('".$city."%'))";
		$this->db->where($where);
		$result = $this->db->get('cities');
		//echo $this->db->last_query();die;
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	
	public function getTruckstopRelatedId( $truckstopId = null ) {
		$this->db->select('id');
		$this->db->where('truckstopID',$truckstopId);
		$result = $this->db->get('loads');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	}
	
	public function fetchVehicleAddress( $vehicleId = null ) {
		$this->db->select('latitude,longitude,state,city,vehicle_address,label');
		$this->db->where('id', $vehicleId);
		$res = $this->db->get('vehicles');
		if ( $res->num_rows() > 0 ) {
			return $res->row_array();
		} else {
			return false;
		}
	}
	
	/**
	 * Removing Extra stop from table
	 * 
	 */ 

	public function removeExtraStop( $extraStopId = null, $loadId = null, $mileage = null , $stops = 0  ) {
		
		if ( $mileage != '' && $mileage != null && $loadId != null ) {
				if ( $stops < 0 )	
					$stops = 0;
					
			$data = array(
				'Mileage' => $mileage,
				'Stops' => $stops
			);
			
			$this->db->where('loads.id', $loadId);
			$this->db->update('loads',$data);
		}
		
		$this->db->where('id', $extraStopId);
		$result = $this->db->delete('extra_stops');
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Fetching added extra stops
	 * 
	 */
	 
	public function getExtraStops( $extraStopId = null ) {
		$this->db->select('*');
		$this->db->where('load_id', $extraStopId);
		$result = $this->db->get('extra_stops');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	} 

	public function insertDocumentEntry($doc_name,$load_id = null , $parameter = '', $docPrimaryId = null ){
		$doc = array();
		$doc['load_id'] = $load_id;
		$doc['doc_name'] = $doc_name;
		$doc['doc_type'] = $parameter;
		$doc['upload_by'] = $this->session->loggedUser_id;
		
		if ( $docPrimaryId != null && $docPrimaryId != '' ) {
			$this->db->where('id', $docPrimaryId);
			$result = $this->db->update('documents',$doc);
		} else {
			$result = $this->db->insert('documents',$doc);
		}
		
		$checkForInvoice = $this->checkRequiredFeildsForInvoice($load_id);				// checking all required fields to generate invoice in parent model
	}

	public function logEvent($type,$message){
		$doc = array();
		$doc['event_type'] = $type;
		$doc['message'] = $message;
		$doc['user_id'] = $this->session->loggedUser_id;
		//$doc['upload_by'] = $this->session->loggedUser_id;
		$result = $this->db->insert('events_log',$doc);
	}	
	
	public function getDocsList($loadId = null , $docType = ''){
		$this->db->select("*, CASE
								WHEN doc_name LIKE '%.doc%'
								THEN 'doc_thumb.png'
								WHEN doc_name LIKE '%.xls%'
								THEN 'xls_thumb.png'
								ELSE CONCAT( SUBSTRING( CONCAT( 'thumb_', REPLACE( doc_name, 'SIGNED_', '' ) ) , 1, CHAR_LENGTH( CONCAT( 'thumb_', REPLACE( doc_name, 'SIGNED_', '' ) ) ) -4 ) , '.jpg' )
								END AS thumb");
		if ( $docType != '' && $docType == 'broker')
			$condition = array('load_id' => $loadId, 'doc_type' => $docType);
		else 
			$condition = array('load_id' => $loadId, 'doc_type !=' => 'broker');
			
		$this->db->where($condition);
		$result = $this->db->get('documents');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	}

	/**
	 * Deleting uploaded doc from db
	 */
	  
	public function deleteDocument($id = null , $loadId = null, $docType = ''){
		$this->db->where('id',$id);
		$this->db->delete('documents');
		
		if ( $loadId != '' && $docType == 'invoice' ) {
			$data['flag'] = 0;
			$this->db->where('loads.id',$loadId);
			$this->db->update('loads',$data);
		}		
		return true;
	}

	/**
	 * Getting bundle document detail for particular load Id
	 */
	
	public function getBundleDocInfo( $loadId = null ) {
		$this->db->select('id,doc_type,doc_name');
		$this->db->where(array('load_id' => $loadId, 'doc_type' => 'bundle'));
		$result = $this->db->get('documents');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return array();
		}	
	}
	 
	public function getDocDetail($id){
		$this->db->select('*');
		$this->db->where(array('id'=>$id));
		$result = $this->db->get('documents');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}	
	}

	public function updateDocFile($id,$name){
		$this->db->where('id',$id);
		$data = array('doc_name' => $name);
		$res = $this->db->update('documents',$data);
		return $res;
	}

	/**
	 * Check if woRefno already exist 
	 */
	
	public function checkWoNumberExist( $woRefno = '' , $primaryId = null ) {
		if ( $primaryId  != null && $primaryId != '' ) {
			$condition = array('woRefno' => $woRefno, 'id !=' => $primaryId, 'delete_status' => 0 );
		} else {
			$condition = array('woRefno' => $woRefno, 'delete_status' => 0);
		}
		
		$result = $this->db->select('id')->where($condition)->get('loads');
		if ( $result->num_rows() > 0 ) {
			$loadIdArr = $result->row_array();
			return $loadIdArr['id'];
		} else {
			return 'true';
		}
	} 

	public function getWidgetsOrder($userId){
		$this->db->select('widget_order');
		$this->db->where('user_id',$userId);
		$res = $this->db->get('dashbord_widgets');
		if ( $res->num_rows() > 0 ) {
			return $res->row_array();
		} else {
			return false;
		}

	}

	public function updateWidgetsOrder($userId,$args,$new = false){
		$data = array();
		if($new){
			$this->db->where('user_id',$userId);
			$data['widget_order'] = $args;
			$res = $this->db->update('dashbord_widgets',$data);
		}else{
			$data['user_id'] = $this->userId;
			$data['widget_order'] = $args;
			$this->db->where('user_id',$userId);
			$res 	 = $this->db->insert('dashbord_widgets',$data);
		}
		return $res;
	}
	
	/**
	 * Checking if Rate sheet is uploaded or not
	 */
	
	public function checkRateSheetUploaded( $loadId = null , $doc_type = '' ) {
		$this->db->select('id');
		$condition = array('load_id' => $loadId, 'doc_type' => $doc_type);
		$this->db->where($condition);
		$result = $this->db->get('documents');
		if ( $result->num_rows() > 0 ) {
			return 'yes';
		} else {
			return 'no';
		}
	} 

	//Return all matched commodities with parameter string
	public function getMatchedCommodities($args){
		$this->db->distinct();
		$this->db->select("commodity");
		$this->db->like('LCASE(commodity)', strtolower($args), 'both');
		$this->db->group_by("commodity");
		$this->db->order_by("commodity");
		$this->db->limit(25);
		$result = $this->db->get('loads');
		//echo $this->db->last_query();die;
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	}

	public function checkIfExists($deviceID){
		$this->db->select('deviceID');
		$this->db->where('deviceID',$deviceID);
		$res = $this->db->get('calamp');
		return ( $res->num_rows() > 0 ) ? true : false;
	}

	public function addData($table,$data,$multiple = false){
		if($multiple){
			$this->db->insert_batch($table,$data);
		}else{
			$this->db->insert($table,$data);
		}
		
	}

	public function getVehicleLogForJob($args){
		date_default_timezone_set('GMT');
		$from = date('Y-m-d H:i:s',strtotime($args['pickupDate']));
		$to = date('Y-m-d H:i:s',strtotime($args['dropDate']));

		//$from = date('Y-m-d H:i:s',strtotime('2016-11-26'));
		//$to = date('Y-m-d H:i:s',strtotime('2016-11-31'));

		$and = " AND GMTTime >='".$from."' AND GMTTime<= '".$to."'";
		$status = " AND avl_events.eventType IN ('STOP','MOVING') ";
		$this->db->select('avl_events.GMTTime, avl_events.eventType, avl_events.latitude, avl_events.longitude')
         ->from('vehicles')
         ->join('avl_events', 'vehicles.tracker_id = avl_events.deviceID'.$and)
         ->where('vehicles.id',$args['vehicleID'])
         ->order_by('avl_events.GMTTime','asc');
		$result = $this->db->get();
		//echo $this->db->last_query();die;
		if ( $result->num_rows() > 0 ){
			return $result->result_array();
		} 
	}


	public function jobLogOnMap($args){
		date_default_timezone_set('GMT');
		$from = date('Y-m-d H:i:s',strtotime($args['pickupDate']));
		$to = date('Y-m-d H:i:s',strtotime($args['dropDate']));
		$and = " AND GMTTime >='".$from."' AND GMTTime<= '".$to."'";
		$status = " AND avl_events.eventType IN ('STOP','IGOFF','IGON') ";
		$this->db->select('avl_events.GMTTime, avl_events.eventType, avl_events.latitude, avl_events.longitude')
         ->from('vehicles')
         ->join('avl_events', 'vehicles.tracker_id = avl_events.deviceID'.$and)
         ->where('vehicles.id',$args['vehicleID']);
		$result = $this->db->get();
		//echo $this->db->last_query();die;
		if ( $result->num_rows() > 0 ){
			return $result->result_array();
		} 
	}
	
	/**
	 * Check if same pick date load already exist
	 */
	 
	public function checkLoadDateExist( $date = '', $parameter = '', $vehicle_id = null, $id = null ) {
		$this->db->select('PickupDate,DeliveryDate,id');
		
		if ( $parameter == 'pick' ) {
			$string = '';
			$string .= " ((`PickupDate` = '".$date."')";
			$string .= " OR (`PickupDate` < '".$date."' AND `DeliveryDate` > '".$date."'))";
			$string .= " AND (`vehicle_id` = ".$vehicle_id.")";
			
			if ( $id != '' && $id != null )
				$string .= " AND (`id` != ".$id.")";
		} else {
			$string = '';
			$string .= " (`PickupDate` < '".$date."' AND `DeliveryDate` > '".$date."')";
			$string .= " AND (`vehicle_id` = ".$vehicle_id.")";
			if ( $id != '' && $id != null )
				$string .= " AND (`id` != ".$id.")";
		}
		
		$string .= " AND (`JobStatus` != 'cancel' ) AND (`delete_status` = 0 )";
		$this->db->where($string);
		$this->db->order_by('id','DESC');
		$result = $this->db->get('loads');
		//~ echo $this->db->last_query(); die;
		if( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return array();
		}
	} 
	
	
	/**
	 *check if zip code exist in db
	 */
	
	public function checkZipCodeExist( $address = '') {
		$data = $this->db->select('zipcode')->from('zip_codes')->where('address',str_replace(' ','~',$address))->get();
		if( $data->num_rows() > 0 ) {
			return $data->row_array();
		} else {
			return array();
		}
	} 
	
	
	public function fetchZipLoad() {
		$this->db->select('PickupAddress,DestinationAddress,id');
		$result = $this->db->get('loads');
		return $result->result_array();		
	}
	
	/**
	 * Saving zip code to table in db
	 */
	 
	public function saveZipCode($dataArray){
		
		$data = $this->db->select('*')->from('zip_codes')
		->where('address',str_replace(' ','~',$dataArray['address']))->get();
		
		if($data->num_rows() < 1){

			$data = $this->db->insert('zip_codes',$dataArray);
			//echo $this->db->last_query();
		}
	}
	
	
	public function saveLoadZip($zip = '', $type = '', $id = null){
		
		if ( $type == 'origin' )
			$data['OriginZip'] = $zip;
		else
			$data['DestinationZip'] = $zip;
		
		$this->db->where('id',$id);
		$this->db->update('loads',$data);
		
	}
	
	/**
	 * check if pod and rate sheet is uploaded if status is completed
	 */
	
	public function checkDocumentUploaded( $loadId = null, $doc_type = '' ) {
		if ( $loadId != '' && $loadId != null ) {
			$this->db->select('id')->where(array('documents.load_id' => $loadId, 'documents.doc_type' => $doc_type));
			$res = $this->db->get('documents');
			if( $res->num_rows() > 0 ) {
				return 'exist';
			} else {
				return 'notexist';
			}
		} else {
			return 'notexist';
		}
	}
	
	/**
	 * Get Driver name and vehicle label
	 */
	
	public function getDriverVehicleNames($id = null, $driverType = '', $loadFrom = '' ) {
		if ( isset($driverType) && $driverType == 'team' ) {
			if ( $loadFrom == 'assignedLoads' )
				$this->db->select('concat(drivers.first_name," + ",team.first_name) as driverName');
			else
				$this->db->select('concat(drivers.first_name," + ",team.first_name,"-",vehicles.label) as driverName');
		} else {
			if ( $loadFrom == 'assignedLoads' )
				$this->db->select('concat(drivers.first_name," ",drivers.last_name) as driverName');
			else
				$this->db->select('concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label) as driverName');
		}
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		
		if ( isset($driverType) && $driverType == 'team' ) 
			$this->db->join('drivers as team','team.id = loads.second_driver_id','LEFT');
			
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		$this->db->where('loads.id',$id);
					
		$result = $this->db->get('loads');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	}  
	 
	public function readyInvoice() {
		$this->db->select('id');
		$result =  $this->db->get('loads');
		foreach($result->result_array() as $res ) {
			$this->checkRequiredFeildsForInvoice($res['id']);
		}
	}
	
}
?>
