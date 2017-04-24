<?php
/****************************
*                           *
* Truck stop Api Controller *
*                           *
*****************************/

class Utilities extends Admin_Controller{

	function __construct(){
		parent::__construct();
	}

	public function getNextPredictedJobs($dispatcherId){
		$dispatcherId = 35;
		$this->load->model("Utility");
		$response = $this->Utility->getNextPredictedJobs($dispatcherId);
		foreach ($response as $key => $value) {
			$response[$key]["jobs"] = unserialize($value["jobs"]);
		}
		echo json_encode( $response);
	}
}
?>