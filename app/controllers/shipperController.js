app.controller('shipperController', function(dataFactory,$scope, PubNub , $http ,$rootScope , $location , $cookies, $stateParams,getShipperListing){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');
	$rootScope.dataNotFound = false;	
	$rootScope.showHeader 	= true;
	$rootScope.data 		= getShipperListing.rows;
	$scope.changedRating 	= 1;

	$scope.hidedeletemessage = function(){
		$scope.alertdeletemsg = false;
	}

	$scope.removeShipper = function(brokerid,index){
		angular.element("#confirm-delete").modal('show');
		angular.element("#confirm-delete").data("brokerid",brokerid);
		angular.element("#confirm-delete").data("index",index);
	}

	$scope.confirmDelete = function(confirm){
		if(confirm == 'yes'){
			var brokerid = angular.element("#confirm-delete").data("brokerid");
			var index  = angular.element("#confirm-delete").data("index");
			if ( brokerid != '' && brokerid != undefined ) {
				dataFactory.httpRequest(URL + '/shippers/delete/'+brokerid).then(function(data) {
					PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
		 		    if ( data.success == true ) {
						$scope.brokerdeleteMessage = $rootScope.languageArray.shipperDeleteSuccMsg;
						$scope.alertdeletemsg = true;
						$scope.alertmsg = false;
						$rootScope.data.splice(index,1);
					} else {
						$scope.brokerdeleteMessage = $rootScope.languageArray.shipperDeleteErrMsg;
						$scope.alertdeletemsg = true;
						$scope.alertmsg = false;
					}  
			    });
			}
		}else{
			angular.element("#confirm-delete").removeData("brokerid");
			angular.element("#confirm-delete").removeData("index");
		}
		angular.element("#confirm-delete").modal('hide');
	}


});

app.controller('addShipperController', function(dataFactory,$scope, PubNub , $http ,$rootScope , $location , $cookies, $stateParams){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');
	$rootScope.dataNotFound = false;	
	$rootScope.showHeader = true;
	$scope.addBrokersData = {};
	$scope.changedRating = 1;
	
	$scope.dropzoneConfigBrokerAdd = {
		parallelUploads: 5,
		maxFileSize: 3,
		url: URL+ '/shippers/uploadContractDocs',
		addRemoveLinks: true, 
		autoProcessQueue: false,
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .bmp, .svg',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("brokerId", $scope.lastBrokerId);
			formData.append("srcPage", $rootScope.srcPage);
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
	
	$scope.addshipper = function(){
		dataFactory.httpRequest(URL + '/shippers/addShipper/','POST',{},{data:$scope.addBrokersData,srcPage:$rootScope.srcPage}).then(function(data) {
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			if ( data.success == true ) {
				if(data.update == true){
					$rootScope.brokerEditMessage = $rootScope.languageArray.brokerExistingSuccMsg;	
				}else{
					$rootScope.brokerEditMessage = $rootScope.languageArray.brokerSavedSuccMsg;
				}
				$scope.lastBrokerId = data.lastInsertId;
				$scope.dropzone.processQueue();
			} else {
				$rootScope.brokerEditMessage = $rootScope.languageArray.brokerSavedErrMsg;
			}
			$location.path('shipper');
		});
	}
	$scope.rateFunction = function(rating) {
		$scope.changedRating = rating;
    };
});

app.controller('editShipperController', function(dataFactory,getShipperData, $scope, PubNub, $http ,$rootScope , $location , $cookies, $stateParams){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');
		
	$scope.rating = 5;
    $scope.changedRating = 0;
	$rootScope.showHeader = true;
	$scope.brokersData = {};
	$scope.brokersData = getShipperData.brokerData;
	$scope.editedBrokerId = $scope.brokersData.id;
	
	if(getShipperData.brokerDocuments != undefined && getShipperData .brokerDocuments.length > 0){
		$scope.brokerDocs = getShipperData.brokerDocuments;	
	} else {
		$scope.brokerDocs = [];
	}
	
	$scope.changedRating = parseInt($scope.brokersData.rating);
	$scope.changedRating = $scope.changedRating == 0 ? 1: $scope.changedRating;
	
	$scope.dropzoneConfigBrokerEdit = {
		parallelUploads: 5,
		maxFileSize: 3,
		url: URL+ '/shippers/uploadContractDocs',
		addRemoveLinks: true, 
		//~ autoProcessQueue: false,
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .bmp, .svg',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("brokerId", $scope.editedBrokerId);
		},
		success:function(file,response){
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			file.previewElement.classList.add("dz-success");
			if(!response.error){ // succeeded
				this.removeFile(file);
				response = angular.fromJson(response);
				if ( response.error_exceed != undefined && response.error_exceed == 1 ) {
					/*$rootScope.ExceedMessage = 'Error! : The uploaded document exceeds the maximum allowed limit of 128MB.';*/
				}
				else if(response.docList != undefined && response.docList.length > 0){
					$scope.brokerDocs = response.docList;
				}else{
					$scope.brokerDocs = [];
				}
				
				console.log($scope.brokerDocs);
				$scope.$apply();
			}
		},
	};
	
	$scope.saveBroker = function(){
		$scope.brokersData.rating 	= $scope.changedRating;
		dataFactory.httpRequest(URL + '/shippers/update/'+$scope.brokersData.id,'POST',{},$scope.brokersData).then(function(data) {
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			if ( data.success == true ) {
				$rootScope.brokerEditMessage = $rootScope.languageArray.brokerUpdatedSuccMsg;
				//~ $scope.dropzone.processQueue();
			} else {
				$rootScope.brokerEditMessage = $rootScope.languageArray.brokerUpdatedErrMsg;
			}
			 
			//$location.path('shipper');
		});
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
		if(confirm == 'yes'){
			dataFactory.httpRequest(URL + '/shippers/deleteContractDocs/'+$scope.docId+"/"+$scope.documentName+"/"+$rootScope.srcPage).then(function(data) {
				PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				if(data.success == true) {
					$scope.brokerDocs.splice($scope.documentIndex,1);
				}
			});
		}
		angular.element("#common-document-status").modal('hide');
	}
	
	$scope.rateFunction = function(rating) {
		$scope.changedRating = rating;
    };
	
});