<?php
/**
* 
*/
class Garages extends Admin_Controller{
	
	function __construct() {
		parent::__construct();
		$this->load->library('email');

		$this->load->model('Garage');		
	}

	public function index(){
		$data = array();
		$data['page_title'] = 'Garage listing';
		$search = '';
		$page = '';
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
		
		$data['rows'] = $this->Garage->fetchAllRecords($search,$page,$sort,$order);		
		$data['total_records'] = $this->Garage->countAllRecords($search,$page);
		
		echo json_encode($data);
	}

	public function add(){

		if($this->input->post()){
			$this->Garage->add_edit_garage();
			$this->session->set_flashdata('success', 'Garage has been created successfully.');
			redirect('/admin/garages');
		}

		$data = array();
		$data['page_title'] = 'Adding New Garage';
		$data['page_sidebar'] = $this->load->view('admin/sidebar',$data,true);
		$data['page_body'] = $this->load->view('admin/garage/add',$data,true);
		$this->load->view('admin/admin',$data);
	}

	 
	public function edit( $garage_id = Null ){
		$data['page_title'] = 'Edit Garage';
		$data['garages'] = $this->Garage->get_row($garage_id);
		echo json_encode($data);
	}
	
	public function update() {
		$_POST = json_decode(file_get_contents('php://input'), true); 
    	$id = $_POST['id'];
    	$result = $this->Garage->add_edit_garage($_POST, $id);
    	
		echo json_encode(array('success' => true));
	}

	public function napi_save_data(){

    	$post_data = $this->input->post();
    	
		if(isset($post_data['garage']) && !empty($post_data['garage'])){
			
			$this->Garage->napi_save_all_vehicle();
			$this->session->set_flashdata('success', 'Garage has been saved successfully.');
			redirect('/admin/garages');
		}else{
			$this->Garage->napi_save_vehicle();
		}
	}

	public function delete($garageID=null){
		$result = $this->Garage->delete( $garageID );
		
		if ( $result ) {
			echo json_encode(array('success' => true));
		} else {
			echo json_encode(array('success' => false));
		}
		
	}
	public function addGarage(){
		$_POST = json_decode(file_get_contents('php://input'), true); 
		
    	$result = $this->Garage->add_edit_garage($_POST);
    	
		echo json_encode(array('success' => true));	
	}
}

