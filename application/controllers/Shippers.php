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

		$this->itemsPerPage = $this->config->item('limit_per_page');
		if($this->session->role != 1){
			$this->userId = $this->session->loggedUser_id;
		}
	}

	/*
	* Request URL: http://domain/shippers/index
	* Method: get
	* Params: null
	* Return: list array
	* Comment: Used for fetching shippers listing
	*/

	public function index(){
		if ( isset($_POST) && count($_POST) > 0 ) {
			$filters = $_POST;
			if($filters["pageNo"] < 1){
				$filters["limitStart"] = ($filters["pageNo"] * $this->itemsPerPage + 1);	
			}else{
				$filters["limitStart"] = ($filters["pageNo"] * $this->itemsPerPage );	
			}
			$filters['itemsPerPage'] = $this->itemsPerPage;
		} else {
			$filters = array(
				"itemsPerPage" => $this->itemsPerPage,
				"limitStart" => 1, 
				"sortColumn" => "id", 
				"sortType" => "DESC"
			);
		}

		$this->data['rows']  = $this->Shipper->getShippersList( $filters, false );
		$this->data['total'] = $this->Shipper->getShippersList( $filters, true );
		$this->data['total'] = $this->data['total'][0]['totalRows'];
		echo json_encode($this->data);
	}	

	public function addShipper(){
		try{
			$postData = $_POST;
			$resposne = $this->Shipper->insertUpdateShipper($postData['data']);
			$srcPage = $postData['srcPage'];
			$type = "add";
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> added a new shipper <a class="notify-link" href="'.$this->serverAddr.'#/editshipper/'.$resposne[1].'">'.$postData['data']["shipperCompanyName"]."</a>";
			logActivityEvent($resposne[1], $this->entity["shipper"], $this->event["add"], $message, $this->Job, $srcPage);	
			echo json_encode(array('success' => true,'update'=>'no','lastInsertId'=>$resposne[1]));
		} catch(Exception $e){
			log_message('error', strtoupper($type).'_SHIPPER'.$e->getMessage());
			echo json_encode(array("success" => false));
		}
	}

	public function update( $shipperId = Null ){
		
		if($this->input->server('REQUEST_METHOD') =='POST'){
			try{
				$postData = json_decode(file_get_contents('php://input'), true);
				$shipperOldData = $this->Shipper->getshippersById($postData['id']);
		       	$this->Shipper->insertUpdateShipper($postData);
				$editedFields = array_diff_assoc($postData,$shipperOldData);
				
				if(count($editedFields) > 0){
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited a shipper <a class="notify-link" href="'.$this->serverAddr.'#/editshipper/'.$postData['id'].'">'.$shipperOldData["shipperCompanyName"]."</a>.";
					foreach ($editedFields as $key => $value) {
						$prevField = isset($shipperOldData[$key]) ? $shipperOldData[$key] : "" ;
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
					$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> edited a shipper <a class="notify-link" href="'.$this->serverAddr.'#/editshipper/'.$postData['id'].'">'.$shipperOldData["shipperCompanyName"].'</a>, But changed nothing.';
				}
				logActivityEvent($shipperOldData["id"], $this->entity["shipper"], $this->event["edit"], $message, $this->Job);	
				echo json_encode(array('success' => true));
				return;
			} catch(Exception $e){
				log_message('error','EDIT_VEHICLE'.$e->getMessage());
				echo json_encode(array('success' => false));
				return;
			}
		}

		$this->data['brokerData'] = $this->Shipper->getshippersById($shipperId);
		$this->data['brokerDocuments'] = $this->Shipper->fetchContractDocuments($shipperId, 'shipper');
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
			$thumbFile	 =  $pathGen.'assets/uploads/documents/thumb_shipper/thumb_'.$fileName;
			$filePath	 =  $pathGen.'assets/uploads/documents/shipper/'.$docName;

			if(file_exists($filePath)){
				unlink($filePath); 	
			}
			
			if(file_exists($thumbFile)){
				unlink($thumbFile); 	
			}

			$shipperInfo = $this->Shipper->getEntityInfoByDocId($docId,$this->entity["shipper"]);
			
			$this->Shipper->removeContractDocs($docId);

			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted a document ('.$shipperInfo["document_name"].') from shipper <a class="notify-link" href="'.$this->serverAddr.'#/editshipper/'.$shipperInfo["id"].'"> '.$shipperInfo["shipperCompanyName"].'</a>';
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
                    $this->Shipper->insertContractDocument($docs);

                    $response['docList'] = $this->Shipper->fetchContractDocuments($_POST['brokerId'], 'shipper');
                    $brokerInfo 		 = $this->Shipper->getShipperInfo($_POST['brokerId']);

                    $message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> uploaded a new document ('.$docs["document_name"].') for shipper <a class="notify-link" href="'.$this->serverAddr.'#/editshipper/'.$brokerInfo["id"].'">'.ucfirst($brokerInfo["shipperCompanyName"]).'</a>';
                    logActivityEvent($brokerInfo['id'], $this->entity["shipper"], $this->event["upload_doc"], $message, $this->Job);
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
			$brockerOldData = $this->Shipper->getShipperInfo($shipperID);
			$result 		= $this->Shipper->deleteShipper($shipperID);

			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> deleted the shipper <a class="notify-link" href="'.$this->serverAddr.'#/editshipper/'.$shipperID.'">'.$brockerOldData["shipperCompanyName"]."</a>";
			logActivityEvent($shipperID, $this->entity["shipper"], $this->event["delete"], $message, $this->Job);		
			echo json_encode(array('success' => true));

		}catch(Exception $e){
			log_message('error', 'DELETE_SHIPPER'.$e->getMessage());
			echo json_encode(array("success" => false));
		}		
	}

	/**
	* changing the shipper status
	*/

	public function changeStatus($shipperId = null, $status = false) 
	{
		try{
			$requestedStatus = ($status == 1) ? 'Deactive' : 'Active';
			$result = $this->Shipper->changeShipperStatus($shipperId, $status);
			
			if ( $result ) { 
				$status = true; 
			} else { 
				$status = false; 
			}

			$data['rows'] = $shipperInfo = $this->Shipper->fetchShipperInfo($shipperId);
			$message = '<span class="blue-color uname">'.ucfirst($this->userName).'</span> changed the status to <i>'.$requestedStatus.'</i> of shipper  <a class="notify-link" href="'.$this->serverAddr.'#/editShipper/'.$shipperId.'">'.ucfirst($shipperInfo["shipperCompanyName"]).'</a>.';
			logActivityEvent($shipperId, $this->entity["shipper"], $this->event["status_change"], $message, $this->Job);	
			
			echo json_encode(array('records' => $data, 'status' => $status ));	
		}catch(Exception $e){
			log_message('error','CHANGE_DRIVER_STATUS'.$e->getMessage());
			echo json_encode(array('success' => false));
		}
	}

	/**
	* fetch list of us states
	*/

	public function fetchUsStates() {
		$states_data = $this->Job->getAllStates();
		echo json_encode($states_data);
	}

	public function fetchDataForCsv() {
		
		$postObj 	= json_decode(file_get_contents("php://input"),true);
		$keys = [['Company Name','PointOfContact','PointOfContactPhone','TruckCompanyEmail','TruckCompanyPhone','TruckCompanyFax','postingAddress','City','State','Zipcode','Status','Rating','Deleted']];

		$dataRow  	= $this->Shipper->exportShippers($postObj);
		
		foreach ($dataRow as $key => $value) {
			
			unset($dataRow[$key]['id']);
			$dataRow[$key]['deleted'] = ($value['deleted']==1)?'Yes':'No';
			$dataRow[$key]['status']  = ($value['status']==1)?'Active':'In-Active';
		}

		$data 		= array_merge($keys,$dataRow);
		echo json_encode(array('fileName'=>$this->createExcell('brokers',$data)));
	}
	/**
	* fetch shipper info based on id
	*/

	public function getshipperListById($shipperId = null ) {
		$result['shipperData'] = $this->Shipper->getshippersById($shipperId);
		echo json_encode($result);
	}
}