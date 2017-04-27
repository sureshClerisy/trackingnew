app.controller('sendPaymentController', ["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "getSendBillingData","$compile","$filter","$log",'Sample', 'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window','$sce','$timeout', function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, getSendBillingData , $compile,$filter,$log,utils, Sample, mouseOffset, debounce, moment,$q,$window,$sce,$timeout){
	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	//----------drop-down ---------------------------
    $rootScope.extraStops = '';
    $scope.duplicatejobstatus='';
    window.scrollTo(0,0);
	
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

	var sendPym = this;
	
	$scope.callDynamicFn = false;
	$rootScope.Message = '';
		
	$rootScope.showHeader = true;
	$rootScope.SendLoads = [];
	$rootScope.Docs = [];
	
	$rootScope.sentPaymentCount 		= 0;
	$rootScope.factoredPaymentCount 	= 0;
	$rootScope.readyToSendPaymentCount  = 0;
	
	$scope.deletedRowIndex = '';
	$scope.idSelection = [];
	$scope.noRecordFoundMessage = $rootScope.languageArray.noRecordFound;
	
	$rootScope.sentPaymentCount 	   = getSendBillingData.sentPaymentCount;
	$rootScope.factoredPaymentCount    = getSendBillingData.factoredPaymentCount;
	$rootScope.readyToSendPaymentCount = getSendBillingData.loadsForPaymentCount;
	
	if ( getSendBillingData.sendPaymentContType == 'sendForPayment') {
		$rootScope.SendLoads = getSendBillingData.loads;		
		$scope.showPaymentSidebarLiSelected = 'inbox';

	} else if ( getSendBillingData.sendPaymentContType == 'factoredLoads') {
		$scope.showPaymentSidebarLiSelected = 'factoring';
		$rootScope.SendLoads 	= getSendBillingData.factoredLoads;
		$scope.idSelection = [];
		if ( getSendBillingData.triumphIdsArray.length > 0 ) {
		 	$scope.idSelection = getSendBillingData.triumphIdsArray;
		}
	} 

	$rootScope.saveTypeLoad = 'sendForPayment';    			// setting the save type for dynamic changing the listing on routes
	
	
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
	
	
	/**Send for payment page start*/
			    		
		/**
		 * Sending bundle document to triumph request 
		 **/
		sendPym.sendDocsForPayment = function() {
			if ( $scope.idSelection.length > 0 ) {
				$rootScope.showErrorMessage = false;
				$scope.autoFetchLoads = true;
				dataFactory.httpRequest(URL+'/billings/sendPayment','POST',{},{selectedIds : $scope.idSelection }).then(function(data){
					$scope.autoFetchLoads = false;
					if ( data.success == true ) {
						$rootScope.SendLoads 				 = data.loadsInfo.factoredLoads;
						$rootScope.sentPaymentCount			 =  data.loadsInfo.sentPaymentCount;
						$rootScope.factoredPaymentCount 	 =  data.loadsInfo.factoredPaymentCount;
						$rootScope.readyToSendPaymentCount   =  data.loadsInfo.loadsForPaymentCount;
						
						if ( data.errorMessage != '' ) {
							$rootScope.showErrorMessage = true;
							$rootScope.ErrMessage = data.errorMessage;
						}

						$rootScope.noLoadSelected = true;
						$scope.idSelection = [];
					}
					
				});
			} else {
				$rootScope.showErrorMessage = true;
				$scope.ErrMessage = $rootScope.languageArray.selectAtleastOneLoadErr;
			}		
		}
		
		/**
		 * Flag or unflag the load for payment
		 */
		
		$scope.flagUnflagLoad = function(flagStatus, loadId, paymentType) {			
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/billings/flagLoad/'+flagStatus+'/'+loadId+'/'+paymentType+'/'+$rootScope.srcPage).then(function(data){
				if ( flagStatus == 'flag' ) {
					$scope.showLoadDetail.flag = 1;
				} else  {
					$scope.showLoadDetail.flag = 0;
				}
				
				if ( $scope.showPaymentSidebarLiSelected == 'inbox' ) {
					$rootScope.SendLoads = data.loadsInfo.loads;
				} else if ( $scope.showPaymentSidebarLiSelected == 'factoring' ) {
					$rootScope.SendLoads = data.loadsInfo.factoredLoads;
					$scope.idSelection = [];
					if ( data.loadsInfo.triumphIdsArray.length > 0 ) {
					 	$scope.idSelection = data.loadsInfo.triumphIdsArray;
					}	
					$rootScope.noLoadSelected = true;
				}
				$rootScope.sentPaymentCount 		= data.loadsInfo.sentPaymentCount;
				$rootScope.factoredPaymentCount 	= data.loadsInfo.factoredPaymentCount;
				$rootScope.readyToSendPaymentCount  = data.loadsInfo.loads.length;
				
				if ( $rootScope.SendLoads.length == 0 ) 
					$rootScope.noLoadSelected = true;
					
				$scope.autoFetchLoads = false;
			});
		}	
		
		/**
		* process flagged triumph and manual payments
		*/

		sendPym.processPaymentQueue = function() {
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/billings/skipAcl_setFinalFlagLoads').then(function(data){
				$scope.autoFetchLoads = false;
				$rootScope.SendLoads 		= data.loads;
				$rootScope.sentPaymentCount = data.sentPaymentCount;
				$rootScope.factoredPaymentCount = data.factoredPaymentCount;
				$rootScope.readyToSendPaymentCount = data.loads.length;
				$rootScope.noLoadSelected = true;
			});
		}

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
		 * Fetching loads whose flag is set
		 *
		
		$scope.showFactoredPaymentRecords = function() {
			$scope.showPaymentSidebarLiSelected = 'factoring';
			$rootScope.noLoadSelected = true;
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/billings/fetchFactoredPaymentRecords').then(function(data){
				$rootScope.SendLoads = data.factoredLoads;
				$scope.idSelection = [];
				if ( data.triumphIdsArray.length > 0 ) {
				 	$scope.idSelection = data.triumphIdsArray;
				}
				$scope.autoFetchLoads = false;
			});
		} 
		
		/**
		 * Fetching loads sending for payment
		 */
		 
		$rootScope.showSendPaymentsLoads = function() {
			$scope.showPaymentSidebarLiSelected = 'inbox';
			$rootScope.noLoadSelected = true;
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/billings/sendForPayment').then(function(data){
				$rootScope.SendLoads = data.loads;
				$scope.autoFetchLoads = false;
			});
		}
		
		$rootScope.noLoadSelected = true;
		   var w = angular.element($window);
		   
	        $scope.showLoad = function(id, index) {
				$rootScope.selectedIndex = index;
				dataFactory.httpRequest(URL+'/billings/skipAcl_getLoadDetail/'+id).then(function(data){
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

		$scope.exportSendPayment = function (type) {
			dataFactory.httpRequest(URL+'/billings/exportSendPayment/'+type).then(function(data){
				$rootScope.donwloadExcelFile(data.fileName);
			});
		}
}]);
