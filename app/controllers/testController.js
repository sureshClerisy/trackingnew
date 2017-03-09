app.controller('testController', function( dataFactory,$rootScope,$sce,$http ,$scope ,$state, $location , $cookies, $stateParams, $localStorage, getAllTrucksStopData ,  $compile,$timeout){
	
	$scope.loadSize = [{ 'loadvalue' : 'Full','loadkey' : 'Full'}];
	 $scope.cityOptions = {
            highlight: true
        };
    //----------drop-down ---------------------------
 
    $scope.duplicatejobstatus='';
	$scope.trustAsHtml = function(value) {
            return $sce.trustAsHtml(value);
    };


	
    $scope.onSelectCallback = function (item, model){
		$scope.form.destination_state = item.code;
		$("#select_state").val(item.code);
	};
  
    //----------drop-down ---------------------------

	$rootScope.Message = '';
	
	$rootScope.showHeader = true;
	
	$scope.states_data = {};
	$rootScope.form = {};
	$rootScope.form.trailerType = 'F';
	$rootScope.form.loadType = 'Full';
	$rootScope.form.origin_country = 'USA';
	$rootScope.form.destination_country = 'USA';
	$rootScope.form.origin_range = 100;
	$rootScope.form.destination_range = 100;
	$rootScope.form.posted_time1 = {'val' : '8 Hours Ago','key' : '8'};
	$rootScope.form.posted_time = 8;
	

	
	$scope.showDiv = false;
	$scope.newSearchButtonShow = false;
	$scope.onSelectpostedTime = function(item) {
		$rootScope.form.posted_time = item.key;
	}	
	
	$scope.fetchSearchResultsTest = function() {
		$rootScope.form.multistateCheck = $scope.multistateCheck;
		$rootScope.form.multistateOriginCheck = $scope.multistateOriginCheck;
		if ( $rootScope.form.moreLoadCheck == true )
			$rootScope.form.moreLoadCheck = 1;
		else 
			$rootScope.form.moreLoadCheck = 0;
			
		$scope.newSearchButtonShow = true;
		dataFactory.httpRequest(URL+'/test/fetchTestSearchResults','POST',{},{post:$rootScope.form}).then(function(data){
				$scope.showDiv = true;	
				$scope.results = data.loadsData.rows;		
				$scope.totalRecords = data.loadsData.tableCount;		
		});
	}

	

			
});





