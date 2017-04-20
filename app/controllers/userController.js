app.controller('usersController', 
	["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "getUsers","$compile","$filter","$log",'$q','$window','$sce','$timeout',
	function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, getUsers , $compile,$filter,$log,utils, $q,$window,$sce,$timeout){

	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	$scope.alertmsg 		='';	
	$scope.alertdeletemsg 	='';
	$scope.message 		 	='';	
	$scope.deleteMessage 	='';
	$scope.organisations = getUsers.data;

}]);

// app.controller('aclManage', ["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "getAction","$compile","$filter","$log", 'Sample', 'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window','$sce','$timeout',function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, getAction , $compile,$filter,$log,utils, Sample, mouseOffset, debounce, moment,$q,$window,$sce,$timeout){

// 	if($rootScope.loggedInUser == false)
// 		$location.path('login');
	
// 	$scope.alertmsg 		='';	
// 	$scope.alertdeletemsg 	='';
// 	$scope.message 		 	='';	
// 	$scope.deleteMessage 	='';
// 	$scope.getAction = getAction.data;


// }]);

// app.controller('roleManage', ["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "getRoles","$compile","$filter","$log",'$window','$timeout',function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, getRoles , $compile,$filter,$log,utils,$window,$timeout){

// 	if($rootScope.loggedInUser == false)
// 		$location.path('login');
	
// 	$scope.alertmsg 		='';	
// 	$scope.alertdeletemsg 	='';
// 	$scope.message 		 	='';	
// 	$scope.deleteMessage 	='';

// 	$scope.aclData	= getRoles.data;
// 	$scope.addrole = function(){
// 		dataFactory.httpRequest(URL + '/acl/addroles/','POST',{},{data:$scope.aclData,srcPage:$rootScope.srcPage}).then(function(data) {
// 			$scope.message 	= 'Role added successfully.';
// 			$scope.alertmsg = true;
// 			$location.path('roles');
// 		});
// 	}

// 	$scope.removeRole = function(roleId,index){
// 		dataFactory.httpRequest(URL + '/acl/removeroles/','POST',{},{data:roleId}).then(function(data) {
// 			$scope.deleteMessage 	= 'Role deleted successfully.';
// 			$scope.alertdeletemsg = true;
// 			$location.path('roles');
// 		});
// 	}
// }]);