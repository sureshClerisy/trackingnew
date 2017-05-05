
<?php

class Roles extends Admin_Controller {

	private $data;
	private $parentID;
	
	public function __construct(){	

		parent::__construct();
		$this->load->model('AclModel','Acl');
		
		// echo $this->userID;
	}

	/**
	* Request URL: http://domain.com/roles/roles_listing
	* Method : get
	* @param : null
	* @return : list of roles
	* Comment: Used for fetching list of roles created by organisations
	*/

	public function roles_listing( $userId = null ){
		if(!empty($this->superAdmin))
			$userId = false;

		$this->data = $this->Acl->getRoles($userId);
		echo json_encode(['success'=>true, 'data'=>$this->data, 'currentUserId'=>$this->userID, 'superadmin'=>$this->superAdmin]);
	}

	/**
	* Request URL: http://domain.com/roles/edit_role
	* Method : get
	* @param : roleId
	* @return : role details of roles
	* Comment: Used for editing role created by organisations
	*/

	public function edit_role( $roleId = null ){
		$this->data = $this->Acl->editRole($roleId);
		echo json_encode(['success'=>true, 'data'=>$this->data, 'currentUserId'=> $this->userID, 'superadmin'=>$this->superAdmin]);
	}
	
	/**
	* Request URL: http://domain.com/acl/add_role
	* Method : get
	* @param : null
	* @return : list of roles
	* Comment: Used for adding new role
	*/

	public function add_role(){
		$objPost = json_decode(file_get_contents('php://input'),true);

		$this->Acl->addRole($objPost);
		echo json_encode(['success'=>true]);	
	}

	/**
	* Request URL: http://domain.com/roles/checkRoleNameAlreadyExist
	* Method : get
	* @param : roleId
	* @return : true or false
	* Comment: Used for checking if same name role already exist or not
	*/

	public function skipAcl_checkRoleNameAlreadyExist($roleId = null){
		$objPost = json_decode(file_get_contents('php://input'),true);

		$res = $this->Acl->checkRoleNameExist($roleId, $this->userID, $objPost['roleName']);
		$errMsg = '';
		if ( $res == 0 ) {
			$status = 'success';
		} else {
			$status = 'failure';
		}
		echo json_encode(['status' => $status]);	
	}

	/**
	* Request URL: http://domain.com/roles/manage_roles
	* Method : get
	* @param : roleId
	* @return : true or false
	* Comment: Used for managing permissions for roles
	*/

	public function manage_roles($roleId = null){

		$this->data['roleName'] = $this->Acl->getRoleName($roleId);
		$this->data['roles'] = $this->Acl->getRolesPermissionsData($roleId);

		// ex($this->data['roles']);

		$this->data['roleId'] = $roleId;

		echo json_encode(['success'=>true,'data'=> $this->data]);
	}
	
	/**
	* Request URL: http://domain.com/roles/changing_permissions
	* Method : get
	* @param : roleId
	* @return : true or false
	* Comment: Used for changing permissions for roles
	*/

	public function skipAcl_change_permission($type = '', $roleId = null, $value = false){
		
		$value 		= ( !$value) ? 0 : 1;
		$objPost 	= json_decode(file_get_contents('php://input'),true);

		if ( $type == 'parent') {
			$this->Acl->changePermission($roleId,$objPost['children'][0]['parent_id'],0,$value);
			if ( !empty($objPost) ) {
				foreach($objPost['children'] as $obj) {
				
					$this->Acl->changePermission($roleId,$obj['id'],$obj['parent_id'],$value);
				}
			}
		} else {
			$this->Acl->changePermission($roleId,$objPost['childId'],$objPost['parent_id'],$value);
		}
		echo json_encode(['success'=>true]);
	}
}