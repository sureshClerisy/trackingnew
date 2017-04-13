app.controller('brokersController', function(dataFactory,$scope, PubNub ,$http ,$rootScope , $location , $cookies, $localStorage,getBrokersListing,$timeout){
	if($rootScope.loggedInUser == false)
		$location.path('login');
	$rootScope.showHeader = true;
  	$rootScope.data = [];


  	$scope.Message = $rootScope.brokerEditMessage;
	if($scope.Message!== undefined){
		$scope.alertmsg = true;
	} else {
		$scope.alertmsg = false; 
	}

	$rootScope.data = getBrokersListing.rows;
	
	if($scope.Message == $rootScope.brokerEditMessage){
	  $rootScope.brokerEditMessage = undefined;
	}
	$rootScope.dataTableOpts(10,10);			 //set datatable options   -r288
	$scope.hidedeletemessage = function(){
		$scope.alertdeletemsg = false;
	}
	
	$scope.removeBroker = function(brokerid,index){
		angular.element("#confirm-delete").modal('show');
		angular.element("#confirm-delete").data("brokerid",brokerid);
		angular.element("#confirm-delete").data("index",index);
	}

	$scope.confirmDelete = function(confirm){
		if(confirm == 'yes'){
			var brokerid = angular.element("#confirm-delete").data("brokerid");
			var index  = angular.element("#confirm-delete").data("index");
			if ( brokerid != '' && brokerid != undefined ) {
				dataFactory.httpRequest(URL + '/brokers/delete/'+brokerid).then(function(data) {
					PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
		 		    if ( data.success == true ) {
						$scope.brokerdeleteMessage = $rootScope.languageArray.brokerDeleteSuccMsg;
						$scope.alertdeletemsg = true;
						$scope.alertmsg = false;
						$rootScope.data.splice(index,1);
					} else {
						$scope.brokerdeleteMessage = $rootScope.languageArray.brokerDeleteErrMsg;
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

	$scope.blackListBroker = function( brokerId, status, index) {
		$scope.blBrokerId = brokerId;
		$scope.blStatus = status; 
		$scope.blIndex = index; 
		angular.element("#black-list").modal('show');
	}

	$scope.confirmBlackList = function(confirm){
		if(confirm == 'yes'){
			dataFactory.httpRequest(URL + '/brokers/blackListBroker/'+$scope.blBrokerId+'/'+$scope.blStatus).then(function(data) {
				PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				angular.copy(data.records.brokerData, $scope.data[$scope.blIndex]);
				if ( data.status == true ) {
					$scope.brokerdeleteMessage = $rootScope.languageArray.brokerBlackListUpdate;
					$scope.alertdeletemsg = true;
				}else{
					$scope.brokerdeleteMessage = $rootScope.languageArray.brokerBlackListErrorMsg;
					$scope.alertdeletemsg = true;
				}
			});

		}
		angular.element("#black-list").modal('hide');
	}
});

app.controller('editBrokersController', function(dataFactory,getBrokersData, $scope, PubNub, $http ,$rootScope , $location , $cookies, $stateParams, $timeout){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');
		
	$scope.rating = 5;
    $scope.changedRating = 0;
	$rootScope.showHeader = true;
	$scope.brokersData = {};
	$scope.brokersData = getBrokersData.brokerData;
	$scope.editedBrokerId = $scope.brokersData.id;
	if(getBrokersData.brokerDocuments != undefined && getBrokersData .brokerDocuments.length > 0){
		$scope.brokerDocs = getBrokersData.brokerDocuments;	
	} else {
		$scope.brokerDocs = [];
	}
	
	$scope.brokersData.MCNumber = parseInt($scope.brokersData.MCNumber);
	$scope.brokersData.CarrierMC = parseInt($scope.brokersData.CarrierMC);
	$scope.brokersData.DOTNumber = parseInt($scope.brokersData.DOTNumber);
	$scope.changedRating = parseInt($scope.brokersData.rating);
	$scope.changedRating = $scope.changedRating == 0 ? 1: $scope.changedRating;
	
	$scope.dropzoneConfigBrokerEdit = {
		parallelUploads: 5,
		maxFileSize: 3,
		url: URL+ '/brokers/uploadContractDocs',
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
					$timeout(function() {
						$scope.brokerDocs = response.docList;
					}, 400);					
				}else{
					$scope.brokerDocs = [];
				}
				$scope.$apply();
			}
		},
	};
	
	$scope.saveBroker = function(){
		$scope.brokersData.rating = $scope.changedRating;
		dataFactory.httpRequest(URL + '/brokers/update/'+$scope.brokersData.id,'POST',{},$scope.brokersData).then(function(data) {
			PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			if ( data.success == true ) {
				$rootScope.brokerEditMessage = $rootScope.languageArray.brokerUpdatedSuccMsg;
				//~ $scope.dropzone.processQueue();
			} else {
				$rootScope.brokerEditMessage = $rootScope.languageArray.brokerUpdatedErrMsg;
			}
			 
			$location.path('broker');
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
			dataFactory.httpRequest(URL + '/brokers/deleteContractDocs/'+$scope.docId+"/"+$scope.documentName+"/"+$rootScope.srcPage).then(function(data) {
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
app.controller('addBrokersController', function(dataFactory,$scope, PubNub , $http ,$rootScope , $location , $cookies, $stateParams){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');
	$rootScope.dataNotFound = false;	
	$rootScope.showHeader = true;
	$scope.addBrokersData = {};
	$scope.changedRating = 1;
	
	$scope.dropzoneConfigBrokerAdd = {
		parallelUploads: 5,
		maxFileSize: 3,
		url: URL+ '/brokers/uploadContractDocs',
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
	
	$scope.addBroker = function(){
		dataFactory.httpRequest(URL + '/brokers/addBroker/','POST',{},{data:$scope.addBrokersData,srcPage:$rootScope.srcPage}).then(function(data) {
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
			$location.path('broker');
		});
	}
	$scope.rateFunction = function(rating) {
		$scope.changedRating = rating;
    };
	
	$scope.fetch_triumph_request = function(mcNumber){

		if(mcNumber===undefined||mcNumber===null){
			return false;
		}
		$scope.errorMessage = $rootScope.languageArray.noDetailError;
		$scope.triumphLoader = true;
		$scope.addBrokersData.TruckCompanyName = '';
		$scope.addBrokersData.TruckCompanyPhone = '';
		$scope.addBrokersData.postingAddress = '';
		dataFactory.httpRequest(URL+'/triumph/index/'+mcNumber+'/'+0+'/addBroker').then(function(data) {
		
			if ( data.length == 0 ) {
				mc_status = 'Not Available';
			} else {
				if ( data.creditResultTypeId.name == 'Credit Request Approved' ) {
					mc_status = 'Approved';
				} else {
					mc_status = 'Not Approved';
				}
				
				if ( data.newMethodInfo != undefined && data.newMethodInfo.Customers != undefined && data.newMethodInfo.Customers.length > 0 ) {
					$scope.addBrokersData.TruckCompanyName = data.newMethodInfo.Customers[0].Name;
					$scope.addBrokersData.postingAddress = data.newMethodInfo.Customers[0].Addr1;
					$scope.addBrokersData.city = data.newMethodInfo.Customers[0].City;
					$scope.addBrokersData.state = data.newMethodInfo.Customers[0].State;
					$scope.addBrokersData.zipcode = data.newMethodInfo.Customers[0].ZipCode;
					$scope.addBrokersData.DebtorKey = data.newMethodInfo.Customers[0].DebtorKey;
					$rootScope.dataNotFound = false;
				} else if ( data.companyName != null && data.phone != null ) {
					$scope.addBrokersData.TruckCompanyName = data.companyName;
					$scope.addBrokersData.city = data.city;
					$scope.addBrokersData.state = data.state;
				} else {
					$rootScope.dataNotFound = true;
				}
			}
			$scope.addBrokersData.brokerStatus = mc_status;
			$scope.triumphLoader = false;		
		});
	}
});


app.directive('starRating',
	function() {
		return {
			restrict : 'A',
			template : '<ul class="rating">'
					 + '	<li ng-repeat="star in stars" ng-class="star" ng-click="toggle($index)">'
					 + '\u2605'
					 + '</li>'
					 + '</ul>',
			scope : {
				ratingValue : '=',
				max : '=',
				onRatingSelected : '&'
			},
			link : function(scope, elem, attrs) {
				var updateStars = function() {
					scope.stars = [];
					for ( var i = 0; i < scope.max; i++) {
						scope.stars.push({
							filled : i < scope.ratingValue
						});
					}
				};
				
				scope.toggle = function(index) {
					scope.ratingValue = index + 1;
					scope.onRatingSelected({
						rating : index + 1
					});
				};
				
				scope.$watch('ratingValue',
					function(oldVal, newVal) {
						if (newVal) {
							updateStars();
						}
					}
				);
			}
		};
	}
);
