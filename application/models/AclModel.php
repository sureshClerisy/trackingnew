<?php

/**
* 
*/
class AclModel extends Parent_Model {
	
	private $controller;
	private $action;
	private $controllerID;
	private $dateTime;
	private $assignedModules = [];

	
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
		$this->db->where('role_id', $this->setOrganisationRoleId);
		return $this->db->get('users')->result_array();
	}

	/**
	* add or edit organisation
	*/

	public function AddEditOrganisations(){
		$file_name = '';		
		$array = $this->input->post('posted_data');
		$data = json_decode($array, true);

		if(!empty($_FILES) && $_FILES['profile_image']['name'] != ''){
			$str = $_FILES['profile_image']['name'];
			$ext =  substr($str, strrpos($str, '.') + 1);
			$config['file_name'] = $file_name = date('Ymdhis').'.'.$ext;
			$config['upload_path'] = './assets/uploads/users/';
			$config['allowed_types'] = 'gif|jpg|png|jpeg';
			$this->load->library('upload', $config);

			if($this->upload->do_upload('profile_image')) {
				$config['image_library'] = 'gd2';
				$config['source_image'] = 'assets/uploads/users/'.$file_name;	
				$config['new_image'] = './assets/uploads/users/thumbnail/';
					//~ $config['create_thumb'] = TRUE;
				$config['maintain_ratio'] = TRUE;
				$config['width']         = 200;
				$config['height']       = 200;	
				$this->load->library('image_lib',$config);
				$this->image_lib->resize();
			} else {
				$fileError = $this->upload->display_errors();	
				if ( !empty($fileError)) {
					echo json_encode(['imageUploadError' => 1, 'error' => $fileError]);
					exit();
				}
			}

			$file_path = 'assets/uploads/users/'.$file_name;
			$file_path_thumb = 'assets/uploads/users/thumbnail/'.$file_name;
			if(file_exists($file_path)) { chmod($file_path,0777); }
			if(file_exists($file_path_thumb)){ chmod($file_path_thumb,0777); }

			if ( isset($data['profile_image']) && !empty($data['profile_image']) ) {
				$file_path = 'assets/uploads/users/'.$data['profile_image'];
				$file_path_thumb = 'assets/uploads/users/thumbnail/'.$data['profile_image'];
				if(file_exists($file_path)) { unlink($file_path); }
				if(file_exists($file_path_thumb)){ unlink($file_path_thumb); }
			}
		}

		if(!empty($data['password'])){
			$data['password'] 	= md5($data['password']);
		}

		if(!empty($file_name)){
			$data['profile_image'] = $file_name;
		}
		
		$data['role_id'] 	= $this->setOrganisationRoleId;
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
		} else {
			$this->db->insert('users',$data);
			$lastId = $this->db->insert_id();
			$allowedId = $this->allowIntialAccess();
			$data = array(
				'organization_id' => $lastId,
				'status' => 1,
				'module_id' => $allowedId,
				'module_name' => 'dashboard',
				'slug' => NULL
			);
			$this->db->insert('organisation_module',$data);
			return true;
		}
	}

	/**
	* setting default permission whern organisations is created
	*/

	public function allowIntialAccess() {
		$this->db->select('id')->where('parent_id',NULL);
		$this->db->like("LOWER(action)", 'dashboard');
		$result = $this->db->get('acl_actions');
		if( $result->num_rows() > 0 ) 
			return $result->row()->id;
		else
			return false;
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
		// ex($data);

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
				'slug'  => '',
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
				'parent_id'	=> $this->controllerID,
				'action'	=> $this->action,
				'slug'		=> strtolower($this->controller).'/'.strtolower($this->action),
				'created'	=> "{$this->dateTime}",
				'updated'	=> "{$this->dateTime}"
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
		$this->db->where(array('roles.id !=' => $this->setOrganisationRoleId, 'roles.user_id' => $this->selectedOrgId));		
		$data = $this->db->get('roles')->result_array();
		// echo $this->db->last_query();
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

		$this->db->select('acl_actions.id,acl_actions.action,acl_actions.name,acl_actions.description,org.status,org.organization_id')->where('parent_id IS NULL');
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
		if ( isset($data['data']['id']) && $data['data']['id'] != null && $data['data']['id'] != '' ) {
			$filterData = array(
				'name'=>$data['data']['name'],
				'alias'=>$data['data']['alias'],
				'updated'=>$dateTime
			);
		} else {
			$filterData = array(
				'name'=>$data['data']['name'],
				'alias'=>$data['data']['alias'],
				'user_id' => $this->selectedOrgId,
				'created' => $dateTime,
				'updated' => $dateTime
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

	/**
	* getting role Name
	*/

	public function getRoleName( $roleId = null ) {
		return $this->db->where('id',$roleId)->get('roles')->row()->name;
	}


	/**
	* changing user permissions
	*/

	public function changePermission( $roleId, $aco_id, $parentId, $status) {
		$data['role_id'] = $roleId;
		$data['aco_id'] = $aco_id;
		$data['parent_id'] = $parentId;
		$data['permission'] = $status;

		$this->db->select('id')->where(array('role_id' => $roleId, 'aco_id' => $aco_id));
		$res = $this->db->get('role_permissions');
		if( $res->num_rows() > 0 ) {
			$primaryId = $res->row()->id;
			$this->db->where('id',$primaryId);
			$this->db->update('role_permissions',$data);
		} else {
			$this->db->insert('role_permissions',$data);
		}
	}

	/**
	* fetch permissions given to role
	*
	*/	
	public function getAssignedModules() {


		$data = $this->db->select('GROUP_CONCAT(module_id) as module')->where(['organisation_module.status'=>1,'organization_id'=>$this->userID])->get('organisation_module')->result_array();

		if(!empty($data[0]['module'])){
			$this->assignedModules = explode(',', $data[0]['module']);
		}
	}


	/**
	* fetch permissions given to role
	*/

	public function getRolesPermissionsData($roleId = null) {
		$columns = ['id','action','parent_id','name','description'];

		if(empty($this->superAdmin)){
			$columns[] = 'parent_id';
			$this->getAssignedModules();
			$this->db->where_in('acl_actions.parent_id',$this->assignedModules)
			->or_where_in('id',$this->assignedModules);
		}
		
		$this->db->select($columns);
		$data 		=  $this->db->get('acl_actions')->result_array();
		$actions 	= [];
		$parentid 	= '';		
		$res 		= [];

		$this->db->select('aco_id');
		$this->db->where(array('role_id' => $roleId, 'permission' => 1 ));
		$result = $this->db->get('role_permissions');

		if ( $result->num_rows() > 0 ) {
			$res = $result->result_array();
			$res = array_column($res, 'aco_id');
		} 
		
		$tempArray = [];

		foreach ($data as $key => $action) {
			
			$selected     = 0;

			if($action['parent_id']==''){

				$parentid 	= $action['id'].'~'.$action['name'].'~'.$action['description'];
				$tempArray 	= [];
			}else{
				
				if (in_array($data[$key]['id'], $res)) {
					 $selected = 1;
				}

				$actions[$parentid]['children'][] 	= array_merge($data[$key],['selected'=>$selected]);  
				
				$tempArray[] = $selected;//Collecting all children's status for parent checkbox(Make it checked or unchecked)
				$actions[$parentid]['checkAll'] 	= (in_array(0, $tempArray))?'No':'Yes';  
			}
		}
		
		return $actions;
	}

	
	public function assign_module(){
		$postData = json_decode(file_get_contents('php://input'));
		if( isset($postData->type) && $postData->type == 'all') {
			foreach($postData->moduleName as $module) {
				$data = ['module_name'=>$module->action,'module_id'=>$module->id,'status'=>$postData->status,'organization_id' =>$postData->org_id];
				$dataRow = $this->db->select('id')->where(['organization_id'=>$postData->org_id,'module_id'=>$module->id])->get('organisation_module')->result_array();
				if(!empty($dataRow)){			
					$this->db->where('id',$dataRow[0]['id']);
					$this->db->update('organisation_module',$data);
				} else {
					$this->db->insert('organisation_module',$data);
				}
			}
			return true;
		} else  {
			$data = ['module_name'=>$postData->moduleName,'module_id'=>$postData->moduleID,'status'=>$postData->status,'organization_id'=>$postData->org_id];
			$dataRow = $this->db->select('id')->where(['organization_id'=>$postData->org_id,'module_id'=>$postData->moduleID])->get('organisation_module')->result_array();
			if(!empty($dataRow)){			
				$this->db->where('id',$dataRow[0]['id']);
				$this->db->update('organisation_module',$data);
			} else {
				$this->db->insert('organisation_module',$data);
			}
			return $this->getController($postData->org_id);
		}
		
	}

	/**
	* checking organisation permission is allowed or not
	*/

	public function checkOrgPermission( $controllerName = '' ) {
		$this->db->select('status')->where(array('status' => 1, 'organization_id' => $this->userID ));
		 $this->db->like("LOWER(module_name)", $controllerName);
		$res = $this->db->get('organisation_module');
		if ($res->num_rows() > 0 ) {
			return 1;
		} else {
			return 0;
		}
	}

	/**
	* checking organisation permission is allowed or not
	*/

	public function checkRolesPermission( $controllerName = '', $actionName ) {
		$value = $controllerName.'/'.$actionName;
		$this->db->select('id');
		$this->db->Where("LOWER(acl_actions.slug)", $value);
		$res = $this->db->get('acl_actions');
		if ($res->num_rows() > 0 ) {
			$acoId = $res->row()->id;

			$this->db->select('permission');			
			// $this->db->join('acl_actions', "acl_actions.parent_id = role_permissions.parent_id", 'LEFT');
			// $this->db->where("LOWER(acl_actions.action)", $actionName);
			$this->db->where(array('permission' => 1, 'role_id' => $this->globalRoleId, 'role_permissions.aco_id' => $acoId ));
			$result = $this->db->get('role_permissions');
			if ($result->num_rows() > 0 ) {
				return 1;
			} else {
				return 0;
			}
		} else {
			return 0;
		}

		// $this->db->select('id')->where('parent_id',NULL);
		// $this->db->like("LOWER(acl_actions.action)", $controllerName);
		// $res = $this->db->get('acl_actions');
		// if ($res->num_rows() > 0 ) {
		// 	$parent_id = $res->row()->id;		
		// 	$this->db->select('permission');			
		// 	$this->db->join('acl_actions', "acl_actions.parent_id = role_permissions.parent_id", 'LEFT');
		// 	$this->db->where("LOWER(acl_actions.action)", $actionName);
		// 	$this->db->where(array('permission' => 1, 'role_id' => $this->globalRoleId, 'role_permissions.parent_id' => $parent_id ));
		// 	$result = $this->db->get('role_permissions');
		// 	if ($result->num_rows() > 0 ) {
		// 		return 1;
		// 	} else {
		// 		return 0;
		// 	}
		// } else {
		// 	return 0;
		// }

	}
	
// 	public function updateTable( $data = null ){

// 		foreach ($data as $key => $value) {
// 			pr($value);	
// 		}

// 		// $this->update
// 	}

}