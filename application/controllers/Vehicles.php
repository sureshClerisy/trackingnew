<?php
class Vehicles extends Admin_Controller {

	// private $entity;
	// private $event;
	// private $serverAddr;
	// private $protocol;
	public  $search;
	public  $userId;
	public  $roleId;
	public  $userName;
	

	function __construct()
	{ 
		parent::__construct();	
		$this->userId     = $this->session->loggedUser_id;
		$this->roleId     = $this->session->role;	
		$this->userName   = $this->session->loggedUser_username;
		$this->load->library('user_agent');
		$this->load->model('Vehicle','vehicle');		
		$this->load->model('Garage','garage');
		$this->load->model(array('Job','User'));
		$this->load->helper('truckstop_helper');
	}

	public function index() {
		$data = array();
		$parent_id = null;
		$parentIdCheck = $this->session->userdata('loggedUser_parentId');
		if( isset($parentIdCheck) && $parentIdCheck != 0 ) {
			$parent_id = $this->session->userdata('loggedUser_parentId');
		}

		if ( $this->roleId == 2 ) {
			$parent_id = $this->userId;
		}
		$childIds = $this->User->fetchDispatchersChilds($this->userId);
		if ( !empty($childIds) ) {
			$parentId = array();
			foreach($childIds as $child ) {
				array_push($parentId,$child['id']);
			}
			$parent_id = array_merge($parentId,array($this->userId));
		}
		$data['rows'] = $this->vehicle->get_vechicls($parent_id);
		$data['total_records'] = count($data['rows']);
		echo json_encode($data);
	}
	
	/*
	* Request URL: http://domain/vehicles/edit
	* Method: get
	* Params: vehicleId
	* Return: vehicle array or empty array
	* Comment: Used for fetching trucks detail to edit it
	*/
	public function edit( $vehicle_id = null ){
		$data['trucks'] = $this->vehicle->get_row($vehicle_id);
		$vehicleTypeArray = array();
		if ( $data['trucks']['vehicle_type'] != '' ) {
			$vehicleTypes = explode(',',$data['trucks']['vehicle_type']);
			$temp = array();
			foreach ( $vehicleTypes as $vType ) {
				$vDescription = str_replace(',',' ',$this->Job->getRelatedEquipment($vType));
				$temp['abbrevation'] = $vType;
				$temp['name'] = $vDescription;
				array_push($vehicleTypeArray,$temp);
			}
		}
		$data['trucks']['vehicle_type'] = $vehicleTypeArray;
		$data['driversList'] = $this->vehicle->getRelatedDrivers($this->userId);
		$data['states_data'] = $this->Job->getAllStates();
		$data['truckDocuments'] = $this->vehicle->fetchContractDocuments($vehicle_id, 'truck');
		echo json_encode($data);
	}
	 
	public function skipAcl_states(){
		$data['states_data'] = $this->Job->getAllStates();
		$data['driversList'] = $this->vehicle->getRelatedDrivers($this->userId);
		echo json_encode($data);
	}
	
	/*
	* Request URL: http://domain/vehicles/update
	* Method: post
	* Params: null
	* Return: array
	* Comment: Used for update truck information
	*/
	public function update() 
	{
		try{

			$_POST = json_decode(file_get_contents('php://input'), true);
			$id = $_POST['id'];
	    	$vehicleType = '';

			if ( isset($_POST['vehicle_type']) && !empty($_POST['vehicle_type']) ) {
				foreach( $_POST['vehicle_type'] as $vehicleTypes ) {
					$vehicleType .= $vehicleTypes['abbrevation'].",";
				}
				$vehicleType = rtrim($vehicleType,',');
			}

			$_POST['vehicle_type'] = $vehicleType;
			$driverType = $_POST["driverType"];
			unset($_POST["driverType"]);
			$_POST["driver_type"] = $driverType;
			
			if($driverType == "team"){
				
				$_POST["driver_id"] = isset($_POST["team_driver_one"]["id"]) && !empty($_POST["team_driver_one"]["id"]) ? $_POST["team_driver_one"]["id"] : (isset($_POST["team_driver_one"]) && !is_array($_POST["team_driver_one"])  ? $_POST["team_driver_one"] : 0);
				$_POST["team_driver_id"] = isset($_POST["team_driver_two"]["id"]) && !empty($_POST["team_driver_two"]["id"]) ? $_POST["team_driver_two"]["id"] : (isset($_POST["team_driver_two"]["id"]) ? $_POST["team_driver_two"]["id"] : $_POST["team_driver_two"]);
			}else{
				$_POST["team_driver_id"] = 0;
			}

			unset($_POST["team_driver_one"]);
			unset($_POST["team_driver_two"]);
			unset($_POST["teamDriverOne"]);
			unset($_POST["teamDriverTwo"]);
			
			$vehicleOldData = $this->vehicle->get_row($id);
			$editedFields = array_diff_assoc($_POST,$vehicleOldData);
			$skipFlag = false;
			
			$result = $this->vehicle->add_edit_vehicle($_POST, $id);	
				
			if(count($editedFields) > 0){
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited the vehicle <a class="notify-link" href="'.$this->serverAddr.'#/editTruck/'.$vehicleOldData["id"].'"> Truck - '.$vehicleOldData["label"].'</a>';
				foreach ($editedFields as $key => $value) {

					$prevField = isset($vehicleOldData[$key]) ? $vehicleOldData[$key] : "" ;
					if( ( $key == "driver_id" || $key == "team_driver_id") && !$skipFlag){
						$skipFlag = true; $key = "Driver"; $prevField = $vehicleOldData["driverName"];
						
						if($vehicleOldData["driver_type"] == "team") {
							$prevField = $vehicleOldData["driverName"]." + ".$vehicleOldData["teamDriverTwo"];
						}

						$driverName = $this->vehicle->getDriverName($_POST["driver_id"]);
						$value = $driverName["first_name"]." ".$driverName["last_name"];

						if(!empty($_POST["team_driver_id"]) && $_POST["driver_type"] == "team"){
							$teamDriver = $this->vehicle->getDriverName($_POST["team_driver_id"]);	
							$value .= " + ".$teamDriver["first_name"]." ".$teamDriver["last_name"];
						}

						$newDisp = $this->vehicle->getDispatcherId($_POST['driver_id']);
						$oldDisp = $this->vehicle->getDispatcherId($vehicleOldData['driver_id']);

						if ( $newDisp == $oldDisp)
							$this->vehicle->addDispatcherDriverLog($newDisp, false);
						else
							$this->vehicle->addDispatcherDriverLog($newDisp, $oldDisp);

						if(!empty($prevField)){
							$message.= "<br/> - Changed ".ucwords(str_replace("_"," ",$key))." from <i>".$prevField."</i> to <i>".$value."</i>";
						}else{
							$message.= "<br/> - Added  <i>".$value."</i> to ".ucwords(str_replace("_"," ",$key));
						}
					} else {
						if(in_array($key,array("driverName","team_driver_id","driver_id"))){ continue;}
						if(!empty($prevField)){
							$message.= "<br/> - Changed ".ucwords(str_replace("_"," ",$key))." from <i>".$prevField."</i> to <i>".$value."</i>";
						}else{
							$message.= "<br/> - Added  <i>".$value."</i> to ".ucwords(str_replace("_"," ",$key));
						}	
					}
				}
			}else{
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited the vehicle <a class="notify-link" href="'.$this->serverAddr.'#/editTruck/'.$vehicleOldData["id"].'"> Truck - '.$vehicleOldData["label"].'</a>, but changed nothing.';
			}
	       	
	       	logActivityEvent($vehicleOldData["id"], $this->entity["truck"], $this->event["edit"], $message, $this->Job);	
			echo json_encode(array('success' => true));
		}catch(Exception $e){
			log_message('error','EDIT_VEHICLE'.$e->getMessage());
			echo json_encode(array('success' => false));
		}
	}
	

	/*
	* Request URL: http://domain/vehicles/addTruck
	* Method: post
	* Params: null
	* Return: array
	* Comment: Used for add truck information
	*/
	public function addTruck() 
	{
		try{
			
			$_POST = json_decode(file_get_contents('php://input'), true);
			$_POST['user_id'] = $this->userId;
			$vehicleType = '';

			if ( isset($_POST['vehicle_type']) && !empty($_POST['vehicle_type']) ) {
				foreach( $_POST['vehicle_type'] as $vehicleTypes ) {
					$vehicleType .= $vehicleTypes['abbrevation'].",";
				}
				$vehicleType = rtrim($vehicleType,',');
			}

			$_POST['vehicle_type'] = $vehicleType;
			$driverType = $_POST["driverType"];
			unset($_POST["driverType"]);
			$_POST["driver_type"] = $driverType;
			
			if($driverType == "team"){
				$_POST["driver_id"] = isset($_POST["team_driver_one"]["id"]) && !empty($_POST["team_driver_one"]["id"]) ? $_POST["team_driver_one"]["id"] : (isset($_POST["team_driver_one"]) && !is_array($_POST["team_driver_one"])  ? $_POST["team_driver_one"] : 0);
				$_POST["team_driver_id"] = isset($_POST["team_driver_two"]["id"]) && !empty($_POST["team_driver_two"]["id"]) ? $_POST["team_driver_two"]["id"] : (isset($_POST["team_driver_two"]["id"]) ? $_POST["team_driver_two"]["id"] : (isset($_POST["team_driver_two"]) ? $_POST["team_driver_two"] : 0));
			}else{
				$_POST["team_driver_id"] = 0;
			}
			
			unset($_POST["team_driver_one"]);
			unset($_POST["team_driver_two"]);
			unset($_POST["teamDriverOne"]);
			unset($_POST["teamDriverTwo"]);


			$result = $this->vehicle->add_edit_vehicle($_POST);

			if ( isset($_POST['driver_id']) && $_POST['driver_id'] != '' ) {
				$dispId = $this->vehicle->getDispatcherId($_POST['driver_id']);
				$this->vehicle->addDispatcherDriverLog($dispId, false);
			}

			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> added a new vehicle <a class="notify-link" href="'.$this->serverAddr.'#/editTruck/'.$result.'"> Truck - '.$_POST["label"]."</a>";
			logActivityEvent($result, $this->entity["truck"], $this->event["add"], $message, $this->Job);	
			echo json_encode(array('success' => true,'lastTruckId' => $result));
		}catch(Exception $e){
			log_message('error','ADD_VEHICLE'.$e->getMessage());
			echo json_encode(array("success" => false));
		}
	}
	
	// public function delete($truckID=null){
	// 	$result = $this->vehicle->deleteVehicle($truckID);
	// 	if ( $result ) {
	// 		echo json_encode(array('success' => true));
	// 	} else {
	// 		echo json_encode(array('success' => false));
	// 	}
		
	// }
	

	/*
	* Request URL: http://domain/vehicles/edit
	* Method: POST
	* Params: null
	* Return: string
	* Comment: Used for uploading vehicle image on server
	*/
	public function skipAcl_image()
	{
		try{

			$data = array();
			$data['vehicle_image1'] = '';
			$data['vehicle_image'] = '';
			$fileName = '';

			if(!empty($_FILES)){
				if(!empty($_FILES['vehicle_image']['name'])){
					($_FILES['vehicle_image']['name']);
				   	$fileName = $config['file_name']   = time().str_replace(' ', '_', $_FILES['vehicle_image']['name']);
					if(file_exists("assets/uploads/vehicles/".$fileName."")){
						$old_image =  "assets/uploads/vehicles/".$fileName."";
						$old_thumb =  "assets/uploads/vehicles/thumbnail/".$fileName."";
					    unlink($old_image);
					    unlink($old_thumb);
				    }
				   $input = 'vehicle_image';
				   }else{
					$input = 'vehicle_image';
					$fileName = $config['file_name']   = time().$_FILES['vehicle_image']['name'];
				}

				$config['upload_path']          = 'assets/uploads/vehicles/';
				$config['allowed_types']        = 'gif|jpg|png|jpeg';
				
				$this->load->library('upload', $config);
				if ( $this->upload->do_upload($input))
				{
					$config['image_library'] = 'gd2';
					$config['source_image'] = 'assets/uploads/vehicles/'.$fileName;	
					$config['new_image'] = './assets/uploads/vehicles/thumbnail/'.$fileName;
						//~ $config['create_thumb'] = TRUE;
					$config['maintain_ratio'] = TRUE;
					$config['width']         = 100;
					$config['height']       = 100;	
					$this->load->library('image_lib',$config);
					$this->image_lib->resize();
					$error = array('error' => $this->upload->display_errors());
					unset($data['vehicle_image1']);
					$data['vehicle_image'] = $fileName;
				}
			}

			echo $fileName;

		}catch(Exception $e){
			log_message('error','UPLOAD_VEHICLE_IMAGE'.$e->getMessage());
		}
	}
	
	/**
	 *  checking driver already assigned to another truck or not
	 */ 
	 
	public function skipAcl_changeDriver( $driverId = null, $vehicleId = null ) {
		$truckName = '';
		$result = $this->vehicle->checkChangeDriver($driverId,$vehicleId, $this->userId);
		if ( !empty($result) ) {
			$res = false;
			$truckName = $result['label'];
		} else {
			$res = true;
		}
		echo json_encode(array('result' => $res, 'truckName' => $truckName));
	}
	
	/*
	* Request URL: http://domain/vehicles/change status
	* Method: get
	* Params: vehicleId, status
	* Return: status value
	* Comment: Used for updating vehicle status
	*/
	
	public function changeStatus( $vehicleId = null, $status = null )
	{
		try{

			$requestedStatus = ($status == 1) ? "Deactivated" : "Activated";
			$result = $this->vehicle->changeDriverStatus($vehicleId, $status);
			if ( $result ) {
				$status = true;
			} else {
				$status = false;
			}
			
			$data['rows'] = $this->vehicle->fetchSingleUpdatedRecord($vehicleId);
			$vehicleTypeArray = array();
			if ( $data['rows']['vehicle_type'] != '' ) {
				$vehicleTypes = explode(',',$data['rows']['vehicle_type']);
				$temp = array();
				foreach ( $vehicleTypes as $vType ) {
					$vDescription = str_replace(',',' ',$this->Job->getRelatedEquipment($vType));
					$temp['abbrevation'] = $vType;
					$temp['name'] = $vDescription;
					array_push($vehicleTypeArray,$temp);
				}
			}
			$data['rows']['vehicle_type'] = $vehicleTypeArray;
			$message = '<span class="blue-color uname">'.ucfirst($this->userName)."</span> changed the status to <i>".$requestedStatus.'</i> of  vehicle <a class="notify-link" href="'.$this->serverAddr.'#/editTruck/'.$vehicleId.'"> Truck - '.$data['rows']["label"].'</a>';
			logActivityEvent($vehicleId, $this->entity["truck"], $this->event["status_change"], $message, $this->Job);	
			echo json_encode(array('records' => $data, 'status' => $status ));
		}catch(Exception $e){
			log_message('error','CHANGE_VEHICLE_STATUS'.$e->getMessage());
			echo json_encode(array('success' => false));
		}
	}
	
	/**
	 * Check truck number exist or not
	 */
	
	public function skipAcl_checkTruckNumber( $truckNo = null, $id = null ) {
		if ( $id != '' && $id != null )
			$result = $this->vehicle->checkTruckNumberExist($truckNo, $id);
		else
			$result = $this->vehicle->checkTruckNumberExist($truckNo);
					
		if ( !empty($result) ) 
			$success = false;
		else
			$success = true;
		
		echo json_encode(array('success' => $success));
	} 
	
	/*
	* Request URL: http://domain/vehicles/add or edit
	* Method: Post
	* Params: fileArray
	* Return: success or error
	* Comment: Used for uploading trucks documents
	*/
	
	public function skipAcl_uploadContractDocuments()
	{
		$prefix = "truck"; 
	    $response  = array();
	    if(isset($_POST["truckId"]) && $_POST["truckId"] != ""){
			$response = $this->uploadContractDocsToServer($_FILES, $prefix, $prefix);	
			if(isset($response["error"]) && !$response["error"]){
					$docs = array(
						'document_name' => $response['data']['file_name'],
						'entity_type' => $prefix,
						'entity_id' => $_POST['truckId']
					);
				try{
					$this->vehicle->insertContractDocument($docs);
					$response['docList'] = $this->vehicle->fetchContractDocuments($_POST['truckId'], 'truck');

					$vehicleInfo = $this->vehicle->get_row($_POST['truckId']);
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> uploaded a new document ('.$docs["document_name"].') for vehicle <a class="notify-link" href="'.$this->serverAddr.'#/editTruck/'.$vehicleInfo["id"].'"> Truck - '.$vehicleInfo["label"].'</a>';
					logActivityEvent($vehicleInfo['id'], $this->entity["truck"], $this->event["upload_doc"], $message, $this->Job);
				}catch(Exception $e){
					log_message('error','UPLOAD_VEHICLE_DOC'.$e->getMessage());
				}
			}
		}
		echo json_encode($response);
	}
	
	/*
	* Request URL: http://domain/vehicles/deleteContractDocs
	* Method: get
	* Params: documentId, documentname
	* Return: success or error
	* Comment: Used for deleting vehicles documents
	*/
	
	public function skipAcl_deleteContractDocs($docId = null, $docName = '')
	{
		try{
			
			$pathGen = str_replace('application/', '', APPPATH);
			$fileNameArray = explode('.',$docName);
			$ext = end($fileNameArray);
			$extArray = array( 'pdf','xls','xlsx','txt', 'bmp', 'ico','jpeg' );
			$fileName = '';
			for ( $i = 0; $i < count($fileNameArray) - 1; $i++ ) {
				$fileName .= $fileNameArray[$i];
			}
			$fileName 	= $fileName.'.jpg';
			$thumbFile 	=  $pathGen.'assets/uploads/documents/thumb_truck/thumb_'.$fileName;
			$filePath 	=  $pathGen.'assets/uploads/documents/truck/'.$docName;

			if(file_exists($filePath)){
				unlink($filePath); 	
			}
			
			if(file_exists($thumbFile)){
				unlink($thumbFile); 	
			}
			$vehicleInfo = $this->vehicle->getEntityInfoByDocId($docId,$this->entity["truck"]);
			$this->vehicle->removeContractDocs($docId);
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted a document ('.$vehicleInfo["document_name"].') from vehicle <a class="notify-link" href="'.$this->serverAddr.'#/editTruck/'.$vehicleInfo["id"].'"> Truck - '.$vehicleInfo["label"].'</a>';
			logActivityEvent($vehicleInfo['id'], $this->entity["truck"], $this->event["remove_doc"], $message, $this->Job);	
			echo json_encode(array("success" => true));

		}catch(Exception $e){

			log_message('error','DELETE_VEHICLE_DOC'.$e->getMessage());
			echo json_encode(array("success" => false));

		}
	}

	public function fetchDataForCsv() {
		$data = array();
		$parent_id = null;
		$content ='';
		$parentIdCheck = $this->session->userdata('loggedUser_parentId');
	
		if( isset($parentIdCheck) && $parentIdCheck != 0 ) {
			$parent_id = $this->session->userdata('loggedUser_parentId');
		}

		if ( $this->roleId == 2 ) {
			$parent_id = $this->userId;
		}
		$childIds = $this->User->fetchDispatchersChilds($this->userId);
		if ( !empty($childIds) ) {
			$parentId = array();
			foreach($childIds as $child ) {
				array_push($parentId,$child['id']);
			}
			$parent_id = array_merge($parentId,array($this->userId));
		}

		$keys = [['Label','Model','Vehicle Type','Tracker ID','Driver Name','Dispatcher','Registration Plate','VIN','Permitted Speed','Cargo Capacity','Cargo Bay Length','Cargo Bay Width','Fuel Type','Fuel Consumption','Tank Capacity','Tyres Size','Tyres Number','State','City','Vehicle Address','Owner','Unit']];
		
		$searchText 	= json_decode(file_get_contents('php://input'), true);		
		$dataRow 		= $this->vehicle->fetchVehiclesForCSV($parent_id,$searchText);
		$data 			= array_merge($keys,$dataRow);
		echo json_encode(array('fileName'=>$this->createExcell('vehicles',$data)));
	}
}
