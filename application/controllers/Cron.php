<?php

/**
* Truck stop Api Controller
*  
*/

class Cron extends CI_Controller{
	
	function __construct()
	{
		parent::__construct();
		
		$this->load->model(array('Vehicle','Driver','User','Job'));
		$this->load->helper('truckstop');

		$this->protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$this->serverAddr = base_url();

		if( (isset($loggedUser_username) && $loggedUser_username != '') && (isset($loggedUser_id) && $loggedUser_id != '') && (isset($user_logged_in) && $user_logged_in == true) ) {
		} else {
			die();	
		}

		$this->id 			= $this->config->item('truck_id');	
		$this->username 	= $this->config->item('truck_username');
		$this->password 	= $this->config->item('truck_password');
		$this->url 			= $this->config->item('truck_url');
		$this->wsdl_url		= 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl'; //Live Mode Api 
	}
	
	private function in_array_r($needle, $haystack, $strict = false) {
	    foreach ($haystack as $item) {
	        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
	            return true;
	        }
	    }
	    return false;
	}
	
	public function fetchCalampEvents(){

		$response = calAmp($this->Vehicle);
		$calmpEvents = array();
		date_default_timezone_set('GMT');
		//Calamp Master Table Data
		$calmpEvents['AlertEvents'] = $response['AlertEvents'];
		$calmpEvents['AlertEventList'] = json_encode($response['AlertEventList']);
		$calmpEvents['AVLEvents'] = $response['AVLEvents'];
		$calmpEvents['JBUSEvents'] = $response['JBUSEvents'];
		$calmpEvents['errorMessage'] = $response['errorMessage'];
		$calmpEvents['JBUSDTCEvents'] = $response['JBUSDTCEvents'];
		$calmpEvents['JBUSDTCEventList'] = json_encode($response['JBUSDTCEventList']);
		$calmpEvents['DTCEvents'] = json_encode($response['DTCEvents']);
		$calmpEvents['DTCEventList'] = json_encode($response['DTCEventList']);
		$this->Job->addData('calamp_events',$calmpEvents);

		//Add Data for AVL Events
		if (isset($response['AVLEvents']) && !empty($response['AVLEvents']) ) {
			$AVLEventsList = array();
			if($response['AVLEvents'] > 1){
				$AVLEventsList = $response['AVLEventList']['AVLEvent'];	
			}else{
				$AVLEventsList[0] = $response['AVLEventList']['AVLEvent'];	
			}
			$calampData = $avlEventsData = array();
			$i = 0;
			foreach ($AVLEventsList as $key => $events) {

				//For vechile location update
				$deviceID = $events['deviceID'];
				$allTrucks["ID_".$deviceID] = $events;
				
				if(!$this->in_array_r($events['deviceID'], $calampData)){
					$calampData[$i]['deviceID']	 	= $events['deviceID'];
					$calampData[$i]['vehicleID'] 	= $events['vehicleID'];
					$calampData[$i]['vehicleVIN']	= $events['vehicleVIN'];
					$calampData[$i]['driverID']		= $events['driverID'];
				}

				$avlEventsData[$i]['deviceID']	= $events['deviceID'];
				$avlEventsData[$i]['GMTTime']	= date('Y-m-d H:i:s', strtotime($events['event']['GMTTime']));
				$avlEventsData[$i]['timeOffset']= $events['event']['timeOffset'];
				$avlEventsData[$i]['eventType']	= $events['event']['eventType'];

				if(is_array($events['event']['GPS'])){
					$avlEventsData[$i] = array_merge($avlEventsData[$i],$events['event']['GPS']);
				}else{
					$avlEventsData[$i]['GPSVAlidity'] = $avlEventsData[$i]['latitude'] = $avlEventsData[$i]['longitude'] = $events['event']['longitude'] = '';
				}


				if(is_array($events['event']['address'])){
					$avlEventsData[$i] = array_merge($avlEventsData[$i],$events['event']['address']);	
				}else{
					$avlEventsData[$i]['street'] = $avlEventsData[$i]['crossStreet'] = $avlEventsData[$i]['city'] = $avlEventsData[$i]['state'] = '';
					$avlEventsData[$i]['zip'] = $avlEventsData[$i]['country']	= $avlEventsData[$i]['POIName']	= $avlEventsData[$i]['POIType'] = '';
				}
				
				
				if(is_array($events['event']['telemetry'])){
					$avlEventsData[$i] = array_merge($avlEventsData[$i],$events['event']['telemetry']);
				}else{
					$avlEventsData[$i]['vehicleSpeed'] = $avlEventsData[$i]['heading'] = $avlEventsData[$i]['odometer']	= $avlEventsData[$i]['engineHours']	= '';
					$avlEventsData[$i]['fuelUsage'] = $avlEventsData[$i]['trueOdometer'] = $avlEventsData[$i]['gpsOdometer'] = '';
				}
				
				if(is_array($events['event']['digitalIO'])){
					$avlEventsData[$i] = array_merge($avlEventsData[$i],$events['event']['digitalIO']);	
				}else{
					$avlEventsData[$i]['pin'] = $avlEventsData[$i]['value'] = '';
				}
				if(is_array($events['event']['speeding'])){
					$avlEventsData[$i] = array_merge($avlEventsData[$i],$events['event']['speeding']);	
				}else{
					$avlEventsData[$i]['speedingEventType'] = $avlEventsData[$i]['speedingDuration'] = $avlEventsData[$i]['maximumSpeed'] = '';	
				}
				$avlEventsData[$i]['messageSeqID'] =  $events['messageSeqID'];
				$avlEventsData[$i++]['messageDataID']= $events['messageDataID'];
			}

			//For vehicle location update
			foreach ($allTrucks as $key => $truck) {
				$this->Vehicle->updateTruckInfo($truck);
			}

			if(count($calampData) > 0){
				foreach ($calampData as $key => $value) {
					if(!$this->Job->checkIfExists($value['deviceID'])){
						$this->Job->addData('calamp',$value);
					}
				}
			}

			if(count($avlEventsData) > 0){
				$this->Job->addData('avl_events',$avlEventsData,true);
			}
		}




		//Code for JBUS Events
		if (isset($response['JBUSEvents']) && !empty($response['JBUSEvents']) ) {
			$JBUSEventsList = array();
			if($response['JBUSEvents'] > 1){
				$JBUSEventsList = $response['JBUSEventList']['JBUSEvent'];	
			}else{
				$JBUSEventsList[0] = $response['JBUSEventList']['JBUSEvent'];	
			}
			$calampData = $jbusEventsData = array();
			$i = 0;
			foreach ($JBUSEventsList as $key => $events) {
				date_default_timezone_set('GMT');
				if(!$this->in_array_r($events['deviceID'], $calampData)){
					$calampData[$i]['deviceID']	 	= $events['deviceID'];
					$calampData[$i]['vehicleID'] 	= $events['vehicleID'];
					$calampData[$i]['vehicleVIN']	= $events['vehicleVIN'];
					$calampData[$i]['driverID']		= $events['driverID'];
				}

				$jbusXtra['deviceID']	= $events['deviceID'];
				$jbusXtra['GMTTime']	= date('Y-m-d H:i:s', strtotime($events['GMTTime']));
				
				if(count($events['JBUSMessageList']['JBUSMessage']) > 1){
					$JBUSMessageList = $events['JBUSMessageList']['JBUSMessage'];	
				}else{
					$JBUSMessageList[0] = $events['JBUSMessageList']['JBUSMessage'];
				}
				
				foreach ($JBUSMessageList as $key => $JBUSMessage) {
					 $JBUSMessage = array_merge($JBUSMessage,$jbusXtra);
					 array_push($jbusEventsData,$JBUSMessage);
				}
			}

			if(count($calampData) > 0){
				foreach ($calampData as $key => $value) {
					if(!$this->Job->checkIfExists($value['deviceID'])){
						$this->Job->addData('calamp',$value);
					}
				}
			}

			if(count($jbusEventsData) > 0){
				$this->Job->addData('jbus_events',$jbusEventsData,true);
			}
		}



		//Code for JBUSDTCEvents

		if (isset($response['JBUSDTCEvents']) && !empty($response['JBUSDTCEvents']) ) {
			$JBUSDTCEventList = array();
			
			if($response['JBUSDTCEvents'] > 1){
				$JBUSDTCEventList = $response['JBUSDTCEventList']['JBUSDTCEvent'];	
			}else{
				$JBUSDTCEventList[0] = $response['JBUSDTCEventList']['JBUSDTCEvent'];	
			}
			$calampData = $jbusdtcEventsData = array();
			$i = 0;
			foreach ($JBUSDTCEventList as $key => $events) {
				
				if(!$this->in_array_r($events['deviceID'], $calampData)){
					$calampData[$i]['deviceID']	 	= $events['deviceID'];
					$calampData[$i]['vehicleID'] 	= $events['vehicleID'];
					$calampData[$i]['vehicleVIN']	= $events['vehicleVIN'];
					$calampData[$i]['driverID']		= $events['driverID'];
				}


				$jbusdtcEVentsList['deviceID']	= $events['deviceID'];
				$jbusdtcEVentsList['GMTTime']	= date('Y-m-d H:i:s', strtotime($events['GMTTime']));
				if(isset($events['JBUSDTCMessage']) && !is_array($events['JBUSDTCMessage'])){
					$events['JBUSDTCMessage']['SPN'] = $events['JBUSDTCMessage']['FMI'] = $events['JBUSDTCMessage']['OC'] = '';
				}
				$jbusdtcEVentsList = array_merge($jbusdtcEVentsList,$events['JBUSDTCMessage']);
				array_push($jbusdtcEventsData,$jbusdtcEVentsList);
			}
				
			if(count($calampData) > 0){
				foreach ($calampData as $key => $value) {
					if(!$this->Job->checkIfExists($value['deviceID'])){
						$this->Job->addData('calamp',$value);
					}
				}
			}
			if(count($jbusdtcEventsData) > 0){
				$this->Job->addData('jbusdtc_events',$jbusdtcEventsData,true);
			}
		}
	}


	/*
	* Request URI : http://siteurl/Truckstop/updateGoogleKeyByHitAPI
	* Method : CronJob
	* Params : null
	* Return : null
	* Comment: Not in use now.
	*/

	public function updateGoogleKeyByHitAPI(){
		$gKeys = array("AIzaSyC4BwSAdx7ejol56LQjoZ_ZMTwLXrjYZJ0","AIzaSyBWEM0dXBzFS63qB82dP_jCIf-s6-pGfbg","AIzaSyCEFH2LkKezwk5XJsgBekN2vhfCtdUiyMk","AIzaSyActdBf8AhNG6846b971AmWpUCb4oFlW_M","AIzaSyBzKjQv_GApxMshR6h_Qjj-7ig0jR7le20","AIzaSyBTOC3xsGe8ft7_KZS2YofUlsDOCGLl5zg","AIzaSyAZrLPhQEVo2F3sz-FjmRacWIo1jv8UB4Y","AIzaSyDe5FFx4H0DIrc-PGZmI6U_RbeH7uIV7zQ","AIzaSyC6Msx3qCWtk9UToMJr9ztobJkEkuiq7Ak","AIzaSyCDCxatmrRX80Ft4muozMlhILoa9vsmrog","AIzaSyBGrDwpuVKlXmJA-B0QioXuipZfAr9ZNEg","AIzaSyCAFV1gpJZpE6RDOIjbYhLiAIehZSx8di0","AIzaSyBQB9KIKUv_ivCH21vs8eAw80rm8Cn_kjs","AIzaSyCHjGZD49WvOapXhek-MGeW1kPQ2cAcKz4","AIzaSyAkrSGI9cEXYBJ9EIciM98lLE2uink6weg","AIzaSyBlNHdgx_wxqXcFKX2E9fJV2vXa85A0QV4","AIzaSyDPdWG13xv8O-OcT2SVSdJWBDGG6HuWldg","AIzaSyC-ynfpBawou_Z_bY4LDawXWOcDfTUrb68","AIzaSyBTc3RpmZ8BbUZoRfHgM7utQq8VfnabZb0","AIzaSyCWz4BokVJCI-Yw9Chaa6UZoyQfUilqEgs","AIzaSyCp97kpoW5y0LFekMa-qgHubVQhiFFUWT8","AIzaSyDKPdW5FsxofNPYMp4-pcuzEfBib4eJsfg","AIzaSyC9td9T3s1XOsOy9_AclkIaEvUx3LoM81Y","AIzaSyCZ59YUBNXm3szAYKPIMCYtWVb24avqy1Y","AIzaSyBzYnza3J0g2iMwNsRCi2t82emvFmsoGRA","AIzaSyCraYMxGRdLPDiUDfl_LJzzo2VrglegZUc","AIzaSyBuxdIKh5B_e_t3py4NMIcgQ_Etv-8hgiw","AIzaSyDYzbiGY_K_x2hbrwFGKvQxSKLEzNJGAb8","AIzaSyATf_sXFLiDO8_gZgmA4EAP2X5FY0rJrsI","AIzaSyCv39MHSFSHgb9wkVJf6A5Ni0m695DuLTQ","AIzaSyBg12QRJBuOq-GjF-Iz3SQQw3AnHhQ8n_o","AIzaSyDfm5qM3Hx6hEjEl2a5KAG0IsvVeSrz91Y","AIzaSyCJwXGKQpVqwu7-H_fQAp_iNEaUMpSv53I","AIzaSyDtUGtcMdZDAJqIPx4PEZFFMICAd2OIzwk","AIzaSyCnQO6mPhlhTpZyngV_dBvZdJRH3Id7BFE","AIzaSyBmf1DOY9DXk2UZ7I6t0iVLr-gTiYLmGtg","AIzaSyC2X7Bq_54OiopHUzChgK3tcodLwS_viQE","AIzaSyDeUMQxXJn4do1TRV-vtNohsOLeqfK9xQo","AIzaSyDzNFpaW8mOUS9nA6GnUtngiJVgTqWBtVs","AIzaSyCwI-FmFuz_Ke2ooH_DjZaaSSCjWpgoYNk","AIzaSyBlGhrkkXU1S9Cp07k9-gdch5SfZ8xU4XI","AIzaSyCBNUIWSaHCYIadyPbYLah656-G4o6T0Fw","AIzaSyAs1krawH6JZ5SF0lyTyIWrE0gewZ5SPzI","AIzaSyDw20vcbVsxjfg5VYpNeh3DAoEluBAk8AI","AIzaSyAwF0iqNCvpZgXwOs6MFiklA6YkhVMOnV8","AIzaSyBSPVGmxdOqe2yrxzla4iezs00zWe_p6j4", "AIzaSyBUriaO-aK0cbf64cHSe1LPNIoLswUWng8","AIzaSyAhHieDgOmfBZZeaK_YiMQlhnANyUKQt2k","AIzaSyBO4h9EABf5zt1SUeyCqV4qyFmfF8SxDqM","AIzaSyCw9XpjOXlZwsFYVimc5o2lNAVSE8wO5Tc" );
        $keyIndex = 0;
        $i=1;
        try
        {
	        while(true){
	            $response =  file_get_contents("https://maps.googleapis.com/maps/api/place/search/json?location=-33.8670522,151.1957362&radius=500&sensor=false&key=".$gKeys[$keyIndex]);
	            $response =  file_get_contents("https://maps.googleapis.com/maps/api/place/search/json?location=-33.8670522,151.1957362&radius=500&sensor=false&key=".$gKeys[$keyIndex]);
	            $response =  file_get_contents("https://maps.googleapis.com/maps/api/place/search/json?location=-33.8670522,151.1957362&radius=500&sensor=false&key=".$gKeys[$keyIndex]);
	            
	            $response = json_decode($response,true);
	            if(in_array($response["status"], array("OVER_QUERY_LIMIT","REQUEST_DENIED"))){
	                $keyIndex++;
	            }else if($response["status"] == "OK"){
	                
	                $validKey = $gKeys[$keyIndex];
	               
	                break;
	            }else{
	                $keyIndex++;
	            }
	            if($keyIndex >= count($gKeys)){
	                $validKey = end($gKeys);
	                break;
	            }
	        }
	        $this->User->updateGoogleKey($validKey);
	    }catch(Exception $e){
	    	log_message('error', $e->getMessage());
	    }
        
	}


	/*
	* Request URI : http://siteurl/Truckstop/updateGoogleKey
	* Method : CronJob
	* Params : null
	* Return : null
	* Comment: Auto Change Google Map key in database (runs after every hour).
	*/

	public function updateGoogleKey(){
		$keyInUse = $this->User->getMapKey();
		$gKeys =  array("AIzaSyC4BwSAdx7ejol56LQjoZ_ZMTwLXrjYZJ0","AIzaSyBWEM0dXBzFS63qB82dP_jCIf-s6-pGfbg","AIzaSyCEFH2LkKezwk5XJsgBekN2vhfCtdUiyMk","AIzaSyActdBf8AhNG6846b971AmWpUCb4oFlW_M","AIzaSyBzKjQv_GApxMshR6h_Qjj-7ig0jR7le20","AIzaSyBTOC3xsGe8ft7_KZS2YofUlsDOCGLl5zg","AIzaSyAZrLPhQEVo2F3sz-FjmRacWIo1jv8UB4Y","AIzaSyDe5FFx4H0DIrc-PGZmI6U_RbeH7uIV7zQ","AIzaSyC6Msx3qCWtk9UToMJr9ztobJkEkuiq7Ak","AIzaSyCDCxatmrRX80Ft4muozMlhILoa9vsmrog","AIzaSyBGrDwpuVKlXmJA-B0QioXuipZfAr9ZNEg","AIzaSyCAFV1gpJZpE6RDOIjbYhLiAIehZSx8di0","AIzaSyBQB9KIKUv_ivCH21vs8eAw80rm8Cn_kjs","AIzaSyCHjGZD49WvOapXhek-MGeW1kPQ2cAcKz4","AIzaSyAkrSGI9cEXYBJ9EIciM98lLE2uink6weg","AIzaSyBlNHdgx_wxqXcFKX2E9fJV2vXa85A0QV4","AIzaSyDPdWG13xv8O-OcT2SVSdJWBDGG6HuWldg","AIzaSyC-ynfpBawou_Z_bY4LDawXWOcDfTUrb68","AIzaSyBTc3RpmZ8BbUZoRfHgM7utQq8VfnabZb0","AIzaSyCWz4BokVJCI-Yw9Chaa6UZoyQfUilqEgs","AIzaSyCp97kpoW5y0LFekMa-qgHubVQhiFFUWT8","AIzaSyDKPdW5FsxofNPYMp4-pcuzEfBib4eJsfg","AIzaSyC9td9T3s1XOsOy9_AclkIaEvUx3LoM81Y","AIzaSyCZ59YUBNXm3szAYKPIMCYtWVb24avqy1Y","AIzaSyBzYnza3J0g2iMwNsRCi2t82emvFmsoGRA","AIzaSyCraYMxGRdLPDiUDfl_LJzzo2VrglegZUc","AIzaSyBuxdIKh5B_e_t3py4NMIcgQ_Etv-8hgiw","AIzaSyDYzbiGY_K_x2hbrwFGKvQxSKLEzNJGAb8","AIzaSyATf_sXFLiDO8_gZgmA4EAP2X5FY0rJrsI","AIzaSyCv39MHSFSHgb9wkVJf6A5Ni0m695DuLTQ","AIzaSyBg12QRJBuOq-GjF-Iz3SQQw3AnHhQ8n_o","AIzaSyDfm5qM3Hx6hEjEl2a5KAG0IsvVeSrz91Y","AIzaSyCJwXGKQpVqwu7-H_fQAp_iNEaUMpSv53I","AIzaSyDtUGtcMdZDAJqIPx4PEZFFMICAd2OIzwk","AIzaSyCnQO6mPhlhTpZyngV_dBvZdJRH3Id7BFE","AIzaSyBmf1DOY9DXk2UZ7I6t0iVLr-gTiYLmGtg","AIzaSyC2X7Bq_54OiopHUzChgK3tcodLwS_viQE","AIzaSyDeUMQxXJn4do1TRV-vtNohsOLeqfK9xQo","AIzaSyDzNFpaW8mOUS9nA6GnUtngiJVgTqWBtVs","AIzaSyCwI-FmFuz_Ke2ooH_DjZaaSSCjWpgoYNk","AIzaSyBlGhrkkXU1S9Cp07k9-gdch5SfZ8xU4XI","AIzaSyCBNUIWSaHCYIadyPbYLah656-G4o6T0Fw","AIzaSyAs1krawH6JZ5SF0lyTyIWrE0gewZ5SPzI","AIzaSyDw20vcbVsxjfg5VYpNeh3DAoEluBAk8AI","AIzaSyAwF0iqNCvpZgXwOs6MFiklA6YkhVMOnV8","AIzaSyBSPVGmxdOqe2yrxzla4iezs00zWe_p6j4", "AIzaSyBUriaO-aK0cbf64cHSe1LPNIoLswUWng8","AIzaSyAhHieDgOmfBZZeaK_YiMQlhnANyUKQt2k","AIzaSyBO4h9EABf5zt1SUeyCqV4qyFmfF8SxDqM","AIzaSyCw9XpjOXlZwsFYVimc5o2lNAVSE8wO5Tc" );
		$keyInUseIndex = array_search($keyInUse, $gKeys);

		try
        {
			if(isset($gKeys[$keyInUseIndex + 1])){
				$this->User->updateGoogleKey($gKeys[$keyInUseIndex + 1]);
			}else{
				$this->User->updateGoogleKey($gKeys[0]);
			}
       	}catch(Exception $e){
	    	log_message('error', $e->getMessage());
	    }
        
	}



	/*
	* Request URI : http://siteurl/Truckstop/findJobs
	* Method : CronJob
	* Params : null
	* Return : null
	* Comment: Search jobs for drivers every hour
	*/

	public function findJobs(){

	}


	/*
	* Request URI : http://siteurl/Truckstop/findJobs
	* Method : CronJob
	* Params : null
	* Return : null
	* Comment: Search jobs for drivers every hour
	*/

	public function findJobsFromTruckStop($params){
		$client   = new SOAPClient($this->wsdl_url);
		$params   = array(
					'searchRequest' => array(
					'UserName'  => $this->username, 'Password' => $this->password, 'IntegrationId' => $this->id,
					'Criteria'  => array(
								'OriginCity'        => $params["origin_city"],
								'OriginState'       => $params["origin_state"],//Getting records of first state(Dispatcher)
								'OriginCountry'     => $params["origin_country"],
								'OriginRange'       => $params["origin_range"],
								'OriginLatitude'    => '',
								'OriginLongitude'   => '',
								'DestinationCity'   => $params["destination_city"],
								'DestinationState'  => $params["destination_states"],
								'DestinationCountry'=> $params["dest_country"],
								'DestinationRange'  => $params["destination_range"],
								'EquipmentType'     => $params["abbreviation"],
								'LoadType'          => 'Full',
								'PickupDates'       => $params["dateTime"],
								'HoursOld'          => $params["hoursOld"],
								'EquipmentOptions'  => '',
								'PageNumber'        => $params["pageNo"],
								'PageSize'          => 200,
								'SortBy'            => 'Miles',
								'SortDescending'    => true
							)
						)
					);

		$return = $client->GetLoadSearchResults($params);
		
		if(empty($return->GetLoadSearchResultsResult->SearchResults)  || empty($return->GetLoadSearchResultsResult->SearchResults->LoadSearchItem)){
			$this->rows   = array();
		} else if(empty($return->GetLoadSearchResultsResult->Errors->Error)){
			$this->rows   = $return->GetLoadSearchResultsResult->SearchResults->LoadSearchItem;
		} else{			
			$data['no_result'] 	= $return->GetLoadSearchResultsResult->Errors->Error->ErrorMessage;
		}

		$data['rows'] = array();
		if(count($this->rows) == 1){
			$data['rows'][]   = json_decode(json_encode($this->rows),true);
		}else{
			$data['rows'] 	  = json_decode(json_encode($this->rows),true);
		}
		
		$dat = array_merge($dat,$data['rows']);
	}



	
}
