<?php
class BrokersModel extends Parent_Model
{

	function __construct() 
	{
        parent::__construct();
        $this->load->library('email');
        $this->limit_per_page = $this->config->item('limit_per_page');  
		
    }

    public function get_brokers()
    {
        $this->db->select('*')->from('broker_info');
        $this->db->where("delete_broker",0);
        $result = $this->db->get(); 
        if($result->num_rows()>0){
			return $result->result_array();
        } else {
			return false;
		}
    } 
  
    /**
     * Add and update the broker table
     */ 
    public function add_update_broker($data = array(),$id = null) {
		$this->db->select('id');
		$this->db->where('MCNumber',$data['MCNumber']);
		$brokerRes = $this->db->get('broker_info');
		if ( $brokerRes->num_rows() > 0 ) {
			$finalResult = $brokerRes->row_array();
		
			$broker_id = $finalResult['id'];
			$this->db->where('id',$broker_id);
			$res = $this->db->update('broker_info',$data);
			$brokerLastId = $broker_id;
			$updated = 'yes';
		} else {
			if (!empty($id)) {
				$this->db->update('broker_info',$data,"id={$id}");
				$brokerLastId = $id;
			} else {
				$this->db->insert('broker_info',$data);
				$brokerLastId = $this->db->insert_id();
			}
			$updated = 'no';
		}
		return array($updated,$brokerLastId);
		
	}

	public function deleteBrocker($id=null){

		$this->db->where('id', $id);
		$data = array(
			'delete_broker' => 1
		);
		$this->db->update('broker_info',$data);

  		//  $this->db->where('id',$id);
		// $result = $this->db->delete('broker_info');
        return true;
    }
	
	public function getRelatedBroker($broker_id = null){
		$this->db->select('*');
		$this->db->where('id',$broker_id);
		$result = $this->db->get('broker_info');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	}
	
	public function changeBlackListStatus($broker_id = null, $status = null ) {
		if ( $status == 0 || $status == '' || $status == null ) {
			$newStatus = 1;
		} else {
			$newStatus = 0;
		}
		
		$this->db->where('id',$broker_id);
		$data = array(
			'black_list' => $newStatus,
		);
		$result = $this->db->update('broker_info',$data);
		return true;
	}
	
	/**
	 * Getting brokers list for dropdown on add load
	 */
	 
	public function getBrokersList() {
		$this->db->select('TruckCompanyName,id,MCNumber');
		$whereCondition = array('black_list' => 0, 'MCNumber !=' => 0 );
		$this->db->where($whereCondition)->order_by("TruckCompanyName","asc");
		$result = $this->db->get('broker_info');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	} 
	
	/**
	 * Fetching broker complete Detail on click
	 */
	 
	public function getBrokerInfo( $brokerId = null ) {
		//~ return $this->db->select('*')->where('broker_info.id',$brokerId)->get('broker_info')->row_array();
		return $this->db->select('id,MCNumber,TruckCompanyName,postingAddress,city,state,zipcode,CarrierMC,DOTNumber,brokerStatus,DebtorKey,black_list,rating')->where('broker_info.id',$brokerId)->get('broker_info')->row_array();
	}

	public function getBrokerInfoByMCNumber( $mcNumber = null ) {
		//~ return $this->db->select('*')->where('broker_info.id',$brokerId)->get('broker_info')->row_array();
		return $this->db->select('id,MCNumber,TruckCompanyName,TruckCompanyPhone, postingAddress,city,state,zipcode,CarrierMC,DOTNumber,brokerStatus,DebtorKey,black_list,rating')->where('broker_info.MCNumber',$mcNumber)->get('broker_info')->row_array();
	}
	
	/**
	 * Fetching broker information after checking loadId
	 */
	 
	public function getBrokerDetail( $loadId = null, $type = '' ) {
		$brokerId = '';
		$brokerDetail = array();
		$loadInfo = array();
		
		if ( $loadId != '' && $loadId != null ) {
			$loadInfo = $this->db->select('loads.PointOfContact,loads.PointOfContactPhone,loads.TruckCompanyEmail,loads.TruckCompanyPhone,loads.TruckCompanyFax,loads.broker_id')->where('loads.id',$loadId)->get('loads')->row_array();
			$brokerId = $loadInfo['broker_id'];
		}
			
		if ( $brokerId != '' && $brokerId != null && $type == 'broker' ) {
			$brokerDetail = $this->getBrokerInfo( $brokerId );
		} else if ( $brokerId != '' && $brokerId != null && $type == 'shipper' ) {
			$brokerDetail = $this->db->select('id,shipperCompanyName,postingAddress,city,state,zipcode,rating')->where('shippers.id',$brokerId)->get('shippers')->row_array();
		}
		
		if ( !empty($loadInfo) ) {
			$brokerDetail['PointOfContact'] = $loadInfo['PointOfContact'];
			$brokerDetail['PointOfContactPhone'] = $loadInfo['PointOfContactPhone'];
			$brokerDetail['TruckCompanyEmail'] = $loadInfo['TruckCompanyEmail'];
			$brokerDetail['TruckCompanyPhone'] = $loadInfo['TruckCompanyPhone'];
			$brokerDetail['TruckCompanyFax'] = $loadInfo['TruckCompanyFax'];
		}
		
		return $brokerDetail;
	} 
	
	/**
	 * Updating broker information on new request
	 */
	 
	public function updateBrokerInfo( $id = null, $data = array() ){
		if ( !empty($data) ) {
			$this->db->where('id',$id);
			$this->db->update('broker_info',$data);
		}
		return true;
	} 
	
	/**
	 * Update broker documents_list in broker_info table
	 */
	 
	public function uploadBrokerDocument( $fileName = '', $entityId = null, $parameter = '' ) {
		$docs = array(
			'document_name' => $fileName,
			'entity_type' => $parameter,
			'entity_id' => $entityId
		);
		$this->insertContractDocument($docs);
		return true;
	} 
	
	/**
	 * Fetch list of black listed brokers
	 */
	
	public function getListOfBlacklistedBrokersOld() {//
		$broker_array = array();
		$this->db->select("id,REPLACE(REPLACE(REPLACE(REPLACE(`PointOfContactPhone`,'(',''),')',''),'-',''),' ','') as phoneNumber");
		$this->db->where('broker_info.black_list',1);
		$res = $this->db->get('broker_info');
		if ( $res->num_rows() > 0 ) {
			foreach( $res->result_array() as $result ) {
				array_push($broker_array,$result['phoneNumber']);
			}
			return $broker_array;
		} else {
			return $broker_array;
		}
	}

	public function getListOfBlacklistedBrokers() {
		
		$broker_array = array();
		
		$this->db->select("DISTINCT(broker_info.id),loads.TruckCompanyPhone,loads.PointOfContactPhone");
		$this->db->from('broker_info');
		$this->db->join('loads', 'broker_info.id = loads.broker_id');
		$this->db->where('broker_info.black_list',1);
		$data = $this->db->get()->result_array();
		
		if(!empty($data)){
			foreach( $data as $result ) {				
				$broker_array[] = str_replace(array('(', ')', ' ','-'), '', $result['PointOfContactPhone']);
				$broker_array[] = str_replace(array('(', ')', ' ','-'), '', $result['TruckCompanyPhone']);
			}
		}
		return $broker_array;
	}

	/**
	* Method getListOfBlacklisted
	* Get black listed companies name
	* @param NULL
	* @return Array
	*/

	public function getListOfBlacklisted() {
		$data = $this->db->select("LOWER(company_name) AS company")->from('blacklisted_brockers')->get()->result_array();
		// echo $this->db->last_query();
		return array_column($data, 'company');
	}
	
	
	public function getBrokerslisting() {
		$this->db->select('id,MCNumber');
		$result = $this->db->get('broker_info');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	} 
	
	/**
	 * Check if broker exist in db
	 */
	  
	public function checkTriumphDataExist ( $mcNumber = null ) {
		$this->db->select('*');
		$this->db->where('MCNumber',$mcNumber);
		$result = $this->db->get('broker_info');
		if ($result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}		
	}

	/**
	* Fetch top 5 customers who produced maximum invoice
	*/

	public function getTopFiveCustomer($args = array() ){

		$this->db->select('bi.TruckCompanyName , SUM(loads.PaymentAmount) AS Total,loads.broker_id' );
		$this->db->join('broker_info AS bi', 'bi.id = loads.broker_id','Left');

		if ( isset($args['startDate']) ) 
			$this->db->where('loads.PickupDate >=', $args['startDate']);

		if ( isset($args['endDate']))
			$this->db->where('loads.PickupDate <=', $args['endDate']);

		if( isset($args['driverId']) && !empty($args['driverId']) ) {
			$this->db->where('loads.driver_id', $args['driverId']);
		} 

		if ( isset($args['secondDriverId']) && !empty($args['secondDriverId']) && $args['secondDriverId'] > 0 ) {
			$this->db->where(array('loads.second_driver_id' => $args['secondDriverId'], 'driver_type' => 'team'));
		}

		if( isset($args['dispatcherId']) && !empty($args['dispatcherId']) ) {
			$this->db->where('loads.dispatcher_id', $args['dispatcherId']);
		}

		$this->db->where_in("loads.JobStatus",$this->config->item('loadStatus'));
		$this->db->where('delete_status',0);
		$this->db->where('broker_id is NOT NULL', NULL, FALSE);
		$this->db->group_by("loads.broker_id");
		$this->db->order_by("Total DESC");
		$this->db->limit(5,0);
		$result = $this->db->get('loads');
		// echo $this->db->last_query();
		if( $result->num_rows() > 0 )
			return $result->result_array();
		else
			return array();
	}
	

	/**
	* Fetch loads of particular broker
	*/
	 
	public function getBrokerRelatedLoads( $brokerId, $startDate = '', $endDate = '', $filters = array(), $total = false ,$export=NULL) {
		if(count($filters) <= 0){
			$filters = array("itemsPerPage"=>20, "limitStart"=>1, "sortColumn"=>"DeliveryDate", "sortType"=>"DESC","status"=>"");
		}

		$data =  $condition = array();
		if(!$total) {
        $this->db->select('loads.created,loads.overallTotalProfit,loads.overallTotalProfitPercent,CONCAT( d.first_name ," + " ,team.first_name) AS teamdriverName, 
        					CASE loads.driver_type 
        						WHEN "team" THEN CONCAT(d.first_name," + ",team.first_name) 
        									ELSE concat(d.first_name," ",d.last_name) 
        									END AS driverName, loads.driver_type, loads.id, loads.invoiceNo, loads.vehicle_id, loads.truckstopID,loads.Bond,loads.PointOfContactPhone,loads.equipment_options,loads.LoadType,loads.PickupDate,loads.DeliveryDate,loads.OriginCity,loads.OriginState,loads.DestinationCity,loads.DestinationState,loads.PickupAddress,loads.DestinationAddress,loads.PaymentAmount,loads.Mileage, (loads.PaymentAmount/loads.Mileage) as rpm, loads.deadmiles,loads.Weight,loads.Length,loads.JobStatus,loads.totalCost,loads.pickDate,loads.load_source,broker_info.TruckCompanyName as companyName');
		} else {
			$this->db->select("count(loads.id) as count");
		}	
		$this->db->join("vehicles as v", "loads.vehicle_id = v.id","Left");
		$this->db->join("drivers as d", "loads.driver_id = d.id","Left");
		$this->db->join('broker_info', 'broker_info.id = loads.broker_id','Left');
		$this->db->join('drivers as team','v.team_driver_id = team.id','left');	
		
		if ( isset($filters['dispatcherId']) && $filters['dispatcherId'] != '' )
			$this->db->where('loads.dispatcher_id',$filters['dispatcherId']);

		if ( isset($filters['secondDriverId']) && $filters['secondDriverId'] != '' && $filters['secondDriverId'] != 0 && $filters['driverId'] != '' )
			$this->db->where(array('loads.driver_id' => $filters['driverId'], 'loads.second_driver_id' => $filters['secondDriverId'], 'loads.driver_type' => 'team'));
		else if ( isset($filters['driverId']) && $filters['driverId'] != '' )
			$this->db->where('loads.driver_id',$filters['driverId']);


		if ( $startDate != '' && $startDate != 'undefined' ) {
			$startDate = date('Y-m-d',strtotime($startDate)); 
			$endDate = date('Y-m-d',strtotime($endDate)); 
			$string = " (`loads`.`PickupDate` >='".$startDate."' AND `loads`.`PickupDate` <= '".$endDate."')";
			$this->db->where($string);
		}

		$this->db->where_in("loads.JobStatus",$this->config->item('loadStatus'));
	
		$this->db->where('loads.delete_status',0);
		$this->db->where('loads.broker_id', $brokerId );

		if(isset($filters["searchQuery"]) && !empty($filters["searchQuery"])){
            $this->db->group_start();
            $this->db->like('LOWER(CONCAT(TRIM(d.first_name)," ",TRIM(d.last_name)))', strtolower($filters['searchQuery']));
            $this->db->or_like('loads.id', $filters['searchQuery'] );
            $this->db->or_like('loads.invoiceNo', $filters['searchQuery'] );
            $this->db->or_like('loads.PointOfContactPhone', $filters['searchQuery'] );
            $this->db->or_like('LOWER(loads.equipment_options)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.LoadType)', strtolower($filters['searchQuery']) );
            $this->db->or_like('loads.PickupDate', $filters['searchQuery'] );
            $this->db->or_like('loads.DeliveryDate', $filters['searchQuery'] );
            $this->db->or_like('LOWER(loads.OriginCity)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.OriginState)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationCity)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationState)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.PickupAddress)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.DestinationAddress)', strtolower($filters['searchQuery']) );
            $this->db->or_like('loads.PaymentAmount', $filters['searchQuery']);
            $this->db->or_like('loads.Mileage', $filters['searchQuery']);
            $this->db->or_like('loads.deadmiles', $filters['searchQuery']);
            $this->db->or_like('loads.Weight', $filters['searchQuery']);
            $this->db->or_like('loads.Length', $filters['searchQuery']);
            $this->db->or_like('LOWER(loads.JobStatus)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(loads.load_source)', strtolower($filters['searchQuery']) );
            $this->db->or_like('LOWER(broker_info.TruckCompanyName)', strtolower($filters['searchQuery']) );
            $this->db->group_end();
		}

		$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
	
		if(isset($filters["sortColumn"]) && $filters["sortColumn"] == "driverName"){
			$this->db->order_by('CASE 
								     WHEN loads.driver_type  = "team" THEN CONCAT(TRIM(d.first_name), " + ", TRIM(team.first_name)) 
								     ELSE concat(d.first_name, " ", d.last_name) 
								 END '.$filters["sortType"]);
		}else if(in_array($filters["sortColumn"] ,array("TruckCompanyName"))){
			$this->db->order_by("broker_info.".$filters["sortColumn"],$filters["sortType"]);	
		}else if($filters["sortColumn"] == "rpm"){
			$this->db->order_by("(loads.PaymentAmount/loads.Mileage) ",$filters["sortType"]);	 
		}else if($filters["sortColumn"] == "Weight"){
			$this->db->order_by("CAST(loads.".$filters["sortColumn"]."  AS DECIMAL)",$filters["sortType"]);	
		}else{
			$this->db->order_by("loads.".$filters["sortColumn"],$filters["sortType"]);	
		}

		if(!$total ){
			$filters["limitStart"] = $filters["limitStart"] == 1 ? 0 : $filters["limitStart"];
			if(!empty($filters['export'])){//bypass while export data
				$this->db->limit($filters["itemsPerPage"],$filters["limitStart"]);
			}
		}
		
		$query = $this->db->get('loads');
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}

	}

	public function fetchDriversForCSV($search = null) {

        $this->db->select('*')->from('broker_info');

        $this->db->where("delete_broker",0);
        if(!empty($search['searchText'])){
        	$this->db->like('broker_info.TruckCompanyName',$search['searchText']); 
			$this->db->or_like('broker_info.postingAddress',$search['searchText']);
			$this->db->or_like('broker_info.city',$search['searchText']);
			$this->db->or_like('broker_info.state',$search['searchText']);
			$this->db->or_like('broker_info.zipcode',$search['searchText']);
			$this->db->or_like('broker_info.MCNumber',$search['searchText']);
			$this->db->or_like('broker_info.MCNumber',$search['searchText']);
			$this->db->or_like('broker_info.CarrierMC',$search['searchText']);
			$this->db->or_like('broker_info.DOTNumber',$search['searchText']);
			$this->db->or_like('broker_info.brokerStatus',$search['searchText']);
        }

        $result = $this->db->get(); 
        if($result->num_rows()>0){
			return $result->result_array();
        } else {
			return false;
		}
    } 
}

