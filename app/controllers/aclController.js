app.controller('organisationController',function(dataFactory,$scope,$http, $rootScope, $state, $location, $stateParams, getOrganisations, $timeout, PubNub){

	if($rootScope.loggedInUser == false)
		$location.path('login');
	var organi = this;

	organi.type 		 = $stateParams.type;	
	organi.message 		 = $stateParams.message;
	organi.organisations = getOrganisations.data;
	
	$rootScope.dataTableOpts(10,4);			 //set datatable options

	if($state.current.name == 'editOrganization'){
		organi.organisations = getOrganisations.data[0];
	}

	/**
	* check unique username for organisation
	*/

	organi.notSaveOrg = true;
	organi.checkUniqueUsername = function() {
		dataFactory.httpRequest(URL + '/users/skipAcl_checkUnique/','POST',{},{data:{username: organi.organisations.username, id:organi.organisations.id}}).then(function(data) {
			if(data.user !=0){
				organi.notSaveOrg = false;
			} else {
				organi.notSaveOrg = true;
			}
		});
	}

	$scope.addOrg = function(){
	organi.checkUniqueUsername();	
		/*var file = $scope.myFile;

		fd = new FormData();
		fd.append('vehicle_image', file);
		fd.append('data1',$scope.name);
		$http.post(URL+'/users/uploadImage/',fd,{
			transformRequest: angular.identity,
			headers: {'Content-Type': undefined},
			headers: {'Content-Disposition': 'attachment'},
		}).then(function successCallback(response) {
			if(response.data!=='') {
				// $scope.truckData.vehicle_image = response.data;
			}
			/*dataFactory.httpRequest(URL+'/vehicles/update/'+$scope.truckData.id,'POST',{},$scope.truckData).then(function(data) {
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
		});*/

		$timeout(function(){
			if ( organi.notSaveOrg == false ) {
				return false;
			}

			dataFactory.httpRequest(URL + '/acl/AddOrganizations/','POST',{},{ data:organi.organisations, srcPage:$rootScope.srcPage}).then(function(data) {
				// PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				if ( organi.organisations.id != undefined && organi.organisations.id != '') {
					message = 'Success : Organisation has been updated successfully.';
				} else {
					message = 'Success : Organisation has been added successfully.';	
				}			
				$state.go('organizations',{type : 'success', message : message });
				$rootScope.fetchOrganisationsList();
			});
		}, 300);
	}



});

app.controller('aclManage', ["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "getAction","$compile","$filter","$log", 'Sample', 'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window','$sce','$timeout',function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, getAction , $compile,$filter,$log,utils, Sample, mouseOffset, debounce, moment,$q,$window,$sce,$timeout){

	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	$scope.alertmsg 		= '';	
	$scope.alertdeletemsg 	= '';
	$scope.message 		 	= '';	
	$scope.deleteMessage 	= '';
	$scope.getAction 		= getAction.data;
	$scope.companyName 		= getAction.company;
	
	$scope.showActions = function($event){
		
		element = angular.element($event.currentTarget).parent().next('div.action-list');
		angular.element($event.currentTarget).children('i.fa').toggleClass('fa-plus fa-minus');
		element.slideToggle('slow');
	}

	$scope.backToList = function(){
		$state.go('organizations');
	}

	$scope.assignRemove = function(moduleID,$event){
		
		checked = 0;
		$scope.message = 'Permission updated successfully.';
		if(angular.element($event.currentTarget).is(':checked')){
			checked = 1;
			$scope.message = 'Permission added successfully.';
		}

		dataFactory.httpRequest(URL + '/acl/assign_module/','POST',{},{ org_id:$stateParams.id, moduleID:moduleID, status:checked, srcPage:$rootScope.srcPage}).then(function(data) {
				$scope.getAction 		= getAction.data;
				$scope.alertmsg = true;
				$scope.message 	= 'Permission added successfully.';
		});
	}

}]);

app.controller('roleManage', function(dataFactory,$scope,$http ,$rootScope ,$state, $location , $cookies, $stateParams, getRoles ,$window,$timeout, PubNub){

	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	$rootScope.dataTableOpts(10,3);			 //set datatable options
	var roles = this;

	roles.type		= $stateParams.type;	
	roles.message 	= $stateParams.message;
	roles.aclData	= getRoles.data;
	$scope.currentuserid 	= getRoles.currentUserId;
	$scope.superadmin 		= getRoles.superadmin;
	$scope.roleID 			= '';
	roles.notSaveRole =  false;
	
	if($state.current.name == 'editrole'){
		roles.aclData 	= getRoles.data;
	}


	$scope.addrole = function(){
		if ( roles.notSaveRole == true ) {
			return false;
		}

		dataFactory.httpRequest(URL + '/acl/addroles/','POST',{},{data: roles.aclData,srcPage:$rootScope.srcPage}).then(function(data) {
			//PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			if ( roles.aclData.id != undefined && roles.aclData.id != '') {
				message = 'Success : Role has been updated successfully.';
			} else {
				message = 'Success : Role has been added successfully.';	
			}			
			$state.go('roles',{type : 'success', message : message });
		});
	}

	$scope.removeRole = function(roleId,index){
		angular.element("#confirm-delete-role").modal('show');
		$scope.roleID = roleId;
	}

	$scope.confirmDeleteRole = function(confirm){
		if(confirm == 'yes'){
			dataFactory.httpRequest(URL + '/acl/removeroles/','POST',{},{data:$scope.roleID}).then(function(data) {
			//	PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				message 	= 'Success: Role has been deleted successfully.';
				$state.go('roles',{type : 'success', message : message });
			});
		}
		angular.element("#confirm-delete-role").modal('hide');
	}

	/**
	* check role name exist or not
	*/

	roles.checkRoleExistOrNot = function(roleId,roleName) {
		roleId = (roleId != undefined ) ? roleId : '';
		dataFactory.httpRequest(URL + '/acl/checkRoleNameAlreadyExist/'+roleId,'POST',{},{roleName : roleName}).then(function(data) {
			if ( data.status == 'failure') {
				roles.showError = true;
				roles.message = 'Error : Role with same name already exist. Please try another one.'
				roles.notSaveRole =  true;
			} else {
				roles.showError = false;
				roles.notSaveRole =  false;
			}
		});

	}
});