<?php
class Login extends CI_Controller {

	private $entity;
	private $event;
	protected $superAdmin = 0;
	protected $userID;

	function __construct()
	{ 
		parent::__construct();	
		$this->load->model(array('User','Job','AclModel'));
		$this->entity 	= $this->config->item('entity');
		$this->event 	= $this->config->item('event');
		$this->load->helper("truckstop");
		$this->userID   = $loggedUser_id = $this->session->userdata('loggedUser_id');

		if(in_array($this->userID, $this->config->item('superAdminIds'))){
			$this->superAdmin = 1;
		}

	}
	

	function index()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		$data = array();
		if( $this->User->check_admin_credentials($username, $password) == true ) {
			
			$profile_img 	= $this->User->user_Profile($this->session->userdata('loggedUser_id'));
			$roleId 		= $this->session->userdata('role');

			if ( $roleId == 8 ) {

				$result 		= $this->User->getOrganisationsList();
				$selectedOrg 	= $this->User->getSelectedOrganisation($this->session->userdata('loggedUser_id'));
				$selectedId 	= (!empty($selectedOrg) && !empty($selectedOrg['organisation_id'])) ? $selectedOrg['organisation_id'] : ( !empty($result) ? $result[0]['id'] : '');
				$selectedOrgName = (!empty($selectedOrg) && !empty($selectedOrg['name'])) ? $selectedOrg['name'] : ( !empty($result) ? $result[0]['first_name'] : '');

				if ( !isset($selectedOrg) || empty($selectedOrg['name']) ) {					
					$data['name'] = $selectedOrgName;
					$data['organisation_id'] = $selectedId;
					$this->User->setSelectedOrganisation($this->session->userdata('loggedUser_id'),$data);
				} 

				if( !empty($result)) {
					$key = array_search($selectedId, array_column($result, 'id'));
					$temp = $result[$key];
					unset($result[$key]);
					array_unshift($result, $temp);
				}
				$redirectTo 		= 'dashboard';
				$showGlobalDropDown = true;
			} else {
				$result 			= array();
				$selectedOrgName 	= $selectedId = '';
				$showGlobalDropDown = false;

				if ( $roleId == 9 ) {
					$selectedId = $this->session->userdata('loggedUser_id');
					$getAllowedActions = $this->User->orgAllowedActions($this->session->userdata('loggedUser_id'));
				} else {
					$getAllowedActions = $this->User->roleAllowedActions($roleId);	
					$selectedId = $this->session->userdata('loggedUser_parentId');
				}	

				if ( !empty($getAllowedActions)) {
					$getAllowedActions = array_column($getAllowedActions,'action');
					if ( in_array('dashboard',$getAllowedActions) )
						$redirectTo = 'dashboard';
					else if ( in_array('assignedloads',$getAllowedActions))
						$redirectTo = 'myLoad';
					else if ( in_array('drivers',$getAllowedActions))
						$redirectTo = 'drivers';
					else if ( in_array('vehicles',$getAllowedActions))
						$redirectTo = 'trucks';
					else if ( in_array('reports',$getAllowedActions))
						$redirectTo = 'report';
					else if ( in_array('investors',$getAllowedActions))
						$redirectTo = 'investor';
					else if ( in_array('trailers',$getAllowedActions))
						$redirectTo = 'trailers';
					else if ( in_array('brokers',$getAllowedActions))
						$redirectTo = 'broker';
					else
						$redirectTo = 'logout';
				} else {
					$redirectTo = 'logout';
				}				
			}

			echo json_encode(
				array(
					'success'			  =>true,
					'LastName' 			  => $this->session->userdata('LastName'),
					'color' 			  => $this->session->userdata('color'),
					'loggedUser_email' 	  => $this->session->userdata('loggedUser_email'),
					'loggedUser_username' => $this->session->userdata('loggedUser_username'),
					'loggedUser_fname' 	  => $this->session->userdata('loggedUser_fname'),
					'loggedUser_id' 	  => $this->session->userdata('loggedUser_id'),
					'loggedUserRole_id'   => $this->session->userdata('role'),
					'profile_img' 		  => $profile_img,
					'organisations'       => $result,
					'selectedOrgName'     => $selectedOrgName, 
					'selectedId'   		  => $selectedId, 
					'redirectUserTo' 	  => $redirectTo,
					'showGlobalDropDown'  => $showGlobalDropDown
					)
				);

			$message = '<span class="blue-color uname">'.ucfirst($this->session->userdata('loggedUser_username'))."</span> logged in from ip : <b>".$_SERVER['REMOTE_ADDR']."</b>";
			logActivityEvent($this->session->userdata('loggedUser_id'),$this->entity["user"], $this->event["login"], $message, $this->Job);
		} else { 
			echo json_encode(array('success'=>false));
		}
	}

	public function logout()
	{
		$this->User->removeSelectedOrganisation($this->session->userdata('loggedUser_id'));
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

		$user_logged_username 	= $this->session->userdata('loggedUser_username');
		$user_logged_id 		= $this->session->userdata('loggedUser_id');
		$user_logged_in 		= $this->session->userdata('loggedUser_loggedin');
		$user_role_id 			= $this->session->userdata('role');
		$myModules = [];
		if( (isset($user_logged_username) && $user_logged_username != '') && (isset($user_logged_id) && $user_logged_id != '') && (isset($user_logged_in) && $user_logged_in == true) ) {
				$myModules = $this->User->getAssignedModules();
				$selectedOrgId = 0;
				if(in_array($user_logged_id, $this->config->item('superAdminIds'))){
					$res = $this->User->getSelectedOrganisation($user_logged_id);
					$selectedOrgId = ( !empty($res)) ? $res['organisation_id'] : 0;
				} else if ( $user_role_id == 9 ) {
					$selectedOrgId = $user_logged_id;
				} else {
					$selectedOrgId = $this->session->userdata('loggedUser_parentId');
				}

			echo json_encode(array('success' => true ,'modules'=>$myModules,'superadmin'=>$this->superAdmin,"selectedOrgId"=>$selectedOrgId));

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
		$post = json_decode(file_get_contents('php://input'),true);
		$this->session->set_userdata('userTimeZone', $post["tz"]);
		$response = array();
		$response["ureadCount"]    = $this->Job->getUnreadNotificationsCount($userId);
		$notifications = $this->Job->getNotifications($userId);
		//$response["notifications"] = $this->Job->getNotifications($userId,$filters);
		if(count($notifications) > 0){
			foreach ($notifications as $key => $value) {
				$notifications[$key]["created_at"] = toLocalTimezone($value["created_at"]);
			}
		}
		$response["notifications"] = $notifications;
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
		$notifications = $this->Job->getNotifications($userId,$filters);
		//$response["notifications"] = $this->Job->getNotifications($userId,$filters);
		if(count($notifications) > 0){
			foreach ($notifications as $key => $value) {
				$notifications[$key]["created_at"] = toLocalTimezone($value["created_at"]);

				//echo $notifications[$key]["created_at"];die;
			}
		}
		$response["notifications"] = $notifications;
		echo json_encode($response);
	}

	/*
	* Request URI : http://siteurl/Login/ticketActivity
	* Method : post
	* Params : null
	* Return : null
	* Comment: used for fetch notifications
	*/
	public function ticketActivity($loadId = false){
		//$userId = $this->session->userdata('loggedUser_id');
		//$filters = json_decode(file_get_contents('php://input'),true);
		
		$response = $result = array();
		if($loadId){
			$result = $this->Job->getTicketActivity($loadId);
			foreach ($result as $key => $value) {
				if($value["event_type"] == "add" && $value["entity_name"] == "ticket"){
					$result[$key]["title"] = "Added a ticket.";
					$result[$key]["event_color"] = "primary";
				}

				if($value["event_type"] == "add_to_queue" && $value["entity_name"] == "ticket"){
					$result[$key]["title"] = "Added a ticket to queue.";
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
				if($value["event_type"] == "remove_from_queue" && $value["entity_name"] == "ticket"){
					$result[$key]["title"] = "Remove ticket from queue.";
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
				$result[$key]["created_at"] = toLocalTimezone($value["created_at"]);
			}
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
		$notifications = $this->Job->getNotifications($userId);
		//$response["notifications"] = $this->Job->getNotifications($userId,$filters);
		if(count($notifications) > 0){
			foreach ($notifications as $key => $value) {
				$notifications[$key]["created_at"] = toLocalTimezone($value["created_at"]);
			}
		}
		$response["notifications"] = $notifications;
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
				// 'expire' => time()+86500,
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

		$this->lang->load('billing');
		$languageArray['billing'] = json_encode($this->lang->language);
		echo "languageArr['billing']=".$languageArray['billing'].';';

		$this->lang->load('users');
		$languageArray['users'] = json_encode($this->lang->language);
		echo "languageArr['users']=".$languageArray['users'].';';
		
		//~ $this->lang->load('loads');	
		//~ $load = json_encode($this->lang->language);
		//~ echo "var loadsArr=".$load.';';
	}

	public function get_current_user_sidebar(){

	}

	/**
	* set forgot password
	*/

	public function forgotPassword() {
		$postData = json_decode(file_get_contents('php://input'), true);
		$email = $postData['email'];
		$res = $this->User->forgotPassword($email);
		if($res == 'emailNotExist') {
			echo json_encode(array('status' => 'emailNotExist'));
		} else{
			echo json_encode(array('status' => 'success'));
		}
	}

	/**
	* 
	* set reset password
	*/

	public function resetPassword( $hash=null ) {
		
		$postData 	= json_decode(file_get_contents('php://input'), true);

		if(!empty($_REQUEST['hash'])){
			echo json_encode(array('data'=>$_REQUEST['hash']));die();
		}
		
		if(!empty($postData['password'])){
			echo json_encode(array('status'=>$this->User->resetPassword($postData)));die();
		}
	}
}