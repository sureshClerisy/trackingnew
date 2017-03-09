app.controller('departmentsController', function(dataFactory,$scope,$http ,$rootScope , $location , $cookies, $stateParams, $localStorage){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');
	 $scope.sortReverse  = false;  // set the default sort order 
	$scope.sortType='label';
	$rootScope.showHeader = true;
	$scope.data = [];
	$scope.pageNumber = 1;
	$scope.libraryTemp = {};
	$scope.totalItemsTemp = {};

	$scope.totalItems = 0;
	$scope.pageChanged = function(newPage) {
	    getResultsPage(newPage);
	};

	getResultsPage(1);
	function getResultsPage(pageNumber) {
		  if(! $.isEmptyObject($scope.libraryTemp)){
			  dataFactory.httpRequest(URL+'/departments?search='+$scope.searchText+'&page='+pageNumber+'&sort='+$scope.sortType+'&order='+$scope.sortReverse).then(function(data) {
				$scope.data = data.rows;
				$scope.totalItems = data.total_records;
				$scope.pageNumber = pageNumber;
			  });
		  }else{
			dataFactory.httpRequest(URL+'/departments?page='+pageNumber+'&sort='+$scope.sortType+'&order='+$scope.sortReverse).then(function(data) {
				$scope.Message = $rootScope.deptEditMessage;
				if($scope.Message!== undefined)
				{
				$scope.alertmsg = true;
				}
				else
				{
					$scope.alertmsg = false; 
				}
			  $scope.data = data.rows;
			  $scope.totalItems = data.total_records;
			  $scope.pageNumber = pageNumber;
			  if($scope.Message == $rootScope.deptEditMessage)
			  {
				  $rootScope.deptEditMessage = undefined;
			  }
      
			});
		  }
	  }
	$scope.sortPage = function(){
			getResultsPage(1);
	
	}
	$scope.searchDepartmentDB = function(){
		if($scope.searchText.length >= 2){
			if($.isEmptyObject($scope.libraryTemp)){
				$scope.libraryTemp = $scope.data;
				$scope.totalItemsTemp = $scope.totalItems;
				$scope.data = {};
			}
			getResultsPage(1);
			}else{
				if(! $.isEmptyObject($scope.libraryTemp)){
				$scope.data = $scope.libraryTemp ;
				$scope.totalItems = $scope.totalItemsTemp;
				$scope.libraryTemp = {};
			}
		}
	}
	
	$scope.hidedeletemessage = function(){
		$scope.alertdeletemsg = false;
	}
	$scope.removeDept = function(deptid,index){	 
		var result = confirm("Are you sure delete this item?");
		if (result) {
			dataFactory.httpRequest(URL + '/departments/delete/'+deptid).then(function(data) {
				if ( data.success == true ) {
					$scope.deptdeleteMessage = 'The department details has been deleted successfully.';
					$scope.alertdeletemsg = true;
					$scope.data.splice(index,1);
				}
				else
				{
					$scope.deptdeleteMessage = 'Error! : The department details details could not be deleted.';
					$scope.alertdeletemsg = true;
					
				}
			});
		}
	}
   
		
});

app.controller('editDepartmentController', function(dataFactory,getDepartmentData, $scope,$http ,$rootScope , $location , $cookies, $stateParams){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	$rootScope.showHeader = true;
		
	$scope.departmentData = {};
	$scope.departmentData = getDepartmentData.department;
	
	
	$scope.saveDept = function(){
		dataFactory.httpRequest('departments/update/'+$scope.departmentData.id,'POST',{},$scope.departmentData).then(function(data) {
			if ( data.success == true ) {
				$rootScope.deptEditMessage = "Success: The department details has been saved successfully.";
			}
			else
			{
				$rootScope.deptkEditMessage = 'Error: The department details could not be saved.';
			}
			 
			$location.path('departments');
		});
	}
	
	
});

