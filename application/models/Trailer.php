<?php

/**
 * 
 */
class Trailer extends Parent_Model {

	public $limit_per_page;
    function __construct() {
        parent::__construct();
        $this->limit_per_page = $this->config->item('limit_per_page');  
	}

	/**
	 * Fetching trailer list
	 */
	 
    public function getTrailersList() {
		$this->db->select('trailers.id,trailers.truck_id,trailers.unit_id,trailers.year,trailers.vin,trailers.owner,trailers.type,trailers.description,trailers.status,vehicles.label');
		$this->db->join('vehicles','vehicles.id = trailers.truck_id','LEFT');
        $result = $this->db->get('trailers'); 
        //echo $this->db->last_query();die;
        if( $result->num_rows() > 0){
			return $result->result_array();
        } else {
			return array();
		}
    } 
    
	/**
	 * Getting trailer data for edit
	 */
	 
	public function getTrailerInfo( $trailerId = null ) {
		$this->db->select('trailers.*,concat("Truck-",vehicles.label) as truckName');
		$this->db->join('vehicles','vehicles.id = trailers.truck_id','LEFT');
		return $this->db->where('trailers.id',$trailerId)->get('trailers')->row_array();
	}
	 
	/**
     * Adding  and updatingthe trailer to db
     */
    
    public function addEditTrailer() {
		$saveData = $this->input->post();
		if ( isset($saveData['monthly_payment']) ) {
			$saveData['monthly_payment'] = str_replace('$','',$saveData['monthly_payment']);
			$saveData['monthly_payment'] = str_replace(',','',$saveData['monthly_payment']);
		}
		
		if ( isset($saveData['purchase_price']) ) {
			$saveData['purchase_price'] = str_replace('$','',$saveData['purchase_price']);
			$saveData['purchase_price'] = str_replace(',','',$saveData['purchase_price']);
		}
		unset($saveData['truckName']);
		if ( isset( $saveData['id']) && $saveData['id'] != '' && $saveData['id'] != null) {
			$this->db->update('trailers',$saveData,"id={$saveData['id']}");
			$lastId = $saveData['id'];
		} else {
			$this->db->insert('trailers', $saveData);
			$lastId = $this->db->insert_id();
		}
		return $lastId;
	}

	/**
	 * Deleteing Trailer
	 */
	
	public function deleteTrailer( $id=null ){
        $this->db->where('id',$id);
		$result = $this->db->delete('trailers');
        if ($result) {
            return true;
        } else {
            return false;
        }
    } 
	
    /**
	 * checking if same truck already assinged to another trailer
	 */ 
	 
	public function checkChangeTrailer( $truckId = null, $trailerId = null ) {
		$finalArray = array();
		$this->db->select('id,unit_id');
		$condition = array('truck_id' => $truckId, 'id !=' => $trailerId);
		$this->db->where($condition);
		$result = $this->db->get('trailers');
		if ( $result->num_rows() > 0 ) {
			$finalArray = $result->row_array();
			return $finalArray;
		} else {
			return $finalArray;
		}
	}
	
	/*
     * CHange status
     */
      
    public function changeTrailerStatus($trailerId = null, $status = null) {
		if ( $status == 0 || $status == '' || $status == null ) {
			$newStatus = 1;
		} else {
			$newStatus = 0;
		}
		
		$this->db->where('id',$trailerId);
		$data = array(
			'status' => $newStatus,
		);
		$result = $this->db->update('trailers',$data);
		return true;
	}
	
	/**
	 * Check if trailer unit id exist
	 */
	 
	public function checkTrailerUnitExist( $trailerNo = null, $id = null ) {
		$this->db->select('id');
		if ( $id != null && $id != '' )
			$this->db->where(array('unit_id' => $trailerNo,'id !=' => $id));
		else
			$this->db->where('unit_id',$trailerNo);
			
		$result = $this->db->get('trailers');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return array();
		}
	}

	 public function fetchDriversForCSV($search) {
		$this->db->select('trailers.truck_id,trailers.unit_id,trailers.vin,trailers.owner,trailers.type,trailers.description,trailers.monthly_payment,trailers.due_date,trailers.purchase_price,trailers.interest_rate,trailers.notes,trailers.status,trailers.created');
		
		if(!empty($search['searchText'])){
			$this->db->like('trailers.unit_id',$search['searchText']); 
			$this->db->or_like('trailers.vin',$search['searchText']);
			$this->db->or_like('trailers.owner',$search['searchText']);
			$this->db->or_like('trailers.type',$search['searchText']);
			$this->db->or_like('trailers.description',$search['searchText']);
		}

        $result = $this->db->get('trailers');
        // echo $this->db->last_query();
        
        if( $result->num_rows() > 0){
			return $result->result_array();
        } else {
			return array();
		}
    }  
}
