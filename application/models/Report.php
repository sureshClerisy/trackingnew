<?php

class Report extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	public function getAllVehicles(){
		$this->db->select('id,tracker_id, CONCAT("T",label) as truck');
		$this->db->where('tracker_id !=','');
		$this->db->where('tracker_id !=','0');
		$query = $this->db->get('vehicles');
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	public function getBreadcrumbsDetail( $args = array(), $total = false , $parameter = ''){
		
		$includeLatLong = '';

		if(isset($args['includeLatLong']) && $args['includeLatLong']){
			$includeLatLong = 'ae.`latitude` , ae.`longitude`';
		}

		if(!$total){

		$this->db->select("ae.`deviceID`,  DATE_FORMAT(GMTTime,'%d-%b-%Y %r') AS GMTTime , ae.`eventType` , ".$includeLatLong." , CONCAT( ae.`street` , ', ', ae.`crossStreet` , ', ', ae.`city` , ', ', ae.`state` , ', ', ae.`zip` ) AS location, ae.`vehicleSpeed`,ae.`latitude`,ae.`longitude` , ae.`odometer`, v.label, CONCAT(d.first_name, ' ', d.last_name) as driverName,
			CASE
				WHEN FLOOR(ae.heading/45) = 0 THEN 'N' 
				WHEN FLOOR(ae.heading/45) = 1 THEN 'NE' 
				WHEN FLOOR(ae.heading/45) = 2 THEN 'E' 
				WHEN FLOOR(ae.heading/45) = 3 THEN 'SE' 
				WHEN FLOOR(ae.heading/45) = 4 THEN 'S' 
				WHEN FLOOR(ae.heading/45) = 5 THEN 'SW' 
				WHEN FLOOR(ae.heading/45) = 6 THEN 'W' 
				WHEN FLOOR(ae.heading/45) = 7 THEN 'NW' 
			END as hdirection

			");
		} else {
			$this->db->select("count(ae.deviceID) as totalRows");
		}

		$this->db->join('vehicles as v', 'ae.deviceID = v.tracker_id','Left');
		$this->db->join('drivers as d', 'd.id = v.driver_id','Left');
		
		if(isset($args['deviceID'])){
			$this->db->where_in('ae.deviceID',$args['deviceID']);
		}

		if(isset($args['eventType'])){
			$this->db->where_in('ae.eventType',$args['eventType']);
		}

		$this->db->where('ae.eventType !=','INFORMATION');
		$this->db->where('ae.eventType !=','BATTERY POWER ON');

		if ( isset($args['customDate']) && $args['customDate'] != '' )
			$this->db->where('DATE(ae.GMTTime) =',$args['customDate']);

		if ( isset($args['startDate']) && $args['startDate'] != '') {
			$this->db->where('DATE(ae.GMTTime) >=',$args['startDate']);
			$this->db->where('DATE(ae.GMTTime) <=',$args['endDate']);
		}

		if(isset($args["searchQuery"]) && !empty($args["searchQuery"])){
            $this->db->group_start();
            $this->db->like('ae.deviceID', $args['searchQuery'] );
            $this->db->or_like('ae.eventType', $args['searchQuery'] );
            $this->db->or_like('ae.vehicleSpeed', $args['searchQuery'] );
            $this->db->or_like('ae.odometer', $args['searchQuery'] );
            $this->db->or_like("LOWER(CONCAT( ae.`street` , ', ', ae.`crossStreet` , ', ', ae.`city` , ', ', ae.`state` , ', ', ae.`zip` ))", strtolower($args['searchQuery']));
            $this->db->or_like("CONCAT(DATE_FORMAT(GMTTime,'%d-%b-%Y %r'),' GMT') ", $args['searchQuery'] );
            $this->db->or_like('v.label', $args['searchQuery']);
            $this->db->or_like("LOWER(CONCAT(d.first_name, ' ', d.last_name))", strtolower($args['searchQuery']) );
            $this->db->group_end();
		}

		$this->db->order_by('ae.'.$args['sortColumn'],$args['sortType']);
		if(!$total && $parameter != 'others'){
			$args["limitStart"] = $args["limitStart"] == 1 ? 0 : $args["limitStart"];
			$this->db->limit($args["itemsPerPage"],$args["limitStart"]);
		}

		$query = $this->db->get('avl_events as ae');

		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}



	public function getLoadsTrackingIndividual($args,$type='website',$from = 'report'){

		/*if($type == "export"){
			$this->db->select("l.id as loadid, CONCAT( `u`.`first_name` , ' ', u.last_name ) AS dispatcher,  case l.driver_type when 'team' then concat(d.first_name,' + ',team.first_name) ELSE concat(d.first_name,' ',d.last_name) end as driver, CONCAT(UCASE(LEFT(b.TruckCompanyName, 1)), LCASE(SUBSTRING(b.TruckCompanyName, 2))) AS broker,  l.paymentAmount as invoice, l.totalCost as charges, l.overallTotalProfit AS profit, l.overallTotalProfitPercent,  l.Mileage as miles, l.deadmiles, l.deadmiles as rmile, l.PickupDate, l.PickupAddress, l.DeliveryDate, l.DestinationAddress ");
		}else{*/

		$extraFields = "";
		if($from == "dashboard"){
			$extraFields = ", l.totalCost,l.pickDate, l.truckstopID, l.vehicle_id";
		}

		$this->db->select("l.id as loadid, CONCAT( `u`.`first_name` , ' ', u.last_name ) AS dispatcher,  case l.driver_type when 'team' then concat(d.first_name,' + ',team.first_name) ELSE concat(d.first_name,' ',d.last_name) end as driver, CONCAT(UCASE(LEFT(b.TruckCompanyName, 1)), LCASE(SUBSTRING(b.TruckCompanyName, 2))) AS broker,  l.paymentAmount as invoice, l.totalCost as charges, l.overallTotalProfit AS profit, l.overallTotalProfitPercent,  l.Mileage as miles, l.deadmiles, l.deadmiles as rmile, l.PickupDate, l.PickupAddress, l.OriginCity, l.OriginState, l.OriginCountry, l.DeliveryDate, l.DestinationAddress, l.DestinationCity, l.DestinationState, l.DestinationCountry, v.driver_type".$extraFields);	
		//}
		$this->db->join("vehicles as v", "l.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "l.driver_id = d.id","Left");
		$this->db->join("users as u", "d.user_id = u.id","Left");
		$this->db->join("broker_info as b", "l.broker_id = b.id","Left");
		$this->db->join('drivers as team','team.id = l.second_driver_id','LEFT');
		
		/*if( (isset($args["scope"]) && $args["scope"] != "" && $args["scope"] != "all"  ) && (isset($args["selScope"]) && !empty($args["selScope"]) ) || ( ($type != "export" && $type != "drivers" && !empty($args["selScope"]) )  ) ){

			$this->db->where_in("l.vehicle_id",$args["selScope"]);
		}*/


		if ( $from == 'dashboard' && ( $type == 'loads' || $type == 'team') ) {
			$args['scope'] = $type;
		}

		if(isset($args["secondDriverId"]) && !empty($args['secondDriverId']) && isset($args['scope']) && $args['scope'] == "team"){
			$driverId = $args["driverId"];
			$this->db->where(array("l.driver_id" => $args["driverId"], 'l.second_driver_id' => $args['secondDriverId']));
		} else if (isset($args["driverId"]) && !empty($args['driverId']) && isset($args['scope']) && $args['scope'] != "team") {
			$driverId = $args["driverId"];
			if(!is_array($args["driverId"])){
				$driverId = array($args["driverId"]);
			}
			$this->db->where_in("l.driver_id",$driverId);
		}

		if((isset($args["scope"]) && $args["scope"] == "dispatcher" || $type=="loads") && isset($args["dispId"]) && $args["dispId"] != ""){
			$this->db->where("l.dispatcher_id",$args["dispId"]);
		}

		if($type == "team"){
			$this->db->where("l.driver_type = ","team");
		}

		if(isset($args["startDate"]) && !empty($args["startDate"])){
			$this->db->where("DATE(l.PickupDate) >= ",date("Y-m-d",strtotime($args["startDate"])));
		}

		if(isset($args["endDate"]) && !empty($args["endDate"])){
			$this->db->where("DATE(l.PickupDate) <= ",date("Y-m-d",strtotime($args["endDate"])));
		}

		$this->db->where_in("l.JobStatus",$this->config->item('loadStatus'));
		$this->db->where('delete_status',0);
		$this->db->order_by("l.DeliveryDate DESC");
		$query = $this->db->get('loads as l');
		 //echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}

	public function getLoadsTrackingAggregate($args,$group_by=false,$from="report"){

		$addFilter ="";
		if(($group_by && $group_by !== "dispatchers" && $from == "dashboard") || (isset($args["scope"]) && $args["scope"] !== "" && $args["scope"] !== "all")) {
			$addFilter = "case l.driver_type when 'team' then concat(d.first_name,' + ',team.first_name) ELSE concat(d.first_name,' ',d.last_name) end as driver, ";
		}

		$this->db->select("CONCAT( `u`.`first_name` , ' ', u.last_name ) AS dispatcher,".$addFilter." ROUND(SUM(l.PaymentAmount),2) as invoice, ROUND(SUM(l.totalCost),2) as charges,   ROUND(SUM(l.Mileage),2) AS miles, ROUND(SUM(l.deadmiles),2) AS deadmiles, ROUND(SUM(l.overallTotalProfit),2) AS profit, ROUND(SUM(l.overallTotalProfitPercent),2) AS profitPercent,u.id as dispatcherId,u.username,l.driver_id as driverId,l.second_driver_id, CONCAT( `d`.`first_name` , ' ', d.last_name ) AS driverName,v.driver_type");
		$this->db->join("vehicles as v", "l.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "l.driver_id = d.id","Left");
		$this->db->join("users as u", "l.dispatcher_id = u.id");
		$this->db->join("broker_info as b", "l.broker_id = b.id","Left");
		$this->db->join('drivers as team','team.id = l.second_driver_id','LEFT');

		if($args["selScope"] && is_array($args["selScope"]) && count($args["selScope"] > 0 ) && $group_by != "drivers"){
			//$this->db->where_in("l.vehicle_id",$args["selScope"]);
		}
// echo $group_by; 
// pr($args);
// die;
		if(isset($args["secondDriverId"]) && !empty($args['secondDriverId']) && isset($args['scope']) && $args['scope'] == "team"){
			$driverId = $args["driverId"];
			$this->db->where(array("l.driver_id" => $args["driverId"], 'l.second_driver_id' => $args['secondDriverId']));
		} else if (isset($args["driverId"]) && !empty($args['driverId']) && isset($args['scope']) && $args['scope'] == "driver") {
			$driverId = $args["driverId"];
			if(!is_array($args["driverId"])){
				$driverId = array($args["driverId"]);
			}
			$this->db->where_in("l.driver_id",$driverId);
		}

		if($group_by == "drivers" || $group_by == "loads" || $group_by == "team"){
			$this->db->where_in('l.dispatcher_id',$args["dispId"]);	
		}

		if(isset($args["startDate"]) && !empty($args["startDate"])){
			$this->db->where("DATE(l.PickupDate) >= ",date("Y-m-d",strtotime($args["startDate"])));
		}

		if(isset($args["endDate"]) && !empty($args["endDate"])){
			$this->db->where("DATE(l.PickupDate) <= ",date("Y-m-d",strtotime($args["endDate"])));
		}
		$this->db->where_in('l.JobStatus',$this->config->item('loadStatus'));

		$this->db->where('delete_status',0);
		$this->db->where("l.driver_id IS NOT NULL");
		$this->db->where("l.driver_id != 0");
		$this->db->where("l.driver_id != ''");
		if(($group_by && $group_by == "dispatchers") || (isset($args["scope"]) && $args["scope"] == "all") || (isset($args["scope"]) && $args["scope"] == "")){
			$this->db->group_by("l.dispatcher_id");	
		} else {
			$this->db->group_by("d.id");	
		}
		$this->db->where_IN('l.JobStatus',$this->config->item('loadStatus'));
		$this->db->order_by("u.first_name");
		$query = $this->db->get('loads as l');
		// echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	/**
	* method   : Get
	* @param   : arguments
	* @return  : results array
	* @comment : fetch records using pagination and sorting
	*/

	public function getTrackingIndividualLoadsPagination($args = array(), $type = 'website', $total = false){
		
		if(!$total) {
			$this->db->select("l.JobStatus,l.id as loadid, CONCAT( `u`.`first_name` , ' ', u.last_name ) AS dispatcher,  case l.driver_type when 'team' then concat(d.first_name,' + ',team.first_name) ELSE concat(d.first_name,' ',d.last_name) end as driver, CONCAT(UCASE(LEFT(b.TruckCompanyName, 1)), LCASE(SUBSTRING(b.TruckCompanyName, 2))) AS broker,  l.paymentAmount as invoice, l.totalCost as charges, l.overallTotalProfit AS profit, l.overallTotalProfitPercent,  l.Mileage as miles, l.deadmiles, l.deadmiles as rmile, l.PickupDate, l.PickupAddress, l.OriginCity, l.OriginState, l.OriginCountry, l.DeliveryDate, l.DestinationAddress, l.DestinationCity, l.DestinationState, l.DestinationCountry");	
		} else {
			$this->db->select("count(l.id) as count");
		}	

		$this->db->join("vehicles as v", "l.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "l.driver_id = d.id","Left");
		$this->db->join("users as u", "d.user_id = u.id","Left");
		$this->db->join("broker_info as b", "l.broker_id = b.id","Left");
		$this->db->join('drivers as team','team.id = l.second_driver_id','LEFT');
		
		if( (isset($args["scope"]) && $args["scope"] != "" && $args["scope"] != "all"  ) && (isset($args["selScope"]) && !empty($args["selScope"]) ) ){
			//$this->db->where_in("l.vehicle_id",$args["selScope"]);
		}

		if(isset($args["secondDriverId"]) && !empty($args['secondDriverId']) && $args['scope'] == "team"){
			$driverId = $args["driverId"];
			$this->db->where(array("l.driver_id" => $args["driverId"], 'l.second_driver_id' => $args['secondDriverId']));
		} else if (isset($args["driverId"]) && !empty($args['driverId']) && $args['scope'] != "team") {
			$driverId = $args["driverId"];
			if(!is_array($args["driverId"])){
				$driverId = array($args["driverId"]);
			}
			$this->db->where_in("l.driver_id",$driverId);
		}

			
		if((isset($args["scope"]) && $args["scope"] == "dispatcher" || $type=="loads") && isset($args["dispId"]) && $args["dispId"] != ""){
			$this->db->where("l.dispatcher_id",$args["dispId"]);
		}

		if($type == "team")
			$this->db->where("l.driver_type = ","team");

		if(isset($args["startDate"]) && !empty($args["startDate"])){
			$this->db->where("DATE(l.PickupDate) >= ",date("Y-m-d",strtotime($args["startDate"])));
		}

		if(isset($args["endDate"]) && !empty($args["endDate"])){
			$this->db->where("DATE(l.PickupDate) <= ",date("Y-m-d",strtotime($args["endDate"])));
		}

		

		if(isset($args["searchQuery"]) && !empty($args["searchQuery"])){
            $this->db->group_start();
            $this->db->like('LOWER(CONCAT(d.first_name," ", d.last_name))', strtolower($args['searchQuery']));
            $this->db->or_like('LOWER(CONCAT(u.first_name," ", u.last_name))', strtolower($args['searchQuery']));
            $this->db->or_like('LOWER(l.LoadType)', strtolower($args['searchQuery']) );
            $this->db->or_like('l.PickupDate', $args['searchQuery'] );
            $this->db->or_like('l.DeliveryDate', $args['searchQuery'] );
            $this->db->or_like('LOWER(l.OriginCity)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(l.OriginState)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(l.DestinationCity)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(l.DestinationState)', strtolower($args['searchQuery']) );
            $this->db->or_like('l.PaymentAmount', $args['searchQuery']);
            $this->db->or_like('l.Mileage', $args['searchQuery']);
            $this->db->or_like('l.deadmiles', $args['searchQuery']);
            $this->db->or_like('l.totalCost', $args['searchQuery'] );
            $this->db->or_like('l.overallTotalProfit', $args['searchQuery'] );
            $this->db->or_like('l.overallTotalProfitPercent', $args['searchQuery'] );
            $this->db->or_like('LOWER(b.TruckCompanyName)', strtolower($args['searchQuery']) );
            $this->db->group_end();
		}

		
		if(isset($args["sortColumn"]) && $args["sortColumn"] == "driver"){
			$this->db->order_by('CASE 
								     WHEN l.driver_type  = "team" THEN CONCAT(d.first_name, " + ", team.first_name) 
								     ELSE concat(d.first_name, " ", d.last_name) 
								 END '.$args["sortType"]);
		} else if( isset($args["sortColumn"]) && $args["sortColumn"] == "broker") {
			$this->db->order_by("b.TruckCompanyName",$args["sortType"]);	
		} else if( isset($args["sortColumn"]) && $args["sortColumn"] == "dispatcher") {
			$this->db->order_by("u.first_name",$args["sortType"]);	
		} else if(isset($args["sortColumn"]) && $args["sortColumn"] == "rpm"){
			$this->db->order_by("(l.PaymentAmount/l.Mileage) ",$args["sortType"]);	 
		}else{ 
			$this->db->order_by("l.".$args["sortColumn"],$args["sortType"]);	
		}

		$this->db->where('delete_status',0);
		$this->db->where_in("l.JobStatus",$this->config->item('loadStatus'));

		if(!$total){
			$args["limitStart"] = $args["limitStart"] == 1 ? 0 : $args["limitStart"];
			$this->db->limit($args["itemsPerPage"],$args["limitStart"]);
		}
		$query = $this->db->get('loads as l');

		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	public function getLoadsTrackingAggregateDashboard($args = array(), $group_by=false, $from="report", $dispatcherId = null){
		$addFilter ="";
		if(($group_by && $group_by !== "dispatchers" && $from == "dashboard") || (isset($args["scope"]) && $args["scope"] !== "" && $args["scope"] !== "all")) {
			$addFilter = "case l.driver_type when 'team' then concat(d.first_name,' + ',team.first_name) ELSE concat(d.first_name,' ',d.last_name) end as driver, ";
		}

		$this->db->select("CONCAT( `u`.`first_name` , ' ', u.last_name ) AS dispatcher,".$addFilter." ROUND(SUM(l.PaymentAmount),2) as invoice, ROUND(SUM(l.totalCost),2) as charges,   ROUND(SUM(l.Mileage),2) AS miles, ROUND(SUM(l.deadmiles),2) AS deadmiles, ROUND(SUM(l.overallTotalProfit),2) AS profit, ROUND(SUM(l.overallTotalProfitPercent),2) AS profitPercent,u.id as dispatcherId,u.username,l.driver_id as driverId,l.second_driver_id, CONCAT( `d`.`first_name` , ' ', d.last_name ) AS driverName,v.driver_type");
		$this->db->join("vehicles as v", "l.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "l.driver_id = d.id","Left");
		$this->db->join("users as u", "l.dispatcher_id = u.id");
		$this->db->join("broker_info as b", "l.broker_id = b.id","Left");
		$this->db->join('drivers as team','team.id = l.second_driver_id','LEFT');

		if(isset($args["secondDriverId"]) && !empty($args['secondDriverId']) && isset($args['scope']) && $args['scope'] == "team"){
			$driverId = $args["driverId"];
			$this->db->where(array("l.driver_id" => $args["driverId"], 'l.second_driver_id' => $args['secondDriverId']));
		} else if (isset($args["driverId"]) && !empty($args['driverId']) && isset($args['scope']) && $args['scope'] == "driver") {
			$driverId = $args["driverId"];
			if(!is_array($args["driverId"])){
				$driverId = array($args["driverId"]);
			}
			$this->db->where_in("l.driver_id",$driverId);
		}

		if($group_by == "drivers" || $group_by == "loads" || $group_by == "team"){
			$this->db->where_in('l.dispatcher_id',$args["dispId"]);	
		}

		if(isset($args["startDate"]) && !empty($args["startDate"])){
			$this->db->where("DATE(l.PickupDate) >= ",date("Y-m-d",strtotime($args["startDate"])));
		}

		if(isset($args["endDate"]) && !empty($args["endDate"])){
			$this->db->where("DATE(l.PickupDate) <= ",date("Y-m-d",strtotime($args["endDate"])));
		}
		$this->db->where_in('l.JobStatus',$this->config->item('loadStatus'));

		$this->db->where('delete_status',0);
		$this->db->where("l.driver_id IS NOT NULL");
		$this->db->where("l.driver_id != 0");
		$this->db->where("l.driver_id != ''");
		if(($group_by && $group_by == "dispatchers") || (isset($args["scope"]) && $args["scope"] == "all") || (isset($args["scope"]) && $args["scope"] == "")){
			$this->db->group_by("l.dispatcher_id");	
		} else {
			$this->db->group_by("d.id");	
		}
		$this->db->where_IN('l.JobStatus',$this->config->item('loadStatus'));
		
		if ( isset($dispatcherId) && $dispatcherId != '')
			$this->db->where('l.dispatcher_id',$dispatcherId);

		$this->db->order_by("u.first_name");
		$query = $this->db->get('loads as l');
	// echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			if ( isset($dispatcherId) && $dispatcherId != '')
				return $query->row_array();
			else
				return $query->result_array();
		} else {
			return array();
		}
	}

	
	/**
	* fetch total driver of dispatchers
	*/

	public function getTotalTeamDrivers( $dispatcherId = null, $startDate = '', $endDate = '' ){
		$startDate = date('Y-m-d H:i:s',strtotime($startDate));
		$endDate  = date('Y-m-d 23:59:59',strtotime($endDate));
		$this->db->distinct('created');
		$this->db->select('assigned_drivers,single,team,assigned_team,DATE(created) as createdDate, created as createdTime');
		$this->db->where(array('dispatcher_id' => $dispatcherId, 'created >=' => $startDate, 'created <=' => $endDate));
		//$this->db->group_by('Date(created)');
		$this->db->order_by('created','ASC');
		$result = $this->db->get('disp_dri_logs');
		// echo $this->db->last_query(); 
		if( $result->num_rows() > 0 )
			return $result->result_array();
		else 
			return array();

	}

	/**
	* fetch total driver of dispatchers without date range
	*/
	public function getTotalDriversList($dispatcherId = null, $startDate = '', $endDate = '') {
		$this->db->select('assigned_drivers,single,team,assigned_team,DATE(created) as createdDate');
		$this->db->where(array('dispatcher_id' => $dispatcherId));
		$this->db->order_by('created','DESC');
		$result = $this->db->get('disp_dri_logs');
		// echo $this->db->last_query(); 
		if( $result->num_rows() > 0 )
			return $result->row_array();
		else 
			return array();
	}

	/**
	* Fetch dispatcher last log from start date
	*/

	public function getDispatcherLastLog( $dispatcherId = null, $startDate = '' ) {
		$startDate = date('Y-m-d 23:59:59',strtotime($startDate));
		$this->db->select('assigned_drivers,single,team,assigned_team,DATE(created) as createdDate');
		$this->db->where(array('dispatcher_id' => $dispatcherId,'created <=' => $startDate));
		$this->db->order_by('created','DESC');
		$result = $this->db->get('disp_dri_logs');
		if( $result->num_rows() > 0 )
			return $result->row_array();
		else 
			return array();
	}

	/**
	 * Getting list of dispatcher for job ticket page
	 */
	
	public function getDispatchersListForGoals($args = array()) {
		
		// $startDate = date('Y-m-d H:i:s',strtotime($args['startDate']));
		// $endDate  = date('Y-m-d 23:59:59',strtotime($args['endDate']));
		
		// $this->db->distinct('dispatcher_id');
		// $this->db->select("dispatcher_id AS dispId,users.username,CONCAT( `users`.`first_name` , ' ', users.last_name ) AS dispatcher,");
		// $this->db->join('users','users.id = dispatcher_id');
		// $this->db->where('users.status',1);
		// $this->db->where(array('disp_dri_logs.created >=' => $startDate, 'disp_dri_logs.created <=' => $endDate));
		
		// $result = $this->db->get('disp_dri_logs');
		// if ( $result->num_rows() > 0 ) {
		// 	return $result->result_array();
		// } else {
			$inArray = array(2,5);					// role id for 2 for dispatchers and role id 5 for admin_dispatcher
			$this->db->select("users.id AS dispId,users.username,CONCAT( `users`.`first_name` , ' ', users.last_name ) AS dispatcher,");
			$this->db->where('users.status',1);
			$this->db->where_in('users.role_id',$inArray);
			$this->db->where_not_in('users.id',array(23,25,27));
			$result = $this->db->get('users');
			if ( $result->num_rows() > 0 ) {
				return $result->result_array();
			} else {
				return array();
			}
			
		// }
	}

	/**
	* Get list of drivers under particular dispatcher within given range
	*/

	public function getDispatcherDriverDashboard( $args = array() ) {
		$this->db->distinct('l.driver_id');
		$this->db->select("l.driver_id as driverId,l.second_driver_id, l.driver_type,l.DeliveryDate");
		$this->db->join("vehicles as v", "l.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "l.driver_id = d.id","Left");
		$this->db->join('drivers as team','team.id = l.second_driver_id','LEFT');

		$this->db->where('l.dispatcher_id',$args["dispId"]);	
		
		if(isset($args["startDate"]) && !empty($args["startDate"])){
			$this->db->where("DATE(l.PickupDate) >= ",date("Y-m-d",strtotime($args["startDate"])));
		}

		if(isset($args["endDate"]) && !empty($args["endDate"])){
			$this->db->where("DATE(l.PickupDate) <= ",date("Y-m-d",strtotime($args["endDate"])));
		}
		$this->db->where_in('l.JobStatus',$this->config->item('loadStatus'));

		$this->db->where('delete_status',0);
		$this->db->where("l.driver_id IS NOT NULL");
		$this->db->where("l.driver_id != 0");
		$this->db->where("l.driver_id != ''");
		
		$this->db->group_by("d.id");	
		$this->db->order_by('l.DeliveryDate','DESC');

		$query = $this->db->get('loads as l');
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return array();
		}

	}

	
	public function getLoadsTrackingDriverDashboardNew($args = array(), $group_by=false, $dispatcherId = null){
		
		$addFilter ="";
		if(($group_by && $group_by !== "dispatchers" ) || (isset($args["scope"]) && $args["scope"] !== "" && $args["scope"] !== "all")) {
			$addFilter = "case l.driver_type when 'team' then concat(d.first_name,' + ',team.first_name) ELSE concat(d.first_name,' ',d.last_name) end as driver, ";
		}

		$this->db->select("CONCAT( `u`.`first_name` , ' ', u.last_name ) AS dispatcher,".$addFilter." ROUND(SUM(l.PaymentAmount),2) as invoice, ROUND(SUM(l.totalCost),2) as charges,   ROUND(SUM(l.Mileage),2) AS miles, ROUND(SUM(l.deadmiles),2) AS deadmiles, ROUND(SUM(l.overallTotalProfit),2) AS profit, ROUND(SUM(l.overallTotalProfitPercent),2) AS profitPercent,u.id as dispatcherId,u.username,l.driver_id as driverId,l.second_driver_id, CONCAT( `d`.`first_name` , ' ', d.last_name ) AS driverName,v.driver_type");
	
		$this->db->join("vehicles as v", "l.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "l.driver_id = d.id","Left");
		$this->db->join("users as u", "l.dispatcher_id = u.id");
		$this->db->join('drivers as team','team.id = l.second_driver_id','LEFT');

		$driverId = $args["driverId"];
		if(isset($args["secondDriverId"]) && !empty($args['secondDriverId']) && $group_by == 'team'){
			$this->db->where(array("l.driver_id" => $args["driverId"], 'l.second_driver_id' => $args['secondDriverId']));
		} else if (isset($args["driverId"]) && !empty($args['driverId']) && $group_by == 'driver') {
			$this->db->where("l.driver_id",$driverId);
		}

		if(isset($args["startDate"]) && !empty($args["startDate"])){
			$this->db->where("DATE(l.PickupDate) >= ",date("Y-m-d",strtotime($args["startDate"])));
		}

		if(isset($args["endDate"]) && !empty($args["endDate"])){
			$this->db->where("DATE(l.PickupDate) <= ",date("Y-m-d",strtotime($args["endDate"])));
		}
		

		$this->db->where('delete_status',0);
		$this->db->where("l.driver_id IS NOT NULL");
		$this->db->where("l.driver_id != 0");
		$this->db->where("l.driver_id != ''");			
		
		$this->db->where_IN('l.JobStatus',$this->config->item('loadStatus'));
		
		$this->db->where('l.dispatcher_id',$dispatcherId);
		$this->db->group_by("d.id");
		$this->db->order_by("u.first_name");
		$query = $this->db->get('loads as l');
		 // echo $this->db->last_query();
		if ($query->num_rows() > 0) {
			// if ( isset($dispatcherId) && $dispatcherId != '') 
			 	return $query->row_array();
			// else
				// return $query->result_array();
		} else {
			return array();
		}
	}

	/**
	* Fetch name of driver
	*/

	public function fetchDriverName( $driverId = null ) {
		if ( strpos($driverId,':') !== false ) {
			$ids = explode(':',$driverId);
			$fir = $this->getName($ids[0]);
			$las = $this->getName($ids[1]);

			$driverName = $fir['driverName'].' + '.$las['driverName'];
			return $driverName;			
		} else {
			$this->db->select("CONCAT( `drivers`.`first_name` , ' ', drivers.last_name ) AS driverName");
			$this->db->where('drivers.id',$driverId);
			$result = $this->db->get('drivers');
			$driverName = $result->row_array();
			if ( !empty($driverName) ) 
				return $driverName['driverName'];
			else
				return '';
		}

	}

	public function getName( $driverId = null) {
		$this->db->select("first_name AS driverName");
		$this->db->where('drivers.id',$driverId);
		$result = $this->db->get('drivers');
		return $result->row_array();
	}


}
