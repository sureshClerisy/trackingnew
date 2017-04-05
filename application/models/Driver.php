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

	public function countAllRecords($keyword = null, $page = null, $userId=null) {
        if ($keyword) {
            $this->db->like('first_name', $keyword);
            $this->db->or_like('email', $keyword);
        }
        
        if ( $this->session->userdata('role') != 3 ) {
			if(!empty($userId))
				$this->db->where('user_id',$userId);
		}
		
        $num_rows = $this->db->count_all_results('drivers');
       
        return $num_rows;
    }
    
    //~ public function fetchAllRecords($search = '', $page = null, $userId = null,$sort=null,$order=null) {
    public function fetchAllRecords($userId = null) {
        $data = array();
        /*$this->db->select('drivers.first_name,drivers.last_name,drivers.phone,drivers.email,drivers.driver_license_number,drivers.id,drivers.status, CONCAT(users.first_name, " ", users.last_name) AS dispatcher, vehicles.label,vehicles.id as vehicle_id');
       	$this->db->join('vehicles','vehicles.driver_id = drivers.id','LEFT');
		$this->db->join('users','drivers.user_id = users.id','LEFT');
		$this->db->from('drivers');       */

		$this->db->select('CONCAT(SUBSTRING(d.first_name, 1, 1),SUBSTRING(d.last_name, 1, 1)) as userIntial, v.city, d.color,  d.id as driverId, d.first_name,d.last_name,d.phone,d.email,d.driver_license_number,d.id,d.status, CONCAT(u.first_name, " ", u.last_name) AS dispatcher, d.user_id as dispId, v.team_driver_id,  v.label,v.id as vehicle_id');
		
		$this->db->join("vehicles as v", "d.id = v.driver_id","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		$this->db->join('users as u','d.user_id = u.id','left');
		
        if( $userId != null)
			$this->db->where('d.user_id',$userId); 
			 
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
    
	public function addDrivers( $data = array(),$userId ){
		if(empty($data['user_id'])){
			$data['user_id'] = $userId;
		}
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
		//echo '<pre>';print_r($data);print_r($id);
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
	
	/********* Dispatcher List - r288 ****************/
	public function get_dispatcher_list() {
		$this->db->where_in('role_id',array(2,5));
		$this->db->select('id,username');
		$result = $this->db->get('users');
		if ($result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	public function get_selected_dispatcher($driver_id = null) {
		//~ $this->db->where('id',$user_id);
		//~ $this->db->select('id,username');
		//~ $result = $this->db->get('users');
		//~ if ($result->num_rows() > 0 ) {
			//~ return $result->row_array();
		//~ } else {
			//~ return false;
		//~ }
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

		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
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

		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
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
		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
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
		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
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
		$this->db->group_by('users.id');
		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
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
		if ( !in_array($this->session->userdata('role'),$this->config->item('with_admin_role'))) {
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
		if ( $entityId != '' && $entityId != null )
			$this->db->where(array($entityName => $entityValue, 'id !=' => $entityId ));
		else
			$this->db->where($entityName, $entityValue);
		$result = $this->db->get($table);
		if ( $result->num_rows() > 0 )
			return  true;
		else
			return false;
	}
	
}
