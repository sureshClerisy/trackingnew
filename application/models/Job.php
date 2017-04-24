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

	public function getTodayReport($args = null, $type, $reportType = "pickup",$date = ''){
		if(empty($date)){ $date = date('Y-m-d'); }
		
		if($reportType == "exceptIdle"){
			$this->db->select("count(DISTINCT d.id) as total");
		}else{
			$this->db->select("loads.created,b.TruckCompanyName as companyName,CASE loads.driver_type 
    						WHEN 'team' THEN CONCAT(d.first_name,' + ',team.first_name) 
    									ELSE concat(d.first_name,' ',d.last_name) 
    									END AS driverName,loads.invoiceNo,loads.totalCost,loads.overallTotalProfit,loads.overallTotalProfitPercent,loads.Mileage,CONCAT('Truck - ',v.label) as truckName, CONCAT(u.first_name, ' ', u.last_name) as dispatcher, loads.PickupDate, loads.DeliveryDate, loads.OriginCity, loads.OriginState, loads.DestinationCity,loads.DestinationState, loads.PaymentAmount, (loads.PaymentAmount/loads.Mileage) as RPM, loads.deadmiles,loads.id,loads.JobStatus,loads.pickDate,loads.invoiceNo");
		}

		$this->db->join("vehicles as v", "loads.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "loads.driver_id = d.id","Left");
		$this->db->join('users as u', 'loads.dispatcher_id = u.id','Left');
		$this->db->join("broker_info as b", "loads.broker_id = b.id","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	

		if($type == "_idispatcher"){
			$this->db->where_in('loads.dispatcher_id',$args["dispId"]);
		}else if($type == "_idriver" || $type == "driver" ) {
			$this->db->where_in('loads.dispatcher_id',$args["dispId"]);
			$this->db->where_in('loads.driver_id',$args["driverId"]);
		} else if( $type == "_iteam" || $type == "team"){
			$this->db->where(array("loads.driver_id" => $args["driverId"], 'loads.second_driver_id' => $args['secondDriverId'],'loads.dispatcher_id' => $args["dispId"]));
		}

		if($type == "team" || $type == "_team" || $type == "_iteam"){
			$this->db->where('loads.driver_type = ',"team");
		}
		
		$this->db->where(array('loads.delete_status' => 0));

		if($reportType == "booked"){
			$this->db->where( "DATE(loads.PickupDate) ",  $date);	
			$this->db->where('loads.JobStatus',"booked");	
		}else if($reportType == "delivery"){
			$this->db->where( "DATE(loads.DeliveryDate) ", $date );	
			$this->db->where_in('loads.JobStatus',array('booked','inprogress'));	
		}else if($reportType == "inprogress"){
			$this->db->where('loads.JobStatus',"inprogress");	
		}else if($reportType == "exceptIdle"){
			$this->db->where("d.status",1);
			$this->db->where( "(( DATE(loads.PickupDate)  = '".$date."' AND loads.JobStatus = 'booked')  OR (DATE(loads.DeliveryDate) = '".$date."' and loads.JobStatus IN ('completed','inprogress', 'booked', 'delayed', 'delivered', 'invoiced') )
				OR (
				  	loads.pickupdate <= '".$date."' AND loads.DeliveryDate >= '".$date."' AND loads.JobStatus IN('completed','inprogress','booked','delayed','delivered','invoiced')
				  )

				)");	
		}

		$this->db->order_by("loads.DeliveryDate DESC");
		$query = $this->db->get('loads');
		//echo $this->db->last_query()."<br/> <br/>";
		if($reportType == "exceptIdle"){
			return $query->row_array()["total"];
		}
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}	

	public function getIdleDrivers($args = null, $type ='', $rtype = '', $date = ''){

		if(empty($date)){ $date = date("Y-m-d"); }
		if($rtype == "getIdleIds" ){
			$this->db->select("group_concat(d.id) as total");
		}if($rtype=="idle"){
			$this->db->select("count(DISTINCT d.id) as total");
		}else{
			$this->db->select("d.id as driver_id, v.team_driver_id,  CONCAT( 'Truck - ', v.label) AS truckName, CONCAT(u.first_name, ' ' , u.last_name) as dispatcher, CASE v.driver_type 
							WHEN 'team' THEN CONCAT(d.first_name,' + ',team.first_name)  ELSE concat(d.first_name,' ',d.last_name)  END AS driverName");
		}
		
		
		$this->db->join("vehicles as v", "d.id = v.driver_id","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		$this->db->join('users as u','d.user_id = u.id','left');	
		if(isset($args["userToken"])){
			$userToken = $args["userToken"];
			$dispId = $args["userToken"];
		}
		if(isset($args["dispId"])){
			$dispId = $args["dispId"];	
		}
		if(isset($args["driverId"])){
			$userToken = $args["driverId"];	
		}


		if($type == "_idispatcher" || $type == "dispatcher"){
			$this->db->where_in('d.user_id',$dispId);
		}else if($type == "_idriver" || $type == "driver" ){
			$this->db->where_in('d.user_id',$dispId);
			$this->db->where_in('d.id',$userToken);
		}else if( $type == "_iteam" || $type == "team"){
			$this->db->where_in('d.user_id',$dispId);
			$this->db->where(array("d.id" => $userToken, 'v.team_driver_id' => $args['secondDriverId']));
		}

		if($type == "team" || $type == "_team" || $type == "_iteam"){
			$this->db->where('v.driver_type ',"team");
		}
		
		$this->db->where("d.status = 1 and d.id NOT IN (
							  SELECT DISTINCT loads.driver_id FROM loads where loads.delete_status = 0  AND 
							  ( (loads.pickupdate = '".$date."' AND loads.JobStatus = 'booked') OR (loads.DeliveryDate = '".$date."' AND loads.JobStatus IN('completed','inprogress', 'booked', 'delayed', 'delivered', 'invoiced'))
							  OR (
							  	loads.pickupdate <= '".$date."' AND loads.DeliveryDate >= '".$date."' AND loads.JobStatus IN('completed','inprogress','booked','delayed','delivered','invoiced')
							  )
						)) 
						AND d.id IN (SELECT DISTINCT  driver_id  FROM `vehicles` where driver_id != '0' and driver_id is not null and driver_id != '')	
						AND d.id NOT IN (SELECT DISTINCT vehicles.team_driver_id FROM vehicles where vehicles.driver_type = 'team')
						");
	
		
		$this->db->order_by("CONCAT(u.first_name, ' ' , u.last_name)");

		$query = $this->db->get('drivers as d');

		//echo $this->db->last_query()."<br/><br/>";die;
		if($rtype=="idle" || $rtype == "getIdleIds"){
			return $query->row_array()["total"];
		}
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}	


	public function getDriversWithFilter($filters = array(), $type ='', $rtype = '', $date = '', $total = false){
		
		if(empty($date)){ $date = date("Y-m-d"); }
		if($rtype == "getIdleIds" ){
			$this->db->select("group_concat(d.id) as total");
		}if($rtype=="idle"){
			$this->db->select("count(DISTINCT d.id) as total");
		}else{
			$this->db->select("d.id as driver_id, v.team_driver_id,  CONCAT( 'Truck - ', v.label) AS truckName, CONCAT(u.first_name, ' ' , u.last_name) as dispatcher, CASE v.driver_type 
							WHEN 'team' THEN CONCAT(d.first_name,' + ',team.first_name)  ELSE concat(d.first_name,' ',d.last_name)  END AS driverName");
		}
		
		
		$this->db->join("vehicles as v", "d.id = v.driver_id","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		$this->db->join('users as u','d.user_id = u.id','left');

		if(isset($filters["userToken"])){
			$userToken = $filters["userToken"];
		}
		if(isset($filters["dispId"])){
			$dispId = $filters["dispId"];	
		}	

		if(isset($filters["driverId"])){
			$userToken = $filters["driverId"];	
		}
		if($type == "_idispatcher" || $type == "dispatcher"){
			$this->db->where_in('d.user_id',$dispId);
		}else if($type == "_idriver" || $type == "driver" ){
			$this->db->where_in('d.user_id',$dispId);
			$this->db->where_in('d.id',$userToken);
		}else if( $type == "_iteam" || $type == "team"){
			$this->db->where_in('d.user_id',$dispId);
			$this->db->where(array("d.id" => $userToken, 'v.team_driver_id' => $filters['secondDriverId']));
		}

		if($type == "team" || $type == "_team" || $type == "_iteam"){
			$this->db->where('v.driver_type ',"team");
		}
		
		$this->db->where("d.status = 1 and d.id NOT IN (
							  SELECT DISTINCT loads.driver_id FROM loads where loads.delete_status = 0  AND 
							  ( (loads.pickupdate = '".$date."' AND loads.JobStatus = 'booked') OR (loads.DeliveryDate = '".$date."' AND loads.JobStatus IN('completed','inprogress', 'booked', 'delayed', 'delivered', 'invoiced'))
							  OR (
							  	loads.pickupdate <= '".$date."' AND loads.DeliveryDate >= '".$date."' AND loads.JobStatus IN('completed','inprogress','booked','delayed','delivered','invoiced')
							  )
						)) 
						AND d.id IN (SELECT DISTINCT  driver_id  FROM `vehicles` where driver_id != '0' and driver_id is not null and driver_id != '')	
						AND d.id NOT IN (SELECT DISTINCT vehicles.team_driver_id FROM vehicles where vehicles.driver_type = 'team')
						");
	
		


		if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){

            $this->db->group_start();
            $this->db->like("LOWER(CONCAT( 'Truck - ', v.label))", strtolower($filters['searchQuery']));
            $this->db->or_like("LOWER(CONCAT(TRIM(u.first_name), ' ' , TRIM(u.last_name)))", strtolower($filters['searchQuery']));
            $this->db->or_like("LOWER(CONCAT(TRIM(d.first_name),' + ',TRIM(team.first_name)))", strtolower($filters['searchQuery']));
            $this->db->or_like("LOWER(concat(TRIM(d.first_name),' ',TRIM(d.last_name)))", strtolower($filters['searchQuery']));
            $this->db->group_end();
		}
		$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
	
		if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "driverName"){
			$this->db->order_by('CASE 
								     WHEN loads.driver_type  = "team" THEN CONCAT(TRIM(d.first_name),' + ',TRIM(team.first_name))  
								     ELSE concat(TRIM(d.first_name), " ", TRIM(d.last_name)) 
								 END '.$filters["sortType"]);
		}else if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "dispatcher"){
			$this->db->order_by("CONCAT(u.first_name, ' ' , u.last_name) ".$filters["sortType"]);
		}else{
			$this->db->order_by("CONCAT(u.first_name, ' ' , u.last_name) ".$filters["sortType"]);
			//$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);	
		}
		if(!$total){
			$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
		}

		$query = $this->db->get('drivers as d');
		//echo $this->db->last_query();die;
		if($total){
			return $query->row_array()["total"];
		}


		if($rtype=="idle" || $rtype == "getIdleIds"){
			return $query->row_array()["total"];
		}
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}	


	public function getIdleDriversLoads($filters = array(), $type, $total = false){
		if($total){
			$this->db->select('count(loads.id) as total'); 
		}else{
			$this->db->select('CONCAT( d.first_name ," + " ,team.first_name) AS teamdriverName, 
	    					CASE loads.driver_type 
	    						WHEN "team" THEN CONCAT(d.first_name," + ",team.first_name) 
									ELSE concat(d.first_name," ",d.last_name) 
									END AS driverName, loads.driver_type, loads.id, loads.invoiceNo, loads.vehicle_id, loads.truckstopID,loads.Bond,loads.PointOfContactPhone,loads.equipment_options,loads.LoadType,loads.PickupDate,loads.DeliveryDate,loads.OriginCity,loads.OriginState,loads.DestinationCity,loads.DestinationState,loads.PickupAddress,loads.DestinationAddress,loads.PaymentAmount,loads.Mileage, (loads.PaymentAmount/loads.Mileage) as rpm, loads.deadmiles,loads.Weight,loads.created,loads.overallTotalProfitPercent,loads.overallTotalProfit,loads.Length,loads.JobStatus,loads.totalCost,loads.pickDate,loads.load_source,b.TruckCompanyName as companyName
								');	
		}
		$this->db->join("vehicles as v", "loads.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "loads.driver_id = d.id","Left");
		$this->db->join('users as u', 'loads.dispatcher_id = u.id','Left');
		$this->db->join("broker_info as b", "loads.broker_id = b.id","Left");
		$this->db->join('drivers as team','loads.second_driver_id = team.id','left');	
		$this->db->where('loads.delete_status',0);
		if($type == "_idispatcher" || $type == "dispatcher"){
			$this->db->where_in('loads.dispatcher_id',$filters["userToken"]);
		}else if($type == "_idriver" || $type == "driver" ) {
			$this->db->where_in('loads.driver_id',$filters["driverId"]);
		} else if( $type == "_iteam" || $type == "team"){
			$this->db->where(array("loads.driver_id" => $filters["driverId"], 'loads.second_driver_id' => $filters['secondDriverId']));
		}

		if($type == "team" || $type == "_team" || $type == "_iteam"){
			$this->db->where('loads.driver_type = ',"team");
		}


		if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){

            $this->db->group_start();
            $this->db->like('LOWER(CONCAT(TRIM(d.first_name)," ",TRIM(d.last_name)))', strtolower($filters['searchQuery']));
            $this->db->or_like('loads.id', $filters['searchQuery'] );
            $this->db->or_like('loads.invoiceNo', $filters['searchQuery'] );
            $this->db->or_like('loads.PointOfContactPhone', $filters['searchQuery'] );
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
            $this->db->or_like('LOWER(b.TruckCompanyName)', strtolower($filters['searchQuery']) );
            //$this->db->or_like('LOWER(CONCAT( d.first_name ," + " ,team.first_name))', strtolower($filters['searchQuery']));
            $this->db->group_end();
		}
		$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
	
		if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "driverName"){
			$this->db->order_by('CASE 
								     WHEN loads.driver_type  = "team" THEN CONCAT(TRIM(d.first_name), " + ", TRIM(team.first_name)) 
								     ELSE concat(d.first_name, " ", d.last_name) 
								 END '.$filters["sortType"]);
		}else if(in_array($filters["sortColumn"] ,array("TruckCompanyName"))){
			$this->db->order_by("b.".$filters["sortColumn"],$filters["sortType"]);	
		}else if($filters["sortColumn"] == "rpm"){
			$this->db->order_by("(loads.PaymentAmount/loads.Mileage) ",$filters["sortType"]);	 
		}else if($filters["sortColumn"] == "Weight"){
			$this->db->order_by("CAST(loads.".$filters["sortColumn"]."  AS DECIMAL)",$filters["sortType"]);	
		}else{
			$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);	
		}
		if(!$total){
			$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
		}

		$query = $this->db->get('loads');
		if($total){
			return $query->row_array()["total"];
		}
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	
	}
	

	public function getActiveDrivers($filters = array(), $type, $date = '',$total = false){
		if(empty($date)){ $date = date('Y-m-d'); }

		if(count($filters) <= 0 || !$filters){
			$filters = array("itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC","status"=>"");
		}

		
		if($total){
			$this->db->select("count(DISTINCT d.id) as total");
		}else{
			$this->db->select('CONCAT( d.first_name ," + " ,team.first_name) AS teamdriverName, 
    					CASE loads.driver_type 
    						WHEN "team" THEN CONCAT(d.first_name," + ",team.first_name) 
								ELSE concat(d.first_name," ",d.last_name) 
								END AS driverName, loads.driver_type, loads.id, loads.invoiceNo, loads.vehicle_id, loads.truckstopID,loads.Bond,loads.PointOfContactPhone,loads.equipment_options,loads.LoadType,loads.PickupDate,loads.DeliveryDate,loads.OriginCity,loads.OriginState,loads.DestinationCity,loads.DestinationState,loads.PickupAddress,loads.DestinationAddress,loads.PaymentAmount,loads.Mileage, (loads.PaymentAmount/loads.Mileage) as rpm, loads.deadmiles,loads.Weight,loads.Length,loads.JobStatus,loads.totalCost,loads.pickDate,loads.load_source,b.TruckCompanyName as companyName
							');	
		}
			

		$this->db->join("vehicles as v", "loads.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "loads.driver_id = d.id","Left");
		$this->db->join('users as u', 'loads.dispatcher_id = u.id','Left');
		$this->db->join("broker_info as b", "loads.broker_id = b.id","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	

		if($type == "_idispatcher" || $type == "dispatcher"){
			$this->db->where_in('loads.dispatcher_id',$filters["userToken"]);
		}else if($type == "_idriver" || $type == "driver" ) {
			$this->db->where_in('loads.dispatcher_id',$filters["dispId"]);
			$this->db->where_in('loads.driver_id',$filters["userToken"]);
		} else if( $type == "_iteam" || $type == "team"){
			$this->db->where(array("loads.driver_id" => $filters["userToken"], 'loads.second_driver_id' => $filters['secondDriverId'],'loads.dispatcher_id' => $filters["dispId"]));
		}

		if($type == "team" || $type == "_team" || $type == "_iteam"){
			$this->db->where('loads.driver_type = ',"team");
		}
		$this->db->where(array('loads.delete_status' => 0));

		$this->db->where( "(( DATE(loads.PickupDate)  = '".$date."' AND loads.JobStatus = 'booked')  OR (DATE(loads.DeliveryDate) = '".$date."' and loads.JobStatus IN ('completed','inprogress', 'booked', 'delayed', 'delivered', 'invoiced') )
			OR (
			  	loads.pickupdate <= '".$date."' AND loads.DeliveryDate >= '".$date."' AND loads.JobStatus IN('completed','inprogress','booked','delayed','delivered','invoiced')
			  )

			)");	
		
		


		if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){

            $this->db->group_start();
            $this->db->like('LOWER(CONCAT(TRIM(d.first_name)," ",TRIM(d.last_name)))', strtolower($filters['searchQuery']));
            $this->db->or_like('loads.id', $filters['searchQuery'] );
            $this->db->or_like('loads.invoiceNo', $filters['searchQuery'] );
            $this->db->or_like('loads.PointOfContactPhone', $filters['searchQuery'] );
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
            $this->db->or_like('LOWER(b.TruckCompanyName)', strtolower($filters['searchQuery']) );
            //$this->db->or_like('LOWER(CONCAT( d.first_name ," + " ,team.first_name))', strtolower($filters['searchQuery']));
            $this->db->group_end();
		}
		$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
	
		if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "driverName"){
			$this->db->order_by('CASE 
								     WHEN loads.driver_type  = "team" THEN CONCAT(TRIM(d.first_name), " + ", TRIM(team.first_name)) 
								     ELSE concat(d.first_name, " ", d.last_name) 
								 END '.$filters["sortType"]);
		}else if(in_array($filters["sortColumn"] ,array("TruckCompanyName"))){
			$this->db->order_by("b.".$filters["sortColumn"],$filters["sortType"]);	
		}else if($filters["sortColumn"] == "rpm"){
			$this->db->order_by("(loads.PaymentAmount/loads.Mileage) ",$filters["sortType"]);	 
		}else if($filters["sortColumn"] == "Weight"){
			$this->db->order_by("CAST(loads.".$filters["sortColumn"]."  AS DECIMAL)",$filters["sortType"]);	
		}else{
			$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);	
		}
		if(!$total){
			$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
		}

		$query = $this->db->get('loads');
		if($total){
			return $query->row_array()["total"];
		}
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}




	public function fetchSavedJobsNew( $loggedUserId = null, $vehicleId = null, $scopeType = '', $dispatcherId = null, $driverId = null, $secondDriverId = null, $startDate = '', $endDate = '',$filters = array()) {

		if(count($filters) <= 0){
			$filters = array("itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC","status"=>"");
		}

		$data =  $condition = array();
        $this->db->select('loads.id,CONCAT( d.first_name ," + " ,team.first_name) AS teamdriverName, 
        					CASE loads.driver_type 
        						WHEN "team" THEN CONCAT(d.first_name," + ",team.first_name) 
        									ELSE concat(d.first_name," ",d.last_name) 
        									END AS driverName, 
        						CASE loads.billType 
        							WHEN "shipper" THEN (shippers.shipperCompanyName) 
        									   ELSE (broker_info.TruckCompanyName)
        									END AS companyName, 
        					loads.driver_type,loads.invoiceNo, loads.vehicle_id, loads.truckstopID,loads.Bond,loads.PointOfContactPhone,loads.equipment_options,loads.LoadType,loads.PickupDate,loads.DeliveryDate,loads.OriginCity,loads.OriginState,loads.DestinationCity,loads.DestinationState,loads.PickupAddress,loads.DestinationAddress,loads.PaymentAmount,loads.Mileage, (loads.PaymentAmount/loads.Mileage) as rpm, loads.deadmiles,loads.Weight,loads.Length,loads.JobStatus,loads.totalCost,loads.pickDate,loads.load_source,loads.created,loads.overallTotalProfit,loads.overallTotalProfitPercent,loads.DeliveryDate,loads.totalCost,loads.billType,loads.invoiceNo');
		
		$this->db->join("vehicles as v", "loads.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "loads.driver_id = d.id","Left");
		$this->db->join("broker_info", "broker_info.id = loads.broker_id AND loads.billType = 'broker'","Left");
		$this->db->join("shippers", "shippers.id = loads.broker_id AND loads.billType = 'shipper'","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
	
		if($scopeType == "team") {
			$this->db->where(array('loads.driver_type' => "team",'loads.driver_id' => $driverId, 'loads.second_driver_id' => $secondDriverId, 'loads.dispatcher_id' => $dispatcherId));	
		} else if ($scopeType == "driver"){
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
		
		$this->db->where('loads.delete_status',0);
		if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){

            $this->db->group_start();
            $this->db->like('LOWER(CONCAT(TRIM(d.first_name)," ",TRIM(d.last_name)))', strtolower($filters['searchQuery']));
            $this->db->or_like('loads.id', $filters['searchQuery'] );
            $this->db->or_like('loads.invoiceNo', $filters['searchQuery'] );
            $this->db->or_like('loads.PointOfContactPhone', $filters['searchQuery'] );
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
            $this->db->or_like('LOWER(loads.billType)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(shippers.shipperCompanyName)', strtolower($filters['searchQuery']) );
            //$this->db->or_like('LOWER(CONCAT( d.first_name ," + " ,team.first_name))', strtolower($filters['searchQuery']));
            $this->db->group_end();
		}
		
	
		if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "driverName"){
			$this->db->order_by('CASE 
								     WHEN loads.driver_type  = "team" THEN CONCAT(TRIM(d.first_name), " + ", TRIM(team.first_name)) 
								     ELSE concat(d.first_name, " ", d.last_name) 
								 END '.$filters["sortType"]);
		}else if(in_array($filters["sortColumn"] ,array("TruckCompanyName"))){
			$this->db->order_by('CASE 
								     WHEN loads.billType  = "shipper" THEN (shippers.shipperCompanyName) 
								     ELSE (broker_info.TruckCompanyName)
								 END '.$filters["sortType"]);
			//$this->db->order_by("broker_info.".$filters["sortColumn"],$filters["sortType"]);	
		}else if($filters["sortColumn"] == "rpm"){
			$this->db->order_by("(loads.PaymentAmount/loads.Mileage) ",$filters["sortType"]);	 
		}else if($filters["sortColumn"] == "Weight"){
			$this->db->order_by("CAST(loads.".$filters["sortColumn"]."  AS DECIMAL)",$filters["sortType"]);	
		}else{
			$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);	
		}

		//Bypass limit when you want to export saved load as csv
		if(empty($filters["export"])){
			$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
			$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
		}

		$query = $this->db->get('loads');

		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	public function fetchSavedJobsTotal( $loggedUserId = null, $vehicleId = null, $scopeType = '', $dispatcherId = null, $driverId = null, $secondDriverId = null, $startDate = '', $endDate = '',$filters = array() ) {
		if(count($filters) <= 0){
			$filters = array("status"=>"");
		}		
		$data =  $condition = array();
        $this->db->select('count(loads.id) as total');
		$this->db->join("vehicles as v", "loads.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "loads.driver_id = d.id","Left");
		$this->db->join("broker_info", "broker_info.id = loads.broker_id AND loads.billType = 'broker'","Left");
		$this->db->join("shippers", "shippers.id = loads.broker_id AND loads.billType = 'shipper'","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		
		if($scopeType == "team") {
			$this->db->where(array('loads.driver_type' => "team",'loads.driver_id' => $driverId, 'loads.second_driver_id' => $secondDriverId, 'loads.dispatcher_id' => $dispatcherId));	
		} else if ($scopeType == "driver"){
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

		$this->db->where('loads.delete_status',0);
		if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){

            $this->db->group_start();
            $this->db->like('LOWER(CONCAT( TRIM(d.first_name) ," + " ,TRIM(team.first_name)))', strtolower($filters['searchQuery']));
            $this->db->or_like('LOWER(CONCAT(TRIM(d.first_name)," ", TRIM(d.last_name)))', 		strtolower($filters['searchQuery']));
            $this->db->or_like('loads.id', $filters['searchQuery'] );
            $this->db->or_like('loads.id', $filters['searchQuery'] );
            $this->db->or_like('loads.invoiceNo', $filters['searchQuery'] );
            $this->db->or_like('loads.PointOfContactPhone', $filters['searchQuery'] );
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
            $this->db->or_like('LOWER(loads.billType)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(shippers.shipperCompanyName)', strtolower($filters['searchQuery']) );
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
		//$this->db->join('documents',"documents.load_id = loads.id and documents.doc_type NOT IN ('pod','rateSheet') ",'LEFT');
		
 		
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
		$this->db->where("(SELECT  count(documents.load_id) as c FROM  documents WHERE  documents.load_id  =  loads.id and documents.doc_type in ('pod','rateSheet') )< 2  ");
		$this->db->where("loads.DeliveryDate <",date("Y-m-d"));
		$this->db->where_IN('loads.JobStatus',$this->config->item('loadStatus'));
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

	public function FetchSingleJob( $jobId, $type = '' ) {
		if ( $type == 'shipper') {
			$this->db->select('loads.*,concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label) as assignedDriverName, users.username, concat("Truck - ",vehicles.label) as assignedTruckLabel,concat(drivers.first_name," ",drivers.last_name) as assigedDriverFullName, shippers.shipperCompanyName as TruckCompanyName, shippers.postingAddress,shippers.city,shippers.state,shippers.zipcode,drivers.color');
			$this->db->join('shippers', 'shippers.id = loads.broker_id','Left');
		} else {
			$this->db->select('loads.*,concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label) as assignedDriverName, users.username, concat("Truck - ",vehicles.label) as assignedTruckLabel,concat(drivers.first_name," ",drivers.last_name) as assigedDriverFullName, broker_info.MCNumber,broker_info.DOTNumber,broker_info.TruckCompanyName,broker_info.postingAddress,broker_info.CarrierMC,broker_info.city,broker_info.state,broker_info.zipcode,drivers.color');
			$this->db->join('broker_info', 'broker_info.id = loads.broker_id','Left');
		}		
		$this->db->join('drivers', 'drivers.id = loads.driver_id','Left');
		$this->db->join('vehicles', 'vehicles.id = loads.vehicle_id','Left');
		$this->db->join('users', 'users.id = loads.dispatcher_id','Left');
		$this->db->where('loads.id', $jobId);
		return $this->db->get('loads')->row_array();
		
	}

	/**
	* Fetching the billType of load
	*/

	public function fetchLoadBillType($jobId = null ) {
		return $this->db->where('loads.id',$jobId)->get('loads')->row()->billType;
	}
	
	/**
	 * Fetching single load details for generating invoice
	 */
	  
	public function FetchSingleJobForInvoice( $jobId = null, $type = '' ) {
		if ( $type == 'shipper') {
			$this->db->select('loads.invoiceNo, loads.invoicedDate,loads.id, loads.PickupDate,loads.DeliveryDate,loads.woRefno, loads.shipper_name, loads.LoadType, loads.PickupAddress, loads.OriginStreet, loads.OriginCity,loads.OriginState, loads.OriginCountry,loads.OriginZip,loads.shipper_phone, loads.Quantity,loads.PickupDate, loads.Weight,loads.consignee_name, loads.DestinationAddress,loads.DestinationStreet, loads.DestinationCity, loads.DestinationState, loads.DestinationCountry, loads.DestinationZip, loads.consignee_phone, loads.DeliveryDate, loads.PaymentAmount, loads.Stops, shippers.shipperCompanyName as TruckCompanyName, shippers.postingAddress,shippers.city,shippers.state,shippers.zipcode');
			$this->db->join('shippers', 'shippers.id = loads.broker_id','Left');
		} else {
			$this->db->select('loads.invoiceNo,loads.invoicedDate,loads.id,loads.PickupDate,loads.DeliveryDate,loads.woRefno,loads.shipper_name,loads.LoadType,loads.PickupAddress, loads.OriginStreet,loads.OriginCity,loads.OriginState,loads.OriginCountry,loads.OriginZip,loads.shipper_phone,loads.Quantity,loads.PickupDate,loads.Weight,loads.consignee_name, loads.DestinationAddress,loads.DestinationStreet, loads.DestinationCity, loads.DestinationState, loads.DestinationCountry, loads.DestinationZip, loads.consignee_phone, loads.DeliveryDate,loads.PaymentAmount,loads.Stops,broker_info.TruckCompanyName,broker_info.postingAddress,broker_info.city,broker_info.state,broker_info.zipcode');
			$this->db->join('broker_info', 'broker_info.id = loads.broker_id','Left');
		}
		$this->db->where('loads.id', $jobId);
		
		return $this->db->get('loads')->row_array();
	}
	
	/**
	 * Getting broker table mc number for add request show
	 */
	
	public function getBrokerForLoadDetail( $loadId = null, $type = '') {
		if ( $type == 'shipper') {
			$this->db->select('shippers.shipperCompanyName as TruckCompanyName');
			$this->db->join('shippers', 'shippers.id = loads.broker_id','INNER');
		} else {
			$this->db->select('broker_info.MCNumber as mc_number,broker_info.DOTNumber as dot_number, broker_info.TruckCompanyName, broker_info.postingAddress, broker_info.CarrierMC');
			$this->db->join('broker_info', 'broker_info.id = loads.broker_id','LEFT');
		}
		
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
		$this->db->select('CASE loads.billType 
				WHEN "shipper" THEN (shippers.shipperCompanyName) 
			   ELSE (broker_info.TruckCompanyName)
			END AS TruckCompanyName, loads.invoiceNo,loads.invoicedDate,loads.woRefno,loads.PaymentAmount,loads.OriginCity,loads.OriginState,loads.OriginZip,loads.PickupDate,loads.PickDate,loads.DestinationCity,loads.DestinationState,loads.DestinationZip,loads.DeliveryDate,broker_info.MCNumber');
		$this->db->join("broker_info", "broker_info.id = loads.broker_id AND loads.billType = 'broker'" ,"LEFT");
		$this->db->join("shippers",	   "shippers.id = loads.broker_id AND loads.billType = 'shipper'"   ,"LEFT");
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
		$this->db->select('id,load_id, truckstopID, vehicle_average,diesel_needed,avg_cost_diesel,origin_to_dest,deadmiles_dist, dead_miles_not_paid, dead_head_miles_paid, pay_for_dead_head_mile, pay_for_miles_cargo, ifta_taxes, tarps, detention_time, tolls');
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
	
	// public function FindVehicles( $jobSpec = '', $jobCollect = '', $jobDeliver = '', $jobVehicle = '' ,$fetchAssignedTruck = null , $jobVehicleType = '' ,$jobId = null ,$loggedUserId = null, $jobWidth = 0, $jobLength = 0, $vehicleId = null) {
	public function FindVehicles( $vehicleId = null ) {
		$this->db->select('vehicles.id,vehicles.fuel_consumption,vehicles.destination_address');
		$this->db->join('drivers', 'drivers.id = vehicles.driver_id','LEFT');
			
		$i = 1;
		$string = '';
	
		if ( $vehicleId != '' && $vehicleId != null ) {
			$string .= "`vehicles.id` = ".$vehicleId;	
		}
		
		$this->db->where($string);

		$result = $this->db->get('vehicles');
		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	
	public function FindJobVehicles( $vehicleId = null, $userId = null, $thirdParameter = '' ) {	// third parameter to savejob frm disp having no truck assigned	
		$this->db->select('vehicles.id,fuel_consumption,destination_address');
		$this->db->join('drivers', 'drivers.id = vehicles.driver_id','LEFT');
		$condition = array('vehicles.id' => $vehicleId);
		$this->db->where($condition);

		$result = $this->db->get('vehicles');
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
	
		/** saving broker info to broker table */
		$brokerData = array(
			'postingAddress' => $saveData['postingAddress'],
			'city' => isset($saveData['city']) ? $saveData['city'] : '',
			'state' => isset($saveData['state']) ? $saveData['state'] : '',
			'zipcode' => isset($saveData['zipcode']) ? $saveData['zipcode'] : '',
		);
		if ( isset($saveData['MCNumber']) && $saveData['MCNumber'] != '' && $saveData['MCNumber'] != null ) {
			$brokerData['TruckCompanyName'] = $saveData['TruckCompanyName'];
			$brokerData['MCNumber'] 		= $saveData['MCNumber'];
			$brokerData['CarrierMC'] 		= isset($saveData['CarrierMC']) ? $saveData['CarrierMC'] : '';
			$brokerData['DOTNumber'] 		= $saveData['DOTNumber'];
			$brokerData['brokerStatus']		= isset($saveData['brokerStatus']) ? $saveData['brokerStatus'] : '';
			$brokerData['DebtorKey'] 		= isset($saveData['DebtorKey']) ? $saveData['DebtorKey'] : '';
						
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

			$saveData['billType'] = 'broker';
		} else if ( isset($saveData['billType']) && $saveData['billType'] == 'shipper' && !empty($saveData['broker_id']) ) {
			// $brokerData['shipperCompanyName'] = $saveData['shipperCompanyName'];
			$this->db->where('shippers.id',$saveData['broker_id']);
			$this->db->update('shippers',$brokerData);
			$brokerLastId = $saveData['broker_id'];
			$saveData['billType'] = 'shipper';
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
		unset($saveData['shipperCompanyName']);
		unset($saveData['companyName']);
		unset($saveData['TruckCompanyCity']);
		unset($saveData['TruckCompanyState']);
		unset($saveData['MCNumber']);
		unset($saveData['CarrierMC']);
		unset($saveData['DOTNumber']);	
		unset($saveData['brokerStatus']);	
		unset($saveData['DebtorKey']);	
		unset($saveData['city']);	
		unset($saveData['state']);	
		unset($saveData['zipcode']);	
		unset($saveData['driverName']);	
		unset($saveData['username']);
		unset($saveData['assignedTruckLabel']);
		unset($saveData['assigedDriverFullName']);
		unset($saveData['color']);
		unset($saveData['Entered']);
				
		$saveData['broker_id'] = $brokerLastId;

		if ($id != '' ) {
			$saveData['updated_record'] = 1;
			$this->db->where('loads.id',$id);
			$res = $this->db->update('loads',$saveData);
			$last_id = $id;
		} else {
			if ( !isset($saveData['load_source']) || $saveData['load_source'] == '' )
				$saveData['load_source'] = 'truckstop.com';

			$saveData['user_id'] = $loggedUserId;
			$saveData['created'] = date('Y-m-d H:i:s');
			$res = $this->db->insert('loads',$saveData);
			$last_id = $this->db->insert_id();
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
		unset($saveData['shipperCompanyName']);
		unset($saveData['companyName']);
		unset($saveData['MCNumber']);
		unset($saveData['CarrierMC']);
		unset($saveData['DOTNumber']);	
		unset($saveData['brokerStatus']);	
		unset($saveData['DebtorKey']);
		unset($saveData['city']);
		unset($saveData['state']);
		unset($saveData['zipcode']);
		unset($saveData['driverName']);	
		
		unset($saveData['username']);
		unset($saveData['assignedTruckLabel']);
		unset($saveData['assigedDriverFullName']);
		unset($saveData['PostedOn']);
		unset($saveData['color']);
		
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
				$saveData['user_id'] 	= $loggedUserId;
				$saveData['postedDate'] = date('Y-m-d H:i:s'); 
				$saveData['created'] 	= date('Y-m-d H:i:s');
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
		
		/*$savedata['vehicle_average_actual'] = @$data['vehicle_average_actual'];
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
		$savedata['tolls_actual'] = str_replace('$','',@$data['tolls_actual']);*/
		
		$updateFlag = false;
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
	

	public function findTripDetailsId($truckstopID,$loadId){
		$this->db->select('id');
			if ( $truckstopID != '' && $truckstopID != null && $truckstopID != 'undefined' )
				$condition = array('truckstopID' => $truckstopID,'load_id' => $loadId);
			else
				$condition = array('load_id' => $loadId);
				
			$this->db->where($condition);
			$result = $this->db->get('trip_details');
			if( $result->num_rows() > 0 ) {
				$finalResult = $result->row_array();
				$primary_key = $finalResult['id'];
				return $primary_key;
			}else{
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
	
	public function getRelatedEquipment( $equipmentOption = '', $parameter = '' ) {
		
		if ( $parameter == 'changeType') {
			$equipment = str_replace('or', '', $equipmentOption);
			$this->db->where('name',$equipment);
		}
		else {
			$this->db->where('abbrevation',$equipmentOption);
		}

		$result = $this->db->get('equipment_types');
		if ($result->num_rows() > 0) {
			if ($parameter == 'changeType') 
				return $result->row()->abbrevation;
			else
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
					             SELECT loc.store_id,loc.name,
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
		
		// echo $this->db->last_query();

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

	public function logActivityEvent($entityId, $entityType, $eventType, $message, $srcPage){
		$logs = array();
		$logs['user_id']  	 = $this->session->loggedUser_id;
		$logs['entity_id'] 	 = $entityId;
		$logs['entity_name'] = $entityType;
		$logs['event_type']	 = $eventType;
		$logs['event_msg'] 	 = $message;
		$logs['src_page'] 	 = $srcPage;
		$result = $this->db->insert('activity_log',$logs);
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


	public function isInvoiceGenerated($loadId){
		$this->db->select("*, CASE
								WHEN doc_type LIKE 'invoice'
								THEN 'yes'
								ELSE 'no'
								END AS haveInvoice");
		$this->db->where(array('load_id' => $loadId, "doc_type"=>"invoice"));
		
		$result = $this->db->get('documents');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array()["haveInvoice"];
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
		$condition = array('woRefno' => $woRefno, 'delete_status' => 0);
		if ( $primaryId  != null && $primaryId != '' ) {
			$condition['id !='] = $primaryId;
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
	* Method widgetsVisibility
	* @param POST DATA
	* @return Boolean
	* 
	*/

	public function widgetsVisibility($data){
		
		$dataRow = $this->db->select('id')
				 	->where(['user_id'=>$this->session->loggedUser_id,'widget_id'=>$data['widgetID']])
				 	->get('widgets_visibility');		
		$result 	= $dataRow->result_array();
		$columns 	= ['visibility'=>$data['visibility'],'user_id'=>$this->session->loggedUser_id,'widget_id'=>$data['widgetID']];

		if ( !empty($result)) {			
			$this->db->where('id',$result[0]['id'])
				 ->update('widgets_visibility',['visibility'=>$data['visibility']]);
		} else {
			$this->db->insert('widgets_visibility',$columns);
		}
	}
	public function getPortletVisibility(){
		
		$dataRow = $this->db->select('widget_id,visibility')
				 	->where(['user_id'=>$this->session->loggedUser_id])
				 	->get('widgets_visibility');
	 	$result 	= $dataRow->result_array();

	 	$widgetVisibility = [1=>1,2=>1,3=>1,4=>1,5=>1,6=>1,7=>1,8=>1,9=>1,10=>1,11=>1];
	 	foreach ($result as $key => $value) {
	 		$widgetVisibility[$value['widget_id']] = $value['visibility']; 
	 	}
	 	return $widgetVisibility;
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

	/*
	* method  : post
	* params  : loadId
	* return  : load detail array
	* comment : for fetching load detail information for printing ticket
	*/

	public function FetchSingleJobForPrint( $jobId = null ) {
		$this->db->select('loads.id,loads.vehicle_id, loads.driver_id, loads.second_driver_id, loads.driver_type,loads.shipper_entity, loads.shipper_name, loads.shipper_phone, loads.PickupDate, loads.PickupTime, loads.PickupTimeRangeEnd,loads.PickupAddress, loads.OriginCity, loads.OriginState, loads.OriginCountry, loads.OriginZip, loads.consignee_entity, loads.consignee_phone, loads.consignee_name, loads.DeliveryDate, loads.DeliveryTime, loads.DeliveryTimeRangeEnd, loads.DestinationAddress, loads.DestinationCity, loads.DestinationState, loads.DestinationCountry, loads.DestinationZip, loads.equipment, loads.equipment_options, loads.LoadType, loads.Weight, loads.Length, loads.Mileage, loads.PaymentAmount, loads.Quantity, loads.Stops, loads.commodity, loads.Rate, loads.specInfo, loads.deadmiles, loads.JobStatus, loads.invoiceNo, loads.postedDate, loads.totalCost, loads.overallTotalProfit, loads.overallTotalProfitPercent, loads.woRefno, loads.broker_id, loads.	PointOfContact, loads.PointOfContactPhone, loads.TruckCompanyEmail, loads.TruckCompanyPhone, loads.TruckCompanyFax, loads.billType, concat(drivers.first_name," ",drivers.last_name,"-",vehicles.label) as assignedDriverName');
		$this->db->join('drivers', 'drivers.id = loads.driver_id','Left');
		$this->db->join('vehicles', 'vehicles.id = loads.vehicle_id','Left');
		$this->db->where('loads.id', $jobId);
		
		return $this->db->get('loads')->row_array();
	}
//http://localhost/trackingnew/dashboard/getPortletVisibility
	/*
	* method  : post
	* params  : loadId
	* return  : load detail array
	* comment : for fetching load detail information for printing ticket
	*/

	public function getBrokerForLoadDetailForPrint( $entityId = null, $billType = '' ) {
		if ( $billType == 'shipper' ){
			$this->db->select('shipperCompanyName as TruckCompanyName, postingAddress, city, state, zipcode');
			$this->db->where('shippers.id', $entityId);
			return $this->db->get('shippers')->row_array();
		} else {
			$this->db->select('TruckCompanyName, postingAddress, city, state, zipcode, MCNumber, CarrierMC, DOTNumber, brokerStatus');
			$this->db->where('broker_info.id', $entityId);
			return $this->db->get('broker_info')->row_array();
		}
		
	}

	/**
	* method  : post
	* @param  : loadId
	* @return : load detail array
	* comment : for fetching load whose delivery date is past and load is not completed
	*/

	public function fetchPastLoadsIncomplete($args = array(), $total = false) {
		$todayDate = date('Y-m-d');
		
		if($total){
			$this->db->select('loads.id');
		} else {
			$this->db->select('loads.id,CONCAT( d.first_name ," + " ,team.first_name) AS teamdriverName, 
	        					CASE loads.driver_type 
	        						WHEN "team" THEN CONCAT(d.first_name," + ",team.first_name) 
	        									ELSE concat(d.first_name," ",d.last_name) 
	        									END AS driverName, 
	        						CASE loads.billType 
	        							WHEN "shipper" THEN (shippers.shipperCompanyName) 
	        									   ELSE (broker_info.TruckCompanyName)
	        									END AS companyName, 
	        					loads.driver_type,loads.invoiceNo, loads.vehicle_id, loads.truckstopID,loads.Bond,loads.PointOfContactPhone,loads.equipment_options,loads.LoadType,loads.PickupDate,loads.DeliveryDate,loads.OriginCity,loads.OriginState,loads.DestinationCity,loads.DestinationState,loads.PickupAddress,loads.DestinationAddress,loads.PaymentAmount,loads.Mileage, (loads.PaymentAmount/loads.Mileage) as rpm, loads.deadmiles,loads.Weight,loads.Length,loads.JobStatus,loads.totalCost,loads.pickDate,loads.load_source,loads.created,loads.overallTotalProfit,loads.overallTotalProfitPercent,loads.DeliveryDate,loads.totalCost,loads.billType,loads.invoiceNo');
		}

		$this->db->join("vehicles as v", "loads.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "loads.driver_id = d.id","Left");
		$this->db->join("broker_info", "broker_info.id = loads.broker_id AND loads.billType = 'broker'","Left");
		$this->db->join("shippers", "shippers.id = loads.broker_id AND loads.billType = 'shipper'","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		
		if(isset($args["searchQuery"]) && !empty($args["searchQuery"])){
            $this->db->group_start();
            $this->db->like('LOWER(CONCAT(TRIM(d.first_name)," ",TRIM(d.last_name)))', strtolower($args['searchQuery']));
            $this->db->or_like('loads.id', $args['searchQuery'] );
            $this->db->or_like('loads.invoiceNo', $args['searchQuery'] );
            $this->db->or_like('loads.PointOfContactPhone', $args['searchQuery'] );
            $this->db->or_like('LOWER(loads.equipment_options)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.LoadType)', strtolower($args['searchQuery']) );
            $this->db->or_like('loads.PickupDate', $args['searchQuery'] );
            $this->db->or_like('loads.DeliveryDate', $args['searchQuery'] );
            $this->db->or_like('LOWER(loads.OriginCity)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.OriginState)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationCity)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationState)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.PickupAddress)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationAddress)', strtolower($args['searchQuery']) );
            $this->db->or_like('loads.PaymentAmount', $args['searchQuery']);
            $this->db->or_like('loads.Mileage', $args['searchQuery']);
            $this->db->or_like('loads.deadmiles', $args['searchQuery']);
            $this->db->or_like('loads.Weight', $args['searchQuery']);
            $this->db->or_like('loads.Length', $args['searchQuery']);
            $this->db->or_like('LOWER(loads.JobStatus)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.load_source)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(loads.billType)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(shippers.shipperCompanyName)', strtolower($args['searchQuery']) );
            //$this->db->or_like('LOWER(CONCAT( d.first_name ," + " ,team.first_name))', strtolower($filters['searchQuery']));
            $this->db->group_end();
		}
		
	
		if(isset($args["sortColumn"]) && $args["sortColumn"] == "driverName"){
			$this->db->order_by('CASE 
								     WHEN loads.driver_type  = "team" THEN CONCAT(TRIM(d.first_name), " + ", TRIM(team.first_name)) 
								     ELSE concat(d.first_name, " ", d.last_name) 
								 END '.$args["sortType"]);
		}else if(in_array($args["sortColumn"] ,array("TruckCompanyName"))){
			$this->db->order_by('CASE 
								     WHEN loads.billType  = "shipper" THEN (shippers.shipperCompanyName) 
								     ELSE (broker_info.TruckCompanyName)
								 END '.$args["sortType"]);
		}else if($args["sortColumn"] == "rpm"){
			$this->db->order_by("(loads.PaymentAmount/loads.Mileage) ",$args["sortType"]);	 
		}else if($args["sortColumn"] == "Weight"){
			$this->db->order_by("CAST(loads.".$args["sortColumn"]."  AS DECIMAL)",$args["sortType"]);	
		}else{
			$this->db->order_by("loads.".$args["sortColumn"],$args["sortType"]);	
		}

		$this->db->join('documents','documents.load_id = loads.id','LEFT');
		$this->db->where('loads.DeliveryDate <',$todayDate);

		if( isset($args['driverId']) && !empty($args['driverId']) ) {
			$this->db->where('loads.driver_id', $args['driverId']);
		} 

		if ( isset($args['secondDriverId']) && !empty($args['secondDriverId']) && $args['secondDriverId'] > 0 ) {
			$this->db->where(array('loads.second_driver_id' => $args['secondDriverId'], 'loads.driver_type' => 'team'));
		}

		if( isset($args['dispatcherId']) && !empty($args['dispatcherId']) ) {
			$this->db->where('loads.dispatcher_id', $args['dispatcherId']);
		}


		$this->db->where('broker_id is NOT NULL', NULL, FALSE);
		$this->db->where(array('loads.vehicle_id != ' => null, 'loads.vehicle_id != ' => 0, 'loads.delete_status' => 0 ));			
		$this->db->where_IN('documents.doc_type',array('pod','rateSheet'));
		$this->db->group_by('documents.load_id');
		$this->db->having('count(*) < 2', null, false );

		if(!$total){
			$args["limitStart"] = $args["limitStart"] == 1 ? 0 : $args["limitStart"];
			$this->db->limit($args["itemsPerPage"],$args["limitStart"]);
		}
		$result = $this->db->get('loads');
		//echo $this->db->last_query();
		
		if( $result->num_rows() > 0 )
			return $result->result_array();
		else
			return array();

	}

	/*
	* method  : post
	* params  : loadId
	* return  : load detail array
	* comment : for fetching load unread Notifications count
	*/

	public function getUnreadNotificationsCount($userId) {
		if($userId){
			$this->db->select('count(activity_log.id) as unreadCount');
			$this->db->where("activity_log.user_id != ", $userId);
			$this->db->where("not find_in_set(".$userId.", activity_log.read_users)");
			$this->db->where("activity_log.event_type != 'login' AND activity_log.event_type != 'logout'");
			return $this->db->get('activity_log')->row_array()["unreadCount"];
		}else{
			return 0;
		}
	}

	public function flagNotificationsAsRead($userId) {
		$this->db->query("UPDATE `activity_log` SET `read_users` = TRIM(BOTH ',' FROM concat(read_users,',',".$userId.")) WHERE `activity_log`.`user_id` != ".$userId." AND not find_in_set(".$userId.", activity_log.read_users) AND activity_log.event_type != 'login' AND activity_log.event_type != 'logout'");
	}

	public function getNotifications($userId,$filters=array()) {
		if(count($filters) <= 0){
			$filters = array("itemsPerPage"=>15, "limitStart"=>1);
		}

		$this->db->select('CONCAT(SUBSTRING(u.first_name, 1, 1),SUBSTRING(u.last_name, 1, 1)) as userIntial, u.profile_image,  log.event_msg, u.color, log.created_at AS created_at');
		$this->db->Join("users as u","log.user_id = u.id","inner");
		$this->db->where("log.user_id != ", $userId);
		$this->db->where("log.event_type != 'login' AND log.event_type != 'logout'");
		$this->db->order_by("log.created_at",'DESC');
		$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
		$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
		return $this->db->get('activity_log as log')->result_array();

	}


	public function getTicketActivity( $loadId ) {
		$this->db->select("CONCAT(SUBSTRING(u.first_name, 1, 1),SUBSTRING(u.last_name, 1, 1)) as userIntial, log.entity_name, u.profile_image, log.event_type, CONCAT(u.first_name, ' ', u.last_name) as userName, log.event_msg, log.created_at AS created_at, u.color,
			CASE
				WHEN log.event_type = 'edit'              THEN   'fa-pencil'
				WHEN log.event_type = 'add'               THEN   'fa-plus'
				WHEN log.event_type = 'delete'            THEN   'fa-trash'
				WHEN log.event_type = 'upload_doc'        THEN   'fa-upload'
				WHEN log.event_type = 'remove_doc'        THEN   'fa-trash'
				WHEN log.event_type = 'overwrite_doc'     THEN   'fa-trash'
				WHEN log.event_type = 'status_change'     THEN   'fa-exchange'
				WHEN log.event_type = 'generate_invoice'  THEN   'fa-file-text-o'
				WHEN log.event_type = 'bundle_document'   THEN   'fa-file-text-o'
			END as action_type,u.first_name, u.last_name,u.first_name,u.color"
			);
		$this->db->Join("users as u","log.user_id = u.id","inner");
		$this->db->where("log.entity_name",'ticket');
		$this->db->where("log.entity_id",$loadId);
		$this->db->order_by("log.created_at",'DESC');
		return $this->db->get('activity_log as log')->result_array();
		
	}

	public function getNotificationsTotal($userId,$filters=array()) {

		$this->db->select('count(log.id) as total');
		$this->db->Join("users as u","log.user_id = u.id","inner");
		$this->db->where("log.user_id != ", $userId);
		$this->db->where("log.event_type != 'login' AND log.event_type != 'logout'");
		$this->db->order_by("created_at",'DESC');
		return $this->db->get('activity_log as log')->row_array()["total"];
	}

	public function getTripDetailsById($id = null){
		return $this->db->select('*')->from('trip_details')->where('id',$id)->get()->row_array();
	}

	public function getLoadDetailsById($load_id = null){
		return $this->db->select('*')->from('loads')->where('id',$load_id)->get()->row_array();
	}

	public function getColumns($id,$columns,$model){

			$data = $this->db->select(implode(',',$columns))->where('id', $id)->get($model)->result_array();
			// echo $this->db->last_query();
			return $data;
		}


	}
?>
