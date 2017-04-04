app.controller('assignedLoadsController', ["dataFactory","$scope", "PubNub", "$http","$rootScope", "$state","$location","$cookies","$stateParams", "$localStorage", "getAllAssignedLoads","$compile","$filter","$log","ganttUtils",'GanttObjectModel', 'Sample', 'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window','$sce','$timeout','DTOptionsBuilder',function(dataFactory,$scope, PubNub, $http ,$rootScope ,$state, $location ,  $cookies, $stateParams, $localStorage, getAllAssignedLoads , $compile,$filter,$log,utils, ObjectModel, Sample, mouseOffset, debounce, moment,$q,$window,$sce,$timeout,DTOptionsBuilder){
	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	//----------drop-down ---------------------------

	$scope.duplicatejobstatus='';
	$scope.HOS = {}   ;
	$scope.trustAsHtml = function(value) {
            return $sce.trustAsHtml(value);
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

	

	//----------drop-down ---------------------------	

	
	$rootScope.Message = '';
	$scope.siteURL = URL;
	$scope.showGantt = false;
	$scope.mindate = new Date();
	
	$rootScope.fetchnewsearch = false;
	$rootScope.showHeader = true;
	$scope.loadsData = [];
	$scope.saveLoadsData = [];
	$rootScope.tableTitle = [];
	$rootScope.Docs = [];
	$scope.vDriversList = [];
	
	$rootScope.assignedLoads = (getAllAssignedLoads.assigned_loads != undefined ) ? getAllAssignedLoads.assigned_loads : [];
	if(Object.keys($rootScope.assignedLoads).length <= 0){
		$scope.haveRecords = true;
	} else {
		$scope.haveRecords = false;
	}
	
	$scope.DeliveryDateSortType = 'DESC';				// initially setting value of delivery date column to desc
	$rootScope.tableTitle.push(getAllAssignedLoads.table_title);
	
	$rootScope.selectedVehicleId = '';
	$rootScope.vehicleIdRepeat = getAllAssignedLoads.vehicleIdRepeat;
	$scope.vDriversList = getAllAssignedLoads.labelArray;
	$scope.total = getAllAssignedLoads.total;

	if ( $cookies.get('_globalDropdown') != undefined ) {		// initially setting value to run query on change of date picker
		itemNew = JSON.parse($cookies.get('_globalDropdown'));
		$rootScope.selectedVehicle = itemNew.vid;
		if(itemNew.vid == "" || itemNew.label == "" ){  
            $rootScope.selScope = [];
            $rootScope.scope = "all";
        } else if (itemNew.label == '_idispatcher') {
            $rootScope.selScope = [];
            $rootScope.scope = "dispatcher";
			angular.forEach($scope.vDriversList, function(value, key) {
                if(value.username == itemNew.username && value.label != '_idispatcher'){
                    $rootScope.selScope.push(value.vid); //Add driver ids
                }
            });
        } else {
        	if(itemNew.label == '_team'){
        		$rootScope.scope = "team";
        	}else{
        		$rootScope.scope = "driver";
        	}
            $rootScope.selScope = [];     
            $rootScope.selScope.push(itemNew.vid);  
            $rootScope.setVehicleDriverId = itemNew.id;				// setting vehicle driver id to fetch saved loads
            $rootScope.setVehicleSecondDriverId = itemNew.team_driver_id;				// setting vehicle driver id to fetch saved loads
        }
	}
	
	$rootScope.selectedScope = getAllAssignedLoads.selectedDriver;  // Before it is search_label 
			
	$rootScope.selectedVehicleId = ($rootScope.vehicleIdRepeat != undefined && $rootScope.vehicleIdRepeat != '' ) ? $rootScope.vehicleIdRepeat.toString() : '';
	$scope.deletedRowIndex = '';
	
	$scope.noRecordFoundMessage = $rootScope.languageCommonVariables.noRecordFound;
	$rootScope.saveTypeLoad = 'assignedLoads';    			// setting the save type for dynamic changing the listing on routes
	$scope.loadSource = getAllAssignedLoads.loadSource; 			// setting load source for getting loads listing
	
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

	$scope.groupFind = function(item){
        if(item.username !== "")
            return 'Dispatcher: '+item.username;
        else
            return item.username;
    }


    //-------------- Pagination functions ------------------------- 

	//Set data for pagiantion
	$scope.itemsPerPage     = 20;
	$scope.perPageOptions   = [10, 20, 50];
	$scope.currentPage      = 1,
	//$scope.total            = 0;
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
    	//~ var canceller, isSending = false;
    	//~ if(isSending) {
            //~ canceller.resolve();
        //~ }
        //~ isSending = true;
        //~ canceller = $q.defer();
		$scope.autoFetchLoads = true;
        dataFactory.httpRequest(URL+'/Assignedloads/getRecords/','Post',{} ,{ pageNo:pageNumber, itemsPerPage:$scope.itemsPerPage,searchQuery: search, sortColumn:sortColumn, sortType:sortType,startDate: $scope.dateRangeSelector.startDate, endDate:$scope.dateRangeSelector.endDate }).then(function(data){
        	$scope.autoFetchLoads = false;
        	$rootScope.assignedLoads = data.data;
        	if(Object.keys($rootScope.assignedLoads).length <= 0){
				$scope.haveRecords = true;
			}else{
				$scope.haveRecords = false;
			}
            $scope.total = data.total;
            //~ isSending = false;
        	return data;
		});
		//~ canceler.resolve();  // Aborts the $http request if it isn't finished.
    };

    $scope.callSearchFilter = function(query){
    	$scope.loadNextPage(($scope.currentPage - 1), query, $scope.lastSortedColumn,$scope.lastSortType);
    };

    $scope.sortCustom = function(sortColumn,type) {
		type = type == "ASC" ? "DESC" : "ASC";
		$scope.lastSortedColumn = sortColumn;
    	$scope.lastSortType 	= type;
    	$scope.idSortType = ''; $scope.PointOfContactPhoneSortType = ''; $scope.equipment_optionsSortType = ''; $scope.LoadTypeSortType = ''; $scope.PickupDateSortType = ''; $scope.DeliveryDateSortType = ''; $scope.OriginCitySortType = ''; $scope.OriginStateSortType = ''; $scope.DestinationCitySortType = ''; $scope.DestinationStateSortType = ''; $scope.driverNameSortType = ''; $scope.invoiceNoSortType = ''; $scope.PaymentAmountSortType = ''; $scope.MileageSortType = '';$scope.deadmilesSortType = ''; $scope.LengthSortType = ''; $scope.LengthSortType = ''; $scope.WeightSortType = ''; $scope.companyNameSortType = ''; $scope.load_sourceSortType = ''; $scope.JobStatusSortType = ''; $scope.RpmSortType = '';

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
    		case 'rpm'			 		: $scope.RpmSortType = type; break;
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

	
	/** Changing driver in dropdown */
	$scope.onSelectVehicleCallback = function (item, model){
		$rootScope.selectedScope = item.vid;  //Vehicle id
    	$rootScope.selectedVehicle = item.vid;  //Vehicle id
    	$cookies.remove("_globalDropdown");
        $cookies.putObject('_globalDropdown', item);    
    	
        if(item.vid == "" ){  
            $rootScope.selScope = [];
            $rootScope.scope = "all";
            $rootScope.tableTitle = [];
            $rootScope.tableTitle.push("All Groups");
        } else if (item.label == '_idispatcher') {
            $rootScope.selScope = [];
            $rootScope.scope = "dispatcher";
            $rootScope.tableTitle = [];
            $rootScope.tableTitle.push("Dispatcher : "+item.username);
            angular.forEach($scope.vDriversList, function(value, key) {
                if(value.username == item.username && value.label != '_idispatcher'){
                    $rootScope.selScope.push(value.vid); //Add driver ids
                }
            });
        } else {
        	if(item.label == '_team'){
        		$rootScope.scope = "team";
        	}else{
        		$rootScope.scope = "driver";
        	}
        	
            $rootScope.selScope = [];    
            $rootScope.selScope.push(item.vid);  
            $rootScope.setVehicleDriverId = item.id;				// setting vehicle driver id to fetch saved loads
            $rootScope.setVehicleSecondDriverId = item.team_driver_id;
        }
    }

	
	$rootScope.nonewRequest = false;
	
	$scope.showAssignedLoad = function( valueArray ) {

		$scope.showRouteOnMap = true;
		$scope.showGantt = true;
		console.log(valueArray);
		dataFactory.httpRequest(URL+'/iterationloads/getIterationLoadNextDate/'+valueArray.vehicle_id,'Post',{} ,{ valueArray : valueArray , DriverName : $rootScope.tableTitle[0] , previousDate : $scope.previousDate, skipSaveSession:'yes' , calAssignedHours : 'yes',vehicleGPS:true}).then(function(data)
		{
	
			valueArray.nextPickupDate1 = $filter('date')(new Date(data.nextPickupDate1), 'MM/dd/yyyy');
			valueArray.drivingHours = data.drivingHours;
			valueArray.previousDate = $filter('date')(new Date($scope.previousDate), 'MM/dd/yyyy');
			valueArray.PickUpDate = data.PickUpDate;

			//---------------- Hours of Service --------------
			$scope.HOS.totalDays  	= data.totalWorkingDays;
			$scope.HOS.startDate 	= data.PickUpDate;
			$scope.HOS.endDate 		= data.nextPickupDate1;
			$scope.HOS.lastDayHours	= data.hoursRemainingNextDay;
			$scope.HOS.originCity	= valueArray.OriginCity;
			$scope.HOS.OriginState	= valueArray.OriginState;
			$scope.HOS.DestinationCity	= valueArray.DestinationCity;
			$scope.HOS.DestinationState	= valueArray.DestinationState;

			//---------------- Hours of Service --------------


			//valueArray.dailyDriving 	= data.dailyDriving;
			//valueArray.totalDrivingHour = data.totalDrivingHour;
			//valueArray.totalWorkingDays = data.totalWorkingDays;
			//valueArray.hoursRemaining 	= data.hoursRemaining;
			//valueArray.drivingHours 	= data.drivingHours;
			//valueArray.workingHour 	= data.compWorkingHours;
			valueArray.vehicleLog 	= data.vehicleLogForJob;

			if ( valueArray.PickupAddress != '' && valueArray.PickupAddress != undefined ) {
				startAddress = valueArray.PickupAddress;
			} else {
				startAddress = valueArray.OriginCity+', '+ valueArray.OriginState + ', USA';
			}
				
			if ( valueArray.DestinationAddress != '' && valueArray.DestinationAddress != undefined ) {
				destAddress = valueArray.DestinationAddress;
			} else {
				destAddress = valueArray.DestinationCity+', '+ valueArray.DestinationState + ', USA';
			}
			
			latValue = '';
			lngValue = '';
			stateValue = '';
			cityValue = '';
			labelValue = '';
	
			if ( valueArray.JobStatus == 'inprogress' ) {			
					dataFactory.httpRequest(URL+'/assignedloads/fetchVehicleAddress/'+valueArray.vehicle_id).then(function(result){
						if ( result.latitude  != '' && result.latitude != undefined ) {
							latValue = result.latitude;
							lngValue = result.longitude;
							stateValue = result.state;
							cityValue = result.city;
							labelValue = result.label;
						}
					});
				setTimeout(function(){
					showGoogleMapRoutes(startAddress, destAddress, latValue, lngValue, stateValue, cityValue, labelValue, data.jobLogOnMap);
				},1000);
			} 
			else {
				showGoogleMapRoutes(startAddress, destAddress, latValue, lngValue, stateValue, cityValue, labelValue, data.jobLogOnMap);
			}
			if(valueArray.vehicleLog.length != 0){
				$scope.hosType = $rootScope.languageArray.actualHOS;
				$scope.renderVehicleLogOnGantt(valueArray);
			}else{
				$scope.hosType = $rootScope.languageArray.estimatedHOS;
				$scope.renderHOSGantt($scope.HOS);
			}
		});
		
		/************* scroll down page to map -r288 *************/
		$timeout(function(){
				$scope.header = angular.element($('#headerFixed')).height();
				$scope.height = angular.element($('.show-map-on-top')).offset().top;
				$('html,body').animate({scrollTop:$scope.height-($scope.header+25)}, 2000);
			},2000);
	/************* scroll down page to map -r288 *************/
	}

	$scope.getImageMarker = function(text,type,fmap){
		var returnImg = {};
		var canvas = document.getElementById('viewport'),
		context = canvas.getContext('2d');
		//Green Pointer
		
		canvas.height = $scope.base_image.height ;
		canvas.width = $scope.base_image.width ;
		context.clearRect(0, 0, canvas.width, canvas.height);
		context.drawImage($scope.base_image,0,0);
		context.font = 'bold 14px Arial';
		context.fillStyle = '#359407';
		context.fillText(text,9,19);
		returnImg.green =  canvas.toDataURL();
		
		if(fmap == true)	{
		//Red Pointer
		
			context.clearRect(0, 0, canvas.width, canvas.height);
			context.drawImage($scope.base_image2,0,0);
			context.font = 'bold 14px Arial';
			context.fillStyle = '#F62A2A';
			context.fillText(text,9,19);
			returnImg.red =  canvas.toDataURL();
			return returnImg;	
		}
		return returnImg.green;
	}
	
	function showGoogleMapRoutes(startAddress, destAddress, latValue, lngValue,stateValue, cityValue, labelValue,jobLogOnMap) {
		var mainHeight = $(window).height();
		var head = 0;
		var ganttHeight = 0;
		var bodyHeight = mainHeight-(ganttHeight+head);
		var dataHeight = (bodyHeight/2)+100;
		var mapHeight = bodyHeight/2;
		$("#map_canvas").height(mapHeight);
		$(".change-table-height").css('max-height',dataHeight);
		$(".map-distance-detail").css('max-height',mapHeight);

        arrayLength = 1;
			
			var mapOptions = {
				zoom: 4,
				center: {lat: 37.09024, lng: -95.712891},
				scrollwheel: false, 
				scaleControl: false, 
				icon:"./pages/img/truck-stop.png"
			}
			
			$scope.map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
			$scope.directionsService = new google.maps.DirectionsService;
			var infowindow = new google.maps.InfoWindow();
			$scope.directionDisplay = [];
			$scope.labels = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];

			for( var i = 0 ; i < arrayLength; i++ ) {
				var dirRend = 'directionsDisplay'+i;
				$scope.dirRend = new google.maps.DirectionsRenderer({suppressMarkers: true,preserveViewport: true});
				$scope.dirRend.setMap($scope.map);

				$scope.text = $scope.labels[i];//Generating google pin text
				 
				$scope.origin  = startAddress;
				$scope.destination = destAddress;
	
				makeRoute( $scope.origin, $scope.destination, $scope.dirRend,$scope.text);
				if ( latValue != '' ) {
					
					var position = {lat: parseFloat(latValue), lng: parseFloat(lngValue)};
					var marker = new google.maps.Marker({
						position: position,
						map: $scope.map,
						icon:"./pages/img/map-truck.png"
					});

					google.maps.event.addListener(marker, 'click', function() {
						infowindow.setContent('<div class="info-container">\
													<p><b>Driver : </b>'+ $rootScope.tableTitle[0]+'</p>\
													<p><b>Truck Name : </b> '+labelValue+ '</p>\
													<p><b>Address : </b> '+cityValue+', '+ stateValue+'</p>\
													</div>');
						infowindow.open($scope.map, this);
					});
				}


				if(jobLogOnMap != null){
					angular.forEach(jobLogOnMap, function (item) {
						var position = {lat: parseFloat(item.latitude), lng: parseFloat(item.longitude)};
						var mapIcon = false;
						
						switch(item.eventType){
							case 'STOP'		: mapIcon = 'vehicle-stop.png';break;
							case 'IGOFF'	: mapIcon = 'engine-off.png';break;
							case 'IGON'		: mapIcon = 'engine-on.png';break;
							//case 'DEVICEIO'	: mapIcon = 'truck-deviceio.png';break;
							//case 'MOVING'	: mapIcon = 'truck-moving.png';break;
						}

						if(mapIcon){
							var logMarker = new google.maps.Marker({
								position: position,
								map: $scope.map,
								icon:"./pages/img/"+mapIcon
							});	
							
						}
						
					});
				}
			}
			
			function makeRoute(origin, destination,renderer,text) {
				
				$scope.directionsService.route({
						origin: origin,
						destination: destination,
						travelMode: 'DRIVING'
					}, function(response, status) {
						if (status === 'OK') {
							renderer.setDirections(response);
							var leg = response.routes['0'].legs['0'];
							var pointers = $scope.getImageMarker(text,'G',true)
  							
							makeMarker( leg.start_location,pointers.green, origin);
							
  							var endPointer = $scope.getImageMarker(text,'R')
							makeMarker( leg.end_location, pointers.red, destination);

						} else {
							console.log('Directions request failed due to ' + status);
						}
					});
			}
		
			function makeMarker( position, icon, title ) {
				new google.maps.Marker({
					position: position,
					icon: icon,
					title: title,
					map: $scope.map,
				});
            }
			return true;
	}

	/**
	 * Deleting the assinged loads permanently
	 * 
	 */ 
	$scope.removeLoadDelete = function( loadId, index ) {
		angular.element("#confirm-delete").modal('show');
		angular.element("#confirm-delete").data("loadId",loadId);
		angular.element("#confirm-delete").data("index",index);
	}


	$scope.confirmDelete = function(confirm){
		if(confirm == 'yes'){
			var loadId = angular.element("#confirm-delete").data("loadId");
			var index  = angular.element("#confirm-delete").data("index");
			if ( loadId != '' && loadId != undefined ) {
				dataFactory.httpRequest('assignedloads/deleteAssignedLoad/'+loadId+'/'+$rootScope.srcPage).then(function(data) {
					PubNub.ngPublish({ channel: $rootScope.notificationChannel, message: {content:"activity", sender_uuid : $rootScope.activeUser } });
					if ( data.success == true ) {
						$rootScope.alertdeletemsg = true;
						$rootScope.Message = $rootScope.languageArray.LoadDeleteSuccMsg;
						$rootScope.assignedLoads.splice(index,1);
						$timeout( function(){ $rootScope.alertdeletemsg = false; }, 3000);
					}				
				});
			}
			$timeout( function(){ $rootScope.alertdeletemsg = false; }, 3000);
			
		}else{
			angular.element("#confirm-delete").removeData("loadId");
			angular.element("#confirm-delete").removeData("index");
		}
		angular.element("#confirm-delete").modal('hide');
	}
	
	$scope.removeLoad = function($event) {
		t = $('#data').DataTable();
		$($event.target).closest('tr.ng-scope').hide();
		var index = $($event.target).closest('tr.ng-scope').index();
        
        t.row(index).remove().draw();
		t.row().draw();
	}
	
	$scope.removeItenaryLoad = function(truckstopId, $event) {
		t = $('#data').DataTable();
		var x = $("#data").find("tr[data-uinfo='"+truckstopId+"']");
		t.row(x).remove().draw();
		t.row().draw();
	}
	
	function checkArrayAlreadyExist( id, pickupdate, deadmilesDist, newArray ) {
		var x;		
		for( x in newArray ) {
			if (newArray[x].ID == id ) {
				return false;
			}
		}
		return true;
	}
	
   	$scope.refreshTimepicker = function($event){
	   	angular.element($event.currentTarget).keypress();
	   	angular.element($event.currentTarget).keyup();
   	}
   	
   	/**
   	 * On Change Driver Iteration load
   	 * 
   	 */
   	  
   	$scope.changeDriverLoads = function() {
		if ( $rootScope.scope != undefined && $rootScope.selScope != undefined ) {
			$scope.autoFetchLoads = true;
		
			dataFactory.httpRequest(URL+'/assignedloads/getChangeDriverLoads/'+$rootScope.selectedVehicle+'/'+$rootScope.setVehicleDriverId+'/'+$rootScope.setVehicleSecondDriverId,'POST',{},{scopeType:$rootScope.scope, scope: $rootScope.selScope, loadSource : $scope.loadSource, startingDate: $scope.dateRangeSelector.startDate, endingDate : $scope.dateRangeSelector.endDate}).then(function(data) {
				$rootScope.assignedLoads = [];
				$rootScope.assignedLoads = data.assigned_loads;
				if(Object.keys($rootScope.assignedLoads).length <= 0){
					$scope.haveRecords = true;
				}else{
					$scope.haveRecords = false;
				}
				$scope.idSortType = ''; $scope.PointOfContactPhoneSortType = ''; $scope.equipment_optionsSortType = ''; $scope.LoadTypeSortType = ''; $scope.PickupDateSortType = ''; $scope.DeliveryDateSortType = ''; $scope.OriginCitySortType = ''; $scope.OriginStateSortType = ''; $scope.DestinationCitySortType = ''; $scope.DestinationStateSortType = ''; $scope.driverNameSortType = ''; $scope.invoiceNoSortType = ''; $scope.PaymentAmountSortType = ''; $scope.MileageSortType = '';$scope.deadmilesSortType = ''; $scope.LengthSortType = ''; $scope.LengthSortType = ''; $scope.WeightSortType = ''; $scope.companyNameSortType = ''; $scope.load_sourceSortType = ''; $scope.JobStatusSortType = '';
				$scope.DeliveryDateSortType = 'DESC';				// initially setting value of delivery date column to desc
				$scope.total = data.total;
				$scope.currentPage = 1;
				if(data.hasOwnProperty('vehicleIdRepeat')){
					$rootScope.vehicleIdRepeat = data.vehicleIdRepeat;
					$rootScope.tableTitle = [];	
					$rootScope.tableTitle.push(data.table_title);
				}
				
				//$rootScope.search_label = driverValue;
				
				$scope.newChangeDriverLoads = false;
				$scope.showRouteOnMap = false;
				$scope.showGantt = false;
				
				$scope.autoFetchLoads = false;
			});	
		}
				
	}
	
	if( $cookies.getObject('_gDateRange') ){
        $scope.dateRangeSelector = $cookies.getObject('_gDateRange');
        if($scope.dateRangeSelector.startDate == null || $scope.dateRangeSelector.endDate == null){
        	$scope.dateRangeSelector = {};
        }
    }else{
        $scope.dateRangeSelector = {};    
        $cookies.putObject('_gDateRange', {startDate:null,endDate:null});    
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
            	if ( $scope.dateRangeSelector.startDate != null && $scope.dateRangeSelector != undefined && Object.keys($scope.dateRangeSelector).length > 0 ) {
	            	$scope.dateRangeSelector.startDate = $scope.dateRangeSelector.startDate.format('YYYY-MM-DD');
	            	$scope.dateRangeSelector.endDate = $scope.dateRangeSelector.endDate.format('YYYY-MM-DD');
	            	$cookies.putObject('_gDateRange', $scope.dateRangeSelector);    
	            }else{
	            	$scope.dateRangeSelector = {};
	            }
                $scope.changeDriverLoads();
            },
            'cancel.daterangepicker': function(ev, picker) {  
                $scope.dateRangeSelector = {};
                $cookies.putObject('_gDateRange', {startDate:null,endDate:null});    
                angular.element('#myLoadsDRPicker').data('daterangepicker').setStartDate(new Date());
                angular.element('#myLoadsDRPicker').data('daterangepicker').setEndDate(new Date());
                $scope.changeDriverLoads();
            }
        },
    };




	/*$scope.selectDateRange = function(date) {
		$scope.changeDriverLoads();
	}*/
	/*************Fetching load Details start**********************/
	
	/**Clicking on load detail changes url withour reload state*/
	$scope.clickMatchLoadDetail = function(truckstopId,loadId, deadmile,calPayment,totalCost,orignPickDate,vehicleID, index) {
		
		$rootScope.alertdeletemsg = false;
		if ( loadId == '' && loadId == undefined ) 
			loadId = '';
			
		$rootScope.globalListingIndex = index;			// set index to update the particular record from list
		encodedUrl = btoa(truckstopId+'-'+loadId+'-'+deadmile+'-'+calPayment+'-'+totalCost+'-'+orignPickDate+'-'+vehicleID);
		$state.go('search.popup', {staticId:2,encodedurl:encodedUrl}, {notify: false,reloadOnSearch: false});
	}	
		
	$scope.hideLoadDetailPopup = function() {
		$rootScope.firstTimeClick = true;
		if ( $rootScope.statesArr[0] != '' && $rootScope.statesArr[0] != undefined ) {
			$state.go($rootScope.statesArr[0], {'type':false}, {notify: false,reload: true});
		} else {
			$state.transitionTo('myLoad', {'type': false}, {notify: false,reload: false});
		}
	}
	
	/*Changing url on outer click of popup*/
	$(document).off('click','#edit-fetched-load').on("click",'#edit-fetched-load', function(event){
		var $trigger1 = $(".popup-container-wid1");
		if($trigger1 !== event.target && !$trigger1.has(event.target).length){
			if ( $rootScope.statesArr[0] != '' && $rootScope.statesArr[0] != undefined ) {
				$state.go($rootScope.statesArr[0], {'type':false}, {notify: false,reload: true});
			} else {
				$state.transitionTo('myLoad', {'type': false}, {notify: false,reload: false});
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
	
		$scope.updateDashboard = function(direction){
			if(direction == "prev"){
				$scope.options.fromDate = moment($scope.options.fromDate).subtract(1,  'days');
				var bufferedDate = $scope.options.fromDate;
				$scope.options.toDate = moment(bufferedDate).add(23.59,'hours');

				var response = Sample.fetchEastimateHOS($scope.HOS,$scope.options.fromDate);
				$scope.data = response.vlog;
	        	$scope.onDuty = parseFloat(response.totals.onDuty).toFixed(2);
	        	$scope.offDuty = parseFloat(response.totals.offDuty).toFixed(2);
	        	$scope.SB = parseFloat(response.totals.SB).toFixed(2);
	        	$scope.driving = parseFloat(response.totals.driving).toFixed(2);

	        	$scope.thours = response.totals.thours;

			}else if(direction = "next"){
				$scope.options.fromDate = moment($scope.options.fromDate).add(1,  'days');
				var bufferedDate = $scope.options.fromDate;
				$scope.options.toDate = moment(bufferedDate).add(23.59,  'hours');
				var response = Sample.fetchEastimateHOS($scope.HOS,$scope.options.fromDate);
				$scope.data = response.vlog;
	        	$scope.onDuty = parseFloat(response.totals.onDuty).toFixed(2);
	        	$scope.offDuty = parseFloat(response.totals.offDuty).toFixed(2);
	        	$scope.SB = parseFloat(response.totals.SB).toFixed(2);
	        	$scope.driving = parseFloat(response.totals.driving).toFixed(2);

	        	$scope.thours = response.totals.thours;

			}
			$scope.displayHOSDate = moment($scope.options.fromDate).format("MMMM Do YYYY");
		}

	//------------------------------------- Gantt Chart Options ----------------------------
	$scope.options = {
            mode: 'custom',
            scale: 'day',
            sortMode: undefined,
            sideMode: 'Table',
            daily: false,
            maxHeight: true,
            width: false,
            zoom: 11,
            columns: ['model.name'],
            treeTableColumns: [],
            columnsHeaders: {'model.name' : ''},
            columnsClasses: {'model.name' : 'gantt-column-name', 'from': 'gantt-column-from', 'to': 'gantt-column-to'},
            //headers:true,
            columnsFormatters: {
                'from': function(from) {
                    return from !== undefined ? from.format('lll') : undefined;
                },
                'to': function(to) {
                    return to !== undefined ? to.format('lll') : undefined;
                }
            },
            treeHeaderContent: '<i class="fa fa-align-justify"></i> {{getHeader()}}',
            columnsHeaderContents: {
                'model.name': '{{getHeader()}}',
                
            },
            viewScale: "5 minutes",
			headersFormats: {
				day: 'D',
				hour: 'H',
				minute: 'mm'
			},
            autoExpand: 'none',
            taskOutOfRange: 'truncate',
            fromDate: moment(null),
            toDate: undefined,
            rowContent: '<i class="fa fa-align-justify"></i> {{row.model.name}}',
            taskContent : '<i class="fa fa-tasks"></i> {{task.model.name}}',
            allowSideResizing: false,
            labelsEnabled: true,
            currentDate: 'none',
            currentDateValue: new Date(),
            draw: false,
            readOnly: true,
            groupDisplayMode: 'group',
            filterTask: '',
            filterRow: '',
           	timeFrames: {
               'day': {
                    start: moment('8:00', 'HH:mm'),
                    end: moment('24:00', 'HH:mm'),
                    working: true,
                    default: true
                },
                'weekend': {
                    working: true
                },
                'holiday': {
                    working: false,
                    color: 'red',
                    classes: ['gantt-timeframe-holiday']
                }
            },
            dateFrames: {
                'weekend': {
                    evaluator: function(date) {
                        return date.isoWeekday() === 6 || date.isoWeekday() === 7;
                    },
                    targets: ['weekend']
                },
                '1-january': {
                    evaluator: function(date) {
                        return (date.month()+1) === 1 && date.date() === 1;
                    },
                    targets: ['holiday']
                },
                '15-April': {
                    evaluator: function(date) {
                        return (date.month()+1) === 4 && date.date() === 15;
                    },
                    targets: ['holiday']
                },
                '5-May': {
                    evaluator: function(date) {
                        return (date.month()+1) === 28 && date.date() === 5;
                    },
                    targets: ['holiday']
                },
                '3-July': {
                    evaluator: function(date) {
                        return (date.month()+1) === 7 && date.date() === 3;
                    },
                    targets: ['holiday']
                },
                '3-September': {
                    evaluator: function(date) {
                        return (date.month()+1) === 9 && date.date() === 3;
                    },
                    targets: ['holiday']
                },
                '22-November': {
                    evaluator: function(date) {
                        return (date.month()+1) === 11 && date.date() === 22;
                    },
                    targets: ['holiday']
                },
                '24-December': {
                    evaluator: function(date) {
                        return (date.month()+1) === 12 && date.date() === 24;
                    },
                    targets: ['holiday']
                },
                '30-December': {
                    evaluator: function(date) {
                        return (date.month()+1) === 12 && date.date() === 30;
                    },
                    targets: ['holiday']
                }
            },
        
        	canDraw: function(event) {
                var isLeftMouseButton = event.button === 0 || event.button === 1;
                return $scope.options.draw && !$scope.options.readOnly && isLeftMouseButton;
            },
            drawTaskFactory: function() {
                return {
                    id: utils.randomUuid(),  // Unique id of the task.
                    name: 'Drawn task', // Name shown on top of each task.
                    color: '#AA8833' // Color of the task in HEX format (Optional).
                };
            },
           
        };

        $scope.renderHOSGantt = function(hos,data) {
        	if (angular.isObject(hos)) {
				var finalDate = hos.startDate;
				if(finalDate.indexOf('/') !== -1){
					var dateArray = finalDate.split('/');
					var year = moment().format("YYYY");
					if(dateArray[dateArray.length -1].length == 2){
						year = year.substr(0,2)+dateArray[dateArray.length -1];	
					}
					finalDate = dateArray[0]+'/'+dateArray[1]+'/'+year;
					hos.startDate = finalDate;
				}

				finalDate = finalDate.replace(/-/g , "/");
				var startDate = new Date(finalDate);
				$scope.options.fromDate = moment(startDate);
	        	$scope.displayHOSDate = moment($scope.options.fromDate).format("MMMM Do YYYY");
	        	var bufferedDate = $scope.options.fromDate;
				$scope.options.toDate = moment(bufferedDate).add(23.59,  'hours');
	        	var response = Sample.fetchEastimateHOS(hos,$scope.options.fromDate,data);
	        	$scope.data = response.vlog;
	        	$scope.onDuty = response.totals.onDuty;
	        	$scope.offDuty = response.totals.offDuty;
	        	$scope.SB = response.totals.SB;
	        	$scope.driving = response.totals.driving;
	        	$scope.thours = response.totals.thours;
			}
        }

        $scope.renderVehicleLogOnGantt = function(log){
        	var date = log.pickDate ;
			if( date.toLowerCase() == 'daily') {
				finalDate = log.previousDate;
			}else {
				finalDate = date;
				if(finalDate.indexOf("/") != -1){
					var dateArray = finalDate.split('/');
					var year = '';
					if(dateArray[2].length == 2){
						 year = '20'+dateArray[2];
					}
					finalDate = dateArray[0]+'/'+dateArray[1]+'/'+year;
				}
			}
        	var startDate = new Date(finalDate);
        	$scope.options.fromDate = moment(startDate);
        	startDate.setDate(startDate.getDate() + 30);
			$scope.options.toDate = moment(startDate);
        	$scope.data = Sample.getVehicleLog(log);
        }

        $scope.handleTaskIconClick = function(taskModel) {
            alert($rootScope.languageArray.iconFrom+taskModel.name+$rootScope.languageArray.taskClicked);
        };

        $scope.handleRowIconClick = function(rowModel) {
            alert($rootScope.languageArray.iconFrom+rowModel.name+$rootScope.languageArray.rowClicked);
        };

        $scope.expandAll = function() {
            $scope.api.tree.expandAll();
        };

        $scope.collapseAll = function() {
            $scope.api.tree.collapseAll();
        };

        $scope.$watch('options.sideMode', function(newValue, oldValue) {
            if (newValue !== oldValue) {
                $scope.api.side.setWidth(undefined);
                $timeout(function() {
                    $scope.api.columns.refresh();
                });
            }
        });

		$scope.headersFormats = { 
		  day: function(column) {
		    return column.date.format('D ddd');
		  },
		  hour: function(column) {
		    return column.date.format('H');
		  }
		};
        $scope.canAutoWidth = function(scale) {
            if (scale.match(/.*?hour.*?/) || scale.match(/.*?minute.*?/)) {
                return false;
            }
            return true;
        };

        $scope.getColumnWidth = function(widthEnabled, scale, zoom) {
            if (!widthEnabled && $scope.canAutoWidth(scale)) {
                return undefined;
            }

            if (scale.match(/.*?week.*?/)) {
                return 150 * zoom;
            }

            if (scale.match(/.*?month.*?/)) {
                return 800 * zoom;
            }

            if (scale.match(/.*?quarter.*?/)) {
                return 500 * zoom;
            }

            if (scale.match(/.*?year.*?/)) {
                return 800 * zoom;
            }
            return 100 * zoom;
        };

        // Reload data action
        $scope.load = function() {
            //$scope.data = Sample.getSampleData();
            dataToRemove = undefined;
            //$scope.timespans = Sample.getSampleTimespans();
        };

        $scope.reload = function() {
            $scope.load();
        };

        // Remove data action
        $scope.remove = function() {
            $scope.api.data.remove(dataToRemove);
        };

        // Clear data action
        $scope.clear = function() {
            $scope.data = [];
        };

        // Visual two way binding.
        $scope.live = {};

        var debounceValue = 1000;

        var listenTaskJson = debounce(function(taskJson) {
            if (taskJson !== undefined) {
                var task = angular.fromJson(taskJson);
                objectModel.cleanTask(task);
                var model = $scope.live.task;
                angular.extend(model, task);
            }
        }, debounceValue);
        $scope.$watch('live.taskJson', listenTaskJson);

        var listenRowJson = debounce(function(rowJson) {
            if (rowJson !== undefined) {
                var row = angular.fromJson(rowJson);
                objectModel.cleanRow(row);
                var tasks = row.tasks;

                delete row.tasks;
                var rowModel = $scope.live.row;

                angular.extend(rowModel, row);

                var newTasks = {};
                var i, l;

                if (tasks !== undefined) {
                    for (i = 0, l = tasks.length; i < l; i++) {
                        objectModel.cleanTask(tasks[i]);
                    }

                    for (i = 0, l = tasks.length; i < l; i++) {
                        newTasks[tasks[i].id] = tasks[i];
                    }

                    if (rowModel.tasks === undefined) {
                        rowModel.tasks = [];
                    }
                    for (i = rowModel.tasks.length - 1; i >= 0; i--) {
                        var existingTask = rowModel.tasks[i];
                        var newTask = newTasks[existingTask.id];
                        if (newTask === undefined) {
                            rowModel.tasks.splice(i, 1);
                        } else {
                            objectModel.cleanTask(newTask);
                            angular.extend(existingTask, newTask);
                            delete newTasks[existingTask.id];
                        }
                    }
                } else {
                    delete rowModel.tasks;
                }

                angular.forEach(newTasks, function(newTask) {
                    rowModel.tasks.push(newTask);
                });
            }
        }, debounceValue);
        $scope.$watch('live.rowJson', listenRowJson);

        $scope.$watchCollection('live.task', function(task) {
            $scope.live.taskJson = angular.toJson(task, true);
            $scope.live.rowJson = angular.toJson($scope.live.row, true);
        });

        $scope.$watchCollection('live.row', function(row) {
            $scope.live.rowJson = angular.toJson(row, true);
            if (row !== undefined && row.tasks !== undefined && row.tasks.indexOf($scope.live.task) < 0) {
                $scope.live.task = row.tasks[0];
            }
        });

        $scope.$watchCollection('live.row.tasks', function() {
            $scope.live.rowJson = angular.toJson($scope.live.row, true);
        });
	//------------------------------------- Gantt Chart Options ----------------------------

	$scope.changeClass = function(){
		if($scope.iClass === false){
			$scope.iClass = true;
		}
		else{
			$scope.iClass = false;
		}
	}
	

	//------------------------ Documents Upload ----------------------------------
		$scope.printDoc = function($event){
			angular.element('#print-me').remove();
			var that = angular.element($event.currentTarget);
			$scope.printUrl =  URL + '/'+ that.data('url');
			var ifrm = document.createElement("iframe");
	        ifrm.setAttribute("src", $scope.printUrl);
	        ifrm.setAttribute("id", 'print-me');
	        ifrm.style.width = "640px";
	        ifrm.style.height = ($(window).height())+'px';
	        angular.element('body').append(ifrm);

	        angular.element('#print-me').attr("src", $scope.printUrl).load(function(){
			    angular.element('#print-me').contentWindow.print();
			    angular.element('#print-me').remove();
			});
		}

	//----------------------------- Documents Upload -----------------------
	
}]);
app.filter('capitalize', function() {
    return function(input) {
      return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
    }
});
app.directive('elastic',['$timeout',function($timeout) {
        return {
            restrict: 'A',
            link: function($scope, element) {
                $scope.initialHeight = $scope.initialHeight || element[0].style.height;
                var resize = function() {
                    element[0].style.height = $scope.initialHeight;
                    element[0].style.height = "" + element[0].scrollHeight + "px";
                };
                element.on("input change", resize);
                $timeout(resize, 0);
            }
        };
    }]);

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
