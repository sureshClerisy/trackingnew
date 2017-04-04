<?php
class Script extends CI_Model {

	function __construct() {
        parent::__construct();
        $this->load->library('email');
        $this->limit_per_page = $this->config->item('limit_per_page');  
		
    }

    public function get_brokers() {
        $this->db->select('*')->from('broker_info');
        $result = $this->db->get(); 
        if($result->num_rows()>0){
			return $result->result_array();
        } else {
			return false;
		}
    } 

  
	
	/**
	 * Getting brokers list for dropdown on add load
	 */
	 
	public function getBrokersList() {
		$this->db->select('TruckCompanyName,id,MCNumber');
		$whereCondition = array('black_list' => 0, 'MCNumber !=' => 0 );
		$this->db->where($whereCondition)->order_by("TruckCompanyName","asc");
		$result = $this->db->get('broker_info');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	} 
	
		
	/**
	 * Fetching information from backup table 'broker_info_revert'
	 */
	  
	public function getBrokerslistingRevertTable() {
		$this->db->select("id,MCNumber,PointOfContact,REPLACE(REPLACE(REPLACE(REPLACE(`PointOfContactPhone`,'(',''),')',''),'-',''),' ','') as pointPhoneNumber,REPLACE(REPLACE(REPLACE(REPLACE(`TruckCompanyPhone`,'(',''),')',''),'-',''),' ','') as truckPhoneNumber,TruckCompanyEmail,TruckCompanyPhone,TruckCompanyFax");
		$result = $this->db->get('broker_info_revert');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	} 

	/**
	 * Fetching information from loads table to update fields
	 */
	  
	public function getLoadslisting() {
		$this->db->select("id,broker_id");
		$result = $this->db->get('loads');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	}
	
	/**
	 * Fetching information from broker info table using its id
	 */
	  
	public function getBrokerInfo( $brokerId = null ) {
		$this->db->select("id,MCNumber,PointOfContact,PointOfContactPhone,TruckCompanyPhone,TruckCompanyEmail,TruckCompanyFax");
		$this->db->where('id',$brokerId);
		$result = $this->db->get('broker_info');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return array();
		}
	}
	
	/**
	 * Updating loads table 5 fields with broker info  talbe fields
	 */
	 
	public function updateLoadsTable( $data = array(), $loadId = null ) {
		$this->db->where('loads.id',$loadId);
		$this->db->update('loads',$data);
		return true;
	}
	 
	public function updateBrokerInfoTable( $data = array(), $mc_number = null ) {
		$this->db->where('broker_info.MCNumber',$mc_number);
		$this->db->update('broker_info',$data);
		return true;
	}
	
	/**
	 * Fetching all records from loads table
	 */
	
	public function fetchAllLoads() {
		$this->db->select('id,PickupAddress,OriginStreet,OriginCity,OriginState,OriginCountry,DestinationAddress,DestinationStreet,DestinationCity,DestinationState,DestinationCountry');
		return $this->db->get('loads')->result_array();
	} 
	
	/**
	 * updating the loads table fields by splitting
	 */
	
	public function saveLoadData($data = array(), $loadId = null) {
		$this->db->where('loads.id',$loadId);
		$this->db->update('loads',$data);
		return true;
	} 
	
	/**
	 * Fetching all records from extra Stops table
	 */
	
	public function fetchAllExtraStops() {
		$this->db->select('id,extraStopAddress');
		return $this->db->get('extra_stops')->result_array();
	} 
	
	/**
	 * updating the loads table fields by splitting
	 */
	
	public function saveExtraStopData($data = array(), $stopId = null) {
		$this->db->where('extra_stops.id',$stopId);
		$this->db->update('extra_stops',$data);
		return true;
	} 
	
	/**
	* Fetching driver logs from logs table
	*/

	public function getDriverLogs($entity) {
		$this->db->where('entity_name',$entity);
		$result = $this->db->get('activity_log');
		return $result->result_array();

	}
	 
}

