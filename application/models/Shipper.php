<?php
class Shipper extends Parent_Model {

	function __construct(){
		parent::__construct();
        $this->load->library('email');
        $this->limit_per_page = $this->config->item('limit_per_page'); 
    }

    /**
    * Fetching shippers listing
    */

    public function getShippersList($args= array(), $total = false ){

        if(!$total){
            $this->db->select('*');
        } else {
            $this->db->select("count(id) as totalRows");
        }

        $this->db->where(['organisation_id'=>$this->selectedOrgId,'deleted'=>0]);
        
        if(isset($args["searchQuery"]) && !empty($args["searchQuery"])){
            $this->db->group_start();
            $this->db->like('LOWER(shipperCompanyName)', strtolower($args['searchQuery']));
            $this->db->or_like('LOWER(postingAddress)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(city)', strtolower($args['searchQuery']) );
            $this->db->or_like('LOWER(state)', strtolower($args['searchQuery']) );
            $this->db->or_like('zipcode', strtolower($args['searchQuery']) );
            $this->db->group_end();
        }

        if(isset($args["sortColumn"]))
            $this->db->order_by("shippers.".$args["sortColumn"],$args["sortType"]);  

        if(!$total){
            $args["limitStart"] = $args["limitStart"] == 1 ? 0 : $args["limitStart"];
            $this->db->limit($args["itemsPerPage"],$args["limitStart"]);
        }

        $result = $this->db->get('shippers');
        
        // echo $this->db->last_query();

        if($result->num_rows()>0){
            return $result->result_array();
        } else {
            return [];
        }
    } 

    /**
     * Add and update the broker table
     */ 
    public function insertUpdateshipper($shipperInfo = array()) {

      if(!empty($shipperInfo['id'])){
        $rowData = $this->db->select('count(id) as count')
        ->from('shippers')
        ->where('id',$shipperInfo['id'])
        ->get()
        ->result_array();
        if($rowData['0']['count'] > 0){
            $this->db->where('id',$shipperInfo['id']);
            $res            = $this->db->update('shippers',$shipperInfo);
            $shippersLastId  = $shipperInfo['id'];
            $updated        = 'yes';
        }
    } else {
        $shipperInfo['status'] = 1;
        $shipperInfo['organisation_id'] = $this->selectedOrgId;

        $this->db->insert('shippers',$shipperInfo);
        $shippersLastId   = $this->db->insert_id();
        $updated        = 'no';
    } 
    return array($updated,$shippersLastId);
}

    /**
    * Method getshippersById
    * @param shippersID
    * @return Data Row
    */

    public function getshippersById($shippersId = null){

        $result = $this->db->select('*')
        ->where('id',$shippersId)
        ->get('shippers');
        
        if ( $result->num_rows() > 0 ) {
            return $result->row_array();
        } else {
            return array();
        }
    }

    public function deleteShipper($id=null){
        $this->db->where('id', $id);
        $this->db->update('shippers',['deleted' => 1]);
        return true;
    }

    public function getShipperInfo( $shipperId = null ) {
        return $this->db->select('id,shipperCompanyName,postingAddress,city,state,zipcode,rating')->where('shippers.id',$shipperId)->get('shippers')->row_array();
    }

    public function fetchShipperInfo( $shipperId = null ) {
        return $this->db->select('id,shipperCompanyName,postingAddress,city,state,zipcode,rating,status')->where('shippers.id',$shipperId)->get('shippers')->row_array();
    }

    public function changeShipperStatus($shipperId = null, $status = null) {
        if ( $status == 0 || $status == '' || $status == null ) {
            $newStatus = 1;
        } else {
            $newStatus = 0;
        }
        
        $this->db->where('id',$shipperId);
        $data = array(
            'status' => $newStatus,
            );
        $result = $this->db->update('shippers',$data);
        return true;
    }

    public function exportShippers($args= array(), $total = false ){

       $this->db->select('*')->where(['deleted'=>0,'organisation_id'=>$this->selectedOrgId]);
        
       if(isset($args["searchText"]) && !empty($args["searchText"])){
           $this->db->group_start();
           $this->db->or_like('shipperCompanyName', strtolower($args['searchText']));
           $this->db->or_like('LOWER(postingAddress)', strtolower($args['searchText']) );
           $this->db->or_like('LOWER(city)', strtolower($args['searchText']) );
           $this->db->or_like('LOWER(state)', strtolower($args['searchText']) );
           $this->db->or_like('zipcode', strtolower($args['searchText']) );
           $this->db->or_like('status', strtolower($args['searchText']) );
           $this->db->group_end();
       }

       $result = $this->db->get('shippers');
       if($result->num_rows()>0){
           return $result->result_array();
       } else {
           return [];
       }
    }

    /**
    * Fetch shippers list
    */
    public function fetchShipperList() {
        $this->db->select('id,shipperCompanyName');
        $this->db->where(['status'=>1,'deleted'=>0]);
        
        $result = $this->db->get('shippers');
        if( $result->num_rows() > 0 ) {
            return $result->result_array();
        } else {
            return array();
        }
    }
}