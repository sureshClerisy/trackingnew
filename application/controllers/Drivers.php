<?php
class Drivers extends Admin_Controller {
	public $userId;
	public $roleId;
	public $pathGen;
	public $userName;
	// private $entity;
	// private $event;
	function __construct() { 
		parent::__construct();	
		$this->userName = $this->session->loggedUser_username;
		$this->pathGen = str_replace('application/', '', APPPATH);
		$this->load->model(array('Driver',"Job", 'User'));
	}

	public function index() {
		$data 		= array();
		$parent_id 	= null;
		
		if ( $this->globalRoleId == 2 ) {
			$parent_id = $this->userID;
		}

		$childIds = $this->User->fetchDispatchersChilds($this->userID);	
		if ( !empty($childIds) ) {
			$parentId = array();
			foreach($childIds as $child ) {
				array_push($parentId,$child['id']);
			}
			$parent_id = array_merge($parentId,array($this->userID));
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
	public function deleteContractDocs($docId = null, $docName = '') {
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
	public function skipAcl_uploadDocs() {
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
	
	public function add() {	
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

			if (isset($data['user_id']) && $data['user_id'] != '') {
				$this->Driver->addDispatcherDriverLog($data['user_id'], false);
			} 

			$data["first_name"] = trim($data["first_name"]);
			$data["last_name"] = trim($data["last_name"]);

			$result = $this->Driver->addDrivers($data);
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
		$this->checkOrganisationIsValid($driver_id,'drivers');
		$data['drivers'] = $this->Driver->get_driver_data($driver_id);
		$data['driverDocuments'] = $this->Driver->fetchContractDocuments($driver_id, 'driver');
		echo json_encode($data);
	}
	
	public function skipAcl_update() 
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
			$id = @$_POST['id'];
			$previous = array();
			$previous = $this->Driver->get_driver_data($id);

			$editedFields = array_diff_assoc($data,$previous);
			unset($editedFields['created']);
			unset($editedFields['updated']);
			unset($data['uFirstName']); 
			unset($data['uLastName']);

			$data["first_name"] = trim($data["first_name"]);
			$data["last_name"] = trim($data["last_name"]);
			
			$messageValue = 0;
			$messageNot = '';
		
			if ( !empty($editedFields) && isset($editedFields['user_id']) ) {
				$notif = $this->changeAssignedLoads($data['id'],$editedFields['user_id']);
				if ( is_array($notif) && !empty($notif)) {
					$yes = 'yes';
					$no = 'no';
					$messageNot = "Success : Data saved successfully. ".$notif['driverName']." is in team with ".$notif['teamName'].". <span style='color:red;'>Please change </span> the assignment of <a href='#/editDrivers/'".$notif['second_driver_id']." target = '_blank' class='notify-link'>{$notif['teamName']}</a>";
					// $message = "Success : Data saved successfully." $notif['driverName']." is in team with ".$notif['teamName'].". Do you want to change the team member also <a href='javascript:void(0);' class='notify-link' ng-click='editDriver.changeBothDrivers({$notif['driver_id']},{$editedFields['user_id']},{$previous['user_id']},{$notif['second_driver_id']})'>Yes</a>  or <a href='javascript:void(0);' class='notify-link' ng-click='editDriver.changeSingleDriver({$notif['driver_id']},{$editedFields['user_id']},{$previous['user_id']})'>No</a>";
					// $notif['user_id'] = $editedFields['user_id'];
					// $notif['previous_userId'] = $previous['user_id'];

					$messageValue = 1;
				}
				
			}
			
			$result = $this->Driver->update($data, $id);

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
	
						$this->Driver->addDispatcherDriverLog($editedFields['user_id'], $previous['user_id']);

					} 
					if(!empty($prevField)){
						$message.= "<br/> - Changed ".ucwords(str_replace("_"," ",$key))." from <i>".$prevField."</i> to <i>".$value."</i>";
					}else{
						$message.= "<br/> - Added  <i>".$value."</i> to ".ucwords(str_replace("_"," ",$key));
					}
				}
			} else {
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited the driver <a class="notify-link" href="'.$this->serverAddr.'#/editDrivers/'.$previous["id"].'">'.ucfirst($previous["first_name"])." ".ucfirst($previous["last_name"])."</a>, but changed nothing.";
			}
			
			logActivityEvent($previous["id"], $this->entity["driver"], $this->event["edit"], $message, $this->Job);	

			if( $messageValue == 1 ) {
				echo json_encode(array('notification' => 'yes', 'message' => $messageNot));
			} else {
				echo json_encode(array('success' => true));
			}
		
		}catch(Exception $e){
			log_message('error','EDIT_DRIVER'.$e->getMessage());
			echo json_encode(array('success' => false));
		}
	}
	
	// public function delete($driverID=null)
	// {
	// 	$result = $this->Driver->delete( $driverID );
	// 	if ( $result ) {
	// 		$message = "$this->userName deleted a driver";
	// 		logEvent('delete',$message,$this->Job);
	// 		echo json_encode(array('success' => true));
	// 	} else {
	// 		echo json_encode(array('success' => false));
	// 	}
	// }
	
	 
	public function skipAcl_dispatcherList($driver_id = null)
	{
		$data['selected'] = ''; 
		if ( $this->globalRoleId == 2 )
			$userId = $this->userID;
		else
			$userId = false;

		$data['list'] = $this->Driver->get_dispatcher_list($userId, false);
		$childIds = $this->Driver->get_dispatcher_list($this->userID, true);
		if ( !empty($childIds) ) {
			$data['list'] = array_merge($data['list'],$childIds);
		}
		
		$new = array("id"=>"","username"=>"Unassigned");
		array_unshift($data['list'], $new);
		if(!empty($driver_id)){
			$data['selected'] = $this->Driver->get_selected_dispatcher($driver_id);
		} else {
			if($this->globalRoleId == 2){
				foreach($data['list'] as $list){
					if($list['id'] == $this->userID){
						$data['selected'] = $list;
					}
				}
			}
		}
		echo json_encode($data);
	}
	
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

	/*
	* Request URL: http://domain/drivers/checkLicenceNumber
	* Method: get
	* Params: licenceNo
	* Return: array 
	* Comment: Used to check duplicate driver licence number exist
	*/

	public function skipAcl_checkLicenceNumber( $entityId = null ) {
		
		$_POST = json_decode(file_get_contents('php://input'), true);
		
		$response = true;

		if ( $_POST['value'] != '') {
			
			$result = $this->Driver->checkLicenceNumberExist($_POST['value'], $_POST['name'], $_POST['tblName'], $entityId);
			if ( $result ){
				$response = false;
			}
		}

		echo json_encode(array('response' => $response));
	}

	public function fetchDataForCsv() {

		$data 		= array();
		$parent_id 	= null;
		$content 	= '';
		
		if ( $this->globalRoleId == 2 ) {
			$parent_id = $this->userID;
		}

		$childIds = $this->User->fetchDispatchersChilds($this->userID);
		if ( !empty($childIds) ) {
			$parentId = array();
			foreach($childIds as $child ) {
				array_push($parentId,$child['id']);
			}
			$parent_id = array_merge($parentId,array($this->userID));
		}

		$searchText 	= json_decode(file_get_contents('php://input'), true);
		$keys 			= [['First Name','Last Name','Email','Label','License Number','Dispatcher','Phone','Status','DOB','City','Vehicle ID']];
		$dataRow 		= $this->Driver->fetchDriversForCSV($parent_id,$searchText);
		$data = array_merge($keys,$dataRow);
		echo json_encode(array('fileName'=>$this->createExcell('drivers',$data)));
		die();
		// echo json_encode($data);
	}

	/**
	* change future assignedloads under new dispatcher
	*/

	private function changeAssignedLoads( $driverId = null, $userId = null ) {
		$jobsList = $this->Driver->fetchDriversLoad($driverId);
		if ( !empty($jobsList) ) {
			$notification = 0;
			$notifyArray = array();
			foreach($jobsList as $jobs ) {

				if ( $jobs['driver_type'] == 'team ') {
					$notification = 1;
					$notifyArray['driver_id'] = $jobs['driver_id'];
					$notifyArray['second_driver_id'] = $jobs['second_driver_id'];
					$notifyArray['id'] = $jobs['id'];
					$notifyArray['driverName'] = $jobs['driverName'];
					$notifyArray['teamName'] = $jobs['teamName'];
				} 

				$data[] = array(
					'id' => $jobs['id'],
					'driver_id' => $jobs['driver_id'],
					'dispatcher_id' => $userId,
				);
			}

			$this->Driver->updateLoadsAssignments($data);

			if ( $notification == 1 )
				return $notifyArray;

		}
		return true;
	}

	/**
	* Changing driver assignment after selecting yes or no 
	*/

	public function skipAcl_changeDriverAssignment( $type = '', $driverId = null , $userId = null , $previousUser = null, $secondDriverId = null) {

		$type  = ( $type == 'team') ? $type : '';
		$jobsList = $this->Driver->fetchDriversLoad($driverId,$type);
		if ( !empty($jobsList) ) {
			foreach($jobsList as $jobs ) {
				$data[] = array(
					'id' => $jobs['id'],
					'dispatcher_id' => $userId,
				);
			}
			$this->Driver->updateLoadsAssignments($data);
		}

		$data = array(
			'id' => $driverId,
			'user_id' => $userId
		);

		$this->Driver->updateDriver($data);

		if ( $secondDriverId != '' && $secondDriverId != 0 ) {
			$data = array(
				'id' => $secondDriverId,
				'user_id' => $userId
			);
			$this->Driver->updateDriver($data);
		}
		$previousUser = (isset($previousUser) && $previousUser != '' ) ? $previousUser : false;
		$this->Driver->addDispatcherDriverLog($userId, $previousUser);
		echo json_encode(array('success' => true));
	}
}