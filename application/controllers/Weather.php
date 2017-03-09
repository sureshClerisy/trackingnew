<?php

ini_set('max_execution_time', 300); 

class Weather extends CI_Controller {

	public $data = array();
	public $rows = array();
	public $cols = array();
	public $url;
	public $userId;

	function __construct() {
		parent::__construct();
	
		//~ $this->load->model('admin/User');
		//~ $this->load->model('admin/Job');
		$this->load->library('Htmldom');
		//~ if($this->session->role != 1){
			//~ $this->userId = $this->session->admin_id;
		//~ }
	}


	public function index(){
	$curl = curl_init('http://investors.ryder.com/rss/pressrelease.aspx');
	$result = curl_exec($curl);
	curl_close($curl);
	$array_result = json_decode($result, TRUE);	
	print_r($array_result);		
	//~ $feed = file_get_contents('http://investors.ryder.com/rss/pressrelease.aspx');
	//~ $feed = str_replace('<media:', '<', $feed);
	//~ $rss = simplexml_load_string('http://investors.ryder.com/rss/pressrelease.aspx');
	//~ foreach ($rss->channel->item as $item) {
		//~ echo "<pre>";
		//~ print_r($item);
	//~ }
	}


}
