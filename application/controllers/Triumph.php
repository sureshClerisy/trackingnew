<?php

class Triumph extends Admin_Controller {
    public $triuser;
    public $password;
    public $apikey;
    public $token;
    public $data;

    public function __construct(){
        parent::__construct();
       
        $this->load->helper('cookie');
        $this->triuser  = $this->config->item('triumph_user');
        $this->password = $this->config->item('triumph_pass');
        $this->apikey   = $this->config->item('triumph_apik');
        $this->load->model('Job');
        $this->load->model('BrokersModel');
        $this->data = array();
    }

    public function index($mc_number = null, $usDot = null, $parameter = ''){
		$mc_number = (string)$mc_number;
		if ( strlen($mc_number) < 6 ) {
			$mc_number = str_pad($mc_number, 6, "0", STR_PAD_LEFT);
		}
			
		if ( $mc_number == '' || $mc_number == null ) {
			$response = array();
			echo json_encode($response);
		} else {
			$token = $this->get_sessionToken();
			$docket_type = 'MC';
	
			$c = curl_init('https://api.mytriumph.com/v1Credit/SearchCredit');
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, "docket_number={$mc_number}&docket_type={$docket_type}");
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_COOKIE, "SessionToken={$token->SessionToken}");
			$page = curl_exec($c);
			curl_close($c);
		   
			$datalist = json_decode($page);
			$detailInfo = $this->getcredittypeinfo($datalist->creditResultTypeId,$token->SessionToken);
			$datalist->creditResultTypeId = $detailInfo;
		
			if ( $parameter == 'sameController' ) {				// sameController parameter to return value to another function
				return $datalist;
			} 
			
			$response = array('Error'=>true,'ErrorMessage'=>'Broker Information not found');
			if(!empty($datalist)){
				$dataExist = array();
				if( $parameter != 'addBroker' )					// hit request of v1list customers in case of addbroker
					$dataExist = $this->checkTriumphExist($mc_number);
					
				if ( !empty($dataExist) ) {
					$datalist->newMethodInfo = $dataExist;
				} else {
					//~ $cmpName = $datalist->companyName;
					//~ if ( $cmpName != '' ) {
						$customerResult = $this->getCustomersData($mc_number,$token->SessionToken);
						$datalist->newMethodInfo = $customerResult;
					//~ }
				}
				
				//~ if ( $parameter == 'sameCont' ) {
					//~ return $datalist;
				//~ } else {			
					echo json_encode($datalist);
					exit();
				//~ }
				
			}
			echo json_decode($response);
		}
	}
	
	/**
	 * Find v1list/customers methods
	 */
	 
	public function getCustomersData($mcNumber = '', $token = '') {
		$c = curl_init('https://api.mytriumph.com/V1list/customers');
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, "searchText={$mcNumber}&existingCustomersOnly=true");
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_COOKIE, "SessionToken={$token}");
		$dataResults = curl_exec($c);
		curl_close($c);
		$dataResults = json_decode($dataResults);
		if (isset($dataResults->Customers) && !empty($dataResults->Customers)) {
			$dataResults->Customers[0]->Phone1 = $this->sanitize_phone($dataResults->Customers[0]->Phone1);
		}
		return $dataResults;
	} 
	
	/**
	 * Generating session token
	 */
	  
	public function get_sessionToken(){
       
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->config->item('triumph_url'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "username={$this->triuser}&password={$this->password}&apiKey={$this->apikey}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        $data = (json_decode($server_output));
        curl_close ($ch);
        return $data;
    }

    public function getcredittypeinfo($type,$token){

        $url = 'https://api.mytriumph.com/v1List/CreditResultTypes';
        $c   = curl_init($url);
        curl_setopt($c, CURLOPT_POST, 0);
        //curl_setopt($c, CURLOPT_POSTFIELDS, "docket_number={$docket_number}&docket_type={$docket_type}");
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_COOKIE, "SessionToken={$token}");
        $datalist = curl_exec($c);
        curl_close ($c);
        $datalist = json_decode($datalist);
        return $datalist->CreditResultTypes[$type];
    }
    
    public function checkTriumphExist( $mcNumber = null ) {
		$result = $this->BrokersModel->checkTriumphDataExist( $mcNumber );
	
		$finalArray = array();
		if ( $result ) {
			$finalArray = array(
				'Customers' => array(
					array(
						'DebtorKey' => $result['DebtorKey'],
						'Name' => $result['TruckCompanyName'],
						'Addr1' => $result['postingAddress'],
						'City' => $result['city'],
						'State' => $result['state'],
						'ZipCode' => $result['zipcode'],
						//~ 'Phone1' => $result['TruckCompanyPhone'],
						//~ 'Fax' => $result['TruckCompanyFax'],
						),
				),
			);
		}
		
		return $finalArray;
	}
	
	/**
	 * Fetching broker full detail
	 */
	
	public function getBrokerFullDetails( $brokerId = null, $mcNumber = null ) {
		$brokerDetail = $this->BrokersModel->getBrokerInfo($brokerId);
		if ( !empty($brokerDetail) ) {
			$this->data['brokerDetail'] = $brokerDetail;
			
			$result = $this->index($mcNumber, $this->data['brokerDetail']['DOTNumber'],'sameController');
			$result = json_decode(json_encode($result), true);
			
			if ( !empty($result) ) {
				if ( $result['creditResultTypeId']['name'] == 'Credit Request Approved') {
					$brokerStatus = 'Approved';
				} else {
					$brokerStatus = 'Not Approved';
				}
				
				$this->data['brokerDetail']['brokerStatus'] = $brokerStatus;
					
				$data = array(
					'brokerStatus' => $brokerStatus,
				);
				
				$this->BrokersModel->updateBrokerInfo($brokerDetail['id'],$data);
			}	
					
			$result = $this->BrokersModel->fetchContractDocuments($brokerDetail['id'], 'broker');
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
					$this->data['brokerDocuments'][$i]['BrokerId'] = $brokerDetail['id'];
				}
			}
		}
		echo json_encode($this->data);
	} 
	
		
	
}
