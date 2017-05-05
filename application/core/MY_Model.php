<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model {
	function __construct()
	{
		parent::__construct();

	}
}

class Parent_Model extends CI_Model {

	public  $superAdmin;
	public  $role;
	public  $userID;
	public 	$selectedOrgId;
	public  $setOrganisationRoleId;

	function __construct() {

		parent::__construct();
		$this->userID 	= $this->session->loggedUser_id;
		$this->role 	= $this->session->role;
		$this->setOrganisationRoleId = 9;

		if(in_array($this->userID, $this->config->item('superAdminIds'))){
			$this->superAdmin = true;
			$res = $this->getSelectedOrganisation($this->userID);
			$this->selectedOrgId = ( !empty($res)) ? $res['organisation_id'] : 0;
		} else if ( $this->role == 9 ) {
			$this->selectedOrgId = $this->userID;
		} else {
			$this->selectedOrgId = $this->session->userdata('loggedUser_parentId');
		}			
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

		/**
		* Request URL: 
		* Method  : get
		* @param  : entityId, entity_type
		* @return  : documents array or empty array
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
				case 'shipper': $this->db->select("shippers.id, contract_docs.document_name, shippers.shipperCompanyName");
				$this->db->join("contract_docs","contract_docs.entity_id = shippers.id","inner"); 	$table = "shippers"; break;
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
				case 'shipper'	   : $this->db->select("shippers.shipperCompanyName as TruckCompanyName"); $table = "shippers";  break;
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

		/*
		* Method: Get
		* Params: newDispatcherId, previousDispatcher
		* Return: success or error
		* Comment: Used for storing dispatcer driver assignments in disp_dri_logs
		*/

		public function addDispatcherDriverLog( $newDispatcherId = null, $oldDispatcherId = null, $parameter = '' ) {
			for( $i = 0; $i < 2; $i++) {
				if ( $i == 0 ) 
					$dispatcherId = $newDispatcherId;
				else
					$dispatcherId = $oldDispatcherId;

				if ( !empty($dispatcherId) && $dispatcherId != 0 ) {
					$driversList = $this->getDispatcherDriverList($dispatcherId);
					$driver = array();
					$teamArray = array();
					$assignedDriverIds = array();
					$assignedTeamIds = array();
					$idleDrivers = array();
					foreach( $driversList as $drivers ){
						if ( $drivers['driver_type'] == 'team' && $drivers['team_driver_id'] != '') {
							$teamArray[] = $drivers['team_driver_id'];
							$driver['team'][] = $drivers;
							$assignedTeamIds[] = $drivers['id'].':'.$drivers['team_driver_id'];
						} else if( ($drivers['driver_type'] == 'single' || $drivers['driver_type'] == '') && $drivers['vehicleId'] != '' ) {
							$teamArray[] = $drivers['team_driver_id'];
							$driver['single'][] = $drivers;
							$assignedDriverIds[] = $drivers['id'];
						} else if ( !in_array($drivers['id'],$teamArray)){
							$driver['others'][] = $drivers;
							$idleDrivers[] = $drivers['id'];
						}
					}
					
					$singleDrivers 	= isset($driver['single']) ? count($driver['single']) : 0;
					$idleCount      = isset($driver['others']) ? count($driver['others']) : 0;
					$teamDrivers 	= isset($driver['team']) ? count($driver['team']) : 0;
					$data = array(
						'dispatcher_id' 	=> $dispatcherId,
						'assigned_drivers'  => implode(',',$assignedDriverIds),
						'assigned_team' 	=> implode(',',$assignedTeamIds),
						'idle_drivers'		=> !empty($idleDrivers) ? implode(',',$idleDrivers) : '',
						'single' 			=> $singleDrivers,
						'team'				=> $teamDrivers,
						'idle'				=> $idleCount,
						'created'			=> date('Y-m-d H:i:s')
						);
					
					$this->db->insert('disp_dri_logs',$data);
				}
			}
			
		}

		/*
		* method 	: Get
		* Params 	: dispatcherId
		* Return  	: return drivers list array
		* Comment 	: used to fetch list of drivers to dispatcher
		*/

		public function getDispatcherDriverList( $dispatcherId = null ) {
			$this->db->distinct('drivers.id,team_driver_id');
			$this->db->select('drivers.id,vehicles.id as vehicleId,vehicles.team_driver_id,vehicles.driver_type');
			$this->db->join('vehicles','drivers.id = vehicles.driver_id','LEFT');
			
			$this->db->where(array('drivers.user_id' => $dispatcherId));
			$this->db->order_by('vehicles.driver_id','DESC');
			$result = $this->db->get('drivers');
			if( $result->num_rows() > 0 )
				return $result->result_array();
			else 
				return array();

		}

		/**
		* method 	: getColumnsList
		* @param 	: Table Name,Column List, Condition
		* @return  	: Return columns array
		* Comment 	: used to fetch list of columns
		*/

		public function getColumnsList( $table, $coumns = [],$condition = [] ) {
			if(!empty($condition)){
				$this->db->where($condition);
			}
			return $this->db->select(implode(',',$coumns))->get($table)->result_array();
		}

	/**
	* fetch permissions given to role
	*
	*/	
	public function getAssignedModules() {
		
		$assignedModules = [];
		if( $this->role == 8 ) {
			$data = $this->db->select('GROUP_CONCAT(LOWER(acl_actions.action)) as module')
					->where('parent_id',NULL)
			->get('acl_actions')
			->result_array();
			if(!empty($data[0]['module'])){
				$assignedModules = explode(',', $data[0]['module']);
			}
		} else if( $this->role == 9 ) {
			$data = $this->db->select('GROUP_CONCAT(LOWER(module_name)) as module')
			->where(['organisation_module.status'=>1,'organization_id'=>$this->userID])
			->get('organisation_module')
			->result_array();
			if(!empty($data[0]['module'])){
				$assignedModules = explode(',', $data[0]['module']);
			}
		} else {
			$this->db->select('LOWER(slug) as module');
			$this->db->join('acl_actions','acl_actions.id = role_permissions.aco_id');
			$this->db->where(['role_permissions.permission' => 1 , 'role_permissions.parent_id !=' => 0, 'role_permissions.role_id' => $this->role]);
			$data = $this->db->get('role_permissions');
			if($data->num_rows() > 0 ){
				$assignedModules = $data->result_array();
				$assignedModules = array_column($assignedModules, 'module');
			}

		}
		
		return $assignedModules;
	}

	/**
	* Get selected organisation id
	*/

	public function getSelectedOrganisation( $userId = null) {
		$this->db->select('name,organisation_id');
		$this->db->where('user_id',$userId);
		$result = $this->db->get('selected_organisation');
		if( $result->num_rows() > 0 )
			return $result->row_array();
		else
			return array();
	}

}