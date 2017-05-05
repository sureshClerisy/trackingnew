<?php
class Trailers extends Admin_Controller {

	public $search;
    public $userId;
    public $roleId;
    public $userName;
    public $data;
    public $table = 'trailers';
    
    function __construct()
    { 
        parent::__construct();    
        $this->load->model(array('Trailer','Vehicle',"Job"));

        
        $this->userId = $this->session->loggedUser_id;
        $this->roleId = $this->session->role;    
        $this->userName = $this->session->loggedUser_username;
        $this->load->helper('truckstop_helper');        
        $this->data = array();
    }
	
	/**
	* Request URL: http://domain.com/api/trailers/index
	* Method  : get
	* @param  : null
	* @return : list array
	* Comment : Used for fetching trailers listing
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
		$this->data['fetchTrucks'] 		= $this->Vehicle->fetchTruckListForTrailers();
		$this->data['trailerAddEdit'] 	= 'add';
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

		$this->checkOrganisationIsValid($trailerId,$this->table);

		$this->data['fetchTrucks'] = $this->Vehicle->fetchTruckListForTrailers();
		$this->data['trailerData'] = $this->Trailer->getTrailerInfo( $trailerId );
		$this->data['trailerDocuments'] = $this->Trailer->fetchContractDocuments($trailerId, 'trailer');
		$this->data['trailerAddEdit'] = 'edit';
		echo json_encode($this->data);
	} 
	


	/*
	* Request URL: http://domain/trailers/addEditTrailer
	* Method: post
	* Params: submitType
	* Return: array
	* Comment: Used for add or edit the trailer record
	*/

	public function skipAcl_addEditTrailer($submitType = 'add')
	{
		try{

			$_POST = json_decode(file_get_contents('php://input'), true);
			$postData = $this->input->post(); $message = "";

			if( isset($postData) && !empty($postData) ){
				
				if($submitType == "add"){
					$result = $this->Trailer->addEditTrailer();
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> added a new trailer <a class="notify-link" href="'.$this->serverAddr.'#/editTrailer/'.$result.'"> Trailer - '.$postData["unit_id"].'</a>';
					logActivityEvent($result, $this->entity["trailer"], $this->event["add"], $message, $this->Job);	
				}else{

					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited a trailer <a class="notify-link" href="'.$this->serverAddr.'#/editTrailer/'.$postData["id"].'"> Trailer - '.$postData["unit_id"].'</a>';
					$trailerOldData = $this->Trailer->getTrailerInfo($postData["id"]);
					$trailerOldData["monthly_payment"] = money_format('%.2n', (float)$trailerOldData["monthly_payment"]);
					$trailerOldData["purchase_price"] = money_format('%.2n', (float)$trailerOldData["purchase_price"]);
					$editedFields = array_diff_assoc($postData,$trailerOldData);

					if(count($editedFields) > 0){
						$skipFlag = false;
						foreach ($editedFields as $key => $value) {
							$prevField = isset($trailerOldData[$key]) ? $trailerOldData[$key] : "" ;

							if($key == "truck_id"){
								$key = "truck_number";
								$prevField = $trailerOldData["truckName"];
								$value = $editedFields["truckName"];
							}
							if($key == "truckName"){ continue; }

							if(!empty($prevField)){
								$message.= "<br/> - Changed ".ucwords(str_replace("_"," ",$key))." from <i>".$prevField."</i> to <i>".$value."</i>";
							}else{
								$message.= "<br/> - Added  <i>".$value."</i> to ".ucwords(str_replace("_"," ",$key));
							}
							
						}
					}else{
						$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited a trailer <a class="notify-link" href="'.$this->serverAddr.'#/editTrailer/'.$postData["id"].'"> Trailer - '.$postData["unit_id"].'</a>, But changed nothing.';
					}

					$result = $this->Trailer->addEditTrailer();
					logActivityEvent($result, $this->entity["trailer"], $this->event["edit"], $message, $this->Job);
				}

				if ( $result )  { $success = true; } else { $success = false; }
				$this->data['success'] = $success;
				$this->data['lastInsertedId'] = $result;
			} 

			echo json_encode($this->data);
		}catch(Exception $e){
			log_message('error', strtoupper($submitType).'_TRAILER'.$e->getMessage());
			echo json_encode(array("success" => false));
		}
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
	 
	public function skipAcl_changeTruck( $truckId = null, $trailerId = null ) {
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
	
	/*
	* Request URL: http://domain/trailers/changeStatus
	* Method: Post
	* Params: $trailerId , $status 
	* Return: array
	* Comment: Used for uploading trailers documents
	*/
	
	public function changeStatus( $trailerId = null, $status = null ){
		try{
			$requestedStatus = ($status == 1) ? "Deactivated" : "Activated";
			$result = $this->Trailer->changeTrailerStatus($trailerId, $status);
			if ( $result ) {
				$this->data['status'] = true;
			} else {
				$this->data['status'] = false;
			}
			
			$this->data['rows'] = $this->Trailer->getTrailerInfo( $trailerId );

			$message = '<span class="blue-color uname">'.ucfirst($this->userName)."</span> changed the status to <i>".$requestedStatus.'</i> of  <a class="notify-link" href="'.$this->serverAddr.'#/editTrailer/'.$trailerId.'"> Trailer - '.$this->data['rows']["unit_id"].'</a>';
			logActivityEvent($trailerId, $this->entity["trailer"], $this->event["status_change"], $message, $this->Job);	
			echo json_encode($this->data);

		}catch(Exception $e){
			log_message('error','CHANGE_TRAILER_STATUS'.$e->getMessage());
			echo json_encode(array('success' => false,"rows"=>array()));
		}
	}
	
	/**
	 * Check trailer unit id exist or not
	 */
	
	public function skipAcl_checkTrailerUnit( $trailerNo = null, $id = null ) {
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
	
	public function skipAcl_uploadContractDocs()
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
				try{
					
					$this->Trailer->insertContractDocument($docs);
					$response['trailerDocuments'] = $this->Trailer->fetchContractDocuments($_POST['trailerId'], 'trailer');
					$trailerInfo = $this->Trailer->getTrailerInfo($_POST['trailerId']);
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> uploaded a new document ('.$docs["document_name"].') for <a class="notify-link" href="'.$this->serverAddr.'#/editTrailer/'.$trailerInfo["id"].'"> Trailer - '.$trailerInfo["unit_id"].'</a>';
					logActivityEvent($trailerInfo['id'], $this->entity["trailer"], $this->event["upload_doc"], $message, $this->Job);

				}catch(Exception $e){
					log_message('error','UPLOAD_TRAILER_DOC'.$e->getMessage());
				}
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
		try{
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
			
			$trailerInfo = $this->Trailer->getEntityInfoByDocId($docId,$this->entity["trailer"]);
			$this->Trailer->removeContractDocs($docId);
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted a document ('.$trailerInfo["document_name"].') from <a class="notify-link" href="'.$this->serverAddr.'#/editTrailer/'.$trailerInfo["id"].'"> Trailer - '.$trailerInfo["unit_id"].'</a>';
			logActivityEvent($trailerInfo['id'], $this->entity["trailer"], $this->event["remove_doc"], $message, $this->Job);	

			echo json_encode(array("success" => true));
		}catch(Exception $e){
			log_message('error','DELETE_TRAILER_DOC'.$e->getMessage());
			echo json_encode(array("success" => false));
		}
	}

	public function fetchDataForCsv() {

		$data 		= array();
		$content 	= '';
        $searchText = json_decode(file_get_contents('php://input'), true);
		$keys 	 	= [['Truck ID','Unit ID','VIN','Owner','Type','Description','Monthly payment','Due date','Purchase price','Interest rate','Notes','Status','Created']];

		$dataRow = $this->Trailer->fetchDriversForCSV($searchText);
		
		$data 	 = array_merge($keys,$dataRow);
		echo json_encode(array('fileName'=>$this->createExcell('trailers',$data)));
	}
}