app.controller('garagesController', function(dataFactory,$scope,$http ,$rootScope , $location , $cookies, $localStorage){
	if($rootScope.loggedInUser == false)
		$location.path('login');
  $scope.sortReverse  = false;  // set the default sort order 
	$scope.sortType='organization_name';
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
			dataFactory.httpRequest(URL+'/garages?search='+$scope.searchText+'&page='+pageNumber+'&sort='+$scope.sortType+'&order='+$scope.sortReverse).then(function(data) {
				$scope.data = data.rows;
				$scope.totalItems = data.total_records;
				$scope.pageNumber = pageNumber;
				
			});
		}else{
			dataFactory.httpRequest(URL+'/garages?page='+pageNumber+'&sort='+$scope.sortType+'&order='+$scope.sortReverse).then(function(data) {
				
				$scope.Message = $rootScope.garageEditMessage;
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
				if($scope.Message == $rootScope.garageEditMessage)
				{
					$rootScope.garageEditMessage = undefined;
				}
			});
		}
	}
	
	$scope.sortPage = function(){
			getResultsPage(1);
	
	}
	

	$scope.searchGarageDB = function(){
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

	$scope.dtOptions = {
		"scrollCollapse": true,
		"oLanguage": {
			"sLengthMenu": "_MENU_ ",
			"sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
		},
		"aaSorting": [],
		"iDisplayLength": 10,
		"rowReorder": {
            "selector": 'td:nth-child(2)'
        },
        "bLengthChange":false,
        "responsive": true,
        "columnDefs": [ {
		"targets": 4,
		"orderable": false,
		"responsivePriority":1
		} ]
	};
	
	$scope.hidedeletemessage = function(){
		$scope.alertdeletemsg = false;
	}

    $scope.removeGarage = function(garageid,index){
	  	 
    var result = confirm("Are you sure delete this item?");
   	if (result) {
      dataFactory.httpRequest(URL + '/garages/delete/'+garageid).then(function(data) {
		  
		  if ( data.success == true ) {
			$scope.garagedeleteMessage = 'The garage details has been deleted successfully.';
			$scope.alertdeletemsg = true;
			$scope.data.splice(index,1);
		}
		else
		{
			$scope.garagedeleteMessage = 'Error! : The garage details details could not be deleted.';
			$scope.alertdeletemsg = true;
			
		}
         
          
      });
    }
  }
   $scope.siteScrap = function(){
		
		dataFactory.httpRequest('scrap/getData/').then(function(data) {
			if ( data.success == true ) {
			console.log('clear');	
			}
		});
	}
   
		
});
app.controller('editGaragesController', function(dataFactory,getGaragesData, $scope,$http ,$rootScope , $location , $cookies, $stateParams){
	if($rootScope.loggedInUser == false)
		$location.path('logout');
	$scope.refreshAddress = function($event,date){
		
		angular.element($event.currentTarget).keypress();
	   	angular.element($event.currentTarget).keyup();
	
   	}
	
	$rootScope.showHeader = true;
	$scope.garagesData = {};
	$scope.garagesData = getGaragesData.garages;
	
	$scope.saveGarage = function(){
		$scope.garagesData.address=document.getElementById('pac-input').value;
		dataFactory.httpRequest('garages/update/'+$scope.garagesData.id,'POST',{},$scope.garagesData).then(function(data) {
			if ( data.success == true ) {
				$rootScope.garageEditMessage = "Success: The garage details has been saved successfully.";
			}
			else
			{
				$rootScope.garageEditMessage = 'Error: The truck garage could not be saved.';
			}
		    $location.path('garages');
		});
	}
	
	
});
app.controller('addGaragesController', function(dataFactory, $scope,$http ,$rootScope , $location , $cookies, $stateParams){
	if($rootScope.loggedInUser == false)
		$location.path('logout');
	$rootScope.showHeader = true;
	$scope.addGaragesData = {};
	$scope.addGarage = function(){
		$scope.addGaragesData.address=document.getElementById('pac-input').value;
		dataFactory.httpRequest('garages/addGarage/','POST',{},$scope.addGaragesData).then(function(data) {
			if ( data.success == true ) {
				$rootScope.garageEditMessage = "Success: The garage details has been added successfully.";
			}
			else
			{
				$rootScope.garageEditMessage = 'Error: The truck garage could not be added.';
			}
		    $location.path('garages');
		});
	}
	
	
});



