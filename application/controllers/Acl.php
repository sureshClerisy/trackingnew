<?php

class Acl extends Admin_Controller {

	private $data;
	private $className;
	private $skippedControllers = ['Login','Acl','Cron','Test','Welcome','Index','.','Autoloads','Scrap','Triumph','.goutputstream-SOWCVY','index','States','Billingtest',];
	private $actions 			= [];
	private $fileName;
	private $parentID;
	
	public function __construct(){	

		parent::__construct();
		$this->load->model('AclModel','Acl');
		
	}

	/**
	* Request URL: http://domain.com/organisations/index
	* Method : get
	* @param : orgId
	* @return : list of organisation
	* Comment: Used for fetching list of organisations
	*/

	public function organisations($orgID = null){
		$this->data = $this->Acl->getOrganisations($orgID);
		echo json_encode(['success'=>true,'data'=>$this->data]);
	}

	/**
	* Request URL: http://domain.com/organisation/edit
	* Method : get
	* @param : orgId
	* @return : organisation detail
	* Comment: Used for fetching details of organisations for edit
	*/

	public function editOrganisation($orgID = null){
		$this->data = $this->Acl->getOrganisations($orgID);
		echo json_encode(['success'=>true,'data'=>$this->data]);
	}


	/**
	* Request URL: http://domain.com/organisation/add or update
	* Method : get
	* @param : null
	* @return : list of organisation
	* Comment: Used for adding or updating the organisations
	*/

	public function AddOrganizations(){
		$this->data = $this->Acl->AddEditOrganisations();
		echo json_encode(['success'=>true,'data'=>$this->data]);
	}


	public function getPublicMethods(){
		
		$handle = opendir(APPPATH . 'controllers');
		while ($entry = readdir($handle)) {
			if($this->isValidClass($entry)){
				$this->getAction();
			}
		}

		$this->UpdateAclActions();
	}

	public function UpdateAclActions(){
		$this->Acl->syncControllers($this->actions);
	}

	protected function getAction(){
		
		if(!empty($this->className)){
			require_once APPPATH . 'controllers/'.$this->fileName;
			
			$reflectorObj 	= new ReflectionClass($this->className);
			$methodNames 	= [];

			foreach ($reflectorObj->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
				if($method->name !='__construct' && $method->name !='get_instance'){
					$this->actions[$this->className][] = $method->name;
				}
			}
		}

		return $this->actions;
	}

	private function isValidClass($fileName){

		$fileArray = explode('.',$fileName);

		if(!in_array($fileArray[0], $this->skippedControllers)){
			$this->className 	= $this->getClassName($fileArray[0]);
			$this->fileName 	= $fileName;
			return true;
		}
	}

	private function getClassName($className){
		$fileArray = explode('.',$className);
		return $this->className = ucfirst($fileArray['0']);
	}

	public function getController($id = null){
		
		
		/*$this->data = $this->Acl->getController();
		ex($this->data);*/

		$this->data = $this->Acl->allAction();

		
		ex($this->data);

		echo json_encode(['success'=>true,'data'=>$this->data]);
	}

	/**
	* @link : http://site_url/acl/get_module/ID
	* @param : Organisation ID
	* @return : JSON
	* @Description : Get all module name
	*
	*/
	public function get_module( $organisationID = null ){

		$this->data = $this->Acl->getController($organisationID);
		
		$companyName = $this->Acl->getColumnsList('users',['CONCAT(first_name, ,last_name) as name'],['id'=>$organisationID]);

		echo json_encode(['success'=>true,'data'=>$this->data,'company'=>$companyName[0]['name']]);
	}

	public function getActions($id = null){
		$this->data = $this->Acl->getActions($id);
		echo json_encode(['success'=>true,'data'=>$this->data]);
	}

	/**
	* Request URL: http://domain.com/acl/getRoles
	* Method : get
	* @param : null
	* @return : list of roles
	* Comment: Used for fetching list of roles created by organisations
	*/

	public function getRoles( $userId = null ){
		$this->data = $this->Acl->getRoles($userId);
		echo json_encode(['success'=>true, 'data'=>$this->data, 'currentUserId'=>$this->userID, 'superadmin'=>$this->superAdmin]);
	}

	/**
	* Request URL: http://domain.com/acl/editRole
	* Method : get
	* @param : roleId
	* @return : role details of roles
	* Comment: Used for fetching list of roles created by organisations
	*/

	public function editRole( $roleId = null ){
		$this->data = $this->Acl->editRole($roleId);
		echo json_encode(['success'=>true, 'data'=>$this->data, 'currentUserId'=> $this->userID, 'superadmin'=>$this->superAdmin]);
	}
	
	/**
	* Request URL: http://domain.com/acl/addRoles
	* Method : get
	* @param : null
	* @return : list of roles
	* Comment: Used for fetching list of roles created by organisations
	*/

	public function addroles(){
		$objPost = json_decode(file_get_contents('php://input'),true);

		$this->Acl->addRole($objPost);
		echo json_encode(['success'=>true]);	
	}

	/**
	* Request URL: http://domain.com/acl/checkRoleNameAlreadyExist
	* Method : get
	* @param : roleId
	* @return : true or false
	* Comment: Used for checking if same name role already exist or not
	*/

	public function checkRoleNameAlreadyExist($roleId = null){
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
	

	public function removeroles(){
		$this->Acl->removeRole();
		echo json_encode(['success'=>true]);
	}

	/**
	* @Method : assign_module
	* @link   : http://site_url/acl/assign_permission
	* @param  : Module ID
	* @return : Boolean(JSON)
	* @Description : Assign/revoke permission
	*
	*/
	public function assign_module(){
		$this->data = $this->Acl->assign_module();		
		echo json_encode(['success'=>true,'data'=>$this->data]);
	}
}