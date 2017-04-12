app.controller('filteredBillingsController', ["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "getBillingData","$compile","$filter","$log", 'Sample', 'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window','$sce','$timeout',function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, getBillingData , $compile,$filter,$log,utils, Sample, mouseOffset, debounce, moment,$q,$window,$sce,$timeout){
	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	//----------drop-down ---------------------------
    $rootScope.extraStops = '';
    $scope.duplicatejobstatus='';
	
	//~ $scope.trustAsHtml = function(value) {
        //~ return $sce.trustAsHtml(value);
    //~ };
    $scope.onSelectStateCallback = function (item, model){
		$rootScope.editSavedLoad.OriginState = item.code;
		$rootScope.originPlaceholderValue = item.label;
	};
	$scope.onSelectDestStateCallback = function (item, model){
		$rootScope.editSavedLoad.DestinationState = item.code;
		$rootScope.destinationPlaceholder = item.label;
	};
    
	$scope.onSelectExtraStopCallback = function (item, model){
		$rootScope.editSavedLoad.Stops = item.key;
	};
	
	//----------drop-down ---------------------------	

	$rootScope.editLoads 	= true;
	$rootScope.matchingTrucks = false;
	$rootScope.showMaps 	= false;
	$rootScope.showhighlighted = 'loadDetail';

	$scope.showPlusMinus 	= true;
	$scope.canDocsShow 		= true;
	

	$scope.showCalendar = true;
	$rootScope.filteredBillingLoads = [];
	$rootScope.Docs = [];
	$scope.dateRangeSelector = {};
	$scope.noRecordFoundMessage = $rootScope.languageCommonVariables.noRecordFound;
	$scope.filterArgs = getBillingData.filterArgs;
	$scope.firstParam = getBillingData.filterArgs.firstParam;
	$rootScope.filteredBillingLoads = getBillingData.loads;
	$scope.total            = getBillingData.total;
	$scope.DeliveryDateSortType = "DESC"; 					// intially setting delivery date column to desc		
	$rootScope.saveTypeLoad = 'filteredBillingLoads';    			// setting the save type for dynamic changing the listing on routes
	
	if(Object.keys($rootScope.filteredBillingLoads).length <= 0){
		$scope.haveRecords = true;
	}else{
		$scope.haveRecords = false;
	}

	if($scope.filterArgs.requestFrom != undefined && $scope.filterArgs.requestFrom == "billings"){
		$scope.showCalendar = false;
	}else{
		$scope.showCalendar = true;
	}

	if(getBillingData.filterArgs.startDate != undefined){
		$scope.dateRangeSelector.startDate = getBillingData.filterArgs.startDate; 	
		$scope.dateRangeSelector.endDate = getBillingData.filterArgs.endDate; 	
	}else{
		if(getBillingData.filterArgs.requestFrom != undefined && getBillingData.filterArgs.requestFrom != "billings"){
			$scope.dateRangeSelector = {startDate: moment().startOf('month').format('YYYY-MM-DD'), endDate: moment().format('YYYY-MM-DD')};    
		}
	}

	/*if( $cookies.getObject('_gDateRange') ){
        $scope.dateRangeSelector = $cookies.getObject('_gDateRange');
        if($scope.dateRangeSelector.startDate == null || $scope.dateRangeSelector.endDate == null){
        	$scope.dateRangeSelector = {};
        }
    }else{
        $scope.dateRangeSelector = {startDate: moment().subtract(29, 'days'), endDate: moment()};    
        $cookies.putObject('_gDateRange', $scope.dateRangeSelector);  
    }
*/

	$scope.opts = {
		opens:'left',
     	autoUpdateInput: false,
        locale: {
            applyClass: 'btn-green',
            applyLabel: "Apply",
            fromLabel: "From",
            format: "YYYY-MM-DD",
            toLabel: "To",
            cancelLabel: 'Clear',
        },
        eventHandlers: {
            'apply.daterangepicker': function(ev, picker) { 
            	if ( $scope.dateRangeSelector.startDate != null && $scope.dateRangeSelector != undefined && Object.keys($scope.dateRangeSelector).length > 0 ) { 
	            	$scope.dateRangeSelector.startDate = $scope.dateRangeSelector.startDate.format('YYYY-MM-DD');
	                $scope.dateRangeSelector.endDate = $scope.dateRangeSelector.endDate.format('YYYY-MM-DD');
	            }else{
	            	$scope.dateRangeSelector = {};
	            }
	            $scope.loadItems();
            },
            'cancel.daterangepicker': function(ev, picker) {  
                $scope.dateRangeSelector = {};
                angular.element('#filteredLoadsDRPicker').data('daterangepicker').setStartDate(new Date());
                angular.element('#filteredLoadsDRPicker').data('daterangepicker').setEndDate(new Date());
                $scope.loadItems();
            }
        },
    };


	$scope.getProfitPercent = function(profitAmount1, totalPayment1){
		var profitPercent= ((profitAmount1 / totalPayment1) * 100).toFixed(2);	
		return profitPercent;
	};
	

	$rootScope.nonewRequest = false;
		
	/*************Fetching load Details start**********************/
	
	/**Clicking on load detail changes url withour reload state*/
	$scope.clickMatchLoadDetail = function(truckstopId,loadId, deadmile,calPayment,totalCost,orignPickDate,vehicleID, index ) {
		if ( loadId == '' && loadId == undefined ) 
			loadId = '';
			
		$rootScope.globalListingIndex = index;			// set index to update the particular record from list		
		$rootScope.globalVehicleId = vehicleID;
		encodedUrl = btoa(truckstopId+'-'+loadId+'-'+deadmile+'-'+calPayment+'-'+totalCost+'-'+orignPickDate+'-'+vehicleID);
		$state.go('search.popup', {staticId:2,encodedurl:encodedUrl}, {notify: false,reloadOnSearch: false});
	}	


	$scope.hideLoadDetailPopup = function() {
		$rootScope.firstTimeClick = true;
		var url = decodeURI($rootScope.absUrl.q);
	
		if ( url != '' && url != undefined && url != 'undefined' ) {
			$state.go('billings', {'key':$scope.firstParam,q:url,type:false}, {notify: false,reload: false});
		} 
		else {
			$state.go('search', {}, {notify: false,reload: false});	
		}	
	}
		
	
	/*Changing url on outer click of popup*/
	$(document).off('click','#edit-fetched-load').on("click",'#edit-fetched-load', function(event){
		var $trigger1 = $(".popup-container-wid1");
		var url = decodeURI($rootScope.absUrl.q);
		if($trigger1 !== event.target && !$trigger1.has(event.target).length){
			if ( $rootScope.statesArr[0] != '' && $rootScope.statesArr[0] != undefined ) {
				$state.go($rootScope.statesArr[0], {'key':$scope.firstParam,q:url,type:false}, {notify: false,reload: false});
			} else {
				$state.go('search', {'key':$scope.firstParam,q:url}, {notify: false,reload: false});
			}
		}
	});
	
	
	/***********Load Details ends******************/
	
	$scope.changeClass = function(){
		if($scope.iClass === false){
			$scope.iClass = true;
		}
		else{
			$scope.iClass = false;
		}
	}

	$rootScope.showInvoicedLoads = function(){
		$scope.loadItems();	
	}
	//-------------- Pagination functions ------------------------- 

	//Set data for pagiantion
	$scope.itemsPerPage     = 20;
	$scope.perPageOptions   = [10, 20, 50];
	$scope.currentPage      = 1,
	//$scope.total            = 40;
	$scope.lastSortedColumn = '';
    $scope.lastSortType 	= '';
    $scope.loadItems = function(){
        $scope.loadNextPage(($scope.currentPage - 1),$scope.searchFilter,$scope.lastSortedColumn,$scope.lastSortType);
    };

    $scope.pageChanged = function(newPage){
        $scope.currentPage = newPage;
        $scope.loadItems();
    };

    $scope.loadNextPage = function(pageNumber,search,sortColumn,sortType){
    	$scope.autoFetchLoads = true;
        dataFactory.httpRequest(URL+'/Filteredbillings/getRecords/'+$scope.listTypeParameter,'Post',{} ,{ pageNo:pageNumber, itemsPerPage:$scope.itemsPerPage,searchQuery: search, sortColumn:sortColumn, sortType:sortType,startDate: $scope.dateRangeSelector.startDate, endDate:$scope.dateRangeSelector.endDate,filterArgs:$scope.filterArgs }).then(function(data){
        	$scope.autoFetchLoads = false;
        	$rootScope.filteredBillingLoads = data.data;
			$scope.total            = data.total;
            isSending = false;
            if(Object.keys($rootScope.filteredBillingLoads).length <= 0){
				$scope.haveRecords = true;
			}else{
				$scope.haveRecords = false;
			}
        	return data;
		});
		//canceler.resolve();  // Aborts the $http request if it isn't finished.
    };

    $scope.callSearchFilter = function(query){
    	$scope.loadNextPage(($scope.currentPage - 1), query, $scope.lastSortedColumn,$scope.lastSortType);
    };

    $scope.sortCustom = function(sortColumn,type) {
		type = type == "ASC" ? "DESC" : "ASC";
		$scope.lastSortedColumn = sortColumn;
    	$scope.lastSortType 	= type;
    	$scope.PointOfContactPhoneSortType = ''; $scope.equipment_optionsSortType = ''; $scope.LoadTypeSortType = ''; $scope.PickupDateSortType = ''; $scope.DeliveryDateSortType = ''; $scope.OriginCitySortType = ''; $scope.OriginStateSortType = ''; $scope.DestinationCitySortType = ''; $scope.DestinationStateSortType = ''; $scope.driverNameSortType = ''; $scope.invoiceNoSortType = ''; $scope.PaymentAmountSortType = ''; $scope.MileageSortType = '';$scope.deadmilesSortType = ''; $scope.LengthSortType = ''; $scope.LengthSortType = ''; $scope.WeightSortType = ''; $scope.companyNameSortType = ''; $scope.load_sourceSortType = ''; $scope.JobStatusSortType = ''; $scope.RpmSortType = '';

    	switch(sortColumn){
    		case 'id' 					: $scope.idSortType = type;  break;
    		case 'PointOfContactPhone'	: $scope.PointOfContactPhoneSortType = type; break;
    		case 'equipment_options'	: $scope.equipment_optionsSortType = type; break;
    		case 'LoadType'			 	: $scope.LoadTypeSortType = type; break;
    		case 'PickupDate'			: $scope.PickupDateSortType = type; break;
    		case 'DeliveryDate'			: $scope.DeliveryDateSortType = type; break;
    		case 'OriginCity'			: $scope.OriginCitySortType = type; break;
    		case 'OriginState'			: $scope.OriginStateSortType = type; break;
    		case 'DestinationCity'		: $scope.DestinationCitySortType = type; break;
    		case 'DestinationState'		: $scope.DestinationStateSortType = type; break;
    		case 'driverName'			: $scope.driverNameSortType = type; break;
    		case 'invoiceNo'			: $scope.invoiceNoSortType = type; break;
    		case 'PaymentAmount'		: $scope.PaymentAmountSortType = type; break;
    		case 'Mileage'			 	: $scope.MileageSortType = type; break;
    		case 'rpm'			 		: $scope.RpmSortType  = type; break;
    		case 'deadmiles'			: $scope.deadmilesSortType = type; break;
    		case 'Length'			 	: $scope.LengthSortType = type; break;
    		case 'Weight'			 	: $scope.WeightSortType = type; break;
    		case 'TruckCompanyName'		: $scope.TruckCompanyNameSortType = type; break;
    		case 'load_source'			: $scope.load_sourceSortType = type; break;
    		case 'JobStatus'			: $scope.JobStatusSortType = type; break;
    	}

    	
		$scope.loadNextPage(($scope.currentPage - 1), $scope.searchFilter, sortColumn, type);
    }

    $scope.toggleRow = function($event,index){
		angular.element("#hblock"+index).slideToggle();
		angular.element($event.target).toggleClass("minus-1");
	}
	//-------------- Pagination functions -------------------------


	 
}]);

