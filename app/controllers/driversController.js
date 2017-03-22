app.controller('driversController', function(dataFactory,$scope, PubNub, $http ,$rootScope , $location , $cookies, $localStorage,getDriversListing,$state){
	
  $rootScope.showHeader = true;
  $scope.sortReverse  = false;  // set the default sort order 
  $scope.sortType='first_name';
  $scope.data = [];
  $scope.pageNumber = 1;
  $scope.libraryTemp = {};
  $scope.totalItemsTemp = {};

  $scope.totalItems = 0;
  $scope.pageChanged = function(newPage) {
	  getResultsPage(newPage);
  };

  	$scope.Message = $rootScope.driverEditMessage;
  	if($scope.Message!== undefined){
		$scope.alertmsg = true;
	}else{
		$scope.alertmsg = false; 
	}
	$scope.data = getDriversListing.rows;
	$scope.totalItems = getDriversListing.total_records;
	//$scope.pageNumber = pageNumber;

	if($scope.Message == $rootScope.driverEditMessage){
		$rootScope.driverEditMessage = undefined;
	}
	
	$scope.sortPage = function(){
		getResultsPage(1);
	}

	$scope.goForDashboard = function(vid){
		$rootScope.ofDriver = vid;
		$state.go('dashboard',{},{reload: true});
	}

	$scope.searchDriverDB = function(){
	      if($scope.searchText.length >= 3){
	          if($.isEmptyObject($scope.libraryTemp)){
	              $scope.libraryTemp = $scope.data;
	              $scope.totalItemsTemp = $scope.totalItems;
	              $scope.data = {};
	          }
	          getResultsPage(1);
	      }else{
			  if(! $.isEmptyObject($scope.libraryTemp)){
				  $scope.data = $scope.libraryTemp ;
	              $scope.totalItems = $scope.totalItemsTemp;
	              $scope.libraryTemp = {};
	          }
	      }
	}

	$rootScope.dataTableOpts(10,6);			 //set datatable options   -r288
	$scope.hidedeletemessage = function(){
		$scope.alertdeletemsg = false;
	}
	
  	$scope.removeDriver = function(driverid,index){
		angular.element("#confirm-delete").modal('show');
		angular.element("#confirm-delete").data("driverid",driverid);
		angular.element("#confirm-delete").data("index",index);
	}
  
  /** Change Driver Status **/
  
	$scope.changeDriverStatus = function( driverId, status, index) {
		$scope.driverID = driverId;
		$scope.Status = status; 
		$scope.driverIndex = index; 
		angular.element("#driver-list-status").modal('show');
	}
	
	$scope.confirmDriverStatus = function( confirm) {
		if(confirm == 'yes'){
			dataFactory.httpRequest(URL+'/drivers/changeStatus/'+$scope.driverID+'/'+$scope.Status).then(function(data) {
				PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				angular.copy(data.records.rows, $scope.data[$scope.driverIndex]);
				if ( data.status == true ) {
					$scope.driverdeleteMessage = $rootScope.languageArray.driverStatusSuccMsg;
					$scope.alertdeletemsg = true;
				}else{
					$scope.driverdeleteMessage = $rootScope.languageArray.driverStatusErrMsg;
					$scope.alertdeletemsg = true;
				}
			});
		} 
		angular.element("#driver-list-status").modal('hide');
	}


  $scope.confirmDelete = function(confirm){
		if(confirm == 'yes'){
			var driverid = angular.element("#confirm-delete").data("driverid");
			var index  = angular.element("#confirm-delete").data("index");
			if ( driverid != '' && driverid != undefined ) {
				dataFactory.httpRequest(URL + '/drivers/delete/'+driverid).then(function(data) {
					PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
					if ( data.success == true ) {
						$scope.driverdeleteMessage = $rootScope.languageArray.driverDeleteSuccMsg;
						$scope.alertdeletemsg = true;
						$scope.data.splice(index,1);
					} else {
						$scope.driverdeleteMessage = $rootScope.languageArray.driverDeleteErrMsg;
						$scope.alertdeletemsg = true;
					}
			    });
			}
		} else {
			angular.element("#confirm-delete").removeData("driverid");
			angular.element("#confirm-delete").removeData("index");
		}
		angular.element("#confirm-delete").modal('hide');
	} 
		
});

app.controller('editDriversController', function(dataFactory,getDriversData, $scope,$http ,$rootScope , $location , $cookies, $stateParams,getDispatcherList){
	$rootScope.showHeader = true;
	$scope.driversData = {};
	$scope.driversData = getDriversData.drivers;
	$scope.editedDriverId = $scope.driversData.id;
	if(getDriversData.driverDocuments != undefined && getDriversData.driverDocuments.length > 0){
		$scope.driverDocs = getDriversData.driverDocuments;	
	} else {
		$scope.driverDocs = [];
	}
	
	$scope.title = $rootScope.languageArray.editDriver ;

	$scope.dropzoneConfigDriverEdit = {
		parallelUploads: 5,
		maxFileSize: 3,
		url: URL+ '/drivers/uploadDocs',
		addRemoveLinks: true, 
		/*autoProcessQueue: false,*/
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .bmp, .svg',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("driverId", $scope.editedDriverId);
		},
		success:function(file,response){
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			file.previewElement.classList.add("dz-success");
			if(!response.error){ // succeeded
				this.removeFile(file);
				response = angular.fromJson(response);
				if ( response.error_exceed != undefined && response.error_exceed == 1 ) {
					$rootScope.ExceedMessage = 'Error! : The uploaded document exceeds the maximum allowed limit of 128MB.';
				}else if(response.docList != undefined && response.docList.length > 0){
					$scope.driverDocs = response.docList;
				}else{
					$scope.driverDocs = [];
				}
				$scope.$apply();
			}
		},
	};

	$scope.deleteDoc = function( docId, documentName, index) {
		$scope.docId = docId;
		$scope.documentName = documentName; 
		$scope.documentIndex = index; 
		angular.element("#common-document-status").modal('show');
	}
	
	$rootScope.confirmCommonDocumentStatus = function(confirm){
		if(confirm == 'yes'){
			dataFactory.httpRequest(URL + '/drivers/deleteContractDocs/'+$scope.docId+"/"+$scope.documentName).then(function(data) {
				PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				if(data.success == true) {
					$scope.driverDocs.splice($scope.documentIndex,1);
				}
		    });
		}
		angular.element("#common-document-status").modal('hide');
	}

	$scope.dispatcherList = getDispatcherList.list;    //Dispatcher List -r 288
	$scope.selectedDispatcher = getDispatcherList.selected;    //Selected Dispatcher -r 288
	$scope.dispatcher = {};
	if($scope.selectedDispatcher.id !== '' && $scope.selectedDispatcher.id !== undefined && $scope.selectedDispatcher.id !==0 && $scope.selectedDispatcher.id !== null){
		$scope.dispatcher.selected = {'id':$scope.selectedDispatcher.id,'username':$scope.selectedDispatcher.username};
	}
	$scope.onSelectDispatcherCallback = function (item, model){
		$scope.driversData.user_id = item.id;
	};
		
	$scope.saveDriver = function(){
		$scope.driversData.address=document.getElementById('pac-input').value;
		var file = $scope.myFile;
			var fd = new FormData();
            var data = JSON.stringify($scope.driversData);
            fd.append('profile_image', file);
			fd.append('posted_data',data);
			fd.append('id',$scope.driversData.id);
			$http.post(URL+'/drivers/update/',fd,{
			transformRequest: angular.identity,
			headers: {'Content-Type': undefined}
            }).then(function successCallback(response){
			if ( response.data.success == true ) {
				$rootScope.driverEditMessage = $rootScope.languageArray.driverUpdatedSuccMsg;
				//$scope.dropzone.processQueue();
				$location.path('drivers');
			} else {
				$rootScope.driverEditMessage = $rootScope.languageArray.driverUpdatedErrMsg;
				$location.path('drivers');
			}
		});
	}
});


app.controller('addDriversController', function(dataFactory,$scope,$http ,$rootScope , $location , $cookies, $stateParams,getDispatcherList){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');
		
	$rootScope.showHeader = true;
	
	$scope.addDriversData = {};
	$scope.title = $rootScope.languageArray.editDriver ;

	$scope.dropzoneConfigDriverAdd = {
		parallelUploads: 5,
		maxFileSize: 3,
		url: URL+ '/drivers/uploadDocs',
		addRemoveLinks: true, 
		autoProcessQueue: false,
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .bmp, .svg',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("driverId", $scope.lastAddedDriver);
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
	
	/************ Dispatcher Dropdown -r 288 ***************/
	$scope.dispatcherList = getDispatcherList.list;    //Dispatcher List -r 288
	$scope.selectedDispatcher = getDispatcherList.selected;    //Selected Dispatcher -r 288
	$scope.dispatcher = {};
	if($scope.selectedDispatcher.id !== '' && $scope.selectedDispatcher.id !== undefined ){
		$scope.dispatcher.selected = {'id':$scope.selectedDispatcher.id,'username':$scope.selectedDispatcher.username};
	}
	$scope.onSelectDispatcherCallback = function (item, model){
		$scope.addDriversData.user_id = parseInt(item.id);
	};
	/************ Dispatcher Dropdown ***************/
	$scope.addDriver = function(){
		$scope.addDriversData.address=document.getElementById('pac-input').value;
		var file = $scope.myFile;
			var fd = new FormData();
            var data = JSON.stringify($scope.addDriversData);
            fd.append('profile_image', file);
			fd.append('posted_data',data);
			$http.post(URL+'/drivers/add/',fd,{
			transformRequest: angular.identity,
			headers: {'Content-Type': undefined}
            }).then(function successCallback(response){
				if ( response.data.success == true ) {
					$rootScope.driverEditMessage = $rootScope.languageArray.driverSavedSuccMsg;
					$scope.lastAddedDriver = response.data.lastAddedDriver;
					$scope.dropzone.processQueue();
				} else {
					$rootScope.driverEditMessage = $rootScope.languageArray.driverSavedErrMsg;
				}
				$location.path('drivers');
			});
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

app.filter('filetoimg', function () {
  return function (input) {
  		var re = /(?:\.([^.]+))?$/;

		var ext = re.exec(input)[1];   
		var fname = input;
		switch(ext){
			case "pdf" : fname = input.replace("pdf","jpg"); break;
			case "bmp" : fname = input.replace("bmp","jpg"); break;
			case "png" : fname = input.replace("png","jpg"); break;
			case "doc" : fname = "doc_thumb.png"; break;
			case "xls" : fname = "xls_thumb.png"; break;
			case "xlsx" : fname = "xls_thumb.png"; break;
		}

      return fname;
  };
});
