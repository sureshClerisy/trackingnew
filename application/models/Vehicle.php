<?php

/**
 * 
 */
class Vehicle extends Parent_Model 
{
	public $limit_per_page;
    function __construct() {
        parent::__construct();
        $this->load->library('email');
        $this->limit_per_page = $this->config->item('limit_per_page');  
		
    }

    public function get_vechicls( $userId = false ) {
		$this->db->DISTINCT();
		$this->db->select('CONCAT(concat( drivers.first_name, " ", `drivers`.`last_name` )," + ", concat(team.first_name," ",team.last_name)) AS teamDriverName  , vehicles.id,vehicles.label, CONCAT(users.first_name," ",users.last_name) AS dispatcher, vehicles.vin, vehicles.model, vehicles.vehicle_type,vehicles.vehicle_status,vehicles.cargo_bay_l, vehicles.cargo_capacity,concat(drivers.first_name," ",drivers.last_name) as driverName,GROUP_CONCAT(equipment_types.name) as vehicleType',FALSE)->from('vehicles');
        $this->db->join('drivers','drivers.id=vehicles.driver_id','LEFT');
        $this->db->join('equipment_types',("FIND_IN_SET(equipment_types.abbrevation , vehicles.vehicle_type) > 0"), 'LEFT');
        $this->db->join('users',("drivers.user_id = users.id"), 'LEFT');
        $this->db->join('drivers as team','vehicles.team_driver_id = team.id','left');
      
        if($userId)
			$this->db->where('vehicles.user_id',$userId);   
			
		$this->db->Group_by('vehicles.id');
        $result = $this->db->get(); 
        if($result->num_rows()>0){
			return $result->result_array();
        } else {
			return false;
		}
    } 
    
    public function get_vechicles_count($search = '', $page = null, $userId=null) {

        if($search){
			$this->db->or_group_start();
            $this->db->like('model', $search);
            $this->db->or_like('label', $search);
            $this->db->or_like('vehicle_type', $search);
            $this->db->group_end();
        }
       
        if(!empty($userId)){
            $this->db->where('vehicles.user_id',$userId);    
        }
		
		$num_rows = $this->db->count_all_results('vehicles');

		// echo $this->db->last_query();

		return $num_rows;

	}

    public function add_edit_vehicle($data = array(),$id = null) {

        $data = $data;

        $data['created'] = date('Y-m-d h:m:s');
        $data['modified'] = date('Y-m-d h:m:s');

        if(!empty($_FILES)){
				if(!empty($_FILES['vehicle_image']['name'])){
					$fileName = $config['file_name']   = time().str_replace(' ', '_', $_FILES['vehicle_image']['name']);
					$input = 'vehicle_image';
				} else {
				$input = 'vehicle_image';
				$fileName = $config['file_name']   = time().$_FILES['vehicle_image']['name'];
			}

			$config['upload_path']          = 'assets/uploads/vehicles/';
			$config['allowed_types']        = 'gif|jpg|png|jpeg';
			$this->load->library('upload', $config);
			if ( $this->upload->do_upload($input))
			{
				$config['image_library'] = 'gd2';
				$config['source_image'] = 'assets/uploads/vehicles/'.$fileName;	
				$config['new_image'] = './assets/uploads/vehicles/thumbnail/';
					//~ $config['create_thumb'] = TRUE;
				$config['maintain_ratio'] = TRUE;
				$config['width']         = 200;
				$config['height']       = 200;	
				$this->load->library('image_lib',$config);
				$this->image_lib->resize();

				$error = array('error' => $this->upload->display_errors());
				print_r($error);
				unset($data['vehicle_image1']);
				$data['vehicle_image'] = $fileName;
			}

		}
		unset($data['vehicle_image1']);
		unset($data['gid']);
		unset($data['organization_name']);
		unset($data['driverName']);
    
		if (!empty($id)) {
			$this->db->update('vehicles',$data,"id={$id}");
			$last_id = $id;
		} else {
			$this->db->insert('vehicles', $data);
			$last_id = $this->db->insert_id();
		}
		return $last_id;
	}


	public function count_all_records($keyword = null,$id=null) {

		if ($keyword) {
			$this->db->like('model', $keyword);
			$this->db->or_like('label', $keyword);
			$this->db->or_like('vehicle_type', $keyword);
			//$num_rows   = $this->db->count_all_results('vehicles');
		} 

		if(!empty($id)){
			$this->db->where('user_id',$id);
		}
		$num_rows = $this->db->count_all_results('vehicles');

		// echo $this->db->last_query();

		return $num_rows;
	}

    /*
    * Get Vehicle by ID
    * Get Vehicle by ID
    * @return type boolean or data
    */

    public function get_row($id=null){

        $data = $this->db->select('concat(dj.first_name," ", dj.last_name) as teamDriverTwo,  vehicles.*,concat(drivers.first_name," ",drivers.last_name) as driverName')->from('vehicles')
        ->join('drivers','vehicles.driver_id = drivers.id','left')
        ->join('drivers as dj','vehicles.team_driver_id = dj.id','left')
        ->where('vehicles.id',$id)->get();

        if($data->num_rows()>0){
            return $data->row_array();
        }
        return false;
    }

    public function deleteVehicle($id=null){
        $this->db->where('id',$id);
		$result = $this->db->delete('vehicles');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function napi_save_all_vechiles() {

        $vehicles_info = $this->input->post();
        
        foreach ($vehicles_info['vehicles'] as $key => $vehicle) {

            $data = unserialize($vehicle);
            
            $data->navixy_id    = $data->id;
            unset($data->location);
            unset($data->id);

            $this->db->where("navixy_id",$data->navixy_id);

            $num_rows       = $this->db->count_all_results('vehicles');        
            if($num_rows){
                $data->modified = date('Y-m-d h:m:s');
                $this->db->update('vehicles', $data, "navixy_id={$data->navixy_id}");
            }else{
                $data->created  = date('Y-m-d h:m:s');
                $data->modified = date('Y-m-d h:m:s');
                $this->db->insert('vehicles', $data);
            }
        }
    }

    public function get_vehicles_address($userid=null,$vehicleId = null){
		$this->db->select('CONCAT( drivers.first_name ," + " ,team.first_name) AS teamDriverName , ABS( TIMESTAMPDIFF( MINUTE , Now( ) , vehicles.`modified` ) ) AS mintues_ago,  telemetry, CONCAT(DATE_FORMAT( CONVERT_TZ( vehicles.`modified`, "+00:00", "-05:00" ) , "%d-%b-%Y %r" )," ","EST") AS timestamp, vehicles.state,vehicles.tracker_id, vehicles.id, vehicles.city, vehicles.vehicle_address, vehicle_type, label, concat(drivers.first_name," ",drivers.last_name) as `driverName` ,users.username,destination_address,vehicles.latitude, vehicles.longitude,vehicles.user_id, drivers.id as driver_id');
        $this->db->join('vehicles','drivers.id = vehicles.driver_id');
        $this->db->join('users','drivers.user_id = users.id');
        $this->db->join('drivers as team','vehicles.team_driver_id = team.id','left');
        
        if ( !in_array($this->session->userdata('role'),$this->config->item('with_admin_role'))) {
			if(!empty($userid))
            	$this->db->where('drivers.user_id',$userid);
        }
                
        if($vehicleId){
            if(is_array($vehicleId))
                $this->db->where_in('vehicles.id',$vehicleId);
            else
                $this->db->where('vehicles.id',$vehicleId);
        }

        $this->db->where('drivers.status',1);
		$this->db->order_by('driverName',"asc");
        $data = $this->db->get('drivers');
        //echo $this->db->last_query();die;
        if ( $data->num_rows() > 0 ) 
			return $data->result_array();
		else
			return array();
    }


    public function get_vehicles_fuel_consumption( $userid = null , $vehicle_id = null){

        $this->db->select('fuel_consumption,destination_address,vehicle_address,city,state,cargo_capacity,cargo_bay_l');
       
        if ( $vehicle_id != '' && $vehicle_id != 0 && $vehicle_id != null ) {
			$this->db->where('vehicles.id',$vehicle_id);
		}
        
        //~ $this->db->where('vehicles.vehicle_status',1);
        $data = $this->db->get('vehicles');
      	if( $data->num_rows() > 0 ) {
			return $data->result_array();
		} else {
			return false;
		}
	} 
    
    
    public function get_vehicle_address($vehicleID,$inclued=true,$driverType = "driver"){
        $addFilter = "";
        if($driverType == "team"){
            $addFilter .= " CONCAT( drivers.first_name ,' + ' ,team.first_name) AS driverName , ";
        }else{
            $addFilter .= "concat(drivers.first_name,' ',drivers.last_name) as driverName, ";
        }
       
        $this->db->select($addFilter.'ABS( TIMESTAMPDIFF(MINUTE , Now( ) , vehicles.`modified` ) ) AS mintues_ago, telemetry, CONCAT(DATE_FORMAT( CONVERT_TZ( vehicles.`modified`, "+00:00", "-05:00" ) , "%d-%b-%Y %r" )," ","EST") AS timestamp , vehicles.tracker_id, u.id as dispId, drivers.id as driver_id, u.username, drivers.profile_image, vehicles.state, drivers.id, vehicles.id as vid, vehicles.city, vehicle_address, vehicle_type, vehicles.label,  destination_address, vehicles.latitude, vehicles.longitude');
        $this->db->join('drivers','drivers.id = vehicles.driver_id');
        $this->db->join('users as u','drivers.user_id = u.id');
        $this->db->join('drivers as team','vehicles.team_driver_id = team.id','left');

        if($driverType == "team"){
            $this->db->where('vehicles.driver_type =','team');
        }
        //~ if($inclued)
            //~ $this->db->where('vehicles.state !=','');

        $this->db->where('vehicles.id',$vehicleID);
        $result = $this->db->get('vehicles');
        if ( $result->num_rows() > 0 )
			return $result->row_array();
		else
			return array();
    }

    public function get_vehicles_drop($userid=null) {

        $this->db->select('vehicles.id,label,state,city')
        ->from('vehicles');
        
        if(!empty($userid)){
            $this->db->where('vehicles.user_id',$userid);
        }
        $vehicles = $this->db->get()->result_array();

        //echo $this->db->last_query();
        $options = '<option value="all">All</option>';
        foreach ($vehicles as $key => $vehicle) {
            $options .= "<option value={$vehicle['state']}>{$vehicle['label']}</option>";
        }
        return $options;
    }

    public function get_vehicles_state($userId) {
        $this->db->select('state')
        ->from('vehicles');
        if($userId){
            $this->db->where('user_id',$userId);
        }
        $data =  $this->db->group_by('state')
        ->get()->result_array();
        $states = implode(',',array_column($data,'state'));
        return $states;
    }




    
    public function get_states_areas( $country ) {
        $this->db->select('country, regions, areas, group_concat( DISTINCT code ) AS scode');
        $where_array = array('country' => $country, 'areas !=' => '' , 'regions !=' => '' );
        $this->db->where($where_array);
        $this->db->group_by('areas');
        $result = $this->db->get('states');
        //echo $this->db->last_query();die;
        if( $result->num_rows() > 0 ){
            return $result->result_array();
        } else {
            return false;
        }
    } 

    public function get_regions_areas( $country ) {
		$this->db->select('country, regions, group_concat( DISTINCT areas ) AS areas, group_concat( DISTINCT code ) AS scode');
		$where_array = array('country' => $country, 'areas !=' => '' , 'regions !=' => '' );
        $this->db->where($where_array);
        $this->db->group_by('regions');
        $result = $this->db->get('states');
        //echo $this->db->last_query();die;
        if( $result->num_rows() > 0 ){
            return $result->result_array();
        } else {
			return false;
		}
	}
	
	public function get_vehicles_detail( $id = null ){

        $this->db->select('state,vehicles.id,city,vehicle_address,vehicle_type,equipment_types.abbrevation,label,concat(first_name," ",last_name) as driverName,destination_address')
        ->from('vehicles')->join('drivers','drivers.id = vehicles.driver_id');
        
        $this->db->join('equipment_types','equipment_types.name = vehicles.vehicle_type','left');

        $data =  $this->db->where('vehicles.id', $id)->get()->row_array();
        //echo $this->db->last_query();
        return $data;
    }

    public function get_current_load( $id , $userId=false){
        $filters = array();
        $filters["JobStatus"] = "inprogress";
        if($userId){ $filters["user_id"] =  $userId ; }
        if($id){ $filters["vehicle_id"] = $id; }

        $data = $this->db->select('*')->from('loads')->where( $filters )->get()->row_array();
        //echo $this->db->last_query();
        return $data;
    }

    public function updateTruckInfo($truckInfo){
        $infoToUpdate = array();
        $infoToUpdate["latitude"] = $truckInfo['event']['GPS']['latitude'];
        $infoToUpdate["longitude"] = $truckInfo['event']['GPS']['longitude'];

        $infoToUpdate["eventType"] = $truckInfo['event']['eventType'];

        $infoToUpdate["vehicle_address"] = $truckInfo['event']['address']['street'].', '.$truckInfo['event']['address']['crossStreet'];
        $infoToUpdate["city"] = $truckInfo['event']['address']['city'];
        $infoToUpdate["state"] = $truckInfo['event']['address']['state'];
        
        $infoToUpdate["telemetry"] = serialize($truckInfo['event']['telemetry']);
        $infoToUpdate["lastMessageSeqID"] = $truckInfo['messageSeqID'];
        //$this->db->where( array('tracker_id'=>$truckInfo['deviceID'],'vehicleID'=>$vehicleID));
        $this->db->where( array('tracker_id'=>$truckInfo['deviceID']));
        $this->db->update('vehicles', $infoToUpdate);
        //echo $this->db->last_query();die;
    }


    public function updateCurrentWeather($weather,$vehicleID){
        $this->db->where(array('id'=> 1,'vehicleID'=>$vehicleID));
        $this->db->update('weather', $weather);
    }

    public function updateForecastWeather($weather,$key){
        $this->db->where( 'id', $key);
        $this->db->update('weather', $weather);
    }

    public function getWeatherInfo( $vehicleId = null ) {
        $this->db->select('*');
        $data = $this->db->get('weather')->result_array();
        return $data;
    }
	
	/**
	 * Fetching vehicle Info for job ticket
	 */
	  
	public function getVehicleInfo( $vehicleId = null ) {
        $this->db->select('vehicles.id,label,vehicle_type,cargo_capacity,cargo_bay_l,cargo_bay_w,fuel_consumption,vehicles.vehicle_image,drivers.profile_image,trailers.unit_id');
        $this->db->join('drivers', 'drivers.id = vehicles.driver_id');
        $this->db->join('trailers', 'trailers.truck_id = vehicles.id','LEFT');
        $this->db->where('vehicles.id',$vehicleId);
        $result = $this->db->get('vehicles');
        if ( $result->num_rows() > 0 ) {
            return $result->row_array();
        } else {
            return array();
        }
    }

	/**
	 * Fetching driver names and vehicle number for teams
	 */
	  
    public function getTeamVehicleInfo( $loadId = null ) {
        $this->db->select('CONCAT( drivers.first_name, " + ", team.first_name," - ",vehicles.label) AS driverName, vehicles.id,label,vehicle_type,cargo_capacity,cargo_bay_l,cargo_bay_w,drivers.first_name,drivers.last_name,fuel_consumption,vehicles.driver_id,vehicles.vehicle_image,drivers.profile_image,trailers.unit_id');
        $this->db->join('drivers', 'drivers.id = loads.driver_id');
        $this->db->join('drivers as team','loads.second_driver_id = team.id','left');
        $this->db->join('vehicles', 'vehicles.id = loads.vehicle_id','Left');
        $this->db->join('trailers', 'trailers.truck_id = vehicles.id','LEFT');
        $this->db->where(array('loads.id' => $loadId));
        $result = $this->db->get('loads');
       
        if ( $result->num_rows() > 0 ) {
            return $result->row_array();
        } else {
            return false;
        }
    }
	
	/**
	 *  Assigning Truck to driver on load detail
	 */
	 
	public function assignTruckDriver( $driveId = null ,$driverType = "driver") {
        $addFilter = "";
        if($driverType == "team"){

            $addFilter .= " CONCAT( drivers.first_name ,' + ' ,team.first_name, ' - ',vehicles.label) AS driverName , ";
            $addFilter .= " CONCAT( drivers.first_name ,' + ' ,team.first_name) AS assignedTeamName , ";
        }
		$this->db->select($addFilter.'  concat( drivers.first_name, " ", `drivers`.`last_name`, " - ",vehicles.label ) as dName,users.username, concat( drivers.first_name, " ", `drivers`.`last_name`) as assignedDrivername, concat( "Truck - ", vehicles.label) as assignedVehicleName, ,drivers.user_id as dispatcher_id,drivers.id,label,vehicle_type,cargo_capacity,cargo_bay_l,cargo_bay_w,drivers.first_name,drivers.last_name,fuel_consumption,driver_id,team_driver_id,vehicles.vehicle_image,drivers.profile_image,vehicles.id as assignedVehicleId,trailers.unit_id,trailers.id as trailerId');
		$this->db->join('vehicles', 'vehicles.driver_id = drivers.id','LEFT');
		$this->db->join('trailers', 'trailers.truck_id = vehicles.id','LEFT');
		$this->db->join('users', 'users.id = drivers.user_id','LEFT');
        $this->db->join('drivers as team','vehicles.team_driver_id = team.id','left');

        if($driverType == "team"){
            $this->db->where('vehicles.driver_type =','team');
        }

		$this->db->where('drivers.id',$driveId);
		$result = $this->db->get('drivers');
		//~ echo $this->db->last_query(); die;
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return false;
		}
	}
	
	
	/**
	 *  fetching Drivers list of particular user for trucks edit
	 */ 
	
	public function getRelatedDrivers( $userId = null ) {
		$this->db->select('drivers.id,concat(drivers.first_name," ",drivers.last_name) as driverName, users.username')->order_by("driverName","asc");
        $this->db->join('users',"drivers.user_id = users.id");  
        //role#4 is for role Disp. Coordinator
        if ( in_array($this->session->userdata('role'),$this->config->item('with_admin_role')) || $this->session->userdata('role') == 4) {
			$this->db->where('drivers.status',1);
		} else {
			$this->db->where(array('user_id'=>$userId,'drivers.status'=>1));
		}
		$result = $this->db->get('drivers');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();
		} else {
			return array();
		}
	}
	
	/**
	 * checking if same driver already assinged to another truck
	 */ 
	 
	public function checkChangeDriver( $driverId = null, $vehicleId = null, $userId = null ) {
		$finalArray = array();
		$this->db->select('id,driver_id,label');
		if ( $this->session->userdata('role') == 3 ) {
			$condition = array('driver_id' => $driverId, 'id !=' => $vehicleId);
		} else {
			$condition = array('user_id' => $userId, 'driver_id' => $driverId, 'id !=' => $vehicleId);
		}
		$this->db->where($condition);
		$result = $this->db->get('vehicles');
		if ( $result->num_rows() > 0 ) {
			$finalArray = $result->row_array();
			return $finalArray;
			//return false;
		} else {
			return $finalArray;
		}
	}
	
	/**
	 * Save driver and vehicle ids to separate table
	 */
	public function saveVehicleDriver( $driverId = null, $vehicleId = null, $userId = null, $status = '') {
		
		if ( $status == 'save' ) {
			$data = array(
				'vehicle_id' =>  $vehicleId,
				'driver_id' => $driverId,
				'user_id' => $userId,
				'status' => 1,
				'created' => date('Y-m-d H:i:s')
			);
				
			$this->db->insert('vehicle_drivers',$data);
		} else {
			$this->db->select('id,status');
			$condition = array('vehicle_id' => $vehicleId, 'driver_id' => $driverId, 'user_id' => $userId);
			$this->db->where($condition);
			$result = $this->db->get('vehicle_drivers');
			
			if ( $result->num_rows() > 0 ) {
				$finalRes = $result->row_array(); 
				//if ( $finalRes['status'] == 
				$data = array(
					'vehicle_id' =>  $vehicleId,
					'driver_id' => $driverId,
					'user_id' => $userId,
				);
				$this->db->where('id',$finalRes['id']);
				$this->db->update('vehicle_drivers',$data);
			} else {
				$data = array(
					'vehicle_id' =>  $vehicleId,
					'driver_id' => $driverId,
					'user_id' => $userId,
					'status' => 1,
					'created' => date('Y-m-d H:i:s')
				);
				
				$this->db->insert('vehicle_drivers',$data);
			}
		}
		
	}
	
	/**
	 * Fetching trailer types from database
	 */
	
	public function fetchTrailerTypes() {
		$this->db->select('abbrevation,name');
		return $this->db->get('equipment_types')->result_array();
    } 
    
    /**
     * getting infomation of load after assinging load to another driver
     */
      
    public function get_vehicles_aferAssign($userid = null, $vehicle_id = null){

        //~ $this->db->select('state,vehicles.id,city,label,concat(first_name," ",last_name) as driverName,destination_address')
        //~ ->from('vehicles')->join('drivers','drivers.id = vehicles.driver_id');
        
		$this->db->select('drivers.id as driver_id, concat(drivers.first_name," ",drivers.last_name," - ",vehicles.label) as driverName,vehicles.label,users.username,vehicles.id,vehicles.city,vehicles.vehicle_address, vehicles.state, destination_address');
        $this->db->join('vehicles','vehicles.driver_id = drivers.id');
        $this->db->join('users','drivers.user_id = users.id');
        $this->db->where('drivers.status',1);
       
        if ( $this->session->userdata('role') !=  3 && $this->session->userdata('role') != 1 ) {
			//~ if(!empty($userid))
				//~ $this->db->where('drivers.user_id',$userid);
		}
       
		$this->db->where('vehicles.id',$vehicle_id);
        $result = $this->db->get('drivers');
        if ( $result->num_rows() > 0 ) {
            return $result->row_array();
        } else {
            return array();
        }       
    }
    
    /*
     * CHange status
     */
      
    public function changeDriverStatus($vehicle_id = null, $status = null) {
		if ( $status == 0 || $status == '' || $status == null ) {
			$newStatus = 1;
		} else {
			$newStatus = 0;
		}
		
		$this->db->where('id',$vehicle_id);
		$data = array(
			'vehicle_status' => $newStatus,
		);
		$result = $this->db->update('vehicles',$data);
		return true;
	}
	
	/**
	 * Fetching trucks list for trailers dropdown
	 */
	
	//~ public function fetchTruckListForTrailers() {
		//~ $this->db->select('vehicles.id,concat("Truck-",vehicles.label) as vehicleName');
		//~ $result = $this->db->get('vehicles');
		//~ if ( $result->num_rows() > 0 ) {
			//~ return $result->result_array();		
		//~ } else {
			//~ return array();
		//~ }
	//~ }
	public function fetchTruckListForTrailers() {
		$this->db->select('vehicles.id,concat("Truck-",vehicles.label) as vehicleName');
		$result = $this->db->get('vehicles');
		if ( $result->num_rows() > 0 ) {
			return $result->result_array();		
		} else {
			return array();
		}
	}
	
	/**
	 * Check if truck number exist
	 */
	 
	public function checkTruckNumberExist( $truckNo = null, $id = null ) {
		$this->db->select('id');
		if ( $id != null && $id != '' )
			$this->db->where(array('label' => $truckNo,'id !=' => $id));
		else
			$this->db->where('label',$truckNo);
			
		$result = $this->db->get('vehicles');
		if ( $result->num_rows() > 0 ) {
			return $result->row_array();
		} else {
			return array();
		}
	} 
	
	/**
	 * Updating Vehicle Destination address
	 */
	
	public function updateTruckDestination( $vehicleId = null, $pickDate = '', $planDeadmiles = null  ) {
		//~ $this->db->select('DestinationCity,DestinationState,DestinationCountry,DeliveryDate');
		$this->db->select('DestinationCity,DestinationState,DestinationCountry,PickupDate,DeliveryDate,DATE_FORMAT(STR_TO_DATE(pickDate, \'%m/%d/%y\'), \'%Y-%m-%d\') as pickday');
		if ( isset($pickDate) && $pickDate != '' ) {
			if ( $planDeadmiles == 1 ) 
				$condition = array( 'vehicle_id' => $vehicleId, 'delete_status' => 0, 'JobStatus !=' => 'cancel');
			else
				$condition = array( 'vehicle_id' => $vehicleId, 'PickupDate <' => $pickDate ,'delete_status' => 0, 'JobStatus !=' => 'cancel');
		} else {
			$condition = array( 'vehicle_id' => $vehicleId , 'delete_status' => 0, 'JobStatus !=' => 'cancel');
		}
		$this->db->where($condition);
		$this->db->order_by('PickupDate','DESC');
		$result = $this->db->get('loads');
	//~ echo $this->db->last_query(); die;
		if ( $result->num_rows() > 0 ) {
			$loadDetail = $result->row_array();
			return $loadDetail;
		} else {
			return false;
		}		
	}
	
	/**
	 * Fetching last load details for vehicle
	 */
	
	public function getLastLoadRecord( $vehicle_id = null , $driverId = null ) {
		$this->db->select('DestinationCity,DestinationState,DestinationCountry,DeliveryDate,DATE_FORMAT(STR_TO_DATE(pickDate, \'%m/%d/%y\'), \'%Y-%m-%d\') as pickday');
		if ( $driverId != null && $driverId != 0 )  
			$this->db->where(array('vehicle_id' => $vehicle_id, 'driver_id' => $driverId, 'delete_status' => 0, 'JobStatus !=' => 'cancel'));
		else
			$this->db->where(array('vehicle_id' => $vehicle_id, 'delete_status' => 0, 'JobStatus !=' => 'cancel'));
		$this->db->order_by('PickupDate','DESC');
		$results = $this->db->get('loads');
		//~ echo $this->db->last_query(); die;
		if ( $results->num_rows() > 0 ) {
			return $results->row_array();
		} else {
			return array();
		}
	}  
	
	/**
	 * Fetching list of vehicles for job ticket dropdown
	 */
	 
	public function getVehiclesList() {
		$this->db->select('vehicles.id,concat("Truck - ",vehicles.label) as vehicleLabel');
		//~ $this->db->where('vehicles.vehicle_status',1);
		$result = $this->db->get('vehicles');
		if ($result->num_rows() > 0 ) 
			return $result->result_array();
		else
			return array();
	} 

    /*
    * method  : Get
    * params  : vehicleId
    * retrun  : driver Array
    * comment : used for fetching single row while updating status
    */

    public function fetchSingleUpdatedRecord( $vehicleId = null ) {
        $this->db->DISTINCT();
        $this->db->select('CONCAT(concat( drivers.first_name, " ", `drivers`.`last_name` )," + ", concat(team.first_name," ",team.last_name)) AS teamDriverName  , vehicles.id,vehicles.label, CONCAT(users.first_name," ",users.last_name) AS dispatcher, vehicles.vin, vehicles.model, vehicles.vehicle_type,vehicles.vehicle_status,vehicles.cargo_bay_l, vehicles.cargo_capacity,concat(drivers.first_name," ",drivers.last_name) as driverName,GROUP_CONCAT(equipment_types.name) as vehicleType',FALSE);
        $this->db->join('drivers','drivers.id=vehicles.driver_id','LEFT');
        $this->db->join('equipment_types',("FIND_IN_SET(equipment_types.abbrevation , vehicles.vehicle_type) > 0"), 'LEFT');
        $this->db->join('users',("drivers.user_id = users.id"), 'LEFT');
        $this->db->join('drivers as team','vehicles.team_driver_id = team.id','left');
        $this->db->where('vehicles.id',$vehicleId);   
        $this->db->Group_by('vehicles.id');
       
        $result = $this->db->get('vehicles'); 
        if($result->num_rows()>0){
            return $result->row_array();
        } else {
            return false;
        }
    }
}
