<?php
class Drivers extends Admin_Controller
{
	public $userId;
	public $roleId;
	public $pathGen;
	public $userName;
	// private $entity;
	// private $event;
	function __construct()
	{ 
		parent::__construct();	
		$this->userName = $this->session->loggedUser_username;
		// $this->entity = $this->config->item('entity');
		// $this->event = $this->config->item('event');
		$this->pathGen = str_replace('application/', '', APPPATH);
		$this->load->model(array('Driver',"Job"));
		$this->load->helper('truckstop_helper');		
		$this->roleId = $this->session->role;
		if($this->roleId != 1){
			$this->userId = $this->session->loggedUser_id;
		}
		
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
		try{
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
			
			$driverInfo = $this->Driver->getEntityInfoByDocId($docId,$this->entity["driver"]);
			$this->Driver->removeContractDocs($docId);
			
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted a document ('.$driverInfo["document_name"].') from driver <a class="notify-link" href="'.$this->serverAddr.'#/editDrivers/'.$driverInfo["id"].'">'.ucfirst($driverInfo["first_name"])." ".ucfirst($driverInfo["last_name"])."</a>";
			logActivityEvent($driverInfo['id'], $this->entity["driver"], $this->event["remove_doc"], $message, $this->Job);	
			echo json_encode(array("success" => true));

		}catch(Exception $e){
			log_message('error','DELETE_DRIVER_DOC'.$e->getMessage());
			echo json_encode(array("success" => false));
		}
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
				
				try{
					$this->Driver->insertContractDocument($docs);
					$response['docList'] = $this->Driver->fetchContractDocuments($_POST['driverId'], 'driver');	
					
					$driverInfo = $this->Driver->get_driver_data($_POST['driverId'],array("drivers.first_name","drivers.last_name"));
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> uploaded a new document ('.$docs["document_name"].') for driver <a class="notify-link" href="'.$this->serverAddr.'#/editDrivers/'.$_POST['driverId'].'">'.ucfirst($driverInfo["first_name"])." ".ucfirst($driverInfo["last_name"]).'</a>';
					logActivityEvent($_POST['driverId'], $this->entity["driver"], $this->event["upload_doc"], $message, $this->Job);	

				}catch(Exception $e){
					log_message('error','UPLOAD_DRIVER_DOC'.$e->getMessage());
				}
			}
		}

		echo json_encode($response);
	}



	/*
	* Request URL: http://domain/drivers/add
	* Method: POST
	* Params: null
	* Return: array 
	* Comment: Used for add new driver
	*/
	
	public function add()
	{	
		try{
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
				if(file_exists($file_path)) { chmod($file_path,0777); }
				if(file_exists($file_path_thumb)){ chmod($file_path_thumb,0777); }
			}
			
			$array = $this->input->post('posted_data');
			$data = json_decode($array, true);
			$data['created'] = date('Y-m-d h:m:s');

			if(!empty($file_name)){
				$data['profile_image'] = $file_name;
			}

			$result = $this->Driver->addDrivers($data,$this->userId);
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> added a new driver <a class="notify-link" href="'.$this->serverAddr.'#/editDrivers/'.$result.'">'.ucfirst($data["first_name"])." ".ucfirst($data["last_name"]).'</a>';
			logActivityEvent($result, $this->entity["driver"], $this->event["add"], $message, $this->Job);	
			echo json_encode(array('success' => true,'lastAddedDriver'=>$result));

		}catch(Exception $e){
			log_message('error','ADD_DRIVER'.$e->getMessage());
			echo json_encode(array('success' => false));
		}
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
		try{
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
					$config['maintain_ratio'] = TRUE;
					$config['width']         = 200;
					$config['height']       = 200;	
					$this->load->library('image_lib',$config);
					$this->image_lib->resize();
				}	

				$file_path = 'assets/uploads/drivers/'.$file_name;
				$file_path_thumb = 'assets/uploads/drivers/thumbnail/'.$file_name;
				if(file_exists($file_path)) { chmod($file_path,0777); }
				if(file_exists($file_path_thumb)){ chmod($file_path_thumb,0777); }
				
			}
			$array = $this->input->post('posted_data');
			$data = json_decode($array, true);
			$data['updated'] = date('Y-m-d h:i:s');
			if(!empty($file_name)){
				$data['profile_image'] = $file_name;
			}
			$id = $_POST['id'];
			$previous = array();
			$previous = $this->Driver->get_driver_data($id);
			$editedFields = array_diff_assoc($data,$previous);
			unset($editedFields['created']);
			unset($editedFields['updated']);

			if(count($editedFields) > 0){
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited the driver <a class="notify-link" href="'.$this->serverAddr.'#/editDrivers/'.$previous["id"].'">'.ucfirst($previous["first_name"])." ".ucfirst($previous["last_name"])."</a>";
				foreach ($editedFields as $key => $value) {
					$prevField = isset($previous[$key]) ? $previous[$key] : "" ;
					$key = ($key == "driver_license_cats") ? "driver_license_class" : $key;
					if( $key == "user_id" ){
						$dispatcherInfo =  $this->Driver->getUserInfo($value);
						$key = "dispatcher";
						$prevField = $previous["uFirstName"]." ".$previous["uLastName"];
						$value = $dispatcherInfo["first_name"]." ".$dispatcherInfo["last_name"];
					} 

					if(!empty($prevField)){
						$message.= "<br/> - Changed ".ucwords(str_replace("_"," ",$key))." from <i>".$prevField."</i> to <i>".$value."</i>";
					}else{
						$message.= "<br/> - Added  <i>".$value."</i> to ".ucwords(str_replace("_"," ",$key));
					}
				}
			}else{
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited the driver <a class="notify-link" href="'.$this->serverAddr.'#/editDrivers/'.$previous["id"].'">'.ucfirst($previous["first_name"])." ".ucfirst($previous["last_name"])."</a>, but changed nothing.";
			}
			unset($data['uFirstName']); unset($data['uLastName']);
			$result = $this->Driver->update($data, $id);
			logActivityEvent($previous["id"], $this->entity["driver"], $this->event["edit"], $message, $this->Job);	
			echo json_encode(array('success' => true));
		}catch(Exception $e){
			log_message('error','EDIT_DRIVER'.$e->getMessage());
			echo json_encode(array('success' => false));
		}
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
	
	
	/*
	* Request URL: http://domain/drivers/changeStatus
	* Method: get
	* Params: driverId, status
	* Return: array 
	* Comment: Used for change driver status
	*/
	public function changeStatus( $driverId = null, $status = null )
	{
		try{
			
			$requestedStatus = ($status == 1) ? "Deactivated" : "Activated";
			$result = $this->Driver->changeDriverStatus($driverId, $status);
			
			if ( $result ) { 
				$status = true; 
			} else { 
				$status = false; 
			}

			$data['rows'] = $driverInfo = $this->Driver->fetchSingleUpdatedRecord($driverId);
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> changed the status to <i>'.$requestedStatus.'</i> of driver  <a class="notify-link" href="'.$this->serverAddr.'#/editDrivers/'.$driverId.'">'.ucfirst($driverInfo["first_name"]).' '.ucfirst($driverInfo["last_name"]).'</a>.';
			logActivityEvent($driverId, $this->entity["driver"], $this->event["status_change"], $message, $this->Job);	
			
			echo json_encode(array('records' => $data, 'status' => $status ));	
		}catch(Exception $e){
			log_message('error','CHANGE_DRIVER_STATUS'.$e->getMessage());
			echo json_encode(array('success' => false));
		}
	}
	
}


