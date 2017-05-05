<?php

/**
* 
*/
class Driver extends Parent_Model
{
	public $limit_per_page;
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('email');
		$this->limit_per_page = $this->config->item('limit_per_page');  
		
	}

	/**
	* fetch all drivers records for listing
	*/

    public function fetchAllRecords($userId = null) {
        $data = array();
       	$this->db->select('CONCAT(SUBSTRING(d.first_name, 1, 1),SUBSTRING(d.last_name, 1, 1)) as userIntial, v.city, d.color,  d.id as driverId, d.first_name,d.last_name,d.phone,d.email,d.driver_license_number,d.id,d.status, CONCAT(u.first_name, " ", u.last_name) AS dispatcher, d.user_id as dispId, v.team_driver_id,  v.label,v.id as vehicle_id');
		
		$this->db->join("vehicles as v", "d.id = v.driver_id","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		$this->db->join('users as u','d.user_id = u.id','left');

		if ( is_array($userId) && !empty($userId) ) {
			$this->db->where_in('d.user_id',$userId); 
		} else if( $userId != null)
			$this->db->where('d.user_id',$userId); 
		
		$this->db->where('d.organisation_id',$this->selectedOrgId);
        $query = $this->db->get("drivers as d");
        //echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
    }

    public function getInfoByDriverId($driverId){
    	$this->db->select("v.id as vehicleId, d.id as driverId, v.team_driver_id,d.user_id as dispatcherId");
    	$this->db->join('vehicles as v','d.id = v.driver_id','LEFT');
		$this->db->join('users as u','d.user_id = u.id','LEFT');
		$this->db->from('drivers as d');       
		$this->db->where('d.id',$driverId);
        $query = $this->db->get();
        //echo $this->db->last_query();
		if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
    } 
    
    /**
    * adding driver to database
    */

	public function addDrivers( $data = array()){
		$data['organisation_id'] = $this->selectedOrgId;
		$result = $this->db->insert('drivers',$data);
		if ($result) {
			return $this->db->insert_id();
		} else {
			return false;
		}
	}
	
	public function get_driver_data($driver_id = Null,  $fields = array() ) {
		if(count($fields) > 0){
			$this->db->select(implode(",", $fields));
		}else{
			$this->db->select('drivers.*, users.first_name as uFirstName, users.last_name as uLastName');	
		}
		$this->db->join("users","drivers.user_id = users.id","Left");
		$this->db->where('drivers.id', $driver_id);
		$result = $this->db->get('drivers');
		if( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	}

	

	public function getDocs($driverId){
		$this->db->select('documents');
		$this->db->where('id', $driverId);
		$result = $this->db->get('drivers');
		if( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}	
	}
	
	public function update( $data = array(),$id=null ){
		$result = $this->db->update('drivers',$data,"id={$id}");
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get_driver_image( $driver_id = null ) {
		$this->db->where('id', $driver_id);
		return $this->db->get('drivers')->row()->profile_image;
	}
	
	public function delete( $driver_id = null ) {
		$driver_image = $this->get_driver_image($driver_id);
		if ($driver_image != '') {
			unlink('assets/uploads/drivers/'.$driver_image);
			unlink('assets/uploads/drivers/thumbnail/'.$driver_image);					
		}
		
		$this->db->where('id', $driver_id);
        $result = $this->db->delete('drivers');
        if ($result) {
            return true;
        } else {
            return false;
        }
		
	}
	
	public function get_departments() {
		$this->db->select('id,label');
		$result = $this->db->get('departments');
		if ($result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	
	/**
	* get list of dispatchers
	*/

	public function get_dispatcher_list( $userId = null, $childIds = false ) {
		$this->db->where_in('role_id',array(2,5));
		$this->db->select('id,username');

		if ( $childIds ) {
			$this->db->where('childs_parent',$userId);
		} else if ( $userId )
			$this->db->where('id',$userId);

		$this->db->where('users.parent_id',$this->selectedOrgId);
		$result = $this->db->get('users');
		if ($result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	}

	public function get_selected_dispatcher($driver_id = null) {
		$this->db->where('drivers.id',$driver_id);
		$this->db->select('drivers.user_id as id,users.username');
		$this->db->join('users','drivers.user_id = users.id','LEFT');
		$this->db->from('drivers'); 
		$result = $this->db->get();
		if ($result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	}
	/********* Dispatcher List - r288 ****************/
	/**
	 * Getting list of logged dispatcher drivers
	 */
	 
	public function getDriversList( $userId =  null, $skipTeam = false, $driverNameWithoutCurrentAssignment = false ) {
		$driverName = ', concat(drivers.first_name," ",drivers.last_name," - ",vehicles.label) as driverName, ';
		if($driverNameWithoutCurrentAssignment){
			$driverName = ', concat(drivers.first_name," ",drivers.last_name) as driverName, ';
		}
		/*$this->db->select('ABS( TIMESTAMPDIFF(MINUTE , Now( ) , vehicles.`modified` ) ) AS mintues_ago, telemetry, CONCAT(DATE_FORMAT( CONVERT_TZ( vehicles.`modified`, "+00:00", "-05:00" ) , "%d-%b-%Y %r" )," ","EST") AS timestamp, drivers.id, vehicles.tracker_id, drivers.profile_image, '.$driverName.'vehicles.label,users.username,users.id as dispId,vehicles.latitude, vehicles.longitude, vehicles.id as vid,vehicles.city,vehicles.vehicle_address, vehicles.state,vehicles.team_driver_id');*/

		$this->db->select('ABS( TIMESTAMPDIFF(MINUTE , Now( ) , vehicles.`modified` ) ) AS mintues_ago, telemetry, vehicles.`modified` AS timestamp, drivers.id, vehicles.tracker_id, drivers.profile_image, '.$driverName.'vehicles.label,users.username,users.id as dispId,vehicles.latitude, vehicles.longitude, vehicles.id as vid,vehicles.city,vehicles.vehicle_address, vehicles.state,vehicles.team_driver_id');
		$this->db->join('vehicles','vehicles.driver_id = drivers.id');
		$this->db->join('users','drivers.user_id = users.id');
		$this->db->where(array('drivers.status' => 1, 'vehicles.vehicle_status' => 1) )->order_by("drivers.first_name","asc");
		if($userId){
			$this->db->where('drivers.user_id',$userId);
		}

		if($skipTeam){
			//$this->db->where(array('vehicles.driver_type !=' => "team"));	
			$this->db->where("(vehicles.driver_type IS NULL OR vehicles.driver_type = '' OR vehicles.driver_type = 'driver' OR vehicles.driver_type = 'single')");	
		}
		$this->db->where('drivers.organisation_id',$this->selectedOrgId);
		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	}
	
	/**
	 * New list of driver with less fields
	 */
	  
	public function getDriversListNew( $userId =  null, $skipTeam = false ) {
		$this->db->select('drivers.id, concat(drivers.first_name," ",drivers.last_name) as driverName,vehicles.label,users.username,users.id as dispId, vehicles.id as vid,vehicles.team_driver_id');
		$this->db->join('vehicles','vehicles.driver_id = drivers.id');
		$this->db->join('users','drivers.user_id = users.id');
		$this->db->where(array('drivers.status' => 1, 'vehicles.vehicle_status' => 1) )->order_by("driverName","asc");
		if($userId){
			$this->db->where('drivers.user_id',$userId);
		}

		if($skipTeam){
			$this->db->where("(vehicles.driver_type IS NULL OR vehicles.driver_type = '' OR vehicles.driver_type = 'driver' OR vehicles.driver_type = 'single')");	
		}

		$this->db->where('drivers.organisation_id',$this->selectedOrgId);
		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	}

	public function getDriversListAsTeam( $userId =  null, $driverNameWithoutCurrentAssignment = false ) {
		$driverName = ', concat(drivers.first_name , " + ", team.first_name," - ",vehicles.label) as driverName, ';
		if($driverNameWithoutCurrentAssignment){
			//~ $driverName = ', concat(drivers.first_name," ",drivers.last_name) as driverName, ';
			$driverName = ', CONCAT(drivers.first_name , " + ", team.first_name) as driverName, ';
		}

		$this->db->select('ABS( TIMESTAMPDIFF(MINUTE , Now( ) , vehicles.`modified` ) ) AS mintues_ago, telemetry, CONCAT(DATE_FORMAT( CONVERT_TZ( vehicles.`modified`, "+00:00", "-05:00" ) , "%d-%b-%Y %r" )," ","EST") AS timestamp, drivers.id, vehicles.tracker_id, drivers.profile_image'.$driverName.' vehicles.label,users.username,users.id as dispId,vehicles.latitude, vehicles.longitude, vehicles.id as vid,vehicles.city,vehicles.vehicle_address, vehicles.state,vehicles.team_driver_id');
		$this->db->join('vehicles','vehicles.driver_id = drivers.id');
		$this->db->join('users','drivers.user_id = users.id');
		$this->db->join('drivers as team','vehicles.team_driver_id = team.id','left');
		$this->db->where('drivers.status',1);
		$this->db->where('vehicles.team_driver_id !=',0);
		$this->db->where('vehicles.team_driver_id !=','');
		$this->db->where('vehicles.team_driver_id IS NOT NULL');
		$this->db->order_by("driverName","asc");
		if($userId){
			$this->db->where('drivers.user_id',$userId);
		}
		$this->db->where('drivers.organisation_id',$this->selectedOrgId);
		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	}
	
	/**
	 * Get drivers teams list new
	 */
	  
	public function getDriversListAsTeamNew( $userId =  null ) {
		$this->db->select('drivers.id, CONCAT(drivers.first_name , " + ", team.first_name) AS driverName,vehicles.label, users.username,users.id as dispId, vehicles.id as vid,vehicles.team_driver_id');
		$this->db->join('vehicles','vehicles.driver_id = drivers.id');
		$this->db->join('users','drivers.user_id = users.id');
		$this->db->join('drivers as team','vehicles.team_driver_id = team.id','left');
		$this->db->where('drivers.status',1);
		$this->db->where('vehicles.team_driver_id !=',0);
		$this->db->where('vehicles.team_driver_id !=','');
		$this->db->where('vehicles.team_driver_id IS NOT NULL');
		$this->db->order_by("driverName","asc");
		if($userId){
			$this->db->where('drivers.user_id',$userId);
		}
		$this->db->where('drivers.organisation_id',$this->selectedOrgId);
		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	}

	/**
	 * Getting list of dispatcher for job ticket page
	 */
	
	public function getDispatchersListForLoad() {
		$inArray = array(2,5);					// role id for 2 for dispatchers and role id 5 for admin_dispatcher
		$this->db->select("users.id AS dispId,users.username");
		$this->db->where('users.status',1);
		$this->db->where_in('users.role_id',$inArray);
		$result = $this->db->get('users');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	
	public function getDispatcherList( $userId =  null ) {
		$this->db->select("users.id AS dispId, drivers.id,drivers.profile_image, 'All Drivers' as driverName, '_idispatcher' AS label,users.username,vehicles.latitude, vehicles.longitude, users.id as vid,vehicles.city,vehicles.vehicle_address, vehicles.state");
		$this->db->join('vehicles','vehicles.driver_id = drivers.id');
		$this->db->join('users','drivers.user_id = users.id');
		$this->db->where('drivers.status',1);
		
		if($userId){
			$this->db->where("users.id",$userId);
		}
		$this->db->where('drivers.organisation_id',$this->selectedOrgId);
		$this->db->group_by('users.id');
		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	}
	
	public function changeDriverStatus($driver_id = null, $status = null) {
		if ( $status == 0 || $status == '' || $status == null ) {
			$newStatus = 1;
		} else {
			$newStatus = 0;
		}
		
		$this->db->where('id',$driver_id);
		$data = array(
			'status' => $newStatus,
		);
		$result = $this->db->update('drivers',$data);
		return true;
	}
	
	/**
	 * Getting drivers list for SRP page
	 */
	 
	public function getSearchDriversList( $userId = null ) {
		$this->db->select('drivers.id, concat(drivers.first_name," ",drivers.last_name," - ",vehicles.label) as driverName,users.username, vehicles.id as vehicleId');
		$this->db->join('vehicles','vehicles.driver_id = drivers.id');
		$this->db->join('users','drivers.user_id = users.id');
		$this->db->where('drivers.status',1)->order_by("driverName","asc");
		if ( !in_array($this->role,$this->config->item('with_admin_role'))) {
			if($userId)
				$this->db->where('drivers.user_id',$userId);
		}
		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	} 

	/*
	* method  : Get
	* params  : driverId
	* retrun  : driver Array
	* comment : used for fetching single row while updating status
	*/

	public function fetchSingleUpdatedRecord( $driverId = null ) {
		$this->db->select('drivers.first_name,drivers.last_name,drivers.phone,drivers.email,drivers.driver_license_number,drivers.id,drivers.status, CONCAT(users.first_name, " ", users.last_name) AS dispatcher, vehicles.label,vehicles.id as vehicle_id');
		$this->db->join('vehicles','vehicles.driver_id = drivers.id','LEFT');
		$this->db->join('users','drivers.user_id = users.id','LEFT');
		$this->db->where('drivers.id',$driverId); 
		$query = $this->db->get('drivers');
		if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return false;
        }

	}

	/*
	* method  : Get
	* params  : driverLicenceNo, driverId
	* retrun  : true or false
	* comment : used for checking duplicate entry for driver licence no 
	*/

	public function checkLicenceNumberExist( $entityValue = '', $entityName = '' , $table ='', $entityId = null ) {
		$this->db->select('id');
		if ( $entityId != '' && $entityId != null ){
			$this->db->where(array($entityName => $entityValue, 'id !=' => $entityId ));
		}
		else{
			$this->db->where($entityName, $entityValue);
		}

		$this->db->where(['organisation_id'=>$this->selectedOrgId]);
		
		$result = $this->db->get($table);
		// echo $this->db->last_query();
		
		if ( $result->num_rows() > 0 )
			return  true;
		else
			return false;
	}

	/*
	* method  : Get
	* params  : null
	* retrun  : drivers array
	* comment : used for checking active drivers without truck assigned
	*/
	
	public function fetchDriversWithoutTruck( $filters = array(),$total = false ) {

		if(isset($filters["driverId"])){ //Show 0 active driver without truck if driver selected
			return 0;
		}

		$this->db->select('group_concat(drivers.id) as ids');
		$this->db->join('vehicles','vehicles.driver_id = drivers.id OR vehicles.team_driver_id = drivers.id');
		$this->db->where('drivers.status',1);
		if(!isset($filters["driverId"]) && isset($filters["dispatcherId"])){
			$this->db->where_in('drivers.user_id',$filters["dispatcherId"]);
		}
		$res = $this->db->get('drivers');
		$result = "";
		if( $res->num_rows() > 0 ){
			$result = $res->row_array()["ids"];
		}

		if($total){
			$this->db->select('count(d.id) as totalCount');	
		}else{
			$this->db->select("d.id as driver_id, 'withoutTruck' as recordType,  v.team_driver_id,  CONCAT( 'Truck - ', v.label) AS truckName, CONCAT(u.first_name, ' ' , u.last_name) as dispatcher, CASE v.driver_type 
							WHEN 'team' THEN CONCAT(d.first_name,' + ',team.first_name)  ELSE concat(d.first_name,' ',d.last_name)  END AS driverName");	
				
		}

		$this->db->join("vehicles as v", "d.id = v.driver_id","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		$this->db->join('users as u','d.user_id = u.id','left');
		$this->db->where('d.status',1);
		
		$this->db->where(['d.status'=>1,'d.organisation_id'=>$this->selectedOrgId]);

		if(!isset($filters["driverId"]) && isset($filters["dispatcherId"]) ){
			$this->db->where_in('d.user_id',$filters["dispatcherId"]);
		}


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
			$this->db->order_by('CASE v.driver_type 
							WHEN "team" THEN CONCAT(d.first_name," + ",team.first_name)  ELSE concat(d.first_name," ",d.last_name)  END'.$filters["sortType"]);
		}else if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "dispatcher"){
			$this->db->order_by("CONCAT(u.first_name, ' ' , u.last_name) ".$filters["sortType"]);
		}else{
			$this->db->order_by("CONCAT(u.first_name, ' ' , u.last_name) ".$filters["sortType"]);
			//$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);	
		}
		if(!$total){
			if(empty($filters['export'])){
				$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
			}
		}


		$result = $this->db->get('drivers as d');
		if($total){
			return $result->row_array()["totalCount"];
		}

		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return array();
		}

	}
	public function fetchTrucksWithoutDriver( $filters = array(),$total = false ) {
		if(isset($filters["driverId"]) || (isset($filters["userToken"]) && isset($filters["dispId"]))){ //Show 0 active driver without truck if driver selected
			return 0;
		}

		if($total){
			$this->db->select('count(v.id) as totalCount');	
		}else{
			$this->db->select("v.id as vehicleId, 'trucksWithoutDriver' as recordType,  v.label, v.vin, v.model");	
		}

		$this->db->where(['v.vehicle_status'=>1,'v.organisation_id'=>$this->selectedOrgId]);

		$this->db->group_start();
		$this->db->where("v.driver_id = 0 OR v.driver_id IS NULL OR  v.driver_id = ''");
		$this->db->group_end();

		if(isset($filters["vehicles"]) && is_array($filters["vehicles"])){
			$this->db->where_in("v.id", $filters["vehicles"]);
		}

		if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){
            $this->db->group_start();
            $this->db->like("LOWER(v.label)", strtolower($filters['searchQuery']));
            $this->db->or_like("LOWER(v.vin)", strtolower($filters['searchQuery']));
            $this->db->or_like("LOWER(v.model)", strtolower($filters['searchQuery']));
            $this->db->group_end();
		}

		$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
		$this->db->order_by("v.label ".$filters["sortType"]);
		if(!$total){
			if(empty($filters["export"])){
				$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
			}
		}

		$result = $this->db->get('vehicles as v');
		
		// echo $this->db->last_query();die;

		if($total){
			return $result->row_array()["totalCount"];
		}

		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return array();
		}

	}


	public function trucksReporting($filters,$total=false,$type="stale")
	{
		if($total){
			$this->db->select("count(v.id) as total" );
		}else{
			$this->db->select("CASE v.driver_type 
							WHEN 'team' THEN CONCAT(d.first_name,' + ',team.first_name)  ELSE concat(d.first_name,' ',d.last_name)  END AS driverName, 'trucksReporting' as recordType,
							ABS( TIMESTAMPDIFF(MINUTE , Now( ) , v.`modified` ) ) AS mintues_ago,  v.id as vehicleId, v.tracker_id, concat('Truck - ',v.label) as truckName, CONCAT(u.first_name, ' ' , u.last_name) as dispatcher
							");	
		}

		$this->db->join('drivers as d','v.driver_id = d.id', 'left');
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		$this->db->join('users as u','d.user_id = u.id');
		$this->db->where(['d.organisation_id'=>$this->selectedOrgId,'v.vehicle_status' => 1, "v.tracker_id != "=>0, "v.tracker_id IS NOT "=>NULL]);
		$this->db->where("ABS( TIMESTAMPDIFF(MINUTE , Now( ) , v.`modified` ) ) >= 120");

		if(isset($filters["userToken"]) && isset($filters["userType"]) && $filters["userType"] == "dispatcher" ){
			$filters["dispatcherId"] = $filters["userToken"];
		}
		if(isset($filters["userType"]) && $filters["userType"] == "driver"){
			$filters["dispatcherId"] = $filters["dispId"];	
			$filters["driverId"]     = $filters["userToken"];	
		}


		if(isset($filters["driverId"]) && isset($filters["dispatcherId"]) && isset($filters["secondDriverId"]) &&  $filters["secondDriverId"] != 0 && $filters["secondDriverId"] != "") {
			$this->db->where_in('d.user_id',$filters["dispatcherId"]);
			$this->db->where_in('d.id',$filters["driverId"]);
			$this->db->where_in('v.team_driver_id',$filters["secondDriverId"]);
			$this->db->where('v.driver_type ',"team");
		}else if(!isset($filters["driverId"]) && isset($filters["dispatcherId"]) ) {
			$this->db->where_in('d.user_id',$filters["dispatcherId"]);
		}else if(isset($filters["driverId"]) && isset($filters["dispatcherId"]) ){
			$this->db->where_in('d.id',$filters["driverId"]);
			$this->db->where_in('d.user_id',$filters["dispatcherId"]);
		}
		
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
			$this->db->order_by('CASE v.driver_type 
							WHEN "team" THEN CONCAT(d.first_name," + ",team.first_name)  ELSE concat(d.first_name," ",d.last_name)  END'.$filters["sortType"]);
		}else if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "dispatcher"){
			$this->db->order_by("CONCAT(u.first_name, ' ' , u.last_name) ".$filters["sortType"]);
		}else{
			$this->db->order_by("CONCAT(u.first_name, ' ' , u.last_name) ".$filters["sortType"]);
		}

		if(!$total){
			if(empty($filters['export'])){
				$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
			}

		}


		$result = $this->db->get("vehicles as v");
		
		// echo $this->db->last_query();

		if($total){
			return $result->row_array()["total"];
		}
		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return array();
		}
	}


	public function fetchDriversLoad( $driverId = null, $type = '' ) {
		$todayDate = date('Y-m-d');
		$this->db->select("loads.id,driver_id,dispatcher_id,second_driver_id,driver_type,PickupDate,DeliveryDate,delete_status,CONCAT(d.first_name,' + ',team.first_name) as teamName, concat(d.first_name,' ',d.last_name) as driverName");
		$this->db->join("drivers as d", "loads.driver_id = d.id","Left");
		$this->db->join('drivers as team','loads.second_driver_id = team.id','left');	
		$this->db->where('loads.driver_id',$driverId);
		$where = "(loads.PickupDate >= '$todayDate' OR loads.DeliveryDate >= '$todayDate')";
		$this->db->where($where);

		if( $type != '' )
			$this->db->where('loads.driver_type',$type);

		$result= $this->db->get('loads');
		
		if($result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}

	}

	public function updateLoadsAssignments($data = array() ) {
		$this->db->update_batch('loads', $data, 'id'); 
		return true;
	}

	public function updateDriver($data = array()) {
		$id = $data['id'];
		unset($data['id']);
		$this->db->where('id',$id);
		$this->db->update('loads',$data);
		return true;
	}
	
	public function fetchDriversForCSV($userId = null,$search = null){
        
        $this->db->select('d.first_name,d.last_name,d.email,v.label,d.driver_license_number,CONCAT(u.first_name, " ", u.last_name) AS dispatcher,d.phone,if(d.status=1,"Active","In-Active"),d.date_of_birth as dob,v.city,v.id as vehicle_id');
		
		$this->db->join("vehicles as v", "d.id = v.driver_id","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		$this->db->join('users as u','d.user_id = u.id','left');

		if ( is_array($userId) && !empty($userId) ) {
			$this->db->where_in('d.user_id',$userId); 
		} else if( $userId != null)
			$this->db->where('d.user_id',$userId);

		if(!empty($search['searchText'])){
			$this->db->like('d.first_name',$search['searchText']); 
			$this->db->or_like('d.last_name',$search['searchText']);

			$this->db->like('u.first_name',$search['searchText']); 
			$this->db->or_like('u.last_name',$search['searchText']); 
			
			$this->db->or_like('d.email',$search['searchText']); 
			$this->db->or_like('d.email',$search['searchText']); 
			$this->db->or_like('d.driver_license_number',$search['searchText']); 
		}
		
		$this->db->where('d.organisation_id',$this->selectedOrgId);
        $query = $this->db->get("drivers as d");

		if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
	}
}
