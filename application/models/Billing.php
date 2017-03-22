<?php

class Billing extends CI_Model {

	function __construct() {
		parent::__construct();
	}


	/**
	 * Fetching only In progress loads
	 */
	
	public function getFilteredLoads( $filters = array(), $total=false,$startDate="", $endDate="" ) {
		$inarray = $this->db->distinct()->select('documents.load_id')->where('doc_type','invoice')->get('documents')->result_array();
		$in_aray =array('');
		if ( !empty($inarray) ) {
			$in_aray =array();
			foreach( $inarray as $inarr ) {
				array_push($in_aray, $inarr['load_id']);
			}
		}
		
		if(count($filters) <= 0){
			$filters = array("itemsPerPage"=>20, "limitStart"=>0, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC");
		}

		$this->db->select('
		case loads.driver_type
			when "team" then concat(drivers.first_name," + ",team.first_name,"-",vehicles.label)
			ELSE concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label)
		end as driverName,loads.LoadType, loads.PickupDate, loads.PickupAddress, loads.OriginCity, loads.OriginState, loads.DestinationCity, loads.DestinationState, loads.DestinationAddress, loads.PaymentAmount, loads.Mileage, loads.deadmiles, loads.DeliveryDate, loads.JobStatus, loads.truckstopID, loads.id, loads.deadmiles, loads.totalCost, loads.pickDate, loads.invoiceNo, loads.load_source,loads.ready_for_invoice,broker_info.TruckCompanyName,vehicles.id as vehicleID');
		$this->db->join('broker_info','broker_info.id = loads.broker_id','LEFT');
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('drivers as team','team.id = loads.second_driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		
		
			if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){
	            $this->db->group_start();
	            $this->db->like('LOWER(CONCAT(drivers.first_name," ", drivers.last_name))', strtolower($filters['searchQuery']));
	            $this->db->or_like('loads.id', $filters['searchQuery'] );
	            $this->db->or_like('loads.invoiceNo', $filters['searchQuery'] );
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
	            $this->db->or_like('LOWER(loads.JobStatus)', strtolower($filters['searchQuery']) );
	            $this->db->or_like('LOWER(loads.load_source)', strtolower($filters['searchQuery']) );
	            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($filters['searchQuery']) );
	            $this->db->group_end();
			}
		
		

		if(isset($filters["userType"]) && $filters["userType"] == 'team') {
			if(isset($filters["vehicleId"]) && $filters["vehicleId"] != '') {
				$this->db->where_in('loads.vehicle_id',$filters["vehicleId"]);
			}			
			$this->db->where('loads.driver_type',"team");	
			$this->db->where('loads.dispatcher_id',$filters["dispatcherId"]);
		} else if(isset($filters["userType"]) && $filters["userType"] == 'driver') {
			if(isset($filters["vehicleId"]) && $filters["vehicleId"] != '') {
				$this->db->where_in('loads.vehicle_id',$filters["vehicleId"]);
			}
			$this->db->where("(`loads`.`driver_type` = '' OR `loads`.`driver_type` IS NULL || `loads`.`driver_type` = 'driver')");	
			$this->db->where('loads.dispatcher_id',$filters["dispatcherId"]);
			$this->db->where('loads.driver_id',$filters["userId"]);

		} else if(isset($filters["userType"]) && $filters["userType"] == 'dispatcher') {
			if(isset($filters["userId"]) && $filters["userId"] != '') {
				$this->db->where('loads.dispatcher_id',$filters["userId"]);
			}
		}
		

		if(isset($filters["startDate"]) && !empty($filters["startDate"])){
			$this->db->where("DATE(loads.PickupDate) >= ",date("Y-m-d",strtotime($filters["startDate"])));
		}

		if(isset($filters["endDate"]) && !empty($filters["endDate"])){
			$this->db->where("DATE(loads.PickupDate) <= ",date("Y-m-d",strtotime($filters["endDate"])));
		}
		


		if ( isset($filters["filterType"]) && $filters["filterType"] == 'invoices' ) {	
			$this->db->where_in('loads.id', $in_aray);
			$this->db->where(array('loads.sent_for_payment' => 0, 'loads.delete_status' => 0));
		} else if ( isset($filters["filterType"]) && $filters["filterType"] == 'waiting-paperwork' ) {	
			$this->db->where(array('loads.sent_for_payment' => 0, 'loads.delete_status' => 0,'loads.ready_for_invoice' => 0));
			$this->db->where("(SELECT  count(documents.load_id) as c FROM  documents WHERE  documents.load_id  =  loads.id and documents.doc_type in ('pod','rateSheet') )< 2  ");
		}else if ( isset($filters["filterType"]) && $filters["filterType"] == 'sentForPayment' ) {	
			$this->db->where(array('loads.sent_for_payment' => 1, 'loads.delete_status' => 0));
		}
		//$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);

		if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "driverName"){
			$this->db->order_by('CASE 
								     WHEN loads.driver_type  = "team" THEN CONCAT(drivers.first_name, " + ", team.first_name) 
								     ELSE concat(drivers.first_name, " ", drivers.last_name) 
								 END '.$filters["sortType"]);
		}else if( isset($filters["sortColumn"]) && in_array($filters["sortColumn"] ,array( "TruckCompanyName"))){
			$this->db->order_by("broker_info.".$filters["sortColumn"],$filters["sortType"]);	
		}else if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "rpm"){
			$this->db->order_by("(loads.PaymentAmount/loads.Mileage) ",$filters["sortType"]);	 
		}else{
			$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);	
		}

		
		if(!$total){
			$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
			$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
		}

		$result = $this->db->get('loads');
		//echo $this->db->last_query(); die;
		if($total){
			return $result->num_rows();
		}

		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	} 


	/**
	 * Fetching only In progress loads
	 */
	
	public function getInProgressLoads( $parameter = '',$total=false, $filters = array()  ) {
		$inarray = $this->db->distinct()->select('documents.load_id')->where('doc_type','invoice')->get('documents')->result_array();
		$in_aray =array('');
		if ( !empty($inarray) ) {
			$in_aray =array();
			foreach( $inarray as $inarr ) {
				array_push($in_aray, $inarr['load_id']);
			}
		}
		
		if(count($filters) <= 0){
			$filters = array("itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC");
		}

		$this->db->select('
		case loads.driver_type
			when "team" then concat(drivers.first_name," + ",team.first_name,"-",vehicles.label)
			ELSE concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label)
		end as driverName,loads.LoadType, loads.PickupDate, loads.PickupAddress, loads.OriginCity, loads.OriginState, loads.DestinationCity, loads.DestinationState, loads.DestinationAddress, loads.PaymentAmount, loads.Mileage, (loads.PaymentAmount/loads.Mileage) as rpm, loads.deadmiles, loads.DeliveryDate, loads.JobStatus, loads.truckstopID, loads.id, loads.deadmiles, loads.totalCost, loads.pickDate, loads.invoiceNo, loads.load_source,loads.ready_for_invoice,broker_info.TruckCompanyName,vehicles.id as vehicleID');
		$this->db->join('broker_info','broker_info.id = loads.broker_id','LEFT');
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('drivers as team','team.id = loads.second_driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		
		if ( $parameter == 'invoice' ) {			// for showing loads which have all required fields for generating invoice are filled
			$this->db->join('documents','documents.load_id = loads.id','LEFT');
			$this->db->where(array('loads.vehicle_id != ' => null, 'loads.vehicle_id != ' => 0, 'loads.delete_status' => 0, 'broker_info.TruckCompanyName !=' => '', 'loads.JobStatus' => 'completed'));
			$this->db->where_not_in('loads.id', $in_aray);
			$this->db->where_IN('documents.doc_type',array('pod','rateSheet'));

			if(isset($filters["startDate"]) && !empty($filters["startDate"])){
				$this->db->where("DATE(loads.PickupDate) >= ",date("Y-m-d",strtotime($filters["startDate"])));
			}

			if(isset($filters["endDate"]) && !empty($filters["endDate"])){
				$this->db->where("DATE(loads.PickupDate) <= ",date("Y-m-d",strtotime($filters["endDate"])));
			}
			

			if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){
	            $this->db->group_start();
	            $this->db->like('LOWER(CONCAT(drivers.first_name," ", drivers.last_name))', strtolower($filters['searchQuery']));
	            $this->db->or_like('loads.id', $filters['searchQuery'] );
	            $this->db->or_like('loads.invoiceNo', $filters['searchQuery'] );
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
	            $this->db->or_like('LOWER(loads.JobStatus)', strtolower($filters['searchQuery']) );
	            $this->db->or_like('LOWER(loads.load_source)', strtolower($filters['searchQuery']) );
	            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($filters['searchQuery']) );
	            $this->db->group_end();
			}

			$this->db->group_by('documents.load_id');
			$this->db->having('count(*) > 1', null, false );
				
		} else {
			$this->db->where(array('loads.vehicle_id != ' => null, 'loads.vehicle_id != ' => 0, 'loads.delete_status' => 0));
			$this->db->where_not_in('loads.id', $in_aray);

			if(isset($filters["startDate"]) && !empty($filters["startDate"])){
				$this->db->where("DATE(loads.PickupDate) >= ",date("Y-m-d",strtotime($filters["startDate"])));
			}

			if(isset($filters["endDate"]) && !empty($filters["endDate"])){
				
				$this->db->where("DATE(loads.PickupDate) <= ",date("Y-m-d",strtotime($filters["endDate"])));
			}
			

			if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){
	            $this->db->group_start();
	            $this->db->like('LOWER(CONCAT(drivers.first_name," ", drivers.last_name))', 		strtolower($filters['searchQuery']));
	            $this->db->or_like('loads.id', $filters['searchQuery'] );
	            $this->db->or_like('loads.invoiceNo', $filters['searchQuery'] );
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
	            $this->db->or_like('LOWER(loads.JobStatus)', strtolower($filters['searchQuery']) );
	            $this->db->or_like('LOWER(loads.load_source)', strtolower($filters['searchQuery']) );
	            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($filters['searchQuery']) );
	            $this->db->group_end();
			}
		}

		//$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);
		if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "driverName"){
			$this->db->order_by('CASE 
								     WHEN loads.driver_type  = "team" THEN CONCAT(drivers.first_name, " + ", team.first_name) 
								     ELSE concat(drivers.first_name, " ", drivers.last_name) 
								 END '.$filters["sortType"]);
		}else if( isset($filters["sortColumn"]) && in_array($filters["sortColumn"] ,array( "TruckCompanyName"))){
			$this->db->order_by("broker_info.".$filters["sortColumn"],$filters["sortType"]);	
		}else if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "rpm"){
			$this->db->order_by("(loads.PaymentAmount/loads.Mileage) ",$filters["sortType"]);	 
		}else{
			$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);	
		}

		if(!$total){
			$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
			$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
		}

		$result = $this->db->get('loads');
		//echo $this->db->last_query(); die;
		if($total){
			return $result->num_rows();
		}

		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	} 
	
	/**
	 * Fetching uploaded documents
	 */
	
	public function fetchUploadedDocs( $loadId = null, $parameter = '' ) {
		$condition =  array('load_id' => $loadId, 'doc_type' => $parameter );
		$this->db->select('*');
		$this->db->where($condition);
		$result = $this->db->get('documents');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	} 
	
	/**
	 * Fetching documents to bundle
	 */
	
	public function fetchDocToBundle( $loadId = null ) {
		$this->db->select('*');
		$this->db->where('load_id',$loadId);
		$this->db->where("(documents.doc_type LIKE 'rateSheet' OR documents.doc_type LIKE 'pod' OR documents.doc_type LIKE 'invoice')");
		$this->db->order_by("documents.doc_type", "asc");
		$results = $this->db->get('documents');
		if ( $results->num_rows() > 0 ) {
			return $results->result_array();
		} else {
			return false;
		}
	}
	
	/**
	 * Fetching loads having invoice generated
	 */
	
	public function fetchLoadsForPayment() {
		$inarray = $this->db->distinct()->select('documents.load_id')->where('doc_type','invoice')->get('documents')->result_array();
		$in_aray = array('');
		if ( !empty($inarray) ) {
			$in_aray = array();
			foreach( $inarray as $inarr ) {
				array_push($in_aray, $inarr['load_id']);
			}
		}
		
		$this->db->select('loads.PickupDate,loads.PickupAddress,loads.OriginCity,loads.OriginState,loads.OriginCountry,loads.DestinationAddress,loads.DestinationCity,loads.DestinationState,loads.DestinationCountry,loads.PaymentAmount,loads.truckstopID,loads.id,loads.flag,concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label) as driverName,drivers.profile_image,vehicles.id as vehicleID');
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		$this->db->where_in('loads.id', $in_aray);
		$this->db->where(array('loads.sent_for_payment' => 0, 'loads.delete_status' => 0));
		$this->db->order_by('loads.invoicedDate','DESC');
		$result = $this->db->get('loads');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	} 
	
	/**
	 * Fetching loads already sent count
	 */
	
	public function sentPaymentCount() {
		$this->db->where(array('loads.sent_for_payment' => 1, 'loads.delete_status' => 0));
		$num_rows = $this->db->count_all_results('loads');
		return $num_rows;
	}
	
	/**
	 * Fetching flagged loads count
	 */
	 
	public function flaggedPaymentCount() {
		$where = array('loads.flag' => 1, 'loads.sent_for_payment' => 0, 'loads.delete_status' => 0 );
		$this->db->where($where);
		$num_rows = $this->db->count_all_results('loads');
		return $num_rows;
	} 
	 
	/**
	 * Fetching loads already sent for payment
	 */
	 
	public function fetchSentPaymentLoads() {
		$this->db->select('loads.PickupDate,loads.PickupAddress,loads.OriginCity,loads.OriginState,loads.OriginCountry,loads.DestinationAddress,loads.DestinationCity,loads.DestinationState,loads.DestinationCountry,loads.PaymentAmount,loads.truckstopID,loads.id,loads.flag,loads.sent_for_payment,concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label) as driverName,drivers.profile_image,vehicles.id as vehicleID,drivers.first_name,drivers.last_name,drivers.color');
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		$this->db->where(array('loads.sent_for_payment' => 1, 'loads.delete_status' => 0 ));
		$this->db->order_by('loads.invoicedDate','DESC');
		$result = $this->db->get('loads');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	} 
	
	/**
	 * Fetching loads whose flagged is set for payment
	 */
	 
	public function fetchFlaggedPaymentLoads() {
		$this->db->select('loads.PickupDate,loads.PickupAddress,loads.OriginCity,loads.OriginState,loads.OriginCountry,loads.DestinationAddress,loads.DestinationCity,loads.DestinationState,loads.DestinationCountry,loads.PaymentAmount,loads.truckstopID,loads.id,loads.flag,concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label) as driverName,drivers.profile_image,vehicles.id as vehicleID');
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		$where = array('loads.flag' => 1, 'loads.sent_for_payment' => 0, 'loads.delete_status' => 0 );
		$this->db->where($where);
		$this->db->order_by('loads.invoicedDate','DESC');
		$result = $this->db->get('loads');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	}
	
	/**
	 * Geting load Data for schedule
	 */
	  
	public function getLoadDataForSchedule ( $loadId = null) {
		$this->db->select('invoiceNo,invoicedDate,PaymentAmount');
        $loadsArray = $this->db->where('loads.id',$loadId)->get('loads')->row_array();
        
        $this->db->select('doc_type,doc_name');
        $this->db->where('load_id',$loadId);
        $this->db->where("(documents.doc_type LIKE 'bundle' OR documents.doc_type LIKE 'pod' )");
        $this->db->order_by('documents.doc_type','ASC');
        $docsArray = $this->db->get('documents')->result_array();
               
        return array($loadsArray,$docsArray);
    }
	
	/**
	 * Saving triumph sent request to db
	 */ 
	 
	public function saveTriumphPaymentRequest( $data = array() ) {
		$this->db->insert('triumph_payment_requests',$data);
		return true;
	}
	
	/**
	 * Fetching single load record for send payment
	 */
	
	public function getSingleLoadDetail( $loadId = null ) {
		$this->db->select('loads.PickupDate,loads.PickupAddress,loads.OriginCity,loads.OriginState,loads.OriginCountry,loads.DestinationAddress,loads.DestinationCity,loads.DestinationState,loads.DestinationCountry,loads.PaymentAmount,loads.Mileage,loads.deadmiles,loads.Weight,loads.id,loads.invoicedDate,loads.invoiceNo,loads.flag,loads.shipper_name,loads.shipper_phone,loads.truckstopID,loads.totalCost,loads.pickDate,loads.vehicle_id,broker_info.TruckCompanyName,loads.PointOfContactPhone,broker_info.MCNumber,documents.doc_type,documents.doc_name,documents.id as documentID');
		$this->db->join('broker_info','broker_info.id = loads.broker_id','LEFT');
		$this->db->join('documents','documents.load_id = loads.id','LEFT');
		$this->db->where('loads.id',$loadId);
		//~ $this->db->where('documents.doc_type','bundle');
		$this->db->order_by('documents.doc_type','ASC');
		$result = $this->db->get('loads');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	} 
	
	/**
	 * Flag or unflag load for payment
	 */
	 
	public function flagUnflagLoad( $flagStatus = '', $loadId = null ) {
		if ( $flagStatus == 'flag' ) 
			$status = 1;
		else 
			$status = 0;
			
		$this->db->set('flag', $status, FALSE);
		$this->db->where('id', $loadId);
		$this->db->update('loads');
		
		return true;
	} 
	
	/**
	 * Fetching bundle file name for api
	 */
	
	public function getBundleFileName( $loadId = null ) {
		$condition = array('load_id' => $loadId,'doc_type' => 'bundle');
		return $this->db->where($condition)->get('documents')->row()->doc_name;
	} 
	
	/**
	 * Updating Payment sent value to 1 after sent
	 */
	 
	public function updatePaymentSent( $loadId = null ) {
		$data['sent_for_payment'] = 1;
		$this->db->where('id',$loadId);
		$this->db->update('loads',$data);
		return true;
	} 
	
	/**
	 * Saving confirmation code to db
	 */
	 
	public function saveConfirmationCode( $ids = '', $code = '') {
		$data = array(
			'loadIds' => $ids,
			'confirmationCode' => $code,
			'user_id' => $this->userId,
		);
		
		$this->db->insert('save_payment_confirmCode', $data);
		return true;
	} 
	
	/**
	 * Fetching Billing load info for add load
	 */
	
	public function getShippingLoadInfo( $loadId = null ) { 
		$this->db->select('*');
		$this->db->where('billing_details.load_id',$loadId);
		$result = $this->db->get('billing_details');
		if ( $result->num_rows() > 0 )
			return $result->row_array();
		else 
			return array();
	} 
	
	/**
	 * Generating invoice number for load at time of generating invoice
	 */
	 
	public function generateInvoiceNumber( $loadId = null ) {
		$this->db->select_max('invoiceNo');
		$result = $this->db->get('loads')->row();  
		
		$invoiceNo = $result->invoiceNo; 
		if ( $invoiceNo  == '' || $invoiceNo == null || $invoiceNo == 'undefined' ) { 
			$invoiceNo = 10000;
		} else {
			$invoiceNo = $invoiceNo + 1;
		}
		
		$data['invoiceNo'] = $invoiceNo;
					
		$this->db->where('loads.id',$loadId);
		$this->db->update('loads',$data);
		
		return $invoiceNo;		
	} 
	
	/**
	 * Generating invoice date while generating invoice file
	 */
	
	public function addGenerateInvoiceDate( $loadId = null ) {
		$data['invoicedDate'] = date('Y-m-d');
		$this->db->where('loads.id',$loadId);
		$this->db->update('loads',$data);
		
		return $data['invoicedDate'];
	}  

}
?>
