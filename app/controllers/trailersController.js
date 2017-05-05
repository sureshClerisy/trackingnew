app.controller('trailersController', function(dataFactory,$scope, PubNub, $rootScope , $location , $cookies, $localStorage, getTrailersListing,$uibModal){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');
		
	$rootScope.showHeader = true;
	
	$scope.trailers = [];
	$scope.trailers = getTrailersListing.rows;

	$scope.Message = $rootScope.trailerEditMessage;
	if($scope.Message !== undefined){
		$scope.alertmsg = true;
	}else{
		$scope.alertmsg = false; 
	}
	
	$scope.hidedeletemessage = function(){
		$scope.alertdeletemsg = false;
	}

	$rootScope.dataTableOpts(10,7);			 //set datatable options   -r288
		
	/** Change Driver Status **/
  
	$scope.changeTrailerStatus = function( trailerId, status, index) {
		$scope.trailerId = trailerId;
		$scope.Status = status; 
		$scope.statusIndex = index; 
		angular.element("#vehicle-list-status").modal('show');
	}
	
	$scope.confirmVehicleStatus = function( confirm ) {
		$scope.Message = '';
		if(confirm == 'yes'){
			dataFactory.httpRequest(URL+'/trailers/changeStatus/'+$scope.trailerId+'/'+$scope.Status).then(function(data) {
				PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				angular.copy(data.rows, $scope.trailers[$scope.statusIndex]);
				if ( data.status == true ) {
					$scope.Message = $rootScope.languageArray.trailerStatusMsg;
					$scope.alertmsg = true;
				}else{
					$scope.errMessage = $rootScope.languageArray.trailerStatusErrMsg;
					$scope.alertdeletemsg = true;
				}
			});
		} 
		angular.element("#vehicle-list-status").modal('hide');
	}   

	$scope.openAddPopup = function() {
		var modalInstance = $uibModal.open({
            templateUrl: 'assets/templates/trailers/addEditTrailer.html',
            controller: 'addEditTrailerController',
            controllerAs: 'trailers',
            openedClass: 'main-popup-custom',
            size: 'lg',
            resolve: {
               getAddTrailerData: function(dataFactory, $stateParams) {
					return dataFactory.httpRequest(URL+'/trailers/edit/1');
				}
            }
        });
	}
   
		
});

app.controller('addEditTrailerController', function(dataFactory, getAddTrailerData, $scope, PubNub, $rootScope , $location , $cookies, $stateParams, $sce){
	if($rootScope.loggedInUser == false)
		$location.path('logout');
		
	console.log(getAddTrailerData);
	$scope.trailerData = {};
	$scope.trailerData.truckName = '';
	$scope.trucksList = getAddTrailerData.fetchTrucks;
	$scope.trailerAddEditType = getAddTrailerData.trailerAddEdit;
	$rootScope.uniqueFieldsValue = true;
	$rootScope.dataNotFound = false;
	
	if(getAddTrailerData.trailerDocuments != undefined && getAddTrailerData.trailerDocuments.length > 0){
		$scope.trailerDocs = getAddTrailerData.trailerDocuments;	
	} else {
		$scope.trailerDocs = [];
	}
	$scope.trustAsHtml = function(value) {
		return $sce.trustAsHtml(value);
    };
    
	if ( getAddTrailerData.trailerAddEdit == 'add' ) {
		$scope.trailerHeading = $rootScope.languageArray.trailerHeadingForAdd;
		$scope.saveButton = $rootScope.languageArray.brokerListingTableAddbutton;
		$scope.submitType = 'add';
	} else  {
		$scope.submitType = 'edit';
		$scope.trailerHeading = $rootScope.languageArray.trailerHeadingForEdit;
		$scope.saveButton = 'Update';
		$scope.trailerData = getAddTrailerData.trailerData;
		$scope.editTrailerId = getAddTrailerData.trailerData.id;
		
		if ( $scope.trailerData.due_date == '' || $scope.trailerData.due_date == '0000-00-00' )
			$scope.trailerData.due_date = '';
	}
	
	$scope.onSelectTruckCallback = function (item, model) {
		$scope.trailerData.truckName = item.vehicleName;
		var truckId =item.id;
		if ( truckId != '' && truckId != undefined )  {
			$scope.trailerData.truck_id = item.id;
			dataFactory.httpRequest(URL+'/trailers/skipAcl_changeTruck/'+truckId+'/'+$scope.trailerData.id).then(function(data) {
				if ( data.result == false ) {
					$scope.trailerUnit = data.trailerUnit;
					$('#changeTruckOnTrailer').modal('show');
				}
			});
		}
	};
	
	$scope.changeTrailerOrNot = function(status) {
		if ( status == 'no' ) {
			$scope.trailerData.truck_id = '';
			$scope.trailerData.truckName = '';
		}
	}
	
	$scope.dropzoneConfigTrailerAdd = {
		parallelUploads: 5,
		maxFileSize: 3,
		url: URL+ '/trailers/skipAcl_uploadContractDocs',
		addRemoveLinks: true, 
		autoProcessQueue: false,
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .bmp, .svg',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("trailerId", $scope.lastAddedTrailer);
		},
		success:function(file,response){
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			file.previewElement.classList.add("dz-success");
			if(!response.error){ // succeeded
				this.removeFile(file);
				response = angular.fromJson(response);
				/*if ( response.error_exceed != undefined && response.error_exceed == 1 ) {
					
				}*/
				$scope.$apply();
				$location.path('trailers');
			}
		},
	};
	
		$scope.dropzoneConfigTrailerEdit = {
			parallelUploads: 5,
			maxFileSize: 3,
			url: URL+ '/trailers/skipAcl_uploadContractDocs',
			addRemoveLinks: true, 
			autoProcessQueue: true,
			acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .bmp, .svg',
			init:function(){
				$rootScope.imDropzone = this;
			},
			sending:function(file, xhr, formData){
				formData.append("trailerId", $scope.editTrailerId);
			},
			success:function(file,response){
				PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				file.previewElement.classList.add("dz-success");
				if(!response.error){ // succeeded
					this.removeFile(file);
					response = angular.fromJson(response);
					if(response.trailerDocuments != undefined && response.trailerDocuments.length > 0){
						$scope.trailerDocs = response.trailerDocuments;	
					} else {
						$scope.trailerDocs = [];
					}
					$scope.$apply();
				}
			},
		};
			
	
	
	$scope.saveTrailer = function(submitType){
		if ( $rootScope.uniqueFieldsValue == false ) {
			return false;
		}

		dataFactory.httpRequest(URL+'/trailers/skipAcl_addEditTrailer/'+submitType,'POST',{},$scope.trailerData).then(function(data) {
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			if ( data.success == true ) {
				if ( submitType == 'add') {

					$scope.lastAddedTrailer = data.lastInsertedId;
					$scope.dropzone.processQueue();
					$rootScope.trailerEditMessage = $rootScope.languageArray.trailerSaveMsg;
				}
				else{
					$rootScope.trailerEditMessage = $rootScope.languageArray.trailerUpdateMsg;
					$location.path('trailers');
				}
				
				
			} else {
				if ( submitType == 'add') 
					$rootScope.trailerEditMessage = $rootScope.languageArray.trailerSaveErrMsg;
				else
					$rootScope.trailerEditMessage = $rootScope.languageArray.trailerUpdateErrMsg;

				$scope.dropzone.processQueue();
			}
			
		});
	}
	
	/*
	 * Check if same trailer unit id exist
	 */
	 
	$scope.checkTrailerUnitExist = function( trailerNo, trailerId ) {
		if ( trailerNo != '' && trailerNo != undefined) {
			dataFactory.httpRequest(URL+'/trailers/skipAcl_checkTrailerUnit/'+trailerNo+'/'+trailerId).then(function(data) {
				if ( data.success == false ) {
					$rootScope.dataNotFound = true;
					$scope.errorMessage = 'Error! : This trailer unit id already exist, please try another number.';
					//~ $scope.trailerData.unit_id = '';
				} else {
					$rootScope.dataNotFound = false;
					$scope.errorMessage = '';
				}
			});
		}
	}
	
	/*
	* Method : get
	* params : entityId, entityName
	* Return : success or error
	* comment: used to delete trailer document 
	*/
	$scope.deleteDocument = function( docId, documentName, index) {
		$scope.docId = docId;
		$scope.documentName = documentName; 
		$scope.documentIndex = index; 
		angular.element("#common-document-status").modal('show');
	}

	$rootScope.confirmCommonDocumentStatus = function(confirm){
		if(confirm=='yes'){
		dataFactory.httpRequest(URL + '/trailers/deleteContractDocs/'+$scope.docId+"/"+$scope.documentName).then(function(data) {
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			if(data.success == true) {
				$scope.trailerDocs.splice($scope.documentIndex,1);
			}
	    });
		}
		angular.element("#common-document-status").modal('hide');
	}
	
});
