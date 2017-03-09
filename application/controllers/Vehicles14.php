<?php
class Vehicles extends Admin_Controller {

	public $search;
	public $userId;
	public $roleId;

	function __construct()
	{ 
		parent::__construct();	
		$this->load->library('user_agent');
		$this->load->model('Vehicle','vehicle');		
		$this->load->model('Garage','garage');
		
		if($this->session->role != 1){
			$this->userId = $this->session->loggedUser_id;
		}
		$this->roleId = $this->session->role;	
	
	}

	public function index() {
		$data = array();
		$search = '';
		$page = '';
		$data['page_title'] = 'Trucks';
		$sort = '';
		$order = '';
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
		//$page = $this->input->get("page",1);			
		$data['rows'] = $this->vehicle->get_vechicls($search,$page,$this->userId,$sort,$order);
		$data['total_records'] = $this->vehicle->get_vechicles_count($search,$page,$this->userId);
		
		echo json_encode($data);

	}

	/*
	* Add Method for new vehicle
	* @params null
	* @data form data
	* @return type null
	*/
	public function add(){

		if($this->input->post()){
			$this->vehicle->add_edit_vehicle();
			$this->session->set_flashdata('success', 'Vehicle has been added successfully.');
			redirect('/admin/vehicles');
		}

		$data = array();
		$data['page_title'] = 'Adding New Truck';
		$data['garages_list'] = $this->garage->garag_list();
		$data['page_sidebar'] = $this->load->view('admin/sidebar',$data,true);
		$data['page_body'] = $this->load->view('admin/vehicles/add',$data,true);
		// $this->load->view('admin/template',$data);
		$this->load->view('admin/admin',$data);
	}

	/*
	* Add Method for new vehicle
	* @params null
	* @data form data
	* @return type null
	*/
	/*public function edit($id=null){
		$data['page_title'] 	= 'Update Truck';
		$data['garages_list'] 	= $this->garage->garag_list();
		$data['row'] 			= $this->vehicle->get_row($id);
		
		echo json_encode($data);
	}*/
	public function edit( $vehicle_id = Null ){

		
		$data['page_title'] = 'Edit Truck';
		$data['trucks'] = $this->vehicle->get_row($vehicle_id);
		echo json_encode($data);
	}
	
	public function update() {
		
		$_POST = json_decode(file_get_contents('php://input'), true);
		print_r($_POST);
    	$id = $_POST['id'];
    	$result = $this->vehicle->add_edit_vehicle($_POST, $id);
    	
		echo json_encode(array('success' => true));
	}
	
	public function napi_save_data(){
		
		$post_data = $this->input->post();

		if(isset($post_data['vehicles']) && !empty($post_data['vehicles'])){
			
			$this->vehicle->napi_save_all_vechiles();
			$this->session->set_flashdata('success', 'Vehicles has been saved saved successfully.');
			redirect('/admin/vehicles');
		}else{
			$this->vehicle->napi_save_vehicle();
		}
	}

	public function view($id=null)
	{

		$data['row'] 			= $this->vehicle->get_row($id);
		$data['page_title'] 	= 'Vehicle Details';
		$data['page_sidebar'] 	= $this->load->view('admin/sidebar',$data,true);
		$data['page_body'] 		= $this->load->view('admin/vehicles/view',$data,true);
		$this->load->view('admin/template',$data);
	}




	public function delete($truckID=null){
		$result = $this->vehicle->deleteVehicle($truckID);
		if ( $result ) {
			echo json_encode(array('success' => true));
		} else {
			echo json_encode(array('success' => false));
		}
		
	}
}
