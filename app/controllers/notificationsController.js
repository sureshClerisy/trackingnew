//app.controller('notificationsController', ["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "$localStorage", "allNotifications","$compile","$filter","$log","ganttUtils",'GanttObjectModel', 'Sample', 'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window','$sce','$timeout','DTOptionsBuilder',function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, $compile,$filter,$log,utils, ObjectModel, allNotifications, Sample, mouseOffset, debounce, moment,$q,$window,$sce,$timeout){
app.controller('notificationsController', ["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "allNotifications","$compile","$filter","$log", 'Sample', 'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window','$sce','$timeout',function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, allNotifications , $compile,$filter,$log,utils, Sample, mouseOffset, debounce, moment,$q,$window,$sce,$timeout){
	$scope.notifications = allNotifications.notifications;
	$scope.showLoadMore = true;
	$scope.pageNo = 1;
	$scope.loadMoreNotifications = function(){
		$scope.pageNo++;
		$scope.autoFetchLoads = true;
		dataFactory.httpRequest(URL+'/Login/notifications','POST',{},{pageNo: $scope.pageNo}).then(function(data){
			$scope.autoFetchLoads = false;
			if(data.notifications.length > 0){
				angular.forEach(data.notifications, function(value, key) {
					$scope.notifications.push(value);	
				});
			}else{
				$scope.showLoadMore = false;
			}
	    });
	}
	


}]);