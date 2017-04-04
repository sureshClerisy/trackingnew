<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('getLoadDetail'))
{
	function getLoadDetail(  $chainElement = null, $loadId = null, $username,$password, $IntegrationId, $jobModel) {
		$truckStopId = $chainElement["truckstopID"];
		$wsdl_url = 'http://webservices.truckstop.com/V13/Searching/LoadSearch.svc?wsdl'; //Live Mode Api
		$client   = new SOAPClient($wsdl_url);
		$params   = array(
			'detailRequest' => array(
				'UserName' => $username,
				'Password' => $password,
				'IntegrationId' => $IntegrationId,
				'LoadId' => trim($truckStopId),
				)
			);
		$return 	 = $client->GetLoadSearchDetailResult($params);
		$loadResults = $return->GetLoadSearchDetailResultResult->LoadDetail;
		$Detaildata = json_encode($loadResults);
		$jobRecord  = json_decode($Detaildata,true);
		
		$jobRecord['OriginCountry'] = 'USA';
		$jobRecord['DestinationCountry'] = 'USA';
		
		if ( $jobRecord['PickupDate'] != '' && $jobRecord['PickupDate'] != 'DAILY') {
			$jobRecord['PickupDate'] = date('Y-m-d',strtotime($jobRecord['PickupDate']));
		} else {
			$jobRecord['PickupDate'] = '';
		}
		
		if ( $jobRecord['DeliveryDate'] != '' ) {
			$jobRecord['DeliveryDate'] = date('Y-m-d',strtotime($jobRecord['DeliveryDate']));
		}
		$jobRecord['PostedOn'] = '';
		
		if ( isset($jobRecord['Entered']) && $jobRecord['Entered'] != '' ) {
			$jobRecord['postedDate'] = date('Y-m-d H:i:s',strtotime($jobRecord['Entered']));
			$jobRecord['PostedOn']   = date('Y-m-d',strtotime($jobRecord['Entered']));
		} else if ( isset($jobRecord['postedDate']) && $jobRecord['postedDate'] != '' ) {
			$jobRecord['PostedOn'] = date('Y-m-d',strtotime($jobRecord['postedDate']));
		}
		 
		$jobRecord['ExtraInfo'] = $jobRecord['SpecInfo'];
		$jobRecord['PickupDate'] = empty($jobRecord['PickupDate']) ? 'daily' : date('m/d/y',strtotime($jobRecord['PickupDate']));
		
		
		$data['encodedJobRecord'] = $jobRecord;
		$data['valuesArray'] = $chainElement['varr'];
		$data['deleted'] =  $jobRecord['ID'] == 0 ? true : $chainElement['deleted'];
		$data['truckStopId'] = $truckStopId;
		return $data;
	}
}

if (!function_exists('logEvent')){
	function logEvent($type,$message,$jobModel){
		$jobModel->logEvent($type,$message);
	}
}

if (!function_exists('logActivityEvent')){
	function logActivityEvent($entityId, $entityType, $eventType, $message ,  $jobModel, $srcPage=''){
		$jobModel->logActivityEvent($entityId, $entityType, $eventType, $message, $srcPage);
	}
}


if (!function_exists('getProfitPercent'))
{
	function getProfitPercent($profitAmount, $totalPayment){
		$profitPercent = round(($profitAmount / $totalPayment) * 100, 2);
		return $profitPercent;
	}
}

if (!function_exists('getPaymentFromProfitMargin'))
{
	function getPaymentFromProfitMargin($cost, $profit){
		$payment = round(($cost/(1 - ($profit/100))),2);
		return $payment;
	}
}

if (!function_exists('getDrivingHoursOfService'))
{
	function getDrivingHoursOfService($totalHours, $hoursLimitInWeek = 70, $daysInWeek = 8, $dailyDrivingLimit = 11,$consumedHours = 0){
		if($hoursLimitInWeek == 0 && $daysInWeek == 0){
			$hoursLimitInWeek = 70;
			$daysInWeek = 8;
		}		

		$th = 1; $tdays = 0; $dailyDriving = array();
		for ($i=1; $i <= $daysInWeek ; $i++) { 
		    if($totalHours > $hoursLimitInWeek ){
		        $totalHours -= $hoursLimitInWeek;       
		        $th++;
		    }
		    $i = $totalHours == $hoursLimitInWeek ?  $daysInWeek : $i;
		    
		    $parts = getParts($totalHours, $i);
		    $drivingHours = getUnequalParts($parts);   
		    if(!empty($drivingHours) && max($drivingHours) <= $dailyDrivingLimit){
		        $tdays += count($drivingHours);
		        $dailyDriving[] = $drivingHours;
		        break;
		    }
		}

		$totalHours = $hoursLimitInWeek;
		$firstDrivingHours = array();
		for ($ti=1; $ti <=$th-1 ; $ti++) { 
		    $parts = getParts($totalHours, $daysInWeek);
		    $drivingHours = getUnequalParts($parts);   
		    if(!empty($drivingHours) && max($drivingHours) <= $dailyDrivingLimit){
		        $tdays += count($drivingHours);
		        $firstDrivingHours[] = $drivingHours;
		        //print_r($new);
		        break;
		    }        
		}

		$dailyDriving = array_merge($firstDrivingHours, $dailyDriving);

		$returnResponse = array("tdays"=>$tdays,"dailyDriving" => $dailyDriving);
		if(count($dailyDriving) <= 0){
			$HLIW = 70;
			$hoursLimitInWeek = $HLIW - $consumedHours;
			$returnResponse = refineDrivingHoursOfService($totalHours, $hoursLimitInWeek, $daysInWeek, $dailyDrivingLimit,$consumedHours);
		}
		return $returnResponse;
	}
}

if (!function_exists('getParts')){
	function refineDrivingHoursOfService($totalHours, $hoursLimitInWeek, $daysInWeek, $dailyDrivingLimit,$consumedHours){
		if($hoursLimitInWeek == 0 && $daysInWeek == 0){
			$hoursLimitInWeek = 70;
			$daysInWeek = 8;
		}		

		$th = 1; $tdays = 0; $dailyDriving = array();
		for ($i=1; $i <= $daysInWeek ; $i++) { 
		    if($totalHours > $hoursLimitInWeek ){
		        $totalHours -= $hoursLimitInWeek;       
		        $th++;
		    }
		    $i = $totalHours == $hoursLimitInWeek ?  $daysInWeek : $i;
		    
		    $parts = getParts($totalHours, $i);
		    $drivingHours = getUnequalParts($parts);   
		    if(!empty($drivingHours) && max($drivingHours) <= $dailyDrivingLimit){
		        $tdays += count($drivingHours);
		        $dailyDriving[] = $drivingHours;
		        break;
		    }
		}

		$totalHours = $hoursLimitInWeek;
		$firstDrivingHours = array();
		for ($ti=1; $ti <=$th-1 ; $ti++) { 
		    $parts = getParts($totalHours, $daysInWeek);
		    $drivingHours = getUnequalParts($parts);   
		    if(!empty($drivingHours) && max($drivingHours) <= $dailyDrivingLimit){
		        $tdays += count($drivingHours);
		        $firstDrivingHours[] = $drivingHours;
		        //print_r($new);
		        break;
		    }        
		}

		$dailyDriving = array_merge($firstDrivingHours, $dailyDriving);
		$returnResponse = array("tdays"=>$tdays,"dailyDriving" => $dailyDriving);
		return $returnResponse;
	}
}




if (!function_exists('getParts')){
	function getParts($number, $parts){
	    return array_map('round', array_slice(range(0, $number, $number / $parts), 1));
	}
}

if (!function_exists('getUnequalParts')){
	function getUnequalParts($arr){
	    $temp = array();
	    for($i=count($arr)-1;$i>=0;$i--){
	        if($i != 0){
	            $temp[] = $arr[$i]-$arr[$i-1];
	        }else{
	            $temp[] = $arr[$i];
	        }
	    }
	    return $temp;
	}
}


if (!function_exists('toLocalTimezone')){
	function toLocalTimezone($utcDateTime){
		$serverTimeZone = date_default_timezone_get();
		$CI = & get_instance();  //get instance, access the CI superobject
  		$tz = $CI->session->userdata('userTimeZone');
        $tz = empty($tz) ? "America/New_York" : $tz;
        $userTimeZone  = new DateTimeZone($tz);
      	$dt = new DateTime($utcDateTime);
      	$dt->setTimezone($userTimeZone);
		$userTime =  $dt->format('Y-m-d g:i:s A');
        date_default_timezone_set($serverTimeZone);
        return $userTime;
	}
}




if (!function_exists('calAmp'))
{
	 function calAmp($vehicleModel){
		$response = array();
		try{
			$url = "http://sfo.wrx-us.net/datafeedservice/1013/DataFeedService?wsdl";
		    $client = new SoapClient($url);
		    $client->__setSoapHeaders(Array(new WsseAuthHeader("V9250_datapump", "4qHMPswJ9VpVEKzf")));
		    $response = $client->getMessages(array('messages'=>600));
		    $response = json_decode(json_encode($response),true);
		    //$response = $client->getMessageCount();
		    
		}catch(Exception $e){
			log_message('error', 'Getting error while fetching data from calamp webservice : '+$e->getMessage()+' Below is the response string in json format.');
			log_message('error', json_encode($response));
		}
		//updateTrucksInfo($response, $vehicleModel);
		return $response;
	}
}

if (!function_exists('updateTrucksInfo'))
{
	function updateTrucksInfo($response, $vehicleModel){
		if (isset($response['AVLEvents']) && !empty($response['AVLEvents'])) {
			$AVLEventsList = array();
			if($response['AVLEvents'] > 1){
				$AVLEventsList = $response['AVLEventList']['AVLEvent'];	
			}else{
				$AVLEventsList[0] = $response['AVLEventList']['AVLEvent'];	
			}
			$allTrucks = array();
			foreach ($AVLEventsList as $key => $events) {
				$deviceID = $events['deviceID'];
				$allTrucks["ID_".$deviceID] = $events;
			}
			
			foreach ($allTrucks as $key => $truck) {
				$vehicleModel->updateTruckInfo($truck);
			}
		}
	}
}

class WsseAuthHeader extends SoapHeader {

private $wss_ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
private $wsu_ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';

function __construct($user, $pass) {

        $created = gmdate('Y-m-d\TH:i:s\Z');
        $nonce = mt_rand();
        $passdigest = base64_encode( pack('H*', sha1( pack('H*', $nonce) . pack('a*',$created).  pack('a*',$pass))));

        $auth = new stdClass();
        $auth->Username = new SoapVar($user, XSD_STRING, NULL, $this->wss_ns, NULL, $this->wss_ns);
        $auth->Password = new SoapVar($pass, XSD_STRING, NULL, $this->wss_ns, NULL, $this->wss_ns);
        $auth->Nonce = new SoapVar($passdigest, XSD_STRING, NULL, $this->wss_ns, NULL, $this->wss_ns);
        $auth->Created = new SoapVar($created, XSD_STRING, NULL, $this->wss_ns, NULL, $this->wsu_ns);

        $username_token = new stdClass();
        $username_token->UsernameToken = new SoapVar($auth, SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'UsernameToken', $this->wss_ns);

        $security_sv = new SoapVar(
            new SoapVar($username_token, SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'UsernameToken', $this->wss_ns),
            SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'Security', $this->wss_ns);
        parent::__construct($this->wss_ns, 'Security', $security_sv, true);
    }
}
