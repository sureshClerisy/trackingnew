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
}