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
	 
	public function getBrokerDetail( $loadId = null ) {
		$brokerId = '';
		$brokerDetail = array();
		$loadInfo = array();
		
		if ( $loadId != '' && $loadId != null ) {
			$loadInfo = $this->db->select('loads.PointOfContact,loads.PointOfContactPhone,loads.TruckCompanyEmail,loads.TruckCompanyPhone,loads.TruckCompanyFax,loads.broker_id')->where('loads.id',$loadId)->get('loads')->row_array();
			$brokerId = $loadInfo['broker_id'];
		}
			
		if ( $brokerId != '' && $brokerId != null ) {
			$brokerDetail = $this->getBrokerInfo( $brokerId );
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
	 
	public function uploadBrokerDocument( $fileName = '', $brokerId = null ) {
		$docs = array(
			'document_name' => $fileName,
			'entity_type' => 'broker',
			'entity_id' => $brokerId
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
	
	 
}

