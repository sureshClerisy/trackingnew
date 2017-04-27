<?php
/****************************
*                           *
* Truck stop Api Controller *
*                           *
*****************************/

class Utilities extends Admin_Controller{
	private $userRoleId;
	private $userId;

	function __construct(){
		parent::__construct();
		$this->userRoleId 	= $this->session->role;
		$this->userId 		= $this->session->loggedUser_id;
	}



	public function getNextPredictedJobs($dispatcherId){
		$this->load->model("Utility");
		$args = json_decode( $_COOKIE["_globalDropdown"], true );

		if($this->userRoleId == _DISPATCHER){
			$response = $this->Utility->getNextPredictedJobs($args, $dispatcherId);
		}else{
			$response = $this->Utility->getNextPredictedJobs($args);
		}
		
		if( count($response) > 0 && is_array($response) ){
			foreach ($response as $key => $value) {
				$response[$key]["jobs"] = (array) unserialize($value["jobs"]);
			}	
		}else{
			$response = array();
		}
		echo json_encode( $response , JSON_FORCE_OBJECT);
	}
}
?>