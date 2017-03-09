<?php
class Login extends CI_Controller {

	function __construct()
	{ 
		parent::__construct();	
		$this->load->model('User');
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
		} else { 
			echo json_encode(array('success'=>false));
		}
	}

	public function logout()
	{
		$this->session->unset_userdata('role');
		$this->session->unset_userdata('loggedUser_email');
		$this->session->unset_userdata('loggedUser_username');
		$this->session->unset_userdata('loggedUser_id');
		$this->session->unset_userdata('loggedUser_loggedin');
		echo json_encode(array('success' => true));
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
