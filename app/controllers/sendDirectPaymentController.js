app.controller('sendDirectPaymentController', ["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "getDirectBillingData","$compile","$filter","$log",'Sample', 'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window','$sce','$timeout',function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, getDirectBillingData , $compile,$filter,$log,utils, Sample, mouseOffset, debounce, moment,$q,$window,$sce,$timeout){
	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	//----------drop-down ---------------------------
    $rootScope.extraStops = '';
    $scope.duplicatejobstatus='';
    window.scrollTo(0,0);
	
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

	
	$scope.callDynamicFn = false;
	$rootScope.Message = '';
	$scope.siteURL = URL;
	$scope.mindate = new Date();
	
	$rootScope.showHeader = true;
	$rootScope.SendLoads = [];
	$scope.saveLoadsData = [];
	$rootScope.Docs = [];
	
	$scope.sentPaymentCount = '';
	$scope.flaggedPaymentCount = '';
	$scope.showPaymentSidebarLiSelected = 'inbox';

	$scope.deletedRowIndex = '';
	$scope.idSelection = [];
	$scope.noRecordFoundMessage = $rootScope.languageArray.noRecordFound;

	$scope.sentPaymentCount = getDirectBillingData.sentPaymentCount;
	$scope.flaggedPaymentCount = getDirectBillingData.flaggedPaymentCount;
	$rootScope.SendLoads = getDirectBillingData.loads;
	var targetVariable = 16;
	var disableAnotherSorting = 0;
	angular.forEach($rootScope.SendLoads,function(value,key) {
		if ( value.flag == 1 ) {
			$scope.idSelection.push(value.id);
		}
	});

	$scope.billPaymentMode = getDirectBillingData.billPaymentType;

	$rootScope.readyToSendPaymentCount = $rootScope.SendLoads.length;	
	
	$rootScope.saveTypeLoad = 'sendForPayment';    			// setting the save type for dynamic changing the listing on routes
	
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
	$scope.getProfitPercent = function(profitAmount1, totalPayment1){
		var profitPercent= ((profitAmount1 / totalPayment1) * 100).toFixed(2);	
		return profitPercent;
	};
	
	$rootScope.notHitPaymentLoadRequest = 1;		// hitting request on send for payment page if ticket is closed
	
	/** Changing driver in dropdown */
	$scope.onSelectVehicleCallback = function ( key,value) {
		$scope.selectedDriver = value.label;
		$scope.search_label = value.key;
	}
	
	$rootScope.nonewRequest = false;
	
	/*************Fetching load Details start**********************/
	
	/**Clicking on load detail changes url withour reload state*/
	$scope.clickMatchLoadDetail = function(truckstopId,loadId, deadmile,calPayment,totalCost,orignPickDate,vehicleID, billingParameter) {
		if ( loadId == '' && loadId == undefined ) 
			loadId = '';
		
		if ( billingParameter != undefined && billingParameter == 'sendForPayment' ) {
			$rootScope.saveTypeLoad = billingParameter;
			$scope.sameLoadId = loadId;				// setting value of primary load id while opening the ticket to use load id when ticket is closed
		}
			
		$rootScope.globalVehicleId = vehicleID;
		encodedUrl = btoa(truckstopId+'-'+loadId+'-'+deadmile+'-'+calPayment+'-'+totalCost+'-'+orignPickDate+'-'+vehicleID);
		$state.go('search.popup', {staticId:2,encodedurl:encodedUrl}, {notify: false,reloadOnSearch: false});
	}	
	
	$scope.hideLoadDetailPopup = function() {
		$rootScope.firstTimeClick = true;
		if ( $rootScope.statesArr[0] != '' && $rootScope.statesArr[0] != undefined && $rootScope.saveTypeLoad == 'sendForPayment' ) { //show same load with refrehed info of open ticket while closing popup
			var tempIndex = $rootScope.selectedIndex;			// storing index temproraily for showing selected row highlighted
			console.log($rootScope.notHitPaymentLoadRequest);	
			if ( $rootScope.notHitPaymentLoadRequest != undefined && $rootScope.notHitPaymentLoadRequest == 0 ) {		//not to hit load request if invoice is deleted for that load
			} else {
				$scope.showLoad($scope.sameLoadId);
				$rootScope.selectedIndex = tempIndex;
			}
			$state.go('sendForPayment', {}, {notify: false,reload: false});
		} else {
			$state.go('sendForPayment', {}, {notify: false,reload: false});
		}
	}
	
	/*Changing url on outer click of popup*/
	$(document).off('click','#edit-fetched-load').on("click",'#edit-fetched-load', function(event){
		var $trigger1 = $(".popup-container-wid1");
		if($trigger1 !== event.target && !$trigger1.has(event.target).length){
			if ( $rootScope.statesArr[0] != '' && $rootScope.statesArr[0] != undefined && $rootScope.saveTypeLoad == 'sendForPayment' ) { //show same load with refrehed info of open ticket while closing popup
				var tempIndex = $rootScope.selectedIndex;			// storing index temproraily for showing selected row highlighted
				if ( $rootScope.notHitPaymentLoadRequest != undefined && $rootScope.notHitPaymentLoadRequest == 0 ) {	//not to hit load request if invoice is deleted for that load
				
				} else {	
					$scope.showLoad($scope.sameLoadId);
					$rootScope.selectedIndex = tempIndex;
				}
				$state.go('sendForPayment', {}, {notify: false,reload: false});
			}  else {
				$state.go('sendForPayment', {}, {notify: false,reload: false});
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
	
	/**Send for payment page start*/
			    		
		/**
		 * Sending bundle document to triumph request 
		 **/
		$scope.sendDocsForPayment = function() {
			if ( $scope.idSelection.length > 0 ) {
				$rootScope.showErrorMessage = false;
				$scope.autoFetchLoads = true;
				dataFactory.httpRequest(URL+'/billings/creatingSchedule','POST',{},{selectedIds : $scope.idSelection }).then(function(data){
					if ( data.success == true ) {
						$rootScope.SendLoads = data.loadsInfo.loads;
						$scope.sentPaymentCount = data.loadsInfo.sentPaymentCount;
						$scope.flaggedPaymentCount = data.loadsInfo.flaggedPaymentCount;
						$rootScope.readyToSendPaymentCount = data.loadsInfo.loads.length;
						
						if ( data.errorMessage != '' ) {
							$rootScope.showErrorMessage = true;
							$rootScope.ErrMessage = data.errorMessage;
						}
						$rootScope.noLoadSelected = true;
						$scope.idSelection = [];
					}
					$scope.autoFetchLoads = false;
				});
			} else {
				$rootScope.showErrorMessage = true;
				$scope.ErrMessage = $rootScope.languageArray.selectAtleastOneLoadErr;
			}		
		}
		
		/**
		 * Flag or unflag the load for payment
		 */
		
		$scope.flagUnflagLoad = function(flagStatus, loadId) {
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/billings/flagLoad/'+flagStatus+'/'+loadId+'/'+$rootScope.srcPage).then(function(data){
				if ( flagStatus == 'flag' ) {
					$scope.showLoadDetail.flag = 1;
				} else  {
					$scope.showLoadDetail.flag = 0;
				}
				
				if ( $scope.showPaymentSidebarLiSelected == 'inbox' ) {
					$rootScope.SendLoads = data.loadsInfo.loads;
				} else {
					$rootScope.SendLoads = data.loadsInfo.flaggedLoads;	
					$rootScope.noLoadSelected = true;
				}
				$scope.sentPaymentCount = data.loadsInfo.sentPaymentCount;
				$scope.flaggedPaymentCount = data.loadsInfo.flaggedPaymentCount;
				$rootScope.readyToSendPaymentCount = data.loadsInfo.loads.length;
				
				if ( $rootScope.SendLoads.length == 0 ) 
					$rootScope.noLoadSelected = true;
					
				var idx = $scope.idSelection.indexOf(loadId);
				if (idx > -1) {
					$scope.idSelection.splice(idx, 1);
				} else {
					$scope.idSelection.push(loadId);
				}
				$scope.autoFetchLoads = false;
			});
		}	
		
		/**
		 * Fetching loads already sent for payment
		 */
		 
		$scope.showAlreadySentPaymentRecords = function(paymentType) {
			$scope.showPaymentSidebarLiSelected = 'sent';
			$rootScope.noLoadSelected = true;
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/billings/fetchSentPaymentRecords/'+paymentType).then(function(data){
				$rootScope.SendLoads = data.loads;
				$scope.autoFetchLoads = false;
			});
		}
		
		/**
		 * Fetching loads whose flag is set
		 */
		
		$scope.showFlaggedPaymentRecords = function(paymentType) {
			$scope.showPaymentSidebarLiSelected = 'flagged';
			$rootScope.noLoadSelected = true;
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/billings/fetchFlaggedPaymentRecords/'+paymentType).then(function(data){
				$rootScope.SendLoads = data.loads;
				$scope.autoFetchLoads = false;
			});
		} 
		
		/**
		 * Fetching loads sending for payment
		 */
		 
		$rootScope.showSendPaymentsLoads = function(paymentType) {
			$scope.showPaymentSidebarLiSelected = 'inbox';
			$rootScope.noLoadSelected = true;
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/billings/sendForPayment/'+paymentType).then(function(data){
				$rootScope.SendLoads = data.loads;
				$scope.autoFetchLoads = false;
			});
		}
		 
		$rootScope.noLoadSelected = true;
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

