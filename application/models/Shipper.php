<?php
class Shipper extends Parent_Model {

	function __construct(){
		parent::__construct();
        $this->load->library('email');
        $this->limit_per_page = $this->config->item('limit_per_page');  
    }

    public function get_shippers(){
        
        $result = $this->db->select('*')
                        ->from('shippers')
                        ->where('deleted',0)
                        ->get();        
        
        if($result->num_rows()>0){
            return $result->result_array();
        } else {
            return false;
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
        }}else{
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
            return false;
        }
    }

    public function deleteShipper($id=null){
        $this->db->where('id', $id);
        $this->db->update('shippers',['deleted' => 1]);
        return true;
    }

    public function getBrokerInfo( $brokerId = null ) {
        return $this->db->select('id,TruckCompanyName,postingAddress,city,state,zipcode,rating')->where('shippers.id',$brokerId)->get('shippers')->row_array();
    }

    
}