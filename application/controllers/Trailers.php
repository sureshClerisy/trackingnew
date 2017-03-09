<?php
class Trailers extends Admin_Controller {

	public $search;
	public $userId;
	public $roleId;
	public $data;
	
	function __construct()
	{ 
		parent::__construct();	
		$this->load->model('Vehicle');		
		$this->load->model('Trailer');		
		
		$this->userId = $this->session->loggedUser_id;
		$this->roleId = $this->session->role;	
		$this->data = array();
	}
	
	/*
	* Request URL: http://domain/trailers/index
	* Method: get
	* Params: null
	* Return: list array
	* Comment: Used for fetching trailers listing
	*/
	public function index() {
		$this->data['rows'] = $this->Trailer->getTrailersList();
		echo json_encode($this->data);
	}

	/*
	* Add Method for new trailer
	* @params null
	* @data form data
	* @return type null
	*/
	public function add(){
		$this->data['fetchTrucks'] = $this->Vehicle->fetchTruckListForTrailers();
		$this->data['trailerAddEdit'] = 'add';
		echo json_encode($this->data);
	}
	 
	/*
	* Request URL: http://domain/trailers/edit
	* Method: get
	* Params: trailerId
	* Return: trailer detail array
	* Comment: Used for fetching particular trailer detail
	*/
	
	public function edit( $trailerId = null ) {
		$this->data['fetchTrucks'] = $this->Vehicle->fetchTruckListForTrailers();
		$this->data['trailerData'] = $this->Trailer->getTrailerInfo( $trailerId );
		$this->data['trailerDocuments'] = $this->Trailer->fetchContractDocuments($trailerId, 'trailer');
		$this->data['trailerAddEdit'] = 'edit';
		echo json_encode($this->data);
	} 
	
	/**
	 * Saving and updating Trailer to db
	 */
	 
	public function addEditTrailer(){
		$_POST = json_decode(file_get_contents('php://input'), true);
		
		$postData = $this->input->post();
				
		if( isset($postData) && !empty($postData) ){
			$result = $this->Trailer->addEditTrailer();
			if ( $result ) 
				$success = true;
			else 
				$success = false;
				
			$this->data['success'] = $success;
			$this->data['lastInsertedId'] = $result;
		} 
		echo json_encode($this->data);
	} 
	
	/*
	* Request URL: http://domain/trailers/delete
	* Method: get
	* Params: trailerId
	* Return: true or false
	* Comment: Used for deleting trailer
	*/  
	public function delete( $trailerId = null ){
		$result = $this->Trailer->deleteTrailer($trailerId);
		if ( $result ) {
			$this->data['success'] = true;
		} else {
			$this->data['success'] = false;
		}
		echo json_encode($this->data);
	}
	
	/**
	 *  checking truck already assigned to another trailer or not
	 */ 
	 
	public function changeTruck( $truckId = null, $trailerId = null ) {
		$trailerUnit = '';
		$result = $this->Trailer->checkChangeTrailer($truckId,$trailerId);
		if ( !empty($result) ) {
			$res = false;
			$trailerUnit = $result['unit_id'];
		} else {
			$res = true;
		}
		echo json_encode(array('result' => $res, 'trailerUnit' => $trailerUnit));
	}
	
	/**
	 * Changing trailer Status
	 */ 
	public function changeStatus( $trailerId = null, $status = null ){
		$result = $this->Trailer->changeTrailerStatus($trailerId, $status);
		if ( $result ) {
			$this->data['status'] = true;
		} else {
			$this->data['status'] = false;
		}
		
		$this->data['rows'] = $this->Trailer->getTrailerInfo( $trailerId );
		echo json_encode($this->data);
	}
	
	/**
	 * Check trailer unit id exist or not
	 */
	
	public function checkTrailerUnit( $trailerNo = null, $id = null ) {
		if ( $id != '' && $id != null )
			$result = $this->Trailer->checkTrailerUnitExist($trailerNo, $id);
		else
			$result = $this->Trailer->checkTrailerUnitExist($trailerNo);
					
		if ( !empty($result) ) 
			$success = false;
		else
			$success = true;
		
		echo json_encode(array('success' => $success));
	} 
	
	/*
	* Request URL: http://domain/trailers/add or edit
	* Method: Post
	* Params: null
	* Return: success or error
	* Comment: Used for uploading trailers documents
	*/
	
	public function uploadContractDocs()
	{
		$prefix = "trailer"; 
	    $response  = array();
	    if(isset($_POST["trailerId"]) && $_POST["trailerId"] != ""){
			$response = $this->uploadContractDocsToServer($_FILES, $prefix, $prefix);	
			if(isset($response["error"]) && !$response["error"]){
					$docs = array(
						'document_name' => $response['data']['file_name'],
						'entity_type' => $prefix,
						'entity_id' => $_POST['trailerId']
					);
				$this->Trailer->insertContractDocument($docs);
				$response['trailerDocuments'] = $this->Trailer->fetchContractDocuments($_POST['trailerId'], 'trailer');
			}
		}
		echo json_encode($response);

	}
	
	/*
	* Request URL: http://domain/trailers/deletedocument
	* Method: get
	* Params: documentId, documentname
	* Return: success or error
	* Comment: Used for deleting drivers documents
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
		$fileName = $fileName.'.jpg';
		$thumbFile	 =  $pathGen.'assets/uploads/documents/thumb_trailer/thumb_'.$fileName;
		$filePath	 =  $pathGen.'assets/uploads/documents/trailer/'.$docName;

		if(file_exists($filePath)){
			unlink($filePath); 	
		}
		
		if(file_exists($thumbFile)){
			unlink($thumbFile); 	
		}
		
		$this->Trailer->removeContractDocs($docId);
		echo json_encode(array("success" => true));
	}
	
	
}
