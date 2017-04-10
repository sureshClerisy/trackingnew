<?php

class Shippers extends Admin_Controller {

	function __construct(){

		parent::__construct();	
		$this->load->library('user_agent');
		$this->load->model(array('Shipper'));
		$this->load->model(array('BrokersModel',"Job"));
		$this->load->helper('truckstop_helper');
		$this->roleId   = $this->session->role;	
		$this->data 	= array();
		$this->userName = $this->session->loggedUser_username;

		if($this->session->role != 1){
			$this->userId = $this->session->loggedUser_id;
		}
	}

	public function index(){

		$this->data['rows'] = $this->Shipper->get_shippers();
		$this->data['total_records'] = count($this->data['rows']);
		echo json_encode($this->data);
	}	

	public function addShipper(){
		
		$postData = json_decode(file_get_contents('php://input'), true);
		$resposne = $this->Shipper->insertUpdateShipper($postData['data']);
		echo json_encode(array('success' => true,'update'=>'no','lastInsertId'=>$resposne[1]));
	}

	public function update( $shipperId = Null ){
		
		if($this->input->server('REQUEST_METHOD') =='POST'){
			
			$postData = json_decode(file_get_contents('php://input'), true);
			$this->Shipper->insertUpdateShipper($postData);
			echo json_encode(array('success' => true));
			return;
		}

		$this->data['brokerData'] = $this->Shipper->getshippersById($shipperId);
		$this->data['brokerDocuments'] = $this->BrokersModel->fetchContractDocuments($shipperId, 'shipper');
		echo json_encode($this->data);
	}

	public function deleteContractDocs($docId = null, $docName = '', $srcPage = '') {
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
			$filePath	 =  $pathGen.'assets/uploads/documents/shipper/'.$docName;

			if(file_exists($filePath)){
				unlink($filePath); 	
			}
			
			if(file_exists($thumbFile)){
				unlink($thumbFile); 	
			}

			$shipperInfo = $this->BrokersModel->getEntityInfoByDocId($docId,$this->entity["shipper"]);
			
			$this->BrokersModel->removeContractDocs($docId);

			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted a document ('.$shipperInfo["document_name"].') from shipper <a class="notify-link" href="'.$this->serverAddr.'#/editshipper/'.$shipperInfo["id"].'"> '.$shipperInfo["TruckCompanyName"].'</a>';
			logActivityEvent($shipperInfo['id'], $this->entity["shipper"], $this->event["remove_doc"], $message, $this->Job, $srcPage);	
			echo json_encode(array("success" => true));

		}catch(Exception $e){
			log_message('error','DELETE_SHIPPER_DOC'.$e->getMessage());
			echo json_encode(array("success" => false));
		}
	}


    public function uploadContractDocs(){

        $prefix = "shipper"; 
        $response  = array();

        if(isset($_POST["brokerId"]) && $_POST["brokerId"] != ""){
            $response = $this->uploadContractDocsToServer($_FILES, $prefix, $prefix);   
            if(isset($response["error"]) && !$response["error"]){
                    $docs = array(
                        'document_name' => $response['data']['file_name'],
                        'entity_type' 	=> $prefix,
                        'entity_id' 	=> $_POST['brokerId']
                    );
                try{
                    $this->BrokersModel->insertContractDocument($docs);

                    $response['docList'] = $this->BrokersModel->fetchContractDocuments($_POST['brokerId'], 'shipper');
                    $brokerInfo 		 = $this->BrokersModel->getBrokerInfo($_POST['brokerId']);

                    $message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> uploaded a new document ('.$docs["document_name"].') for shipper <a class="notify-link" href="'.$this->serverAddr.'#/editbroker/'.$brokerInfo["id"].'">'.ucfirst($brokerInfo["TruckCompanyName"]).'</a>';
                    logActivityEvent($brokerInfo['id'], $this->entity["broker"], $this->event["upload_doc"], $message, $this->Job);
                }catch(Exception $e){
                    log_message('error','UPLOAD_SHIPPER_DOC'.$e->getMessage());
                }
            }
        }
        echo json_encode($response);
    }

    /**
	* Method delete
	* @param shipper ID
	* @return Boolean
    */

    public function delete($shipperID=null){
		try{

			$brockerOldData = $this->Shipper->getBrokerInfo($shipperID);
			$result 		= $this->Shipper->deleteShipper($shipperID);

			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted the shipper <a class="notify-link" href="'.$this->serverAddr.'#/editshipper/'.$shipperID.'">'.$brockerOldData["TruckCompanyName"]."</a>";
			logActivityEvent($shipperID, $this->entity["broker"], $this->event["delete"], $message, $this->Job);		
			echo json_encode(array('success' => true));

		}catch(Exception $e){
			log_message('error', 'DELETE_SHIPPER'.$e->getMessage());
			echo json_encode(array("success" => false));
		}

		
	}
}	