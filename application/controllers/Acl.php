<?php

class Acl extends Admin_Controller {

	private $data;
	private $className;
	private $skippedControllers = ['Login','Acl','Cron','Test','Welcome','Index','.','Autoloads','Scrap','Triumph','.goutputstream-SOWCVY','index','States','Billingtest','Utilities','billingbackup15','Scripts'];
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
		// ex($this->actions);

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
				if($method->name !='__construct' && $method->name !='get_instance' && strpos($method->name,'skipAcl') === false ){
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

	/**
	* Get list of controllers to assign permissions
	*/

	public function getController($id = null){
		
		/*$this->data = $this->Acl->getController();
		ex($this->data);*/

		$this->data = $this->Acl->allAction();
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
		$checkAll = 0;
		$this->data = $this->Acl->getController($organisationID);
		if( !empty($this->data)) {
			$statusData = array_column($this->data, 'status');
			if( !in_array(0,$statusData)) {
				$checkAll = 1;
			}
		}
		
		$companyName = $this->Acl->getColumnsList('users',['CONCAT(first_name, ,last_name) as name'],['id'=>$organisationID]);

		echo json_encode(['success'=>true,'data'=>$this->data,'company'=>$companyName[0]['name'], 'checkAll' => $checkAll]);
	}

	public function getActions($id = null){
		$this->data = $this->Acl->getActions($id);
		echo json_encode(['success'=>true,'data'=>$this->data]);
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