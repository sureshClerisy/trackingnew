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

	public function index($userId=null){	
		if ( $this->superAdmin == 1 )
			$userId = $this->selectedOrgId;

		$this->data = $this->User->getUsers($userId);
		echo json_encode(['success'=>true,'roles'=>[],'data'=>$this->data]);
	}	

	/**
	* @Method Index
	* @param NULL
	* @return userID
	* @Description: Used to Get All Users
	*/

	public function getAddEdit($userID=null){
		
		$this->data = $this->User->getUsersById($userID);

		if ( $this->superAdmin == 1 )
			$userId = $this->selectedOrgId;
		else
			$userId = $this->userID;

		$roleList 	= $this->AclModel->getRoles($userId);
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

		if(!empty($postData)){
			$data 				= (array)$postData->data;
			$id 				= (!empty($data['id']))?$data['id']:NULL;
			$data['role_id'] 	= (!empty($data['role_id']->id)) ?$data['role_id']->id : $data['role_id'];
			if ( $this->superAdmin == 1 )
				$parentId = $this->selectedOrgId;
			else
				$parentId = $this->userID;

			$data['parent_id'] 	= $parentId;
		
			if(!empty($data['password']) || !empty($data['newpassword'])){
				$password = @($data['password'])?$data['password']:$data['newpassword'];
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

	public function skipAcl_checkUnique(){
		echo json_encode(['success'=>true,'user'=>$this->User->checkUnique()]);
	}

	/**
	* @Method changeStatus
	* @param userId, status
	* @return Boolean
	* @Description: Check User exists or not
	*/

	public function changeStatus( $userId = null, $status = null){
		$this->User->changeStatus($status,$userId);
		$data = $this->User->getUserDetail($userId);
		echo json_encode(array('success'=>true,'userData' => $data));
	}

	/**
	* @Method deleteUser
	* @param userId,
	* @return Boolean
	* @Description: Check User exists or not
	*/

	public function deleteUser( $userId = null){
		$this->User->deleteUser($userId);
		echo json_encode(array('success'=>true));
	}

	protected function uploadImage(){
		pr($_FILES);
		echo json_encode(['success'=>true,'status'=>$this->User->changeStatus()]);
	}


	/**
	* Request URL: http://domain.com/login/organisationsList
	* @Method: Get
	* @param : null
	* @return : organisationsList
	* @Description: Used fro fetching list of organisation added by superadmin
	*/

	public function skipAcl_getOrganisationsList() {
		$result = $this->User->getOrganisationsList();
		$selectedOrg = $this->User->getSelectedOrganisation($this->userID);
		$selectedId = (!empty($selectedOrg) && !empty($selectedOrg['organisation_id'])) ? $selectedOrg['organisation_id'] : ( !empty($result) ? $result[0]['id'] : '');
		$selectedOrgName = (!empty($selectedOrg) && !empty($selectedOrg['name'])) ? $selectedOrg['name'] : ( !empty($result) ? $result[0]['first_name'] : '');

		echo json_encode(array('organisations' => $result, 'selectedOrgName' => $selectedOrgName, 'selectedId' => $selectedId));
	}

	/**
	* Request URL: http://domain.com/login/setSelectedOrganisation
	* @Method: Get
	* @param : null
	* @return : organisationsList
	* @Description: Used to setting value selected in main organisation dropdown
	*/

	public function skipAcl_setSelectedOrganisation() {
		$post = json_decode(file_get_contents('php://input'),true);
		$selectedOrg = $this->User->setSelectedOrganisation($this->userID,$post);		
		echo json_encode(array('success' => true));
	}


}