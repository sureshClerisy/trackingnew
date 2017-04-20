<?php

/**
* 
*/
class AclModel extends CI_Model {
	
	private $controller;
	private $action;
	private $controllerID;
	private $dateTime;

	function __construct() {
		parent::__construct();
		$this->dateTime = date('Y-m-d H:m:s');
	}

	public function getOrganisations(){
		return $this->db->select('id,username,CONCAT(first_name, ,last_name) as name')->where(['role_id'=>1,'id!='=>1])->get('users')->result_array();
	}

	public function syncControllers($data = null){
		
		foreach ($data as $controller => $actions){
			
			$this->controller 	= $controller;
			$this->controllerID = $this->insertController();
			foreach ($actions as $key => $action) {
				$this->action = $action;
				$this->insertAction();
			}
		}
	}

	public function insertController(){
		
		$dataRow 	= $this->db->select('id')
							->where('action',$this->controller)
							->get('acl_actions')
							->result_array();
		
		if(empty($dataRow)){
			
			$data = [
				'action'=>$this->controller,
				'created'=>"{$this->dateTime}",
				'updated'=>"{$this->dateTime}"
				];

			$this->db->insert('acl_actions',$data);
			return $last_id = $this->db->insert_id();
		}
		return $dataRow[0]['id'];
	}

	public function insertAction(){

		$dataRow 	= $this->db->select('id')
							->where(['action'=>$this->action,'parent_id'=>$this->controllerID])
							->get('acl_actions')
							->result_array();
		if(empty($dataRow)){
			
			$data = [
				'parent_id'=>$this->controllerID,
				'action'=>$this->action,
				'created'=>"{$this->dateTime}",
				'updated'=>"{$this->dateTime}"
				];
			$this->db->insert('acl_actions',$data);
		}
	}

	public function getActions(){
		return $this->db->select('*')->limit(30)->get('acl_actions')->result_array();
	}

	public function getRoles($organisationID = null){
		$this->db->select('roles.*,users.username')
				 ->join("users", "users.id = roles.user_id");
		if(!empty($organisationID)){
			$this->db->where('roles.id',$organisationID);
		}
		
		$data = $this->db->limit(30)
				 ->get('roles')
				 ->result_array();
				 
		return $data;
	}	

	public function getController(){

		$data = $this->db->select('acl_actions.id,acl_actions.action')
				->limit(30)
				->where('parent_id IS NULL')
				->get('acl_actions')
				->result_array();
		return $data;
	}
	
	public function addRole($data){
		// ex($data);

		$dateTime = date('Y-m-d h:m:s');
		$filterData = ['name'=>$data['data']['name'],'alias'=>$data['data']['alias'],'user_id'=>$this->session->loggedUser_id,'created'=>$dateTime,'updated'=>$dateTime];
		$this->db->insert('roles',$filterData);
	}
}