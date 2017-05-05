<?php

class User extends Parent_Model
{
	function __construct()
	{
		parent::__construct();
		// $this->load->library('email');
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
			$this->session->set_userdata('dispCord_parent', $data['dispatcher_parent']);
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

	/**
	* get list of users for organisation
	*/

	public function getUsers( $userId = null) {
		$column = 'users.id,username,CONCAT(first_name," ",last_name) as fullName,email,users.status, phone,role_id, roles.name as roleName';
		$this->db->join("roles", "users.role_id = roles.id","Left");
		$this->db->select($column);		
		$this->db->where(array('users.parent_id' => $userId, 'users.deleted' => 0));

		$data = $this->db->get('users');
		if ( $data->num_rows() > 0 )
			return $data->result_array();
		else 
			return array();
	}

	/**
	* get user detail organisation
	*/

	public function getUserDetail( $userId = null) {
		$column = 'users.id,username,CONCAT(first_name," ",last_name) as fullName,email,users.status, phone,role_id, roles.name as roleName';
		$this->db->join("roles", "users.role_id = roles.id","Left");
		$this->db->select($column);
		$this->db->where(array('users.id' => $userId, 'users.deleted' => 0));

		$data = $this->db->get('users');
		if ( $data->num_rows() > 0 )
			return $data->row_array();
		else 
			return array();
	}

	public function getUsersById( $userId = null ) {
		
		$column = 'users.id,username,CONCAT(first_name, ,last_name) as fullName,email,users.status,role_id';
		if(!empty($userId)){
			$column .= ',phone,first_name,last_name,city,address,zipcode,roles.name as rname,profile_image';
			$this->db->where(['users.id'=>$userId]);
			$this->db->join("roles", "users.role_id = roles.id","Left");
		}

		$this->db->select($column);
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

	public function changeStatus($status,$userId){
		$status 			= ($status ==1 ) ? '0' : '1';
		$this->db->where('id',$userId)->update('users',['status'=>$status]);
	}

	/**
	* delete user from list
	*/

	public function deleteUser($userId = null ) {
		$this->db->where('id',$userId)->update('users',['deleted' => 1]);
	}

	/**
	* get list of organisation added
	*/

	public function getOrganisationsList() {
		$this->db->select('first_name,id');
		$this->db->where('role_id',$this->setOrganisationRoleId);
		$result = $this->db->get('users');
		if($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return array();
		}
	}

	/**
	* remove selected organisation id
	*/

	public function removeSelectedOrganisation( $userId = null) {
		$data = array(
			'name' => '',
			'organisation_id' => ''
		);

		$this->db->where('user_id',$userId);
		$this->db->update('selected_organisation',$data);
		return true;
	}

	/**
	* set selected organisation id in db
	*/

	public function setSelectedOrganisation( $userId = null, $data = array()) {
		$this->db->select('id');
		$this->db->where('user_id',$userId);
		$result = $this->db->get('selected_organisation');
		if( $result->num_rows() > 0 ) {
			$primaryId = $result->row()->id;
			$this->db->where('id',$primaryId);
			$this->db->update('selected_organisation',$data);
		} else {
			$data['user_id'] = $userId;
			$this->db->insert('selected_organisation',$data);
		}
		return true;
	}

	/**
	* get list of actions allowed to user
	*/

	public function roleAllowedActions($roleId = null ) {
		$this->db->distinct('acl.id');
		$this->db->select('Lower(acl.action) as action, acl.parent_id as contName,slug');
		$this->db->join('acl_actions as acl', "acl.id = role_permissions.parent_id", 'LEFT');
		$this->db->where(array( 'permission' => 1, 'role_id' => $roleId, 'role_permissions.parent_id !=' => 0));
		$result = $this->db->get('role_permissions');
		if( $result->num_rows() > 0 ) 
			return $result->result_array();
		else
			return array();
	}

	/**
	* get list of actions allowed to user
	*/

	public function orgAllowedActions($userId = null ) {
		$result = $this->db->select('Lower(module_name) as action')
				 ->where(array( 'status' => 1, 'organization_id' => $userId))
				 ->get('organisation_module');
		
		if( $result->num_rows() > 0 ){
			return $result->result_array();
		} else {
			return array();
		}
	}

	/**
	* check if edited entity is of same organisation
	*/

	public function checkOrganisationIdValid( $entityId = null, $tableName = '' ) {
		$this->db->select('id')->where(array('id' => $entityId, 'organisation_id' => $this->selectedOrgId));
		$result = $this->db->get($tableName);
		if( $result->num_rows() > 0 ) 
			return 1;
		else
			return 0;
	}

	/**
	* generate password for forgot passwordd
	*/

	public function forgotPassword($email = '') {
		// mail('gsm.jatt@gmail.com','Testing puroposes','testt');
		// exit();
		// $config = Array(
		//   'protocol' => 'sendmail',
		//   'mailpath' => '/usr/sbin/sendmail',	
		//   // 'smtp_host' => 'ssl://smtp.googlemail.com',
		//   // 'smtp_port' => 465,
		//   // 'smtp_user' => 'gsm.jatt@gmail.com', // change it to yours
		//   // 'smtp_pass' => '', // change it to yours
		//   'mailtype' => 'html',
		//   'charset' => 'iso-8859-1',
		//   'wordwrap' => TRUE
		// );

		// $this->load->library('email',$config);

		// $this->email->from('test@gmail.com');
		// $this->email->to('gsm.jatt@gmail.com');
		// $this->email->subject('Email Test');
		// $this->email->message('Testing the email class.');

		//  if($this->email->send())
	 //     {
	 //      echo 'Email sent.';
	 //     }
	 //     else
	 //    {
	 //     show_error($this->email->print_debugger());
	 //    }
	 //    die;
		$this->db->select('id,first_name,last_name')->where('email',$email);	
		$result = $this->db->get('users');
		
		if($result->num_rows() > 0) {
			
			$token 					= $this->generatePassword();
			$data['templateData'] 	= $result->result_array();
			$data['templateData'][0]['token'] = $token;
			$this->load->library('email');

			$this->email->from('admin@vika.io');
			$this->email->to('suresh@dexteroustechnologies.co.in');
			$this->email->set_mailtype('html');
			$this->email->subject('VIKALOGISTIKS: Reset your password');
			$this->email->message($this->load->view('email/forgotPassword.php', $data, TRUE));
			$this->email->send();
			$this->db->where('id',$data['templateData'][0]['id'])->update('users',['token'=>$token]);//setting token for reset password.
			return 'valid';
		} else {
			die('Error: Something went wrong.');
			return 'emailNotExist';
		}
	}

	/**
	* Generating random password
	*/

	public function generatePassword() {
		$characters = '!@#$(^&abcdefghijklmnopqrstuvwxyz0123456789';
		$string = '';
		$max = strlen($characters) - 1;
		for ($i = 0; $i < 13; $i++) {
		   $string .= $characters[mt_rand(0, $max)];
		}
		return $string;
	}

	public function resetPassword($postData = null) {

		$data = $this->db->select('id')->where('token',$postData['token'])->get('users')->result_array();
		
		if(!empty($data[0]['id'])){

			$this->db->where('token',$postData['token'])->update('users',['password'=>md5($postData['password']),'token'=>'']);
			return true;	
		}
		return false;
		//2f926e2d6073c5f6d082f1562d288ab4
	}
	
}