<?php

class Acl extends Admin_Controller {

	private $data;
	private $className;
	private $skippedControllers = ['Login','Acl','Cron','Garages','Test','Welcome','Index','.','Autoloads','Scrap','Scraptest','Templates','Triumph','billingbackup15','.goutputstream-SOWCVY','index','States'];
	private $actions 			= [];
	private $fileName;
	private $parentID;
	

	public function __construct(){	

		parent::__construct();
		$this->load->model('AclModel','Acl');
		
	}

	public function organisations(){
		$this->data = $this->Acl->getOrganisations();
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
			$methodNames 	=[];

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
		$this->data = $this->Acl->getController();
		echo json_encode(['success'=>true,'data'=>$this->data]);
	}

	public function getActions($id = null){
		$this->data = $this->Acl->getActions();
		echo json_encode(['success'=>true,'data'=>$this->data]);
	}

	public function getRoles($roleId=null){

		$this->data = $this->Acl->getRoles($roleId);
		echo json_encode(['success'=>true,'data'=>$this->data,'currentUserId'=>$this->userID,'superadmin'=>$this->superAdmin]);
	}

	public function addroles(){
		$objPost = json_decode(file_get_contents('php://input'),true);
		$this->Acl->addRole($objPost);
		echo json_encode(['success'=>true]);	
	}
}