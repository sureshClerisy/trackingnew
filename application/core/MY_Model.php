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
		
		
	}
	 
	
	

