app.controller('trucksController', function(dataFactory,$scope, PubNub, $http ,$rootScope , $location , $cookies,getTrucksListing){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');

	$scope.sortReverse = false;  // set the default sort order 
	$scope.sortType='label';
	$rootScope.showHeader = true;
	$scope.data = [];
	$scope.pageNumber = 1;
	$scope.libraryTemp = {};
	$scope.totalItemsTemp = {};

	$scope.totalItems = 0;
	$scope.pageChanged = function(newPage) {
		getResultsPage(newPage);
	};

	$rootScope.uniqueFieldsValue = true;	

  	$scope.Message = $rootScope.truckEditMessage;
	if($scope.Message!== undefined){
		$scope.alertmsg = true;
	}else{
		$scope.alertmsg = false; 
	}

	$scope.errorMessage = $rootScope.truckEditErrorMessage;
	if($scope.errorMessage!== undefined){
		$scope.alertErrorMsg = true;
	}else{
		$scope.alertErrorMsg = false; 
	}

	$scope.data = getTrucksListing.rows;
	$scope.totalItems = getTrucksListing.total_records;
	
	if($scope.Message == $rootScope.truckEditMessage){
		$rootScope.truckEditMessage = undefined;
	}

	$scope.team = false;
	$scope.switchToTeam = function(team){
		console.log(team);
	}

	$scope.sortPage = function(){
		getResultsPage(1);
	}

  	$scope.searchTruckDB = function(){
	    if($scope.searchText.length >= 3){
	        if($.isEmptyObject($scope.libraryTemp)){
	            $scope.libraryTemp = $scope.data;
	            $scope.totalItemsTemp = $scope.totalItems;
	            $scope.data = {};
	        }
	        getResultsPage(1);
	      } else {
			if(! $.isEmptyObject($scope.libraryTemp)){
		        $scope.data = $scope.libraryTemp ;
	            $scope.totalItems = $scope.totalItemsTemp;
	            $scope.libraryTemp = {};
	        }
	    }
 	 }

	$scope.hidedeletemessage = function(){
		$scope.alertdeletemsg = false;
		$scope.alertdeleteErrormsg = false;
		$scope.alertmsg = false;
		$scope.alertErrorMsg = false;
	}


	$rootScope.dataTableOpts(10,8);			 //set datatable options   -r288
	
	$scope.removeTruck = function(truckid,index){
		angular.element("#confirm-delete").modal('show');
		angular.element("#confirm-delete").data("truckid",truckid);
		angular.element("#confirm-delete").data("index",index);
	}

	$scope.confirmDelete = function(confirm){
		if(confirm == 'yes'){
			var truckid = angular.element("#confirm-delete").data("truckid");
			var index  = angular.element("#confirm-delete").data("index");
			if ( truckid != '' && truckid != undefined ) {
				dataFactory.httpRequest(URL + '/vehicles/delete/'+truckid).then(function(data) {
					PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
					if ( data.success == true ) {
						$scope.truckdeleteMessage = $rootScope.languageArray.truckDeleteSuccMsg;
						$scope.alertdeletemsg = true;
						$scope.data.splice(index,1);
					} else {
						$scope.truckdeleteMessage = $rootScope.languageArray.truckDeleteErrMsg;
						$scope.alertdeleteErrormsg = true;
					}
		      	});
			}
		} else {
			angular.element("#confirm-delete").removeData("truckid");
			angular.element("#confirm-delete").removeData("index");
		}
		angular.element("#confirm-delete").modal('hide');
	}
	
	/** Change Driver Status **/
  
	$scope.changeVehicleStatus = function( vehicleId, status, index) {
		$scope.vehicleId = vehicleId;
		$scope.Status = status; 
		$scope.StatusIndex = index; 
		angular.element("#vehicle-list-status").modal('show');
	}
	
	$scope.confirmVehicleStatus = function( confirm) {
		if(confirm == 'yes'){
			dataFactory.httpRequest(URL+'/vehicles/changeStatus/'+$scope.vehicleId+'/'+$scope.Status).then(function(data) {
				PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				angular.copy(data.records.rows, $scope.data[$scope.StatusIndex]);
				if ( data.status == true ) {
					$scope.truckdeleteMessage = $rootScope.languageArray.truckStatusSuccMsg;
					$scope.alertdeletemsg = true;
				}else{
					$scope.truckdeleteMessage = $rootScope.languageArray.truckStatusErrMsg;
					$scope.alertdeleteErrormsg = true;
				}
			});
		} 
		angular.element("#vehicle-list-status").modal('hide');
	}  		
});

app.controller('editTruckController', function(dataFactory,getTruckData,$sce,$scope, PubNub, $http ,$rootScope , $location , $cookies, $stateParams){
	if($rootScope.loggedInUser == false)
	$location.path('logout');	
	
	$scope.myFile = {};
	$scope.truckData = {};
	$scope.truckData = getTruckData.trucks;	
	
	var editedDriverId		 = $scope.truckData.id;
	$scope.truckDocuments	 = getTruckData.truckDocuments;
	$scope.states_data 		 = getTruckData.states_data;
	$scope.driversListTeam 	 = [];
	angular.copy(getTruckData.driversList,$scope.driversListTeam);
	$scope.driversList 		= getTruckData.driversList;
	$scope.unassignedDriverData = {'id':'0','driverName':'Unassign','username':''}; 
	$scope.driversList.push($scope.unassignedDriverData);
	$scope.showimage = true;

	$rootScope.showErrorMessage = false;
	$scope.dtype = 'single';
	$scope.truckData.driverType ='single';
	
	if($scope.truckData.team_driver_id != 0 && $scope.truckData.team_driver_id != "" && $scope.truckData.team_driver_id !== undefined){
		$scope.truckData.driverType = "team";
		$scope.dtype = 'team';	
		//On load page
		if($scope.truckData.driverName===null||$scope.truckData.driverName===undefined||$scope.truckData.driverName===''){
		  }
		$scope.tempTeamDriverOne = $scope.truckData.driverName === null ? "Select Driver" : $scope.truckData.driverName;
		$scope.temp_team_driver_one = $scope.truckData.driver_id;
		$scope.tempTeamDriverTwo = $scope.truckData.teamDriverTwo;
		$scope.temp_team_driver_two = $scope.truckData.team_driver_id;

		$scope.truckData.teamDriverOne =  $scope.truckData.driverName === null ? "Select Driver" : $scope.truckData.driverName;
		$scope.truckData.team_driver_one = $scope.truckData.driver_id;
		$scope.truckData.teamDriverTwo = $scope.truckData.teamDriverTwo;
		$scope.truckData.team_driver_two = $scope.truckData.team_driver_id;
	}else{
		$scope.tempTeamDriverOne = "Select Driver";
		$scope.temp_team_driver_one = "0";
		$scope.tempTeamDriverTwo = "Select Driver";
		$scope.temp_team_driver_two = "0";

		$scope.truckData.teamDriverOne = "Select Driver";
		$scope.truckData.team_driver_one = "0";
		$scope.truckData.teamDriverTwo = "Select Driver";
		$scope.truckData.team_driver_two = "0";
	}

	$scope.trustAsHtml = function(value) {
		return $sce.trustAsHtml(value);
    };
    
    $scope.truckData.label = parseInt($scope.truckData.label);
    if($scope.truckData.driverName===null||$scope.truckData.driverName===undefined||$scope.truckData.driverName===''){
		$scope.truckData.driverName = 'Unassign';
		$scope.truckData.driver_id = '0';
	}

    $scope.tempDriveId = $scope.truckData.driver_id;
    $scope.tempDriverName = $scope.truckData.driverName;
    
	$scope.fuelType = [{ 'val' : 'Diesel','key' : 'diesel'},{ 'val' : 'Petrol','key' : 'petrol'},{ 'val' : 'Gas','key' : 'gas'}];
    $scope.onSelectVehicleTypeCallback = function (item, model){
		$scope.truckData.vehicle_type = item.key;
	};
	
    $scope.onSelectFuelTypeCallback = function (item, model){
		$scope.truckData.fuel_type = item.key;
	};
	
	dataFactory.httpRequest(URL + '/truckstop/fetchTrailerType').then(function(data) {
		$scope.trailerTypes = data.trailerTypes;
	});
	
	$scope.onSelectStateCallback = function (item, model){
		$scope.truckData.state = item.code;
	};
	$scope.groupFind = function(item){
        if(item.username !== "")
            return 'Dispatcher: '+item.username;
        else
            return item.username;
    }
    
	$scope.onSelectDriverNameCallback = function (item, model){
		if ( item.id == '' || item.id == undefined ){
			$scope.driversList="Unassigned";
		}	
		var driverId =item.id;
		if ( driverId != '' && driverId != undefined ) {
			$scope.truckData.driver_id = item.id;
			if(driverId === '0'){
				$scope.truckData.driverName = 'Unassign';
			}
			$scope.truckData.driverName = item.driverName;
			if(driverId !== '0'){
				dataFactory.httpRequest(URL+'/vehicles/changeDriver/'+driverId+'/'+$scope.truckData.id).then(function(data) {
					if ( data.result == false ) {
						$scope.truckName = data.truckName;
						$('#changeDriverOnTruck').modal('show');
					}
				});
			}
		}
	};


	$scope.changeDriverType = function(type){
		$scope.dtype = type;
	}
	
	$scope.teamDispatcher = "";
	$scope.onSelectTeamDriverCallback = function (item, model,driverIndex){
		$scope.dindex = driverIndex;
		var driverId =item.id;
		if ( driverId != '' && driverId != undefined ) {
			$scope.truckData.driver_id = item.id;
			
			if(driverIndex == "team_driver1"){
				$scope.truckData.teamDriverOne = item.driverName;
				$scope.teamDispatcher = item.username;
			}else{
				$scope.truckData.teamDriverTwo = item.driverName;
			}
			$scope.truckData.driverName = item.driverName;

			if(driverId !== '0'){
				dataFactory.httpRequest(URL+'/vehicles/changeDriver/'+driverId+'/'+$scope.truckData.id).then(function(data) {
					if ( data.result == false ) {
						$scope.truckName = data.truckName;
						$('#changeDriverOnTruck').modal('show');
					}
				});
			}
		}
	};
	
	$scope.changeDriverOrNot = function(status,type, dindex) {
		if ( status == 'yes') {
			if(type == "single"){
				$scope.saveTruck('popup');	
			}
		} else {
			if(type == "team"){
				if(dindex == "team_driver1"){
					$scope.truckData.teamDriverOne = $scope.tempTeamDriverOne;
					$scope.truckData.team_driver_one = $scope.temp_team_driver_one;
				}else{
					$scope.truckData.teamDriverTwo = $scope.tempTeamDriverTwo;
					$scope.truckData.team_driver_two = $scope.temp_team_driver_two;
				}
			}else{
				$scope.truckData.driver_id = $scope.tempDriveId;
				$scope.truckData.driverName = $scope.tempDriverName;	
			}
		}
		$('#changeDriverOnTruck').modal('hide');
	}
	
	if($scope.truckData.vehicle_image==''||$scope.truckData.vehicle_image==null)
	{
		$scope.showimage = false;
	}
	
	/*
	* Method: get
	* Params: docId,name
	* Return: success or error
	* Comment: Used for deleting the attachment for truck
	*/ 
	
	$scope.deleteDocument = function( docId, documentName, index) {
		$scope.docId = docId;
		$scope.documentName = documentName; 
		$scope.documentIndex = index; 
		angular.element("#common-document-status").modal('show');
	}
	
	$rootScope.confirmCommonDocumentStatus = function(confirm){
		if(confirm=='yes'){
		dataFactory.httpRequest(URL + '/vehicles/deleteContractDocs/'+$scope.docId+"/"+$scope.documentName).then(function(data) {
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			if(data.success == true) {
				$scope.truckDocuments.splice($scope.documentIndex,1);
			}
	    });
		}	
		angular.element("#common-document-status").modal('hide');
	}
	
	$scope.dropzoneConfigTruckEdit = {
		parallelUploads: 5,
		maxFileSize: 10,
		url: URL+ '/vehicles/uploadContractDocuments',
		addRemoveLinks: true, 
		//~ autoProcessQueue: false,
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .bmp, .svg',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("truckId", editedDriverId);
		},
		success:function(file,response){
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			file.previewElement.classList.add("dz-success");
			if(!response.error){ // succeeded
				this.removeFile(file);
				response = angular.fromJson(response);
				if ( response.error_exceed != undefined && response.error_exceed == 1 ) {
					$rootScope.ExceedMessage = 'Error! : The uploaded document exceeds the maximum allowed limit of 10MB.';
				}else if(response.docList != undefined && response.docList.length > 0){
					$scope.truckDocuments = response.docList;
				}else{
					$scope.truckDocuments = [];
				}
				$scope.$apply();
			}
		},
	};
	
	$scope.saveTruck = function(from){
		if ( $rootScope.uniqueFieldsValue == false ) {
			return false;
		}

		var file = $scope.myFile;
        $scope.name = $scope.truckData.vehicle_image;
        var fd = new FormData();
            fd.append('vehicle_image', file);
			fd.append('data1',$scope.name);
              $http.post(URL+'/vehicles/image/',fd,{
                  transformRequest: angular.identity,
                  headers: {'Content-Type': undefined}
               }).then(function successCallback(response) {
				   if(response.data!=='')
				   {
					$scope.truckData.vehicle_image = response.data;
					}
			dataFactory.httpRequest(URL+'/vehicles/update/'+$scope.truckData.id,'POST',{},$scope.truckData).then(function(data) {
				PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				if ( data.success == true ) {
					$rootScope.truckEditMessage = $rootScope.languageArray.truckUpdatedSuccMsg;
					//~ $scope.dropzone.processQueue();
				} else {
					$rootScope.truckEditErrorMessage = $rootScope.languageArray.truckUpdatedErrMsg;
				}
				if(from != 'popup'){
					$location.path('trucks');
				}
				$('.fixed-header').removeClass("modal-open");
			});
		});
	}
	
	/*
	 * Check if same truck no exist
	 */
	 
	$scope.checkVehicleLabelExist = function( truckNo, truckId ) {
		if ( truckNo != '' && truckNo != undefined ) {
			dataFactory.httpRequest(URL+'/vehicles/checkTruckNumber/'+truckNo+'/'+truckId).then(function(data) {
				if ( data.success == false ) {
					$rootScope.showErrorMessage = true;
					$scope.errorMessage = 'Error! : This truck number already exist, please try another number.';
					$scope.truckData.label = '';
				} else {
					$rootScope.showErrorMessage = false;
					$scope.errorMessage = '';
				}
			});
		}
	}
});

 
 app.controller('addTruckController', function(dataFactory,getTruckData,$sce,$scope,PubNub, $http ,$rootScope , $location , $cookies, $stateParams){
	if($rootScope.loggedInUser == false)
		$location.path('logout');
		
	$scope.myFile = {};
	$scope.addTruckData = {};
	$scope.truckData = {};
	$scope.states_data = getTruckData.states_data;
	$scope.showimage = true;
	
	$scope.driversList = getTruckData.driversList;
	$scope.driversListTeam = [];
	angular.copy($scope.driversList,$scope.driversListTeam);

	$scope.unassignedDriverData = {'id':'0','driverName':'Not Assigned','username':''}; 
	$scope.driversList.push($scope.unassignedDriverData);
	
	//~ $scope.driversList.driver_id = '';
	$scope.addTruckData.driverName = '';
	$scope.addTruckData.driver_id = '';
	$scope.addTruckData.vehicle_type = '';

	$scope.dtype = 'single';
	$scope.addTruckData.driverType ='single';

    
    
	$scope.trustAsHtml = function(value) {
		return $sce.trustAsHtml(value);
    };
    
	$scope.fuelType = [{ 'val' : 'Diesel','key' : 'diesel'},{ 'val' : 'Petrol','key' : 'petrol'},{ 'val' : 'Gas','key' : 'gas'}];
    $scope.addTruckData.fuel_consumption = 600;					// set value of fuel consumption per 100 miles to 600
    $scope.addTruckData.permitted_speed = 75;					// set value of permitted speed to 75
    $scope.addTruckData.vehicle_type = [{ 'abbrevation' : 'F','name' : 'Flatbed'}];
    
    dataFactory.httpRequest(URL + '/truckstop/fetchTrailerType').then(function(data) {
		$scope.trailerTypes = data.trailerTypes;
	});
			
    $scope.onSelectVehicleTypeCallback = function (item, model){
		$scope.addTruckData.vehicle_type = item.key;
	};
    
    $scope.onSelectFuelTypeCallback = function (item, model){
		$scope.addTruckData.fuel_type = item.key;
	};
    
    $scope.onSelectStateCallback = function (item, model){
		$scope.addTruckData.state = item.code;
	};
	$scope.groupFind = function(item){
        if(item.username !== "")
            return 'Dispatcher: '+item.username;
        else
            return item.username;
    }
	
	$scope.changeDriverType = function(type){
		$scope.dtype = type;
	}

	$scope.onSelectDriverNameCallback = function (item, model){
		var driverId =item.id;
		if ( item.id == '' || item.id == undefined ){
			$scope.driversList="Unassigned";
		}
		if ( driverId != '' && driverId != undefined )  {
			$scope.addTruckData.driver_id = item.id;
			if(driverId === '0'){
				$scope.truckData.driverName = 'Not Assigned';
			}
			$scope.addTruckData.driverName = item.driverName;
			$scope.truckData.driverName = item.driverName;
			if(driverId !== '0'){
				dataFactory.httpRequest(URL+'/vehicles/changeDriver/'+driverId+'/'+$scope.addTruckData.id).then(function(data) {
					if ( data.result == false ) {
						$scope.truckName = data.truckName;
						$('#changeDriverOnTruck').modal('show');
					}
				});
			}
		}
	};


	$scope.teamDispatcher = "";
	//Select Driver as Team
	$scope.onSelectTeamDriverCallback = function (item, model,driverIndex){
		$scope.dindex = driverIndex;
		var driverId =item.id;
		if ( driverId != '' && driverId != undefined ) {
			$scope.addTruckData.driver_id = item.id;
			
			if(driverIndex == "team_driver1"){
				$scope.teamDispatcher = item.username;
				$scope.addTruckData.teamDriverOne = item.driverName;
			}else{
				$scope.addTruckData.teamDriverTwo = item.driverName;
			}
			$scope.addTruckData.driverName = item.driverName;
			$scope.truckData.driverName = item.driverName;

			if(driverId !== '0'){
				dataFactory.httpRequest(URL+'/vehicles/changeDriver/'+driverId+'/'+$scope.addTruckData.id).then(function(data) {
					if ( data.result == false ) {
						$scope.truckName = data.truckName;
						$('#changeDriverOnTruck').modal('show');
					}
				});
			}
		}
	};


	$scope.changeDriverOrNot = function(status) {
		if ( status == 'yes' ) {
			
		} else {
			$scope.addTruckData.driver_id = '';
			$scope.addTruckData.driverName = '';
		}
		$('#changeDriverOnTruck').modal('hide');
	}
		
	if($scope.addTruckData.vehicle_image==''||$scope.addTruckData.vehicle_image==null)
	{
		$scope.showimage = false;
	}
	
	$scope.dropzoneConfigTruckAdd = {
		parallelUploads: 5,
		maxFileSize: 3,
		url: URL+ '/vehicles/uploadContractDocuments',
		addRemoveLinks: true, 
		autoProcessQueue: false,
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .bmp, .svg',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("truckId", $scope.lastAddedTruck);
		},
		success:function(file,response){
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			file.previewElement.classList.add("dz-success");
			if(!response.error){ // succeeded
				this.removeFile(file);
				response = angular.fromJson(response);
				if ( response.loadIdNotExist != undefined && response.loadIdNotExist == 1 ) {
					
				} else if ( response.error_exceed != undefined && response.error_exceed == 1 ) {
					
				} else {
					
				}
				$scope.$apply();
			}
		},
	};
	
	$scope.addTruck = function(){
		if ( $rootScope.uniqueFieldsValue == false ) {
			return false;
		}

		var file = $scope.myFile;
		var fd = new FormData();
            fd.append('vehicle_image', file);
			
              $http.post(URL+'/vehicles/image/',fd,{
                  transformRequest: angular.identity,
                  headers: {'Content-Type': undefined}
               }).then(function successCallback(response) {
					if(response.data!=='') {
						$scope.addTruckData.vehicle_image = response.data;
					}
	
					dataFactory.httpRequest(URL+'/vehicles/addTruck/','POST',{},$scope.addTruckData).then(function(data) {
						PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
						if ( data.success == true ) {
							$rootScope.truckEditMessage = $rootScope.languageArray.truckSavedSuccMsg;
							$scope.lastAddedTruck = data.lastTruckId;
							$scope.dropzone.processQueue();
						} else {
							$rootScope.truckEditErrorMessage = $rootScope.languageArray.truckSavedErrMsg;
						}
					$location.path('trucks');
			});
		});
	}
	
	/*
	 * Check if same truck no exist
	 */
	 
	$scope.checkVehicleLabelExist = function( truckNo ) {
		if ( truckNo != '' && truckNo != undefined ) {
			dataFactory.httpRequest(URL+'/vehicles/checkTruckNumber/'+truckNo).then(function(data) {
				if ( data.success == false ) {
					$rootScope.showErrorMessage = true;
					$scope.errorMessage = 'Caution! : This truck number already exist, please try another number.';
					$scope.addTruckData.label = '';
				} else {
					$rootScope.showErrorMessage = false;
					$scope.errorMessage = '';
				}
			});
		}
	}
	
});
 

 

 app.directive('fileModel', ['$parse', function ($parse) {
	return {
	   restrict: 'A',
	   link: function(scope, element, attrs) {
		  var model = $parse(attrs.fileModel);
		  var modelSetter = model.assign;
		  
		  element.bind('change', function(){
			 scope.$apply(function(){
				modelSetter(scope, element[0].files[0]);
			 });
		  });
	   }
	};
 }]);
 
 app.service('fileUpload', ['$http', function ($http) {
	this.uploadFileToUrl = function(file, uploadUrl){
	   var fd = new FormData();
	   fd.append('file', file);
	
	   $http.post(uploadUrl, fd, {
		  transformRequest: angular.identity,
		  headers: {'Content-Type': undefined}
	   })
	
	   .success(function(){
	   })
	
	   .error(function(){
	   });
	}
 }]);
