<?php

/**
* 
*/
class Garage extends CI_Model{
	
	public $limit_per_page;
	function __construct() {
        parent::__construct(); 
        
        $this->limit_per_page = $this->config->item('limit_per_page');  
        }

    public function garag_list(){
    	
    	$data  = $this->db->select('id,organization_name')->get('garage');
    	if($data->num_rows()>0){
    		return $data->result_array();
    	}
    	return false;
    }

    public function add_edit_garage($insert=null,$id=null){


        $data = $insert;
        $data['created'] = date('Y-m-d h:m:s');
        $data['updated'] = date('Y-m-d h:m:s');

        if (!empty($id)) {
            // $this->db->where('id', $id);
            $this->db->update('garage', $data, "id={$id}");
            return true;
        }
        $this->db->insert('garage', $data);        
    }

    public function get_row($id=null){
        
        $data = $this->db->select('*')->from('garage')->where('id',$id)->get();

        if($data->num_rows()>0){
            return $data->row_array();
        }
        return false;
    }

    public function countAllRecords($search = '' , $page = null) {
        if($search){
            $this->db->like('organization_name', $search);
            $this->db->or_like('mechanic_name', $search);
            $num_rows = $this->db->count_all_results('garage');
        } else {
			$num_rows = $this->db->count_all_results('garage');
		}
		return $num_rows;
    }
    
    public function fetchAllRecords($search = '', $page = null,$sort = null,$order = null) {
        $data = array();
        $this->db->select('*');
        if ($search != '') {            
            $this->db->like("organization_name", "$search");
            $this->db->or_like("mechanic_name", "$search");
        } 
           
       if(empty($sort))
       {
		   $sort = 'organization_name';
		   $order = 'asc';
	   }
	  
		$this->db->order_by($sort,$order);
        if ( $page != '' )
			$this->db->limit($this->limit_per_page, ($page - 1) * $this->limit_per_page);
		else 
			$this->db->limit($this->limit_per_page, 0);

        $query = $this->db->get('garage');
		if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
    }

    public function napi_save_all_vehicle() {
        $garage_info = $this->input->post();
        
        foreach ($garage_info['garage'] as $key => $garage) {
            
            $data = unserialize($garage);
            $data->created  = date('Y-m-d h:m:s');
            $data->updated  = date('Y-m-d h:m:s');
            $data->address  = ($data->location->address)?$data->location->address:'--';
            $data->radius   = ($data->location->radius)?$data->location->radius:'';
            $data->lat      = ($data->location->lng)?$data->location->lng:'';
            $data->lng      = ($data->location->lat)?$data->location->lat:'';

            $data->garage_id = $data->id;
            unset($data->location);
            unset($data->id);

            $this->db->where("garage_id",$data->garage_id);

            $num_rows       = $this->db->count_all_results('garage');        
            if($num_rows){
                $this->db->update('garage', $data, "garage_id={$data->garage_id}");
            }else{
                $this->db->insert('garage', $data);
            }
        }
    }

    public function napi_save_vehicle() {
        
        $garage_info = $this->input->post();

        $data = unserialize($garage_info['data']);
        
        $data->created  = date('Y-m-d h:m:s');
        $data->updated  = date('Y-m-d h:m:s');
        $data->address  = ($data->location->address)?$data->location->address:'--';
        $data->radius   = ($data->location->radius)?$data->location->radius:'';
        $data->lat      = ($data->location->lng)?$data->location->lng:'';
        $data->lng      = ($data->location->lat)?$data->location->lat:'';

        $data->garage_id = $data->id;
        unset($data->location);
        unset($data->id);
        
        $this->db->where("garage_id",$data->garage_id);

        $num_rows       = $this->db->count_all_results('garage');        
        if($num_rows){
            $this->db->update('garage', $data, "garage_id={$data->garage_id}");
            return;
        }
        $this->db->insert('garage', $data);
        return;
    }
    
    public function delete($garageID = null) {		
		$this->db->where('id', $garageID);
        $result = $this->db->delete('garage');
        if ($result) {
            return true;
        } else {
            return false;
        }
	}
    
}
