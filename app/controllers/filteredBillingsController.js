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

	$rootScope.editLoads = true;
	$rootScope.matchingTrucks = false;
	$rootScope.showMaps = false;
	$rootScope.showhighlighted = 'loadDetail';
	$scope.iClass = false;
	$rootScope.save_cancel_div  = false;
	$rootScope.save_edit_div = true;
	$rootScope.showFormClass = true;
	$scope.showPlusMinus = true;
	$scope.canDocsShow = true;
	$scope.callDynamicFn = false;
	$rootScope.Message = '';
	$scope.siteURL = URL;
	$scope.showSearchButtonText = false;
	$scope.newSearchButtonShow = false;
	$scope.showMatchingTrucks = false;
	$scope.showNotCalculatedRecords = false;
	$scope.showStartOver = false;
	$scope.showRouteOnMap = false;
	$scope.showGantt = false;
	$scope.mindate = new Date();
	
	$rootScope.showHeader = true;
	$rootScope.fetchnewsearch = false;
	$rootScope.newIterationButtonShow = false;
	$rootScope.showtdvalue = false;
	$scope.iterationMultiStateCheck = false;
	$scope.startOverSpin = false;
	$scope.continueSpin = false;
	$scope.search_label_show = false;
	
	$rootScope.billingLoads = [];
	$scope.saveLoadsData = [];
	$rootScope.Docs = [];
	
	$scope.deletedRowIndex = '';
	$scope.idSelection = [];
	$scope.noRecordFoundMessage = $rootScope.languageArray.noRecordFound;
	

	if ( getBillingData.billType == 'billing') {
		$scope.filterArgs = getBillingData.filterArgs;
		$scope.firstParam = getBillingData.filterArgs.firstParam;
		$rootScope.billingLoads = getBillingData.loads;
		$scope.total            = getBillingData.total;
	} 
	$scope.DeliveryDateSortType = "DESC"; 					// intially setting delivery date column to desc		
	$scope.showReadyForInvoiceLoads = true;					// showing button to show only loads which are ready for generating invoice
	$rootScope.saveTypeLoad = 'billingLoads';    			// setting the save type for dynamic changing the listing on routes
	
	$scope.newRowsArray = [];
	$scope.newDriversArray = [];
	$scope.iterationLeftBar = [];
	$scope.iterationDelete = [];
	$scope.firstStartOrigin = '';
	$scope.firstStartPickup = '';
	$scope.showFirstLi = false;
	$scope.previousDate = $filter('date')(new Date(), 'yyyy-MM-dd');
	$scope.multiData = false;
	$scope.base_image = new Image();
	$scope.base_image2 = new Image();
	$scope.base_image.src = 'pages/img/green-point.png';
	$scope.base_image2.src = 'pages/img/red-point.png';
	
    $scope.totalMiles = 0;
	var totalMilesSum = 0;
	var totalDeadMilesSum = 0;
	var totalWorkingHours = 0;
	var totalProfitPercent = 0;
	var totalCost = 0;
	var totalPayment = 0;
	
	$scope.getNextDateForIt = 1;

	if( $cookies.getObject('_gDateRange') ){
        $scope.dateRangeSelector = $cookies.getObject('_gDateRange');
        if($scope.dateRangeSelector.startDate == null || $scope.dateRangeSelector.endDate == null){
        	$scope.dateRangeSelector = {};
        }
    }else{
        $scope.dateRangeSelector = {startDate: moment().subtract(29, 'days'), endDate: moment()};    
        $cookies.putObject('_gDateRange', $scope.dateRangeSelector);  
    }


	$scope.opts = {
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
                $scope.loadItems();
            },
            'cancel.daterangepicker': function(ev, picker) {  
                $scope.dateRangeSelector = {};
                $cookies.putObject('_gDateRange', {startDate:null,endDate:null});    
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
	
	/** Changing driver in dropdown */
	$scope.onSelectVehicleCallback = function ( key,value) {
		$scope.selectedDriver = value.label;
		$scope.search_label = value.key;
	}
	
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
			$state.go('billings', {'key':$scope.firstParam,q:url}, {notify: false,reload: false});
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
				$state.go($rootScope.statesArr[0], {'key':$scope.firstParam,q:url}, {notify: false,reload: false});
			} else {
				$state.go('billings', {'key':$scope.firstParam,q:url}, {notify: false,reload: false});
			}
		}
	});
	
	$scope.$on('ngRepeatFinished', function(ngRepeatFinishedEvent) {
	    if ( $scope.hideTexts == true ) {
			angular.element($('.actuals-class')).hide();
		} else {
			angular.element($('.actuals-class')).show();
		}
		angular.element($('.actuals-class')).hide();
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
    	/*var canceller, isSending = false;
    	if(isSending) {
            canceller.resolve();
        }
        isSending = true;
        canceller = $q.defer();*/
		//alert(pageNumber + ' - ' + search + ' - ' + sortColumn + ' - ' + sortType);
        dataFactory.httpRequest(URL+'/Filteredbillings/getRecords/'+$scope.listTypeParameter,'Post',{} ,{ pageNo:pageNumber, itemsPerPage:$scope.itemsPerPage,searchQuery: search, sortColumn:sortColumn, sortType:sortType,startDate: $scope.dateRangeSelector.startDate, endDate:$scope.dateRangeSelector.endDate,filterArgs:$scope.filterArgs }).then(function(data){
        	$rootScope.billingLoads = data.data;
			$scope.total            = data.total;
            isSending = false;
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
    	$scope.PointOfContactPhoneSortType = ''; $scope.equipment_optionsSortType = ''; $scope.LoadTypeSortType = ''; $scope.PickupDateSortType = ''; $scope.DeliveryDateSortType = ''; $scope.OriginCitySortType = ''; $scope.OriginStateSortType = ''; $scope.DestinationCitySortType = ''; $scope.DestinationStateSortType = ''; $scope.driverNameSortType = ''; $scope.invoiceNoSortType = ''; $scope.PaymentAmountSortType = ''; $scope.MileageSortType = '';$scope.deadmilesSortType = ''; $scope.LengthSortType = ''; $scope.LengthSortType = ''; $scope.WeightSortType = ''; $scope.companyNameSortType = ''; $scope.load_sourceSortType = ''; $scope.JobStatusSortType = '';

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
    		case 'Mileage'			 	: $scope.MileageSortType = type; break;
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


	
	/**
	 * Fetching only loads ready for generation invoic or all loads
	 */
	
	$scope.fetchBillableLoads = function( parameter ) {
		$scope.autoFetchLoads = true;
		$scope.currentPage      = 1;
		$scope.listTypeParameter = parameter;
		dataFactory.httpRequest(URL+'/Flteredbillings/index/'+parameter).then(function(data){
			$rootScope.billingLoads = data.loads;
			$scope.total            = data.total;
			
			if ( parameter == 'invoice' ) {
				$scope.showReadyForInvoiceLoads = false;
				$rootScope.saveTypeLoad = 'readyForInvoice';    			// setting the save type for dynamic changing the listing on routes
			} else {
				$scope.showReadyForInvoiceLoads = true;
				$rootScope.saveTypeLoad = 'billingLoads';    			// setting the save type for dynamic changing the listing on routes
			}
			$scope.PointOfContactPhoneSortType = ''; $scope.equipment_optionsSortType = ''; $scope.LoadTypeSortType = ''; $scope.PickupDateSortType = ''; $scope.DeliveryDateSortType = ''; $scope.OriginCitySortType = ''; $scope.OriginStateSortType = ''; $scope.DestinationCitySortType = ''; $scope.DestinationStateSortType = ''; $scope.driverNameSortType = ''; $scope.invoiceNoSortType = ''; $scope.PaymentAmountSortType = ''; $scope.MileageSortType = '';$scope.deadmilesSortType = ''; $scope.LengthSortType = ''; $scope.LengthSortType = ''; $scope.WeightSortType = ''; $scope.companyNameSortType = ''; $scope.load_sourceSortType = ''; $scope.JobStatusSortType = '';
			$scope.DeliveryDateSortType = 'DESC';
			
			$scope.autoFetchLoads = false;
		});
	}	 
}]);

app.filter('capitalize', function() {
    return function(input) {
      return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
    }
});

app.directive('timepicker', function() {
    return {
        restrict: 'A',
        require : 'ngModel',
        link: function(scope, elem, attrs, ngModel) {
	
				$.fn.timepicker.defaults = {
					defaultTime: ngModel.$viewValue,
					disableFocus: false,
					disableMousewheel: false,
					isOpen: false,
					minuteStep: 15,
					modalBackdrop: false,
					orientation: { x: 'auto', y: 'auto'},
					secondStep: 15,
					showSeconds: false,
					showInputs: true,
					showMeridian: true,
					template: 'dropdown',
					appendWidgetTo: 'body',
					showWidgetOnAddonClick: true
				};
				
            $(elem).timepicker().on('show.timepicker', function(e) {
				$(elem).timepicker('setTime', ngModel.$viewValue);
				var widget = $('.bootstrap-timepicker-widget');
                widget.find('.glyphicon-chevron-up').removeClass().addClass('pg-arrow_maximize');
                widget.find('.glyphicon-chevron-down').removeClass().addClass('pg-arrow_minimize');
            });
        }
    }
});

