<?php
class Brokers extends Admin_Controller {

	public $search;
	public $userId;
	public $roleId;
	public $data;

	function __construct()
	{ 
		parent::__construct();	
		$this->load->library('user_agent');
		$this->load->model('BrokersModel');
		
		if($this->session->role != 1){
			$this->userId = $this->session->loggedUser_id;
		}
		$this->roleId = $this->session->role;	
		$this->data = array();
	}

	/*
	* Request URL: http://domain/brokers/index
	* Method: Post
	* Params: null
	* Return: brokers list
	* Comment: Used for fetching saved brokers list
	*/
	
	public function index() {
		$this->data['rows'] = $this->BrokersModel->get_brokers();
		$this->data['total_records'] = count($this->data['rows']);
		foreach($this->data['rows']  as $key =>  $d ) {
			if ( $d['CarrierMC'] == 0 ) {
				$this->data['rows'][$key]['CarrierMC'] = '';
			}
			if ( $d['DOTNumber'] == 0 ) {
				$this->data['rows'][$key]['DOTNumber'] = '';
			}
			if ( $d['MCNumber'] == 0 ) {
				$this->data['rows'][$key]['MCNumber'] = '';
			}
		}
		echo json_encode($this->data);
	}

	public function edit( $broker_id = Null ){
		$this->data['brokerData'] = $this->BrokersModel->getRelatedBroker($broker_id);
		if($this->data['brokerData']['CarrierMC'] == 0){
			$this->data['brokerData']['CarrierMC'] = '';
		}
		if($this->data['brokerData']['DOTNumber'] == 0){
			$this->data['brokerData']['DOTNumber'] = '';
		}
		if($this->data['brokerData']['MCNumber'] == 0){
			$this->data['brokerData']['MCNumber'] = '';
		}
		$this->data['brokerDocuments'] = $this->BrokersModel->fetchContractDocuments($broker_id, 'broker');
		echo json_encode($this->data);
	}
	
	public function update() {
		$_POST = json_decode(file_get_contents('php://input'), true);
		$id = $_POST['id'];
       	$result = $this->BrokersModel->add_update_broker($_POST, $id);
		if($result){
			echo json_encode(array('success' => true));
		}
		else{
			echo json_encode(array('success' => false));
		}
	}
	
	public function addBroker() {
		$_POST = json_decode(file_get_contents('php://input'), true);
		
		$result = $this->BrokersModel->add_update_broker($_POST);
		
		if(!empty($result) && $result[0] =='yes' ){
			echo json_encode(array('success' => true,'update'=>true, 'lastInsertId' => $result[1]));
		} else {
			echo json_encode(array('success' => true,'update'=>false, 'lastInsertId' => $result[1]));
		}
	}
	
	public function delete($brokerID=null){
		$result = $this->BrokersModel->deleteBrocker($brokerID);
		if ( $result ) {
			echo json_encode(array('success' => true));
		} else {
			echo json_encode(array('success' => false));
		}
		
	}
	
	/*
	* Request URL: http://domain/brokers/blacklistbroker
	* Method: get
	* Params: brokerId, status
	* Return: status
	* Comment: Used for blacklist broker
	*/
	public function blackListBroker( $brokerId = null, $status = null ) {
		$result = $this->BrokersModel->changeBlackListStatus( $brokerId, $status );
		if ( $result ) {
			$status = true;
		} else {
			$status = false;
		}
		
		$this->data['brokerData'] = $this->BrokersModel->getRelatedBroker($brokerId);
		if($this->data['brokerData']['CarrierMC'] == 0){
			$this->data['brokerData']['CarrierMC'] = '';
		}
		if($this->data['brokerData']['DOTNumber'] == 0){
			$this->data['brokerData']['DOTNumber'] = '';
		}
		if($this->data['brokerData']['MCNumber'] == 0){
			$this->data['brokerData']['MCNumber'] = '';
		}
		
		echo json_encode(array('records' => $this->data, 'status' => $status ));
	} 
	
	
	/**
	 * Fetching broker uploaded document on job ticket
	 */ 
	
	public function getBrokerDocumentUploaded( $brokerId = null ) {
		$result = $this->BrokersModel->fetchContractDocuments($brokerId, 'broker');
		if ( !empty($result) ) {
			for( $i = 0; $i < count($result); $i++ ) {
				$fileNameArray = explode('.',$result[$i]['document_name']);
				$fileName = '';
				for ( $j = 0; $j < count($fileNameArray) - 1; $j++ ) {
					$fileName .= $fileNameArray[$j];
				}
				$fileName = 'thumb_'.$fileName.'.jpg';
				
				$this->data['brokerDocuments'][$i]['doc_name'] = $result[$i]['document_name'];
				$this->data['brokerDocuments'][$i]['thumb_doc_name'] = $fileName;
				$this->data['brokerDocuments'][$i]['id'] = $result[$i]['id'];
				$this->data['brokerDocuments'][$i]['BrokerId'] = $brokerId;
			}
		}
		echo json_encode($this->data);
	}
	
	/*
	* Request URL: http://domain/brokers/add or edit
	* Method: Post
	* Params: null
	* Return: success or error
	* Comment: Used for uploading brokers documents
	*/
	
	public function uploadContractDocs()
	{
		$prefix = "broker"; 
	    $response  = array();
	    if(isset($_POST["brokerId"]) && $_POST["brokerId"] != ""){
			$response = $this->uploadContractDocsToServer($_FILES, $prefix, $prefix);	
			if(isset($response["error"]) && !$response["error"]){
					$docs = array(
						'document_name' => $response['data']['file_name'],
						'entity_type' => $prefix,
						'entity_id' => $_POST['brokerId']
					);
				
				$this->BrokersModel->insertContractDocument($docs);
				$response['docList'] = $this->BrokersModel->fetchContractDocuments($_POST['brokerId'], 'broker');
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
		$thumbFile	 =  $pathGen.'assets/uploads/documents/thumb_broker/thumb_'.$fileName;
		$filePath	 =  $pathGen.'assets/uploads/documents/broker/'.$docName;

		if(file_exists($filePath)){
			unlink($filePath); 	
		}
		
		if(file_exists($thumbFile)){
			unlink($thumbFile); 	
		}
		
		$this->BrokersModel->removeContractDocs($docId);
		echo json_encode(array("success" => true));
	}
}
