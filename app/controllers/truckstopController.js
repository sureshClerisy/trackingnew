app.controller('truckstopController', function( dataFactory,$scope,$sce,$http ,$rootScope ,$state, $location , $cookies, $stateParams, $localStorage, getAllTrucksStopData ,  $compile,$timeout){
	if($rootScope.loggedInUser == false)
		$location.path('login');
		
	$('#headerFixed').addClass('headerFixed');
	
	$scope.loadSize = [{ 'loadvalue' : 'Full','loadkey' : 'Full'}];
	 $scope.cityOptions = {
            highlight: true
        };
    //----------drop-down ---------------------------
    $scope.includeDropzone = false;
    $scope.canDocsShow = false;
    $rootScope.extraStops='';
    $scope.duplicatejobstatus='';
	
	$scope.trustAsHtml = function(value) {
    	return $sce.trustAsHtml(value);
    };

    $scope.onSelectCallback = function (item, model){
		$rootScope.form.destination_state = item.code;
		$("#select_state").val(item.code);
	};




	$scope.testSortView = function ($item, $partFrom, $partTo, $indexFrom, $indexTo){

		//$item, $partFrom, $partTo, $indexFrom, $indexTo
		
		// alert('$item = '+$item +' /$index = '+ $index +' /$part = '+$part);
		//$item, $partFrom, $partTo, $indexFrom, $indexTo

		alert('$item = '+$item +' / $partFrom = '+ $partFrom +' / $partTo = '+$partTo+'/ $indexFrom = '+$indexFrom +'/ $indexTo = '+$indexTo);
		
		//$scope.extraStop = [2,1,0];
		
		//Creatting tem variable 
		
		// $extraStopAddress 	= $rootScope.extraStops['extraStopAddress_'+$indexFrom];
		// $extraStopCity   	= $rootScope.extraStops['extraStopCity_'+$indexFrom];
		// $extraStopState  	= $rootScope.extraStops['extraStopState_'+$indexFrom];
		// $extraStopCountry 	= $rootScope.extraStops['extraStopCountry_'+$indexFrom];
		// $extraStopTime 	 	= $rootScope.extraStops['extraStopTime_'+$indexFrom];
		// $extraStopTimeRange 	= $rootScope.extraStops['extraStopTimeRange_'+$indexFrom];
		// $extraStopDate 		= $rootScope.extraStops['extraStopDate_'+$indexFrom];
		// $extraStopZipCode 	= $rootScope.extraStops['extraStopZipCode_'+$indexFrom];
		// $extraStopPhone 		= $rootScope.extraStops['extraStopPhone_'+$indexFrom];
		// $extraStopName 		= $rootScope.extraStops['extraStopName_'+$indexFrom];
		// $extraStopEntity 	= $rootScope.extraStops['extraStopEntity_'+$indexFrom];


		// //Assigning value
		// $rootScope.extraStops['extraStopAddress_'+$indexFrom] 	= $rootScope.extraStops['extraStopAddress_'+$indexTo];
		// $rootScope.extraStops['extraStopCity_'+$indexFrom] 		= $rootScope.extraStops['extraStopCity_'+$indexTo];
		// $rootScope.extraStops['extraStopState_'+$indexFrom] 	= $rootScope.extraStops['extraStopState_'+$indexTo];
		// $rootScope.extraStops['extraStopCountry_'+$indexFrom] 	= $rootScope.extraStops['extraStopCountry_'+$indexTo];
		// $rootScope.extraStops['extraStopTime_'+$indexFrom] 		= $rootScope.extraStops['extraStopTime_'+$indexTo];
		// $rootScope.extraStops['extraStopTimeRange_'+$indexFrom] = $rootScope.extraStops['extraStopTimeRange_'+$indexTo];
		// $rootScope.extraStops['extraStopDate_'+$indexFrom] 		= $rootScope.extraStops['extraStopDate_'+$indexTo];
		// $rootScope.extraStops['extraStopZipCode_'+$indexFrom] 	= $rootScope.extraStops['extraStopZipCode_'+$indexTo];
		// $rootScope.extraStops['extraStopPhone_'+$indexFrom] 	= $rootScope.extraStops['extraStopPhone_'+$indexTo];
		// $rootScope.extraStops['extraStopName_'+$indexFrom] 		= $rootScope.extraStops['extraStopName_'+$indexTo];
		// $rootScope.extraStops['extraStopEntity_'+$indexFrom] 	= $rootScope.extraStops['extraStopEntity_'+$indexTo];
		
		// $rootScope.extraStops['extraStopAddress_'+$indexTo] 	= $extraStopAddress;
		// $rootScope.extraStops['extraStopCity_'+$indexTo] 		= $extraStopCity;
		// $rootScope.extraStops['extraStopState_'+$indexTo] 		= $extraStopState;
		// $rootScope.extraStops['extraStopCountry_'+$indexTo] 	= $extraStopCountry;
		// $rootScope.extraStops['extraStopTime_'+$indexTo] 		= $extraStopTime;
		// $rootScope.extraStops['extraStopTimeRange_'+$indexTo] 	= $extraStopTimeRange;
		// $rootScope.extraStops['extraStopDate_'+$indexTo] 		= $extraStopDate;
		// $rootScope.extraStops['extraStopZipCode_'+$indexTo] 	= $extraStopZipCode;
		// $rootScope.extraStops['extraStopPhone_'+$indexTo] 		= $extraStopPhone;
		// $rootScope.extraStops['extraStopName_'+$indexTo] 		= $extraStopName;
		// $rootScope.extraStops['extraStopEntity_'+$indexTo] 		= $extraStopEntity;
		
		// alert($rootScope.extraStops['extraStopAddress_'+$indexTo]);

		// alert($rootScope.extraStops['extraStopEntity_'+$indexFrom]);

		$scope.$apply();
	};

	/*
	//added listener per @homerjam per https://github.com/angular-ui/ui-select/issues/974
	$scope.$on('uiSelectSort:change', function(event, args) {
	    console.log('uiSelectSort:change', args);
		$scope.multipleDemo.selectedPeople = args.array;
	    if (!$scope.$$phase) { //http://stackoverflow.com/questions/20263118/what-is-phase-in-angularjs
	        $scope.$apply();
	    }
	});*/


    
	$scope.onSelectpostedTimeCallback = function (item, model){
		$rootScope.form.posted_time = item.key;
	    $("#select_postedTime").val(item.key);
	};
    $scope.onSelectloadTimeCallback = function (item, model){
		$rootScope.form.load_type = 'Full';
	    $("#select_loadType").val(item.loadkey);
	};
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

	$scope.parseQuery = function(qstr) {
        var query = {};
        var a = qstr.substr(1).split('&');
        for (var i = 0; i < a.length; i++) {
            var b = a[i].split('=');
            query[decodeURIComponent(b[0])] = decodeURIComponent(b[1] || '');
        }
        return query;
    }
	
    //----------drop-down ---------------------------
  
	$rootScope.Message = '';
	
	$rootScope.showHeader = true;
	$scope.search_deadmile = 'all';
	$scope.newSearch = {};
	
	$scope.states_data = {};
	
	if ( getAllTrucksStopData.loadsData != undefined && getAllTrucksStopData.loadsData.shouldNotMoveFurther != undefined && getAllTrucksStopData.loadsData.shouldNotMoveFurther == false ) {
		return false;
	}
	
	$rootScope.notShowDataTable = false;
	$scope.loadsIdArray = [];
	
	if ( getAllTrucksStopData.loadsData != undefined  && getAllTrucksStopData.loadsData.loadsIdArray != undefined && getAllTrucksStopData.loadsData.loadsIdArray.length != 0  )
		$scope.loadsIdArray = getAllTrucksStopData.loadsData.loadsIdArray;
		
	$rootScope.notShowDataTable = (getAllTrucksStopData.loadsData != undefined) ? getAllTrucksStopData.loadsData.notShowDataTable : true;
	
	if ( getAllTrucksStopData.loadsData != undefined ) {
		$rootScope.tableTitle = [];
		$rootScope.tableTitle.push(getAllTrucksStopData.loadsData.table_title);
		
		$rootScope.loadsData = getAllTrucksStopData.loadsData.rows;
		$rootScope.deadMilesOriginLocation = getAllTrucksStopData.loadsData.deadMilesOriginLocation;
	}
		
	if ( $rootScope.notShowDataTable == false ) {
		$rootScope.loadsData = [];
	}
	
	if ( $rootScope.loadsData == undefined || $rootScope.loadsData.length == 0 ) {
		$rootScope.loadsData = [];
	} else {
		$rootScope.fetchnewsearch = true;
		$rootScope.indexCount = (getAllTrucksStopData.loadsData != undefined ) ? getAllTrucksStopData.loadsData.indexCount : 0;
	}

	var qstring = $location.search();
	args = decodeURI(qstring.q);
	args = $scope.parseQuery(args);

	$rootScope.form.multistateCheck  		= parseInt(args.multistateCheck);
	$rootScope.form.multistateOriginCheck 	= parseInt(args.multistateOriginCheck);
	$rootScope.form.origin_range 			= args.origin_range;
	$rootScope.form.destination_range 		= args.destination_range;
	$rootScope.form.destination_country 	= args.destination_country;
	$rootScope.form.origin_country 			= args.origin_country;
	$rootScope.dcountryname  				= args.destination_country;
	$rootScope.form.load_type 				= args.load_type;
	$rootScope.form.multiDestinations 		= args.multiDestinations;
	$rootScope.form.multiOrigins 			= args.multiOrigins;
	$rootScope.form.destination_state 		= args.destination_state;
	$rootScope.form.destination_state1 		= args.destination_state1;
	$rootScope.form.posted_time 			= args.posted_time;		
	$rootScope.form.posted_time1 			= parseInt(args.posted_time) > 0 ? {val:args.posted_time+" Hours Ago", key: args.posted_time} : "";
	$rootScope.form.company_name 			= args.company_name== undefined   ? '' : args.company_name;
	$rootScope.form.dest_City 				= args.dest_City;
	$rootScope.form.dest_State				= args.dest_State;
	$rootScope.form.origin_City 			= args.origin_City;
	$rootScope.form.origin_State 			= args.origin_State;
	$rootScope.form.pickup_date				= args.pickup_date;
	$rootScope.form.searchAuto 				= args.searchAuto;
	$rootScope.form.multistateCheck 		= args.multistateCheck;
	$rootScope.form.multistateOriginCheck	= args.multistateOriginCheck;
	$rootScope.form.dailyFilter 			= args.dailyFilter;
	$rootScope.form.max_weight 				= parseInt(args.max_weight);
	$rootScope.form.max_length 				= parseInt(args.max_length);
	
	if ( args.moreMiles == 0 ) 
		$rootScope.form.moreLoadCheck = false;
	else
		$rootScope.form.moreLoadCheck = true;
		
	var ttype = args.trailerType != '' && args.trailerType != undefined ? args.trailerType.split(",") : '';
	var tdesc = args.tdesc != '' && args.tdesc != undefined ? args.tdesc.split(",") : '';
	$rootScope.tTRef = [];
	if(ttype != "" && tdesc != ""){
		angular.forEach(ttype, function(value, key) {
			$rootScope.tTRef.push({abbrevation:value,name:tdesc[key]});
		});
	}
	
	$cookies.remove("_search");
	$cookies.putObject('_search', $rootScope.form);
	
	$rootScope.selectedVehicleId = '';
	$scope.showApiPageNo = true;
	$scope.showMultiStatePopup = false;
	$rootScope.autoRequest = false;
	$scope.newChangeDriverLoads = false;
	$scope.perPageNumber = 100;
	
	$scope.onSelectoriginTimeCallback = function (item, model){
		var getOrigin=item.abbrevation+':'+item.destination_address+':'+item.state+':'+item.city+':'+item.driverName+':'+item.label;
		$("#select_origin").val(getOrigin);
	};
		
	$scope.removeLoad = function(truckstopId, $event, index) {
		angular.element("#confirm-delete").modal('show');
		angular.element("#confirm-delete").data("item",truckstopId);
		$scope.setDeleteIndex = index;
	}

	$scope.confirmDelete = function(confirm){
		if(confirm == 'yes'){
			var truckstopId =  angular.element("#confirm-delete").data("item");
			var x = $("#data").find("tr[data-uinfo='"+truckstopId+"']");
			$scope.loadsData.splice($scope.setDeleteIndex,1);
		}else{
			angular.element("#confirm-delete").removeData("item");
		}
		angular.element("#confirm-delete").modal('hide');
	}
	
	/**
	 * Fetching new loads after every 30 seconds
	 */
	 
	$rootScope.fetchLoadsAfterEvery =  function() {
		$scope.newRows = [];
		searchStatus = 'newSearch'; 
	
		dataFactory.httpRequest('truckstop/get_load_data_repeat','POST',{},{loadsArray: $scope.loadsIdArray, vehicleIDRepeat : $scope.vehicleIdRepeat, formPost: $rootScope.form, searchStatus : searchStatus}).then(function(data){
			$scope.newRows = data.rows;
			$scope.loadsIdArray = data.loadsIdArray;
			
			if ( $scope.newRows.length != 0 ){
				$rootScope.loadsData = $scope.newRows.concat($rootScope.loadsData);
			}
		});
	}
	
	$scope.getNewLoadsData = function(stateID, finalString){
		dataFactory.httpRequest(URL+'/truckstop/get_load_data','POST',{},{stateID: stateID}).then(function(data) {
			$scope.newData = data.rows;
			$rootScope.tableTitledata = data.table_title;
			$rootScope.loadsData.push($scope.newData);
			$rootScope.tableTitle.push($rootScope.tableTitledata);
		});
	}
    
    $rootScope.multistateCheck = 0;
	$scope.mindate = new Date();
	$scope.showNewSearchPopup = function() {
		$rootScope.form = {};
		$rootScope.multistateCheck = 0;
		$rootScope.popcheck = $rootScope.multistateCheck;
		$rootScope.form.origin_range = 100;
		$rootScope.form.destination_range = 100;
		$rootScope.form.destination_country = 'USA';
		$rootScope.form.load_type = 'Full';
		$rootScope.form.load_type1 = '';
		$rootScope.form.multiDestinations = '';
		$rootScope.form.destination_state = '';
		$rootScope.form.destination_state1 = '';
		$rootScope.form.posted_time1 = '';
		$rootScope.form.dest_City = '';
		$rootScope.form.dest_State = '';
		$rootScope.form.origin_City = '';
		$rootScope.form.origin_State = '';
		
		$rootScope.form.searchAuto = '';
		$("#destStates").val('');
	
		var modalElem = $('#myModal');
            $('#myModal').modal('show')
        modalElem.children('.modal-dialog').addClass('modal-lg');
	}
	
	$scope.fetchNewSearch = function() {
		if ( angular.element($('#destStates')).val() == '' ) {
			$rootScope.form.dest_City = '';
			$rootScope.form.dest_State = '';
		} 
		$scope.newSearchButtonShow = true;
		dataFactory.httpRequest(URL+'/truckstop/newsearch','POST',{},$rootScope.form).then(function(data) {
		$rootScope.fetchnewsearch = true;
		$rootScope.indexCount = data.indexCount;
			$scope.search_deadmile = 'all';
			
			$rootScope.loadsData = [];
			$rootScope.tableTitle = [];
			
			$rootScope.loadsData = data.rows;
			$rootScope.notShowDataTable = true;
			$rootScope.tableTitle.push(data.table_title);
			$rootScope.selectedVehicleId = data.searchLabel;	
			$scope.tableCount = data.tableCount;
			
			$scope.newSearchButtonShow = false;	
			$(".modal").modal("hide");
		});
	}

	$scope.toggleRow = function($event,index){
		angular.element("#hblock"+index).slideToggle();
		angular.element($event.target).toggleClass("minus-1");
	}
	
	/**
	 * 
	 * Editing the already searched new search
	 */ 
	
	$scope.editNewSearch = function() {
		$rootScope.form.searchAuto = $rootScope.form.origin_City + ',' + $rootScope.form.origin_State
		var modalElem = $('#myModal');
        $('#myModal').modal('show')
        modalElem.children('.modal-dialog').addClass('modal-lg');
	}
	
	$scope.notInterested = function( index ) {
		$rootScope.loadsData.splice(index,1);
	}
		
	/**Clicking on load detail changes url withour reload state*/
	$scope.clickMatchLoadDetail = function(truckstopId,loadId, deadmile,calPayment,totalCost,orignPickDate) {
		if ( loadId == '' && loadId == undefined ) 
			loadId = '';
			
		encodedUrl = btoa(truckstopId+'-'+loadId+'-'+deadmile+'-'+calPayment+'-'+totalCost+'-'+orignPickDate);
		$state.go('search.popup', {staticId:2,encodedurl:encodedUrl}, {notify: false,reloadOnSearch: false});
	}
	
	$scope.hideLoadDetailPopup = function() {
		$rootScope.firstTimeClick = true;
		var url = decodeURI($rootScope.absUrl.q);
		
		if ( url != '' && url != undefined && url != 'undefined' ) {
			$state.go('searchresults', {q:url,type: false}, {notify: false,reload: false});
		} else {
			$state.go('search', {type:false}, {notify: false,reload: false});	
		}	
	}
	
	/*Changing url on outer click of popup*/
	$(document).off('click','#edit-fetched-load').on("click",'#edit-fetched-load', function(event){
		var $trigger1 = $(".popup-container-wid1");
		if($trigger1 !== event.target && !$trigger1.has(event.target).length){
			var url = decodeURI($rootScope.absUrl.q);
			
			if ( url != '' && url != undefined && url != 'undefined' ) {
				$state.go('searchresults', {q:url,type:false}, {notify: false,reload: false});
			} else {
				$state.go('search', {type:false}, {notify: false,reload: false});	
			}	
		}
	});

	$scope.hideloadmsg = function(){
		$rootScope.alertloadmsg = false;
	}
	
	$scope.$on('ngRepeatFinished', function(ngRepeatFinishedEvent) {
	    
	    if ( $scope.hideTexts == true ) {
			angular.element($('.actuals-class')).hide();
		} else {
			angular.element($('.actuals-class')).show();
		}
		angular.element($('.actuals-class')).hide();
	});
			
});

app.directive('onFinishRender', function ($timeout) {
    return {
        restrict: 'A',
        link: function (scope, element, attr) {
            if (scope.$last === true) {
                $timeout(function () {
                    scope.$emit(attr.onFinishRender);
                });
            }
        }
    }
});

app.directive('format', ['$filter', function ($filter) {
    return {
        require: '?ngModel',
        link: function (scope, elem, attrs, ctrl) {
            if (!ctrl) return;

            ctrl.$formatters.unshift(function (a) {
                return $filter(attrs.format)(ctrl.$modelValue)
            });

            elem.bind('blur', function(event) {
                var plainNumber = elem.val().replace(/[^\d|\-+|\.+]/g, '');
                elem.val($filter(attrs.format)(plainNumber));
            });
        }
    };
}]);
app.directive('shouldFocus', function(){
	return {
		restrict: 'A',
		link: function(scope,element,attrs){
			scope.$watch(attrs.shouldFocus,function(newVal,oldVal){
			element[0].scrollIntoView(false);
			});
		}
	};
});
app.directive('setClassWhenAtTop', function ($window) {
  var $win = angular.element($window); // wrap window object as jQuery object

  return {
    restrict: 'A',
    link: function (scope, element, attrs) {
      var topClass = attrs.setClassWhenAtTop, // get CSS class from directive's attribute value
          offsetTop = element.offset().top; // get element's offset top relative to document

      $win.on('scroll', function (e) {
        if ($win.scrollTop() >= offsetTop) {
          element.addClass(topClass);
        } else {
          element.removeClass(topClass);
        }
      });
    }
  };
});