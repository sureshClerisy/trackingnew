<?php

class States extends Admin_Controller{

	function __construct(){
		parent::__construct();
		$this->load->model('User');
		$this->load->model('Vehicle');
	}

	public function fetch_states_areas(  $country ='USA' ) {
		$country = $country == 'undefined' ? 'USA': $country;
		$classes = array("","check-success","check-primary","check-info","check-warning");
		$states = $this->Vehicle->get_states_areas( $country );
		$regions = $this->Vehicle->get_regions_areas( $country );
		$response = $test = array();
		$i = $j=0;
		foreach ($states as $stkey => $stvalue) {
			$scode = explode(',', $stvalue['scode']);
			sort($scode);
			$k = 0;
			foreach ($regions as $rkey => $rvalue) {
				
				$reg = explode(',', $rvalue['scode']);
				$regions[$rkey]['class'] = $classes[$k++];
				foreach ($scode as $skey => $svalue) {
					if(in_array($svalue, $reg)){
						$states[$stkey]['codes'][$i]['state'] = $svalue;
						$states[$stkey]['codes'][$i]['regions'] = $rvalue['regions'];
						$states[$stkey]['codes'][$i]['bindmodel'] = $rvalue['regions'].$j;
						$states[$stkey]['codes'][$i]['class'] = $regions[$rkey]['class'];
						//$states[$stkey]['codes']['class'] = $regions[$rkey]['class'];
						$response[$rvalue['regions'].$j] = false;
						$i++;
					}
				}
				
			}

			$i = 0;
			$j++;
		}
		echo json_encode(array("country"=>$country,"areas"=>$states,"regions"=>$regions));
	}
	
	public function search_links() {
		$search = json_decode(file_get_contents('php://input'),true);
		$finalArray = array();
		
		if ( $search['searchResult'] != '' ) {
			$result = $this->User->search_links( $search['searchResult'] );
			if( $result ) {
				$finalArray = $result;
			}
		}
		
		echo json_encode(array('result' => $finalArray));
	}
	
	/**
	 * Fetching Data for csv
	 */
	  
	public function fetchDataForCsv() {
		$search = json_decode(file_get_contents('php://input'),true);
		$result = $this->User->getTableRecord( $search['tableName'],$search['primaryId'] );
		
		$keys = '';
		$values = '';
		
		foreach( $result as $key => $value ) {
			$keyvalue = $this->replaceUnderScore( $key );
			$keys .= $keyvalue.",";
			$value = str_replace(',',' ',$value);
			$value = str_replace(',',' ',$value);
			$value = str_replace(',',' ',$value);
			//~ $value = preg_replace('/\,/',' ',$value);
			$values .= $value.",";
		}
		
		$data['keys'] = rtrim($keys,',');
		$data['values'] = rtrim($values,',');
		
		echo json_encode($data);
	}
	
	/**
	 * Changing Underscore for table th in csv
	 */
	
	public function replaceUnderScore ( $key = '' ) {
		return ucfirst(str_replace('_', ' ',$key));
	} 
	

}
