<?php
defined('BASEPATH') OR exit('No direct script access allowed');

	class MY_Model extends CI_Model {
		function __construct()
		{
			parent::__construct();
		}
	}
	   
    class Parent_Model extends MY_Model
	{		
		function __construct() {
			parent::__construct();
		}
		
		/**
		 * checking required field for load to generate invoice
		 */
		 
		public function checkRequiredFeildsForInvoice( $loadId = null ) {	
			$this->db->select('loads.JobStatus,loads.id,loads.ready_for_invoice,broker_info.TruckCompanyName');
			$this->db->join('broker_info','broker_info.id = loads.broker_id','LEFT');
			$this->db->join('documents','documents.load_id = loads.id','LEFT');
			//~ $this->db->where(array('loads.woRefno !=' => '','billing_details.shipper_name != ' => '', 'billing_details.consignee_name !=' => '', 'billing_details.transportComp_name !=' => '','loads.vehicle_id != ' => null, 'loads.vehicle_id != ' => 0, 'loads.delete_status' => 0, 'loads.id' => $loadId));			
			$this->db->where(array('loads.vehicle_id != ' => null, 'loads.vehicle_id != ' => 0, 'loads.delete_status' => 0, 'loads.id' => $loadId, 'loads.broker_id != ' => null, 'loads.broker_id !=' => 0,'loads.JobStatus' => 'completed' ));			
			$this->db->where_IN('documents.doc_type',array('pod','rateSheet'));
			$this->db->group_by('documents.load_id');
			$this->db->having('count(*) > 1', null, false );
			$result = $this->db->get('loads');
			if ( $result->num_rows() > 0 ) {
				$data['ready_for_invoice'] = 1;
				$this->db->where('loads.id',$loadId);
				$this->db->update('loads',$data);
			} 		
			return true;
		}
		
		/*
		* Request URL: 
		* Method: get
		* Params: entityId, entity_type
		* Return: documents array or empty array
		* Comment: Used for fetching documents list for driver or trailer or truck or broker
		*/
		
		public function fetchContractDocuments($entityId = null, $entityType = '') {
			$this->db->select('id,document_name');
			$this->db->where(array('entity_id' => $entityId, 'entity_type' => $entityType ));
			$result = $this->db->get('contract_docs');
			if ( $result->num_rows() > 0 ) {
				return $result->result_array();
			} else {
				return array();
			}
		}
		
		/*
		* Request URL: 
		* Method: get
		* Params: documentName, entityId, entity_type
		* Return: success or false
		* Comment: Used for inserting documents
		*/
		
		public function insertContractDocument($docs = array() ) 
		{
			$this->db->insert('contract_docs',$docs);
			return true;
		}
		
		/*
		* Request URL: 
		* Method: get
		* Params: id
		* Return: success or false
		* Comment: Used for deleting documents
		*/
		
		public function removeContractDocs($docId = null ) 
		{
			$this->db->where('id',$docId);
			$this->db->delete('contract_docs');
			return true;
		}


		/*
		* Request URL: 
		* Method: get
		* Params: docId,entityType
		* Return: array or false
		* Comment: Used for getting entity info by document id
		*/
		public function getEntityInfoByDocId($docId,$entityType) {
			$table = "drivers";
			switch ($entityType) {
				case 'driver' : $this->db->select("drivers.id,contract_docs.document_name, drivers.first_name, drivers.last_name");
							    $this->db->join("contract_docs","contract_docs.entity_id = drivers.id","inner"); 	$table = "drivers"; break;
				case 'truck'  : $this->db->select("vehicles.id,contract_docs.document_name, vehicles.label");
							    $this->db->join("contract_docs","contract_docs.entity_id = vehicles.id","inner"); 	$table = "vehicles"; break;
				case 'trailer': $this->db->select("trailers.id, contract_docs.document_name, trailers.unit_id");
							    $this->db->join("contract_docs","contract_docs.entity_id = trailers.id","inner"); 	$table = "trailers"; break;
				case 'broker' : $this->db->select("broker_info.id, contract_docs.document_name, broker_info.TruckCompanyName");
							    $this->db->join("contract_docs","contract_docs.entity_id = broker_info.id","inner"); 	$table = "broker_info"; break;
			}
			$this->db->where('contract_docs.id', $docId);
			$this->db->where('contract_docs.entity_type', $entityType);
			$result = $this->db->get($table);
			if( $result->num_rows() > 0 ) {
				return $result->row_array();
			} else {
				return false;
			}
		}


		/*
		* Request URL: 
		* Method: get
		* Params: docId,entityType
		* Return: array or false
		* Comment: Used for getting entity info by document id
		*/
		public function getEntityInfoById($id,$entityType) {
			$table = "drivers";
			switch ($entityType) {
				case 'dispatcher'  : $this->db->select("users.id, users.first_name, users.last_name"); $table = "users"; break;
				case 'driver'  	   : $this->db->select('drivers.id, case loads.driver_type
															when "team" then concat(drivers.first_name," + ",team.first_name)
															ELSE concat(drivers.first_name," ",drivers.last_name) end as driverName'); 
									 $this->db->join('drivers','drivers.id = loads.driver_id','LEFT');
									 $this->db->join('drivers as team','team.id = loads.second_driver_id','LEFT');
									 $table = "loads"; break;
				case 'truck'	   : $this->db->select("vehicles.id, vehicles.label"); $table = "vehicles";  break;
				case 'broker'	   : $this->db->select("broker_info.TruckCompanyName"); $table = "broker_info";  break;
			}
			$this->db->where($table.'.id', $id);
			$result = $this->db->get($table);
			//if($entityType == "driver"){ echo $this->db->last_query();die;}
			if( $result->num_rows() > 0 ) {
				return $result->row_array();
			} else {
				return false;
			}
		}


		/*
		* Request URL: 
		* Method: get
		* Params: userID, fields = array()
		* Return: array or false
		* Comment: Used for getting user info from user tabl
		*/
		public function getUserInfo($userId,$fields = array()) {
			if(count($fields) > 0){
				$this->db->select(implode(",", $fields));
			}else{
				$this->db->select('*');	
			}
			$this->db->where('id', $userId);
			$result = $this->db->get('users');
			if( $result->num_rows() > 0 ) {
				return $result->row_array();
			} else {
				return false;
			}
		}
		
		
	}
	 
	
	

