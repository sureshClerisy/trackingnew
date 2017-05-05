app.controller('usersController', function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams,  getData,$timeout, PubNub){

	var usr = this;
	usr.type			= $stateParams.type;
	usr.message 		= $stateParams.message;
	usr.usersData 		= getData.data;

	$rootScope.dataTableOpts(10,4);			 //set datatable options

	usr.hidemessages = function() {
		usr.type = '';
		usr.imageError = false;
	}
	/**
	* changing user status 
	*/

	usr.changeUserStatus = function(id,status,index) {
		usr.userid = id;
		usr.status = status; 
		usr.usersIndex = index; 
		angular.element("#vehicle-list-status").modal('show');
	}

	$scope.confirmVehicleStatus = function( confirm) {
		if(confirm == 'yes'){
			dataFactory.httpRequest(URL+'/users/changeStatus/'+usr.userid+'/'+usr.status).then(function(data) {
				// PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
				angular.copy(data.userData, usr.usersData[usr.usersIndex]);
				if ( data.success == true ) {
					usr.message = $rootScope.languageArray.userStatusUpdateSuccMsg;
					usr.type = 'success';
				}else{
					usr.message = $rootScope.languageArray.userStatusUpdateErrMsg;
					usr.type = 'failure';
				}
			});
		} 
		angular.element("#vehicle-list-status").modal('hide');
	}

	/**
	* delete users from list
	*/

	usr.deleteUser = function(id,index) {
		usr.userid = id;
		usr.usersIndex = index; 
		angular.element("#confirm-delete").modal('show');
	}

	$rootScope.confirmDelete = function( confirm) {
		if(confirm == 'yes'){
			dataFactory.httpRequest(URL+'/users/deleteUser/'+usr.userid).then(function(data) {
				if ( data.success == true ) {
					usr.message = $rootScope.languageArray.userDeleteSuccMsg;
					usr.usersData.splice(usr.usersIndex ,1);
				} else {
					usr.message = $rootScope.languageArray.userDeleteErrMsg;
					usr.type = 'failure';
				}
			});
		} 
		angular.element("#confirm-delete").modal('hide');
	}

	usr.imageError = false;
	usr.userData 		= {};
	$scope.roleList 	= getData.roles	
	$scope.request 		= $state.current.name;
	usr.notSaveUser 	=  true;

	if($state.current.name =='editUser'){
		usr.userData 	  = getData.data[0];
		console.log(usr.userData);
		usr.selectedRole  = getData.data[0].rname;
		usr.userHeading   = $rootScope.languageArray.editUser;
	} else {
		usr.userHeading   = $rootScope.languageArray.addUser;
	}

	$scope.onSelectRoleCallback = function(item, model ) {
		if(item.id != undefined && item.id != '' ) {
			usr.selectedRole = item.name;
			usr.userData.role_id = item.id;
		}
	}

	/**
	* checking unique username of user
	*/

	usr.checkUnique = function() {
		dataFactory.httpRequest(URL + '/users/skipAcl_checkUnique/','POST',{},{data:{username:usr.userData.username, id:usr.userData.id}}).then(function(data) {
			if(data.user !=0){
				usr.notSaveUser =  false;
			} else {
				usr.notSaveUser =  true;
			}			
		});
	}

	/**
	* save user to db
	*/

	$scope.addUser = function() {
		usr.checkUnique();

		$timeout(function() {
			if ( usr.notSaveUser == false ) {
				return false;
			}
			var file = usr.profile_image;
			var fd = new FormData();
            var data = JSON.stringify(usr.userData);
           
            fd.append('profile_image', file);
			fd.append('posted_data',data);
			fd.append('srcPage',$rootScope.srcPage);
			$http.post(URL+'/users/skipAcl_postAddEdit/',fd,{
				transformRequest: angular.identity,
				headers: {'Content-Type': undefined },
				headers: {'typeCheck': 'type' }
            }).then(function successCallback(response){
            	console.log(response);
            	if( response.data.imageUploadError != undefined && response.data.imageUploadError == 1 ) {
            		usr.imageError = true;
            		usr.imageUploadError = response.data.error;
            	} else {
            		if( $state.current.name =='editUser') {
						message = $rootScope.languageArray.userSuccessUpdateMsg;
					} else {
						message = $rootScope.languageArray.userSuccessAddMsg;
					}
					$state.go('users',{type : 'success', message : message });
            	}
			});
               
			// formObj = new FormData();
			// var file = document.getElementById("profileImage").files[0];
			// formData.append('file', file);
			// dataFactory.httpRequest(URL + '/users/skipAcl_postAddEdit/','POST',{},{data: usr.userData,srcPage:$rootScope.srcPage}).then(function(data) {
			// 	if( $state.current.name =='editUser') {
			// 		message = 'Success : User has been updated successfully.';
			// 	} else {
			// 		message = 'Success : User has been added successfully.';
			// 	}
			// 	$state.go('users',{type : 'success', message : message });
			// });
		},300);
	};

});