<?php

class Report extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	public function getAllVehicles(){
		$this->db->select('id,tracker_id, CONCAT("Truck - ",label) as truck');
		$this->db->where('tracker_id !=','');
		$this->db->where('tracker_id !=','0');
		$query = $this->db->get('vehicles');
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	public function getBreadcrumbsDetail($args){
		$includeLatLong = '';
		if(isset($args['includeLatLong']) && $args['includeLatLong']){
			$includeLatLong = 'ae.`latitude` , ae.`longitude`';
		}
		$this->db->select("ae.`deviceID`,  CONCAT(DATE_FORMAT(GMTTime,'%d-%b-%Y %r'),' GMT') AS GMTTime , ae.`eventType` , ".$includeLatLong." , CONCAT( ae.`street` , ', ', ae.`crossStreet` , ', ', ae.`city` , ', ', ae.`state` , ', ', ae.`zip` ) AS location, ae.`vehicleSpeed` , ae.`odometer`, v.label, CONCAT(d.first_name, ' ', d.last_name) as driverName,
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

		$this->db->where('DATE(ae.GMTTime) =',$args['customDate']);
		$this->db->order_by('ae.GMTTime','asc');
		$query = $this->db->get('avl_events as ae');

		//echo $this->db->last_query();die;
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

		$this->db->select("l.id as loadid, CONCAT( `u`.`first_name` , ' ', u.last_name ) AS dispatcher,  case l.driver_type when 'team' then concat(d.first_name,' + ',team.first_name) ELSE concat(d.first_name,' ',d.last_name) end as driver, CONCAT(UCASE(LEFT(b.TruckCompanyName, 1)), LCASE(SUBSTRING(b.TruckCompanyName, 2))) AS broker,  l.paymentAmount as invoice, l.totalCost as charges, l.overallTotalProfit AS profit, l.overallTotalProfitPercent,  l.Mileage as miles, l.deadmiles, l.deadmiles as rmile, l.PickupDate, l.PickupAddress, l.DeliveryDate, l.DestinationAddress ".$extraFields);	
		//}
		$this->db->join("vehicles as v", "l.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "l.driver_id = d.id","Left");
		$this->db->join("users as u", "d.user_id = u.id","Left");
		$this->db->join("broker_info as b", "l.broker_id = b.id","Left");
		$this->db->join('drivers as team','team.id = l.second_driver_id','LEFT');
		
		if( (isset($args["scope"]) && $args["scope"] != "dispatcher" && $args["scope"] != "") && isset($args["selScope"]) && $args["selScope"] != ""  || ($type != "export" && $type != "drivers" && $args["selScope"] && $args["selScope"] !="" )  ){

			$this->db->where_in("l.vehicle_id",$args["selScope"]);
		}

		if(isset($args["driverId"])){
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




		$this->db->where('delete_status',0);
		$this->db->order_by("l.DeliveryDate");
		$query = $this->db->get('loads as l');
		//echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	public function getLoadsTrackingAggregate($args,$group_by=false,$from="report"){
		$addFilter ="";
		if(($group_by && $group_by !== "dispatchers" && $from == "dashboard") || (isset($args["scope"]) && $args["scope"] !== "" && $args["scope"] !== "all")) {
			$addFilter = "case l.driver_type when 'team' then concat(d.first_name,' + ',team.first_name) ELSE concat(d.first_name,' ',d.last_name) end as driver, ";
		}


		$this->db->select("CONCAT( `u`.`first_name` , ' ', u.last_name ) AS dispatcher,".$addFilter." ROUND(SUM(l.paymentAmount),2) as invoice, ROUND(SUM(l.totalCost),2) as charges,   ROUND(SUM(l.Mileage),2) AS miles, ROUND(SUM(l.deadmiles),2) AS deadmiles, ROUND(SUM(l.overallTotalProfit),2) AS profit, ROUND(SUM(l.overallTotalProfitPercent),2) AS profitPercent,");
		$this->db->join("vehicles as v", "l.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "l.driver_id = d.id","Left");
		$this->db->join("users as u", "l.dispatcher_id = u.id");
		$this->db->join("broker_info as b", "l.broker_id = b.id","Left");
		$this->db->join('drivers as team','team.id = l.second_driver_id','LEFT');
		if($args["selScope"] && is_array($args["selScope"]) && count($args["selScope"] > 0 ) && $group_by != "drivers"){
			$this->db->where_in("l.vehicle_id",$args["selScope"]);
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
		$this->db->where('delete_status',0);
		$this->db->where("l.driver_id IS NOT NULL");
		$this->db->where("l.driver_id != 0");
		$this->db->where("l.driver_id != ''");
		if(($group_by && $group_by == "dispatchers") || (isset($args["scope"]) && $args["scope"] == "all") || (isset($args["scope"]) && $args["scope"] == "")){
			$this->db->group_by("l.dispatcher_id");	
		}else{
			$this->db->group_by("v.driver_id");	
		}
		$this->db->order_by("u.first_name");
		$query = $this->db->get('loads as l');
		//echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}


}
?>
