app.controller('outboxPaymentController', ["dataFactory","$scope","$rootScope", "$state","$location", "getOutboxBillingData","$compile","$filter",'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window','$sce','$timeout', function(dataFactory,$scope,$rootScope ,$state, $location , getOutboxBillingData , $compile,$filter,utils, mouseOffset, debounce, moment,$q,$window,$sce,$timeout){
	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	window.scrollTo(0,0);
	var out = this;

	$rootScope.Message = '';
	$rootScope.showHeader = true;
	$rootScope.outboxLoads = [];
	$scope.deletedRowIndex = '';
	$scope.noRecordFoundMessage = $rootScope.languageArray.noRecordFound;
	
	$rootScope.sentPaymentCount 	   = (getOutboxBillingData.sentPaymentCount != undefined ) ? getOutboxBillingData.sentPaymentCount : 0;
	$rootScope.factoredPaymentCount    = (getOutboxBillingData.factoredPaymentCount != undefined ) ? getOutboxBillingData.factoredPaymentCount : 0;
	$rootScope.readyToSendPaymentCount = (getOutboxBillingData.loadsForPaymentCount != undefined ) ? getOutboxBillingData.loadsForPaymentCount : 0;
	
	$scope.showPaymentSidebarLiSelected = 'outbox';
	$rootScope.outboxLoads 	= getOutboxBillingData.loads;
	out.total 				= getOutboxBillingData.total;
	out.itemsPerPage 		= $rootScope.globalItemsPerPage;
	$scope.currentPage  	= 1;
	out.lastSortedColumn 	= 'DeliveryDate';
    out.lastSortType 		= 'Desc';
    out.DeliveryDateSortType = 'Desc'

	$rootScope.saveTypeLoad = 'outboxLoads';    			// setting the save type for dynamic changing the listing on routes
	out.pageChanged = function(newPage){
        $scope.currentPage = newPage;
        out.loadOutboxItems(($scope.currentPage - 1),out.searchFilter, out.lastSortedColumn, out.lastSortType);
    };

    out.loadOutboxItems = function(pageNumber,search,sortColumn,sortType) {
    	$scope.autoFetchLoads = true;
        dataFactory.httpRequest(URL+'/Billings/fetchSentPaymentRecords','Post',{} ,{ pageNo:pageNumber, itemsPerPage: out.itemsPerPage,searchQuery: search, sortColumn:sortColumn, sortType:sortType }).then(function(data){
        	$scope.autoFetchLoads = false;
        	$rootScope.outboxLoads = data.loads;
			out.total        	  = data.total;
        });
    };

    /**
    * Searching the loads from db
    */

    out.callSearchFilter = function(query){
    	out.loadOutboxItems(($scope.currentPage - 1), query, out.lastSortedColumn,out.lastSortType);
    };

    /**
    * clicking on sort icon 
    */

    out.sortCustom = function(sortColumn,type) {
		type = type == "ASC" ? "DESC" : "ASC";
		out.lastSortedColumn = sortColumn;
    	out.lastSortType 	= type;
    	out.LoadTypeSortType = ''; out.PickupDateSortType = ''; out.DeliveryDateSortType = ''; out.OriginCitySortType = ''; 
    	out.OriginStateSortType = ''; out.DestinationCitySortType = ''; out.DestinationStateSortType = ''; out.driverNameSortType = '';
    	out.invoiceNoSortType = ''; out.PaymentAmountSortType = ''; out.MileageSortType = ''; out.deadmilesSortType = ''; out.LengthSortType = '';
    	out.companyNameSortType = ''; out.load_sourceSortType = ''; out.JobStatusSortType = ''; out.RpmSortType = '';

    	switch(sortColumn){
    		case 'id' 					: out.idSortType = type;  break;
    		case 'LoadType'			 	: out.LoadTypeSortType = type; break;
    		case 'PickupDate'			: out.PickupDateSortType = type; break;
    		case 'DeliveryDate'			: out.DeliveryDateSortType = type; break;
    		case 'OriginCity'			: out.OriginCitySortType = type; break;
    		case 'OriginState'			: out.OriginStateSortType = type; break;
    		case 'DestinationCity'		: out.DestinationCitySortType = type; break;
    		case 'DestinationState'		: out.DestinationStateSortType = type; break;
    		case 'driverName'			: out.driverNameSortType = type; break;
    		case 'invoiceNo'			: out.invoiceNoSortType = type; break;
    		case 'PaymentAmount'		: out.PaymentAmountSortType = type; break;
    		case 'Mileage'			 	: out.MileageSortType = type; break;
    		case 'rpm'			 		: out.RpmSortType = type; break;
    		case 'deadmiles'			: out.deadmilesSortType = type; break;
    		case 'TruckCompanyName'		: out.TruckCompanyNameSortType = type; break;
    		case 'billType'			    : out.load_sourceSortType = type; break;
    		case 'JobStatus'			: out.JobStatusSortType = type; break;
    	}
    	
		out.loadOutboxItems(($scope.currentPage - 1), out.searchFilter, sortColumn, type);
    }

    $scope.toggleRow = function($event,index){
		angular.element("#hblock"+index).slideToggle();
		angular.element($event.target).toggleClass("minus-1");
	}

	$scope.exportSendPayment = function (type) {
		search = angular.element('.srpSearch1 input').val();

		dataFactory.httpRequest(URL+'/billings/exportSendPayment/'+type,'Post',{},{searchQuery: search}).then(function(data){
			$rootScope.donwloadExcelFile(data.fileName);
		});
	}
	/*************Fetching load Details start**********************/
	
	$scope.clickMatchLoadDetail = function(truckstopId,loadId, deadmile,calPayment,totalCost,orignPickDate,vehicleID, index) {
		if ( loadId == '' && loadId == undefined ) 
			loadId = '';
		
		$rootScope.globalListingIndex = index;			
		$rootScope.globalVehicleId = vehicleID;
		encodedUrl = btoa(truckstopId+'-'+loadId+'-'+deadmile+'-'+calPayment+'-'+totalCost+'-'+orignPickDate+'-'+vehicleID);
		$state.go('search.popup', {staticId:2,encodedurl:encodedUrl}, {notify: false,reloadOnSearch: false});
	}	
	
	$scope.hideLoadDetailPopup = function() {
		$rootScope.firstTimeClick = true;
		$state.go('outbox', {}, {notify: false,reload: false});
	}
	
	/*Changing url on outer click of popup*/
	$(document).off('click','#edit-fetched-load').on("click",'#edit-fetched-load', function(event){
		var $trigger1 = $(".popup-container-wid1");
		if($trigger1 !== event.target && !$trigger1.has(event.target).length){
			$state.go('outbox', {}, {notify: false,reload: false});
		}
	});
	

	/***********Load Details ends******************/
	
		/**
		 * Fetching loads already sent for payment
		 *
		 
		$scope.showAlreadySentPaymentRecords = function() {
			$scope.showPaymentSidebarLiSelected = 'outbox';
			$rootScope.noLoadSelected = true;
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/billings/fetchSentPaymentRecords').then(function(data){
				$rootScope.SendLoads = data.loads;
				$scope.autoFetchLoads = false;
			});
		}
		
		
		/**
		 * Fetching loads sending for payment
		 */
		 
		

		   var w = angular.element($window);
		   
	        $scope.showLoad = function(id, index) {
				$rootScope.selectedIndex = index;
				dataFactory.httpRequest(URL+'/billings/getLoadDetail/'+id).then(function(data){
					$scope.showLoadDetail = data;
					$rootScope.noLoadSelected = false;
					
					//~ $timeout(function() {
						//~ if ($.Pages.isVisibleSm() || $.Pages.isVisibleXs()) {
							//~ $('.split-list').toggleClass('slideLeft');
						//~ }
					//~ },200);
				});
			};
            
		w.bind('resize', function() {
			if (w.width() <= 1024) {
				$('.secondary-sidebar').hide();

			} else {
				$('.split-list').length && $('.split-list').removeClass('slideLeft');
				$('.secondary-sidebar').show();
			}
		});
}]);
