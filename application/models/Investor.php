<?php
class Investor extends Parent_Model {

	function __construct(){
		parent::__construct();
        $this->load->library('email');
        $this->limit_per_page = $this->config->item('limit_per_page');  
    }


    /**
	* @param : null
	* @return: list array
	* comment: fetch vehicles listing from investor_vehicle table
	**/
	public function fetchVehiclesList($userId = false){
		$this->db->select("v.id, CONCAT('Truck - ',v.label) as vehicleName");
		$this->db->join("investor_vehicles as iv","v.id = iv.vehicle_id AND iv.current_assigned = 1","inner");
		$this->db->where("iv.investor_id",$userId);
		$result = $this->db->get("vehicles as v")	;

        if($result->num_rows()>0){
            return $result->result_array();
        } else {
            return array();
        }
    }

    /**
	* @param : null
	* @return: list array
	* comment: fetch vehicles listing from investor_vehicle table
	**/

	public function vehiclesWithFilter($userId = false, $vehicleId = false){
		$this->db->select("CASE v.driver_type 
							WHEN 'team' THEN CONCAT(d.first_name,' + ',team.first_name)  
							ELSE concat(d.first_name,' ',d.last_name)  
							END AS driverName, ABS( TIMESTAMPDIFF(MINUTE , Now( ) , v.`modified` ) ) AS mintues_ago,  v.`modified` AS timestamp, v.label, v.latitude, v.tracker_id, v.longitude, v.telemetry, v.id as vehicle_id, v.city, v.vehicle_address, v.state, v.team_driver_id");

		$this->db->join("drivers as d", "v.driver_id = d.id","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		$this->db->join("investor_vehicles as iv","v.id = iv.vehicle_id AND iv.current_assigned = 1","inner");
		if( $userId ) { $this->db->where(array('iv.investor_id' => $userId)); }
		if( $vehicleId && $vehicleId != "all" ) { $this->db->where(array('v.id' => $vehicleId)); }
		$this->db->where(array( 'v.vehicle_status' => 1) );
		$result = $this->db->get('vehicles as v');
		if($result->num_rows()>0){
            return $result->result_array();
        } else {
            return array();
        }
	}


	/**
	* @param : fromDate, toDate, vehicles
	* @return: list array
	* comment: fetch revenue done by specific vehicles on sepcified date
	**/
	public function vehiclesRevenueWithFilter($fromDate, $toDate, $vehicles){
		$this->db->select("SUM( paymentamount )  as sentPayment");
		$this->db->where("FIND_IN_SET( id, ( SELECT GROUP_CONCAT( sp.loadids ) AS id FROM `save_payment_confirmCode` AS `sp` WHERE DATE( sp.created ) >= '".$fromDate."' AND DATE( sp.created ) <= '".$toDate."'))");
		$this->db->where_in("loads.vehicle_id",$vehicles);
		$this->db->where("loads.delete_status", 0);
		$result = $this->db->get('loads');
		//echo $this->db->last_query()."<br/><br/>";die;
		if ( $result->num_rows() > 0 )
			return $result->row_array()["sentPayment"];
		else 
			return array();
	}


	/**
	* Method : getVehiclesJobs 
	* @param : fromDate, toDate, vehicles
	* @return: list array
	* comment: fetch jobs done by specific vehicles on sepcified date
	**/
	public function getVehiclesJobs( $args = array(), $vehicles = array(), $purpose = "" ){
		$this->db->select('CASE loads.billType 
        							WHEN "shipper" THEN (shippers.shipperCompanyName) 
        									   ELSE (broker_info.TruckCompanyName)
        									END AS companyName, 
        					loads.PickupDate, loads.id, v.label, loads.DeliveryDate, CONCAT( loads.OriginCity, " ", loads.OriginState ) AS origin , CONCAT( loads.DestinationCity, " ", loads.DestinationState ) AS destination, loads.PaymentAmount, loads.Mileage, (loads.PaymentAmount/loads.Mileage) as rpm, loads.deadmiles, loads.totalCost, DATE( loads.created ) AS created, loads.overallTotalProfit, loads.overallTotalProfitPercent');

		$this->db->join("vehicles as v", "loads.vehicle_id = v.id","Left");
		$this->db->join("broker_info", "broker_info.id = loads.broker_id AND loads.billType = 'broker'","Left");
		$this->db->join("shippers", "shippers.id = loads.broker_id AND loads.billType = 'shipper'","Left");

		if( $purpose == "traveling" ){
			/*$this->db->where( "(( DATE(loads.PickupDate)  = '".$date."' AND loads.JobStatus = 'booked')  OR (DATE(loads.DeliveryDate) = '".$date."' and loads.JobStatus IN ('completed','inprogress', 'booked', 'delayed', 'delivered', 'invoiced') )
				OR (
				  	loads.pickupdate <= '".$date."' AND loads.DeliveryDate >= '".$date."' AND loads.JobStatus IN('completed','inprogress','booked','delayed','delivered','invoiced')
				  )
				)");*/
		}else{
			if(isset($args["startDate"]) && !empty($args["startDate"])){
				$this->db->where("DATE(loads.PickupDate) >= ",date("Y-m-d",strtotime($args["startDate"])));
			}

			if(isset($args["endDate"]) && !empty($args["endDate"])){
				$this->db->where("DATE(loads.PickupDate) <= ",date("Y-m-d",strtotime($args["endDate"])));
			}	
		}

		

		$this->db->where_in("loads.vehicle_id",$vehicles);
		$this->db->where("loads.delete_status", 0);
		$this->db->where_IN('loads.JobStatus',$this->config->item('loadStatus'));
		if( $purpose == "traveling" ){
			$this->db->group_by("loads.vehicle_id");
		}
		$result = $this->db->get('loads');
		//echo $this->db->last_query()."<br/><br/>";
		if ( $result->num_rows() > 0 )
			return $result->result_array();
		else 
			return array();
	}


	/**
	* @param : fromDate, toDate, vehicles
	* @return: list array
	* comment: fetch revenue done by specific vehicles on sepcified date
	**/
	public function vehiclesWithoutDriver( $filters = array(), $vehicles, $total = false ) {
		if($total){
			$this->db->select('count(v.id) as vehiclesWithoutDriver');	
		}
		//else{
		//	$this->db->select("v.id as vehicleId, 'trucksWithoutDriver' as recordType,  v.label, v.vin, v.model");	
		//}

		$this->db->where('v.vehicle_status',1);
		$this->db->where_in('v.id', $vehicles );
		$this->db->group_start();
		$this->db->where("v.driver_id = 0 OR v.driver_id IS NULL OR  v.driver_id = ''");
		$this->db->group_end();


		if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){
            $this->db->group_start();
            $this->db->like("LOWER(v.label)", strtolower($filters['searchQuery']));
            $this->db->or_like("LOWER(v.vin)", strtolower($filters['searchQuery']));
            $this->db->or_like("LOWER(v.model)", strtolower($filters['searchQuery']));
            $this->db->group_end();
		}
		//$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
		//$this->db->order_by("v.label ".$filters["sortType"]);

		if(!$total){
			if(empty($filters["export"])){
				//$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
			}
		}

		$result = $this->db->get('vehicles as v');
		// echo $this->db->last_query();die;
		if($total){
			return $result->row_array()["vehiclesWithoutDriver"];
		}

		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return array();
		}

	}
}