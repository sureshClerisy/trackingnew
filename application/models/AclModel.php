<?php

/**
* 
*/
class AclModel extends Parent_Model {
	
	private $controller;
	private $action;
	private $controllerID;
	private $dateTime;
	
	function __construct() {
		parent::__construct();
		$this->dateTime = date('Y-m-d H:m:s');
	}

	public function getOrganisations( $orgID = null ){
		
		$columns = 'id,username,CONCAT(first_name, ,last_name) as name,zipcode,address,city,state';
		
		if(!empty($orgID)){
			$columns 	 = 'id,username,first_name,profile_image,email,phone,city,state,zipcode,address';
			$this->db->where(['id'=>$orgID]);
		}

		$this->db->select($columns);
		$this->db->where('role_id', $this->organisationRoleId);
		return $this->db->get('users')->result_array();
	}

	/**
	* add or edit organisation
	*/

	public function AddEditOrganisations(){
		
		$postData 			= json_decode(file_get_contents('php://input'));
		$data 				= (array)$postData->data;

		if(!empty($data['password'])){
			$data['password'] 	= md5($data['password']);
		}
		
		$data['role_id'] 	= $this->organisationRoleId;
		$data['parent_id'] 	= $this->userID;
		$data['created'] 	= date('Y-m-d h:m:s');

		$orgId = $this->getOrganisationId();
		if( $orgId) {
			if ( $data['id'] == $orgId ) {
				$temp['name'] = $data['first_name'];
				$this->db->where('user_id',$this->userID);
				$this->db->update('selected_organisation',$temp);
			}
		}	

		if(!empty($data['id'])){
			$this->db->where('id',$data['id']);
			return $this->db->update('users',$data);
		}
		$this->db->insert('users',$data);

		return true;
	}

	/**
	* get Organisation id of logged superadmin
	*/

	public function getOrganisationId() {
		// $this->db->select('organisation_id');
		$this->db->where('user_id', $this->userID);
		$result = $this->db->get('selected_organisation');
		if ( $result->num_rows() > 0 )
			return $result->row()->organisation_id;
		else 
			return false;
	}

	public function syncControllers($data = null){
		
		foreach ($data as $controller => $actions){
			
			$this->controller 	= $controller;
			$this->controllerID = $this->insertController();
			foreach ($actions as $key => $action) {
				$this->action = $action;
				$this->insertAction();
			}
		}
	}

	public function insertController(){
		
		$dataRow 	= $this->db->select('id')
							->where('action',$this->controller)
							->get('acl_actions')
							->result_array();
		
		if(empty($dataRow)){
			
			$data = [
				'action'=>$this->controller,
				'created'=>"{$this->dateTime}",
				'updated'=>"{$this->dateTime}"
				];

			$this->db->insert('acl_actions',$data);
			return $last_id = $this->db->insert_id();
		}
		return $dataRow[0]['id'];
	}

	public function insertAction(){

		$dataRow 	= $this->db->select('id')
							->where(['action'=>$this->action,'parent_id'=>$this->controllerID])
							->get('acl_actions')
							->result_array();
		if(empty($dataRow)){
			
			$data = [
				'parent_id'=>$this->controllerID,
				'action'=>$this->action,
				'created'=>"{$this->dateTime}",
				'updated'=>"{$this->dateTime}"
				];
			$this->db->insert('acl_actions',$data);
		}
	}

	public function getActions( $controllerID = null ){
		if(!empty($controllerID)){
			$this->db->where('parent_id',$controllerID);
		}
		return $this->db->select('*')->get('acl_actions')->result_array();
	}

	public function getRoles($userId = null){

		$this->db->select('roles.*,users.username')
				 ->join("users", "users.id = roles.user_id");
		
		if(!empty($userId)){
			$this->db->where('roles.user_id',$userId);
		}


		if(empty($this->superAdmin)){
			// $this->db->or_where('roles.id',$this->role);
		}
	
		$data = $this->db->get('roles')->result_array();
		return $data;
	}	

	public function editRole( $roleId = null) {
		$this->db->select('roles.*,users.username')
				 ->join("users", "users.id = roles.user_id");
		$this->db->where('roles.id',$roleId);
		
		if(empty($this->superAdmin)){
			$this->db->where('roles.user_id',$this->userID);
			$this->db->or_where('roles.id',$this->role);
		}

		$data = $this->db->get('roles')->row_array();
		return $data;


	}

	public function getController( $organisationID = NULL ){

		$this->db->select('acl_actions.id,acl_actions.action,org.status,org.organization_id')->where('parent_id IS NULL');
		$this->db->join('organisation_module org','acl_actions.id = org.module_id AND org.organization_id = '.$organisationID,'LEFT');
		
		$data = $this->db->get('acl_actions')
				->result_array();

		return $data;
	}
	
	/**
	* add or edit role
	*/

	public function addRole($data){
		
		$dateTime 	= date('Y-m-d h:m:s');
		if ( $data['data']['id'] != null && $data['data']['id'] != '' ) {
			$filterData = array(
				'name'=>$data['data']['name'],
				'alias'=>$data['data']['alias'],
				'updated'=>$dateTime
			);
		} else {
			$filterData = array(
				'name'=>$data['data']['name'],
				'alias'=>$data['data']['alias'],
				'user_id'=>$this->session->loggedUser_id,
				'created'=>$dateTime,
				'updated'=>$dateTime
			);
		}
		if(!empty($data['data']['id'])){
			$this->db->where('id',$data['data']['id']);
			return $this->db->update('roles',$filterData);
		}
		return $this->db->insert('roles',$filterData);
	}

	/**
	* deleting the role from db
	*/

	public function removeRole(){

		$objPost = json_decode(file_get_contents('php://input'),true);
		$this->db->where('id',$objPost['data'])->delete('roles');
	}

	/**
	* checking if same name role already exist
	*/

	public function checkRoleNameExist($roleId = null, $userId = null, $roleName = '') {
		
		$this->db->select('id');
		if ( !empty($roleId) ) {
			$this->db->where('id !=',$roleId);
		} 
		$this->db->where(array('user_id' => $userId, 'name' => $roleName));
		$result = $this->db->get('roles');
		if ( $result->num_rows() > 0 ){
			return 1;
		} else {
			return 0;
		}
	}

	public function allAction(){

		$data 		= $this->db->select('id,parent_id,action')->get('acl_actions')->result_array();
		$actions 	= [];
		$parentid 	= '';
		
		foreach ($data as $key => $action) {

			if($action['parent_id']==''){
				$parentid = $action['id'].'-'.$action['action'];
			}else{
				$actions[$parentid][] = $data[$key]; 
			}
		}
		// pr($actions);

		return $actions;
	}

	public function assign_module(){
		$postData = json_decode(file_get_contents('php://input'));
		
		// $status = (!empty($postData->status))?0:1;
		$data = ['module_id'=>$postData->moduleID,'status'=>$postData->status,'organization_id'=>$postData->org_id];

		$dataRow = $this->db->select('id')
							->where(['organization_id'=>$postData->org_id,'module_id'=>$postData->moduleID])
							->get('organisation_module')->result_array();
		if(!empty($dataRow)){
		
			$this->db->where('id',$dataRow[0]['id']);
			$this->db->update('organisation_module',$data);
		}else{
			$this->db->insert('organisation_module',$data);
		}
		echo $this->db->last_query();

		return $this->getController($postData->org_id);
	}
}