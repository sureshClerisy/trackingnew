<?php
class Departments extends Admin_Controller {
	function __construct()
	{ 
		parent::__construct();	
		
		$this->load->model('Department');		
	}

	public function index(){

		$data = array();
		$data['page_title'] = 'Departments listing';

		$search = '';
		$page = '';
		$order = '';
		$sort = '';
		if(!empty($this->input->get("search"))){
			$search = $this->input->get("search");		
		}
		
		if(!empty($this->input->get("page"))){
			$page = $this->input->get("page");		
		}
		if(!empty($this->input->get("sort"))){
			$sort = $this->input->get("sort");		
		}
		if(!empty($this->input->get("order"))){
			$order = $this->input->get("order");		
		}
		 if($order == 'true')
		 {
			 $order='desc';
		 }
		 else
		 {
			 $order='asc';
		 }
		
		$data['rows'] = $this->Department->fetchAllRecords($search,$page,$sort,$order);		
		$data['total_records'] = $this->Department->countAllRecords($search,$page);
		
		echo json_encode($data);
	}

	/*
	* Add Method for new Department
	* @params null
	* @data form data
	* @return type null
	*/
	public function add(){
		$this->form_validation->set_rules('department_name', 'Department Name', 'required');
		
		if ($this->form_validation->run() == TRUE)
		{ 	
			$data = $this->input->post();
			$data['created'] = date('Y-m-d h:m:s');

			$result = $this->Department->save( $data );
			if ($result) {
				$this->session->set_flashdata('success', 'The Department has been saved successfully.');
				redirect($this->config->item('admin_url').'/departments');
			} else {
				$this->session->set_flashdata('error', 'The Department could not be saved. Please try again.');
				redirect($this->config->item('admin_url').'/departments/add');
			}
		}

		$data = array();
		$data['page_title'] = 'Add Department';
		$data['page_sidebar'] = $this->load->view('admin/sidebar',$data,true);
		$data['page_body'] = $this->load->view('admin/departments/add',$data,true);
		$this->load->view('admin/admin',$data);
	}
	
	/*
	* edit Method for existing Department
	* @params null
	* @data form data
	* @return type null
	*/
	public function edit( $department_id = Null ){
		$data['page_title'] = 'Edit Department';
		$data['department'] = $this->Department->get_department_data($department_id);
		echo json_encode($data);
	}
	
	public function update() {
		$_POST = json_decode(file_get_contents('php://input'), true);

    	$insert = $this->input->post();
    	$id = $_POST['id'];
    	$result = $this->Department->update($insert, $id);
    	
		echo json_encode(array('success' => true));
	}
	
	public function delete($deptID=null){
		$result = $this->Department->delete( $deptID );
		
		if ( $result ) {
			echo json_encode(array('success' => true));
		} else {
			echo json_encode(array('success' => false));
		}
		
	}

	public function napi_save_data(){

		if($this->input->post()){			
			
			$post_data = $this->input->post();

			if(isset($post_data['department']) && !empty($post_data['department'])){

				$this->Department->napi_save_all_departments();
				$this->session->set_flashdata('success', 'Departments has been saved successfully.');
				redirect('/admin/departments');
			}else{
				
				$this->Department->napi_save_department();
			}
		}
	}
	
	public function locationtest(){
		$data = array();
		$data['page_title'] = 'Location Test';
		$data['page_sidebar'] = $this->load->view('admin/sidebar',$data,true);
		$data['page_body'] = $this->load->view('admin/departments/locationtest',$data,true);
		$this->load->view('admin/admin',$data);
	}
	
	public function getdistace() {
		$this->GetDrivingDistance(53.933271, 36.778261, -116.576504, -119.417932);
	}
	
	
	protected function GetDrivingDistance($location1, $location2) {//  AIzaSyBsZPuC5cca9gr4qPZ-p1O9hPzp3serepk 
		$location1 = urlencode($location1);
		$location2 = urlencode($location2);
		//$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&language=pl-PL";
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=$location1&destinations=$location2&key=AIzaSyBsZPuC5cca9gr4qPZ-p1O9hPzp3serepk";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response, true);
		//$dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
		//$time = $response_a['rows'][0]['elements'][0]['duration']['text'];
		
		echo '<pre>';
		//print_r($dist);
		print_r($response_a);
		die;
		
		//return array('distance' => $dist, 'time' => $time);
	}
	
}
