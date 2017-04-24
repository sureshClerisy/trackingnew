<?php

class User extends Parent_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('email');
	}
	
	public function check_admin_credentials( $username = null, $pass = null) {
		$password = md5($pass);
		$this->db->Select('*');
		$this->db->where(array('username' => $username, 'password' => $password, 'status' => 1));
		$query = $this->db->get('users');
		
		if($query->num_rows() > 0)
		{
			$data = $query->row_array();

			$user_email = $data['email'];
			$user_username = $data['username'];
			$user_id 	= $data['id'];
			$role 		= $data['role_id'];
			$user_fname = $data['first_name'];
			$user_last 	= $data['last_name'];
			$color 		= $data['color'];

			$this->session->set_userdata('role', $role);
			$this->session->set_userdata('loggedUser_parentId', $data['parent_id']);
			$this->session->set_userdata('loggedUser_username', $user_username);
			$this->session->set_userdata('loggedUser_fname', $user_fname);
			$this->session->set_userdata('loggedUser_id', $user_id);
			$this->session->set_userdata('color', $color);
			$this->session->set_userdata('LastName', $user_last);
			$this->session->set_userdata('loggedUser_loggedin', true);
			return true;	
		}
		else
		{
			return false;
		}
	}
	
	public function changeAdminPassword() {
		
		$admin_id = $this->session->userdata('admin_id');
		$current_pass = md5($this->input->post('old_password'));
		$this->db->select('password');
		$this->db->where('id',$admin_id);
		$this->db->where('password',$current_pass);
		$result = $this->db->get('admin');
		
		if( $result->num_rows() > 0 ) {
			$res = $result->row_array();
			$data = array(
				'password' => md5($this->input->post('new_password')),
			);
			
			$this->db->where('id',$admin_id);
			$final_res = $this->db->update('admin',$data);
			if( $final_res ) {
				return true;
			} else {
				return false;
			}
		}
		else {
			return false;
		}		
	}

	public function get_vehicles_state($userId) {
		$data = $this->db->select('state')
		->from('vehicles')
		->where('user_id',$userId)
		->group_by('state')
		->get()->result_array();
		
		$states = implode(',',array_column($data,'state'));
		return $states;
	}

	public function user_Profile($userId) {
		$data = $this->db->select('profile_image')
		->from('users')
		->where('id',$userId)
		->get()->row_array();
		return $data;
	}

	public function getMiles($orign,$destination){
		
		$data = $this->db->select('miles')->from('address_distance')
		->where('origin',str_replace(' ','~',$orign))
		->where('destination',str_replace(' ','~',$destination))
		->get()->result_array();

		return @$data['0']['miles'];
		// echo $this->db->last_query();
	}
	
	public function getTimeMiles($orign,$destination){
		
		$data = $this->db->select('miles,estimated_time')->from('address_distance')
		->where('origin',str_replace(' ','~',$orign))
		->where('destination',str_replace(' ','~',$destination))
		->get()->row_array();
		
		return @$data;
		
	}

	public function saveData($dataArray){
		
		$data = $this->db->select('*')->from('address_distance')
		->where('origin',str_replace(' ','~',$dataArray['origin']))
		->where('destination',str_replace(' ','~',$dataArray['destination']))->get();
		
		if($data->num_rows() < 1){

			$data = $this->db->insert('address_distance',$dataArray);
			//echo $this->db->last_query();
		}
	}
	
	public function saveRemainingHours( $userId = null, $vehicleId = null , $hour = 0 ) {
		$this->db->select('id');
		$condition = array('user_id' => $userId, 'vehicle_id' => $vehicleId );
		$this->db->where($condition);
		$result = $this->db->get('save_previous_hours');
		
		if ( $result->num_rows() > 0 ) {
			$idArray = $result->row_array();
			$data = array(
               'hours' => $hour,
            );
			$this->db->where('id', $idArray['id']);
			$this->db->update('save_previous_hours', $data); 
		} else {
			$data = array(
				'user_id' => $userId,
				'vehicle_id' => $vehicleId,
				'hours' => 0
			);
			$this->db->insert('save_previous_hours',$data);
		}
		return true;
	}
	
	public function getHoursRemaining( $userId = null , $vehicleId = null ) {
		$condition = array('user_id' => $userId, 'vehicle_id' => $vehicleId );
		$this->db->where($condition);
		return $this->db->get('save_previous_hours')->row()->hours;
	}
	
	public function search_links( $search = '' ) {
		$this->db->select('*');
		$this->db->like('name', $search);
		$result = $this->db->get('nav_links');
		if ( $result-> num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return false;
		}
		
	}
	
	/** 
	 * finding language file depending upon state
	 */
	  
	public function getLangFileName( $stateName = '' ) {
		$this->db->select('lang_file');
		$this->db->where('state_name',$stateName);
		return $this->db->get('languages_files	')->row()->lang_file;
	}
	
	/**
	 * Getting table single record for csv file
	 */
	 
	public function getTableRecord( $tableName = '', $primaryId = null ) {
		return $this->db->select('*')->where('id',$primaryId)->get($tableName)->row_array();
	} 

	public function getMapKey(){
		return $this->db->select("key")->where(array("id"=>1,"valid"=>1))->get("keys")->row_array()["key"];
	}

	public function updateGoogleKey($key){
		$this->db->where(array("id"=>1,"valid"=>1));
		$this->db->update("keys",array("key"=>$key));
	}

	/**
	* Fetching list of logged dispatchers childrens
	*/

	public function fetchDispatchersChilds( $userId = null ) {
		$this->db->select('id');
		$this->db->where('users.childs_parent',$userId);
		$result = $this->db->get('users');
		if( $result->num_rows() > 0 )
			return $result->result_array();
		else
			return array();
	}

	public function getUsers() {
		
		$column = 'users.id,username,CONCAT(first_name, ,last_name) as fullName,email,users.status,role_id';

		$this->db->join("roles", "users.role_id = roles.id","Left");
		$this->db->select($column)->where('users.status',1);
		
		if(!$this->superAdmin){
			$this->db->where('users.parent_id',$this->userID);
		}

		$data = $this->db->get('users')
			 ->result_array();
		return $data;
	}


	public function getUsersById( $userId = null ) {
		
		$column = 'users.id,username,CONCAT(first_name, ,last_name) as fullName,email,users.status,role_id';
		if(!empty($userId)){

			$column .= ',phone,first_name,last_name,city,address,zipcode,roles.name as rname';
			$this->db->where(['users.id'=>$userId]);
			$this->db->join("roles", "users.role_id = roles.id","Left");
		}

		$this->db->select($column)
			 ->where('users.status',1);
		$data =  $this->db->get('users')
			 ->result_array();
			 
		// echo $this->db->last_query();
		return $data;
	}

	public function checkUnique(){
		
		$postData = json_decode(file_get_contents('php://input'));
		$this->db->select('count(id) as count')->like('username',$postData->data->username);
		if(!empty($postData->data->id)){
			$this->db->where('id!=',$postData->data->id);
		}

		$userExists =  $this->db->get('users')->result_array();
		return $userExists[0]['count'];
	}	

	public function addEditUser($data,$userID=null){
		
		if(!empty($userID)){
			return $this->db->where('id',$userID)->update('users',$data);
		}
		return $this->db->insert('users',$data);
	}

	public function changeStatus(){

		$postData 			= json_decode(file_get_contents('php://input'));
		$status 			= ($postData->data->status==1)?'0':'1';
		$this->db->where('id',$postData->data->userID)->update('users',['status'=>$status]);
	}
}

?>
