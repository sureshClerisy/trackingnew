<?php

ini_set('max_execution_time', 300); 

class Scraptest extends CI_Controller {

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
		
		$html = file_get_html("http://www.findfuelstops.com/truck-stop-interstate");	
		foreach($html->find('a') as $key=>$element){
			
			 if($key==9)
			{
				 echo $key.'==='. $element->href.'<br>';
				 $anchor = file_get_html("http://www.findfuelstops.com".$element->href);
				foreach($anchor->find('a') as $anchorkey=>$anchorElement){
					 //$anchorkey.'==='. $anchorElement->href.'<br>';
					 //echo "<br><br>anchorkey".$anchorkey."<br><br>";
					 //echo "<br><br>Anchor Element".var_dump($anchorElement->href);
					 if(((strpos($anchorElement,"#"))==false)&&((strpos(trim($anchorElement),"/stop"))==true))
					{
						echo $anchorkey.'==='. $anchorElement->href.'<br>';
						//~ $mainData = file_get_html("http://www.findfuelstops.com/truck-stop-000483");
						//print_r($mainData);
						//~ foreach($mainData->find('tr') as $rowkey=>$rowElement){
								//~ echo $rowkey."outer<br>";
								//~ foreach($rowElement->find('td') as $columnkey=>$columnElement){
									//~ 
										//~ echo $columnkey.'==='. $columnElement.'<br>';
									//~ 
								//~ }
								//~ 
							//~ 
						//~ }
					}
					
					
			//print_r($htmlAnchor):
		     }
        }
			
	}
}

}
