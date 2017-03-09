<?php
ini_set('max_execution_time', 600); 
error_reporting(-1);
ini_set('display_errors',1);
class Scrap extends CI_Controller {

	public $data = array();
	public $rows = array();
	public $cols = array();
	public $url;
	public $userId;

	function __construct() {
		parent::__construct();
		$this->load->model('BrokersModel');
		$this->load->library('Htmldom');
	}

	public function index(){
	   
	  
	   $html = file_get_html("http://www.findfuelstops.com/truck-stop-interstate");	
	  $countArray = array();
		foreach($html->find('a') as $key=>$element){
			
			//if(($key>=9)&&($key<=10))
			if($key == 9)
			{
				
				$anchor = file_get_html("http://www.findfuelstops.com".$element->href);
				
				$i=0;
				foreach($anchor->find('a') as $anchorkey=>$anchorElement){
					$scrap = array();
					$scrap["name"]			     = '';
					$scrap["address"]            = '';
					$scrap["city"]               = '';
					$scrap["state"]              = '';
					$scrap["country"]            = '';
					$scrap["zipcode"]            = '';
					$scrap["interstate"]         = '';
					$scrap["latitude"]           = '';
					$scrap["longitude"]          = '';
					$scrap["phone"]              = '';
					$scrap["parking_spaces"]     = '';
					$scrap["showers"]            = '';
					$scrap["restaurant"]         = '';
					$scrap["atm"]                = '';
					$scrap["repair"]             = '';
					$scrap["security"]           = '';
					$scrap["store"]              = '';
					$scrap["internet"]           = '';
					$scrap["diesel_per_gallon"]  = '';
					$scrap["link"]				 = '';
					
					
					$newkey = '';
					$newColomnkey = '';
					
					
					//echo $anchorkey.'==='. $anchorElement->href.'<br>';
					if(((strpos($anchorElement,"#"))==false)&&((strpos($anchorElement,"/truck-stop"))==true)&&($anchorkey<=26))
					{
						$i++;
						echo $anchorkey.'==='. $anchorElement->href.'key==='.$key.'<br>';
						//~ $mainData = file_get_html("http://www.findfuelstops.com".$anchorElement->href);
						//~ $scrap["link"] = $anchorElement->href;
						 //~ 
						//~ foreach($mainData->find('input[id="MapCoords"]') as $mapkey=>$mapElement){
							//~ $coordinates = explode(",",trim($mapElement->value));	
							//~ $scrap["latitude"] = $coordinates[0];
							//~ $scrap["longitude"] = $coordinates[1];
						//~ }
						//~ foreach($mainData->find('input[id="TSName"]') as $mapkey=>$mapElement){	
							//~ $scrap["name"] = trim($mapElement->value);
						//~ }
						//~ foreach($mainData->find('input[id="MapAddress"]') as $mapkey=>$mapElement){	
							//~ $scrap["address"] = trim($mapElement->value);
						//~ }
						//~ 

						//~ pr($scrap);	
						//~ $scrapData = array(
							//~ 'id'         	=>'',
							//~ 'name'       	=>$scrap["name"],
							//~ 'address'    	=>$scrap["address"],
							//~ 'city'       	=>$scrap["city"],
							//~ 'state'      	=>$scrap["state"],
							//~ 'country'    	=>$scrap["country"],
							//~ 'zip'			=>$scrap["zipcode"],
							//~ 'interstate'	=>$scrap["interstate"],
							//~ 'latitude'		=>$scrap["latitude"],
							//~ 'longitude'		=>$scrap["longitude"],
							//~ 'phone'			=>$scrap["phone"],
							//~ 'parking_spaces'=>$scrap["parking_spaces"],
							//~ 'showers'		=>$scrap["showers"],
							//~ 'retaurants'	=>$scrap["restaurant"],
							//~ 'atm'			=>$scrap["atm"],
							//~ 'repair'		=>$scrap["repair"],
							//~ 'security'		=>$scrap["security"],
							//~ 'store'			=>$scrap["store"],
							//~ 'internet'		=>$scrap["internet"],
							//~ 'fuel_per_gallon'=>$scrap["diesel_per_gallon"],
							//~ 'link'			=>$scrap["link"]
						//~ );
						//$this->BrokersModel->scrapData($scrapData,$key,$anchorkey);

						foreach($mainData->find('input[id="MapCoords"]') as $mapkey=>$mapElement){
							$coordinates = explode(",",trim($mapElement->value));	
							$scrap["latitude"] = $coordinates[0];
							$scrap["longitude"] = $coordinates[1];
						}
						foreach($mainData->find('input[id="TSName"]') as $mapkey=>$mapElement){	
							$scrap["name"] = trim($mapElement->value);
						}
						foreach($mainData->find('input[id="MapAddress"]') as $mapkey=>$mapElement){	
							$scrap["address"] = trim($mapElement->value);
						}
						foreach($mainData->find('tr') as $rowkey=>$rowElement){
							foreach($rowElement->find('td') as $columnkey=>$columnElement){
								if((strpos($columnElement,"ULSD"))==true)
								{
									echo "new outer key".$newkey = $rowkey;
									if($columnkey>0){
										echo "new clmn key".$newColomnkey = $columnkey;
									}
								}
							}
							if($scrap["diesel_per_gallon"]==''){
								if($rowkey == $newkey){
									foreach($rowElement->find('td') as $columnkey=>$columnElement){
										if(($columnkey==($newColomnkey+1))&&($newColomnkey>0)){
											$scrap["diesel_per_gallon"] = trim(strip_tags($columnElement));
											echo "diesel_per_gallon".$scrap["diesel_per_gallon"]."<br><br>";
											}
										
									}
								}
							}
							if($rowkey==2){
								foreach($rowElement->find('td') as $columnkey=>$columnElement){
									if($columnkey==0){
									
										$scrap["phone"] = trim(strip_tags($columnElement));
										
									}
									
								}
							}
							if($rowkey==3){
								foreach($rowElement->find('td') as $columnkey=>$columnElement){
									if($columnkey==0){
										
										$scrap["interstate"] = trim(strip_tags($columnElement));
										
									}
								}
							}
							
							if($rowkey==5){
								foreach($rowElement->find('td') as $columnkey=>$columnElement){
									if($columnkey==0){
										$address = trim(strip_tags($columnElement));
										$addrArray = explode(",",$address);
										$scrap["city1"] = $addrArray[0];
										$scrap["city"] = str_replace("'","`",$scrap["city1"]);
										$stateArray = explode("\t",$addrArray[1]);
										$stateArrayTrimmed = trim($addrArray[1]);
										//$aa =  str_replace(" ","",$b);
										$pin =  substr($stateArrayTrimmed,2);
										$scrap["zipcode"] = str_replace(array("_", "<br>","<br/>", "<br />","<",">","/","b","td"," ","&nbsp","&nbsp;","&nsp;"), "", $pin);
										$scrap["state"] = substr($stateArrayTrimmed,0,2);
										$scrap["country"] = 'USA';
									
										
									}
								}
							}
							foreach($mainData->find('table[class="amenities"]') as $rowkey=>$rowElement){	
					
							foreach($rowElement->find('td') as $columnkey=>$columnElement){
								
								
								
								if($scrap["internet"] != 'Yes'){
									if((strpos($columnElement,"Internet"))==true)
									{
										$scrap["internet"] = 'Yes';
									}
									else
									{
										$scrap["internet"] = 'No';
									}
								}
								if($scrap["atm"] != 'Yes'){
									if((strpos($columnElement,"ATM"))==true)
									{
										$scrap["atm"] = 'Yes';
									}
									else
									{
										$scrap["atm"] = 'No';
									}
								}
								if($scrap["restaurant"] != 'Yes'){
									if((strpos($columnElement,"Restaurant"))==true)
									{
										$scrap["restaurant"] = 'Yes';
									}
									else
									{
										$scrap["restaurant"] = 'No';
									}
								}
								if($scrap["showers"] != 'Yes'){
									if((strpos($columnElement,"Showers"))==true)
									{
										$scrap["showers"] = 'Yes';
									}
									else
									{
										$scrap["showers"] = 'No';
									}
								}
								if($scrap["parking_spaces"] != 'Yes'){
									
									if((strpos($columnElement,"Parking"))==true)
									{
										$scrap["parking_spaces"] = 'Yes';
									}
									else
									{
										$scrap["parking_spaces"] = 'No';
									}
								}
								if($scrap["repair"] != 'Yes'){
									
									if((strpos($columnElement,"Repair"))==true)
									{
										$scrap["repair"] = 'Yes';
									}
									else
									{
										$scrap["repair"] = 'No';
									}
								}
								if($scrap["store"] != 'Yes'){
									if((strpos($columnElement,"Store"))==true)
									{
										
										$scrap["store"] = 'Yes';
									}
									else
									{
										$scrap["store"] = 'No';
									}
								}
								if($scrap["security"] != 'Yes'){
									if((strpos($columnElement,"Security"))==true)
									{
										$scrap["security"] = 'Yes';
									}
									else
									{
										$scrap["security"] = 'No';
									}
								}
							}
						}
						}
						pr($scrap);	
						
						$scrapData = array(
							'id'         	=>'',
							'name'       	=>$scrap["name"],
							'address'    	=>$scrap["address"],
							'city'       	=>$scrap["city"],
							'state'      	=>$scrap["state"],
							'country'    	=>$scrap["country"],
							'zip'			=>$scrap["zipcode"],
							'interstate'	=>$scrap["interstate"],
							'latitude'		=>$scrap["latitude"],
							'longitude'		=>$scrap["longitude"],
							'phone'			=>$scrap["phone"],
							'parking_spaces'=>$scrap["parking_spaces"],
							'showers'		=>$scrap["showers"],
							'retaurants'	=>$scrap["restaurant"],
							'atm'			=>$scrap["atm"],
							'repair'		=>$scrap["repair"],
							'security'		=>$scrap["security"],
							'store'			=>$scrap["store"],
							'internet'		=>$scrap["internet"],
							'fuel_per_gallon'=>$scrap["diesel_per_gallon"],
							'link'			=>$scrap["link"]
						);
						
						pr($scrapData);
						//~ $this->BrokersModel->scrapData($scrapData,$key,$anchorkey);

				  
				
					}
				}
				echo "no. of rows are ".$i;
			}
		}
	}
	
	public function test(){
		  
	$html = file_get_html("http://www.findfuelstops.com/truck-stop-on-I-75");	

	$countArray = array();
	
	//echo count($html->find('div.content table.content'));
	
		foreach($html->find('div.content table.content') as $key=> $element){
		//	echo $element;
			
			foreach( $element->find('tbody tr') as $key1 => $value ) {
				$td = 0;
				foreach ( $value->find('td a') as $innerKey => $tdValue ) {
					if ( $td == 0 ) {
						$mainData = file_get_html("http://www.findfuelstops.com".$tdValue->href);
						
						$countArray[] = $tdValue->href;
						$scrap = array();
						$scrap["name"]			     = '';
						$scrap["address"]            = '';
						$scrap["city"]               = '';
						$scrap["state"]              = '';
						$scrap["country"]            = '';
						$scrap["zip"]           	 = '';
						$scrap["interstate"]         = '';
						$scrap["latitude"]           = '';
						$scrap["longitude"]          = '';
						$scrap["phone"]              = '';
						$scrap["parking_spaces"]     = 'no';
						$scrap["showers"]            = 'no';
						$scrap["restaurant"]         = 'no';
						$scrap["atm"]                = 'no';
						$scrap["repair"]             = 'no';
						$scrap["security"]           = 'no';
						$scrap["store"]              = 'no';
						$scrap["internet"]           = 'no';
						$scrap["fuel_per_gallon_usa"] = '';
						$scrap["fuel_per_gallon_ca"]  = '';
						$scrap["link"]				 = '';
						
						foreach($mainData->find('div#stopdetails table tr') as $rowkey=>$rowElement){
							if ( $rowkey == 0 ) {
								$scrap['name'] = trim($rowElement->find('td',0)->plaintext);
							}
							
							if ( $rowkey == 1 ) {
								$scrap['phone'] = trim($rowElement->find('td',0)->plaintext);
							}
							
							if ( $rowkey == 2 ) {
								$scrap['interstate'] = trim($rowElement->find('td',0)->plaintext);
							}
							
							if ( $rowkey == 3 ) {
								$scrap['address'] .= trim($rowElement->find('td',0)->plaintext);
							}
							
							if( $rowkey == 4 ) {
								$cityState = trim($rowElement->find('td',0)->plaintext);
								$cityArr = explode(',',$cityState);
								
								if ( !empty($cityArr) && $cityArr[0] != '' ) {
									$scrap['city'] = $cityArr[0];
									$stringlength = strlen($cityArr[1]);
									$state = substr($cityArr[1],0,2);
							
									$zip = substr($cityArr[1],2,$stringlength);
									$newZip = str_replace('&nbsp;','',$zip);	
								
									if ( $state != '' ) {
										$scrap['state'] = trim($state);
									}
									if ( $zip != '' ) 
										$scrap['zip'] = str_replace(' ','',$newZip);
								}
							}
							
							if ( $rowkey == 5 ) {								
								if ( strpos($rowElement->find('td',0)->plaintext,'N/A') !== false ) {
									$scrap['fuel_per_gallon_usa'] = 'N/A';
									$scrap['fuel_per_gallon_ca'] = 'N/A';
								} else {
									$diese = 0;
									$ulsd = 0;
									$dieselArr = array();
								
									$innerDiesel = $rowElement->find('td',0);
									foreach( $innerDiesel->find('table tr td') as $dieselRate ) {
										if ( (strtolower(trim($dieselRate->innertext))) == 'ulsd' ) {
											$ulsd =  $diese;
										}
										$dieselArr[] = $dieselRate->innertext;
										$diese++;
									}
									
									if ( $ulsd == 0 ) {
										$scrap['fuel_per_gallon_usa'] = $dieselArr[1];
										$scrap['fuel_per_gallon_ca'] = $dieselArr[2];
									} else {
										$scrap['fuel_per_gallon_usa'] = $dieselArr[$ulsd + 1];
										$scrap['fuel_per_gallon_ca'] = $dieselArr[$ulsd + 2];	
									}
								}
							}
						}
						
						foreach($mainData->find('div.amenities table.amenities tr td') as  $columnElement) {	
							if((strpos($columnElement,"Internet")) == true) {
								$scrap["internet"] = 'Yes';
							}
							
							if((strpos($columnElement,"ATM")) == true) {
								$scrap["atm"] = 'Yes';
							}
							
							if((strpos($columnElement,"Restaurant")) == true) {
								$scrap["restaurant"] = 'Yes';
							}
						
							if((strpos($columnElement,"Showers")) == true) {
								$scrap["showers"] = 'Yes';
							}
							
							if((strpos($columnElement,"Parking")) == true) {
								$scrap["parking_spaces"] = 'Yes';
							}
					
							if((strpos($columnElement,"Repair")) == true) {
								$scrap["repair"] = 'Yes';
							}
							
							if((strpos($columnElement,"Store")) == true) { 
								$scrap["store"] = 'Yes';
							}
							
							if((strpos($columnElement,"Security")) == true) {
								$scrap["security"] = 'Yes';
							}
						}
						
						foreach($mainData->find('input[id="MapCoords"]') as $mapkey=>$mapElement) {
							$coordinates = explode(",",trim($mapElement->value));	
							$scrap["latitude"] = $coordinates[0];
							$scrap["longitude"] = $coordinates[1];
						}
						$scrap['link'] = $tdValue->href;
						$scrap['country'] = 'USA';
					}
					$td++;
					
				}
				pr($scrap);
				$this->BrokersModel->scrapData($scrap,$scrap['link']);
			}
			
		}
		
		echo '<br/>'.'total records inserted are = '.count($countArray);
	}
}

?>

