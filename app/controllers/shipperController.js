app.controller('shipperController', function(dataFactory, $scope, PubNub , $http ,$rootScope ,getShipperListing, shipperService, fetchStatesList,$sce, $state, $stateParams){
	if($rootScope.loggedInUser == false)
		$state.go('login');

	$rootScope.dataNotFound = false;	
	$rootScope.showHeader 	= true;

	var shp 		= this;
	shp.type 		= $stateParams.type;
	shp.message 	= $stateParams.message;
	shp.shipperData = getShipperListing.rows;
	shp.totalRecords = getShipperListing.total;
	shp.lastSortedColumn = 'id';
	shp.currentPage  = 1;
	shp.lastSortType = 'DESC';
	shp.searchFilter = '';

	$scope.changedRating 	= 1;
	shp.states_data 		= fetchStatesList;

	$scope.trustAsHtml = function(value) {
		return $sce.trustAsHtml(value);
    };

	/**
	* fetch records per page from db
	*/

	shp.pageChanged = function(newPage){
		shp.currentPage = newPage;
		shp.loadNextPage((shp.currentPage - 1), shp.searchFilter, shp.lastSortedColumn, shp.lastSortType);
	};

	/**
	* searching the records
	*/
	shp.callSearchFilter = function(query){
		shp.searchFilter = query;
		shp.loadNextPage((shp.currentPage - 1), query, shp.lastSortedColumn, shp.lastSortType);
	};

	/**
	* sorting the columns
	*/
	shp.sortCustom = function(sortColumn,type) {
		type = type === "ASC" ? "DESC" : "ASC";
		shp.lastSortedColumn = sortColumn;
		shp.lastSortType 	 = type;
		shp.shipperNameType = ''; shp.postAddressType = ''; shp.cityType = ''; shp.stateType = ''; shp.zipCodeType = ''; shp.statusType = ''; 

		switch(sortColumn){
			case 'shipperCompanyName' 		: shp.shipperNameType 	= type; break;
			case 'postingAddress'			: shp.postAddressType 	= type; break;
			case 'city'						: shp.cityType 			= type; break;
			case 'state'			 	: shp.stateType 		= type; break;
			case 'zipcode'					: shp.zipCodeType 		= type; break;
			case 'status'					: shp.statusType 		= type; break;
		}
	
		shp.loadNextPage((shp.currentPage - 1), shp.searchFilter, sortColumn, type);
	};

	shp.loadNextPage = function(pageNumber,search,sortColumn,sortType){ 
		$scope.autoFetchLoads = true;
		var shipperData = {};
		shipperData.pageNo = pageNumber;
		shipperData.searchQuery = search;
		shipperData.sortColumn = sortColumn;
		shipperData.sortType = sortType;

		shipperService.fetchShippersList(shipperData)
			.then(function (response) {
				if(response.rows != undefined ) {
					shp.shipperData  = response.rows;
					shp.totalRecords = response.total;
				}
			});
		$scope.autoFetchLoads = false;
	};


	$scope.toggleRow = function($event,index){
		angular.element("#hblock"+index).slideToggle();
		angular.element($event.target).toggleClass("minus-1");
	}


	$scope.hidedeletemessage = function(){
		$scope.alertdeletemsg = false;
	}

	shp.removeShipper = function(shipperId,index){
		angular.element("#confirm-delete").modal('show');
		angular.element("#confirm-delete").data("shipperId",shipperId);
		angular.element("#confirm-delete").data("index",index);
	}

	shp.changeShipperStatus = function(shipperId, status, index) {
		angular.element("#confirm-delete").modal('show');
		angular.element("#confirm-delete").data("shipperId",shipperId);
		angular.element("#confirm-delete").data("index",index);
		angular.element("#confirm-delete").data("shipperStatus",status);
	}

	$rootScope.confirmDelete = function(confirm){
		
		shp.type 	= '';
		if(confirm == 'yes'){
			var shipperId 		= angular.element("#confirm-delete").data("shipperId");
			var index  			= angular.element("#confirm-delete").data("index");
			var shipperStatus  	= angular.element("#confirm-delete").data("shipperStatus");

			if ( shipperId != '' && shipperId != undefined ) {
				if( shipperStatus != undefined ) {
					shipperService.changeShipperStatus(shipperId, shipperStatus)
						.then(function (response) {
							PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
							angular.copy(response.records.rows, shp.shipperData[index]);
							if ( response.status == true ) {
								$scope.brokerdeleteMessage = 'Success: shipper status has been updated successfully.';
								$scope.alertdeletemsg = true;
								$scope.alertmsg = false;
							} else {
								$scope.brokerdeleteMessage = 'Error !: shipper status could not be updated successfully.';
								$scope.alertdeletemsg = true;
								$scope.alertmsg = false;
							}
					});
				} else {
					shipperService.deleteShipper(shipperId)
						.then(function (response) {
							PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
							if ( response.success == true ) {
								$scope.brokerdeleteMessage = $rootScope.languageArray.shipperDeleteSuccMsg;
								$scope.alertdeletemsg = true;
								$scope.alertmsg = false;
								shp.shipperData.splice(index,1);
							} else {
								$scope.brokerdeleteMessage = $rootScope.languageArray.shipperDeleteErrMsg;
								$scope.alertdeletemsg = true;
								$scope.alertmsg = false;
							}
					});
				}
			}
		} else {
			angular.element("#confirm-delete").removeData("brokerid");
			angular.element("#confirm-delete").removeData("index");
		}
		angular.element("#confirm-delete").modal('hide');
	}
$scope.toggleRow = function($event,index){
		angular.element("#hblock"+index).slideToggle();
		angular.element($event.target).toggleClass("minus-1");
	}

	

	$scope.dropzoneConfigShipperAdd = {
		parallelUploads: 5,
		maxFileSize: 3,
		url: URL+ '/shippers/skipAcl_uploadContractDocs',
		addRemoveLinks: true, 
		autoProcessQueue: false,
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .bmp, .svg',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("brokerId", shp.lastBrokerId);
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

	$scope.addBrokersData = {};
	$scope.changedRating = 1;
	shp.stateSelected = '';
	
	shp.addshipper = function(){
		$scope.addBrokersData.rating = $scope.changedRating == 0 ? 1: $scope.changedRating;
		shipperService.addShipperData($scope.addBrokersData,$rootScope.srcPage)
			.then(function (response) {
				
				PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				if ( response.success == true ) {
					$scope.Message = $rootScope.languageArray.shipperSavedSuccMsg;
					shp.lastBrokerId = response.lastInsertId;
					$scope.dropzone.processQueue();
				} else {
					$scope.Message = $rootScope.languageArray.shipperSavedErrMsg;
				}
				$state.go('shipper',{ type: 'success', message: $scope.Message });
			});
	};

	shp.onSelectStateCallback = function(item, model ) {
		if(item.id != undefined && item.id != '' ) {
			shp.stateSelected = item.code;
			$scope.addBrokersData.state = item.code;
		}
	}

	$scope.rateFunction = function(rating) {
		$scope.changedRating = rating;
    };


});

app.controller('editShipperController', function(dataFactory,getShipperData, $scope, PubNub, $http ,$rootScope , $cookies, $stateParams, fetchStatesList, $timeout, $state){
	
	if($rootScope.loggedInUser == false)
		$state.go('login');
		
	$scope.rating = 5;
    $scope.changedRating = 0;
	$rootScope.showHeader = true;
	$scope.brokersData = {};
	$scope.brokersData = getShipperData.brokerData;
	$scope.stateSelected = $scope.brokersData.state;
	$scope.editedBrokerId = $scope.brokersData.id;
	
	$scope.states_data = fetchStatesList;
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
		url: URL+ '/shippers/skipAcl_uploadContractDocs',
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
					$timeout(function(){
						$scope.brokerDocs = response.docList;
					},400);					
				}else{
					$scope.brokerDocs = [];
				}
				
				$scope.$apply();
			}
		},
	};
	
	$scope.saveBroker = function(){
		$scope.brokersData.rating 	= $scope.changedRating;
		dataFactory.httpRequest(URL + '/shippers/update/'+$scope.brokersData.id,'POST',{},$scope.brokersData).then(function(data) {
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });

			if ( data.success == true ) {
				$rootScope.Message = $rootScope.languageArray.shipperUpdatedSuccMsg;
			} else {
				$rootScope.Message = $rootScope.languageArray.shipperUpdatedErrMsg;
			}
			$state.go('shipper',{ type: 'success', message: $rootScope.Message });
			
		});
	}

	$scope.onSelectStateCallback = function(item, model ) {
		if(item.id != undefined && item.id != '' ) {
			$scope.stateSelected = item.code;
			$scope.brokersData.state = item.code;
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