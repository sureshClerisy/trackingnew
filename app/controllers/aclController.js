app.controller('organisationController',function(dataFactory,$scope,$http, $rootScope, $state, $location, $stateParams, getOrganisations, $timeout, PubNub){

	var organi = this;
	organi.type 		 = $stateParams.type;	
	organi.message 		 = $stateParams.message;
	organi.organisations = getOrganisations.data;
	
	$rootScope.dataTableOpts(10,4);			 //set datatable options

	organi.hidemessages = function() {
		organi.type = '';
		organi.imageError = false;
	}
	
	if($state.current.name == 'editOrganization'){
		organi.organisations = getOrganisations.data[0];
		organi.heading = $rootScope.languageCommonVariables.orgHeaderEdit;
	} else {
		organi.heading = $rootScope.languageCommonVariables.orgHeaderAdd;
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
		$timeout(function(){
			if ( organi.notSaveOrg == false ) {
				return false;
			}

			var file = organi.profile_image;
			var fd = new FormData();
            var data = JSON.stringify(organi.organisations);
           
            fd.append('profile_image', file);
			fd.append('posted_data',data);
			fd.append('srcPage',$rootScope.srcPage);
			$http.post(URL+'/acl/AddOrganizations/',fd,{
				transformRequest: angular.identity,
				headers: {'Content-Type': undefined },
				headers: {'typeCheck': 'type' }
            }).then(function successCallback(response){
            	console.log(response);
            	if( response.data.imageUploadError != undefined && response.data.imageUploadError == 1 ) {
            		organi.imageError = true;
            		organi.imageUploadError = response.data.error;
            	} else {
            		if( $state.current.name =='editOrganization') {
						message = $rootScope.languageArray.userSuccessUpdateMsg;
					} else {
						message = $rootScope.languageArray.userSuccessAddMsg;
					}
					$state.go('organizations',{type : 'success', message : message });
					$rootScope.fetchOrganisationsList();	
            	}
			});
			// dataFactory.httpRequest(URL + '/acl/AddOrganizations/','POST',{},{ data:organi.organisations, srcPage:$rootScope.srcPage}).then(function(data) {
			// 	// PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			// 	if ( organi.organisations.id != undefined && organi.organisations.id != '') {
			// 		message = 'Success : Organisation has been updated successfully.';
			// 	} else {
			// 		message = 'Success : Organisation has been added successfully.';	
			// 	}			
			// 	$state.go('organizations',{type : 'success', message : message });
			// 	$rootScope.fetchOrganisationsList();
			// });
		}, 300);
	}



});

app.controller('aclManage', ["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "getAction","$compile","$filter","$log", 'Sample', 'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window','$sce','$timeout',function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, getAction , $compile,$filter,$log,utils, Sample, mouseOffset, debounce, moment,$q,$window,$sce,$timeout){
	 
	var ac = this;
	$scope.alertmsg 		= '';	
	$scope.alertdeletemsg 	= '';
	$scope.message 		 	= '';	
	$scope.deleteMessage 	= '';
	$scope.getAction 		= getAction.data;
	ac.checkAllParent 		= getAction.checkAll;
	$scope.companyName 		= getAction.company;
	
	$scope.showActions = function($event){		
		element = angular.element($event.currentTarget).parent().next('div.action-list');
		angular.element($event.currentTarget).children('i.fa').toggleClass('fa-plus fa-minus');
		element.slideToggle('slow');
	}

	ac.hidemessage = function() {
		$scope.alertmsg = false;
	}

	$scope.message 	= $rootScope.languageArray.permissionSuccessMsg;
	$scope.assignRemove = function(moduleID,$event,moduleName,index){
		checked 		= 0;
		$scope.alertmsg = false;
		if(angular.element($event.currentTarget).is(':checked')){
			checked = 1;
		}

		if ( checked == 0 ) 
			$scope.getAction[index].status = 0;
		else
			$scope.getAction[index].status = 1; 

		dataFactory.httpRequest(URL + '/acl/assign_module/','POST',{},{ moduleName:moduleName, org_id:$stateParams.id, moduleID:moduleID, status:checked, srcPage:$rootScope.srcPage}).then(function(data) {
			$scope.getAction 		= getAction.data;
			$scope.alertmsg = true;
		});
	}

	ac.selectAllValues = function() {		
		if(ac.selectAll == true ){
			checked = 1;
		} else {
			checked = 0;
		}
		dataFactory.httpRequest(URL + '/acl/assign_module/','POST',{},{ moduleName:$scope.getAction, org_id:$stateParams.id, status:checked, srcPage:$rootScope.srcPage, type : 'all'}).then(function(data) {
			// $scope.getAction = getAction.data;
			$scope.alertmsg  = true;
		});
		
		angular.forEach( $scope.getAction,function(value, key) {
			value.status = checked;			
		});
	}

}]);

app.controller('roleManage', function(dataFactory,$scope,$http ,$rootScope ,$state, $location , $cookies, $stateParams, getRoles ,$window,$timeout, PubNub){
	
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

		dataFactory.httpRequest(URL + '/roles/add_role/','POST',{},{data: roles.aclData,srcPage:$rootScope.srcPage}).then(function(data) {
			//PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
			if ( roles.aclData.id != undefined && roles.aclData.id != '') {
				message = $rootScope.languageArray.roleUpdateSuccessMsg;
			} else {
				message = $rootScope.languageArray.roleAddSuccessMsg;	
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
			dataFactory.httpRequest(URL + '/roles/removeroles/','POST',{},{data:$scope.roleID}).then(function(data) {
			//	PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				message 	= $rootScope.languageArray.roleDeleteSuccessMsg;
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
		dataFactory.httpRequest(URL + '/roles/skipAcl_checkRoleNameAlreadyExist/'+roleId,'POST',{},{roleName : roleName}).then(function(data) {
			if ( data.status == 'failure') {
				roles.showError = true;
				roles.message = $rootScope.languageArray.roleSameNameError;
				roles.notSaveRole =  true;
			} else {
				roles.showError = false;
				roles.notSaveRole =  false;
			}
		});

	}
});


app.controller('manageRolesPermissionsCtrl', function(rolesService,$scope,$http ,$rootScope ,$state, $location , $cookies, $stateParams, getRolesAction ,$window,$timeout, PubNub){

	var mngRoles = this;

	mngRoles.type			 = $stateParams.type;	
	mngRoles.message 		 = $stateParams.message;
	mngRoles.aclData		 = getRolesAction.data.roles;
	mngRoles.roleName 		 = getRolesAction.data.roleName;

	// mngRoles.permsissionDone = getRolesAction.data.rolesSelected;
	mngRoles.roleId 		 = getRolesAction.data.roleId;
	$scope.roleID 			 = '';
	mngRoles.child 			= {};
	mngRoles.parentCheck	= {};

	mngRoles.showActions = function($event){
		element = angular.element($event.currentTarget).parent().next('div.action-list');
		angular.element($event.currentTarget).parent().children('i.fa').toggleClass('fa-plus fa-minus');
		element.slideToggle('slow');
	}

	/**
	* changing alertdeletemsg checkbox
	*/
	mngRoles.checkUncheckAll = function(parentId,name, secArg) {
		var value = parentId+'~'+name+'~'+secArg;
		if( mngRoles.parentCheck['parent_'+parentId] ) {
			setValue = 1;
			mngRoles.aclData[value].checkAll = 'Yes';
		} else {
			setValue = 0;
			mngRoles.aclData[value].checkAll = 'No';
		}


		var actions = mngRoles.aclData[value];
		angular.forEach( actions.children,function(value, key) {
			if( setValue == 0) {
				value.selected = 0;
			}
		});

		rolesService.changeManyPermissions(setValue,actions,mngRoles.roleId)
			.then(function (response) {
			mngRoles.type = 'success';
			mngRoles.message = $rootScope.languageArray.permissionSuccessMsg;
		});
	}

	/**
	* changing single checkbox
	*/

	mngRoles.checkSingleChild = function(childId, parentId, name, secArg) {
		var value = parentId+'~'+name+'~'+secArg;
		checkCount = 0;
		
		angular.forEach( mngRoles.aclData[value].children,function(value, key) {
			if( mngRoles.child['child_'+value.id] ) {
				checkCount++;
			}
		});

		if( mngRoles.child['child_'+childId] ) {
			setValue = 1;
		} else {
			setValue = 0;
		}

		data = {
			'parent_id' : parentId,
			'childId' : childId
		}
		
		rolesService.changePermission(setValue,mngRoles.roleId,data)
			.then(function (response) {
			mngRoles.type = 'success';
			mngRoles.message = $rootScope.languageArray.permissionSuccessMsg;
		});	
		
		if ( checkCount == mngRoles.aclData[value].children.length) {
			mngRoles.parentCheck['parent_'+parentId] = true;
			mngRoles.aclData[value].checkAll = 'Yes';
		} 
	}

	/**
	* hiding the message
	*/

	mngRoles.hideMessage = function() {
		mngRoles.type = '';
	}

});