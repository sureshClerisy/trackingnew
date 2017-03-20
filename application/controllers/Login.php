<?php
class Login extends CI_Controller {

	private $entity;
	private $event;
	function __construct()
	{ 
		parent::__construct();	
		$this->load->model('User');
		$this->load->model("Job");
		$this->entity = $this->config->item('entity');
		$this->event = $this->config->item('event');
		$this->load->helper("truckstop");
	}
	
	function index()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$username = $_POST['username'];
		$password = $_POST['password'];
		$data = array();
		if( $this->User->check_admin_credentials($username, $password) == true ) {
			$profile_img = $this->User->user_Profile($this->session->userdata('loggedUser_id'));
			echo json_encode(array('success'=>true,'loggedUser_email' => $this->session->userdata('loggedUser_email'),'loggedUser_username' => $this->session->userdata('loggedUser_username'),'loggedUser_fname' => $this->session->userdata('loggedUser_fname'),'loggedUser_id' => $this->session->userdata('loggedUser_id'),'loggedUserRole_id' => $this->session->userdata('role'),'profile_img' => $profile_img));
			$message = '<span class="blue-color uname">'.ucfirst($this->session->userdata('loggedUser_username'))."</span> logged in from ip : <b>".$_SERVER['REMOTE_ADDR']."</b>";
			logActivityEvent($this->session->userdata('loggedUser_id'),$this->entity["user"], $this->event["login"], $message, $this->Job);
		} else { 
			echo json_encode(array('success'=>false));
		}
	}

	public function logout()
	{
		if($this->session->userdata('loggedUser_id')){
			$message = '<span class="blue-color uname">'.ucfirst($this->session->userdata('loggedUser_username'))."</span> logged out from ip : <b>".$_SERVER['REMOTE_ADDR']."</b>";
			logActivityEvent($this->session->userdata('loggedUser_id'), $this->entity["user"], $this->event["logout"], $message, $this->Job);	
		}
		$this->session->unset_userdata('role');
		$this->session->unset_userdata('loggedUser_email');
		$this->session->unset_userdata('loggedUser_username');
		$this->session->unset_userdata('loggedUser_id');
		$this->session->unset_userdata('loggedUser_loggedin');
		echo json_encode(array('success' => true ));
	}
	
	public function checkLogin() {
	
		$user_logged_username = $this->session->userdata('loggedUser_username');
		$user_logged_id = $this->session->userdata('loggedUser_id');
		$user_logged_in = $this->session->userdata('loggedUser_loggedin');
		
		if( (isset($user_logged_username) && $user_logged_username != '') && (isset($user_logged_id) && $user_logged_id != '') && (isset($user_logged_in) && $user_logged_in == true) ) {
			echo json_encode(array('success' => true ));
		} else {
			echo json_encode(array('success' => false ));
		}
		
	}

	/*
	* Request URI : http://siteurl/Login/getNotifications
	* Method : post
	* Params : null
	* Return : null
	* Comment: used for fetch notifications
	*/
	public function getNotifications(){
		$userId = $this->session->userdata('loggedUser_id');
		$response = array();
		$response["ureadCount"]    = $this->Job->getUnreadNotificationsCount($userId);
		$response["notifications"] = $this->Job->getNotifications($userId);
		echo json_encode($response);
	}

	/*
	* Request URI : http://siteurl/Login/getNotifications
	* Method : post
	* Params : null
	* Return : null
	* Comment: used for fetch notifications
	*/
	public function notifications(){
		$userId = $this->session->userdata('loggedUser_id');
		$filters = json_decode(file_get_contents('php://input'),true);
		
		if(isset($filters["pageNo"]) && $filters["pageNo"] > 0){
			$filters["itemsPerPage"] = 15;
			$filters["limitStart"] = (($filters["pageNo"] -1) * $filters["itemsPerPage"]);
		}
		$response = array();
		$response["total"]    = $this->Job->getNotificationsTotal($userId);
		$response["notifications"] = $this->Job->getNotifications($userId,$filters);

		echo json_encode($response);
	}

	/*
	* Request URI : http://siteurl/Login/ticketActivity
	* Method : post
	* Params : null
	* Return : null
	* Comment: used for fetch notifications
	*/
	public function ticketActivity($loadId){
		//$userId = $this->session->userdata('loggedUser_id');
		//$filters = json_decode(file_get_contents('php://input'),true);
		
		$response = array();
		$result = $this->Job->getTicketActivity($loadId);
		foreach ($result as $key => $value) {
			if($value["event_type"] == "add" && $value["entity_name"] == "ticket"){
				$result[$key]["title"] = "Added a ticket.";
				$result[$key]["event_color"] = "primary";
			}
			if($value["event_type"] == "edit" && $value["entity_name"] == "ticket"){
				$result[$key]["title"]        = "Edited the ticket.";
				$result[$key]["event_color"] = "warning";
			}
			if($value["event_type"] == "delete" && $value["entity_name"] == "ticket"){
				$result[$key]["title"] = "Deleted the ticket.";
				$result[$key]["event_color"] = "danger";
			}
			if($value["event_type"] == "upload_doc" && $value["entity_name"] == "ticket"){
				$result[$key]["title"] = "Uploaded a document.";
				$result[$key]["event_color"] = "info";
			}
			if(($value["event_type"] == "remove_doc" || $value["event_type"] == "overwrite_doc") && $value["entity_name"] == "ticket"){
				$result[$key]["title"] = "Deleted a document.";
				$result[$key]["event_color"] = "danger";
			}
			if($value["event_type"] == "bundle_document" && $value["entity_name"] == "ticket"){
				$result[$key]["title"] = "Generated a bundle document.";
				$result[$key]["event_color"] = "success";
			}
			if($value["event_type"] == "generate_invoice" && $value["entity_name"] == "ticket"){
				$result[$key]["title"] = "Generated an invoice.";
				$result[$key]["event_color"] = "success";
			}if($value["event_type"] == "add_to_queue" && $value["entity_name"] == "ticket"){
				$result[$key]["title"] = "Added to the queue.";
				$result[$key]["event_color"] = "primary";
			}
			$result[$key]["event_type"] = str_replace("_", " ", $result[$key]["event_type"]);
		}

		$response["notifications"]    = $result;
		echo json_encode($response);
	}

	/*
	* Request URI : http://siteurl/Login/flagAsRead
	* Method : post
	* Params : null
	* Return : null
	* Comment: used for fetch notifications
	*/
	public function flagAsRead(){
		$userId = $this->session->userdata('loggedUser_id');
		$this->Job->flagNotificationsAsRead($userId);
		$response = array();
		$response["ureadCount"]    = $this->Job->getUnreadNotificationsCount($userId);
		$response["notifications"] = $this->Job->getNotifications($userId);
		echo json_encode($response);
	}

	
	/**
	 * changing the language files according to module
	 */
	public function changeLanguage( $language = '', $fileName = '') {
		$result =  $this->User->getLangFileName( $fileName );
		$this->config->set_item('language',$language);
		$this->lang->load($result);	
		$langArr = $this->lang->language;
		echo json_encode($langArr);
	} 
	
	/**
	 * changing the common language file 
	 */
	public function changeCommonLanguage( $language = '', $fileName = '') {
		$this->config->set_item('language',$language);
		$this->lang->load($fileName);	
		$langArr = $this->lang->language;
		echo json_encode($langArr);
	} 
	
	/**
	 * Loading all the languages into one file
	 */
	 
	public function loadLanguages() {
		
		$cookieVariable = get_cookie('setLanguageGlobalVariable');
		if ( $cookieVariable == '' || $cookieVariable == null || $cookieVariable == 'undefined' ) {
			$cookie = array(
				'name'   => 'setLanguageGlobalVariable',
				'value'  => 'english',
				'expire' => time()+86500,
			 );
			set_cookie($cookie);
		}		
		//~ $this->config->set_item('language',$_COOKIE['setLanguageGlobalVariable']);
		$this->config->set_item('language',$cookieVariable);
		$languageArray = array();
		
		$this->lang->load('brokers');
		$languageArray['brokers'] = json_encode($this->lang->language);
		echo "var languageArr = [];";
		echo "languageArr['brokers']=".$languageArray['brokers'].';';
		
		$this->lang->load('dashboard');
		$languageArray['dashboard'] = json_encode($this->lang->language);
		echo "languageArr['dashboard']=".$languageArray['dashboard'].';';
		
		$this->lang->load('drivers');
		$languageArray['drivers'] = json_encode($this->lang->language);
		echo "languageArr['drivers']=".$languageArray['drivers'].';';
		
		$this->lang->load('loads');
		$languageArray['loads'] = json_encode($this->lang->language);
		echo "languageArr['loads']=".$languageArray['loads'].';';
		
		$this->lang->load('login');
		$languageArray['login'] = json_encode($this->lang->language);
		echo "languageArr['login']=".$languageArray['login'].';';
		
		$this->lang->load('trucks');
		$languageArray['trucks'] = json_encode($this->lang->language);
		echo "languageArr['trucks']=".$languageArray['trucks'].';';
		
		$this->lang->load('trailers');
		$languageArray['trailers'] = json_encode($this->lang->language);
		echo "languageArr['trailers']=".$languageArray['trailers'].';';
		
		//~ $this->lang->load('loads');	
		//~ $load = json_encode($this->lang->language);
		//~ echo "var loadsArr=".$load.';';
	}
	

}
?>
