<?php
class Vehicles extends Admin_Controller {

	public $search;
	public $userId;
	public $roleId;

	function __construct()
	{ 
		parent::__construct();	
		$this->load->library('user_agent');
		$this->load->model('Vehicle','vehicle');		
		$this->load->model('Garage','garage');
		$this->load->model('Job');
		
		$this->userId = $this->session->loggedUser_id;
		$this->roleId = $this->session->role;	
	}

	public function index() {
		$data = array();
		$parent_id = null;
		$parentIdCheck = $this->session->userdata('loggedUser_parentId');
		if( isset($parentIdCheck) && $parentIdCheck != 0 ) {
			$parent_id = $this->session->userdata('loggedUser_parentId');
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
	 
	public function states(){
		$data['states_data'] = $this->Job->getAllStates();
		$data['driversList'] = $this->vehicle->getRelatedDrivers($this->userId);
		echo json_encode($data);
	}
	
	/**
	 * Updating truck information to db
	 */
	  
	public function update() {
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
       	$result = $this->vehicle->add_edit_vehicle($_POST, $id);
		echo json_encode(array('success' => true));
	}
	
	/**
	 * Adding truck information to db
	 */
	 
	public function addTruck() {
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
		echo json_encode(array('success' => true,'lastTruckId' => $result));
	}
	
	public function delete($truckID=null){
		$result = $this->vehicle->deleteVehicle($truckID);
		if ( $result ) {
			echo json_encode(array('success' => true));
		} else {
			echo json_encode(array('success' => false));
		}
		
	}
	
	public function image(){
	
		$data = array();
		$data['vehicle_image1'] = '';
		$data['vehicle_image'] = '';
		$fileName = '';
		 if(!empty($_FILES)){
			if(!empty($_FILES['vehicle_image']['name'])){
				($_FILES['vehicle_image']['name']);
			   	$fileName = $config['file_name']   = time().str_replace(' ', '_', $_FILES['vehicle_image']['name']);
				/*if(isset($this->input->post('data1')){
					$image = $_POST['data1'];     //Image Name comming from the Form
				}*/
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
				$config['new_image'] = './assets/uploads/vehicles/thumbnail/';
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
	}
	
	/**
	 *  checking driver already assigned to another truck or not
	 */ 
	 
	public function changeDriver( $driverId = null, $vehicleId = null ) {
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
	
	public function changeStatus( $vehicleId = null, $status = null ){
		$result = $this->vehicle->changeDriverStatus($vehicleId, $status);
		if ( $result ) {
			$status = true;
		} else {
			$status = false;
		}
		
		$data['rows'] = $this->vehicle->get_row($vehicleId);
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
		echo json_encode(array('records' => $data, 'status' => $status ));
	}
	
	/**
	 * Check truck number exist or not
	 */
	
	public function checkTruckNumber( $truckNo = null, $id = null ) {
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
	
	public function uploadContractDocuments()
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
				
				$this->vehicle->insertContractDocument($docs);
				$response['docList'] = $this->vehicle->fetchContractDocuments($_POST['truckId'], 'truck');
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
	
	public function deleteContractDocs($docId = null, $docName = '')
	{
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
		
		$this->vehicle->removeContractDocs($docId);
		echo json_encode(array("success" => true));
	}
}
