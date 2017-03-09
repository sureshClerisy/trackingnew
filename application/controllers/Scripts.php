<?php

class Scripts extends Admin_Controller {
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
        $this->load->model('Script');
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
						'Phone1' => $result['TruckCompanyPhone'],
						'Fax' => $result['TruckCompanyFax'],
						),
				),
			);
		}
		
		return $finalArray;
	}
	
		
	public function checkApi() {
		$token = $this->get_sessionToken();
		$tokenValue = $token->SessionToken;
		//~ $tokenValue = '62e8438d-dbb2-4a6a-9c33-67f2a2234e66';
		$mc_number = $_REQUEST['searchText'];
		$existingCustomersOnly = $_REQUEST['existingCustomersOnly'];
			
			$docket_type = 'MC';
	
			echo "searchText={$mc_number}&existingCustomersOnly={$existingCustomersOnly}";
			$c = curl_init('https://api.mytriumph.com/V1list/customers');
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, "searchText={$mc_number}&existingCustomersOnly={$existingCustomersOnly}");
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_COOKIE, "SessionToken={$tokenValue}");
			$page = curl_exec($c);
			curl_close($c);
		
			$page = json_decode($page);
		
		pr($page);
	}
	
	public function updateLocalRecords() {
		$brokerInfo = $this->BrokersModel->getBrokerslisting();
		echo count($brokerInfo); 
		$i = 0;
		foreach( $brokerInfo as $broker ) {
			$brokerDetail = $this->BrokersModel->getBrokerInfo($broker['id']);
		
			if ( !empty($brokerDetail) ) {
				$this->data['brokerDetail'] = $brokerDetail;
				
				if ( $this->data['brokerDetail']['MCNumber'] != ''  && $this->data['brokerDetail']['MCNumber'] != 0 && $this->data['brokerDetail']['postingAddress'] == '' ) {
					$result = $this->index($this->data['brokerDetail']['MCNumber'], $this->data['brokerDetail']['DOTNumber'],'sameCont');
					$result = json_decode(json_encode($result), true);
					
					if ( !empty($result) ) {
						if ( $result['creditResultTypeId']['name'] == 'Credit Request Approved') {
							$brokerStatus = 'Approved';
						} else {
							$brokerStatus = 'Not Approved';
						}
						
					
						if ( !empty($result['newMethodInfo']['Customers']) ) {
							$newData = $result['newMethodInfo']['Customers'][0];
							$this->data['brokerDetail']['TruckCompanyName'] = $newData['Name'];
							$this->data['brokerDetail']['PointOfContactPhone'] = $newData['Phone1'];
							$this->data['brokerDetail']['postingAddress'] = $newData['Addr1'];
							$this->data['brokerDetail']['city'] = $newData['City'];
							$this->data['brokerDetail']['state'] = $newData['State'];
							$this->data['brokerDetail']['zipcode'] = $newData['ZipCode'];
							$this->data['brokerDetail']['TruckCompanyFax'] = $newData['Fax'];
							$this->data['brokerDetail']['DebtorKey'] = $newData['DebtorKey'];
							$this->data['brokerDetail']['brokerStatus'] = $brokerStatus;
							
							$data = array(
								'TruckCompanyName' => $newData['Name'],
								'PointOfContactPhone' => $newData['Phone1'],
								'postingAddress' => $this->data['brokerDetail']['postingAddress'],
								'city' => $this->data['brokerDetail']['city'],
								'state' => $this->data['brokerDetail']['state'],
								'zipcode' => $this->data['brokerDetail']['zipcode'],
								'TruckCompanyFax' => $newData['Fax'],
								'DebtorKey' => $newData['DebtorKey'],
								'brokerStatus' => $brokerStatus,
							);
							
							$this->BrokersModel->updateBrokerInfo($brokerDetail['id'],$data);
						} else if ( $result['city'] != '' && $result['state'] != '' ) {
							$data = array(
								'TruckCompanyName' => $result['companyName'],
								'PointOfContactPhone' => $result['phone'],
								'postingAddress' => '',
								'city' => $result['city'],
								'state' => $result['state'],
								'brokerStatus' => $brokerStatus,
							);
							
							$this->BrokersModel->updateBrokerInfo($brokerDetail['id'],$data);
						}
						
						$i++;
					}	
				}
			}
		}
		
		echo 'updated Records are '.$i;
		
	}
	
	/**
	 * updating broker_info fields from broker backup table
	 */
	  
	public function revertBackBrokerInfo() {
		$brokersBackup = $this->Script->getBrokerslistingRevertTable();
		$i = 0;
		foreach( $brokersBackup as $brokerBackup ) {
			if ( $brokerBackup['MCNumber'] != '' && $brokerBackup['MCNumber'] != 0 ) {
				$pointPhoneNumber = $this->sanitize_phone($brokerBackup['pointPhoneNumber']);
				$truckPhoneNumber = $this->sanitize_phone($brokerBackup['truckPhoneNumber']);
				$data = array(
					'PointOfContact' => $brokerBackup['PointOfContact'],
					'PointOfContactPhone' => $pointPhoneNumber,
					'TruckCompanyEmail' => $brokerBackup['TruckCompanyEmail'],
					'TruckCompanyPhone' => $truckPhoneNumber,
					'TruckCompanyFax' => $brokerBackup['TruckCompanyFax'],
				);
				
				$this->Script->updateBrokerInfoTable($data,$brokerBackup['MCNumber']);
				$i++;
			}
			
		}
		
		echo 'total number of records updated are '.$i;
	}
	
	/**
	 * Adding value of broker_info table 5 fields to loads talbe
	 */
	  
	public function uploadLoadsTableField() {
		$loads = $this->Script->getLoadslisting();
		$i = 0;
		foreach( $loads as $load ) {
			if ( $load['broker_id'] != '' && $load['broker_id'] != null && $load['broker_id'] != 0 ) {
				
				$brokerInfo = $this->Script->getBrokerInfo( $load['broker_id'] );
							
				if ( !empty( $brokerInfo) ) {
					$data = array(
						'PointOfContact' => $brokerInfo['PointOfContact'],
						'PointOfContactPhone' => $brokerInfo['PointOfContactPhone'],
						'TruckCompanyEmail' => $brokerInfo['TruckCompanyEmail'],
						'TruckCompanyPhone' => $brokerInfo['TruckCompanyPhone'],
						'TruckCompanyFax' => $brokerInfo['TruckCompanyFax'],
					);
					
					$this->Script->updateLoadsTable($data,$load['id']);
					$i++;
				}
			}
		}
		
		echo 'total number of records updated are '.$i;
	}
	
	function sanitize_phone( $phone ) {

		$format = "/(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/";
		// Trim & Clean extension
		$phone = preg_replace( '/\s+(#|x|ext(ension)?)\.?:?\s*(\d+)/', ' ext \3', $phone );
		// format
		$phone = preg_replace( $format, "($2) $3-$4", $phone );
		// Remove likely has a preceding dash
		return preg_replace('/\\s+/', ' ', trim( $phone ));
	}
	
	/**
	 * Updating load tables street address fields
	 */
	  
	public function splitRecordsForLoad() {
		$loads = $this->Script->fetchAllLoads();
		//~ pr($loads); die;
		$j = 0;
		foreach( $loads as $load ) {
			$data = array();
			if ( $load['PickupAddress'] != '' && $load['PickupAddress'] != null ) {
				if ( strpos($load['PickupAddress'], ',') !== false ) {
					$addressArr = explode(',',$load['PickupAddress']);
					$data['OriginCountry'] = trim(end($addressArr));
					$data['OriginState'] = trim(prev($addressArr));
					$data['OriginCity'] = trim(prev($addressArr));	
					$streetAddress = '';
					for( $i = 0; $i < count($addressArr) - 3; $i++ ) {
						$streetAddress .= $addressArr[$i].', ';
					}	
						
					$data['OriginStreet'] = rtrim($streetAddress,', ');			
					$data['PickupAddress'] = rtrim($streetAddress,', ');			
				}
			}
			
			if ( isset($load['DestinationAddress']) && $load['DestinationAddress'] != '' ) {
				if ( strpos($load['DestinationAddress'], ',') !== false ) {
					$addressArr = explode(',',$load['DestinationAddress']);
					$data['DestinationCountry'] = trim(end($addressArr));
					$data['DestinationState'] = trim(prev($addressArr));
					$data['DestinationCity'] = trim(prev($addressArr));			
					
					$streetAddress = '';
					for( $i = 0; $i < count($addressArr) - 3; $i++ ) {
						$streetAddress .= $addressArr[$i].', ';
					}	
						
					$data['DestinationStreet'] = rtrim($streetAddress,', ');	
					$data['DestinationAddress'] = rtrim($streetAddress,', ');	
				}
			}
			
			if( !empty($data) ) {
				$this->Script->saveLoadData($data,$load['id']);
				$j++;
			}
			
		}
		
		echo 'Total updated records are '.$j;
		
	}
	
	/**
	 * Updating extra stop tables street address fields
	 */
	  
	public function splitRecordsForExtraStop() {
		$extraStops = $this->Script->fetchAllExtraStops();
		$j = 0;
		foreach( $extraStops as $stop ) {
			$data = array();
			if ( $stop['extraStopAddress'] != '' && $stop['extraStopAddress'] != null ) {
				if ( strpos($stop['extraStopAddress'], ',') !== false ) {
					$addressArr = explode(',',$stop['extraStopAddress']);
					$data['extraStopCountry'] = trim(end($addressArr));
					$data['extraStopState'] = trim(prev($addressArr));
					$data['extraStopCity'] = trim(prev($addressArr));	
					$streetAddress = '';
					for( $i = 0; $i < count($addressArr) - 3; $i++ ) {
						$streetAddress .= $addressArr[$i].', ';
					}	
						
					$data['extraStopAddress'] = rtrim($streetAddress,', ');	
					
					$this->Script->saveExtraStopData($data,$stop['id']);
					$j++;		
				}
			}
			
		}
		
		echo 'Total updated records are '.$j;
		
	}
}
