/*app.controller('usersController', 
	["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "getData","$compile","$filter","$log",'$q','$window','$sce','$timeout',
	function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, getData , $compile,$filter,$log,utils, $q,$window,$sce,$timeout){

	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	$scope.alertmsg 		='';	
	$scope.alertdeletemsg 	='';
	$scope.message 		 	='';	
	$scope.deleteMessage 	='';
	$scope.usersData 		= getData.data;


	$scope.changeUserStatus = function(id,status,$index){
		
		dataFactory.httpRequest(URL + '/users/changeStatus/','POST',{},{data:{userID:id,status:status}}).then(function(data) {
			$scope.alertmsg = true;
			$location.path('users');
		});	
	}


}]);*/

app.controller('manageUsersController', 
	["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", 'getData',"$compile","$filter","$log", '$q','$window','$sce','$timeout',
	function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams,  getData, $compile,$filter,$log,utils, $q,$window,$sce,$timeout){
	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	//
	$scope.alertmsg 		='';	
	$scope.alertdeletemsg 	='';
	// $scope.Message 		 	='';	
	$scope.deleteMessage 	='';
	$scope.usersData 		= getData.data;


	$scope.changeUserStatus = function(id,status,$index){
		dataFactory.httpRequest(URL + '/users/changeStatus/','POST',{},{data:{userID:id,status:status}}).then(function(data) {
			
			$scope.alertmsg = true;
			$location.path('users');
		});	
	}

	$scope.userData 		= {};
	$scope.roleList 		= getData.roles	
	$scope.request 			= $state.current.name;
	
	if($state.current.name =='editUser'){
		$scope.userData 	= getData.data[0];
		$scope.selectedRole = getData.data[0].rname;
	}

	$scope.onSelectRoleCallback = function(item, model ) {
		if(item.id != undefined && item.id != '' ) {
			$scope.selectedRole = item.name;
		}
	}

	$scope.checkUnique = function() {
		$scope.username 		= false;
		dataFactory.httpRequest(URL + '/users/checkUnique/','POST',{},{data:{username:$scope.userData.username,id:$scope.userData.id}}).then(function(data) {
			
			if(data.user !=0){
				$scope.username	= true;
			}
		});
	}

	$scope.addUser = function() {
		user_message = ($state.current.name =='editUser')?'User updated successfully.':'User added successfully.';
		
		formObj = new FormData();
		var file = document.getElementById("profileImage").files[0];
		
		formData.append('file', file);

		dataFactory.httpRequest(URL + '/users/postAddEdit/','POST',{},{data:$scope.userData,srcPage:$rootScope.srcPage,image:file}).then(function(data) {
			$scope.alertmsg = true;
			$scope.Message 	= user_message;	
			

			// $location.path('users');
		});
	}

	$scope.updateUser = function() {

	}

}]);