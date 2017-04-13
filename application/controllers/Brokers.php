<?php
class Brokers extends Admin_Controller {

	public $search;
	public $userId;
	public $roleId;
	public $data;
	public $userName;

	function __construct()
	{ 
		parent::__construct();	
		$this->load->library('user_agent');
		$this->load->model(array('BrokersModel',"Job"));
		$this->load->helper('truckstop_helper');

		$this->roleId   = $this->session->role;	
		$this->data 	= array();
		$this->userName = $this->session->loggedUser_username;

		if($this->session->role != 1){
			$this->userId = $this->session->loggedUser_id;
		}
		
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


	/*
	* Request URL: http://domain/brokers/update
	* Method: post
	* Params: null
	* Return: array
	* Comment: Used for update broker
	*/
	
	public function update() 
	{
		try{

			$_POST = json_decode(file_get_contents('php://input'), true);
			$id = $_POST['id'];
			$brockerOldData = $this->BrokersModel->getBrokerInfo($id);
	       	$result = $this->BrokersModel->add_update_broker($_POST, $id);
			$editedFields = array_diff_assoc($_POST,$brockerOldData);
			
			if(count($editedFields) > 0){
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited a broker <a class="notify-link" href="'.$this->serverAddr.'#/editbroker/'.$id.'">'.$brockerOldData["TruckCompanyName"]."</a>.";
				foreach ($editedFields as $key => $value) {
					$prevField = isset($brockerOldData[$key]) ? $brockerOldData[$key] : "" ;
					if(in_array($key, array("delete_broker","PointOfContact","PointOfContactPhone","TruckCompanyEmail","TruckCompanyPhone","TruckCompanyFax"))){
						continue;
					}
					if(!empty($prevField)){
						$message.= "<br/> - Changed ".ucwords(str_replace("_"," ",$key))." from <i>".$prevField."</i> to <i>".$value."</i>";
					}else{
						$message.= "<br/> - Added  <i>".$value."</i> to ".ucwords(str_replace("_"," ",$key));
					}
				}
			}else{
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited a broker <a class="notify-link" href="'.$this->serverAddr.'#/editbroker/'.$id.'">'.$brockerOldData["TruckCompanyName"].'</a>, But changed nothing.';
			}
			logActivityEvent($brockerOldData["id"], $this->entity["broker"], $this->event["edit"], $message, $this->Job);	
			echo json_encode(array('success' => true));

		}catch(Exception $e){
			log_message('error','EDIT_VEHICLE'.$e->getMessage());
			echo json_encode(array('success' => false));
		}
	}
	

	/*
	* Request URL: http://domain/brokers/addBroker
	* Method: post
	* Params: null
	* Return: array
	* Comment: Used for add/edit broker
	*/

	public function addBroker() 
	{
		try{

			$_POST = json_decode(file_get_contents('php://input'), true);
			$srcPage = $_POST["srcPage"];
			$_POST = $_POST["data"];
			$brockerOldData = $this->BrokersModel->getBrokerInfoByMCNumber($_POST["MCNumber"]);
			$result = $this->BrokersModel->add_update_broker($_POST);
			$type = "add";
			if(!empty($result) && $result[0] =='yes' ){
				$editedFields = array_diff_assoc($_POST,$brockerOldData);
				$type = "edit";
				if(count($editedFields) > 0){
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited a broker <a class="notify-link" href="'.$this->serverAddr.'#/editbroker/'.$result[1].'">'.$_POST["TruckCompanyName"]."</a>.";
					foreach ($editedFields as $key => $value) {
						$prevField = isset($brockerOldData[$key]) ? $brockerOldData[$key] : "" ;
						if(in_array($key, array("PointOfContact","PointOfContactPhone","TruckCompanyEmail","TruckCompanyPhone","TruckCompanyFax"))){
							continue;
						}
						if(!empty($prevField)){
							$message.= "<br/> - Changed ".ucwords(str_replace("_"," ",$key))." from <i>".$prevField."</i> to <i>".$value."</i>";
						}else{
							$message.= "<br/> - Added  <i>".$value."</i> to ".ucwords(str_replace("_"," ",$key));
						}
					}
				}else{
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited a broker <a class="notify-link" href="'.$this->serverAddr.'#/editbroker/'.$result[1].'">'.$_POST["TruckCompanyName"].'</a>, But changed nothing.';
				}
				logActivityEvent($result[1], $this->entity["broker"], $this->event["edit"], $message,  $this->Job, $srcPage);	
				echo json_encode(array('success' => true,'update'=>true, 'lastInsertId' => $result[1]));
			} else {
				$type = "add";
				$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> added a new broker <a class="notify-link" href="'.$this->serverAddr.'#/editbroker/'.$result[1].'">'.$_POST["TruckCompanyName"]."</a>";
				logActivityEvent($result[1], $this->entity["shipper"], $this->event["add"], $message, $this->Job, $srcPage);	
				echo json_encode(array('success' => true,'update'=>false, 'lastInsertId' => $result[1]));
			}
		}catch(Exception $e){
			log_message('error', strtoupper($type).'_BROKER'.$e->getMessage());
			echo json_encode(array("success" => false));
		}
	}
	
	/*
	* Request URL: http://domain/brokers/delete
	* Method: post
	* Params: brokerID
	* Return: array
	* Comment: Used for delete broker info
	*/

	public function delete($brokerID=null){
		try{

			$brockerOldData = $this->BrokersModel->getBrokerInfo($brokerID);
			$result = $this->BrokersModel->deleteBrocker($brokerID);
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted the broker <a class="notify-link" href="'.$this->serverAddr.'#/editbroker/'.$brokerID.'">'.$brockerOldData["TruckCompanyName"]."</a>";
			logActivityEvent($brokerID, $this->entity["broker"], $this->event["delete"], $message, $this->Job);		
			echo json_encode(array('success' => true));

		}catch(Exception $e){
			log_message('error', 'DELETE_BROKER'.$e->getMessage());
			echo json_encode(array("success" => false));
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
		try{
			$requestedStatus = ($status == 0) ? "Blacklisted" : "Whitelisted";
			$result = $this->BrokersModel->changeBlackListStatus( $brokerId, $status );
			
			if ( $result ) { $status = true; } else { $status = false; }

			
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

			$message = '<span class="blue-color uname">'.ucfirst($this->userName)."</span> changed the status to <i>".$requestedStatus.'</i> of  broker <a class="notify-link" href="'.$this->serverAddr.'#/editbroker/'.$brokerId.'"> '.$this->data['brokerData']["TruckCompanyName"].'</a>';
			logActivityEvent($brokerId, $this->entity["broker"], $this->event["status_change"], $message, $this->Job);
			echo json_encode(array('records' => $this->data, 'status' => $status ));

		}catch(Exception $e){
			log_message('error','CHANGE_BROKER_STATUS'.$e->getMessage());
			echo json_encode(array('success' => false));
		}
	} 
	
	
	/**
	 * Fetching broker uploaded document on job ticket
	 */ 
	
	public function getBrokerShipperDocumentUploaded( $brokerId = null, $type = '' ) {
		$result = $this->BrokersModel->fetchContractDocuments($brokerId, $type);
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
				$this->data['brokerDocuments'][$i]['billType'] = $type;
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
				try{
					$this->BrokersModel->insertContractDocument($docs);
					$response['docList'] = $this->BrokersModel->fetchContractDocuments($_POST['brokerId'], 'broker');

					$brokerInfo = $this->BrokersModel->getBrokerInfo($_POST['brokerId']);
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> uploaded a new document ('.$docs["document_name"].') for broker <a class="notify-link" href="'.$this->serverAddr.'#/editbroker/'.$brokerInfo["id"].'">'.ucfirst($brokerInfo["TruckCompanyName"]).'</a>';
					logActivityEvent($brokerInfo['id'], $this->entity["broker"], $this->event["upload_doc"], $message, $this->Job);
				}catch(Exception $e){
					log_message('error','UPLOAD_BROKER_DOC'.$e->getMessage());
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
	public function deleteContractDocs($docId = null, $docName = '', $srcPage = '')
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
			$thumbFile	 =  $pathGen.'assets/uploads/documents/thumb_broker/thumb_'.$fileName;
			$filePath	 =  $pathGen.'assets/uploads/documents/broker/'.$docName;

			if(file_exists($filePath)){
				unlink($filePath); 	
			}
			
			if(file_exists($thumbFile)){
				unlink($thumbFile); 	
			}

			$brokerInfo = $this->BrokersModel->getEntityInfoByDocId($docId,$this->entity["broker"]);
			$this->BrokersModel->removeContractDocs($docId);
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted a document ('.$brokerInfo["document_name"].') from broker <a class="notify-link" href="'.$this->serverAddr.'#/editbroker/'.$brokerInfo["id"].'"> '.$brokerInfo["TruckCompanyName"].'</a>';
			logActivityEvent($brokerInfo['id'], $this->entity["broker"], $this->event["remove_doc"], $message, $this->Job, $srcPage);	
			echo json_encode(array("success" => true));

		}catch(Exception $e){
			log_message('error','DELETE_BROKER_DOC'.$e->getMessage());
			echo json_encode(array("success" => false));
		}
	}

	public function fetchDataForCsv() {
		$content = '';
		$searchText 	= json_decode(file_get_contents('php://input'), true);
		$keys 	= [['TruckCompanyName','PointOfContact','PointOfContactPhone','TruckCompanyEmail','TruckCompanyPhone','TruckCompanyFax','PostingAddress','City','State','Zipcode','MCNumber','CarrierMC','DOTNumber','BrokerStatus','DebtorKey','Black List','Rating','Delete Broker']];

		$dataRow = $this->BrokersModel->fetchDriversForCSV($searchText);
		
		foreach ($dataRow as $key => $value) {
			
			unset($dataRow[$key]['id']);
			$dataRow[$key]['CarrierMC'] = ($value['CarrierMC'] == 0 )?'NA':$value['CarrierMC'];
			$dataRow[$key]['DOTNumber'] = ($value['DOTNumber'] == 0 )?'NA':$value['DOTNumber'];
			$dataRow[$key]['MCNumber']  = ($value['MCNumber'] == 0 )?'NA':$value['MCNumber'];
			$dataRow[$key]['black_list'] = ($value['black_list'] == 0 )?'No':'Yes';
			$dataRow[$key]['delete_broker'] = ($value['delete_broker'] == 0 )?'No':'Yes';
		}

		$data 	= array_merge($keys,$dataRow);
		echo json_encode(array('fileName'=>$this->createExcell('brokers',$data)));
	}
}