<?php

class Utility extends Parent_Model 
{

	/**
	* @param  : null
	* @return : array
	* comment : fetch vehicles listing assigned to user
	**/
	public function getIdleDrivers( $date = '' )
	{
		if(empty($date)){ $date = date("Y-m-d"); }

		$this->db->select("d.id as driver_id, v.id as vehicle_id, u.id as dispatcher_id, v.driver_type");
		$this->db->join("vehicles as v", "d.id = v.driver_id","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		$this->db->join('users as u','d.user_id = u.id','left');	

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
	
		$query = $this->db->get('drivers as d');
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}



	/**
	* @param  : null
	* @return : array
	* comment : fetch vehicles listing assigned to user
	**/
	public function getAllDrivers( )
	{
		$this->db->select("d.id as driver_id, v.id as vehicle_id, v.team_driver_id,  u.id as dispatcher_id, v.driver_type");
		$this->db->join("vehicles as v", "d.id = v.driver_id","Left");
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		$this->db->join('users as u','d.user_id = u.id','left');	

		$this->db->where("d.status = 1 and d.id IN (SELECT DISTINCT  driver_id  FROM `vehicles` where driver_id != '0' and driver_id is not null and driver_id != '')	
						AND d.id NOT IN (SELECT DISTINCT vehicles.team_driver_id FROM vehicles where vehicles.driver_type = 'team')
						");
	
		$query = $this->db->get('drivers as d');
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}

	/**
	* @param  : null
	* @return : array
	* comment : fetch vehicles listing assigned to user
	**/
	public function isAlreadyPredict( $args )
	{
		$this->db->select("id"); 
		if( $args["driver_type"] == "team" ){
			$this->db->where( array( "driver_id" => $args["driver_id"], "team_driver_id" => $args["team_driver_id"]) );
		}else{
			$this->db->where("driver_id", $args["driver_id"]);
		}
		$result = $this->db->get('perdict_next_jobs');
		if ($result->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}


	/**
	* @param  : null
	* @return : array
	* comment : fetch vehicles listing assigned to user
	**/
	public function savePredictedJobs( $args, $jobs )
	{
		$args["jobs"] = serialize( $jobs );
		$res = $this->db->insert( 'perdict_next_jobs', $args );
	}

	/**
	* @param  : null
	* @return : array
	* comment : fetch vehicles listing assigned to user
	**/
	public function getNextPredictedJobs( $dispId ){
		$this->db->select(" CASE pj.driver_type 
    						WHEN 'team' THEN CONCAT(d.first_name,' + ',team.first_name) 
    									ELSE concat(d.first_name,' ',d.last_name) 
    									END AS driverName,	jobs "); 
		
		$this->db->join("drivers as d", "pj.driver_id = d.id","Left");
		$this->db->join('drivers as team','pj.team_driver_id = team.id','left');	
		$this->db->where( "dispatcher_id", $dispId );
		$result = $this->db->get('perdict_next_jobs as pj');
		//echo $this->db->last_query();die;
		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return false;
		}
	}


	
}