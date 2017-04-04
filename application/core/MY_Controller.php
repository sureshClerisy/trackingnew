<?php
defined('BASEPATH') OR exit('No direct script access allowed');

	class MY_Controller extends CI_Controller {
		function __construct()
		{
			parent::__construct();
		}
	}
	   
    class Admin_Controller extends MY_Controller
	{
		public $finalArray;
		public $username;
		public $password;
		public $id;
		public $accountID;
		public $wsdl_url;
		public $entity;
		public $event;
		public $serverAddr;
		public $protocol;

		function __construct() {

		  	parent::__construct();
			$this->load->library(array('session'));
			$loggedUser_username 	= $this->session->userdata('loggedUser_username');
			$loggedUser_id 			= $this->session->userdata('loggedUser_id');
			$user_logged_in 		= $this->session->userdata('loggedUser_loggedin');

			$this->entity     = $this->config->item('entity');
			$this->event      = $this->config->item('event');
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
			$this->finalArray	= array();
		}
		
		public function commonApiHits($abbreviation = 'F', $dateTime = array() , $hoursOld = '', $origin_country = 'USA', $origin_range = 300 ,$destination_city = '', $destination_states = '', $destination_range = 300, $dest_country = 'USA', $load_type = 'Full') {
		if ( strpos($this->origin_state,',') !== false ) {
			$states = explode(',',$this->origin_state);
			$statesCount = count($states);
		} else {
			$statesCount = 1;
		} 
		
			$pageNo = 1;
			$dat 	= array();

			while(true) {
				$client   = new SOAPClient($this->wsdl_url);
				$params   = array(
					'searchRequest' => array(
						'UserName' => $this->username, 'Password' => $this->password, 'IntegrationId' => $this->id,
						'Criteria' => array(
							'OriginCity' => $this->origin_city,
							'OriginState' => $this->origin_state,//Getting records of first state(Dispatcher)
							'OriginCountry' => $origin_country,
							'OriginRange' => $origin_range,
							'OriginLatitude' => '',
							'OriginLongitude' => '',
							'DestinationCity' => $destination_city,
							'DestinationState' => $destination_states,
							'DestinationCountry' =>	$dest_country,
							'DestinationRange' => $destination_range,
							'EquipmentType' => $abbreviation,
							'LoadType' => 'Full',
							'PickupDates' => $dateTime,
							'HoursOld' => $hoursOld,
							'EquipmentOptions' => '',
							'PageNumber'   => $pageNo,
							'PageSize' => 200,
							'SortBy' => 'Miles',
							'SortDescending' => true
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
				
				//~ if ( $statesCount > 3 ) {
					//~ if( count($data['rows']) < 200 || count($data['rows']) >= 3000)  {
						//~ break;
					//~ }
				//~ } else {
					//~ if( count($data['rows']) < 200 ) {
						//~ break;
					//~ }
				//~ }
					
				if( count($data['rows']) < 200 || count($dat) >= 2000)  {
					break;
				}
				$pageNo++;
				
				
			}
			
			$this->finalArray  = $dat;
			unset($dat);
			unset($data['rows']);
			return $this->finalArray;
		}
		
		/** 
		 * Fetching saved loads for one vehicle
		 */
		
		public function getSingleVehicleLoads($userId = null , $vehicleId = null ,$scopeType = '', $dispatcherId = null, $driverId = null, $secondDriverId = null, $startDate = '', $endDate = '',$filters = array() ) {	//dispatcher id to get loads of particular dispatcher only if driver is selected
			$jobs = array();
			$jobs = $this->Job->fetchSavedJobsNew($userId, $vehicleId, $scopeType, $dispatcherId, $driverId, $secondDriverId, $startDate, $endDate,$filters);
			if( !empty($jobs) && count($jobs) > 0){
				foreach ($jobs as $key => $value) {
						if($jobs[$key]['invoiceNo']==0){
							$jobs[$key]['invoiceNo'] = '';
						}
					$_COOKIE_NAME = 'VISIT_'.$value['truckstopID'].'_'.str_replace("/", "_", $value['pickDate']);
					if(isset($_COOKIE[$_COOKIE_NAME])) {
						$jobs[$key]['visited']	= true;
					}else{
						$jobs[$key]['visited'] = false;
					}
				}
			}
			return $jobs;		
		}

		/**
		 * Fetching estimate time and distance for given miles or b/w two locations using google api
		 */
		
		function GetDrivingDistance($location_ori, $location_dest) {
			$location1 = urlencode($location_ori);
			$location2 = urlencode($location_dest);
			
			//~ $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=$location1&destinations=$location2&waypoints=optimize:true|Providence,+RI|Hartford,+CT&key=AIzaSyBsZPuC5cca9gr4qPZ-p1O9hPzp3serepk";
			//~ $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=$location1&destinations=$location2&key=AIzaSyDiojAPmOWusjvasUhm5wKswtCRtkEKyi8";
			$googleKeys = array("AIzaSyCUrQy4amWsY8cxSPaYCMNX6aY9CWWcAtk","AIzaSyBWhHYF6lq4yQ6Jt5DaAo9yS6Bn7-YuDrE","AIzaSyCVxoQq3PxPYechPY9tDyx6vhI8ty27tKk","AIzaSyDWrNBqjp0Ntb66QPa1CZe3UlGyofJDXJY","AIzaSyA1DxFGAg6k6YLw0B9o28cNPL77wHyxvDM","AIzaSyBmxRpO-qdJN5X8fG4_1jrkF_xwcWTKHw0","AIzaSyA-b6uwprb6K9i0SM8ehNapE0ETBEub1xQ", "AIzaSyAFnTukWwp_aQiSbJRaWmTv4k_6Kd3_O9M","AIzaSyBSDP3t4gPmtO-Tm-0bmDj73V3BrgRdJwo","AIzaSyBdFIw8EICyOqA0XNvv1rrMd1B2Int80FI","AIzaSyAc_zfJ9tOrU84jMxkl2QaUznVOOfRZ97M","AIzaSyAUi8ISK2bJ-5QClFr9ECvmE_Ypco4fK0U","AIzaSyAXlFcRW94HggsO8oTWLhO8GYpG1Wy60vg","AIzaSyBLiUcHYsofoyQEj3Z-YbNg_O10-bl1MAU","AIzaSyB8TGBHB7KUtxaMEarzZ39pwnX7evG9Fcc","AIzaSyBsZPuC5cca9gr4qPZ-p1O9hPzp3serepk", "AIzaSyBYpjbCLWTZGK-8Am7Z39_UIUiN88nz7EQ", "AIzaSyCJ04xh3kIVPFSO-MICsJELeab0AMoYWdM","AIzaSyAf-nolNNfcP2QbW0nqSRxSzxg8ZT8DFhA","AIzaSyAV33vT4p-ex6POT5KlTSYFZYDCeZ8AqbE","AIzaSyD0Rn-ITyvVPqPJH9_C6zLKU1lk2zfgYMc","AIzaSyD2UIvZUfmLpb3OouQumUfmxiI7pyjJXdo","AIzaSyCH_hXZ6fpC-asjw4RXFnO1X2F5GMXydKQ","AIzaSyA3Yn_qjQDIMREiEVJ-xztzKvsco7UZtx4","AIzaSyCt6Nw_SyLVPUzpiQtzz2Rt_Km6RtU5fZg","AIzaSyDhQCeg-WFv_QU0GScdV5B9WvsPYV3e5Xs","AIzaSyBBIriqcaYH2wmBAUOlb2erD9G3n3LQMzs","AIzaSyB5Z7trBjXlsE1MzeCvz-82a99gwB-1TpE","AIzaSyDXjSWzoY6Vsgxs-tArv9TAxsRd-DX4Qwk","AIzaSyAQ7XALh70CVmKBpTzMFrecwSCqGi7f1qg","AIzaSyDKYddg0yDXbotlc2iUJPxJM9itcsLFCVM","AIzaSyDgUqLkFw9-YkOkP4j6V0OHoi2rshQfopA","AIzaSyBBf3NWL_Ivggk-MgcwlgFYpRGrFT7dVws","AIzaSyDA-tNbR44Dtq1ukemtKnzsD_TdSxc6UP8","AIzaSyCPdJzixa65dLqkzkyF3r0WHHv0rSqkSq4","AIzaSyBYiKrhju6OepPOkK-5ajWnadQM4v45Uxg","AIzaSyAk3igHg3UnurlxVj5BcXb93TyTGsc1crw","AIzaSyCYMDoubNXna36JIgM1QJ0bpyND0mVQARc","AIzaSyAFtYHko7L8ZogNbDr07HP8k6II-KwSopA","AIzaSyB2ozJqc56BaJk08L-UcHocHHHaxmqoRFA","AIzaSyCpvjAi8GK0qsShnIYAVveqHoBAetnxkPw","AIzaSyATZ4rPq1kG-G5GahgUX9i9yfkM3YMmg98","AIzaSyB1WA3PnqvBCOAhtv4gLjJiV5p1OD90iQM","AIzaSyBCznY99iUKp1IL5Za5JinXu2zDb3qXb28","AIzaSyA9HvBaDZIndpEITAuhNk4Q9Bk2m67zmyw","AIzaSyCFJ_ZxBfyUhhk4jJZacj4cLgesoN7WebA","AIzaSyCjY70mqsz4dUaze4HH654wdxnNvyO_Scw","AIzaSyB-udUmxOkCnKFD4L1Mz4zHdpUXZ2-cLpM","AIzaSyCf4IqT0w0jAzA7wRmnevjckZPdrJ4Mo9U","AIzaSyDoQOfYGkRbTbKt8Vvc-Unr9JIaBqe_QCg","AIzaSyA-MyT4jrFnHTkx1smFMlKCls-H_yVd-r8","AIzaSyDbs8aA8VtindRM7GItDxmMJxUfBSGB1Fw","AIzaSyAN9UKnA29-I6TA96_1Ula59demLpWtAA8","AIzaSyDADcRuXhwjAy215VK9vWy8FXx3cOfD0wU","AIzaSyAPsZ3_S-AQzH8_kTOE8fZdEOsAsaBWnRs","AIzaSyBovZhGB-rPs0IY8zG7Q2pwqWmX9D-ROQo","AIzaSyBYeR6BqB0wVgSPT4VCaIISVf1S77rJ2fI","AIzaSyDN6UeECJ2uCT77PmE8udQJcvwtoOds_kY","AIzaSyDnMvl_GyxMHHYzkYQW2YYJKV6gblaCf88","AIzaSyDf6uF8q4SJqN0FSKH6vSTb2hR9f91pA_w","AIzaSyBo9OIkVyl22q8PARAss9Xd4RGe-QOlcIk","AIzaSyDDIic_ejhUYMZuhCOLnhp-xp8atVOmuVU","AIzaSyARub453GGMsCTip-a9vLVa4G8i2EtYmv0","AIzaSyAOWWoDN16XStpvJm0A0W-njCc_8eY-daM","AIzaSyCqtm1r2lmdguWApeW04AShtR-sA_mpS-w","AIzaSyBtXEnCqjWATgD00HF0__DExRXWzdms_fY","AIzaSyC8BTgvRXddNsph2FrpC_2DVCFh9zAxh-Y","AIzaSyAyWIS7AMabDwaHi8ls05McHQLIphzLTCk","AIzaSyBATfnQADZ-uIXF5qh3fN0iX87LyFnIfVw","AIzaSyCmX_k5zNozIy5bHdGxSHXlH7JiOddG3tY","AIzaSyCh4sux3pZ-YHcD8OW7fsVAe96I8rlvjR4","AIzaSyCS_DiFn3-61mc7vdEduKJc3HlADXsysOw","AIzaSyBmdb9SRTEpAzsGS2sQ9WvLMtfEmzGOkco","AIzaSyA4XkMOLhnQFeOMLfqhihtedvysOHb0Jf0","AIzaSyB6bob6ZGuoDSUJ9Xi24KtO7zDlur6c5Jo","AIzaSyAN2m7ItdRFZWFwddirhRKWpTkkIv3nDH0","AIzaSyD4DVRUGp8_p1oRn_cRN0wSQKPbCgngLmo","AIzaSyAEA3YQ4pry1llnSCD3H9t0d9GO58jU39Q","AIzaSyDy774lFyUWCHShcd9uJJAW3OW4RCO6b2s","AIzaSyDyQUdz-bfASCXLUOLFXSqdFuOTvbq3lgc","AIzaSyAI69oqx7V9R93dj5PwgcR0TJOEBxdCFk8","AIzaSyDfni2hIj4tKCN6M_lRKtosRixfJixh0qk","AIzaSyAJOEUjycXVbrAxqkR2fA3zoVSgBCGjIbc","AIzaSyCmsNGcTZO_9YN1bB_vShkljhdzNxVRLM0","AIzaSyCc-uMoZDTaRRiFujnPKTPw5shw67no8Bs","AIzaSyCeVH2NtXwK666XZqgqsnoY_D9zitTgEnw","AIzaSyAwwRcMri9xjk92aB-MCgODRDdHVXt5Uow","AIzaSyABeanBK7oEMA5thcUMUm3ZTOA9Kro4rCk","AIzaSyCPI4FazfbVChbHRJu0jDxFh1beqWft97I","AIzaSyC8LjnBcHEYe0QckaMOUNf_gK6sJQGSqKM","AIzaSyDQQ6OphDYbjse1DWAp9h_GF9boBjNHtlg","AIzaSyCKovWEDHm-A2KRzcKsJordD2vSN9qAi0k","AIzaSyDIAReLLr0hloOMKMIIJINfqcf7XL-BmXQ","AIzaSyAyC1TOgdoyC9PK7ZD-vvJ5wwTfIf0temA","AIzaSyCglNprL7mjE2mieS6L3bIHcs7WTkNAlNU","AIzaSyAdMGnJcdhPhKGrYKeFK-u-GBlYwM_QTr8","AIzaSyD2GBxarJ57LerNqbByJkS1-dYIMll682k","AIzaSyB9nmyFk_fu1hUw1TCuYHopjd7hiMMaoWk","AIzaSyDVWDjhX8o4wVbeC_Lr2wY063q3HnzMeIA","AIzaSyCaFxYa2HgbVQ1z_DbXZFSofqQu_mRbnbk","AIzaSyDLXZVN--G-Iaq7PTO-29YRjlaGXQ1knpQ","AIzaSyDALWTREvxIpevQewlFXcID9PYsJACSGFM","AIzaSyB68P2LCd5fGzV-QjNKwKY022njOLv8nv4","AIzaSyCg6f3oYvSlG2eT0s9_GxsPDz5jXdrXHj0","AIzaSyAWBvCAHs2k8VwW7RAqrMv9H8HE6IVtJuM","AIzaSyC85i8Q4BLHbOSvITGs9bwcOFpOaG8xzgg","AIzaSyDs_HSPo_XFnYzlbNNW4Ansi0tOzK8cTQY","AIzaSyAY4g3lNsfjkAGuykiKaaPZuyAFsi8vIV8","AIzaSyAmle0YpqHOMm-akJIBj8vNI-zHuB5zLzg","AIzaSyDUgpAY8K2GdRJPfOefQPvY1oj4mQ9kG4g","AIzaSyBwzhjcQGXyGl1kEr1xl9xiSVaHxegOQ64","AIzaSyBdLLN1MCh_SRmbWYYqNMhqo5pO6oA_ymM","AIzaSyAFF7a5nwlXjPiGREn4CnTyf44UeULJQRg","AIzaSyCveKUCd30tO4_eFSe-tuMbSpDxXowJtSU","AIzaSyADXM1hDaj9ZRorOWgZIxCVHNEcbx6aRT4","AIzaSyDbHCgQm-0j2emQGEc1xohSoJhFOjYWMc8","AIzaSyAWzN7TNL--UUAlVM09MsebMKqYplWtklE","AIzaSyBheYvUJl7zN3icKHDM5Ufq-S4DVzlzhZU","AIzaSyAxxVDJBxeUNOEUhsxuP5S9350ykwzvtnI","AIzaSyC7NlINL6yFO7VjzXS8PVa-jAFXkJ891zI","AIzaSyDwQ7TnDMWdS7QIJRpcQfpPiNGYBaWC1BE","AIzaSyDs-exopJx7ZXhFdZKyET923p6rA_FYrBw","AIzaSyB0Xgvgsr8DzkuqMY0SxtYMEMgQHyw-phQ","AIzaSyBaJXO9Flro3f3SFw--Wsdq0gagZRq3c20","AIzaSyAfWxTJ3ar7Gbr_VOrCOqoX3GtvzVyHflI","AIzaSyA1u4RFmRvPjiOlmoQVA-an0M3s5C_2LW4");
			$keyIndex = -1;
			if(!$this->session->has_userdata('validGoogleKey')){
				$this->session->set_userdata('validGoogleKey', $googleKeys[0]);
			}
			
			while(true){
				$url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=$location1&destinations=$location2&key=".$this->session->validGoogleKey;

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				$response = curl_exec($ch);
				curl_close($ch);
				$response_a = json_decode($response, true);	
				if(in_array($response_a["status"], array("OVER_QUERY_LIMIT","REQUEST_DENIED"))){
					if($googleKeys[++$keyIndex] != $this->session->validGoogleKey){
						$this->session->set_userdata('validGoogleKey', $googleKeys[$keyIndex]);	
					}else{
						$this->session->set_userdata('validGoogleKey', $googleKeys[++$keyIndex]);	
					}
				}else if($response_a["status"] == "OK"){
					break;
				}else if($response_a["status"] == "INVALID_REQUEST"){
					break;
				}else{
					$keyIndex++;
				}

				if($keyIndex >= count($googleKeys)){
					break;
				}
			}
			if ( isset($response_a['rows'][0]['elements'][0]['distance']['text']) && $response_a['status'] == 'OK' && !isset($response_a['error_message']) ) {
				$distance = explode('mi',$response_a['rows'][0]['elements'][0]['distance']['text']);
				$estimatedTime = $response_a['rows'][0]['elements'][0]['duration']['text'];
			
				$distanceMile = trim(str_replace(',','',$distance[0]));
				$dataArray = array('origin'=>str_replace(' ','~',$location_ori),'destination'=>str_replace(' ','~',$location_dest),	'miles'=>ceil($distanceMile), 'estimated_time' => $estimatedTime);

				$this->User->saveData($dataArray);
			} else {
				$distanceMile = 0;
				$estimatedTime = '';
			}

			return array('distance' => trim($distanceMile),'time' => $estimatedTime);
		} 
		
		/**
		 * Change phone number format
		 */
		  
		function sanitize_phone( $phone ) {
			$format = "/(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/";
			$phone = preg_replace( '/\s+(#|x|ext(ension)?)\.?:?\s*(\d+)/', ' ext \3', $phone );
			$phone = preg_replace( $format, "($2) $3-$4", $phone );
			return preg_replace('/\\s+/', ' ', trim( $phone ));
		}
		
		/*
		* Method: Post
		* Params: fileArray, prefix, folder name
		* Return: success or error
		* Comment: Used for uploading contract documents
		*/
		
		public function uploadContractDocsToServer( $files, $prefix = '', $uploadFolder = "" ) 
		{
			$_FILES = $files;
			$config['_prefix'] 	= $prefix.'_';
			
			if(!empty($_FILES)){
				if($_FILES['file']['error'] == 0 ){
					$extArr = explode('.',$_FILES['file']['name']);
					$extension = strtolower(end($extArr));
					$config['file_name']   = $config['_prefix'].uniqid().'.'.$extension;
					$config['upload_path']          = 'assets/uploads/documents/'.$uploadFolder.'/';
					$config['allowed_types']        = 'pdf|gif|jpg|png|docx|doc|xls|xlsx|txt|ico|bmp|svg';
					
					$this->load->library('upload', $config);
					$response['error'] = false;

					if ( !$this->upload->do_upload('file'))	{
						$response['error'] = true;
						$response['error_desc'] = $this->upload->display_errors();
					} else {
						$response['error'] = false;
						$response['data'] = $this->upload->data();
						
						if (substr(php_uname(), 0, 7) == "Windows"){ 
							$response['data']['cmd'] = 'Windows';
						} 
						else { 
							$thumbFolder = 'thumb_'.$uploadFolder;
							$cmd = 'cd '.$response['data']['file_path'];
							$cmd .= '; convert -thumbnail x600 '.$response['data']['file_name'].'[0] -flatten ../'.$thumbFolder.'/thumb_'.$response['data']['raw_name'].'.jpg';
							$response['data']['cmd'] = $cmd;
							exec($cmd . " > /dev/null &");   
						} 
					}
				}else{
					$response['error'] = true;
					$response['error_desc'] = $_FILES;
					if ( $_FILES['file']['error'] == 1 && $_FILES['file']['size'] == 0 ) {
						$response['error_exceed'] = 1;
					}
				}
			} else {
				$response['error'] = true;
				$response['data'] = 'Files array empty !!!';
			}
			return $response;
		}


	}
