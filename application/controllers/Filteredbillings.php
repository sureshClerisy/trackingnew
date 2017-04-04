<?php

/**
* Filteredbillings Api Controller
*  
*/

class Filteredbillings extends Admin_Controller{
	
	public $userId;

	function __construct(){
		parent::__construct();
		
		$this->userId 		= $this->session->loggedUser_id;
		$this->load->model('Billing');
		$this->load->model('Driver');
	}
	
	public function index( $parameter = '' ) {
		$data = array();
		$filters = array("itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC");
		$filters["filterType"] = isset($_REQUEST["filterType"]) ? $_REQUEST["filterType"] : 'all';
		$filters["userType"] = isset($_REQUEST["userType"]) ? $_REQUEST["userType"] : 'all';
		$filters["userId"] = isset($_REQUEST["userToken"]) ? $_REQUEST["userToken"] : false;


		if( isset( $_REQUEST["startDate"]) && $_REQUEST["startDate"] != ""){ $filters["startDate"] = date("Y-m-d", strtotime($_REQUEST["startDate"])); }
		if( isset( $_REQUEST["endDate"]  ) && $_REQUEST["endDate"]   != ""){ $filters["endDate"]   = date("Y-m-d", strtotime($_REQUEST["endDate"]));   }

		if($filters["userType"] == "team" || $filters["userType"] == "driver"){

			$driverInfo = $this->Driver->getInfoByDriverId($filters["userId"]);
			if(isset($driverInfo[0])){ $driverInfo = $driverInfo[0]; }
			$filters["vehicleId"] = isset($driverInfo["vehicleId"])  ? $driverInfo["vehicleId"] : '';
			$filters["dispatcherId"] = isset($driverInfo["dispatcherId"])  ? $driverInfo["dispatcherId"] : '';
			$filters["secondDriverId"] = (isset($_REQUEST['secondDriverId']) && $_REQUEST['secondDriverId'] != '' ) ? $_REQUEST['secondDriverId'] : $driverInfo['team_driver_id'];			 
		}


		$jobs = $this->Billing->getFilteredLoads( $filters);
		$data['total'] = $this->Billing->getFilteredLoads( $filters,true);
		
		$data['loads'] = $jobs;
		$data['billType'] = 'billing';
		$data['filterArgs'] = $_REQUEST;
		$data['filterArgs']["firstParam"] = $parameter;

		echo json_encode($data);
	}
		
	

	public function getRecords($parameter = ''){
		$params = json_decode(file_get_contents('php://input'),true);
		$total = 0;
		$jobs = array();
		if($params["pageNo"] < 1){
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] + 1);	
		}else{
			$params["limitStart"] = ($params["pageNo"] * $params["itemsPerPage"] );	
		}
		

		if((isset($params["sortColumn"]) && empty($params["sortColumn"])) || !isset($params["sortColumn"])){ $params["sortColumn"] = "PickupDate"; }
		if((isset($params["sortType"]) && empty($params["sortType"])) || !isset($params["sortType"])){ $params["sortType"] = "DESC"; }
		if(!isset($params["startDate"])){ $params["startDate"] = ''; }
		if(!isset($params["endDate"])){ $params["endDate"] = ''; }

		

		
		$params["filterType"] = isset($params["filterArgs"]["filterType"]) ? $params["filterArgs"]["filterType"] : 'all';
		$params["userType"] = isset($params["filterArgs"]["userType"]) ? $params["filterArgs"]["userType"] : 'all';
		$params["userId"] = isset($params["filterArgs"]["userToken"]) ? $params["filterArgs"]["userToken"] : false;

		

		if($params["userType"] == "team" || $params["userType"] == "driver"){

			$driverInfo = $this->Driver->getInfoByDriverId($params["userId"]);
			if(isset($driverInfo[0])){ $driverInfo = $driverInfo[0]; }
			$params["vehicleId"] = isset($driverInfo["vehicleId"])  ? $driverInfo["vehicleId"] : '';
			$params["dispatcherId"] = isset($driverInfo["dispatcherId"])  ? $driverInfo["dispatcherId"] : '';
		}


		$jobs = $this->Billing->getFilteredLoads( $params);
		$total = $this->Billing->getFilteredLoads( $params,true);
		if(!$jobs){$jobs = array();}
		echo json_encode(array("data"=>$jobs,"total"=>$total));
	}	
	
}



