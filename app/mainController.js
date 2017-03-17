app.controller('mainController', function (dataFactory,$scope, $sce,$rootScope, $location, $http , $cookies, $state, $localStorage, $q, $timeout,$window, $compile) {
$rootScope.logoutmessage=false;
    $scope.login = {};
	
	$scope.trustAsHtml = function(value) {
        return $sce.trustAsHtml(value);
    };
   
    /**Fetching language values*/
   
	$rootScope.languageArray = [];
	$rootScope.languageCommonVariables = [];
	$rootScope.LangArr = [];
	$rootScope.LangArr = languageArr;

	if ( $cookies.get('setLanguageGlobalVariable') == '' || $cookies.get('setLanguageGlobalVariable') == undefined ) 
		$cookies.put('setLanguageGlobalVariable','english');	
		
	$rootScope.setInitialCommonLang = function(lang, stateName) {
		dataFactory.httpRequest(URL + '/login/changeCommonLanguage/'+lang+'/'+stateName).then(function(langdata) {
			$rootScope.languageCommonVariables = langdata;
		});
		/******************for solving prb at load detail popup heading*************/ 
		if ( lang != 'english' ){
			$rootScope.setClassInModel = true;
		}else{
			$rootScope.setClassInModel = false;
		}
		/******************for solving prb at load detail popup heading*************/
	}
		
	/**Fetching content from common file*/
	$rootScope.setInitialCommonLang($cookies.get('setLanguageGlobalVariable'),'common');

	$rootScope.loginCheck = function( currentState ) {
		//~ if ( $cookies.get('userIsLoggedIn') == 1 ) {
		dataFactory.httpRequest(URL + '/login/checkLogin').then(function(data) {
			if ( data.success == true ) {
				$rootScope.loggedInUser = true;
				$rootScope.showHeader = true;
				$rootScope.showBackground = false;
				$rootScope.loggedUserFirstName = $cookies.get('loggedUserFirstNameCookie');
				$rootScope.loggedUserRoleId = $cookies.get('loggedUserRoleId');
				$rootScope.profileImage = $cookies.get('profileImage');
				if ( $rootScope.previousUrl != undefined && $rootScope.previousUrl != '' ) {
					var prevUrl = $rootScope.previousUrl.split('#/');
					if ( prevUrl[1] != 'login' )
						$location.path(prevUrl[1]);
				} else {
					if( currentState == 'login' )
						$location.path('dashboard');
				}
				$rootScope.previousUrl = ''; 
				
			} else {
				if ( currentState != 'login') 
					$rootScope.previousUrl = $location.absUrl();
				$scope.showLogin = true;
				$rootScope.loggedInUser = false;
				$rootScope.showHeader = false;
				$rootScope.showBackground = true;
				$rootScope.loggedFirstUserName = '';
				$state.go('login');
			}
		});
	}
				
	/**Changing the language**/
	$rootScope.setLanguageGlobal = function( language ) {
		if ( $cookies.get('setLanguageGlobalVariable') != language ) {
			$cookies.put('setLanguageGlobalVariable', language);
			$window.location.reload();
		}
	}
	
	$rootScope.dataTableOpts = function(targetLength,targetColumn,disableAnotherSorting,dtoptions){   
		
		$rootScope.targetLength = targetLength;
		$rootScope.targetColumn = targetColumn;
		$rootScope.disableAnotherSorting = disableAnotherSorting;
		$rootScope.dtOptionsorting =dtoptions; 
			$rootScope.dtOptions = {
			"scrollCollapse": true,
			"scrollX": false,
			"language": {
				"search": $rootScope.languageCommonVariables.dataTableSearchMsg,
				"lengthMenu": "Display _MENU_ records per page",
				"zeroRecords": $rootScope.languageCommonVariables.dataTableNoRecordAvlMsg,
				"Info": $rootScope.languageCommonVariables.dataTableShowingMsg +" <b>_START_ to _END_</b> of _TOTAL_ "+ $rootScope.languageCommonVariables.dataTableEntriesMsg,
				//~ "info": "Showing page _PAGE_ of _PAGES_",
				"infoEmpty": $rootScope.languageCommonVariables.dataTableNoRecordAvlMsg,
				"infoFiltered": "("+$rootScope.languageCommonVariables.dataTableFilteredFromRecordMsg+" _MAX_ "+$rootScope.languageCommonVariables.dataTableTotalRecordsMsg+")",
				"paginate": {
					"previous": $rootScope.languageCommonVariables.dataTableFilteredPrevious,
					"next": $rootScope.languageCommonVariables.dataTableFilteredNext,
				},
			},
			
			"aaSorting": [],
			//~ "order":$rootScope.dtOptionsorting,
			"iDisplayLength": $rootScope.targetLength,
			"bLengthChange":false,
			"stateSave": true,
			"responsive": true,
		    "bDestroy": true,
			"columnDefs":[ {
					"targets": [$rootScope.targetColumn,$rootScope.disableAnotherSorting],
					"orderable": false,
					"responsivePriority":1
				} ]
		};
	}
	
	/** 
	 * Generating csv file for tables
	 */
	$scope.csvContent = "1,2,3\n4,5,6";
	$rootScope.exportCsvAPI = function($event){
		
		var csvString = [];
		var tdValue = '';
    	
		angular.element($event.currentTarget).closest('.table-res-1').find('.tb-head').find('.tb-cell').each (function() {
			csvString.push(jQuery.trim(angular.element(this).text()));
		});
		
		csvString.splice(-1,1);
		$scope.csvContent = csvString.join();
		csvString = [];
		angular.element($event.currentTarget).closest('.tb-body').find('.export').each(function() {
			tdValue = angular.element(this).text();
			tdValue = tdValue.replace(/[,*{}★★★★★\n]/g,'');
			csvString.push(tdValue);
		});
		csvString.splice(-1,1);
		$scope.csvContent += "\n" + csvString.join();
		var timestamp = Math.floor(Date.now() / 1000);

    	var downloadContainer = angular.element('<div data-tap-disabled="true"><a></a></div>');
        var downloadLink = angular.element(downloadContainer.children()[0]);
        downloadLink.attr('href', 'data:application/octet-stream;base64,'+btoa($scope.csvContent));
        downloadLink.attr('download', "detailInfo_"+timestamp+".csv");
        downloadLink.attr('target', '_blank');
        angular.element('body').append(downloadContainer);
        $timeout(function () {
          downloadLink[0].click();
          downloadLink.remove();
        }, null);
    }
    
    $rootScope.exportCsv = function($event,table, id){
		var csvString = [];
		dataFactory.httpRequest(URL+'/states/fetchDataForCsv','POST',{},{tableName: table, primaryId: id }).then(function(data){
			csvString = data.keys;
			$scope.csvContent = csvString;
			csvString = [];
			csvString = data.values;
			
			$scope.csvContent += "\n" + csvString;
			var timestamp = Math.floor(Date.now() / 1000);

			var downloadContainer = angular.element('<div data-tap-disabled="true"><a></a></div>');
			var downloadLink = angular.element(downloadContainer.children()[0]);
			downloadLink.attr('href', 'data:application/octet-stream;base64,'+btoa($scope.csvContent));
			downloadLink.attr('download', "detailInfo_"+timestamp+".csv");
			downloadLink.attr('target', '_blank');
			angular.element('body').append(downloadContainer);
			$timeout(function () {
			  downloadLink[0].click();
			  downloadLink.remove();
			}, null);
        });
    }
     
	/**Toggle languages onheader */
	$rootScope.toggleMenu = true;
	$rootScope.toggleLanuages = function() {
		$rootScope.toggleMenu = $scope.custom === false ? true: false;
	}
	
		
		/**
		 * Removing the success message window on close click
		 * 
		 */ 
		$rootScope.hideloadmessage = function(){
			$rootScope.alertdeletemsg = false;
			$rootScope.alertloadmsg = false;
			$rootScope.alertExceedMsg = false;
			$rootScope.dataNotFound = false;
			$rootScope.showErrorMessage = false;
		}
		
		/**
		 * Opening new search popup from top search bar
		 */
		
		$rootScope.form = {};
		$rootScope.shownewSearch = false;
		$rootScope.mindate = new Date();
		$rootScope.minDestdate = new Date();
		$rootScope.multistateCheck = 0;
		$rootScope.multistateOriginCheck = 0;
		$rootScope.popcheck = 0;
		$rootScope.selectDataFor = 'destination';
		$rootScope.noDestCity = false;
		$rootScope.mainDestUL = false;
		$rootScope.noCity = false;
		$rootScope.mainOriginUL = false;
		$rootScope.form.searchAuto = '';
		$rootScope.trailerTypes = [];
		$rootScope.postedOn = [{ 'val' : '2 Hours Ago','key' : '2'},{'val' : '4 Hours Ago','key' : '4'},{'val' : '6 Hours Ago','key' : '6'},{'val' : '8 Hours Ago','key' : '8'},{ 'val' : '8+ Hours Ago','key' : ''}];
		$rootScope.loadsFilter = [{ 'val' : 'All Loads','key' : 'all'},{'val' : 'Single Run','key' : 'single'},{'val' : 'Daily Run','key' : 'daily'}];
		$rootScope.searchDailyFilter = 'All Loads';
		$rootScope.toggleSearchTitle = $rootScope.languageCommonVariables.triggerLoadSearch;

		
		$rootScope.onSelectLoadsFilterCallback = function (item, model){
			$rootScope.form.dailyFilter = item.key;
			$rootScope.searchDailyFilter = item.val;
		}
		
		/**
		 * Search bar click
		*/
		  
		$rootScope.showSearchOverlay = function() {
			$scope.$broadcast('toggleSearchOverlay', {
                show: true
            });
            
            $rootScope.shownewSearch = false;
            $rootScope.form = {};
          
            $rootScope.openNewSearchPopUp();
            $rootScope.searchDriversList = '';
            /**Fetching trailer types from db*/
			dataFactory.httpRequest(URL + '/truckstop/fetchTrailerType').then(function(data) {
				$rootScope.trailerTypes = data.trailerTypes;
				$rootScope.vDriversList = data.driversList;
			});
			/*** to make datepicker empty -r288 ***/
			$(".search-by-date" ).datepicker('setDate','');
			/*** to make datepicker empty End -r288 ***/
		}
		
		/**
		 * Driver select for srp page results
		 */
		
		$rootScope.onSelectDriverFilterCallback = function(item, model) {
			if ( item.vehicleId != undefined && item.vehicleId != '' )
				$rootScope.form.selectedSRPDriver = item.vehicleId;  
			else
				$rootScope.form.selectedSRPDriver = item.vehicleId;  
				
			$rootScope.searchDriversList = item.driverName;  			
		} 
		
		/*** refresh datepicker -r288 ***/
		$rootScope.refreshDatepicker = function($event,date){
			angular.element($event.currentTarget).keypress();
			angular.element($event.currentTarget).keyup();
		}
		/*** refresh datepicker End -r288 ***/
		
		$rootScope.editSearch = function(){
			$rootScope.showSearchOverlay();
			$rootScope.openNewSearchPopUp();
			$rootScope.form = $cookies.getObject('_search');
			
			$rootScope.toggleSearchTitle = $rootScope.languageCommonVariables.closeLoadSearch;
            $rootScope.shownewSearch = true;
            $rootScope.dcountryname = $rootScope.form.destination_country == 'CAN' ? 'Canada' : $rootScope.form.destination_country;
            $rootScope.ocountryname = $rootScope.form.origin_country== 'CAN' ? 'Canada' : $rootScope.form.origin_country;
            $rootScope.multistateCheck = $rootScope.popcheck = parseInt($rootScope.form.multistateCheck) ; 
			$rootScope.multistateOriginCheck = $rootScope.popcheckOrigin = parseInt($rootScope.form.multistateOriginCheck);  
			$rootScope.form.trailerType = $rootScope.tTRef;
			$rootScope.changeOriginMultistateText($rootScope.form.origin_country);
			$rootScope.changeDestMultistateText($rootScope.form.destination_country);

		}

		$rootScope.toggleSeachPopup = function(){
            if($rootScope.shownewSearch){
            	$rootScope.toggleSearchTitle = $rootScope.languageCommonVariables.triggerLoadSearch;
            	$rootScope.shownewSearch = false;
            }else{
            	$rootScope.toggleSearchTitle = $rootScope.languageCommonVariables.closeLoadSearch;
            	$rootScope.shownewSearch = true;
            }
		}
		
		$rootScope.openNewSearchPopUp = function() {
			$rootScope.toggleSeachPopup();
			$rootScope.multistateCheck = 0;
			$rootScope.multistateOriginCheck = 0;
			$rootScope.popcheck = $rootScope.multistateCheck;
			$rootScope.popcheckOrigin = $rootScope.multistateOriginCheck;
			$rootScope.form.origin_range = 100;
			$rootScope.form.destination_range = 100;
			$rootScope.form.destination_country = '';
			$rootScope.form.origin_country = 'USA';


			$rootScope.allCountries = [{ 'name' : 'USA','key' : 'USA'},{ 'name' : 'Canada','key' : 'CAN'}];
			$rootScope.form.origin_country =  $rootScope.country = $rootScope.ocountryname =   $rootScope.sel_country = 'USA';
			$rootScope.dcountryname  = '';
			$rootScope.changeOriginMultistateText('USA');
			$rootScope.changeDestMultistateText('USA');
			$rootScope.form.load_type = 'Full';
			$rootScope.form.multiDestinations = '';
			$rootScope.form.multiOrigins = '';
			$rootScope.form.destination_state = '';
			$rootScope.form.dest_City = '';
			$rootScope.form.dest_State = '';
			$rootScope.form.origin_City = '';
			$rootScope.form.origin_State = '';
			$rootScope.form.pickup_date = '';
			$rootScope.form.searchAuto = '';
			$("#destStates").val('');
			
			$rootScope.form.moreLoadCheck = true;
			$rootScope.form.trailerType = [{ 'abbrevation' : 'F','name' : 'Flatbed'}];
			$rootScope.form.posted_time1 = {'val' : '8 Hours Ago','key' : '8'};
			$rootScope.form.max_weight = 48000;
			$rootScope.form.max_length = 48;
			$rootScope.form.posted_time = 8;
			var modalElem = $('#globalSearchId');
            $('#globalSearchId').modal('show')
			modalElem.children('.modal-dialog').addClass('modal-lg');
			
			$rootScope.search.query = '';
			$rootScope.searchResults = [];
		} 

		//Global Search Results
		$rootScope.fetchSearchResults = function() {
			if ( angular.element($('#destStates')).val() == '' ) {
				$rootScope.form.dest_City = '';
				$rootScope.form.dest_State = '';
			}

			$rootScope.form.multistateCheck = $rootScope.multistateCheck;
			$rootScope.form.multistateOriginCheck = $rootScope.multistateOriginCheck;

			var q  = 'dest_City='+$rootScope.form.dest_City;
		 	q  += '&dest_State='+$rootScope.form.dest_State;
		 	if($rootScope.form.destination_country == ''){
		 		q  += '&destination_country='+$rootScope.form.origin_country;
		 	}else{
		 		q  += '&destination_country='+$rootScope.form.destination_country;
		 	}

		 	if($rootScope.form.company_name == undefined){
		 		$rootScope.form.company_name = '';
		 	}
		 	
		 	q  += '&destination_range='+$rootScope.form.destination_range;
		 	q  += '&destination_state='+$rootScope.form.destination_state;
		 	q  += '&load_type='+$rootScope.form.load_type;
		 	q  += '&multiDestinations='+$rootScope.form.multiDestinations;
		 	q  += '&multiOrigins='+$rootScope.form.multiOrigins;
		 	q  += '&origin_City='+$rootScope.form.origin_City;
		 	q  += '&origin_country='+$rootScope.form.origin_country;
		 	q  += '&origin_State='+$rootScope.form.origin_State;
		 	q  += '&origin_range='+$rootScope.form.origin_range;
		 	q  += '&posted_time='+$rootScope.form.posted_time;
		 	q  += '&searchAuto='+$rootScope.form.searchAuto;
		 	q  += '&pickup_date='+$rootScope.form.pickup_date;
		 	q  += '&company_name='+$rootScope.form.company_name;
		 	q  += '&multistateCheck='+$rootScope.form.multistateCheck;
		 	q  += '&multistateOriginCheck='+$rootScope.form.multistateOriginCheck;

		 	var trailerType = [], ttype = '', ttypeDesc=[], tdesc = '';
		 	
		 	if($rootScope.form.trailerType != undefined){
		 		var i=0;
		 		var obj = $rootScope.form.trailerType;
			 	angular.forEach(obj, function(value, key) {
			 			trailerType.push(value.abbrevation);
			 			ttypeDesc.push(value.name);
				 	});
				ttype = trailerType.toString();
				tdesc = ttypeDesc.toString();
			}
			q  += '&trailerType='+ttype;
			q  += '&tdesc='+tdesc;
			
			if ( $rootScope.form.moreLoadCheck == true )
				var showMoreMiles = 1;
			else 
				var showMoreMiles = 0;
			
			q  += '&moreMiles='+showMoreMiles;
			q  += '&dailyFilter='+$rootScope.form.dailyFilter;
			q  += '&max_weight='+$rootScope.form.max_weight;
			q  += '&max_length='+$rootScope.form.max_length;
			q  += '&selectedSRPDriver='+$rootScope.form.selectedSRPDriver;
			q  += '&setLanguage='+$cookies.get('setLanguageGlobalVariable');
			$rootScope.form.ttype = ttype;
			$cookies.remove("_search");
			$cookies.putObject('_search', $rootScope.form);
		 	$state.go('searchresults', {q:encodeURI(q),type:true}, {reload: true});
		 	$("#mainSearchOverlay").hide();
		}


		$rootScope.originCountry = 'USA';
		$rootScope.destCountry = 'USA';
		/**fetching origin states from db**/
		$rootScope.fetchUSCities = function(enteredCity,country) {
		
			if ( enteredCity != undefined && enteredCity.length > 1 ) {
				dataFactory.httpRequest(URL+'/truckstop/searchCity','POST',{},{city: enteredCity,country:country}).then(function(data){
					
					$scope.mainOriginUL = true;
					if ( data.result.length > 0 ) {
						$('.list-unstyled').show();
						$rootScope.originCities = data.result;
						$rootScope.noCity = false;
					} 					
				});
			} else {
				$('.list-unstyled').hide();
			}
		}
		
		//---------------------- Commodity Auto Suggestion  -----------------------
		$rootScope.haveSuggestions = false;
		$rootScope.getCommodities = function(commodity){
			if ( commodity != undefined && commodity.length > 1 ) {
				dataFactory.httpRequest(URL+'/truckstop/getCommodities','POST',{},{commodity:commodity}).then(function(data){
					$rootScope.haveSuggestions = true
					if(data.suggestions.length > 0){
						$rootScope.commoditySuggestions = data.suggestions;	
					}
				});
			}else{
				$rootScope.haveSuggestions = false;
			}
		}

		$rootScope.clearBuffer = function(){
			$cookies.remove("_globalDropdown");
			$cookies.remove("_gDateRange");
			$cookies.remove("printTicket");
		}

		$rootScope.selectCommoditySuggestion = function(suggestion){
			$rootScope.editSavedLoad.commodity = suggestion.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
			$rootScope.haveSuggestions = false;
		}
		
		$rootScope.hideCommoditySuggestionList = function(){
			$rootScope.haveSuggestions = false;	
		}
		
		$rootScope.fetchUSCitiesSelect = function (city,state) {
			var getOrigin = city +', ' + state;
			$("#originStates").val(getOrigin);
			$('.list-unstyled').hide();
			
			$rootScope.form.origin_City = city;
			$rootScope.form.origin_State = state;
			$rootScope.form.searchAuto = getOrigin;
			$rootScope.mainOriginUL = false;
		}
		
		$rootScope.fetchNoCities = function () {
			$("#originStates").val('');
			$('.list-unstyled').hide();
			
			$rootScope.form.origin_City = '';
			$rootScope.form.origin_State = '';
			$rootScope.form.searchAuto = '';
			$rootScope.mainOriginUL = false;
		}
		
		/** Fetching destination states from db */
		
		$rootScope.fetchUSDestCities = function(enteredCity,country) {
			if ( enteredCity != undefined && enteredCity.length > 1 ) {
				dataFactory.httpRequest(URL+'/truckstop/searchCity','POST',{},{city: enteredCity,country:country}).then(function(data){
					if ( data.result.length > 0 ) {
						$('.list-unstyled-dest').show();
						$rootScope.destCities = data.result;
						$rootScope.noDestCity = false;
						if(country== ''){
							$rootScope.showCountry = true;
						}
					} 
					$rootScope.mainDestUL = true;
				});
			} else {
				$('.list-unstyled-dest').hide();
			}
		}
		
		$rootScope.fetchUSCitiesDest = function (city,state,country,showCountry) {
			var getOrigin = city +', ' + state;
			$("#destStates").val(getOrigin);
			$('.list-unstyled-dest').hide();
			
			if(showCountry){
				$rootScope.dcountryname = country == "CAN" ? "Canada" : country;
				$rootScope.form.destination_country = 	country;
				$rootScope.changeDestMultistateText(country);
			}

			$rootScope.form.dest_City = city;
			$rootScope.form.dest_State = state;
			$rootScope.form.destSearch = getOrigin;
			$rootScope.mainDestUL = false;
		}
		
		$rootScope.fetchNoCitiesDest = function () {
			$("#destStates").val('');
			$('.list-unstyled-dest').hide();
			
			$rootScope.form.dest_City = '';
			$rootScope.form.dest_State = '';
			$rootScope.mainDestUL = false;
		}
		
		/** showing multiple destination checkbox */
		$rootScope.ShowMultiState = function() {
			$rootScope.country = $rootScope.form.destination_country != "" ? $rootScope.form.destination_country : 'USA';
			$rootScope.sel_country = $rootScope.country == "CAN" ? "Canada" : $rootScope.country;
			if ($rootScope.multistateCheck) {
				$rootScope.selectDataFor = 'destination';
				$rootScope.form.multiDestinations = '';
				var modalElem = $('#multistate');
				
				modalElem.children('.modal-dialog').addClass('modal-lg');
				
				dataFactory.httpRequest(URL+'/states/fetch_states_areas/'+$rootScope.form.destination_country).then(function(data) {
					$scope.showMultiStatePopup = true;
					$rootScope.areas = data.areas;
					$rootScope.regions = data.regions;
					$rootScope.country = data.country;
					$rootScope.cDisplay = data.country == "CAN" ? "Canada" : data.country ;
					$('#multistate').modal('show');
				});
			} 
		}
		
		$rootScope.checkmultistate = function()
		{
			if($rootScope.multistateCheck == 0)
			{
				$rootScope.multistateCheck = 1;
				$rootScope.popcheck = $rootScope.multistateCheck;
			}
			else
			{
				$rootScope.multistateCheck = 0;
				$rootScope.popcheck = $rootScope.multistateCheck;	
				$scope.form.multiDestinations = '';
				$rootScope.dcountryname = $rootScope.form.destination_country = 	'';
				$rootScope.changeDestMultistateText('USA');


			}
		}
		
		/** showing multiple origin checkbox */
		
		$rootScope.ShowMultiOriginState = function() {
			$rootScope.country =  $rootScope.form.origin_country != "" ? $rootScope.form.origin_country : 'USA';
			$rootScope.sel_country = $rootScope.country == "CAN" ? "Canada" : $rootScope.country;
			if ($rootScope.multistateOriginCheck) {
				$rootScope.selectDataFor = 'origin';
				$rootScope.form.multiOrigins = '';
				var modalElem = $('#multistate');
				
				modalElem.children('.modal-dialog').addClass('modal-lg');
				
				dataFactory.httpRequest(URL+'/states/fetch_states_areas/'+$rootScope.form.origin_country).then(function(data) {
					$scope.showMultiStatePopup = true;
					$rootScope.areas = data.areas;
					$rootScope.regions = data.regions;
					$rootScope.country = data.country;
					$rootScope.cDisplay = data.country === "CAN" ? "Canada" : data.country ;
					$('#multistate').modal('show');
				});
			} 
		}
		
		$rootScope.checkmultiOriginstate = function()
		{
			
			if($rootScope.multistateOriginCheck == 0)
			{
				$rootScope.multistateOriginCheck = 1;
				$rootScope.popcheckOrigin = $rootScope.multistateOriginCheck;
			}
			else
			{
				$rootScope.multistateOriginCheck = 0;
				$rootScope.popcheckOrigin = $rootScope.multistateOriginCheck;	
				$scope.form.multiOrigins = '';
				$rootScope.ocountryname = $rootScope.form.origin_country =  'USA';
				$rootScope.changeOriginMultistateText($rootScope.form.origin_country);
			}
		}
		
		/**Unchecking the two checkboxex*/
		$rootScope.multiStateUnchek = function(status)
		{
			if ( status == 'origin') {
				if($rootScope.multistateOriginCheck == 0)
				{
					$rootScope.multistateOriginCheck = 1;
					$rootScope.popcheckOrigin = $rootScope.multistateOriginCheck;
				}
				else
				{
					$rootScope.multistateOriginCheck = 0;
					$rootScope.popcheckOrigin = $rootScope.multistateOriginCheck;	
					$scope.form.multiOrigins = '';
				}
			} else if( status == 'searchFrom' ) {					// checking checkbox checked on searchFrom popup on plan page
				if ( $rootScope.iterationpopcheck_searchFrom == 0 ) {			
					$rootScope.iterationpopcheck_searchFrom = 1;
					$rootScope.multistateSearchFromCheck = 1;		// searchFrom popup on plan page showing text area for origin search
				} else {
					$rootScope.iterationpopcheck_searchFrom = 0;
					$rootScope.multistateSearchFromCheck = 0;		// searchFrom popup on plan page showing default textbox for origin search
					$rootScope.askCustom.multiOrigins = '';			// empty multiorigins on searchFrom popup
				}
			} else {
				if($rootScope.multistateCheck == 0)
				{
					$rootScope.multistateCheck = 1;
					$rootScope.popcheck = $rootScope.multistateCheck;
				}
				else
				{
					$rootScope.multistateCheck = 0;
					$rootScope.popcheck = $rootScope.multistateCheck;	
					$scope.form.multiDestinations = '';
				}
			}
		}
		
		$rootScope.changeCountry = function (c) {
			dataFactory.httpRequest(URL+'/states/fetch_states_areas/'+c).then(function(data) {
				$scope.showMultiStatePopup = true;
				$rootScope.areas = data.areas;
				$rootScope.regions = data.regions;
				$rootScope.country = c;
				$rootScope.cDisplay = data.country === "CAN" ? "Canada" : data.country ;
			});
			
		}
		
		$rootScope.getAllCheckedBoxes = function() {
			var selected = [];
			angular.element('.achekbox:checkbox:checked').each(function() {
				if(selected.indexOf(angular.element(this).val()) === -1) {
			    	selected.push(angular.element(this).val());
				}
			});

			if ( $rootScope.selectDataFor != undefined && $rootScope.selectDataFor == 'searchFrom' )  {				// check if searchFrom popup is selected on plan page
				$rootScope.askCustom.multiOrigins = '';
				$rootScope.askCustom.multiOrigins = selected;
				$rootScope.askCustom.origin_country = $rootScope.country == 'CAN' ? "Canada" : $rootScope.country ;
			} else {
				if($rootScope.iterationmultistateCheck){
					$rootScope.iterationPopData.multiDestinations = selected;
					$rootScope.iterationPopData.multiDestCountry = $rootScope.country ;
				} else {
					if ($rootScope.selectDataFor == 'destination' ) {
						$rootScope.form.multiDestinations = '';
						$rootScope.form.multiDestinations = selected;
						$rootScope.form.destination_country = $rootScope.country;
						$rootScope.dcountryname = $rootScope.country == 'CAN' ? "Canada" : $rootScope.country ;
						$rootScope.changeDestMultistateText($rootScope.country);
					} else {
						$rootScope.form.multiOrigins = '';
						$rootScope.form.multiOrigins = selected;
						$rootScope.form.origin_country = $rootScope.country;
						$rootScope.ocountryname = $rootScope.country == 'CAN' ? "Canada" : $rootScope.country ;
						$rootScope.changeOriginMultistateText($rootScope.country);
					}	
				}
			}
			$("#multistate").modal("hide");
		}
		
		/**Select posted time callback*/
		$rootScope.onSelectpostedTimeCallback = function (item, model){
			$rootScope.form.posted_time = item.key;
			//~ $("#select_postedTime").val(item.key);
		};
		
		$rootScope.onSelectOriginCountryCallback = function (item, model){
			
			$rootScope.form.origin_country = item.key;
			$rootScope.ocountryname = item.name;
			$rootScope.sel_country = item.name;
			$rootScope.changeOriginMultistateText(item.key);
			
		};
		$rootScope.onSelectDestinationCountryCallback = function (item, model){
			$rootScope.form.destination_country = item.key;
			$rootScope.dcountryname = item.name;
			$rootScope.sel_country = item.name;
			$rootScope.changeDestMultistateText(item.key);
		};

		$rootScope.onSelectCountryCallback = function (item, model){
			$rootScope.changeCountry(item.key);
			$rootScope.sel_country = item.name;
		};
		
		$rootScope.changeOriginMultistateText = function(key){
			switch(key){
				case 'CAN' 	: $rootScope.originMultiState = $rootScope.languageCommonVariables.searchmultiprovince; break;
				case 'USA' 	: $rootScope.originMultiState = $rootScope.languageCommonVariables.searchoriginmultistate; break;
				default		: $rootScope.originMultiState = $rootScope.languageCommonVariables.searchoriginmultistate;
			}
		}

		$rootScope.changeDestMultistateText = function(key){
			switch(key){
				case 'CAN' 	: $rootScope.destMultiState = $rootScope.languageCommonVariables.searchmultiprovince;  		break;
				case 'USA' 	: $rootScope.destMultiState = $rootScope.languageCommonVariables.searchoriginmultistate;  	break;
				default		: $rootScope.destMultiState = $rootScope.languageCommonVariables.searchoriginmultistate; 
			}
		}
		
		/**Fetching Special Note information for loads start **/
		$rootScope.fetchSpecialNote = function ( truckstopId, loadId ) {
			$rootScope.SpecialInfo = '';
			dataFactory.httpRequest(URL+'/truckstop/fetch_truckstop_special_note/'+truckstopId+'/'+loadId).then(function(data) {
				
				if ( data.specialInfo.trim() != '' ) {
					var specialInfo =  data.specialInfo;
				} else {
					var specialInfo = $rootScope.languageArray.specialInfoNotFound;
				}
				$rootScope.SpecialInfo = specialInfo;
				$('#specialInfoModal').modal('show')
			});
		}
		/**Fetching Special Note information for loads end **/
		
		
	/** Limiting autocomplete search for US country only */
	$scope.limitCountryOptions = {componentRestrictions: {country: 'US'}};	
	
		$rootScope.firstTimeClick = true;
		$rootScope.showPaymentCal = false;
		$rootScope.extraStops = {};
		$rootScope.Docs = [];				// empty array of documents list
		/**Load Detail Popup start**/
		$rootScope.editSaveLoad = function(tArr,encodedArr) {
			$rootScope.jobStatus = [{ 'val' : 'No Status','key' : ''},{ 'val' : $rootScope.languageCommonVariables.negotiating,'key' : 'negotiating'},{ 'val' : $rootScope.languageCommonVariables.booked,'key' : 'booked'},{ 'val' : $rootScope.languageCommonVariables.inprogress,'key' : 'inprogress'},{ 'val' : $rootScope.languageCommonVariables.delayed,'key' : 'delayed'},{ 'val' : $rootScope.languageCommonVariables.delivered,'key' : 'delivered'},{ 'val' : $rootScope.languageCommonVariables.completed,'key' : 'completed'},{ 'val' : $rootScope.languageCommonVariables.cancel,'key' : 'cancel'},{ 'val' : $rootScope.languageCommonVariables.invoiced,'key' : 'invoiced'}];

				encodedArr = atob(encodedArr)
				var splitedArray = encodedArr.split('-');
				truckstopId = splitedArray[0];
				loadId = splitedArray[1];
				deadmile = splitedArray[2];
				calPayment = splitedArray[3];
				totalCost = splitedArray[4];
				orignPickDate = splitedArray[5];
			
				$rootScope.setDeadMilePage = 0;
				$scope.setChainArrayValue = '';
				
				if ( splitedArray[6] != undefined && splitedArray[6] != ''  )  {
					if ( splitedArray[6].includes('plan') == true ) {
						if (splitedArray[6].includes('plan:') == true ) {
							var splitArray = splitedArray[6].split(':');
							$scope.setChainArrayValue = splitArray[1]; 		// setting default value for iteration page left column for deadmiles check
							$rootScope.setDeadMilePage = 1;
						} else {
							$rootScope.setDeadMilePage = 1;				// setting default value for iteration page for deadmiles check
						}
					} else {
						$rootScope.vehicleIdRepeat = splitedArray[6];
					}								
				} 
				
				var driverType = "driver";
				if ( splitedArray[7] != '' && splitedArray[7] != undefined )  {
					driverType = splitedArray[7];
				}
				
				//------------------ Add class on job if open first time ---------------------------------
				var pickDate = $("#testTable").find("div.tb-body[data-uinfo='"+truckstopId+"']").data('pickdate');
				var rowClicked = $("#testTable").find("div.tb-body[data-uinfo='"+truckstopId+"']");
				
				var $_COOKIE_NAME = 'VISIT_'+ truckstopId + '_' + orignPickDate.replace(new RegExp('[/]', 'g'), '_');
				preVisit = $cookies.get($_COOKIE_NAME);
				
				if(preVisit !== "visited"){
					$cookies.put($_COOKIE_NAME, 'visited');
					angular.element(rowClicked).addClass('visited');
				}else{
					if(!angular.element(rowClicked).hasClass('visited')){
						angular.element(rowClicked).addClass('visited');
					}
				}
				
				//------------------ Add class on job if open first time ---------------------------------
			
				$rootScope.alertloadmsg = false;
				$rootScope.alertExceedMsg = false;
				$rootScope.editLoads = true;
				$rootScope.matchingTrucks = false;
				$rootScope.showMaps = false;
				$rootScope.billingDetailsInfo = false;
				$rootScope.brokerDetailInfo = false;
				$rootScope.showhighlighted = 'loadDetail';
				$rootScope.save_cancel_div = false;
				$rootScope.save_edit_div = true;
				$rootScope.showFormClass = true;
								
				$rootScope.defaultDriverImage = false;
				$rootScope.defaultVehicleImage = false;
				$rootScope.vehicleDriverFound = false;
				
				initAutocomplete();
				
				$rootScope.notShowPlusIcon = true;				// showing extra stop check for load# less than 9999
				$rootScope.listDrivers = [];
				
			/**For rate sheet and proof of delivery */
				$rootScope.uploadRateSheetDoc = false;
				$rootScope.uploadPODDoc = false;
				$rootScope.showUploadRateSheetButton = true;
				$rootScope.disabledUISelect = true;		
				
				$rootScope.disableTemp = true;
				$rootScope.disablePerm = true;			// setting fields to disable after status changed to booked
				$rootScope.disableAnotherDropdowns = true;			// setting dropdowns to disable untill drive assigment is made
				$scope.trailerTypes = [];
				
				var deadMilesLocation = '';
				if ( $rootScope.deadMilesOriginLocation != undefined && $rootScope.deadMilesOriginLocation != '' )
					deadMilesLocation = $rootScope.deadMilesOriginLocation;
					
					dataFactory.httpRequest(URL+'/truckstop/matchLoadDetail/'+truckstopId+'/'+$rootScope.vehicleIdRepeat+'/'+loadId,'POST',{},{ deadmiles: deadmile, calPayments: calPayment, totalCost: totalCost,originPickDate: orignPickDate,driverType:driverType, deadMilesLocation : deadMilesLocation}).then(function(data) {
						
						var modalElem = $('#edit-fetched-load');
						$('#edit-fetched-load').modal('show');
						modalElem.attr({backdrop: 'static'});
						
						$rootScope.editSavedLoad = {};
						$rootScope.editSavedDist = {};
						$rootScope.vehicleInfo = {};
						$rootScope.editSavedLoad = data.encodedJobRecord;
						
						$scope.showEditButton = true;
						if ( $rootScope.editSavedLoad.flag != undefined && $rootScope.editSavedLoad.flag == '1' && data.userRoleId != '1' && data.userRoleId != '3' && data.userRoleId != '5' ) {
							$scope.showEditButton = false;
						}
						
						$rootScope.vehicleInfo = data.vehicleInfo;
						$scope.trailerTypes = data.trailerTypes;
						if($rootScope.editSavedLoad.driver_type == "team"){
							$rootScope.driverAssignType = "team";
						}else{
							$rootScope.driverAssignType = "driver";
						}
						$rootScope.deadmileSave = deadmile;
						if ( $rootScope.editSavedLoad.PaymentAmount == 0 || $rootScope.editSavedLoad.PaymentAmount == '' || $rootScope.editSavedLoad.PaymentAmount == undefined ) {
							$rootScope.showPaymentCal = true;
							$rootScope.editSavedLoad.overall_total_rate_mile = parseFloat(calPayment / $rootScope.editSavedLoad.timer_distance).toFixed(2);
							$rootScope.editSavedLoad.PaymentAmount = calPayment;
							$rootScope.editSavedLoad.PaymentAmount1 = calPayment;
						} else {
							$rootScope.showPaymentCal = false;
						}
						
						if ( $rootScope.editSavedLoad.equipment != undefined && $rootScope.editSavedLoad.equipment != '' ) {
							$scope.equipmentValue = $rootScope.editSavedLoad.equipment.replace(',',' ');
						} else {
							$scope.equipmentValue = $rootScope.editSavedLoad.EquipmentTypes.Description.replace(',',' ');
						}
						
						if ( $rootScope.editSavedLoad.vehicle_id != undefined && $rootScope.editSavedLoad.vehicle_id != '' && $rootScope.editSavedLoad.vehicle_id != 0  ) {
							$rootScope.selectedVehicleId = $rootScope.editSavedLoad.vehicle_id;	
							$rootScope.vehicleIdSelected = $rootScope.editSavedLoad.vehicle_id;
							$rootScope.vehicleIdRepeat = $rootScope.editSavedLoad.vehicle_id;
							$rootScope.driverAssignValue = $rootScope.languageCommonVariables.assigned;		
							
							if(data.encodedJobRecord.driver_type == "team"){
								$rootScope.vehicleInfo.dName = data.vehicleInfo.driverName;	
							} else {
								if(data.vehicleInfo.first_name !== undefined ){
									$rootScope.vehicleInfo.dName = data.vehicleInfo.first_name + " " + data.vehicleInfo.last_name + " - "+ data.vehicleInfo.label; 		
								} else {
									$rootScope.vehicleInfo.dName = '';
								}
							}
							
							$rootScope.setVehicleIdFlag = $rootScope.editSavedLoad.vehicle_id;				// set flag for myLoad page to check if vehicle-driver assigment is changed or not				
						} else {
							$rootScope.editSavedLoad.vehicle_id = '';
							$rootScope.selectedVehicleId = '';
							$rootScope.driverAssignValue = $rootScope.languageCommonVariables.popupunassignd;
							$rootScope.driverPlaceholder = '';
							$rootScope.setVehicleIdFlag = 0;
						}
						
						if ( $rootScope.editSavedLoad.shipper_entity != undefined && $rootScope.editSavedLoad.shipper_entity != '' ) { // check value for shipper exist if not then assign initially
							$rootScope.shipperentity = $rootScope.editSavedLoad.shipper_entity;
						} else {
							$rootScope.editSavedLoad.shipper_entity = 'shipper';	
							$rootScope.shipperentity = 'shipper';	
						}
						
						
						if ( $rootScope.editSavedLoad.consignee_entity != undefined && $rootScope.editSavedLoad.consignee_entity != '' ) { // check value for consignee exist if not then assign initially
							$rootScope.consigneeentity = $rootScope.editSavedLoad.consignee_entity;
						} else {
							$rootScope.editSavedLoad.consignee_entity = 'consignee';
							$rootScope.consigneeentity = 'Consignee';	
						}		
																	
						if ( $rootScope.editSavedLoad.PickupDate == '' || $rootScope.editSavedLoad.PickupDate == '0000-00-00' ||  $rootScope.editSavedLoad.PickupDate.toLowerCase() == 'daily' ) {
							if($rootScope.editSavedLoad.pickDate != undefined && $rootScope.editSavedLoad.pickDate.indexOf("/") != -1){
								var dateArray = $rootScope.editSavedLoad.pickDate.split('/');
								var year = '';
								if(dateArray[2].length == 2){
									 year = '20'+dateArray[2];
								} else {
									year = dateArray[2];
								}
								pickUpDate = year+'-'+dateArray[0]+'-'+dateArray[1];
                            } else {
								pickUpDate = '';
							}
                           $rootScope.editSavedLoad.PickupDate = pickUpDate;
						}
						
						$rootScope.calPaymentSaved = calPayment;
						if ( $rootScope.editSavedLoad.Stops == null ) {
							$rootScope.editSavedLoad.Stops = 0;
						}
						
						$rootScope.tempstorage = {};
						$rootScope.tempstorage.originTT = $rootScope.editSavedLoad.PickupTime;
						$rootScope.tempstorage.originTT_range_end = $rootScope.editSavedLoad.PickupTimeRangeEnd;
						$rootScope.tempstorage.deliveryTT = $rootScope.editSavedLoad.DeliveryTime;
						$rootScope.tempstorage.deliveryTT_range_end = $rootScope.editSavedLoad.DeliveryTimeRangeEnd;
						
						$rootScope.extraStop = [];
						$rootScope.viaRoutes = [];
						$rootScope.extraStops = {};
											
						if ( data.extra_stops_data.length  != 0 ) {
							$rootScope.extraStopTotLength = true;
							$rootScope.extraStopsLength = $rootScope.editSavedLoad.Stops;
							$rootScope.extraStopsTemp =  data.extra_stops_data;
							
							var stopsLength = parseInt($rootScope.extraStopsTemp.length);
								for ( var i = 0; i < stopsLength; i++ ) {
									$rootScope.extraStop.push(i);
									$rootScope.extraStops['extraStopAddress_' + i ] = $rootScope.extraStopsTemp[i].extraStopAddress;
									$rootScope.extraStops['extraStopCity_' + i ] = $rootScope.extraStopsTemp[i].extraStopCity;
									$rootScope.extraStops['extraStopState_' + i ] = $rootScope.extraStopsTemp[i].extraStopState;
									$rootScope.extraStops['extraStopCountry_' + i ] = $rootScope.extraStopsTemp[i].extraStopCountry;
									
									if ( $rootScope.extraStopsTemp[i].extraStopDate == '0000-00-00' ) {
										$rootScope.extraStops['extraStopDate_' + i ] = '';
									} else {
										$rootScope.extraStops['extraStopDate_' + i ] = $rootScope.extraStopsTemp[i].extraStopDate;
									}
									$rootScope.extraStops['extraStopName_' + i ] = $rootScope.extraStopsTemp[i].extraStopName;
									$rootScope.extraStops['extraStopPhone_' + i ] = $rootScope.extraStopsTemp[i].extraStopPhone;
									$rootScope.extraStops['extraStopTime_' + i ] = $rootScope.extraStopsTemp[i].extraStopTime;
									$rootScope.extraStops['extraStopTimeRange_' + i ] = $rootScope.extraStopsTemp[i].extraStopTimeRange;
									$rootScope.extraStops['extraStopZipCode_' + i ] = $rootScope.extraStopsTemp[i].extraStopZipCode;
									$rootScope.extraStops['id_'+ i] = $rootScope.extraStopsTemp[i].id;
									
									if( $rootScope.extraStopsTemp[i].extraStopEntity != '' ) {
										if ($rootScope.extraStopsTemp[i].extraStopEntity == 'shipper' ) {
											$rootScope.extraStops['extraStopEntity_' + i ] = { 'val' : 'Shipper','key' : 'shipper'};
										} else {
											$rootScope.extraStops['extraStopEntity_' + i ] = { 'val' : 'Consignee','key' : 'consignee'};
										} 
									}
														
									$rootScope.viaRoutes.push($rootScope.extraStopsTemp[i].extraStopAddress);
								}
								
								LoopForAutoComplete(stopsLength);
								function LoopForAutoComplete (j) {  
									setTimeout(function () { 
										$rootScope.extraStopsAutoAddress(j, 'editRequest');  
										if (--j > 0 ) {
											LoopForAutoComplete(j);      //  decrement i and call myLoop again if i > 0
										}
								   }, 500)
								}
						}
						
						if ( $rootScope.editSavedLoad.id < 9999 ) 
							$rootScope.notShowPlusIcon = false;							// hiding truckstop icon for load added from itsd.com or having load id less than 9999
							
						$rootScope.editSavedDist = data.distance;
						$rootScope.primaryLoadId = data.primaryLoadId;
											
						if (Object.keys($rootScope.vehicleInfo).length > 0 ) {
							$rootScope.vehicleDriverFound = true;
							
							if ( $rootScope.vehicleInfo.vehicle_image == '' ) 
								$rootScope.defaultVehicleImage = true;
							
							if ( $rootScope.vehicleInfo.profile_image == '' ) 
								$rootScope.defaultDriverImage = true;
						}
						
						$rootScope.listDrivers = data.driversList;
						
						$rootScope.driverPlaceholder = ($rootScope.editSavedLoad.assignedDriverName != null && $rootScope.editSavedLoad.assignedDriverName != '' ) ? $rootScope.editSavedLoad.assignedDriverName : 'Unassign';
						/** Three dropdowns on edit page use */
						$rootScope.driver_idName = ($rootScope.editSavedLoad.driver_id != undefined && $rootScope.editSavedLoad.driver_id != 0 ) ? $rootScope.editSavedLoad.assigedDriverFullName : 'Select Driver';
						$rootScope.dispatcher_idName = ($rootScope.editSavedLoad.dispatcher_id != undefined && $rootScope.editSavedLoad.dispatcher_id != 0 ) ? $rootScope.editSavedLoad.username: 'Select Dispatcher';
						$rootScope.vehicle_idName = ($rootScope.editSavedLoad.vehicle_id != undefined && $rootScope.editSavedLoad.vehicle_id != 0 ) ? $rootScope.editSavedLoad.assignedTruckLabel: 'Select Vehicle';
						
						$rootScope.dispatchersList = [];
						$rootScope.vehiclesList = [];
						$rootScope.driversListNew = [];
						$rootScope.dispatchersList = data.dispatchersList;
						$rootScope.vehiclesList = data.vehiclesList;
						$rootScope.driversListNew = data.driversListNew;
						/** Three dropdowns on edit page use */
						
						//----------drop-down ---------------------------
						$rootScope.jobPlaceholder = $rootScope.editSavedLoad.JobStatus;
						
						if ($rootScope.jobPlaceholder === '') {
							$rootScope.jobPlaceholder = $rootScope.languageCommonVariables.jobStatusPlaceholder;
						}else {
							$rootScope.jobPlaceholder = $rootScope.languageCommonVariables[$rootScope.jobPlaceholder];
						}
						//----------drop-down ---------------------------
						
						$rootScope.showhighlighted = 'loadDetail';
						$rootScope.Message = '';
						$rootScope.editLoads = true;
						$rootScope.matchingTrucks = false;
						$rootScope.showMaps = false;

						$rootScope.rateSheetUploaded = data.rateSheetUploaded;
						if ( $rootScope.editSavedLoad.MCNumber != undefined && $rootScope.editSavedLoad.MCNumber != null && $rootScope.editSavedLoad.MCNumber != 0 ) {	
							$rootScope.fetch_triumph_request($rootScope.editSavedLoad.MCNumber,$rootScope.editSavedLoad.DOTNumber);
						} else {
							$rootScope.showBrokerStatus = '';
						}

						if ( loadId == '' || loadId == undefined ) {
							$rootScope.firstTimeClick = false;
						}
						
						$timeout(function(){ $rootScope.disableInputs(); }, 400);
						
					});
			} 
		/**Load Detail Popup ends **/
		
	/**
	 *  Fetch Triumph Request
	 */
	  
	$rootScope.fetch_triumph_request = function( mcNumber, usDot ) {
		dataFactory.httpRequest(URL+'/triumph/index/'+mcNumber+'/'+usDot).then(function(data) {
			if ( data.length == 0 ) {
				mc_status = 'Not Available';
			} else {
				if ( data.creditResultTypeId.name == 'Credit Request Approved' ) {
					mc_status = 'Approved';
				} else {
					mc_status = 'Not Approved';
				}
				
				if ( data.newMethodInfo != undefined && data.newMethodInfo.Customers != undefined && data.newMethodInfo.Customers.length > 0 ) {
					$rootScope.editSavedLoad.TruckCompanyName = data.newMethodInfo.Customers[0].Name;
					$rootScope.editSavedLoad.postingAddress = data.newMethodInfo.Customers[0].Addr1;
					$rootScope.editSavedLoad.city = data.newMethodInfo.Customers[0].City;
					$rootScope.editSavedLoad.state = data.newMethodInfo.Customers[0].State;
					$rootScope.editSavedLoad.zipcode = data.newMethodInfo.Customers[0].ZipCode;
					$rootScope.editSavedLoad.DebtorKey = data.newMethodInfo.Customers[0].DebtorKey;
					$rootScope.dataNotFound = false;
				} else if ( data.companyName != null && data.phone != null ) {
					$rootScope.editSavedLoad.TruckCompanyName = data.companyName;
					$rootScope.editSavedLoad.city = data.city;
					$rootScope.editSavedLoad.state = data.state;
				}
			}
			$rootScope.editSavedLoad.brokerStatus = mc_status  ;
			$rootScope.showBrokerStatus = (mc_status == "Approved") ? $rootScope.languageCommonVariables.statusApproved : $rootScope.languageCommonVariables.statusDeclined;
		});
		
	}
	
	/**
	 * Changing dispatcher , driver and vehicles dropdown
	 */
	 
	$scope.onSelectChangeListsCommon = function(item, model_name) {
		if ( model_name == 'driver_id' ) {
			if ( item.label == '_team' ) {
				$rootScope.editSavedLoad[model_name] = item.id;
				$rootScope.editSavedLoad.second_driver_id = item.team_driver_id;
			} else {
				$rootScope.editSavedLoad[model_name] = item.id;
				$rootScope.editSavedLoad.second_driver_id = 0;
			}
			$rootScope[model_name+'Name'] = item.driverName;
			
			if ( $rootScope.vehicleInfo.label != undefined && $rootScope.vehicleInfo.label != '' )
				$rootScope.driverPlaceholder = item.driverName + '-' + $rootScope.vehicleInfo.label;
			else
				$rootScope.driverPlaceholder = item.driverName;
		} else if ( model_name == 'dispatcher_id' ) {
			$rootScope.editSavedLoad[model_name] = item.dispId;
			$rootScope[model_name+'Name'] = item.username;
		} else {
			$rootScope.editSavedLoad[model_name] = item.id;
			$rootScope[model_name+'Name'] = item.vehicleLabel;
			
			if ( item.id != '' ) {
				$scope.autoFetchLoads = true;
				dataFactory.httpRequest(URL+'/truckstop/FetchVehicleInfo/'+item.id).then(function(data) {
					if ( data.vehicleInfo != undefined && data.vehicleInfo.length != 0 ) {
						$rootScope.vehicleInfo = {};
						$rootScope.vehicleInfo = data.vehicleInfo;
						
						if ( $rootScope.editSavedLoad.driver_id != undefined && $rootScope.editSavedLoad.driver_id != 0 && $rootScope.editSavedLoad.driver_id != '' )
							$rootScope.driverPlaceholder = $rootScope.driver_idName + '-' + $rootScope.vehicleInfo.label;
					}
					
					$scope.autoFetchLoads = false;
				});
				
			} 
			
		}
	}
	 

	/**
	 * Reclick on load detail popup
	 */
	  
	$scope.editSaveLoadTemp = function(truckstopId,loadId, deadmile,calPayment,totalCost,orignPickDate, parameter) {
		
		if ( loadId != '' && loadId != undefined ) {
			if ( parameter == 'addRequest' )
				encodedUrl = btoa(truckstopId+'-'+loadId+'-'+deadmile+'-'+calPayment+'-'+totalCost+'-'+orignPickDate+'-'+parameter);
			else
				encodedUrl = btoa(truckstopId+'-'+loadId+'-'+deadmile+'-'+calPayment+'-'+totalCost+'-'+orignPickDate);
				
			$rootScope.editSaveLoad(2,encodedUrl);
		} else {
			$rootScope.alertloadmsg = false;
			$rootScope.alertExceedMsg = false;
			$rootScope.editLoads = true;
			$rootScope.matchingTrucks   = false;
			$rootScope.showMaps  = false;
			$rootScope.brokerDetailInfo  = false;
			$rootScope.billingDetailsInfo = false;
			$rootScope.showhighlighted = 'loadDetail';
			
			$rootScope.save_cancel_div  = false;
			$rootScope.save_edit_div = true;
			$rootScope.showFormClass = true;
			
			initAutocomplete();
			$rootScope.disableInputs();	
		}
	}

	$scope.printLoadsInfo = function( TS_id, id) {
		var newWindowUrl = URL;
		// switch(infoType){
		// 	case 'loadDetail':
		// 		newWindowUrl +='/truckstop/printLoadDetails/'+editSavedLoad+'/'+id;
		// 	break;
		// 	case 'brocker':
		// 		newWindowUrl += '/assignedloads/PrintBrokersDetails/'+id;				
		// 	break;
		// 	case 'tripDetails':
		// 		newWindowUrl += '/truckstop/printTripDetails/'+id;				
		// 	break;
		// }
		
		// localStorage.setItem('check',JSON.stringify($rootScope.editSavedLoad));
		
		if (id != undefined && id != '' && id != 0 ) {
			$cookies.put('printTicket','');
		} else {
			$cookies.put('printTicket',JSON.stringify($rootScope.editSavedLoad));
		}
		newWindowUrl +='/truckstop/printLoadDetails/'+TS_id+'/'+id;		
		var winPrint = $window.open(newWindowUrl,"Map");
		// winPrint.focus();
		setTimeout(function () {winPrint.print();},1000);
		return false;
	};
	
	/**
	 * change driver dropdown
	 **/
	$rootScope.onSelectChangeDriverCallback = function (item, model, parameter){
		$rootScope.driverAssignType = "";
		if ( item.id == '' || item.id == undefined ) {
			
			$rootScope.vehicleDriverFound = false;
			$rootScope.editSavedLoad.driver_id = '';
			$rootScope.editSavedLoad.vehicle_id = '';
			$rootScope.editSavedLoad.trailer_id = '';
			$rootScope.vehicleIdRepeat = '';
			$rootScope.driverPlaceholder = '';
			$rootScope.driverAssignValue = $rootScope.languageCommonVariables.popupunassignd;
			$rootScope.editSavedLoad.second_driver_id = 0;
			$rootScope.editSavedLoad.dispatcher_id = 0;
			
			$rootScope.dispatcher_idName = 'Select Dispatcher';
			$rootScope.driver_idName = 'Select Driver';
			$rootScope.vehicle_idName = 'Select Vehicle';
			
			if ( parameter == 'addRequest' ) {   // for adding new Load
				$rootScope.setAddVehicleId = '';   
				$rootScope.changeAddMilesDistanceNew('origin','notSearchZip');
			} else {
				$scope.autoFetchLoads = true;
				dataFactory.httpRequest(URL+'/truckstop/UnassignTruckToDriver/','POST',{},{allData : $rootScope.editSavedLoad,driverAssignType:$rootScope.driverAssignType}).then(function(dataRes) {
					$rootScope.editSavedLoad.Mileage = dataRes.distance;
					$rootScope.editSavedLoad.timer_distance = dataRes.distance;
					$rootScope.editSavedLoad.deadmiles = dataRes.new_deadmiles_Cal;
					
					$rootScope.editSavedLoad.loadedDistanceCost = dataRes.loadedDistanceCost;
					$rootScope.editSavedLoad.deadMileDistCost = dataRes.deadMileDistCost;
					$rootScope.editSavedLoad.estimatedFuelCost = dataRes.estimatedFuelCost;
					
					$rootScope.editSavedLoad.totalMiles = parseFloat( dataRes.distance)  + parseFloat($rootScope.editSavedLoad.deadmiles );
					$rootScope.editSavedLoad.totalCost = dataRes.overall_total_charge_Cal;
					$rootScope.editSavedLoad.overall_total_rate_mile = dataRes.overall_total_rate_mile_Cal;
					$rootScope.editSavedLoad.overallTotalProfit = dataRes.overall_total_profit_Cal;
					$rootScope.editSavedLoad.overallTotalProfitPercent = dataRes.overall_total_profit_percent_Cal;
					
					$rootScope.vehicleIdRepeat = 0;
					$scope.autoFetchLoads = false;
				});
			}
			$rootScope.disableAnotherDropdowns = true;
					
		} else {
			$rootScope.editSavedLoad.driver_id = item.id;
			$rootScope.driverPlaceholder = item.driverName;

			$rootScope.driverAssignType = item.label == "_team" ? "team" : "driver";
			
			dataFactory.httpRequest(URL+'/truckstop/assignTruckToDriver/'+item.id,'POST',{},{allData : $rootScope.editSavedLoad,driverAssignType:$rootScope.driverAssignType}).then(function(dataRes) {
				if ( dataRes ) {

					$rootScope.vehicleInfo = dataRes.vehicleDetail;
					if(item.label == "_team"){
						$rootScope.vehicleInfo.dName = dataRes.vehicleDetail.driverName;
						$rootScope.editSavedLoad.second_driver_id = dataRes.vehicleDetail.team_driver_id;
					}else{
						$rootScope.vehicleInfo.dName = dataRes.vehicleDetail.dName;
						$rootScope.editSavedLoad.second_driver_id = 0;
					}
					$rootScope.editSavedLoad.vehicle_id = dataRes.vehicleDetail.assignedVehicleId;
					$rootScope.editSavedLoad.trailer_id = dataRes.vehicleDetail.trailerId;				// assigning trailer Id
					$rootScope.editSavedLoad.dispatcher_id = dataRes.vehicleDetail.dispatcher_id;				// assigning trailer Id
					if ( $rootScope.vehicleInfo.vehicle_image == '' ) 
						$rootScope.defaultVehicleImage = true;
						
					if ( $rootScope.vehicleInfo.profile_image == '' ) 
						$rootScope.defaultDriverImage = true;
						
					$rootScope.vehicleDriverFound = true;
					
					$rootScope.globalVehicleId = dataRes.vehicleDetail.assignedVehicleId; 			// for billing load
					$rootScope.selectedVehicleId = dataRes.vehicleDetail.assignedVehicleId; 			// for truckstop controller load
					$rootScope.vehicleIdSelected = dataRes.vehicleDetail.assignedVehicleId; 			// for assigned loads controller and iterationsLoads controller
					
					if ( parameter == 'addRequest' ) {   // for adding new Load
						$rootScope.setAddVehicleId = dataRes.vehicleDetail.assignedVehicleId;   
						$rootScope.changeAddMilesDistanceNew('origin','notSearchZip');	// notSearchZip is parameter to avoid hitting zip code api on change of truck or assigning truck
					} else if( parameter == 'editRequest') {
						$rootScope.dispatcher_idName = dataRes.vehicleDetail.username;
						if(item.label == "_team")
							$rootScope.driver_idName = dataRes.vehicleDetail.assignedTeamName;
						else
							$rootScope.driver_idName = dataRes.vehicleDetail.assignedDrivername;
							
						$rootScope.vehicle_idName = dataRes.vehicleDetail.assignedVehicleName;
						$rootScope.changeMilesDistanceNew('origin',$rootScope.editSavedLoad.OriginState,'notSearchZip');
					}
					
					if ( dataRes.error != '' && dataRes.error == 'alreadyBookedPickDate') {
						$rootScope.alertExceedMsg = true;
						$rootScope.ExceedMessage = 'Caution: This driver is already assigned with load for '+$rootScope.editSavedLoad.PickupDate+'.';
					} else {
						$rootScope.alertExceedMsg = false;
					}
					
				}
			});
			$rootScope.disableAnotherDropdowns = false;
			
			$rootScope.driverAssignValue = $rootScope.languageCommonVariables.assigned;
		}
	}
		
	/**
	 * On change of origin and destination street address recalulating results after half seconds for binding values
	 **/
	$scope.changeMilesDistance = function(addType,address) {
		//~ $scope.autoFetchLoads = true;
		setTimeout(function(){
			$rootScope.changeMilesDistanceNew(addType,address,'searchZip');						// SearchZip is parameter to hit zip code api on changing or modifying pickup or destination address
		},500);
	}
	
	$rootScope.changeMilesDistanceNew = function(addType, address, zipParameter) {
		
		if (address != undefined ) {
			$scope.autoFetchLoads = true;
		
				dataFactory.httpRequest(URL+'/truckstop/calculateNewDistance/'+$rootScope.primaryLoadId+'/'+$scope.primaryTripDetailId,'POST',{},{ allData : $rootScope.editSavedLoad,vehicleId : $rootScope.editSavedLoad.vehicle_id, searchType : addType, extraStopsAdded : $rootScope.extraStops, deadMileCalculate : $rootScope.iterationPopData, setDeadMilePage : $rootScope.setDeadMilePage , leftSideColumnPlanPage : $scope.setChainArrayValue, driverAssignType:$rootScope.driverAssignType}).then(function(dataRes) {
					if ( dataRes.distance != '' && dataRes.distance != undefined && dataRes.distance != null ) {
						$rootScope.editSavedLoad.Mileage = dataRes.distance;
						$rootScope.editSavedLoad.timer_distance = dataRes.distance;
						$rootScope.editSavedLoad.deadmiles = dataRes.new_deadmiles_Cal;
						
						$rootScope.editSavedLoad.loadedDistanceCost = dataRes.loadedDistanceCost;
						$rootScope.editSavedLoad.deadMileDistCost = dataRes.deadMileDistCost;
						$rootScope.editSavedLoad.estimatedFuelCost = dataRes.estimatedFuelCost;
						
						$rootScope.editSavedLoad.totalMiles = parseFloat( dataRes.distance)  + parseFloat($rootScope.editSavedLoad.deadmiles );
						$rootScope.editSavedLoad.totalCost = dataRes.overall_total_charge_Cal;
						$rootScope.editSavedLoad.overall_total_rate_mile = dataRes.overall_total_rate_mile_Cal;
						$rootScope.editSavedLoad.overallTotalProfit = dataRes.overall_total_profit_Cal;
						$rootScope.editSavedLoad.overallTotalProfitPercent = dataRes.overall_total_profit_percent_Cal;
					}
					$scope.autoFetchLoads = false;
				});
			
		} else {
			$scope.autoFetchLoads = false;
		}
	}
	
	/**Disabling input fiels **/
	
	$rootScope.disableInputs  = function() {
		$(".enable-disable-inputs").find("input,textarea,select").attr("disabled", true);
	}
	
	$rootScope.enableInputs  = function() {
		$(".enable-disable-inputs").find("input,textarea,select").attr("disabled", false);
	}
	
	/**
	 * Showing Broker info tab
	 */
	  
	$scope.showBrokerInfo = function( truckstopId,loadId ) {
			
		$rootScope.alertloadmsg = false;
		$rootScope.alertExceedMsg = false;
		$rootScope.editLoads = false;
		$rootScope.showMaps  = false;
		$rootScope.billingDetailsInfo = false;
		$rootScope.matchingTrucks   = false;
		$rootScope.brokerDetailInfo  = true;		
		$rootScope.showhighlighted = 'brokerDetails';
		
		$rootScope.uploadBrokerDoc = false;
		$rootScope.showUploadBrokerButton = true;
		
		if ( $rootScope.editSavedLoad.broker_id != '' && $rootScope.editSavedLoad.broker_id != undefined && $rootScope.editSavedLoad.broker_id != null ) {
			dataFactory.httpRequest(URL+'/brokers/getBrokerDocumentUploaded/'+$rootScope.editSavedLoad.broker_id).then(function(data) {					// Fetching broker document uploaded information
				//~ $rootScope.BrokerDocUploaded = data.BrokerDocUploaded;
				if ( data.brokerDocuments != undefined && data.brokerDocuments.length > 0 ) {
					$scope.brokerDocuments = data.brokerDocuments;
				} else {
					$scope.brokerDocuments = [];
				}
			});
		}
		
		$scope.showBrokerDropdown = true;				// showing broker dropdown on editing new load
		if ( $rootScope.editSavedLoad.load_source != undefined && $rootScope.editSavedLoad.load_source != '' && $rootScope.editSavedLoad.load_source == 'Vika Dispatch' ) {
			dataFactory.httpRequest(URL+'/assignedloads/getBrokersList/'+loadId).then(function(data) {					// Fetching brokers info to show in dropdown
				$scope.brokersList = data.brokersList;
				if ( data.brokerLoadDetail.length != 0 ) {
					$rootScope.editBrokerLoadInfo = data.brokerLoadDetail;
					$scope.selectedBrokerName = data.brokerLoadDetail.TruckCompanyName;
					$scope.brokerSelectedId = data.brokerLoadDetail.id;
				}
			});
		}
		
		
		
	}
	
	/**
	 * Autocomplete for pickup and destination address
	 **/
	function initAutocomplete() {

		var input = document.getElementById("autocompleteAdd");

		var autocomplete = new google.maps.places.Autocomplete(input,$scope.limitCountryOptions);
		google.maps.event.addListener(autocomplete, 'place_changed', function () {
		// var places = autocomplete.getPlace();
			var result = autocomplete.getPlace();
			
			var street = street_number = postal_code = city = state = county = country = "";
			
			if(result.address_components){

				for(var i = 0; i < result.address_components.length; i += 1) {
				  var addressObj = result.address_components[i];

				  for(var j = 0; j < addressObj.types.length; j += 1) {
					
					switch(addressObj.types[j]){
						case "country": 			 		 country = addressObj.long_name;		break;	//Country
						case "administrative_area_level_1" : state 	= addressObj.short_name; 		break; 	//State
						case "administrative_area_level_2" : county = addressObj.long_name; 		break;	//County
						case "locality" :  					 city 	= addressObj.long_name; 		break;	//City
						case "route": 						 street = addressObj.long_name;			break;	//street
						case "street_number": 				 street_number =  addressObj.long_name;	break;	//street number
						case "postal_code": 				 postal_code = addressObj.long_name; 	break;	//Postal Code
					}
				  }
				}
				
			} else {
				if ( result.name != '' ) {
					var pickupaddress = result.name.split(',');
					lastItem = pickupaddress.pop();
					country = lastItem;
					
					secondLast = pickupaddress.pop();
					var checkInteger = /\d/.test(secondLast);
					if ( checkInteger ) {
						var splitedValue = secondLast.split(' ');
						
						if ( isNaN(splitedValue[0]) ) {
							state = splitedValue[0];
							postal_code = splitedValue[1];
						} else {
							state = splitedValue[1];
							postal_code = splitedValue[0];
						}
					} else {
						state = secondLast;
					}
					
					thirdLast = pickupaddress.pop();
					city = thirdLast;
					
					if ( pickupaddress.length ) {
						for( var i = 0; i < pickupaddress.length; i += 1 ){
							street += pickupaddress[i]+',';
						}
						street = street.replace(/,(?=[^,]*$)/, '');
					}
				}
			}
			$rootScope.editSavedLoad.PickupAddress 	= street_number + " " + street ;			
			$rootScope.editSavedLoad.OriginStreet 	= street_number + " " + street ;	
			$rootScope.editSavedLoad.OriginCity 	= city ;
			$rootScope.editSavedLoad.OriginState 	= state;
			$rootScope.editSavedLoad.OriginCountry 	= country;

		var sendRequest = 0;
			if((street != "" || street_number != "") && postal_code != ""){
				$rootScope.editSavedLoad.OriginZip = postal_code;
				sendRequest = 1;
			}else if((street != "" || street_number != "") && postal_code == ""){
				$scope.autoFetchLoads = true;					
				var fullAddress = $rootScope.editSavedLoad.PickupAddress+','+$rootScope.editSavedLoad.OriginCity+','+$rootScope.editSavedLoad.OriginState+','+$rootScope.editSavedLoad.OriginCountry;
				dataFactory.httpRequest(URL+'/truckstop/getZipCode','POST',{},{address: fullAddress}).then(function(data) {
					if ( data.zipcode != undefined && data.zipcode != '' ) {
							$rootScope.editSavedLoad.OriginZip = data.zipcode;
							//$rootScope.editSavedLoad.DestinationZip = data.zipcode;
					}
					$scope.autoFetchLoads = false;					
				});
				sendRequest = 1;
			}else{
				$rootScope.editSavedLoad.OriginZip = "";
				sendRequest = 1;
			}
			$rootScope.$apply();
			
			if ( sendRequest == 1 ) 
				$scope.changeMilesDistance('origin',$rootScope.editSavedLoad.PickupAddress);
		});
		$scope.destAddress();
	}
	
	$scope.destAddress = function() {

		var destAddress 		= document.getElementById("destinationAddress");
		var autocompleteDest 	= new google.maps.places.Autocomplete(destAddress,$scope.limitCountryOptions);
		google.maps.event.addListener(autocompleteDest, 'place_changed', function () {
		var result = autocompleteDest.getPlace();
		
		var street = street_number = postal_code= city = state = county = country = "";

		if(result.address_components){

			for(var i = 0; i < result.address_components.length; i += 1) {

			  var addressObj = result.address_components[i];

			  for(var j = 0; j < addressObj.types.length; j += 1) {

			    switch(addressObj.types[j]){
			  		case "country": 			 		 country= addressObj.long_name; 		break;	//Country
			  		case "administrative_area_level_1" : state 	= addressObj.short_name;  		break; 	//State
			  		case "administrative_area_level_2" : county = addressObj.long_name; 		break;	//County
			  		case "locality" :  					 city 	= addressObj.long_name; 		break;	//City
			  		case "route": 						 street = addressObj.long_name;			break;	//street
			  		case "street_number": 				 street_number =  addressObj.long_name;	break;	//street number
			  		case "postal_code": 				 postal_code = addressObj.long_name; 	break;	//Postal Code
			  	}
			  }
			}
		} else {
			if ( result.name != '' ) {
				var pickupaddress = result.name.split(',');
				lastItem = pickupaddress.pop();
				country = lastItem;
				
				secondLast = pickupaddress.pop();
				var checkInteger = /\d/.test(secondLast);
				if ( checkInteger ) {
					var splitedValue = secondLast.split(' ');
					
					if ( isNaN(splitedValue[0]) ) {
						state = splitedValue[0];
						postal_code = splitedValue[1];
					} else {
						state = splitedValue[1];
						postal_code = splitedValue[0];
					}
				} else {
					state = secondLast;
				}
				
				thirdLast = pickupaddress.pop();
				city = thirdLast;
				
				if ( pickupaddress.length ) {
					for( var i = 0; i < pickupaddress.length; i += 1 ){
						street += pickupaddress[i]+',';
					}
					street = street.replace(/,(?=[^,]*$)/, '');
				}
			}
		}
		
		$rootScope.editSavedLoad.DestinationAddress = street + " " + street_number;
		$rootScope.editSavedLoad.DestinationStreet 	= street + " " + street_number;
		$rootScope.editSavedLoad.DestinationCity 	= city ;
		$rootScope.editSavedLoad.DestinationState 	= state;
		$rootScope.editSavedLoad.DestinationCountry = country;

		var sendRequest = 0;
			if((street != "" || street_number != "") && postal_code != ""){
				$rootScope.editSavedLoad.DestinationZip = postal_code;
				sendRequest = 1;
			}else if((street != "" || street_number != "") && postal_code == ""){
				$scope.autoFetchLoads = true;					
				var fullAddress = $rootScope.editSavedLoad.DestinationAddress+','+$rootScope.editSavedLoad.DestinationCity+','+$rootScope.editSavedLoad.DestinationState+','+$rootScope.editSavedLoad.DestinationCountry;
				dataFactory.httpRequest(URL+'/truckstop/getZipCode','POST',{},{address: fullAddress}).then(function(data) {
					if ( data.zipcode != undefined && data.zipcode != '' ) {
							$rootScope.editSavedLoad.DestinationZip = data.zipcode;
							//$rootScope.editSavedLoad.DestinationZip = data.zipcode;
					}
					$scope.autoFetchLoads = false;					
				});
				sendRequest = 1;
		  	}else{
		  		$rootScope.editSavedLoad.DestinationZip = "";
		  		sendRequest = 1;
		  	}
		  	$rootScope.$apply();
		  	
		  	if ( sendRequest == 1 ) 
				$scope.changeMilesDistance('origin',$rootScope.editSavedLoad.DestinationAddress);
		});
	}
   
	/**
	 *  Save Load after entering w/o number
	 */ 
	
	$scope.enteredWOnumber = function() {
		angular.element("#enterWOnumber").modal('hide');
		$('#enterWOnumber').on('hidden.bs.modal', function (event) {
			$("body").addClass('modal-open');
		});
		
		if ( $rootScope.editSavedLoad.woRefno != '' && $rootScope.editSavedLoad.woRefno != null && $rootScope.editSavedLoad.woRefno != undefined ) {
			$rootScope.saveEditLoad($rootScope.editSavedLoad,'status');
		} else {
			angular.element($('#edit-fetched-load')).animate({ scrollTop: 0 }, 'slow');
			$rootScope.alertloadmsg = false;
			$rootScope.alertExceedMsg = true;
			$rootScope.ExceedMessage = $rootScope.languageArray.enterWoReference;
			
			if ( status == 'status' ) {
				$rootScope.editSavedLoad.JobStatus = '';
				$scope.duplicatejobstatus = '';
			}
		}
	}
	
	/**
	 * Hiding w/o ref no modal
	 */
		
	$scope.hidewORefNo = function() {
		angular.element("#enterWOnumber").modal('hide');
		$('#enterWOnumber').on('hidden.bs.modal', function (event) {
			$("body").addClass('modal-open');
		});
	}
	
	/***SHowing map on loads pages*/
	$rootScope.showRelatedMap = function( loadId, parameter ) {
		$rootScope.stops = 'Fuelstops';
		$rootScope.showhighlighted = 'showMap';
		$rootScope.showAddhighlighted = 'showMap';
		$rootScope.editLoads = false;
		$rootScope.showMaps = true;
		$rootScope.billingDetailsInfo = false;
		$rootScope.brokerDetailInfo = false;
		$rootScope.matchingTrucks = false;
		$rootScope.alertloadmsg = false;
		$rootScope.alertExceedMsg = false;
		
		$scope.streetAddress 	= ( $rootScope.editSavedLoad.PickupAddress != undefined && $rootScope.editSavedLoad.PickupAddress != '' ) ? $rootScope.editSavedLoad.PickupAddress.trim() : '';
		$scope.cityName 		= ( $rootScope.editSavedLoad.OriginCity != undefined && $rootScope.editSavedLoad.OriginCity != '' ) ? $rootScope.editSavedLoad.OriginCity : '';
		$scope.stateName	 	= ( $rootScope.editSavedLoad.OriginState != undefined && $rootScope.editSavedLoad.OriginState != '' ) ? $rootScope.editSavedLoad.OriginState : '';
		$scope.countryName		= ( $rootScope.editSavedLoad.OriginCountry != undefined && $rootScope.editSavedLoad.OriginCountry != '' ) ? $rootScope.editSavedLoad.OriginCountry.trim() : '';
		
		$scope.destStreetAddress = ( $rootScope.editSavedLoad.DestinationAddress != undefined && $rootScope.editSavedLoad.DestinationAddress != '' ) ? $rootScope.editSavedLoad.DestinationAddress.trim() : '';
		$scope.destCityName 	= ( $rootScope.editSavedLoad.DestinationCity != undefined && $rootScope.editSavedLoad.DestinationCity != '' ) ? $rootScope.editSavedLoad.DestinationCity : '';
		$scope.destStateName	= ( $rootScope.editSavedLoad.DestinationState != undefined && $rootScope.editSavedLoad.DestinationState != '' ) ? $rootScope.editSavedLoad.DestinationState : '';
		$scope.destCountryName	= ( $rootScope.editSavedLoad.DestinationCountry != undefined && $rootScope.editSavedLoad.DestinationCountry != '' ) ? $rootScope.editSavedLoad.DestinationCountry.trim() : '';
		
		$rootScope.origin  = $scope.streetAddress+','+$scope.cityName+','+$scope.stateName+','+$scope.countryName;
		$rootScope.origin = $rootScope.origin.replace(/(^,)|(,$)/g, "");
		
		$rootScope.destination  = $scope.destStreetAddress+','+$scope.destCityName+','+$scope.destStateName+','+$scope.destCountryName;
		$rootScope.destination = $rootScope.destination.replace(/(^,)|(,$)/g, "");
		
		if( $rootScope.origin != '' && $rootScope.origin != ',' && $rootScope.destinaton != '' && $rootScope.destination != ',') {
			$rootScope.showNoRoutesSelected = false;
		} else {
			$rootScope.showNoRoutesSelected = true;					// to show empty div in routes tab in load is not saved
			$rootScope.alertExceedMsg = true;
			$rootScope.ExceedMessage = 'Error !: Please enter the pickup and destination address to view routes.';
			return false;	
		}
				
        var waypts = [];
		for (var i = 0; i < $rootScope.viaRoutes.length; i++) {
			waypts.push({
				location: $rootScope.viaRoutes[i],
				stopover: true
			});
		}
		if( parameter == 'addRequest' ) 
			var panel = document.getElementById('panelAdd');
		else
			var panel = document.getElementById('panel');
			
  		panel.innerHTML = '';
		var mapOptions = {
			zoom: 13,
			scrollwheel: false,
			scaleControl: false,
			center: new google.maps.LatLng(37.09024, -95.712891),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		
		if ( parameter == 'addRequest' )
			$rootScope.mapStop = new google.maps.Map(document.getElementById('mapAdd'), mapOptions);
		else
			$rootScope.mapStop = new google.maps.Map(document.getElementById('map'), mapOptions);
			
		//~ $rootScope.mapFuelStop = new google.maps.Map(document.getElementById('Fuelmap'), mapOptions);
	    $rootScope.infowindowStop = new google.maps.InfoWindow();
		$rootScope.directionsServ = new google.maps.DirectionsService();
	   	    
	    $rootScope.directionsTruckstop = new google.maps.DirectionsRenderer({
			map: $rootScope.mapStop,
	        routeIndex: 0,
	        panel:panel
		});
			  
	   	var requestStop = {
			origin: $rootScope.origin,
			destination: $rootScope.destination,
			waypoints: waypts,
			optimizeWaypoints: true,
			travelMode: google.maps.DirectionsTravelMode.DRIVING,
		};
		
		$rootScope.directionsServ.route(requestStop, function(result, status) {
          	if (status == google.maps.DirectionsStatus.OK) {
	            $rootScope.directionsTruckstop.setDirections(result);
	            
	            var currentRouteArray = result.routes[0];
	            var currentRoute = currentRouteArray.overview_path;
	                  
	            coords = [];
	            var j = 0;
	            for (var x = 0; x < currentRoute.length; x++) {
					if( x % 4 == 0 && x != 0) {
						coords[j] = {"lat":currentRoute[x].lat(), "lng":currentRoute[x].lng()}; //Returns the latitude
						j++;
					}
	            }
	            
	            $rootScope.clearOverlays = function() {
				  for (var i = 0; i < $rootScope.markersArray.length; i++ ) {
					$rootScope.markersArray[i].setMap(null);
				  }
				  $rootScope.markersArray.length = 0;
				}
	            $rootScope.markersArray = [];
	            $rootScope.mapChange = false;
	            $rootScope.CheckStop = function(){
					$rootScope.clearOverlays();
					$rootScope.mapChange = !$rootScope.mapChange;
					if($rootScope.mapChange===true){
						$rootScope.stops = 'Fuelstops';
						dataFactory.httpRequest(URL+'/truckstop/get_nearby_tstops','POST',{},{coords: coords,"radius":5.0}).then(function(data) {
							$rootScope.truckStops = data;
							angular.forEach($rootScope.truckStops, function(tstop){
							   $rootScope.createMarker(tstop); 
						   }) 
						});  
					}       
					else{
						$rootScope.stops = 'Truckstops';
						dataFactory.httpRequest(URL+'/truckstop/get_nearby_fstops','POST',{},{coords: coords,"radius":5.0}).then(function(data) {
							$rootScope.truckStops = data;
							angular.forEach($rootScope.truckStops, function(fstop){
								$rootScope.createsecondMarker(fstop); 
							}) 
						});  
					}
				}
				$rootScope.CheckStop();
			}
	        
    	});
  
    	
    	
    	$rootScope.createsecondMarker = function(fstop) {
		
			$rootScope.secondMarker = new google.maps.Marker({
			  map: $rootScope.mapStop,
			  position: {lat: parseFloat(fstop.latitude), lng: parseFloat(fstop.longitude)},
			  icon:"./pages/img/fuelpump.png"
			});
			$rootScope.markersArray.push($rootScope.secondMarker);
			var parking_spaces = (fstop.parking_spaces == 'Yes') ? '<li class="parking-space-li"><span><img src="pages/img/parking-space.png"></span>Parking Space </li>' : ''; 
			var showers = (fstop.showers == 'Yes') ? '<li class="shower-li"><span><img src="assets/images/shower.png"></span>Showers </li>' : ''; 
			var atm = (fstop.atm == 'Yes') ? '<li class="atm-li"><span><img src="pages/img/atm.png"></span>ATM </li>' : ''; 
			var repair = (fstop.repair == 'Yes') ? '<li class="repair-li"><span><img src="pages/img/repair.png"></span>Repair </li>' : ''; 
			var store = (fstop.store == 'Yes') ? '<li class="store-li"><span><img src="pages/img/store.png"></span>Store </li>' : ''; 
			var security = (fstop.security == 'Yes') ? '<li class="security-li"><span><img src="pages/img/security.png"></span>Security </li>' : ''; 
			var internet = (fstop.internet == 'Yes') ? '<li class="internet-li"><span><img src="pages/img/internet.png"></span>Internet </li>' : ''; 	
			var address = fstop.address != '' ? fstop.address+',' : '';
			var amenities = parking_spaces + showers + atm + repair + store + security + internet;
			amenities = amenities != '' ? "<p class='map-amen'><label>Amenities</label></p>" : '';

			if(fstop.fuel_per_gallon_usa != '' && fstop.fuel_per_gallon_usa != 'N/A' && fstop.fuel_per_gallon_usa != '0' ){
				
				google.maps.event.addListener($rootScope.infowindowStop, 'domready', function() {
				var iwOuter = $('.gm-style-iw').addClass("iw-only-markers");
				iwOuter.children(':nth-child(1)').addClass("iw-width");
				var iwBackground = iwOuter.prev();
				iwBackground.children(':nth-child(2)').addClass('iw-background');
				iwBackground.children(':nth-child(4)').addClass('iw-background');
				iwBackground.children(':nth-child(3)').children().children(':nth-child(1)').addClass('iw-pin');
				iwBackground.children(':nth-child(3)').addClass('iw-pin-zindex');
				var iwCloseBtn = iwOuter.next();
				iwCloseBtn.addClass('iw-close-btn');
			});
				google.maps.event.addListener($rootScope.secondMarker, 'click', function() {

				$rootScope.infowindowStop.setContent("<div id='fuel_stop_if' class='fuel_stop_class'><h2 class='iw-title'>"+fstop.name+"</h2>\
								  	<div class='col-lg-12 fstop-address'>\
									  	<div class='col-lg-8 content-popup-section' style ='padding:0px'>\
									  		<p>"+address+ fstop.city+ ", "+ fstop.state + " "+ fstop.zip  +"</p>\
									  		<p class='fstop-phone'><span>Phone: "+fstop.phone+ "</span></p>\
									  	</div>\
									  	<div class='col-lg-4'>\
										  	<div class='diesel-price-popup'>\
												<div class='col-lg-4'>\
													<div class='image-div'>\
														<img src='pages/img/gas-pump.png'>\
														</div>\
													</div>\
													<div class='col-lg-8 f-price'>\
														<p class='per-gallon'><strong>"+parseFloat(fstop.fuel_per_gallon_usa).toFixed(2)+"</strong><br>\
														per gallon</p>\
													</div>\
											  	</div>\
										  	</div>\
									  	</div>\
									</div>\
								  	<div class='col-lg-12 border-dash-outer'>\
								  		"+ amenities +"\
								  		<ul class='popup-li'>\
									  		"+parking_spaces + showers + atm + repair + store + security + internet +"\
									  	</ul>	\
								  	</div>");
				$rootScope.infowindowStop.open($rootScope.mapStop, this);
				});
				
			}
			else{
				
				google.maps.event.addListener($rootScope.secondMarker, 'click', function() {
				$rootScope.infowindowStop.setContent("<div id='fuel_stop_if' class='fuel_stop_class'><h2 class='iw-title'>"+fstop.name+"</h2>\
							  <div class='col-lg-12 content-popup-section' style ='padding:0px'>\
								  <p>"+address+ fstop.city+ ", "+ fstop.state + " "+ fstop.zip  +"</p>\
								  <p><span>Phone: "+fstop.phone+ "</span></p>\
								  </div>\
								  <div class='col-lg-12 border-dash-outer'>\
							  		"+ amenities +"\
							  		<ul class='popup-li'>\
								  		"+parking_spaces + showers + atm + repair + store + security + internet +"\
								  	</ul>\
							  	</div>\
							  </div>");
				
				$rootScope.infowindowStop.open($rootScope.mapStop, this);
				});
			}
		}
		   
    	window.setTimeout(function(){
            //~ google.maps.event.trigger($rootScope.mapFuelStop, 'resize');
            google.maps.event.trigger($rootScope.mapStop, 'resize');
        },100);
	}
	
	
		$rootScope.createMarker = function(tstop) {
			$rootScope.marker = new google.maps.Marker({
			  map: $rootScope.mapStop,
			  position: {lat: parseFloat(tstop.latitude), lng: parseFloat(tstop.longitude)},
			  icon:"./pages/img/truck-stop.png"
			});
			$rootScope.markersArray.push($rootScope.marker);
		google.maps.event.addListener($rootScope.infowindowStop, 'domready', function() {
				var iwOuter = $('.gm-style-iw').addClass("iw-only-markers");
				iwOuter.children(':nth-child(1)').addClass("iw-width");
				var iwBackground = iwOuter.prev();
				iwBackground.children(':nth-child(2)').addClass('iw-background');
				iwBackground.children(':nth-child(4)').addClass('iw-background');
				iwBackground.children(':nth-child(3)').children().children(':nth-child(1)').addClass('iw-pin');
				iwBackground.children(':nth-child(3)').addClass('iw-pin-zindex');
				var iwCloseBtn = iwOuter.next();
				iwCloseBtn.addClass('iw-close-btn');
			});

			google.maps.event.addListener($rootScope.marker, 'click', function() {
			$rootScope.infowindowStop.setContent("<div id='iw-container'><h2 class='iw-title'>"+tstop.name+"</h2>\
											  <div class='col-lg-8' style ='padding:0px'>\
											  <p>"+tstop.address +", "+ tstop.city+ ", "+ tstop.state + " "+ tstop.zip  +"</p>\
											  <p><span>Phone: "+tstop.phone+ "</span> <span>Fax: "+tstop.fax+"</span></p>\
											  </div>\
											  <div class='col-lg-4'>\
												  <div class='diesel-price-popup'>\
													<div class='col-lg-4'>\
													<div class='image-div'>\
														<img src='pages/img/gas-pump.png'>\
														</div>\
													</div>\
													<div class='col-lg-8'>\
														<p class='per-gallon'><strong>2.50</strong><br>\
														per gallon</p>\
													</div>\
												  </div>\
											  </div>\
											  <div class='tool-detail-cover'>\
													<div class='boxes box1'>\
													  <div class='image'><img src='assets/images/Resturent.png'></div>\
														  <div class='content-section'>\
															  <h2>"+tstop.tRestaurants+"</h2>\
															  <p>Restaurant(s)</p>\
														  </div>\
													</div>\
													<div class='boxes'>\
													  <div class='image'><img src='assets/images/parking.png'></div>\
														  <div class='content-section'>\
															  <h2>"+tstop.parking_spaces+"</h2>\
															  <p>Parking<br>Spaces</p>\
														  </div>\
													</div>\
													<div class='boxes box3'>\
													  <div class='image'><img src='assets/images/shower.png'></div>\
														  <div class='content-section'>\
															  <h2>"+tstop.showers+"</h2>\
															  <p>Showers</p>\
														  </div>\
													</div>\
													<div class='boxes'>\
													  <div class='image'><img src='assets/images/fuel.png'></div>\
														  <div class='content-section'>\
															  <h2>"+tstop.diesel_lanes+"</h2>\
															  <p>Diesel Lanes</p>\
														  </div>\
													</div>\
											  </div>\
											  </div>");
			$rootScope.infowindowStop.open($rootScope.mapStop, this);
			});
			$rootScope.infowindowStop.setContent("<div id='iw-container'><h2 class='iw-title'>"+tstop.name+"</h2>\
											  <div class='col-lg-8' style ='padding:0px'>\
											  <p>"+tstop.address +", "+ tstop.city+ ", "+ tstop.state + " "+ tstop.zip  +"</p>\
											  <p><span>Phone: "+tstop.phone+ "</span> <span>Fax: "+tstop.fax+"</span></p>\
											  </div>\
											  <div class='col-lg-4'>\
												  <div class='diesel-price-popup'>\
													<div class='col-lg-4'>\
													<div class='image-div'>\
														<img src='pages/img/gas-pump.png'>\
														</div>\
													</div>\
													<div class='col-lg-8'>\
														<p class='per-gallon'><strong>2.50</strong><br>\
														per gallon</p>\
													</div>\
												  </div>\
											  </div>\
											  <div class='tool-detail-cover'>\
													<div class='boxes box1'>\
													  <div class='image'><img src='assets/images/Resturent.png'></div>\
														  <div class='content-section'>\
															  <h2>"+tstop.tRestaurants+"</h2>\
															  <p>Restaurant(s)</p>\
														  </div>\
													</div>\
													<div class='boxes'>\
													  <div class='image'><img src='assets/images/parking.png'></div>\
														  <div class='content-section'>\
															  <h2>"+tstop.parking_spaces+"</h2>\
															  <p>Parking<br>Spaces</p>\
														  </div>\
													</div>\
													<div class='boxes box3'>\
													  <div class='image'><img src='assets/images/shower.png'></div>\
														  <div class='content-section'>\
															  <h2>"+tstop.showers+"</h2>\
															  <p>Showers</p>\
														  </div>\
													</div>\
													<div class='boxes'>\
													  <div class='image'><img src='assets/images/fuel.png'></div>\
														  <div class='content-section'>\
															  <h2>"+tstop.diesel_lanes+"</h2>\
															  <p>Diesel Lanes</p>\
														  </div>\
													</div>\
											  </div>\
											  </div>");
			$rootScope.infowindowStop.open($rootScope.mapStop,$rootScope.marker);
		}
	/**Showing map on loads pages ends**/
	
	/**Showing routes map directions**/
	
	$scope.showHidePanel = function( parameter ){
		if ( parameter == 'addRequest' ) {
			$("#paneldivAdd").slideToggle();			// for custom added load
			$("#mapAdd").parent(".map-inner").toggleClass("open");
		} else {
			$("#paneldiv").slideToggle();
			$("#map").parent(".map-inner").toggleClass("open");
		}
	}
	
		$rootScope.entityType = [{ 'val' : 'Select Entity','key' : ''},{ 'val' : 'Shipper','key' : 'shipper'},{ 'val' : 'Consignee','key' : 'consignee'}];
		
		/**
		 * showing value in dropdown for entity
		 */
		 
		$rootScope.onSelectChangeEntityCallback = function(value, key, type) {
			$rootScope.editSavedLoad[type+'_entity'] = value.key;
			$rootScope[type+'entity'] = value.val;
		}
		
		/**
		 * Extra stop entity issue
		 */
		  
		$rootScope.onSelectChangeExtraEntityCallback = function( value, key, index ) {
			if ( value.key == '' ) {
				$rootScope.extraStops['extraStopEntity_' + index] = '';
			}
		}
		
		/**
		 * showing dynamic value in dropdown for extrastops entity
		 */
		
		$rootScope.onSelectChangeExtraStopEntityCallback = function(value, key, index) {
			$rootScope.extraStops['extraStopEntity_'+index] = value.key;
			$rootScope['extraStopEntitys_'+index] = value.val;
		} 
		/**
	     * Adding new loads functionality start
	     */
	     
		$rootScope.addNewGlobalLoad = function() {
			$rootScope.alertloadmsg = false;
			$rootScope.alertExceedMsg = false;
			$rootScope.editLoads = true;
			$rootScope.showMaps = false;
			$rootScope.showAddhighlighted = 'loadDetail';   // showing highlighte tab
			$scope.saveAddPrimaryId = '';				// setting primary Id to empty for adding new load
			$rootScope.editSavedLoad = {};
			$scope.trailerTypes = [];
			$rootScope.viaRoutes = [];					// setting via routes empty for extra stops
			$rootScope.setAddVehicleId = ''; 			// setting vehicle id to empty for initial
			$scope.add_load_div = true;					// by default showing the save button on add laod
			$rootScope.showFormClass = false;
			$rootScope.disablePerm = false;
			$rootScope.disableTemp = false;
			
			$rootScope.editSavedLoad.PickupAddress = '';
			$rootScope.editSavedLoad.DestinationAddress = '';
			$rootScope.vehicleDriverFound = false;
			$rootScope.vehicleInfo = {};
			$rootScope.extraStops = {};					// initallising extra stops
			$rootScope.extraStopTotLength = false;			// hiding the extra stop content
			$rootScope.tempstorage = {};
			$rootScope.uploadRateSheetDoc = false;
			$rootScope.showUploadRateSheetButton = true;	// setting button for upload rate sheet
			
			$rootScope.editSavedLoad.id = '';				// empty the primary key while editing
			$rootScope.editSavedLoad.shipper_entity = 'shipper';				// setting value for shipper and consignee initially
			$rootScope.editSavedLoad.consignee_entity = 'consignee';
			$rootScope.shipperentity = 'Shipper';
			$rootScope.consigneeentity = 'Consignee';
			$rootScope.extraStopsLength = 0;					// initailly setting value of extra stops to 0
			$rootScope.jobPlaceholder = 'Select Status';
			$rootScope.jobStatus = [{ 'val' : 'No Status','key' : ''},{ 'val' : $rootScope.languageCommonVariables.negotiating,'key' : 'negotiating'},{ 'val' : $rootScope.languageCommonVariables.booked,'key' : 'booked'},{ 'val' : $rootScope.languageCommonVariables.inprogress,'key' : 'inprogress'},{ 'val' : $rootScope.languageCommonVariables.delayed,'key' : 'delayed'},{ 'val' : $rootScope.languageCommonVariables.delivered,'key' : 'delivered'},{ 'val' : $rootScope.languageCommonVariables.completed,'key' : 'completed'},{ 'val' : $rootScope.languageCommonVariables.cancel,'key' : 'cancel'},{ 'val' : $rootScope.languageCommonVariables.invoiced,'key' : 'invoiced'}];
			dataFactory.httpRequest(URL+'/truckstop/addNewLoad').then(function(data) {
				$rootScope.driverAssignValue = $rootScope.languageCommonVariables.popupunassignd;
				$rootScope.listDrivers = data.driversList;
				$scope.trailerTypes = data.trailerTypes;    // passing trailer types in dropdown
								
				$rootScope.autoCompleteForAddress();
				$rootScope.extraStop = [];						// initially setting extraStop to 0
				
				/* Setting initial values to 0 */
					$rootScope.editSavedLoad.PaymentAmount1 = 0;
					$rootScope.editSavedLoad.totalCost = 0;
					$rootScope.editSavedLoad.overall_total_rate_mile = 0;
					$rootScope.editSavedLoad.overallTotalProfit = 0;
					$rootScope.editSavedLoad.overallTotalProfitPercent = 0;
				/* Setting initial values 0 ends */
				
				$('#add-load-details').modal('show');
				
			});
		}
		
		/**
		 * Showing load detail on custom add
		 */
	$scope.showAddLoadDetails = function(loadId) {
		$rootScope.showAddhighlighted = 'loadDetail';
		$rootScope.editLoads = true;
		$rootScope.alertloadmsg = false;
		$rootScope.alertExceedMsg = false;
		$rootScope.showMaps = false;
		
		$rootScope.uploadRateSheetDoc = false;
		$rootScope.showUploadRateSheetButton = true;
		
		$scope.add_load_div = true;			// for add load
		$rootScope.disablePerm = false;
		$rootScope.disableTemp = false;
		
		if ( loadId != '' && loadId != undefined ) {
			$scope.add_load_div = false;			// for add load
			$rootScope.save_edit_div = true;			// for add load
			$rootScope.disablePerm = true;
			$rootScope.disableTemp = true;
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/truckstop/matchLoadDetail/'+0+'/'+$rootScope.editSavedLoad.vehicle_id+'/'+loadId,'POST',{},{ loadRequest : 'addRequest' }).then(function(data) {				
				$rootScope.editSavedLoad = {};
				$rootScope.editSavedDist = {};
				$rootScope.editSavedLoad = data.encodedJobRecord;
				
				if ( $rootScope.editSavedLoad.PaymentAmount == 0 || $rootScope.editSavedLoad.PaymentAmount == '' || $rootScope.editSavedLoad.PaymentAmount == undefined ) {
					$rootScope.editSavedLoad.overall_total_rate_mile = 0;
					$rootScope.editSavedLoad.PaymentAmount = 0;
					$rootScope.editSavedLoad.PaymentAmount1 = 0;
				}
				
				if ( $rootScope.editSavedLoad.vehicle_id != undefined && $rootScope.editSavedLoad.vehicle_id != '' ) {
					$rootScope.driverAssignValue = $rootScope.languageCommonVariables.assigned;
					$rootScope.vehicleIdSelected = $rootScope.editSavedLoad.vehicle_id;
				} else {
					$rootScope.editSavedLoad.vehicle_id = '';
					$rootScope.driverAssignValue = $rootScope.languageCommonVariables.popupunassignd;
				}
				
				if ( $rootScope.editSavedLoad.Stops == null ) {
					$rootScope.editSavedLoad.Stops = 0;
				}
				
				$rootScope.shipperentity = $rootScope.editSavedLoad.shipper_entity;
				$rootScope.consigneeentity = $rootScope.editSavedLoad.consignee_entity;
				
				$rootScope.tempstorage = {};
				$rootScope.tempstorage.originTT = $rootScope.editSavedLoad.PickupTime;
				$rootScope.tempstorage.deliveryTT = $rootScope.editSavedLoad.DeliveryTime;
				$rootScope.tempstorage.originTT_range_end = $rootScope.editSavedLoad.PickupTimeRangeEnd;
				$rootScope.tempstorage.deliveryTT_range_end = $rootScope.editSavedLoad.DeliveryTimeRangeEnd;

				
				$rootScope.extraStop = [];
				$rootScope.viaRoutes = [];
				$rootScope.extraStops = {};
				if ( data.extra_stops_data.length  != 0 ) {
					$rootScope.extraStopTotLength = true;
					$rootScope.extraStopsLength = $rootScope.editSavedLoad.Stops;
					$rootScope.extraStopsTemp =  data.extra_stops_data;
					
					var stopsLength = parseInt($rootScope.extraStopsTemp.length);
						for ( var i = 0; i < stopsLength; i++ ) {
							$rootScope.extraStop.push(i);
							$rootScope.extraStops['extraStopAddress_' + i ] 	= $rootScope.extraStopsTemp[i].extraStopAddress;
							$rootScope.extraStops['extraStopCity_' + i ]    	= $rootScope.extraStopsTemp[i].extraStopCity;
							$rootScope.extraStops['extraStopState_' + i ] 		= $rootScope.extraStopsTemp[i].extraStopState;
							$rootScope.extraStops['extraStopCountry_' + i ] 	= $rootScope.extraStopsTemp[i].extraStopCountry;
							
							if ( $rootScope.extraStopsTemp[i].extraStopDate == '0000-00-00' ) {
								$rootScope.extraStops['extraStopDate_' + i ] = '';
							} else {
								$rootScope.extraStops['extraStopDate_' + i ] = $rootScope.extraStopsTemp[i].extraStopDate;
							}
							$rootScope.extraStops['extraStopName_' + i ] = $rootScope.extraStopsTemp[i].extraStopName;
							$rootScope.extraStops['extraStopPhone_' + i ] = $rootScope.extraStopsTemp[i].extraStopPhone;
							$rootScope.extraStops['extraStopTime_' + i ] = $rootScope.extraStopsTemp[i].extraStopTime;
							$rootScope.extraStops['extraStopTimeRange_' + i ] = $rootScope.extraStopsTemp[i].extraStopTimeRange;
							$rootScope.extraStops['extraStopZipCode_' + i ] = $rootScope.extraStopsTemp[i].extraStopZipCode;
							$rootScope.extraStops['id_'+ i] = $rootScope.extraStopsTemp[i].id;
							
							if( $rootScope.extraStopsTemp[i].extraStopEntity != '' ) {
								if ($rootScope.extraStopsTemp[i].extraStopEntity == 'shipper' ) {
									$rootScope.extraStops['extraStopEntity_' + i ] = { 'val' : 'Shipper','key' : 'shipper'};
								} else {
									$rootScope.extraStops['extraStopEntity_' + i ] = { 'val' : 'Consignee','key' : 'consignee'};
								} 
							}
												
							$rootScope.viaRoutes.push($rootScope.extraStopsTemp[i].extraStopAddress);
						}
						
						LoopForAutoComplete(stopsLength);
						function LoopForAutoComplete (j) {  
							setTimeout(function () { 
								$rootScope.extraStopsAutoAddress(j, 'addRequest');  
								if (--j > 0 ) {
									LoopForAutoComplete(j);      //  decrement i and call myLoop again if i > 0
								}
						   }, 500)
						}
						
						
				}
						
						$rootScope.vehicleInfo = {};
						$rootScope.editSavedDist = data.distance;
						$rootScope.vehicleInfo = data.vehicleInfo;
						$rootScope.primaryLoadId = data.primaryLoadId;
										
						if (Object.keys($rootScope.vehicleInfo).length > 0 ) {
							$rootScope.vehicleDriverFound = true;
							
							if ( $rootScope.vehicleInfo.vehicle_image == '' ) 
								$rootScope.defaultVehicleImage = true;
							
							if ( $rootScope.vehicleInfo.profile_image == '' ) 
								$rootScope.defaultDriverImage = true;
						}
						
						$rootScope.listDrivers = data.driversList;
						$rootScope.driverPlaceholder = $rootScope.editSavedLoad.assignedDriverName;
							
						$rootScope.rateSheetUploaded = data.rateSheetUploaded;		
						//----------drop-down ---------------------------
						$rootScope.jobPlaceholder = $rootScope.editSavedLoad.JobStatus;
	
						if ($rootScope.jobPlaceholder != undefined && $rootScope.jobPlaceholder != '') {
							$rootScope.jobPlaceholder = $rootScope.languageCommonVariables[$rootScope.jobPlaceholder];
						}else {
							$rootScope.jobPlaceholder = $rootScope.languageCommonVariables.jobStatusPlaceholder;
						}
			
						//----------drop-down ---------------------------
						
						$scope.equipmentValue = $rootScope.editSavedLoad.equipment.replace(',',' ');
													
						if ( data.brokerData.mc_number != undefined && data.brokerData.mc_number != null && data.brokerData.mc_number != 0 ) {	// check if mc number given to send request to triumph else show declined status
							$rootScope.fetch_triumph_request(data.brokerData.mc_number,data.brokerData.dot_number); 	
						} else {
							$rootScope.showBrokerStatus = $rootScope.languageCommonVariables.statusDeclined;
						}				
				$scope.autoFetchLoads = false;
			});
		} else {
			$rootScope.editSavedLoad.broker_id = '';
		}
		
	}
	
	/**
	 * Autocomplete for pickup and destination address for add Load 
	 */
	$rootScope.autoCompleteForAddress = function() {
		var input = document.getElementById("autocompleteAddPickupAddress");
		var autocomplete = new google.maps.places.Autocomplete(input,$scope.limitCountryOptions);
				google.maps.event.addListener(autocomplete, 'place_changed', function () {
				var places = autocomplete.getPlace();
				var result = autocomplete.getPlace();
				var street = "", street_number="", postal_code="", city = "", state = "", county = "", country = "";
				
				if(result.address_components){
					for(var i = 0; i < result.address_components.length; i += 1) {
					  var addressObj = result.address_components[i];
					  for(var j = 0; j < addressObj.types.length; j += 1) {
					    switch(addressObj.types[j]){
					  		case "country": 			 		 country = addressObj.long_name;		break;	//Country
					  		case "administrative_area_level_1" : state 	= addressObj.short_name; 		break; 	//State
					  		case "administrative_area_level_2" : county = addressObj.long_name; 		break;	//County
					  		case "locality" :  					 city 	= addressObj.long_name; 		break;	//City
					  		case "route": 						 street = addressObj.long_name;			break;	//street
					  		case "street_number": 				 street_number =  addressObj.long_name;	break;	//street number
					  		case "postal_code": 				 postal_code = addressObj.long_name; 	break;	//Postal Code
					  	}
					  }
					}
					
				} else {
					if ( result.name != '' ) {
						var pickupaddress = result.name.split(',');
						lastItem = pickupaddress.pop();
						country = lastItem;
						
						secondLast = pickupaddress.pop();
						var checkInteger = /\d/.test(secondLast);
						if ( checkInteger ) {
							var splitedValue = secondLast.split(' ');
							
							if ( isNaN(splitedValue[0]) ) {
								state = splitedValue[0];
								postal_code = splitedValue[1];
							} else {
								state = splitedValue[1];
								postal_code = splitedValue[0];
							}
						} else {
							state = secondLast;
						}
						
						thirdLast = pickupaddress.pop();
						city = thirdLast;
						
						if ( pickupaddress.length ) {
							for( var i = 0; i < pickupaddress.length; i += 1 ){
								street += pickupaddress[i]+',';
							}
							street = street.replace(/,(?=[^,]*$)/, '');
						}
					}
				}
				
				$rootScope.editSavedLoad.PickupAddress  = street_number +' '+ street;
				$rootScope.editSavedLoad.OriginStreet 	= street_number + " " + street ;
				$rootScope.editSavedLoad.OriginCity 	= city ;
				$rootScope.editSavedLoad.OriginState 	= state;
				$rootScope.editSavedLoad.OriginCountry 	= country;

			var sendRequest = 0;
				if((street != "" || street_number != "") && postal_code != ""){
					$rootScope.editSavedLoad.OriginZip = postal_code;
					sendRequest = 1;
				}else if((street != "" || street_number != "") && postal_code == ""){
					$scope.autoFetchLoads = true;					
					var fullAddress = $rootScope.editSavedLoad.PickupAddress+','+$rootScope.editSavedLoad.OriginCity+','+$rootScope.editSavedLoad.OriginState+','+$rootScope.editSavedLoad.OriginCountry;
					dataFactory.httpRequest(URL+'/truckstop/getZipCode','POST',{},{address: fullAddress}).then(function(data) {
						if ( data.zipcode != undefined && data.zipcode != '' ) {
								$rootScope.editSavedLoad.OriginZip = data.zipcode;
						}
						$scope.autoFetchLoads = false;					
					});
					sendRequest = 1;
			  	}else{
			  		$rootScope.editSavedLoad.OriginZip = "";
			  		sendRequest = 1;
			  	}
			  	$rootScope.$apply();
			  	
			  	if ( sendRequest == 1 )
					$scope.changeAddMilesDistance('origin');
			});
		
	
		var destAddress = document.getElementById("autoCompleteDestAddress");
		var autocompleteDest = new google.maps.places.Autocomplete(destAddress,$scope.limitCountryOptions);
			google.maps.event.addListener(autocompleteDest, 'place_changed', function () {
			var result = autocompleteDest.getPlace();
			var street = "", street_number="", postal_code="", city = "", state = "", county = "", country = "";

			if(result.address_components){
				for(var i = 0; i < result.address_components.length; i += 1) {
				  var addressObj = result.address_components[i];
				  for(var j = 0; j < addressObj.types.length; j += 1) {
				    switch(addressObj.types[j]){
				  		case "country": 			 		 country= addressObj.long_name; 		break;	//Country
				  		case "administrative_area_level_1" : state 	= addressObj.short_name;  		break; 	//State
				  		case "administrative_area_level_2" : county = addressObj.long_name; 		break;	//County
				  		case "locality" :  					 city 	= addressObj.long_name; 		break;	//City
				  		case "route": 						 street = addressObj.long_name;			break;	//street
				  		case "street_number": 				 street_number =  addressObj.long_name;	break;	//street number
				  		case "postal_code": 				 postal_code = addressObj.long_name; 	break;	//Postal Code
				  	}
				  }
				}
				
			} else {
				if ( result.name != '' ) {
					var pickupaddress = result.name.split(',');
					lastItem = pickupaddress.pop();
					country = lastItem;
					
					secondLast = pickupaddress.pop();
					var checkInteger = /\d/.test(secondLast);
					if ( checkInteger ) {
						var splitedValue = secondLast.split(' ');
						
						if ( isNaN(splitedValue[0]) ) {
							state = splitedValue[0];
							postal_code = splitedValue[1];
						} else {
							state = splitedValue[1];
							postal_code = splitedValue[0];
						}
					} else {
						state = secondLast;
					}
					
					thirdLast = pickupaddress.pop();
					city = thirdLast;
					
					if ( pickupaddress.length ) {
						for( var i = 0; i < pickupaddress.length; i += 1 ){
							street += pickupaddress[i]+',';
						}
						street = street.replace(/,(?=[^,]*$)/, '');
					}
				}
			}
			
			$rootScope.editSavedLoad.DestinationAddress = street_number + " " + street;
			$rootScope.editSavedLoad.DestinationStreet 	= street_number + " " + street ;
			$rootScope.editSavedLoad.DestinationCity 	= city ;
			$rootScope.editSavedLoad.DestinationState 	= state;
			$rootScope.editSavedLoad.DestinationCountry = country;
			
			var sendRequest = 0;
			if((street != "" || street_number != "") && postal_code != ""){
				$rootScope.editSavedLoad.DestinationZip = postal_code;
				sendRequest = 1;
			}else if((street != "" || street_number != "") && postal_code == ""){
				$scope.autoFetchLoads = true;					
				var fullAddress = $rootScope.editSavedLoad.DestinationAddress+','+$rootScope.editSavedLoad.DestinationCity+','+$rootScope.editSavedLoad.DestinationState+','+$rootScope.editSavedLoad.DestinationCountry;
				dataFactory.httpRequest(URL+'/truckstop/getZipCode','POST',{},{address: fullAddress}).then(function(data) {
					if ( data.zipcode != undefined && data.zipcode != '' ) {
							$rootScope.editSavedLoad.DestinationZip = data.zipcode;
							//$rootScope.editSavedLoad.DestinationZip = data.zipcode;
					}
					$scope.autoFetchLoads = false;					
				});
				sendRequest = 1;
		  	}else{
		  		$rootScope.editSavedLoad.DestinationZip = "";
		  		sendRequest = 1;
		  	}
		  	$rootScope.$apply();
		  	
		  	if ( sendRequest == 1 )
					$scope.changeAddMilesDistance('origin');
		});
	}
	
	/**
	 * Adding broker detail
	 */ 
	 
	$scope.showAddBrokerInfo = function(primaryAddId) {
	
		if ( primaryAddId != undefined && primaryAddId != '' ) {
			$scope.add_broker_div = false;
			$rootScope.save_edit_div = true;
			$timeout(function(){ $rootScope.disableInputs(); }, 400);
		} else {
			$rootScope.enableInputs();
			$scope.add_broker_div = true;
			$rootScope.save_edit_div = false;
		}
		
		$rootScope.alertloadmsg = false;
		$rootScope.alertExceedMsg = false;
		$rootScope.editBrokerLoadInfo = {};
		$rootScope.editLoads = false;
		$rootScope.showMaps = false;
		$rootScope.showBrokerStatus = '';
		$rootScope.showAddhighlighted = 'brokerDetails';
		
		$rootScope.uploadBrokerDoc = false;
		$rootScope.showUploadBrokerButton = true;
			
		if ( $rootScope.editSavedLoad.broker_id != '' && $rootScope.editSavedLoad.broker_id != undefined && $rootScope.editSavedLoad.broker_id != null ) {	
			dataFactory.httpRequest(URL+'/brokers/getBrokerDocumentUploaded/'+$rootScope.editSavedLoad.broker_id).then(function(data) {					// Fetching broker document uploaded information
				if ( data.brokerDocuments != undefined && data.brokerDocuments.length != 0 ) {
					$scope.brokerDocuments = data.brokerDocuments;
				}
			});
		}
		
		$scope.selectedBrokerName = 'Select Broker';		
		dataFactory.httpRequest(URL+'/assignedloads/getBrokersList/'+primaryAddId).then(function(data) {					// Fetching brokers info to show in dropdown
			$scope.brokersList = data.brokersList;
			if ( data.brokerLoadDetail.length != 0 ) {
				$rootScope.editBrokerLoadInfo = data.brokerLoadDetail;
				$scope.selectedBrokerName = data.brokerLoadDetail.TruckCompanyName;
				$scope.brokerSelectedId = data.brokerLoadDetail.id;
			}
		});
	}
	
	/**
	 * toggle edit and save buttons
	 */ 	
	$scope.changeEditStatus = function() {
		$rootScope.save_cancel_div  = true;
		
		$scope.add_load_div = true;  			// for add load
		$scope.add_broker_div = true;  			// for add load broker tab
		$rootScope.save_edit_div = false;
		$rootScope.showFormClass = false;
		$rootScope.disabledUISelect = false;
		if ( $rootScope.editSavedLoad.id < 9999  ) {
			$rootScope.disablePerm = true;
			$rootScope.disableAnotherDropdowns = true;
		} else {
			if ( $rootScope.editSavedLoad.driver_id != undefined && $rootScope.editSavedLoad.driver_id != '' && $rootScope.editSavedLoad.driver_id != 0 )
				$rootScope.disableAnotherDropdowns = false;
			else
				$rootScope.disableAnotherDropdowns = true;
				
			$rootScope.disablePerm = false;
		}
			
		$rootScope.disableTemp = false;
	}
	
	$scope.changeSaveStatus = function() {
		$rootScope.save_cancel_div  = false;
		$scope.add_load_div = false;  			// for add load
		$scope.add_broker_div = false;  		// for add load broker
		$rootScope.save_edit_div = true;
		$rootScope.showFormClass = true;
		$rootScope.disabledUISelect = true;
		$rootScope.disableTemp = true;
		$rootScope.disablePerm = true;
		$rootScope.disableAnotherDropdowns = true;
	}
	
	$scope.showhideMatchDetail = function() {
		$scope.showPlusMinus = false;
	}
	
	$scope.fetchTimeline = function(truckstopId, loadId){
		$scope.selectTab('timeline');
	}
	
	/**
	 * Fetching broker info on change of selectbox
	 */
	 
	$scope.onSelectChangeBrokerCallback = function( value, key , parameter) {
		$scope.selectedBrokerName = value.TruckCompanyName;
		if ( value.id != '' && value.id != undefined ) {
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/triumph/getBrokerFullDetails/'+value.id+'/'+value.MCNumber).then(function(data) {					// Fetching broker info to show fields selected
				if ( parameter == 'addRequest' ) {
					$rootScope.editBrokerLoadInfo.TruckCompanyName = data.brokerDetail.TruckCompanyName;
					$rootScope.editBrokerLoadInfo.PointOfContact = data.brokerDetail.PointOfContact;
					$rootScope.editBrokerLoadInfo.PointOfContactPhone = data.brokerDetail.PointOfContactPhone;
					$rootScope.editBrokerLoadInfo.TruckCompanyEmail = data.brokerDetail.TruckCompanyEmail;
					$rootScope.editBrokerLoadInfo.TruckCompanyPhone = data.brokerDetail.TruckCompanyPhone;
					$rootScope.editBrokerLoadInfo.TruckCompanyFax = data.brokerDetail.TruckCompanyFax;
					$rootScope.editBrokerLoadInfo.postingAddress = data.brokerDetail.postingAddress;
					$rootScope.editBrokerLoadInfo.city = data.brokerDetail.city;
					$rootScope.editBrokerLoadInfo.state = data.brokerDetail.state;
					$rootScope.editBrokerLoadInfo.zipcode = data.brokerDetail.zipcode;
					$rootScope.editBrokerLoadInfo.MCNumber = data.brokerDetail.MCNumber;
					$rootScope.editBrokerLoadInfo.CarrierMC = data.brokerDetail.CarrierMC;
					$rootScope.editBrokerLoadInfo.DOTNumber = data.brokerDetail.DOTNumber;
					$rootScope.editBrokerLoadInfo.brokerStatus = data.brokerDetail.brokerStatus;
					$rootScope.editBrokerLoadInfo.id = data.brokerDetail.id;
				} else {
					$rootScope.editSavedLoad.TruckCompanyName = data.brokerDetail.TruckCompanyName;
					$rootScope.editSavedLoad.PointOfContact = data.brokerDetail.PointOfContact;
					$rootScope.editSavedLoad.PointOfContactPhone = data.brokerDetail.PointOfContactPhone;
					$rootScope.editSavedLoad.TruckCompanyEmail = data.brokerDetail.TruckCompanyEmail;
					$rootScope.editSavedLoad.TruckCompanyPhone = data.brokerDetail.TruckCompanyPhone;
					$rootScope.editSavedLoad.TruckCompanyFax = data.brokerDetail.TruckCompanyFax;
					$rootScope.editSavedLoad.postingAddress = data.brokerDetail.postingAddress;
					$rootScope.editSavedLoad.city = data.brokerDetail.city;
					$rootScope.editSavedLoad.state = data.brokerDetail.state;
					$rootScope.editSavedLoad.zipcode = data.brokerDetail.zipcode;
					$rootScope.editSavedLoad.MCNumber = data.brokerDetail.MCNumber;
					$rootScope.editSavedLoad.DOTNumber = data.brokerDetail.DOTNumber;
					$rootScope.editSavedLoad.brokerStatus = data.brokerDetail.brokerStatus;
				}
				$rootScope.editSavedLoad.broker_id = data.brokerDetail.id; 					// SHOWING UPLOAD BUTTON ON JOB TICKET	
				$rootScope.showBrokerStatus = (data.brokerDetail.brokerStatus == "Approved") ? $rootScope.languageCommonVariables.statusApproved : $rootScope.languageCommonVariables.statusDeclined;
				
				if ( data.brokerDocuments != undefined ) {
					$scope.brokerDocuments = data.brokerDocuments;
				} else {
					$scope.brokerDocuments = [];
				}
				$scope.autoFetchLoads = false;
			});
		}
	} 
		
		/**
		 * Saving broker and shipper info on add
		 */
		
		$scope.saveBrokerShipper = function(addPrimaryId, type) {
			
			$scope.autoFetchLoads = true;
			if( type == 'broker' ) 
				var postData = $rootScope.editBrokerLoadInfo;
							
			if ( addPrimaryId != '' && addPrimaryId != undefined ) {
				dataFactory.httpRequest(URL+'/truckstop/saveBrokerShipperInfo/'+addPrimaryId+'/'+type,'POST',{},{ loadRecords : postData }).then(function(data) {
					if ( data.success == true ) {
						$rootScope.alertloadmsg = true;
						$rootScope.alertExceedMsg = false;
						$rootScope.Message  = 'Success : The broker details has been saved successfully.';
					}
					$scope.autoFetchLoads = false;
				});
			} else {
				$rootScope.alertExceedMsg = true;
				$scope.ExceedMessage  = 'Error! : Please enter load details in order to save data.';
				$scope.autoFetchLoads = false;
			}
		} 
		
		/**
		 * Resetting the broker and shipping Form
		 */
		 
		$scope.resetTheForm = function(resetType) {			
			if( resetType == 'broker' ) {
				$rootScope.editBrokerLoadInfo = {};
				$scope.selectedBrokerName = '';
				$rootScope.showBrokerStatus = '';
			} else {
				$rootScope.editShippingLoadInfo = {};
			}
		}
		
		/**
		 * changing equipment types dropdown select equipment options
		 */
		 
		$scope.onSelectChangeEquipmentCallback = function( value, parameter ) {
			$scope.equipmentValue = value.name;
			$rootScope.editSavedLoad.equipment = value.name;
			$rootScope.editSavedLoad.equipment_options = value.abbrevation;
			if ( parameter == 'editRequest' ) {
				$rootScope.editSavedLoad.EquipmentTypes.Code = value.abbrevation;
				$rootScope.editSavedLoad.EquipmentTypes.Description = value.abbrevation;
			}
		}
		
		/**
		 * Reseting values of form
		 */
		
		$scope.resetAllForm = function() {
			$rootScope.editSavedLoad = {};
			$rootScope.tempstorage = {};
			$rootScope.vehicleInfo = {};
			$rootScope.vehicleDriverFound = false;
			$rootScope.editSavedLoad.PickupAddress = '';
			$rootScope.editSavedLoad.DestinationAddress = '';
			
			$rootScope.editSavedLoad.Stops = 0;
			$rootScope.extraStopsLength = 0;
			$rootScope.extraStops = [];
			$rootScope.extraStop = [];
			/* Setting initial values to 0 */
				$rootScope.editSavedLoad.PaymentAmount1 = 0;
				$rootScope.editSavedLoad.totalCost = 0;
				$rootScope.editSavedLoad.overall_total_rate_mile = 0;
				$rootScope.editSavedLoad.overallTotalProfit = 0;
				$rootScope.editSavedLoad.overallTotalProfitPercent = 0;
			/* Setting initial values 0 ends */
		} 
		
		/**
		 * saving trip details on custom add
		 */
		 
		$scope.changeAddTruckDetailFields = function( loadId, tripDetailId, value ) {
			$scope.autoFetchLoads = true;
			if ( loadId != '' && loadId != undefined ) {
				dataFactory.httpRequest(URL+'/truckstop/save_trip_details/'+0+'/'+loadId+'/'+tripDetailId,'POST',{},{jobRecords: $rootScope.editSavedLoad, jobPrimary: loadId, vehicleId : $rootScope.editSavedLoad.vehicle_id, truckDetailsInfo: $scope.matchedTruckData ,extraStops : $rootScope.extraStops, loadRequest : 'addRequest',driverAssignType:$rootScope.driverAssignType  }).then(function(data) {
					$rootScope.Message = $rootScope.languageCommonVariables.LoadSavedSuccMsg;
					$rootScope.alertloadmsg = true;
					$rootScope.alertExceedMsg = false;
					$rootScope.save_cancel_div  = false;
					$rootScope.save_edit_div = true;
					$rootScope.showFormClass = true;
					$rootScope.primaryLoadId  = data.loadid;
					$scope.primaryTripDetailId  = data.tripDetailId;
					$scope.matchedTruckData = data.vehicles_Available;
					$rootScope.firstTimeClick = true;
					$scope.autoFetchLoads = false;
				});
			} else {
				$scope.autoFetchLoads = false;
			}
			$scope.showTruckDetailEdit = true;
			$scope.showTruckEdit = true;
			$("#new-row-id").addClass('class-disable-all-input');
			$("#new-row-id").find("input,select").attr("disabled", true);
		} 
		
		/**
		 * On change of origin and destination street address for adding new load recalulating results after half seconds for binding values
		 */
		 
		$scope.changeAddMilesDistance = function(addType) {
			$scope.autoFetchLoads = true;
			$timeout(function(){
				//~ $rootScope.changeAddMilesDistanceNew(addType,address,'searchZip');
				$rootScope.changeAddMilesDistanceNew(addType,'searchZip');
			},500);
		}
		
		//~ $rootScope.changeAddMilesDistanceNew = function(addType,address, zipParameter) {
		$rootScope.changeAddMilesDistanceNew = function(addType, zipParameter) {
			
			if ($rootScope.editSavedLoad.PickupAddress != undefined && $rootScope.editSavedLoad.DestinationAddress  != undefined ) {
				$scope.autoFetchLoads = true;

					dataFactory.httpRequest(URL+'/truckstop/calculateNewDistance','POST',{},{allData : $rootScope.editSavedLoad,vehicleId : $rootScope.setAddVehicleId, searchType : addType, requestType : 'addRequest',extraStopsAdded : $rootScope.extraStops,driverAssignType:$rootScope.driverAssignType}).then(function(dataRes) {
			
						if ( dataRes.distance != '' && dataRes.distance != undefined && dataRes.distance != null ) {
							
							if ( $rootScope.extraStop.length == 0 ) {
								$rootScope.editSavedLoad.originalDistance = dataRes.distance;
							}
							
							$rootScope.editSavedLoad.Mileage = dataRes.distance;
							$rootScope.editSavedLoad.timer_distance = dataRes.distance;
							$rootScope.editSavedLoad.deadmiles = dataRes.new_deadmiles_Cal;
							
							$rootScope.editSavedLoad.loadedDistanceCost = dataRes.loadedDistanceCost;
							$rootScope.editSavedLoad.deadMileDistCost = dataRes.deadMileDistCost;
							$rootScope.editSavedLoad.estimatedFuelCost = dataRes.estimatedFuelCost;
							
							$rootScope.editSavedLoad.totalMiles = parseFloat( dataRes.distance)  + parseFloat($rootScope.editSavedLoad.deadmiles );
							$rootScope.editSavedLoad.totalCost = dataRes.overall_total_charge_Cal;
							$rootScope.editSavedLoad.overall_total_rate_mile = dataRes.overall_total_rate_mile_Cal;
							$rootScope.editSavedLoad.overallTotalProfit = dataRes.overall_total_profit_Cal;
							$rootScope.editSavedLoad.overallTotalProfitPercent = dataRes.overall_total_profit_percent_Cal;
						}
						$scope.autoFetchLoads = false;
					});
				
			} else {
				$scope.autoFetchLoads = false;
			}
		}
		
		/**
		 * Caluclating the profit after changing the payment amount
		 */
		 
		$scope.calculateProfitAgain = function( payment ) {
			payment = payment.replace(',','');
			payment = payment.replace('$','');
			$rootScope.editSavedLoad.PaymentAmount1 = payment;
			
			if ( $rootScope.editSavedLoad.totalCost != '' && $rootScope.editSavedLoad.totalCost != undefined && $rootScope.editSavedLoad.totalCost != 0 ) {
				$scope.autoFetchLoads = true;
				dataFactory.httpRequest(URL+'/truckstop/changingProfitCalulations','POST',{},{allData : $rootScope.editSavedLoad,vehicleId : ''}).then(function(data) {
					$rootScope.editSavedLoad.overall_total_rate_mile = data.overall_total_rate_mile_Cal;
					$rootScope.editSavedLoad.overallTotalProfit = data.overall_total_profit_Cal;
					$rootScope.editSavedLoad.overallTotalProfitPercent = data.overall_total_profit_percent_Cal;
					$scope.autoFetchLoads = false;
				});
			}
		} 
		
		/**
		 * Job status change call back
		 */
		$rootScope.onclickCallback = function (){
			$('.cancel').parent().addClass("cancel-outer");
		}
		$rootScope.onSelectJobCallback = function (item, model,statusValue, parameter){
			if ( parameter != 'addRequest' ) {
				angular.element("#change-status").modal('show');
				angular.element("#change-status").data("oldJobStatus",$rootScope.editSavedLoad.JobStatus);
				angular.element("#change-status").data("jobPlaceholder",$rootScope.jobPlaceholder);
				angular.element("#change-status").data("statusValue",item.key);
				
				if ( parameter == 'addRequest' ) 
					angular.element("#change-status").data("parameter",parameter);
				else 
					angular.element("#change-status").data("parameter",'');
			}
			$rootScope.editSavedLoad.JobStatus = item.key;
			$rootScope.jobPlaceholder= item.val;
		}
		
		/**
		 * changing the job status globally
		 */
		
		$rootScope.confirmChangeStatus = function(confirm){
			if(confirm == 'yes'){
				var statusValue = angular.element("#change-status").data("statusValue");
				var oldJobStatus = angular.element("#change-status").data("oldJobStatus");
				var jobPlaceholder = angular.element("#change-status").data("jobPlaceholder");
				var parameter = angular.element("#change-status").data("parameter");
				
				$scope.previousStatusValue = oldJobStatus;
				angular.element("#change-status").modal('hide');
				if ( statusValue != undefined && parameter != 'addRequest') {
					if ( statusValue == 'booked' && ( $rootScope.editSavedLoad.woRefno == '' || $rootScope.editSavedLoad.woRefno == null) ) {
						angular.element("#enterWOnumber").modal('show');
						return false;
					}
					$rootScope.saveEditLoad($rootScope.editSavedLoad,'status','statusChange',jobPlaceholder);
				}
			} else {
				var statusValue = angular.element("#change-status").data("statusValue");
				var oldJobStatus = angular.element("#change-status").data("oldJobStatus");
				var jobPlaceholder = angular.element("#change-status").data("jobPlaceholder");
				$scope.previousStatusValue = '';
				$rootScope.editSavedLoad.JobStatus = oldJobStatus;
				$rootScope.jobPlaceholder = jobPlaceholder;
				angular.element("#change-status").removeData("statusValue");
				angular.element("#change-status").removeData("oldJobStatus");
				angular.element("#change-status").removeData("jobPlaceholder");
				angular.element("#change-status").removeData("parameter");
				angular.element("#change-status").modal('hide');
			}
			
			$('#change-status').on('hidden.bs.modal', function (event) {
				$("body").addClass('modal-open');
			});	
		} 
	
		
		/**
		 * Saving the load to DB
		 */
		 
		$scope.saveAddLoad = function() {
			$rootScope.saveEditLoad($rootScope.editSavedLoad, '', '', '', 'addRequest');			
		} 
		
		$rootScope.saveEditLoad = function(editSavedLoad, status,from,old , saveParameter) {
			
			if ( saveParameter == 'addRequest' ) {
				$rootScope.editSavedLoad.PickupAddress = document.getElementById('autocompleteAddPickupAddress').value;
				$rootScope.editSavedLoad.DestinationAddress = document.getElementById('autoCompleteDestAddress').value;
			} else {
				$rootScope.editSavedLoad.PickupAddress = document.getElementById('autocompleteAdd').value;
				$rootScope.editSavedLoad.DestinationAddress = document.getElementById('destinationAddress').value;
			}			
		
			if ( $rootScope.editSavedLoad.JobStatus == 'booked' ) {
				if ( $rootScope.editSavedLoad.woRefno == '' || $rootScope.editSavedLoad.woRefno == null || $rootScope.editSavedLoad.woRefno == undefined ) {
					if ( saveParameter == 'addRequest' ) 
						angular.element($('#add-load-details')).animate({ scrollTop: 0 }, 'slow');
					else
						angular.element($('#edit-fetched-load')).animate({ scrollTop: 0 }, 'slow');
					
					$rootScope.alertloadmsg = false;
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = $rootScope.languageCommonVariables.enterWoReference;
					$rootScope.save_cancel_div  = false;
					$rootScope.save_edit_div = true;
					return false;
				}
			}
		
			$scope.autoFetchLoads = true;
			
			if ( saveParameter == 'addRequest' ) {
				$rootScope.primaryLoadId = 0;
				
				if (  $scope.saveAddPrimaryId != '' && $scope.saveAddPrimaryId != undefined )
					$rootScope.primaryLoadId = $scope.saveAddPrimaryId;
				
				var saveType = 'ourLoad';
				$rootScope.vehicleIdRepeat = $rootScope.editSavedLoad.vehicle_id;
			} else {
				var saveType = $rootScope.saveTypeLoad;
			}
			
			$rootScope.editedItem = {};
			dataFactory.httpRequest(URL+'/truckstop/edit_live/'+$rootScope.primaryLoadId+'/'+saveType,'POST',{},{jobRecords: $rootScope.editSavedLoad, jobPrimary: $rootScope.primaryLoadId, jobDistance: $rootScope.editSavedDist, vehicleId : $rootScope.vehicleIdRepeat, extraStops : $rootScope.extraStops, timeStorage : $rootScope.tempstorage,  loadSource : $scope.loadSource,driverAssignType : $rootScope.driverAssignType, vehicleDriverFlag : $rootScope.setVehicleIdFlag}).then(function(data){
			
				$rootScope.alertloadmsg = false;
				if ( data.refStatus != undefined && data.refStatus == false ) {
					angular.element($('#edit-fetched-load')).animate({ scrollTop: 0 }, 'slow');
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = $rootScope.languageCommonVariables.woNumberAlreadyExist+' '+data.loadIdNumber+'.';
				} else if( data.requiredFields != undefined && data.requiredFields == 1 ) {
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = data.errorMessage;
					$scope.previousStatusValue = ( $scope.previousStatusValue != undefined && $scope.previousStatusValue != '' ) ? $scope.previousStatusValue : '';
					$rootScope.editSavedLoad.JobStatus = $scope.previousStatusValue;
					if ( $rootScope.editSavedLoad.JobStatus != undefined  && $rootScope.editSavedLoad.JobStatus != '' ) 
						statusmsgValue = $rootScope.languageCommonVariables[$rootScope.editSavedLoad.JobStatus];	 			
					else
						statusmsgValue = $rootScope.languageCommonVariables['noStatusValue'];	
					$rootScope.jobPlaceholder =  statusmsgValue;		
				} else {
					
					if(from == 'statusChange') {
						if ( $rootScope.editSavedLoad.JobStatus != undefined  && $rootScope.editSavedLoad.JobStatus != '' ) 
							statusmsgValue = $rootScope.languageCommonVariables[$rootScope.editSavedLoad.JobStatus];				
						else
							statusmsgValue = $rootScope.languageCommonVariables['noStatusValue'];				// status message to show success message on status change.
						
						$rootScope.Message = $rootScope.languageCommonVariables.LoadStatusUpdatedSuccMsg+" "+$rootScope.languageCommonVariables.from+" "+old.charAt(0).toUpperCase() + old.substr(1).toLowerCase()+"  "+$rootScope.languageCommonVariables.to +" "+ statusmsgValue.charAt(0).toUpperCase() + statusmsgValue.substr(1).toLowerCase()+".";
					} else {
						$rootScope.Message = $rootScope.languageCommonVariables.LoadUpdatedSuccMsg;	
					}
					
					$rootScope.alertloadmsg = true;
					$rootScope.alertExceedMsg = false;
					$rootScope.primaryLoadId  = data.id;
					$rootScope.editSavedLoad  = data.savedData;
					
					if ( data.savedData.vehicle_id != '' && data.savedData.vehicle_id != undefined ) 
						$rootScope.selectedVehicleId = data.savedData.vehicle_id;
					
					if ( saveType != undefined && saveType == 'readyForInvoice' ) {				// updating particular record from listing using its index value
						angular.copy(data.savedData, $rootScope.billingLoads[$rootScope.globalListingIndex]);
					} else if ( saveType != undefined && saveType == 'billingLoads' ) {
						angular.copy(data.savedData, $rootScope.billingLoads[$rootScope.globalListingIndex]);
					} else if ( saveType != undefined && saveType == 'assignedLoads' ) {
						
						if ( data.checkVehicleDriverFlag != undefined && data.checkVehicleDriverFlag == 0 ) {
							$rootScope.assignedLoads.splice($rootScope.globalListingIndex,1);
						} else {
							angular.copy(data.savedData, $rootScope.assignedLoads[$rootScope.globalListingIndex]);
						}	
						if ( data.table_title != undefined && data.table_title != '' ) {
							$rootScope.tableTitle = [];	
							$rootScope.tableTitle.push(data.table_title);
						}
					}else if(saveType != undefined && saveType == 'fassignedLoads'){
						$rootScope.resetListing();
					}else if(saveType != undefined && saveType == 'dashboard'){
						var returnedData = {fcolumn:data.savedData.id, PickupDate: data.savedData.PickupDate, DeliveryDate: data.savedData.DeliveryDate, miles:data.savedData.Mileage, deadmiles: data.savedData.deadmiles, invoice: data.savedData.PaymentAmount, charges: data.savedData.totalCost, profit: data.savedData.overallTotalProfit,ppercent : data.savedData.overallTotalProfitPercent  };
						//angular.copy(returnedData, $rootScope.loadPerformance[$rootScope.globalListingIndex]);
						$rootScope.updateDashboard();
					}
					
					
					$rootScope.vehicleIdSelected = parseInt(data.savedData.vehicle_id);
					if ( saveType == 'ourLoad' ) {
						$scope.saveAddPrimaryId = data.id;  // setting value for primary id on add load
						$rootScope.primaryLoadId = data.id;  // setting value for primary id on add load for broker document using same id
					}
					$rootScope.editSavedLoad.id = data.id;
					
					$rootScope.save_cancel_div  = false;
					$rootScope.save_edit_div = true;
					$scope.add_load_div = false;  			// for add loads
					
					$rootScope.showFormClass = true;
					
					
					if ( saveParameter != 'addRequest' ) {
						$rootScope.disableInputs();
					}
					
					if ( saveParameter != 'addRequest' ) {
						$rootScope.disabledUISelect = true;
					}
					
					$rootScope.disablePerm = true;
					$rootScope.disableTemp = true;
					$rootScope.disableAnotherDropdowns = true;	
					
					if ( saveParameter  == 'addRequest' ) {
						$('#add-load-details').modal('hide');
						encodedUrl = btoa(0+'-'+data.id+'-'+data.savedData.deadmiles+'-'+data.savedData.PaymentAmount+'-'+data.savedData.totalCost+'-'+data.savedData.pickDate+'-'+data.savedData.vehicle_id);
						$state.go('search.popup', {staticId:2,encodedurl:encodedUrl}, {notify: false,reloadOnSearch: false});
					}
				}
				
				$scope.autoFetchLoads = false;		
							
			});
			$rootScope.firstTimeClick = true;
				
		} 
		
		/**
		 * Fetching truck info for trip detail
		 */
		  
		$scope.fetchMatchingTruck = function( truckstopId,loadId, vehicleId, requestType ) {
			$rootScope.alertloadmsg = false;
			$rootScope.alertExceedMsg = false;
			$scope.showTruckDetailEdit = false;
			$scope.showTruckEdit = false;
			$rootScope.billingDetailsInfo = false;
			
			$rootScope.uploadPODDoc = false;
			$rootScope.showUploadPODButton = true;
			
			$rootScope.showAddhighlighted = 'matchTruck';  			// for custom add load

				
			if ( loadId == '' || loadId == undefined) {
				var ldID = 'L';
			} else {
				var ldID = loadId;
			}

			if ( vehicleId == undefined || vehicleId == '' || vehicleId == 0 || loadId == '' || loadId == undefined || loadId == 0) {		// checking if load is saved and driver is assigned or not
				$rootScope.alertExceedMsg = true;
				$rootScope.ExceedMessage = 'Error !: Please save the load details and assign driver to load to view trip detail page.';
				$rootScope.editLoads = false;
				$rootScope.showMaps  = false;
				$rootScope.brokerDetailInfo  = false;
				$rootScope.matchingTrucks   = true;
				$rootScope.showhighlighted = 'matchTruck';
				$scope.matchedTruckData = {};
				return false;
			}

			$rootScope.showMatchingTrucks = true;
			$rootScope.showMatchingTrucksEdit = true;						// loader icon for trip detail page edit
		
			if ( requestType == 'addRequest') {
				$rootScope.vehicleIdRepeat = $rootScope.editSavedLoad.vehicle_id;
				truckstopId = 0;
			} else {
				if ( $rootScope.vehicleIdRepeat == undefined || $rootScope.vehicleIdRepeat == '' )
					$rootScope.vehicleIdRepeat = $rootScope.editSavedLoad.vehicle_id;
			}	
			
			$("#new-row-id").removeClass('class-disable-all-input');
			$("#new-row-id").find("input,select").attr("disabled", false);
			
			dataFactory.httpRequest(URL+'/truckstop/fetch_matched_trucks_live/'+truckstopId+'/'+ldID+'/1'+'/'+$rootScope.vehicleIdRepeat,'POST',{},{jobRecords: $rootScope.editSavedLoad, jobPrimary: $rootScope.primaryLoadId, jobDistance: $rootScope.editSavedDist, saveDeadMile : $rootScope.deadmileSave, savedCalPayment: $rootScope.calPaymentSaved,driverAssignType: $rootScope.driverAssignType  }).then(function(data) {
				$rootScope.showMatchingTrucks = false;
				$rootScope.showMatchingTrucksEdit = false;
				
				$scope.matchedTruckData = {};
			
				$scope.matchedTruckData = data.vehicles_Available;
				$scope.matchedTruckJobData = data.jobRecord;
							
				if ( data.jobRecord.payment_amount != '' && data.jobRecord.payment_amount != 0 && data.jobRecord.payment_amount != undefined) {
					$scope.showNotCalculatedRecords = false;
					$rootScope.showPaymentCal = false;
				} else if ( data.jobRecord.PaymentAmount == '' || parseInt(data.jobRecord.PaymentAmount) == 0 || data.jobRecord.PaymentAmount == undefined ) {
					$scope.showNotCalculatedRecords = false;
				}  else {
					$scope.showNotCalculatedRecords = false;
				}
							
				$rootScope.showhighlighted = 'matchTruck';
				$rootScope.Message = '';
				$rootScope.editLoads = false;
				$rootScope.showMaps  = false;
				$rootScope.brokerDetailInfo  = false;
				$rootScope.matchingTrucks   = true;
			
				$rootScope.podDocUploaded = data.podDocUploaded;
				$scope.$broadcast("dataloaded");
			});
			
			$rootScope.disableTripDetail = false;				// disable for trip detail tab
			if ($scope.showEditButton == false ) {
				$rootScope.disableTripDetail = true;
			}
			
		}
		
		/**
		 * Saving trip detail on edit 
		 */ 
		$scope.changeTruckDetailFields = function( truckstopId, loadId, tripDetailId, value ) {
			if ( $rootScope.vehicleIdRepeat == undefined || $rootScope.vehicleIdRepeat == '' )
					$rootScope.vehicleIdRepeat = $rootScope.editSavedLoad.vehicle_id;
					
			$scope.autoFetchLoads = true;
			if ( truckstopId != '' && truckstopId != undefined ) {
				dataFactory.httpRequest(URL+'/truckstop/save_trip_details/'+truckstopId+'/'+loadId+'/'+tripDetailId,'POST',{},{jobRecords: $rootScope.editSavedLoad, jobPrimary: $rootScope.primaryLoadId,  vehicleId : $rootScope.vehicleIdRepeat, truckDetailsInfo: $scope.matchedTruckData ,extraStops : $rootScope.extraStops,driverAssignType:$rootScope.driverAssignType }).then(function(data) {
					$rootScope.Message = $rootScope.languageCommonVariables.LoadSavedSuccMsg;
					$rootScope.alertloadmsg = true;
					$rootScope.alertExceedMsg = false;
					$rootScope.save_cancel_div  = false;
					$rootScope.save_edit_div = true;
					$rootScope.showFormClass = true;
					$rootScope.primaryLoadId  = data.loadid;
					$scope.primaryTripDetailId  = data.tripDetailId;
					$scope.matchedTruckData = data.vehicles_Available;
					$rootScope.firstTimeClick = true;
					$scope.autoFetchLoads = false;

				});
			} else {
				$scope.autoFetchLoads = false;
			}
			$scope.showTruckDetailEdit = true;
			$scope.showTruckEdit = true;
			$("#new-row-id").addClass('class-disable-all-input');
			$("#new-row-id").find("input,select").attr("disabled", true);
			
			$rootScope.disableTripDetail = true;				// disable for trip detail tab
		}
		
		$scope.changeTruckDetailStatus = function() {
			$rootScope.disableTripDetail = false;				// disable for trip detail tab
			$scope.showTruckDetailEdit = false;
			$scope.showTruckEdit = false;
			$("#new-row-id").removeClass('class-disable-all-input');
			$("#new-row-id").find("input,select").attr("disabled", false);
		}
		
		/**
		 * hiding actual tabs on trip details
		 */
		
		$scope.$on('ngRepeatFinished', function(ngRepeatFinishedEvent) {
			if ( $scope.hideTexts == true ) {
				angular.element($('.actuals-class')).hide();
			} else {
				angular.element($('.actuals-class')).show();
			}
			angular.element($('.actuals-class')).hide();
		}); 
		
	/**
	 * Adding new extra stop on click of + button
	 */ 
	
	$scope.addingNewExtraStop = function( stpValue, parameter ) {
		var stopsValue = parseInt(stpValue) + parseInt(1);
		var checkValue = parseInt(stpValue - 1);
		if ( ($rootScope.extraStops['extraStopAddress_'+checkValue] == undefined || $rootScope.extraStops['extraStopAddress_'+checkValue] == '') && stpValue != 0 ) {
			//~ return false;
		} 
		
		$scope.autoFetchLoads = true;
		
		$rootScope.extraStopsLength = stopsValue;
		if ( stopsValue != '' && stopsValue != undefined ) {
			$rootScope.extraStopTotLength = true;
			var arrayLength = $rootScope.extraStop.length;
			
			if ( parseInt(arrayLength) <= parseInt(stopsValue) ) {
				var newStopValue = parseInt(stopsValue - 1);
				$rootScope.extraStop.push(newStopValue);
			} 
			 
			setTimeout(function(){ $rootScope.extraStopsAutoAddress( stopsValue, parameter );},500);
			$rootScope.editSavedLoad.Stops = stopsValue;
			$scope.autoFetchLoads = false;
		} else {
			$rootScope.extraStop = [];
			$rootScope.editSavedLoad.Stops = '0';
			$scope.autoFetchLoads = false;
		}
	}
	
	/**
	 * Creating Dynamic autocomplete for dynamic generated Extra stops
	 * 
	 */ 
		
	$rootScope.extraStopsAutoAddress = function(value, parameter ) {
		var integer = parseInt(value) - 1;
		
		var callRequest = 0;	
		if ( parameter == 'addRequest' ) {
			var dirRend = 'dirRender'+integer;
			var dirAuto = 'dirAutoComp'+integer;
			$scope[dirRend] = document.getElementById("extraStopAddAddress_"+integer);
			$scope[dirAuto] = new google.maps.places.Autocomplete($scope[dirRend],$scope.limitCountryOptions);
			google.maps.event.addListener($scope[dirAuto], 'place_changed', function () {			
						
				var places = $scope[dirAuto].getPlace();
				var result = $scope[dirAuto].getPlace();
				var street = "", street_number="", postal_code="", city = "", state = "", county = "", country = "";
				//~ if(typeof result.address_components.length != 'undefined' && result.address_components.length ){
				if(result.address_components ){
					
					for(var i = 0; i < result.address_components.length; i += 1) {
					  var addressObj = result.address_components[i];
					  for(var j = 0; j < addressObj.types.length; j += 1) {
					    switch(addressObj.types[j]){
					  		case "country": 			 		 country = addressObj.long_name;		break;	//Country
					  		case "administrative_area_level_1" : state 	= addressObj.short_name; 		break; 	//State
					  		case "administrative_area_level_2" : county = addressObj.long_name; 		break;	//County
					  		case "locality" :  					 city 	= addressObj.long_name; 		break;	//City
					  		case "route": 						 street = addressObj.long_name;			break;	//street
					  		case "street_number": 				 street_number =  addressObj.long_name;	break;	//street number
					  		case "postal_code": 				 postal_code = addressObj.long_name; 	break;	//Postal Code
					  	}
					  }
					}
				} else {
					if ( result.name != '' ) {
						var pickupaddress = result.name.split(',');
						lastItem = pickupaddress.pop();
						country = lastItem;
						
						secondLast = pickupaddress.pop();
						var checkInteger = /\d/.test(secondLast);
						if ( checkInteger ) {
							var splitedValue = secondLast.split(' ');
							
							if ( isNaN(splitedValue[0]) ) {
								state = splitedValue[0];
								postal_code = splitedValue[1];
							} else {
								state = splitedValue[1];
								postal_code = splitedValue[0];
							}
						} else {
							state = secondLast;
						}
						
						thirdLast = pickupaddress.pop();
						city = thirdLast;
						
						if ( pickupaddress.length ) {
							for( var i = 0; i < pickupaddress.length; i += 1 ){
								street += pickupaddress[i]+',';
							}
							street = street.replace(/,(?=[^,]*$)/, '');
						}
					}
				}
				
				$rootScope.extraStops['extraStopAddress_'+integer]	= street_number +' '+ street ;
				$rootScope.extraStops['extraStopCity_'+integer]			= city ;
				$rootScope.extraStops['extraStopState_'+integer]		= state ;
				$rootScope.extraStops['extraStopCountry_'+integer]		= country ;
							
				if((street != "" || street_number != "") && postal_code != ""){
					$rootScope.extraStops['extraStopZipCode_'+integer] = postal_code;
					callRequest = 1;
				} else if((street != "" || street_number != "") && postal_code == ""){
					$scope.autoFetchLoads = true;					
					dataFactory.httpRequest(URL+'/truckstop/getZipCode','POST',{},{stopArray: $rootScope.extraStops, indexValue : integer, type: 'extraStop'}).then(function(data) {
						if ( data.zipcode != undefined && data.zipcode != '' ) {
								$rootScope.extraStops['extraStopZipCode_'+integer] = data.zipcode;
						}
						$scope.autoFetchLoads = false;					
					});
					callRequest = 1;
			  	} else {
					$rootScope.extraStops['extraStopZipCode_'+integer] = "";
					callRequest = 1;
			  	}
			  	$rootScope.$apply();
			  	
			  	if ( callRequest == 1 )
					$scope.changeExtraStopsMilesDistance(integer);
			  	
			});
		} else {
			
			var dirRendEdit = 'dirRenderEdit'+integer;
			var dirAutoEdit = 'dirAutoCompEdit'+integer;
			$scope[dirRendEdit] = document.getElementById("extraStopEditAddress_"+integer);
			$scope[dirAutoEdit] = new google.maps.places.Autocomplete($scope[dirRendEdit],$scope.limitCountryOptions);
			google.maps.event.addListener($scope[dirAutoEdit], 'place_changed', function () {	
				//var places = $scope[dirAutoEdit].getPlace();
				var result = $scope[dirAutoEdit].getPlace();
				var street = "", street_number="", postal_code="", city = "", state = "", county = "", country = "";

				if(result.address_components){
					
					for(var i = 0; i < result.address_components.length; i += 1) {
					  var addressObj = result.address_components[i];
					  for(var j = 0; j < addressObj.types.length; j += 1) {
					    switch(addressObj.types[j]){
					  		case "country": 			 		 country = addressObj.long_name;		break;	//Country
					  		case "administrative_area_level_1" : state 	= addressObj.short_name; 		break; 	//State
					  		case "administrative_area_level_2" : county = addressObj.long_name; 		break;	//County
					  		case "locality" :  					 city 	= addressObj.long_name; 		break;	//City
					  		case "route": 						 street = addressObj.long_name;			break;	//street
					  		case "street_number": 				 street_number =  addressObj.long_name;	break;	//street number
					  		case "postal_code": 				 postal_code = addressObj.long_name; 	break;	//Postal Code
					  	}
					  }
					}
				} else {
					if ( result.name != '' ) {
						var pickupaddress = result.name.split(',');
						lastItem = pickupaddress.pop();
						country = lastItem;
						
						secondLast = pickupaddress.pop();
						var checkInteger = /\d/.test(secondLast);
						if ( checkInteger ) {
							var splitedValue = secondLast.split(' ');
							
							if ( isNaN(splitedValue[0]) ) {
								state = splitedValue[0];
								postal_code = splitedValue[1];
							} else {
								state = splitedValue[1];
								postal_code = splitedValue[0];
							}
						} else {
							state = secondLast;
						}
						
						thirdLast = pickupaddress.pop();
						city = thirdLast;
						
						if ( pickupaddress.length ) {
							for( var i = 0; i < pickupaddress.length; i += 1 ){
								street += pickupaddress[i]+',';
							}
							street = street.replace(/,(?=[^,]*$)/, '');
						}
					}
				}
				
				$rootScope.extraStops['extraStopAddress_'+integer]		= street_number +' '+ street ;
				$rootScope.extraStops['extraStopCity_'+integer]			= city ;
				$rootScope.extraStops['extraStopState_'+integer]		= state ;
				$rootScope.extraStops['extraStopCountry_'+integer]		= country ;
				
				if((street != "" || street_number != "") && postal_code != ""){
					$rootScope.extraStops['extraStopZipCode_'+integer] = postal_code;
					callRequest = 1;
				}else if((street != "" || street_number != "") && postal_code == ""){
					$scope.autoFetchLoads = true;					
					dataFactory.httpRequest(URL+'/truckstop/getZipCode','POST',{},{stopArray: $rootScope.extraStops, indexValue : integer, type: 'extraStop'}).then(function(data) {
						if ( data.zipcode != undefined && data.zipcode != '' ) {
								$rootScope.extraStops['extraStopZipCode_'+integer] = data.zipcode;
						}
						$scope.autoFetchLoads = false;					
					});
					callRequest = 1;
				} else {
			  		$rootScope.extraStops['extraStopZipCode_'+integer] = "";
			  		callRequest = 1;
			  	}
			  	$rootScope.$apply();
			  	
			  	if ( callRequest == 1 )
					$scope.changeExtraStopsMilesDistance(integer);
			});
			
			
		}
		
	}	
	
	/**
	 *  Re calculating the distance and all calculations on changing extra stops
	 */ 
	 
	$scope.changeExtraStopsMilesDistance = function(index) {
		//~ $scope.autoFetchLoads = true;
		setTimeout(function(){
			$rootScope.viaRoutes.splice(index,1);
				var viaRoute = $rootScope.extraStops['extraStopAddress_'+index]+','+$rootScope.extraStops['extraStopCity_'+index]+','+$rootScope.extraStops['extraStopState_'+index]+','+$rootScope.extraStops['extraStopCountry_'+index];
				viaRoute = viaRoute.trim();
				viaRoute = viaRoute.replace(/(^,)|(,$)/g, "");
					
			if (viaRoute != '' ) {
				$rootScope.viaRoutes.splice(index, 0, viaRoute)
			} 
			$scope.changeExtraStopsMilesDistanceNew($rootScope.viaRoutes,index);
		}, 200);
	}
	
	$scope.changeExtraStopsMilesDistanceNew = function(viaRoutesArray,index) {
		
		if ( viaRoutesArray.length > 0 ) {
			$scope.autoFetchLoads = true;
			
			var addType = 'extraStop';
			dataFactory.httpRequest(URL+'/truckstop/calculateNewDistance/'+$rootScope.primaryLoadId+'/'+$scope.primaryTripDetailId,'POST',{},{ allData : $rootScope.editSavedLoad, vehicleId : $rootScope.editSavedLoad.vehicle_id, searchType : addType, index:index, extraStopsAdded : $rootScope.extraStops, driverAssignType:$rootScope.driverAssignType}).then(function(dataRes) {
				if ( dataRes.distance != '' && dataRes.distance != undefined && dataRes.distance != null ) {
					$rootScope.editSavedLoad.Mileage = dataRes.distance;
					$rootScope.editSavedLoad.timer_distance = dataRes.distance;
					$rootScope.editSavedLoad.deadmiles = dataRes.new_deadmiles_Cal;
					
					$rootScope.editSavedLoad.loadedDistanceCost = dataRes.loadedDistanceCost;
					$rootScope.editSavedLoad.deadMileDistCost = dataRes.deadMileDistCost;
					$rootScope.editSavedLoad.estimatedFuelCost = dataRes.estimatedFuelCost;
					
					$rootScope.editSavedLoad.totalMiles = parseFloat( dataRes.distance)  + parseFloat($rootScope.editSavedLoad.deadmiles );
					$rootScope.editSavedLoad.totalCost = dataRes.overall_total_charge_Cal;
					$rootScope.editSavedLoad.overall_total_rate_mile = dataRes.overall_total_rate_mile_Cal;
					$rootScope.editSavedLoad.overallTotalProfit = dataRes.overall_total_profit_Cal;
					$rootScope.editSavedLoad.overallTotalProfitPercent = dataRes.overall_total_profit_percent_Cal;
					
					$scope.autoFetchLoads = false;
				} else {
					$scope.autoFetchLoads = false;
				}
			});
			
			$rootScope.nonewRequest = false;
		} else {
			$scope.autoFetchLoads = false;
		}
	}
	
	/**
	 * closing the extra stop on click of cancel button
	 * 
	 */
	
	$scope.closeExtraStopFields = function( index, loadId, removeExtraStopId) {				// parameter for diff. b/w add and edit load request
		$scope.autoFetchLoads = true;
		if ( index != undefined ) {
			var newIndex = $rootScope.extraStop.indexOf(index);
					
			var viaRoute = $rootScope.extraStops['extraStopAddress_'+index]+','+$rootScope.extraStops['extraStopCity_'+index]+','+$rootScope.extraStops['extraStopState_'+index]+','+$rootScope.extraStops['extraStopCountry_'+index];
			viaRoute = viaRoute.trim();
			viaRoute = viaRoute.replace(/(^,)|(,$)/g, "");
			
			$rootScope.extraStop = [];						// empty the extra stops count
			for( var k = 0, m = 0; k < parseInt($rootScope.editSavedLoad.Stops); k++ ) {

				if ( k >= parseInt(newIndex) ) {
					if ( k == parseInt($rootScope.editSavedLoad.Stops - 1) ) {		// delete always last extra stop
						delete $rootScope.extraStops['extraStopAddress_'+k];		// deleting added extra stop address
						delete $rootScope.extraStops['extraStopCity_'+k];			// deleting added extra stop address
						delete $rootScope.extraStops['extraStopState_'+k];			// deleting added extra stop address
						delete $rootScope.extraStops['extraStopCountry_'+k];		// deleting added extra stop address
						delete $rootScope.extraStops['extraStopTime_'+k];			// deleting added extra stop time
						delete $rootScope.extraStops['extraStopTimeRange_'+k];		// deleting added extra stop time range
						delete $rootScope.extraStops['extraStopDate_'+k];			// deleting added extra stop date
						delete $rootScope.extraStops['extraStopZipCode_'+k];		// deleting added extra stop zip code
						delete $rootScope.extraStops['extraStopPhone_'+k];			// deleting added extra stop Phone
						delete $rootScope.extraStops['extraStopName_'+k];			// deleting added extra stop zip code
						delete $rootScope.extraStops['extraStopEntity_'+k];
					} else {
						var n = parseInt(k + 1);
						$rootScope.extraStops['extraStopAddress_'+k]	=	$rootScope.extraStops['extraStopAddress_'+n];	// assigning next extra stop value to previous
						$rootScope.extraStops['extraStopCity_'+k]		=	$rootScope.extraStops['extraStopCity_'+n];		// assigning next extra stop value to previous
						$rootScope.extraStops['extraStopState_'+k]		=	$rootScope.extraStops['extraStopState_'+n];		// assigning next extra stop value to previous
						$rootScope.extraStops['extraStopCountry_'+k]	=	$rootScope.extraStops['extraStopCountry_'+n];	// assigning next extra stop value to previous
						$rootScope.extraStops['extraStopTime_'+k]		=	$rootScope.extraStops['extraStopTime_'+n];	
						$rootScope.extraStops['extraStopTimeRange_'+k]	=	$rootScope.extraStops['extraStopTimeRange_'+n];
						$rootScope.extraStops['extraStopDate_'+k]		=	$rootScope.extraStops['extraStopDate_'+n];	
						$rootScope.extraStops['extraStopZipCode_'+k]	=	$rootScope.extraStops['extraStopZipCode_'+n];	
						$rootScope.extraStops['extraStopPhone_'+k]		=	$rootScope.extraStops['extraStopPhone_'+n];	
						$rootScope.extraStops['extraStopName_'+k]		=	$rootScope.extraStops['extraStopName_'+n];	
						$rootScope.extraStops['extraStopEntity_'+k]		=  	$rootScope.extraStops['extraStopEntity_'+n];
						
						$rootScope.extraStop.push(k);					// push the count in array
					}	
					
				} else {
					$rootScope.extraStop.push(k);
				}
			}
			
			if($rootScope.extraStop.length ==0 ){
				$rootScope.extraStopTotLength = false;
			}


			if ( ~newIndex) {	
				var newExtraStopsValue = parseInt($rootScope.editSavedLoad.Stops - 1 );
				$rootScope.editSavedLoad.Stops = newExtraStopsValue;
				$rootScope.extraStopsLength = newExtraStopsValue;			// assinging same value to extraStopLength as of number of stops
			}

			if ( ~viaRoute ) {
				$rootScope.viaRoutes.splice(viaRoute,1);
			}			
			
			var primaryExtraStopId = '';
				
			if ( removeExtraStopId != '' && removeExtraStopId != undefined ) {
				primaryExtraStopId = removeExtraStopId;
			}
						
			dataFactory.httpRequest(URL+'/truckstop/removeExtraStopRecord/'+$rootScope.primaryLoadId+'/'+$scope.primaryTripDetailId+'/'+ primaryExtraStopId,'POST',{},{allData : $rootScope.editSavedLoad,vehicleId : $rootScope.editSavedLoad.vehicle_id, index:index, extraStopsAdded : $rootScope.extraStops,driverAssignType:$rootScope.driverAssignType  }).then(function(data) {
				$rootScope.editSavedLoad.Mileage = data.distance;
				$rootScope.editSavedLoad.timer_distance = data.distance;
				$rootScope.editSavedLoad.deadmiles = data.new_deadmiles_Cal;
				
				$rootScope.editSavedLoad.loadedDistanceCost = data.loadedDistanceCost;
				$rootScope.editSavedLoad.deadMileDistCost = data.deadMileDistCost;
				$rootScope.editSavedLoad.estimatedFuelCost = data.estimatedFuelCost;
				
				$rootScope.editSavedLoad.totalMiles = parseInt( data.distance)  + parseInt($rootScope.editSavedLoad.deadmiles );
				$rootScope.editSavedLoad.totalCost = data.overall_total_charge_Cal;
				$rootScope.editSavedLoad.overall_total_rate_mile = data.overall_total_rate_mile_Cal;
				$rootScope.editSavedLoad.overallTotalProfit = data.overall_total_profit_Cal;
				$rootScope.editSavedLoad.overallTotalProfitPercent = data.overall_total_profit_percent_Cal;
				
				$scope.autoFetchLoads = false;
			});
			
			
		} else {
			$scope.autoFetchLoads = false;
		}
	}	 
	
	//------------------------ Documents Upload ----------------------------------
	$scope.includeDropzone = true;
	$scope.dropzoneConfig = {
		parallelUploads: 3,
		maxFileSize: 10,
		url: URL+ '/truckstop/uploadDocs',
		addRemoveLinks: true, 
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .bmp, .svg',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("loadId", $rootScope.primaryLoadId);
		},
		success:function(file,response){
			file.previewElement.classList.add("dz-success");
			if(!response.error){ // succeeded
				this.removeFile(file);
				response = angular.fromJson(response);
				if ( response.loadIdNotExist != undefined && response.loadIdNotExist == 1 ) {
					$rootScope.alertloadmsg = false;
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = 'Error! : Please save the load details to upload document.';
				} else if ( response.error_exceed != undefined && response.error_exceed == 1 ) {
					$rootScope.alertloadmsg = false;
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = 'Error! : The uploaded document exceeds the maximum allowed limit of 10MB.';
				} else {
					$rootScope.Docs = response.result.dlist;
				}
				$scope.$apply();
			}
		},
	};
	
	$scope.perviewDoc = function($event){
		$(".pdf-preview").remove();
		var that = angular.element($event.currentTarget);
		$scope.docContent =  URL + '/'+ that.data('url');
		var docid =  that.data('id');
		//~ var loadid =  that.data('loadid');
		var ifrm = document.createElement("iframe");
		ifrm.setAttribute("src", $scope.docContent);
		ifrm.setAttribute("rel", "external");
					
		ifrm.style.width = "800px";
		ifrm.style.height = ($(window).height()-50)+'px';

		if($scope.docContent.indexOf('.pdf') > -1){
			var previewContainer = $compile('<div class="pdf-preview" >\
												<div ng-click="closePdfViewer()" class="close-overlay-pdf"></div>\
												<div class="pdf-overlay">\
													<div class="pdf-center">\
														<a href="" style="display:none;" ng-click="signPdf('+docid+'); clear();" class="add-sign">Sign Document</a>\
														<div id="pdf-file" class="pdf-file"></div>\
													</div>\
												</div>\
											</div>')($scope);
			var preview = $compile(ifrm)($scope);
			angular.element('body').append(previewContainer);
			angular.element('.pdf-preview #pdf-file').html(preview);
		}else if( ($scope.docContent.indexOf('.doc') > -1) || ($scope.docContent.indexOf('.docx') > -1) || ($scope.docContent.indexOf('.xls') > -1) || ($scope.docContent.indexOf('.xlsx') > -1)){
			ifrm.setAttribute("id", "download-doc");
			ifrm.setAttribute("style", "display:none");
			var preview = $compile(ifrm)($scope);
			angular.element('body').append(preview);
		} else {
			angular.element("#show-image").modal('show');
		}
	}
	
	/**
	 * send fo payment pdf preview
	 */ 
	$scope.perviewDocBilling = function(doc_type,name,docid){
		$(".pdf-preview").remove();
		$scope.docContent =  URL + '/assets/uploads/documents/'+doc_type+'/'+ name;
		
		var ifrm = document.createElement("iframe");
		ifrm.setAttribute("src", $scope.docContent);
		ifrm.setAttribute("rel", "external");
		ifrm.style.width = "800px";
		ifrm.style.height = ($(window).height()-50)+'px';

		if($scope.docContent.indexOf('.pdf') > -1){
			var previewContainer = $compile('<div class="pdf-preview" >\
												<div ng-click="closePdfViewer()" class="close-overlay-pdf"></div>\
												<div class="pdf-overlay">\
													<div class="pdf-center">\
														<a href="" style="display:none;" ng-click="signPdf('+docid+'); clear();" class="add-sign">Sign Document</a>\
														<div id="pdf-file-billing" class="pdf-file-billing"></div>\
													</div>\
												</div>\
											</div>')($scope);
			var preview = $compile(ifrm)($scope);
			angular.element('body').append(previewContainer);
			angular.element('.pdf-preview #pdf-file-billing').html(preview);
		} else if ( ($scope.docContent.indexOf('.doc') > -1) || ($scope.docContent.indexOf('.docx') > -1) || ($scope.docContent.indexOf('.xls') > -1) || ($scope.docContent.indexOf('.xlsx') > -1)){
			ifrm.setAttribute("id", "download-doc");
			ifrm.setAttribute("style", "display:none");
			var preview = $compile(ifrm)($scope);
			angular.element('body').append(preview);
		} else {
			angular.element("#show-image").modal('show');
		}
	}
	
	$rootScope.clearDocsListing = function(){
		angular.element(".truck-load-pdf").remove();
	}
    
    $scope.deleteDoc = function(id,filename,loadId,doc_type, brokerId){
		angular.element("#confirm-delete").modal('show');
		angular.element("#confirm-delete").data("loadId",loadId);
		angular.element("#confirm-delete").data("id",id);
		angular.element("#confirm-delete").data("filename",filename);
		angular.element("#confirm-delete").data("doc_type",doc_type);
		angular.element("#confirm-delete").data("saveBrokerId",brokerId);
		angular.element(".doc-item").show();
		angular.element(".common-item").hide();
	}

	$scope.confirmDocDelete = function(confirm){
		if(confirm == 'yes'){
			var loadId = angular.element("#confirm-delete").data("loadId");
			var id  = angular.element("#confirm-delete").data("id");
			var filename  = angular.element("#confirm-delete").data("filename");
			var doc_type  = angular.element("#confirm-delete").data("doc_type");
			var assignedBrokerId  = angular.element("#confirm-delete").data("saveBrokerId");
			
			if ( loadId != '' && loadId != undefined ) {
				dataFactory.httpRequest(URL+'/truckstop/deleteDocument','POST',{},{loadId:loadId,docId:id,filename:filename,doc_type:doc_type,assignedBrokeId : assignedBrokerId}).then(function(data){
					$rootScope.alertExceedMsg = false;
					if ( doc_type == 'broker' ) {
						$scope.brokerDocuments = data.result.brokerDocuments;
						$rootScope.alertloadmsg = true;
						$rootScope.Message = 'Success: The broker document has been deleted successfully.';
					} else {
						$rootScope.Docs = data.result.dlist;
						$rootScope.alertloadmsg = true;
						if ( $rootScope.saveTypeLoad == 'sendForPayment' && doc_type == 'invoice' ) {
							$rootScope.Message = 'Success: The document has been deleted successfully and load has been moved to billable section.';
							$rootScope.showSendPaymentsLoads();				// fetching sent for payment loads if invoice for that load is deleted.
							$rootScope.noLoadSelected = true;
							$rootScope.selectedIndex = '';			// setting selected index to empty for send for payment page
							$rootScope.notHitPaymentLoadRequest = 0;		// not hitting request on send for payment page if invoice is deleted
							$rootScope.saveTypeLoad == 'sendForPayment'
							$rootScope.readyToSendPaymentCount = parseInt($rootScope.readyToSendPaymentCount) - 1;		// changing the inbox send for payment count on deleting invoice
						} else {
							$rootScope.Message = 'Success: The document has been deleted successfully.';
							if($rootScope.saveTypeLoad == 'filteredBillingLoads'){
								$rootScope.showInvoicedLoads();
							}
						}							
					}
				});
			}
		} else {
			angular.element("#confirm-delete").removeData("loadId");
			angular.element("#confirm-delete").removeData("id");
			angular.element("#confirm-delete").removeData("filename");
			angular.element("#confirm-delete").removeData("doc_type");	
			angular.element("#confirm-delete").removeData("saveBrokerId");	
		}
		angular.element("#confirm-delete").modal('hide');
		$('#confirm-delete').on('hidden.bs.modal', function (event) {
			$("body").addClass('modal-open');
		});
		angular.element(".doc-item").hide();
		angular.element(".common-item").show();
	}

	$scope.closePdfViewer = function(){
		angular.element('.pdf-preview').remove();
	}

	$scope.fetchJobDocs = function(truckstopId, loadId , docType){
		angular.element(".file-error").hide();
		angular.element(".file-error-text").html('');	
		$rootScope.whenTicketSaved = true;	
		if ( loadId == undefined || loadId == '' || loadId == 0 ) {
			$scope.selectTab('documents');
			$rootScope.alertExceedMsg = true;
			$rootScope.ExceedMessage = 'Error !: Please save the load details to view documents.';
			$rootScope.whenTicketSaved = false;
			return false;
		}
		
		angular.element(".progress-div").show();
		if ( docType == 'broker' ) {
			dataFactory.httpRequest(URL+'/truckstop/fetchBrokerDocuments/'+loadId).then(function(data){
				data = angular.fromJson(data);
				$scope.brokerDocuments = data.result.brokerDocuments;
				angular.element(".progress-div").hide();
			});
		} else {		
			dataFactory.httpRequest(URL+'/truckstop/fetchDocuments','POST',{},{loadId:$rootScope.primaryLoadId}).then(function(data){
				data = angular.fromJson(data);
				$rootScope.Docs = data.result.dlist;
				angular.element(".progress-div").hide();
			});
			
			if ( docType == 'notRefresh' ) {			// notRefresh parameter to hold the success and error messages on document tab for load popup
				
			} else {
				$scope.selectTab('documents');
			}
		}		
	}	
	
	$scope.selectTab = function(label){
		$rootScope.editLoads = $rootScope.showMaps  = $rootScope.matchingTrucks   = $rootScope.brokerDetailInfo  = $rootScope.alertloadmsg = $rootScope.alertExceedMsg = $rootScope.billingDetailsInfo = false;	
		$rootScope.showhighlighted = label;
		$rootScope.showAddhighlighted = label;
	}
    /**
     * Billing page start
     **/
		
	$scope.uploadRateSheet = function( loadId ) {
		if ( loadId != undefined && loadId != '' && loadId != 0  ) {					// check if load id exist in order to upload file
			if ( $rootScope.rateSheetUploaded == 'yes' ) {
				$rootScope.rateSheetUploadConfirm = $rootScope.languageArray.rateSheetConfirmMessage;
				$('#documentsAreadyUploaded').modal('show');
			} else {
				$rootScope.uploadRateSheetDoc = true;
				$rootScope.showUploadRateSheetButton = false;
			}
		} else {
			$rootScope.alertloadmsg = false;
			$rootScope.alertExceedMsg = true;
			$rootScope.ExceedMessage = 'Error !: Please save the load details in order to upload ratesheet file.';
		}
	}
	
	/**
	 * confirm for Proof of delivery upload
	 */ 
	$scope.uploadPOD = function( loadId ) {
		if ( $rootScope.podDocUploaded == 'yes' ) {
			$rootScope.rateSheetUploadConfirm = $rootScope.languageArray.podConfirmMessage;
			$('#documentsAreadyUploaded').modal('show');
		} else {
			$rootScope.uploadPODDoc = true;
			$rootScope.showUploadPODButton = false;
		}
		
	}
	
	/**
	 * confirm for broker Upload
	 */ 
	$scope.uploadBrokerDocument = function( loadId, brokerId ) {
		if ( loadId != undefined && loadId != '' && brokerId != undefined && brokerId != '' ) {
			$rootScope.uploadBrokerDoc = true;
			$rootScope.showUploadBrokerButton = false;
		} else {
			$rootScope.alertloadmsg = false;
			$rootScope.alertExceedMsg = true;
			$rootScope.ExceedMessage = 'Error !: Please save the load details and broker info in order to upload broker document file.';
		}
	}
	
	$scope.confirmDocumentUploadStatus = function( status) {
		if ( status == 'yes' ) {
			$rootScope.uploadRateSheetDoc = true;
			$rootScope.uploadPODDoc = true;
			$rootScope.showUploadRateSheetButton = false;
			$rootScope.showUploadPODButton = false;
		} else {
			$rootScope.uploadRateSheetDoc = false;
			$rootScope.uploadPODDoc = false;
			$rootScope.showUploadRateSheetButton = true;
			$rootScope.showUploadPODButton = true;
		}
		$('#documentsAreadyUploaded').modal('hide');
		$('#documentsAreadyUploaded').on('hidden.bs.modal', function (event) {
			$("body").addClass('modal-open');
		});
	}
	
	/***Generating Invoice *****/
	$scope.generateInvoiceForLoad = function( loadId ) {
		if ( loadId == undefined || loadId == '' || loadId == 0 ) {
			$rootScope.alertExceedMsg = true;
			$rootScope.ExceedMessage = 'Error !: Please save the load details to in order to generate invoice.';
			return false;
		}
		
		$scope.autoFetchLoads = true;
		dataFactory.httpRequest(URL+'/assignedloads/generateInvoice/'+loadId+'/'+$rootScope.saveTypeLoad).then(function(data){
			if ( data.showError == 1 && data.showError != undefined ) {
				$rootScope.alertloadmsg = false;
				$rootScope.alertExceedMsg = true;
				$rootScope.ExceedMessage = data.errorMessage;
			} else {
				if ( $rootScope.saveTypeLoad != undefined && $rootScope.saveTypeLoad == 'filteredBillingLoads' ){
					$rootScope.showInvoicedLoads();
				}else  if ( $rootScope.saveTypeLoad != undefined && $rootScope.saveTypeLoad != 'sendForPayment' ){
					$rootScope.billingLoads = data.billingLoads;
				}
					
				$rootScope.alertloadmsg = true;
				$rootScope.alertExceedMsg = false;
				$rootScope.Message = 'Success : The invoice has been generated successfully.';
			}
			$timeout(function() { $scope.fetchJobDocs('',loadId,'notRefresh'); },1000);				// notRefresh parameter to hold the success and error messages on document tab for load popup
			$scope.autoFetchLoads = false;
		});
	}
		
	$scope.dropzoneConfigRateSheet = {
		parallelUploads: 1,
		maxFileSize: 10,
		url: URL+'/truckstop/uploadDocs/rateSheet',
		addRemoveLinks: true, 
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .svg, .bmp',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("loadId", $rootScope.primaryLoadId);
		},
		success:function(file,response){
			$rootScope.alertloadmsg = true;
			$rootScope.Message = $rootScope.languageArray.rateSheetSuccMsg;
			$rootScope.uploadRateSheetDoc = false;
			$rootScope.showUploadRateSheetButton = true;
			$rootScope.rateSheetUploaded = 'yes';
			
			file.previewElement.classList.add("dz-success");
			if(!response.error){ // succeeded
				this.removeFile(file);
				response = angular.fromJson(response);
				$rootScope.alertExceedMsg = false;
				if ( response.fileCompressionIssue != undefined && response.fileCompressionIssue == 1 ) {
					$rootScope.alertloadmsg = false;
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = 'Error! : The uploaded pdf file seems compressed. Please upload a pdf file without any compression.';
				}
				
				if ( response.error_exceed == 1 ) {
					$rootScope.alertloadmsg = false;
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = 'Error! : The uploaded document exceeds the maximum allowed limit of 128MB.';
				}
				$scope.$apply();
			}
		},
	};
	    
	$scope.dropzoneConfigPOD = {
		parallelUploads: 1,
		maxFileSize: 10,
		url: URL+ '/truckstop/uploadDocs/pod',
		addRemoveLinks: true, 
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .svg, .bmp',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("loadId", $rootScope.primaryLoadId);
		},
		success:function(file,response){
			$rootScope.alertloadmsg = true;
			$rootScope.Message = $rootScope.languageArray.podSuccMsg;
			$rootScope.uploadPODDoc = false;
			$rootScope.showUploadPODButton = true;
			$rootScope.podDocUploaded = 'yes'; 
			
			file.previewElement.classList.add("dz-success");
			if(!response.error){ // succeeded
				this.removeFile(file);
				response = angular.fromJson(response);
				
				if ( response.fileCompressionIssue != undefined && response.fileCompressionIssue == 1 ) {
					$rootScope.alertloadmsg = false;
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = 'Error! : The uploaded pdf file seems compressed. Please upload a pdf file without any compression.';
				}
				
				if ( response.error_exceed == 1 ) {
					$rootScope.alertloadmsg = false;
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = 'Error! : The uploaded document exceeds the maximum allowed limit of 128MB.';
				}
				$scope.$apply();
			}
		},
	};
	
	$scope.dropzoneConfigBrokerDoc = {
		parallelUploads: 3,
		maxFileSize: 10,
		url: URL+ '/truckstop/uploadDocs/broker',
		addRemoveLinks: true, 
		acceptedFiles: 'image/*, application/pdf, .xls, .xlsx, .doc, .docx, .txt, .svg, .bmp',
		init:function(){
			$rootScope.imDropzone = this;
		},
		sending:function(file, xhr, formData){
			formData.append("loadId", $rootScope.primaryLoadId);
			formData.append("brokerId", $rootScope.editSavedLoad.broker_id);
		},
		success:function(file,response){
			$rootScope.alertloadmsg = true;
			$rootScope.alertExceedMsg = false;
			$rootScope.Message = 'Success: The broker document has been uploaded successfully.';
												
			file.previewElement.classList.add("dz-success");
				
			if(!response.error){ // succeeded
				this.removeFile(file);
				response = angular.fromJson(response);
				if ( response.error_exceed == 1 ) {
					$rootScope.alertloadmsg = false;
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = 'Error! : The uploaded document exceeds the maximum allowed limit of 128MB.';
				}
				
				$scope.brokerDocuments = response.result.brokerDocuments;
				$scope.$apply();
				$timeout(function() {$scope.fetchJobDocs('0',$rootScope.editSavedLoad.broker_id,'broker');},800);
			}
		},
	};
		 
	/**
	 * Onpickup date change recalculate all distances
	 */
	 
	$scope.onPickupDateChange = function(date,parameter) {
		if ( parameter == 'pick' )
			$rootScope.minDestdate = new Date(date);
	
		if ( date != '' && date != undefined ) {
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/truckstop/checkDateLoadExist/'+parameter,'POST',{},{ allData : $rootScope.editSavedLoad, setDeadMilePage : $rootScope.setDeadMilePage, extraStopsDate : $rootScope.extraStops ,driverAssignType:$rootScope.driverAssignType }).then(function(dataRes) {

				$rootScope.alertloadmsg = false;
				if ( dataRes.error != undefined && dataRes.error != '' && dataRes.error == 'alreadyBookedPickDate' ) {
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = 'Caution: This driver is already assigned with load for '+date+'.';
				} else if ( dataRes.error != undefined &&  dataRes.error != '' && dataRes.error == 'alreadyBookedDeliveryDate' ) {
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = 'Caution: This driver is already assigned with load for '+date+' delivery date.';
				} else if ( dataRes.error != undefined &&  dataRes.error != '' && dataRes.error == 'extraStopDateIssue') {
					$rootScope.alertExceedMsg = true;
					$rootScope.ExceedMessage = dataRes.errorMsg;
				} else if ( dataRes.estimatedFuelCost != undefined && dataRes.estimatedFuelCost != '' ) {
					$rootScope.alertExceedMsg = false;
					$rootScope.editSavedLoad.deadmiles = dataRes.deadmilesDistance;
					$rootScope.editSavedLoad.deadMileDistCost = dataRes.deadMileDistCost;
					$rootScope.editSavedLoad.estimatedFuelCost = dataRes.estimatedFuelCost;
					
					if ( $rootScope.editSavedLoad.deadmiles == '' || $rootScope.editSavedLoad.deadmiles == undefined )
						$rootScope.editSavedLoad.deadmiles = 0;
						
					if ( $rootScope.editSavedLoad.Mileage == '' || $rootScope.editSavedLoad.Mileage == undefined )
						$rootScope.editSavedLoad.Mileage = 0;
					
					$rootScope.editSavedLoad.timer_distance = $rootScope.editSavedLoad.Mileage;	
					$rootScope.editSavedLoad.totalMiles = parseFloat( $rootScope.editSavedLoad.Mileage )  + parseFloat($rootScope.editSavedLoad.deadmiles );
					$rootScope.editSavedLoad.totalCost = dataRes.overall_total_charge_Cal;
					$rootScope.editSavedLoad.overallTotalProfit = dataRes.overall_total_profit_Cal;
					$rootScope.editSavedLoad.overallTotalProfitPercent = dataRes.overall_total_profit_percent_Cal;
				} else {
					$rootScope.alertExceedMsg = false;
				}
				$scope.autoFetchLoads = false;
			});			
		}
	} 
	
	/**
	 * Check if extra Stop Date is smaller or equal to pick date and is in b/w pickup and delivery date
	 */
	
	$scope.onPickupDateChangeExtraStop = function( index, parameter ) {
		//~ if ( $rootScope.editSavedLoad.PickupDate == undefined || $rootScope.editSavedLoad.PickupDate == '' || $rootScope.editSaveLoad.PickupDate == '0000-00-00' ) {
			//~ $rootScope.alertExceedMsg = true;
			//~ $rootScope.alertloadmsg = false;
			//~ $window.scrollTo(0, 0);
			//~ $rootScope.ExceedMessage = 'Error !: Please enter pickup date in order to enter extra stop date.';
			//~ $rootScope.extraStops['extraStopDate_'+index] = '';
			//~ $("#extraStopDateId_"+index ).datepicker('setDate','');
			//~ return false;
		//~ }
		
		$scope.autoFetchLoads = true;
		dataFactory.httpRequest(URL+'/truckstop/compareExtraStopDates/'+parameter,'POST',{},{ allData : $rootScope.editSavedLoad, index : index, extraStopsDate : $rootScope.extraStops }).then(function(dataRes) {
			if ( dataRes.error != undefined && dataRes.error != '' ) {
				$rootScope.alertExceedMsg = true;
				$rootScope.alertloadmsg = false;
				$rootScope.ExceedMessage = 'Caution !:'+dataRes.error;
			} else {
				$rootScope.alertExceedMsg = false;
			}
			
			$scope.autoFetchLoads = false;
		});
	}
	
	/**
	 * Re Calculations on change of manual deadmiles
	 */ 
	 
	$scope.onCustomMilesChange = function( miles ) {
		if ( miles != '' && miles != undefined ) {
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/truckstop/reCaluclationsOfmiles/','POST',{},{ allData : $rootScope.editSavedLoad,driverAssignType:$rootScope.driverAssignType }).then(function(dataRes) {
				if ( dataRes) {
					$rootScope.editSavedLoad.deadmiles = dataRes.deadmilesDistance;
					$rootScope.editSavedLoad.deadMileDistCost = dataRes.deadMileDistCost;
					$rootScope.editSavedLoad.loadedDistanceCost = dataRes.loadedDistanceCost;
					$rootScope.editSavedLoad.estimatedFuelCost = dataRes.estimatedFuelCost;
					
					if ( $rootScope.editSavedLoad.Mileage == '' || $rootScope.editSavedLoad.Mileage == undefined )
						$rootScope.editSavedLoad.Mileage = 0;
					
					$rootScope.editSavedLoad.timer_distance = $rootScope.editSavedLoad.Mileage;	
					$rootScope.editSavedLoad.totalMiles = parseFloat( $rootScope.editSavedLoad.Mileage )  + parseFloat($rootScope.editSavedLoad.deadmiles );
					$rootScope.editSavedLoad.totalCost = dataRes.overall_total_charge_Cal;
					$rootScope.editSavedLoad.overallTotalProfit = dataRes.overall_total_profit_Cal;
					$rootScope.editSavedLoad.overallTotalProfitPercent = dataRes.overall_total_profit_percent_Cal;
				}
				$scope.autoFetchLoads = false;
			});
		}
	}
	
	/**
	 * Hiding job ticket popup on outer click or cross click
	 */
	
	$scope.hideLoadDetailPopup = function() {
		$rootScope.firstTimeClick = true;
		var url = decodeURI($rootScope.absUrl.q);
		
		if ( url != '' && url != undefined && url != 'undefined' ) {
			$state.go('searchresults', {q:url}, {notify: false,reload: false});
		} else {
			$state.go('myLoad');	
		}	
	}
	
	/*Changing url on outer click of popup*/
	$(document).off('click','#edit-fetched-load').on("click",'#edit-fetched-load', function(event){
		var $trigger1 = $(".popup-container-wid1");
		if($trigger1 !== event.target && !$trigger1.has(event.target).length){
			var url = decodeURI($rootScope.absUrl.q);
			
			if ( url != '' && url != undefined && url != 'undefined' ) {
				$state.go('searchresults', {q:url}, {notify: false,reload: false});
			} else {
				$state.go('myLoad');	
			}	
		}
	}); 
});