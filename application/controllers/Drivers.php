<?php
class Drivers extends Admin_Controller
{
	public $userId;
	public $roleId;
	public $pathGen;
	function __construct()
	{ 
		parent::__construct();	
		
		$this->load->model('Driver');
		$this->load->model('Job');
		$this->load->helper('truckstop_helper');		
		$this->userName = $this->session->loggedUser_username;
		$this->pathGen = str_replace('application/', '', APPPATH);
		if($this->session->role != 1){
			$this->userId = $this->session->loggedUser_id;
		}
		$this->roleId = $this->session->role;
	}

	public function index()
	{
		$data = array();
		$parent_id = null;
		$parentIdCheck = $this->session->userdata('loggedUser_parentId');
		if( isset($parentIdCheck) && $parentIdCheck != 0 ) {
			$parent_id = $this->session->userdata('loggedUser_parentId');
		}
		
		$data['rows'] = $this->Driver->fetchAllRecords($parent_id);		
		$data['total_records'] = count($data['rows']);
	
		echo json_encode($data);
	}

	/*
	* Request URL: http://domain/drivers/delete
	* Method: get
	* Params: documentId, documentname
	* Return: success or error
	* Comment: Used for deleting drivers documents
	*/
	public function deleteContractDocs($docId = null, $docName = '')
	{
		$fileNameArray = explode('.',$docName);
		$ext = end($fileNameArray);
		$extArray = array( 'pdf','xls','xlsx','txt', 'bmp', 'ico','jpeg' );
		$fileName = '';
		for ( $i = 0; $i < count($fileNameArray) - 1; $i++ ) {
			$fileName .= $fileNameArray[$i];
		}
		$fileName = $fileName.'.jpg';
		$thumbFile =  $this->pathGen.'assets/uploads/documents/thumb_driver/thumb_'.$fileName;
		$filePath =  $this->pathGen.'assets/uploads/documents/driver/'.$docName;

		if(file_exists($filePath)){
			unlink($filePath); 	
		}
		
		if(file_exists($thumbFile)){
			unlink($thumbFile); 	
		}
		
		$this->Driver->removeContractDocs($docId);
		echo json_encode(array("success" => true));
	}

	/*
	* Request URL: http://domain/drivers/add or edit
	* Method: Post
	* Params: fileArray, prefix, folder name
	* Return: success or error
	* Comment: Used for uploading drivers documents
	*/
	public function uploadDocs()
	{
		$prefix = "driver"; 
	    $response  = array();
	    if(isset($_POST["driverId"]) && $_POST["driverId"] != ""){
			$response = $this->uploadContractDocsToServer($_FILES, $prefix, $prefix);	
			if(isset($response["error"]) && !$response["error"]){
					$docs = array(
						'document_name' => $response['data']['file_name'],
						'entity_type' => $prefix,
						'entity_id' => $_POST['driverId']
					);
				
				$this->Driver->insertContractDocument($docs);
				$response['docList'] = $this->Driver->fetchContractDocuments($_POST['driverId'], 'driver');
			}
		}

		echo json_encode($response);
	}
	
	public function add()
	{	
			$file_name = '';		
			if(!empty($_FILES) && $_FILES['profile_image']['name'] != ''){
				$str = $_FILES['profile_image']['name'];
				$ext =  substr($str, strrpos($str, '.') + 1);
				$config['file_name'] = $file_name = date('Ymdhis').'.'.$ext;
				$config['upload_path'] = './assets/uploads/drivers/';
				$config['allowed_types'] = 'gif|jpg|png|jpeg';
				$config['max_size']             = 100;
				$config['max_width']            = 1024;
				$config['max_height']           = 768;

				$this->load->library('upload', $config);
				if($this->upload->do_upload('profile_image')) {
					
					$config['image_library'] = 'gd2';
					$config['source_image'] = 'assets/uploads/drivers/'.$file_name;	
					$config['new_image'] = './assets/uploads/drivers/thumbnail/';
						//~ $config['create_thumb'] = TRUE;
					$config['maintain_ratio'] = TRUE;
					$config['width']         = 200;
					$config['height']       = 200;	
					$this->load->library('image_lib',$config);
					$this->image_lib->resize();
				}	
				$file_path = 'assets/uploads/drivers/'.$file_name;
				$file_path_thumb = 'assets/uploads/drivers/thumbnail/'.$file_name;
				chmod($file_path,0777);
				chmod($file_path_thumb,0777);
			}
			
			//$data = json_decode(file_get_contents('php://input'), true);
			$array = $this->input->post('posted_data');
			$data = json_decode($array, true);
			$data['created'] = date('Y-m-d h:m:s');
			if(!empty($file_name))
			{
			$data['profile_image'] = $file_name;
			}
			$message = "$this->userName added a new driver";
			logEvent('insert',$message,$this->Job);
			$result = $this->Driver->addDrivers($data,$this->userId);
			echo json_encode(array('success' => true,'lastAddedDriver'=>$result));
	}
	
	/*
	* Request URL: http://domain/drivers/edit
	* Method: get
	* Params: driverId
	* Return: driver array
	* Comment: Used for fetching driver information
	*/
	public function edit( $driver_id = Null )
	{
		$data['drivers'] = $this->Driver->get_driver_data($driver_id);
		$data['driverDocuments'] = $this->Driver->fetchContractDocuments($driver_id, 'driver');
		echo json_encode($data);
	}
	
	public function update() 
	{
		$file_name = '';		
		if(!empty($_FILES) && $_FILES['profile_image']['name'] != ''){
			$str = $_FILES['profile_image']['name'];
			$ext =  substr($str, strrpos($str, '.') + 1);
			$config['file_name'] = $file_name = date('Ymdhis').'.'.$ext;
			$config['upload_path'] = './assets/uploads/drivers/';
			$config['allowed_types'] = 'gif|jpg|png|jpeg';
			$config['max_size']             = 100;
			$config['max_width']            = 1024;
			$config['max_height']           = 768;

			$this->load->library('upload', $config);
			if($this->upload->do_upload('profile_image')) {
				
				$config['image_library'] = 'gd2';
				$config['source_image'] = 'assets/uploads/drivers/'.$file_name;	
				$config['new_image'] = './assets/uploads/drivers/thumbnail/';
					//~ $config['create_thumb'] = TRUE;
				$config['maintain_ratio'] = TRUE;
				$config['width']         = 200;
				$config['height']       = 200;	
				$this->load->library('image_lib',$config);
				$this->image_lib->resize();
			}	
			$file_path = 'assets/uploads/drivers/'.$file_name;
			$file_path_thumb = 'assets/uploads/drivers/thumbnail/'.$file_name;
			chmod($file_path,0777);
			chmod($file_path_thumb,0777);
		}
		
		//$data = json_decode(file_get_contents('php://input'), true);
		$array = $this->input->post('posted_data');
		$data = json_decode($array, true);
		$data['created'] = date('Y-m-d h:m:s');
		if(!empty($file_name))
		{
		$data['profile_image'] = $file_name;
		}
		$id = $_POST['id'];
		
		$previous = array();
		$previous = $this->Driver->get_driver_data($id);
		$changed = array_diff_assoc($data,$previous);
		unset($changed['created']);
		$fields = array();
		foreach($changed as $key => $changed){
			array_push($fields,ucwords(str_replace("_"," ",$key)));
		}
		$changed_fields = implode(",",$fields);
		$message = "$this->userName edited $changed_fields of a driver";
		logEvent('edit',$message,$this->Job);	
		$result = $this->Driver->update($data, $id);
		echo json_encode(array('success' => true));
	}
	
	public function delete($driverID=null)
	{
		$result = $this->Driver->delete( $driverID );
		
		if ( $result ) {
			$message = "$this->userName deleted a driver";
			logEvent('delete',$message,$this->Job);
			echo json_encode(array('success' => true));
		} else {
			echo json_encode(array('success' => false));
		}
	}
	
	public function addDrivers() 
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
    	$result = $this->Driver->addDrivers($_POST,$this->userId);
		echo json_encode(array('success' => true));
	}
	
	public function uploadDocuments()
	{
		$prefix = 'driver';
		$parameter = 'drivers';
		$docs = $_FILES;
		$responseArray = array();
		foreach($docs as $key=>$documents){
			$response = $this->uploadCommonDocs($docs,$prefix,$parameter,$key);
			if(empty($response['error'])){
				$responseArray[$key] = $response;
				$docNameArray = explode('.',$response['data']['file_name']);
				$thumbName = $docNameArray[0];
				$responseArray[$key]['thumb_name'] = 'thumb_'.$thumbName.'.jpg';
				$path = "./assets/uploads/documents/thumb_drivers/".$responseArray[$key]['thumb_name'];
				while(true){
					if(file_exists($path)){
						break;
						}	
					}
			}
		}
		if(!empty($responseArray)){
			echo json_encode(array("success"=>true,"responseArray"=>$responseArray)); 
		}else{
			echo json_encode(array("success"=>true));
		}
			
	}

	 
	 /********* Dispatcher List - r288 ****************/
	 public function dispatcherList($driver_id = null)
	 {
		$data['selected'] = ''; 
		$data['list'] = $this->Driver->get_dispatcher_list();
		$new = array("id"=>"","username"=>"Unassigned");
		array_unshift($data['list'], $new);
		if(!empty($driver_id)){
			$data['selected'] = $this->Driver->get_selected_dispatcher($driver_id);
		} else {
			if($this->roleId == 2){
				foreach($data['list'] as $list){
					if($list['id'] == $this->userId){
						$data['selected'] = $list;
					}
				}
			}
		}
		echo json_encode($data);
	}
	/********* Dispatcher List - r288 ****************/
	
	/**
	 * Changing driver Status
	 */ 
	public function changeStatus( $driverId = null, $status = null )
	{
		$result = $this->Driver->changeDriverStatus($driverId, $status);
		if ( $result ) {
			$message = "$this->userName changed status of a driver";
			logEvent('status',$message,$this->Job);
			$status = true;
		} else {
			$status = false;
		}
		
		$data['rows'] = $this->Driver->get_driver_data($driverId);
			
		echo json_encode(array('records' => $data, 'status' => $status ));
	}
	
}
