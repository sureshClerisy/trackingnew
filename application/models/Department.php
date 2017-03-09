<?php

class Department extends CI_Model
{
	public $limit_per_page;
	
	function __construct() {
		parent::__construct();
		$this->limit_per_page = $this->config->item('limit_per_page');  
	}

	public function countAllRecords($search = '' , $page = null) {
        if($search){
            $this->db->like('label', $search);
            $num_rows = $this->db->count_all_results('departments');
        } else {
			$num_rows = $this->db->count_all_results('departments');
		}
		return $num_rows;
    }
    
    public function fetchAllRecords($search = '', $page = null,$sort=null,$order=null) {
        $data = array();
        $this->db->select('*');
        if ($search != '') {            
            $this->db->like("label", "$search");
        }
        
          if(empty($sort))
       {
		   $sort = 'label';
		   $order = 'asc';
	   }
	  
		$this->db->order_by($sort,$order);

         if ( $page != '' )
			$this->db->limit($this->limit_per_page, ($page - 1) * $this->limit_per_page);
		else 
			$this->db->limit($this->limit_per_page, 0);

        $query = $this->db->get('departments');
		if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
    }
    
	public function save( $data = array() ){
		
		$result = $this->db->insert('departments',$data);
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get_department_data( $dept_id = Null ) {
		$this->db->select('*');
		$this->db->where('id', $dept_id);
		$result = $this->db->get('departments');
		if( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	}
	
	public function update( $data = array(),$id=null ){
		$result = $this->db->update('departments',$data,"id={$id}");
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
	
	public function delete( $dept_id = null ) {
		$this->db->where('id', $dept_id);
        $result = $this->db->delete('departments');
        if ($result) {
            return true;
        } else {
            return false;
        }
	}


	public function napi_save_department() {
        
        $department_info = $this->input->post();
        
        $data = unserialize($department_info['data']);
        
        $data->created = date('Y-m-d h:m:s');
        
        $data->modified = date('Y-m-d h:m:s');
        
        $data->navixy_id = $data->id;
        $data->address 	= $data->location->address;
        $data->lat 		= $data->location->lat;
        $data->lng 		= $data->location->lng;

        unset($data->location);
        unset($data->id);
        
        $this->db->where("navixy_id",$data->navixy_id);
        $num_rows   = $this->db->count_all_results('departments');        
        if($num_rows){
            $this->db->update('departments', $data, "navixy_id={$data->navixy_id}");
            return;
        }
        $this->db->insert('departments', $data);        
        return;
    }

    public function napi_save_all_departments() {

        $garage_info = $this->input->post();
        
        foreach ($garage_info['department'] as $key => $garage) {
            
            $data = unserialize($garage);
            $data->created  = date('Y-m-d h:m:s');
            $data->modified  = date('Y-m-d h:m:s');

			$data->navixy_id 	= $data->id;
	        $data->address 		= (!empty($data->location->address))?$data->location->address:'';
	        $data->lat 			= (!empty($data->location->lat))?$data->location->lat:'';
	        $data->lng 			= (!empty($data->location->lng))?$data->location->lng:'';

            unset($data->location);
            unset($data->id);

            $this->db->where("navixy_id",$data->navixy_id);

            $num_rows       = $this->db->count_all_results('departments');        
            if($num_rows){
                $this->db->update('departments', $data, "navixy_id={$data->navixy_id}");
            }else{
                $this->db->insert('departments', $data);
            }
        }
    }

}
