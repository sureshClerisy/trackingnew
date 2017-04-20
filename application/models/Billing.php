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
		end as driverName,
		CASE loads.billType 
			WHEN "shipper" THEN (shippers.shipperCompanyName) 
					   ELSE (broker_info.TruckCompanyName)
					END AS TruckCompanyName,
				 loads.LoadType, loads.PickupDate, loads.PickupAddress, loads.OriginCity, loads.OriginState, loads.DestinationCity, loads.DestinationState, loads.DestinationAddress, loads.PaymentAmount, loads.Mileage, loads.deadmiles, loads.DeliveryDate, loads.JobStatus, loads.truckstopID, loads.id, loads.deadmiles, loads.totalCost, loads.pickDate, loads.invoiceNo, loads.load_source,loads.ready_for_invoice,loads.billType,vehicles.id as vehicleID,loads.created,loads.totalCost,loads.overallTotalProfit,loads.overallTotalProfitPercent,(loads.PaymentAmount/loads.Mileage) as rpm,loads.invoiceNo');
		
		$this->db->join("broker_info", "broker_info.id = loads.broker_id AND loads.billType = 'broker'","LEFT");
		$this->db->join("shippers", "shippers.id = loads.broker_id AND loads.billType = 'shipper'","LEFT");
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('drivers as team','team.id = loads.second_driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		
		
			if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){
	            $this->db->group_start();
	            $this->db->like('LOWER(CONCAT(TRIM(drivers.first_name)," ", TRIM(drivers.last_name)))', strtolower($filters['searchQuery']));
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
	            $this->db->or_like('LOWER(loads.billType)', strtolower($filters['searchQuery']) );
	            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($filters['searchQuery']) );
	            $this->db->or_like('LOWER(shippers.shipperCompanyName)', strtolower($filters['searchQuery']) );
	            $this->db->group_end();
			}
		
		if(isset($filters["userType"]) && $filters["userType"] == 'team') {
	
			$this->db->where(array('loads.driver_type' => "team",'loads.driver_id' => $filters["userId"], 'loads.second_driver_id' => $filters['secondDriverId'], 'loads.dispatcher_id' => $filters['dispatcherId']));		
		} else if(isset($filters["userType"]) && $filters["userType"] == 'driver') {

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
		
		if(isset($filters["vehicles"]) && is_array($filters["vehicles"])){
			$this->db->where_in("vehicles.id", $filters["vehicles"]);
		}

		if ( isset($filters["filterType"]) && $filters["filterType"] == 'invoices' ) {	
			$this->db->where_in('loads.id', $in_aray);
			$this->db->where(array('loads.sent_for_payment' => 0, 'loads.delete_status' => 0));

		} else if ( isset($filters["filterType"]) && $filters["filterType"] == 'waiting-paperwork' ) {

			$this->db->where(array('loads.sent_for_payment' => 0, 'loads.delete_status' => 0,'loads.ready_for_invoice' => 0));
			$this->db->where("(SELECT  count(documents.load_id) as c FROM  documents WHERE  documents.load_id  =  loads.id and documents.doc_type in ('pod','rateSheet') )< 2 AND deliverydate < '".date("Y-m-d")."'");
			$this->db->where_IN('loads.JobStatus',$this->config->item('loadStatus'));
			if(isset($filters["dateFrom"]) && !empty($filters["dateFrom"])){
				$this->db->where("DATE(loads.PickupDate) >= ",date("Y-m-d",strtotime($filters["dateFrom"])));
			}

			if(isset($filters["dateTo"]) && !empty($filters["dateTo"])){
				$this->db->where("DATE(loads.PickupDate) <= ",date("Y-m-d",strtotime($filters["dateTo"])));
			}


		}else if ( isset($filters["filterType"]) && $filters["filterType"] == 'sentForPayment' ) {	

			$this->db->where(array('loads.sent_for_payment' => 1, 'loads.delete_status' => 0));

		}else if ( isset($filters["filterType"]) && $filters["filterType"] == 'cash_flow' ) {	

			$oneDayBefore = date('Y-m-d', strtotime('-1 day', strtotime($filters["dateFrom"])));	
			$this->db->where("( date( loads.deliveryDate ) BETWEEN '".$filters["dateFrom"]."' AND '".$filters["dateTo"]."' OR ( date( loads.deliveryDate ) BETWEEN  '2017-02-09' AND '".$oneDayBefore."' AND loads.sent_for_payment = 0 AND  loads.id >=10000) )");	
				$this->db->where(array('loads.delete_status' => 0));	
				$this->db->where_IN('loads.JobStatus',$this->config->item('loadStatus'));


		}else if ( isset($filters["filterType"]) && $filters["filterType"] == 'last_week_sale' ) {	

			$lastWeekStartDay   = date("Y-m-d", strtotime('monday last week'));
			$lastWeekEndDay     = date("Y-m-d", strtotime('sunday last week'));
			if(isset($filters["dateFrom"]) && !empty($filters["dateFrom"])){
				$lastWeekStartDay   = date("Y-m-d",strtotime($filters["dateFrom"]));
			}

			if(isset($filters["dateTo"]) && !empty($filters["dateTo"])){
				$lastWeekEndDay     = date("Y-m-d",strtotime($filters["dateTo"]));
			}

			$this->db->where("FIND_IN_SET( loads.id, ( SELECT GROUP_CONCAT( sp.loadids ) AS id FROM `save_payment_confirmCode` AS `sp` WHERE DATE( sp.created ) >= '".$lastWeekStartDay."' AND DATE( sp.created ) <= '".$lastWeekEndDay."'))");

		}else if ( isset($filters["filterType"]) && $filters["filterType"] == 'this_week_sale' ) {	

			$thisWeekStartDay   = date("Y-m-d", strtotime('monday this week'));
			$thisWeekToday      = date("Y-m-d");
			$this->db->where("FIND_IN_SET( loads.id, ( SELECT GROUP_CONCAT( sp.loadids ) AS id FROM `save_payment_confirmCode` AS `sp` WHERE DATE( sp.created ) >= '".$thisWeekStartDay."' AND DATE( sp.created ) <= '".$thisWeekToday."'))");

		}else if ( isset($filters["filterType"]) && $filters["filterType"] == 'sent_today_expected' ) {	
		
			$oneDayBefore = date('Y-m-d', strtotime('-1 day', strtotime($filters["dateFrom"])));	
			$this->db->where("( date( loads.deliveryDate ) BETWEEN '".$filters["dateFrom"]."' AND '".$filters["dateTo"]."' OR ( date( loads.deliveryDate ) BETWEEN  '2017-02-09' AND '".$oneDayBefore."' AND loads.sent_for_payment = 0 AND  loads.id >=10000) )");	
			$this->db->where("loads.delete_status",0);
			$this->db->where_IN('loads.JobStatus',$this->config->item('loadStatus'));
			$this->db->where('loads.sent_for_payment!=',1);

		}else if ( isset($filters["filterType"]) && $filters["filterType"] == 'sent_today' ) {	

			$dateFrom = $dateTo = date("Y-m-d");
			$this->db->where("FIND_IN_SET( loads.id, ( SELECT GROUP_CONCAT( sp.loadids ) AS id FROM `save_payment_confirmCode` AS `sp` WHERE DATE( sp.created ) >= '".$dateFrom."' AND DATE( sp.created ) <= '".$dateTo."'))");
			//$this->db->where_IN('loads.JobStatus',$this->config->item('loadStatus'));
			//$this->db->where('loads.sent_for_payment!=',1);
		}

		if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "driverName"){
			$this->db->order_by('CASE 
								     WHEN loads.driver_type  = "team" THEN CONCAT(TRIM(drivers.first_name), " + ", TRIM(team.first_name)) 
								     ELSE concat(drivers.first_name, " ", drivers.last_name) 
								 END '.$filters["sortType"]);
		}else if( isset($filters["sortColumn"]) && in_array($filters["sortColumn"] ,array( "TruckCompanyName"))){
			$this->db->order_by('CASE 
								     WHEN loads.billType  = "shipper" THEN (shippers.shipperCompanyName) 
								     ELSE (broker_info.TruckCompanyName)
								 END '.$filters["sortType"]);	
		}else if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "rpm"){
			$this->db->order_by("(loads.PaymentAmount/loads.Mileage) ",$filters["sortType"]);	 
		}else{
			$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);	
		}

		if(!$total){
			$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
			if(empty($filters["export"])){
				$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
			}
		}

		$this->db->where('loads.id >',9999);
		
		$result = $this->db->get('loads');
		//echo $this->db->last_query();die;
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
	
	public function getInProgressLoads( $parameter = '',$total=false, $filters = array() ,$exportRequest=false) {
	
		$inarray = $this->db->distinct()->select('documents.load_id')->where('doc_type','invoice')->get('documents')->result_array();
		$in_aray =array('');
		$ohterColumns ='';
		if ( !empty($inarray) ) {
			$in_aray =array();
			foreach( $inarray as $inarr ) {
				array_push($in_aray, $inarr['load_id']);
			}
		}
		
		if(count($filters) <= 0){
			$filters = array("itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC");
		}

		/*if(!$exportRequest){

			$ohterColumns = ',loads.DeliveryDate,loads.PickupAddress, loads.OriginCity, loads.OriginState, loads.DestinationCity, loads.DestinationState, loads.DestinationAddress, loads.PaymentAmount, loads.Mileage,loads.DeliveryDate,  loads.truckstopID,  loads.deadmiles, loads.PickupDate, loads.load_source,loads.ready_for_invoice,loads.billType,vehicles.id as vehicleID,loads.created';
		}*/

		$this->db->select('DATE_FORMAT(loads.created,"%m/%d") as date,
		case loads.driver_type
			when "team" then concat(drivers.first_name," + ",team.first_name,"-",vehicles.label)
			ELSE concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label)
		end as driverName,
		CASE loads.billType 
				WHEN "shipper" THEN (shippers.shipperCompanyName) 
			   ELSE (broker_info.TruckCompanyName)
			END AS TruckCompanyName, 
		loads.invoiceNo,loads.totalCost,loads.overallTotalProfit,loads.overallTotalProfitPercent,loads.Mileage,loads.deadmiles, (loads.PaymentAmount/loads.Mileage) as rpm,loads.pickDate,concat(loads.OriginCity," ,",loads.OriginState) as pickup,loads.DeliveryDate,concat(loads.DestinationCity," ,",loads.DestinationState) as delivery,loads.id,loads.JobStatus,loads.LoadType,loads.DeliveryDate,loads.PickupAddress, loads.OriginCity, loads.OriginState,loads.DeliveryDate,loads.PickupAddress, loads.OriginCity, loads.OriginState, loads.DestinationCity, loads.DestinationState, loads.DestinationAddress, loads.PaymentAmount, loads.Mileage,loads.DeliveryDate,  loads.truckstopID,  loads.deadmiles, loads.PickupDate, loads.load_source,loads.ready_for_invoice,loads.billType,vehicles.id as vehicleID,loads.created,loads.invoiceNo');

		$this->db->join("broker_info", "broker_info.id = loads.broker_id AND loads.billType = 'broker'","LEFT");
		$this->db->join("shippers", "shippers.id = loads.broker_id AND loads.billType = 'shipper'","LEFT");
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('drivers as team','team.id = loads.second_driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		
		if ( $parameter == 'invoice' ) {

			// for showing loads which have all required fields for generating invoice are filled
			$this->db->join('documents','documents.load_id = loads.id','LEFT');
			$this->db->where(array('loads.vehicle_id != ' => null, 'loads.vehicle_id != ' => 0, 'loads.driver_id != ' => 0, 'loads.delete_status' => 0, 'loads.broker_id !=' => '', 'loads.broker_id !=' => null));
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
	            $this->db->like('LOWER(CONCAT(TRIM(drivers.first_name)," ", TRIM(drivers.last_name)))', strtolower($filters['searchQuery']));
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
	            $this->db->or_like('LOWER(loads.billType)', strtolower($filters['searchQuery']) );
	            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($filters['searchQuery']) );
	            $this->db->or_like('LOWER(shippers.shipperCompanyName)', strtolower($filters['searchQuery']) );
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
	            $this->db->like('LOWER(CONCAT(TRIM(drivers.first_name)," ", TRIM(drivers.last_name)))', 		strtolower($filters['searchQuery']));
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
	            $this->db->or_like('LOWER(loads.billType)', strtolower($filters['searchQuery']) );
	            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($filters['searchQuery']) );
	             $this->db->or_like('LOWER(shippers.shipperCompanyName)', strtolower($filters['searchQuery']) );
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
			$this->db->order_by('CASE 
								     WHEN loads.billType  = "shipper" THEN (shippers.shipperCompanyName) 
								     ELSE (broker_info.TruckCompanyName)
								 END '.$filters["sortType"]);
		}else if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "rpm"){
			$this->db->order_by("(loads.PaymentAmount/loads.Mileage) ",$filters["sortType"]);	 
		}else{
			$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);	
		}

		if(!$total){
			$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
			//Get All records for excell file while export data based on $exportRequest
			if(!$exportRequest){
				$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
			}

		}

		$this->db->where_IN('loads.JobStatus',$this->config->item('loadStatus'));
		$this->db->where('loads.id >',9999);
		$result = $this->db->get('loads');
		// echo $this->db->last_query();

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
		// $inarray = $this->db->distinct()->select('documents.load_id')->where('doc_type','invoice')->get('documents')->result_array();
		// $in_aray = array('');
		// if ( !empty($inarray) ) {
		// 	$in_aray = array();
		// 	foreach( $inarray as $inarr ) {
		// 		array_push($in_aray, $inarr['load_id']);
		// 	}
		// }

		$in_aray = $this->invoiceGeneratedIds();
		
		$this->db->select('loads.PickupDate,loads.PickupAddress,loads.OriginCity,loads.OriginState,loads.OriginCountry,loads.DestinationAddress,loads.DestinationCity,loads.DestinationState,loads.DestinationCountry,loads.PaymentAmount,loads.truckstopID,loads.id,loads.flag,loads.billType, loads.payment_type, loads.flag_perm, concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label) as driverName,drivers.profile_image,vehicles.id as vehicleID,drivers.first_name,drivers.last_name,drivers.color,loads.created,loads.totalCost,loads.overallTotalProfit,loads.overallTotalProfitPercent,loads.Mileage,loads.deadmiles,loads.pickDate,loads.DeliveryDate,loads.JobStatus, 
			CASE loads.billType 
			WHEN "shipper" THEN (shippers.shipperCompanyName) 
					   ELSE (broker_info.TruckCompanyName)
					END AS TruckCompanyName');
		
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		
		$this->db->join("broker_info", "broker_info.id = loads.broker_id AND loads.billType = 'broker'","LEFT");
		$this->db->join("shippers", "shippers.id = loads.broker_id AND loads.billType = 'shipper'","LEFT");

		$this->db->where_in('loads.id', $in_aray);
		$this->db->where(array('loads.sent_for_payment' => 0, 'loads.delete_status' => 0, 'loads.flag_perm' => 0));
		$this->db->order_by('loads.invoicedDate','DESC');
		$result = $this->db->get('loads');

		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	} 
	
	/**
	* Fetching loads which are ready for payment count
	*/

	public function fetchLoadsForPaymentCount() {
		$in_aray = $this->invoiceGeneratedIds();
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		$this->db->where_in('loads.id', $in_aray);
		$this->db->where(array('loads.sent_for_payment' => 0, 'loads.delete_status' => 0, 'loads.flag_perm' => 0));
		$num_rows = $this->db->count_all_results('loads');
		return $num_rows;
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
		$where = array('loads.flag' => 1, 'loads.flag_perm' => 0, 'loads.sent_for_payment' => 0, 'loads.delete_status' => 0);
		$this->db->where($where);
		$num_rows = $this->db->count_all_results('loads');
		return $num_rows;
	} 

	/**
	* Factored Payment loads count
	*/

	public function factoredPaymentCount() {
		$where = array('loads.flag' => 1, 'loads.flag_perm' => 1, 'loads.sent_for_payment' => 0, 'loads.delete_status' => 0 , 'loads.payment_type' => 'triumph');
		$this->db->where($where);
		$num_rows = $this->db->count_all_results('loads');
		return $num_rows;
	}
	 
	/**
	 * Fetching loads already sent for payment
	 */
	 
	public function fetchSentPaymentLoads( $args = array(), $total = false, $export = false ) {

		if(!$total){
			$this->db->select('loads.PickupDate, loads.OriginCity,loads.OriginState, loads.DestinationCity,loads.DestinationState, loads.PaymentAmount,loads.truckstopID,loads.id,loads.flag,loads.sent_for_payment, loads.billType, loads.payment_type, loads.LoadType, loads.invoiceNo, loads.load_source, vehicles.id as vehicleID,
				case loads.driver_type
					when "team" then concat(drivers.first_name," + ",team.first_name,"-",vehicles.label)
					ELSE concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label)
				end as driverName,
				CASE loads.billType 
					WHEN "shipper" THEN (shippers.shipperCompanyName) 
					ELSE (broker_info.TruckCompanyName)
				END AS TruckCompanyName, 
					loads.Mileage,loads.deadmiles,loads.pickDate,loads.DeliveryDate,loads.JobStatus,loads.created,loads.totalCost,loads.overallTotalProfit');
		} else {
			$this->db->select("count(loads.id) as totalRows");
		}
		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		$this->db->join('drivers as team','team.id = loads.second_driver_id','left');	
		$this->db->join("broker_info", "broker_info.id = loads.broker_id AND loads.billType = 'broker'","LEFT");
		$this->db->join("shippers", "shippers.id = loads.broker_id AND loads.billType = 'shipper'","LEFT");

		$this->db->where(array('loads.sent_for_payment' => 1, 'loads.delete_status' => 0 ));

		if(isset($args["searchQuery"]) && !empty($args["searchQuery"])){
            $this->db->group_start();
            $this->db->like('LOWER(CONCAT(TRIM(drivers.first_name)," ",TRIM(drivers.last_name)))', strtolower($args['searchQuery']));
            $this->db->or_like('loads.id', $args['searchQuery'] );
            $this->db->or_like('loads.invoiceNo', $args['searchQuery'] );
            $this->db->or_like('LOWER(loads.LoadType)', strtolower($args['searchQuery']) );
            $this->db->or_like('loads.PickupDate', $args['searchQuery'] );
            $this->db->or_like('loads.DeliveryDate', $args['searchQuery'] );
            $this->db->or_like('LOWER(loads.OriginCity)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.OriginState)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationCity)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationState)', strtolower($args['searchQuery']) );
            $this->db->or_like('loads.PaymentAmount', $args['searchQuery']);
            $this->db->or_like('loads.Mileage', $args['searchQuery']);
            $this->db->or_like('loads.deadmiles', $args['searchQuery']);
            $this->db->or_like('LOWER(loads.JobStatus)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.load_source)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.billType)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(shippers.shipperCompanyName)', strtolower($args['searchQuery']) );
            $this->db->group_end();
		}

		if(isset($args["sortColumn"]) && $args["sortColumn"] == "driverName"){
			$this->db->order_by('CASE 
								     WHEN loads.driver_type  = "team" THEN CONCAT(TRIM(drivers.first_name), " + ", TRIM(team.first_name)) 
								     ELSE concat(drivers.first_name, " ", drivers.last_name) 
								 END '.$args["sortType"]);
		} else if(isset($args['sortColumn']) && in_array($args["sortColumn"] ,array("TruckCompanyName"))) {
			$this->db->order_by('CASE 
								     WHEN loads.billType  = "shipper" THEN (shippers.shipperCompanyName) 
								     ELSE (broker_info.TruckCompanyName)
								 END '.$args["sortType"]);
		} else if( isset($args['sortColumn']) && $args["sortColumn"] == "rpm") {
			$this->db->order_by("(loads.PaymentAmount/loads.Mileage) ",$args["sortType"]);	 
		} else {
			$this->db->order_by("loads.".$args["sortColumn"],$args["sortType"]);	
		}

		if(!$total){
			if(empty($export)){
	            $args["limitStart"] = $args["limitStart"] == 1 ? 0 : $args["limitStart"];
	            $this->db->limit($args["itemsPerPage"],$args["limitStart"]);
        	}
        }
		$result = $this->db->get('loads');
		// echo $this->db->last_query();
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	} 
	
	/**
	 * Fetching loads whose flagged is set for payment
	 */
	 
	public function fetchFactoredPaymentRecords() {
		$this->db->select('loads.PickupDate,loads.PickupAddress,loads.OriginCity,loads.OriginState,loads.OriginCountry,loads.DestinationAddress,loads.DestinationCity,loads.DestinationState,loads.DestinationCountry,loads.PaymentAmount,loads.truckstopID,loads.id,loads.flag, loads.billType,loads.payment_type,concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label) as driverName,drivers.profile_image,vehicles.id as vehicleID,drivers.first_name,drivers.last_name,drivers.color,loads.created,loads.totalCost,loads.overallTotalProfit,loads.overallTotalProfitPercent,loads.Mileage,loads.deadmiles,loads.pickDate,loads.DeliveryDate,loads.JobStatus,
			CASE loads.billType 
			WHEN "shipper" THEN (shippers.shipperCompanyName)
					   ELSE (broker_info.TruckCompanyName)
					END AS TruckCompanyName');

		$this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
		$this->db->join('vehicles','vehicles.id = loads.vehicle_id','LEFT');
		$this->db->join("broker_info", "broker_info.id = loads.broker_id AND loads.billType = 'broker'","LEFT");
		$this->db->join("shippers", "shippers.id = loads.broker_id AND loads.billType = 'shipper'","LEFT");

		$where = array('loads.flag' => 1, 'loads.flag_perm' => 1, 'loads.sent_for_payment' => 0, 'loads.delete_status' => 0 , 'payment_type' => 'triumph');
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
		$this->db->select('CASE loads.billType 
				WHEN "shipper" THEN (shippers.shipperCompanyName) 
			   ELSE (broker_info.TruckCompanyName)
			END AS TruckCompanyName,
			loads.PickupDate,loads.PickupAddress,loads.OriginCity,loads.OriginState,loads.OriginCountry,loads.DestinationAddress,loads.DestinationCity,loads.DestinationState,loads.DestinationCountry,loads.PaymentAmount,loads.Mileage,loads.deadmiles,loads.Weight,loads.id,loads.invoicedDate,loads.invoiceNo,loads.flag,loads.shipper_name,loads.shipper_phone,loads.truckstopID,loads.totalCost,loads.pickDate,loads.vehicle_id,loads.PointOfContactPhone,broker_info.MCNumber,documents.doc_type,documents.doc_name,documents.id as documentID');
		$this->db->join("broker_info", "broker_info.id = loads.broker_id AND loads.billType = 'broker'" ,"LEFT");
		$this->db->join("shippers",	   "shippers.id = loads.broker_id AND loads.billType = 'shipper'"   ,"LEFT");
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
	 
	public function flagUnflagLoad( $flagStatus = '', $loadId = null, $paymentType ) {
		if ( $flagStatus == 'flag' )  {
			$data = array(
				'flag' => 1,
				'payment_type' => $paymentType,
			);
		} else  {
			$data = array(
				'flag' => 0,
				'flag_perm' => 0,
				'payment_type' => $paymentType,
			);
		}

		$this->db->where('id', $loadId);
		$this->db->update('loads',$data);		
		return true;
	}

	/**
	* changing status of flagged loads permanently
	*/ 

	public function changeFlaggedStatusPerm( $batchArray = array() ) {
		$this->db->update_batch('loads', $batchArray, 'id'); 
		return true;
	}
	

	/**
	* Fetch loads which are flagged temp
	*/

	public function fetchLoadsFlaggedTemp() {
		$in_aray = $this->invoiceGeneratedIds();
		$this->db->select('loads.id,loads.flag, loads.payment_type, loads.flag_perm, loads.sent_for_payment');
		$this->db->where_in('loads.id', $in_aray);
		$this->db->where(array('loads.sent_for_payment' => 0, 'loads.delete_status' => 0, 'loads.flag_perm' => 0, 'loads.flag' => 1));
		$result = $this->db->get('loads');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	}

	/**
	* Return ids which have invoiced generated
	*/

	public function invoiceGeneratedIds() {
		$inarray = $this->db->distinct()->select('documents.load_id')->where('doc_type','invoice')->get('documents')->result_array();
		$in_aray = array('');
		if ( !empty($inarray) ) {
			$in_aray = array();
			foreach( $inarray as $inarr ) {
				array_push($in_aray, $inarr['load_id']);
			}
		}

		return $in_aray;
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

	public function sentForPaymentWithFilter($fromDate, $toDate){
		$this->db->select("SUM( paymentamount )  as sentPayment");
		$this->db->where("FIND_IN_SET( id, ( SELECT GROUP_CONCAT( sp.loadids ) AS id FROM `save_payment_confirmCode` AS `sp` WHERE DATE( sp.created ) >= '".$fromDate."' AND DATE( sp.created ) <= '".$toDate."'))");
		$this->db->where("loads.delete_status", 0);
		$result = $this->db->get('loads');
		//echo $this->db->last_query()."<br/><br/>";
		if ( $result->num_rows() > 0 )
			return $result->row_array()["sentPayment"];
		else 
			return array();
	}

	public function getRecentTransactions($date = false, $limit = 5,$args = array()){
		$this->db->select(array("confirmationCode, DATE( created ) as date, (CHAR_LENGTH(loadIds) - CHAR_LENGTH(REPLACE(loadIds, ',', '')) + 1) as inv, ( SELECT SUM( paymentamount ) FROM loads WHERE FIND_IN_SET( id, ( SELECT GROUP_CONCAT( sp.loadids ) AS id FROM `save_payment_confirmCode` AS `sp` where id = spCode.id))) as amount"),false);

		if(isset($args["startDate"])){
			$this->db->where("DATE( spCode.created ) >=", $args["startDate"]);
		}

		if(isset($args["endDate"])){
			$this->db->where("DATE( spCode.created ) <=", $args["endDate"]);	
		}

		if($date){
			$this->db->where("DATE( spCode.created ) <", $date);
			$this->db->limit(1);		
		}else{
			$this->db->limit($limit);		
		}
		
		$this->db->order_by("created","DESC");
		$result = $this->db->get('save_payment_confirmCode as spCode');

		//echo $this->db->last_query()."<br/><br/>";
		if ( $result->num_rows() > 0 )
			return $result->result_array();
		else 
			return array();
	}

	public function expectedBilling( $startDate, $endDate, $limit = 7 ){
		// $startDate =  date ("Y-m-d", strtotime("-1 day", strtotime($args["startDate"]))); 
		$oneDayBefore = date('Y-m-d', strtotime('-1 day', strtotime($startDate)));
		$this->db->select(" sum( paymentAmount ) AS billing , count( id ) AS inv  ");
		$this->db->where("( date( deliveryDate ) BETWEEN '".$startDate."' AND '".$endDate."' OR 
			( date( deliveryDate ) BETWEEN  '2017-02-09' AND '".$oneDayBefore."' AND sent_for_payment = 0 AND  id >=10000) )");
		$this->db->where("loads.delete_status", 0);
		$this->db->where('sent_for_payment!=',1);
		$this->db->where_IN('loads.JobStatus',$this->config->item('loadStatus'));


		$result = $this->db->get('loads');
		//echo $this->db->last_query();die;
		if ( $result->num_rows() > 0 ){
			return $result->row_array();
		}else {
			return array();
		}
	}

	public function expectedBillingOnDate( $dateFrom = '', $dateTo = '', $for = "today" ){
		$oneDayBefore = date('Y-m-d', strtotime('-1 day', strtotime($dateFrom)));
		$this->db->select(" sum( paymentAmount ) AS billing");
		$this->db->where("( date( deliveryDate ) BETWEEN '".$dateFrom."' AND '".$dateTo."' OR 
			( date( deliveryDate ) BETWEEN  '2017-02-09' AND '".$oneDayBefore."' AND sent_for_payment = 0 AND  id >=10000) )");	
		$this->db->where("loads.delete_status", 0);
		$this->db->where_IN('loads.JobStatus',$this->config->item('loadStatus'));
		
		if($for === "today"){
			$this->db->where('sent_for_payment!=',1);	
		}

		$result = $this->db->get('loads');

		//echo $this->db->last_query()."<br/><br/>";

		if ( $result->num_rows() > 0 ){
			return $result->row_array()["billing"];
		}else {
			return 0;
		}
	}


	public function getSettings($page, $type, $userId){
		$this->db->select("settings, date(created) as created");
		$this->db->where( array("page"=>$page, "type" => $type, "user_id" => $userId));
		$result = $this->db->get("user_settings");
		if ( $result->num_rows() > 0 ){
			return $result->row_array();
		}else {
			return 0;
		}
	}

	public function updateUserSettings($page, $type, $userId, $settings){
		$data  = array("user_id"=>$userId, "page" => $page, "type" => $type, "settings" => $settings );
		if($this->getSettings($page, $type, $userId)){
			$this->db->where( array("page"=>$page, "type" => $type, "user_id" => $userId));
			$this->db->update('user_settings',$data);		
		}else{
			$this->db->insert('user_settings',$data);
		}		
		
	}

	public function deleteUserSetting($page, $type, $userId){
		$data  = array("user_id"=>$userId, "page" => $page, "type" => $type );
		$this->db->where($data);
		$this->db->delete('user_settings');
	}

}
?>
