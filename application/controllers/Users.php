<?php
/**
* 
*/
class Users extends Admin_Controller {
	
	private $data;

	function __construct() { 
		parent::__construct();
		$this->load->model(['User','AclModel']);
	}

	/**
	* @Method Index
	* @param NULL
	* @return JSON
	* @Description: Used to Get All Users
	*/

	public function index($userID=null){
		
		$this->data = $this->User->getUsers();
		echo json_encode(['success'=>true,'roles'=>[],'data'=>$this->data]);
	}	

	public function getAddEdit($userID=null){
		
		$this->data = $this->User->getUsersById($userID);

		$roleList 	= $this->AclModel->getRoles();
		echo json_encode(['success'=>true,'roles'=>$roleList,'data'=>$this->data]);
	}


	/**
	* @Method addEditUser
	* @param ID,NULL
	* @return Boolean
	* @Description: Used to add or update user
	*/

	public function postAddEdit(){

		$postData 			= json_decode(file_get_contents('php://input'));
		
		pr($_FILES);

		ex($postData);


		if(!empty($postData)){
			$data 				= (array)$postData->data;
			$id 				= (!empty($data['id']))?$data['id']:NULL;
			$data['role_id'] 	= (!empty($data['role_id']->id))?$data['role_id']->id:$data['role_id'];
			$data['parent_id'] 	= $this->session->loggedUser_id;
			
			if(!empty($data['password']) || !empty($data['newpassword'])){

				$password = @($data['password'])?$data['password']:$data['newpassword'];
				// @
				$data['password'] 	= md5($password);

			}

			$data['created'] 	= date('Y-m-d h:m:s');

			unset($data['selectedRole']);
			unset($data['fullName']);
			unset($data['rname']);
			unset($data['newpassword']);
			$this->User->addEditUser($data,$id);
			echo json_encode(['success'=>true]);
		}
	}

	/**
	* @Method checkUnique
	* @param NULL
	* @return Boolean
	* @Description: Check User exists or not
	*/

	public function checkUnique(){
		echo json_encode(['success'=>true,'user'=>$this->User->checkUnique()]);
	}

	public function changeStatus(){
		echo json_encode(['success'=>true,'status'=>$this->User->changeStatus()]);
	}
}