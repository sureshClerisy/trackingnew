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
	* @Method add_user
	* @param NULL
	* @return userID
	* @Description: Used to Get All roles for adding user
	*/

	public function add_user($userID=null){
		if ( $this->superAdmin == 1 )
			$userId = $this->selectedOrgId;
		else
			$userId = $this->userID;

		$roleList 	= $this->AclModel->getRoles($userId);
		echo json_encode(['success'=>true,'roles'=>$roleList]);
	}

	/**
	* @Method edit_user
	* @param NULL
	* @return userID
	* @Description: Used to Get All roles for editing user and details of user
	*/

	public function edit_user($userID=null){
		
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

	public function skipAcl_postAddEdit(){
		$file_name = '';		
		$array = $this->input->post('posted_data');
		$data = json_decode($array, true);

		if(!empty($_FILES) && $_FILES['profile_image']['name'] != ''){
			$str = $_FILES['profile_image']['name'];
			$ext =  substr($str, strrpos($str, '.') + 1);
			$config['file_name'] = $file_name = date('Ymdhis').'.'.$ext;
			$config['upload_path'] = './assets/uploads/users/';
			$config['allowed_types'] = 'gif|jpg|png|jpeg';
			// $config['max_size']             = 100;
			// $config['max_width']            = 1024;
			// $config['max_height']           = 768;
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

		
		if(!empty($file_name)){
			$data['profile_image'] = $file_name;
		}

		if(!empty($data)){
			$id 				= (!empty($data['id'])) ? $data['id'] : NULL;
			$data['role_id'] 	= (!empty($data['role_id']->id)) ?$data['role_id']->id : $data['role_id'];
			$data['parent_id']  = $this->selectedOrgId;		

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
		$result = $this->getOrgLists();
		echo json_encode(array('organisations' => $result['orgList'], 'selectedOrgName' => $result['selectedOrgName'], 'selectedId' => $result['selectedId']));
	}

	private function getOrgLists() {
		$res = array();
		$result = $this->User->getOrganisationsList();
		$selectedOrg = $this->User->getSelectedOrganisation($this->userID);
		$selectedId = (!empty($selectedOrg) && !empty($selectedOrg['organisation_id'])) ? $selectedOrg['organisation_id'] : (!empty($result) ? $result[0]['id'] : '');
		$selectedOrgName = (!empty($selectedOrg) && !empty($selectedOrg['name'])) ? $selectedOrg['name'] : ( !empty($result) ? $result[0]['first_name'] : '');
		if( !empty($result)) {
			$key = array_search($selectedId, array_column($result, 'id'));
			$temp = $result[$key];
			unset($result[$key]);
			array_unshift($result, $temp);
		}

		$res['orgList'] = $result;
		$res['selectedId'] = $selectedId;
		$res['selectedOrgName'] = $selectedOrgName;
		return $res;
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
		$result = $this->getOrgLists();

		echo json_encode(array('success' => true,'organisations' => $result['orgList']));
	}

	public function skipAcl_fetchSidebarmenu(){
		echo json_encode(array('success' => true));
	}

}