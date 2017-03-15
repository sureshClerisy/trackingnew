app.controller('iterationLoadsController', ["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "$localStorage", "getIterationLoadData","$compile","$filter","$log","ganttUtils",'GanttObjectModel', 'Sample', 'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window','$sce','$timeout',function(dataFactory,$scope,$http ,$rootScope,$state, $location ,  $cookies, $stateParams, $localStorage, getIterationLoadData , $compile,$filter,$log,utils, ObjectModel, Sample, mouseOffset, debounce, moment,$q,$window,$sce,$timeout){
	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	//----------drop-down ---------------------------
    $rootScope.extraStops = '';
    $scope.duplicatejobstatus='';
    $scope.loadUnloadTime = 4;
	$scope.canDocsShow = false;	
	$scope.HOS = {};

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
	
    $scope.onSelectJobCallback = function (item, model,statusValue){
		angular.element("#change-status").modal('show');
		angular.element("#change-status").data("oldJobStatus",$rootScope.editSavedLoad.JobStatus);
		angular.element("#change-status").data("jobPlaceholder",$rootScope.jobPlaceholder);
		angular.element("#change-status").data("statusValue",item.key);
		$rootScope.editSavedLoad.JobStatus = item.key;
		$rootScope.jobPlaceholder = item.val;
	};
	
	$scope.onSelectExtraStopCallback = function (item, model){
		$rootScope.editSavedLoad.Stops = item.key;
	};
	
	$scope.groupFind = function(item){
        if(item.username !== "")
            return 'Dispatcher: '+item.username;
        else
            return item.username;
    }

	/** Changing driver in dropdown */
	$scope.onSelectVehicleCallback = function ( key,value) {
		$scope.selectedDriver = value.driverName;
		$cookies.remove("_globalDropdown");
        $cookies.putObject('_globalDropdown', key);   
        $scope.driverType = value.label;
	}
	//----------drop-down ---------------------------
	$('#headerFixed').addClass('headerFixed');  
	
	$scope.includeDropzone = false;	
	$scope.callDynamicFn = false;
	$rootScope.Message = '';
	$scope.showGantt = false;
	$scope.mindate = new Date();
	
	$rootScope.fetchnewsearch = false;
	$rootScope.showHeader = true;
	$scope.startOverSpin = false;
	$scope.continueSpin = false;
	$scope.search_label_show = false;
	$scope.loadsData = [];
	$scope.saveLoadsData = [];
	$scope.tableTitle = [];
	$scope.dataErrorMessage = true;
	$scope.loadsIdArray = [];
	if(getIterationLoadData.loadsData != undefined && getIterationLoadData.loadsData !==false){
		$scope.dataErrorMessage = false;
		$scope.unFinishedChain = getIterationLoadData.loadsData.unFinishedChain;
		$scope.loadsData = getIterationLoadData.loadsData.rows;
		$scope.saveLoadsData = getIterationLoadData.loadsData.rows;
		$scope.tableTitle.push(getIterationLoadData.loadsData.table_title);
		
		$rootScope.selectedVehicleId = '';
		$scope.vehicleIdRepeat = getIterationLoadData.loadsData.vehicleIdRepeat; //Vehicle id that is selected in driver dropdown :)

		$scope.labelArray = getIterationLoadData.loadsData.labelArray;
		//$scope.selectedDriver = $scope.labelArray[0].driverName+' - '+$scope.labelArray[0].label;
		$scope.selectedDriver = getIterationLoadData.loadsData.selectedDriver;
		$rootScope.search_label = $scope.vehicleIdRepeat.toString();
		$scope.loadsIdArray = getIterationLoadData.loadsData.loadsIdArray;
		
		$scope.originCitySearch = getIterationLoadData.loadsData.originCitySearch;
		$scope.originStateSearch = getIterationLoadData.loadsData.originStateSearch;
		$scope.originDateSearch = getIterationLoadData.loadsData.originDateSearch;
		$rootScope.deadMilesOriginLocation = getIterationLoadData.loadsData.deadMilesOriginLocation;
	}
	
	$rootScope.saveTypeLoad = 'planLoads';    			// setting the save type for dynamic changing the listing on routes
	
	var gdropDown = $cookies.getObject('_globalDropdown');
	if(gdropDown !== undefined){
		$scope.driverType = (getIterationLoadData.loadsData != undefined ) ? getIterationLoadData.loadsData.driverType : 'driver';	
	}else{
		$scope.driverType = "driver";	
	}
	
	$scope.deletedRowIndex = '';
	$scope.initialOrder = [[ 14, "desc" ]];
	$scope.noRecordFoundMessage = "No loads found";
	$scope.newRowsArray = [];
	$scope.newDriversArray = [];
	$scope.iterationLeftBar = [];
	$scope.iterationDelete = [];
	$scope.firstStartOrigin = '';
	$scope.firstStartPickup = '';
	$scope.previousDate = $filter('date')(new Date(), 'yyyy-MM-dd');
	$scope.multiData = false;
	$scope.totalMiles = 0;
	$scope.totalDeadMiles = 0;
	$scope.totalWorkingHours = 0;
	$scope.totalProfitPercent = 0;
    
	$scope.base_image = new Image();
	$scope.base_image2 = new Image();
	$scope.base_image.src = 'pages/img/green-point.png';
	$scope.base_image2.src = 'pages/img/red-point.png';

	$scope.perPageNumber = 25;
	$scope.showNoRecordFoundMsg = false;  /* Showing no record message with date */
	
	$scope.startOverChain = function(){
		$scope.startOverSpin = true;
		dataFactory.httpRequest(URL+'/iterationloads/destroyLoadsChain/'+$scope.vehicleIdRepeat).then(function(data){
			$scope.startOverSpin = false;
			$scope.perPageNumber = 25;
			$scope.loadsData = data.loadsData.rows;
			$scope.tableTitle = [];
			$scope.tableTitle.push(data.loadsData.table_title);
			$scope.vehicleIdRepeat = data.loadsData.vehicleIdRepeat;
			$rootScope.search_label = $scope.vehicleIdRepeat.toString();
			$scope.unFinishedChain = data.loadsData.chainWithDriver;
			$scope.renderDriverChain();
			$scope.newChangeDriverLoads = false;
			$scope.resetChainCalculations();
			$("#map_canvas").removeAttr('style');
			$(".change-table-height").css('max-height','');
			
			$rootScope.iterationPopData = {};					// empty iterationPopData on destroy of chain for deadmiles proper calculation
			
			$scope.originCitySearch = data.loadsData.originCitySearch;
			$scope.originStateSearch = data.loadsData.originStateSearch;
			$scope.originDateSearch = data.loadsData.originDateSearch;
			$rootScope.deadMilesOriginLocation = data.loadsData.deadMilesOriginLocation;
			$scope.loadsIdArray = data.loadsData.loadsIdArray;
		});
	}
	
	$scope.chainElement = 0;
	$scope.totalMiles = 0;
	var totalMilesSum = 0;
	var totalDeadMilesSum = 0;
	var totalWorkingHours = 0;
	var totalProfitPercent = 0;
	var totalCost = 0;
	var totalInvoicedAmount = 0;
	$scope.HOS.totalDays = 0;
	$scope.getNextDateForIt = 1;
	$scope.getProfitPercent = function(profitAmount1, totalPayment1){
		var profitPercent= ((profitAmount1 / totalPayment1) * 100).toFixed(2);	
		return profitPercent;
	};
	
	$scope.getRepitionData = function( valuesArray, driverName, $event,divIndex,sideChain) {
		if(divIndex != undefined){
			$scope.chainElement = divIndex;
		}else{
			$scope.chainElement++;
		}
		var xchain = sideChain == true ? true : false;
		$scope.showStartOver = true;
		$rootScope.iterationmultistateCheck = 0;
		$rootScope.iterationpopcheck = $rootScope.iterationmultistateCheck;	
		$scope.dailyWorkingHoursLimit = 8;
		$rootScope.iterationPopData = {};
		
		$scope.perPageNumber = 9;
							
    	$($event.target).closest('tr.ng-scope').addClass( 'itenary-added-row' ).attr("data-uinfo", valuesArray['ID']);
    	$($event.target).closest('tr.ng-scope').addClass( 'itenary-added-row' ).attr("data-pickdate", valuesArray['pickDate']);
		dataFactory.httpRequest(URL+'/iterationloads/getIterationLoadNextDate/'+$scope.vehicleIdRepeat,'Post',{} ,{ valueArray : valuesArray , DriverName : driverName , previousDate : $scope.previousDate, divIndex:$scope.chainElement,xchain:xchain, driverType:$scope.driverType}).then(function(data) {
			$rootScope.iterationPopData = data;
			
			if($scope.multiData === true)
			{
				$rootScope.iterationPopData.multiDestinations = $scope.checkArray;
			}
			valuesArray.nextPickupDate1 = $filter('date')(new Date(data.nextPickupDate1), 'MM/dd/yyyy');
			valuesArray.dmEstTime = data.dmEstTime;
			//valuesArray.dailyDriving = data.dailyDriving;
			//valuesArray.totalWorkingDays = data.totalWorkingDays;
			//valuesArray.hoursRemaining = data.hoursRemaining;
			valuesArray.totalDrivingHour= data.totalDrivingHour;
			valuesArray.holidayOccured 	= data.holidayOccured;
			valuesArray.hoursRemainingNextDay = data.hoursRemainingNextDay;
			valuesArray.previousDate 	= $filter('date')(new Date($scope.previousDate), 'MM/dd/yyyy');
			valuesArray.deleted 		= false;
			valuesArray.workingHour 	= data.compWorkingHours;
			valuesArray.skippedWeekdays	= data.skippedWeekdays;			
			if ( $scope.newRowsArray.length == 0 ) {
				$scope.previousDate = data.previousDate;
				$scope.newRowsArray.push(valuesArray);
				//--------------- Hours of Service --------------------------
				$scope.HOS.totalDays  	+=  data.totalWorkingDays;
    			$scope.HOS.startDate 	= data.PickUpDate.toLowerCase() === "daily" ? valuesArray.pickDate :  data.PickUpDate;
    			$scope.HOS.endDate 		= valuesArray.nextPickupDate1;
	    		$scope.HOS.lastDayHours	= data.hoursRemainingNextDay
				//--------------- Hours of Service --------------------------
				totalCost = parseFloat(valuesArray.TotalCost);
				totalInvoicedAmount = parseFloat(valuesArray.Payment);
				var totalProfit = parseFloat(totalInvoicedAmount) - parseFloat(totalCost).toFixed(2);
				$scope.totalMiles = parseInt(valuesArray.Miles);
				$scope.totalDeadMiles = parseInt(valuesArray.deadmiles);
				$scope.totalWorkingHours = $scope.toTime($scope.fromTime($scope.totalWorkingHours)  +  $scope.fromTime(valuesArray.totalDrivingHour) + $scope.fromTime($scope.loadUnloadTime));
				var pftPercent = $scope.getProfitPercent(parseFloat(totalProfit) , parseFloat(totalInvoicedAmount));
				totalProfitPercent = parseFloat(pftPercent);
				$scope.totalProfitPercent = totalProfitPercent;
				
				$scope.iterationLeftBar.push(data);
				$scope.firstStartOrigin = valuesArray.OriginCity+', '+valuesArray.OriginState+', USA';
				$scope.firstStartPickup = data.originFirstDate;
				showGoogleMapRoutes($scope.newRowsArray);

				$scope.renderHOSGantt($scope.HOS, $scope.newRowsArray);
			} else {
				$scope.tableIndex = valuesArray['ID'];
				var res = checkArrayAlreadyExist( valuesArray.ID, valuesArray.PickUpDate, valuesArray.OriginDistance, $scope.newRowsArray);
				if ( res == true ) {
					if ( $scope.chainElement != '' && $scope.chainElement >=0 ) {
						if($scope.newRowsArray.hasOwnProperty($scope.chainElement) &&  $scope.newRowsArray[$scope.chainElement].deleted == true){
							$scope.newRowsArray.splice($scope.chainElement,1,valuesArray);	
						}else{
							$scope.newRowsArray.splice($scope.chainElement,0,valuesArray);
						}
					}else{
						$scope.newRowsArray.push(valuesArray);
					}
					//--------------- Hours of Service --------------------------
					$scope.HOS.totalDays  	+=  data.totalWorkingDays;
	    			$scope.HOS.endDate 		= valuesArray.nextPickupDate1;
		    		$scope.HOS.lastDayHours	= data.hoursRemainingNextDay
					//--------------- Hours of Service --------------------------
					valuesArray.hoursRemainingNextDay = data.hoursRemainingNextDay;
					showGoogleMapRoutes($scope.newRowsArray);
					$scope.previousDate = data.previousDate;
					$scope.renderHOSGantt($scope.HOS, $scope.newRowsArray);
					var totalProfitPercent = 0
					if(valuesArray.TotalCost != undefined ){
						totalCost += parseFloat(valuesArray.TotalCost);
						totalInvoicedAmount += parseFloat(valuesArray.Payment);
						var totalProfit = parseFloat(totalInvoicedAmount) - parseFloat(totalCost).toFixed(2);
						var pftPercent = $scope.getProfitPercent(parseFloat(totalProfit).toFixed(2) , parseFloat(totalInvoicedAmount));
						totalProfitPercent = parseFloat(pftPercent);
					}
					$scope.totalMiles += parseInt(valuesArray.Miles);
					$scope.totalDeadMiles += parseInt(valuesArray.deadmiles);
					$scope.totalWorkingHours = $scope.toTime($scope.fromTime($scope.totalWorkingHours)  +  $scope.fromTime(valuesArray.totalDrivingHour) + $scope.fromTime($scope.loadUnloadTime));
					$scope.totalProfitPercent = totalProfitPercent;
				}
			}
				
			$scope.deletedRowIndex = '';
			$scope.showGantt = true;
			$scope.newDriversArray.push(driverName);
			$scope.showRouteOnMap = true;
            $('#iterationNextLoadModal').modal('show')
		});
	/************* scroll down page to map -r288 *************/
		$timeout(function(){
				$scope.header = angular.element($('#headerFixed')).height();
				$scope.height = angular.element($('.show-map-on-top')).offset().top;
				$('html,body').animate({scrollTop:$scope.height-$scope.header}, 2000);
			},2000);
	/************* scroll down page to map -r288 *************/
	}
	
	$scope.toChar = function(number) {
        return String.fromCharCode(number+65);
     }

	$scope.fromTime = function(time) {
	    if(time > 0){
	        time = parseFloat(time).toFixed(2);
	        var timeArray = time.toString().split('.');
	        var hours = parseInt(timeArray[0]);
	        var minutes = parseInt(timeArray[1]);
	        return (hours * 60) + minutes;    
	    }else
	    return 0;
	    
	}

	$scope.toTime = function(number) {
	    var hours = Math.floor(number / 60);
	    var minutes = number % 60;
	    return hours + "." + (minutes <= 9 ? "0" : "") + minutes;
	}

	
	$scope.focusTo = function( oCity,oState,oCountry, dCity,dState,dCountry) {
		geocoder = new google.maps.Geocoder();
		var bounds = new google.maps.LatLngBounds();
		geocoder.geocode({
	    	'address': oCity+', '+oState+', '+oCountry
	    }, function(results, status) {
	        if (status == google.maps.GeocoderStatus.OK) {
	        	var lat = parseFloat(results[0].geometry.location.lat());
	        	var lng = parseFloat(results[0].geometry.location.lng());
				$scope.map.setCenter({
	                lat : lat,
					lng : lng
	            });
	        }
	    });	
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
	
	function showGoogleMapRoutes(mapRouteArray) {
		var mainHeight = $(window).height();
		var head = 0;
		var ganttHeight = 0;
		var bodyHeight = mainHeight-(ganttHeight+head);
		var dataHeight = (bodyHeight/2)+100;
		var mapHeight = bodyHeight/2;
		$("#map_canvas").height(mapHeight);
		$(".map-distance-detail").css('height',mapHeight);
		arrayLength = mapRouteArray.length;
			var mapOptions = {
				zoom: 4,
				center: new google.maps.LatLng(37.09024,-95.712891),
				scrollwheel: false, 
				scaleControl: false, 
				icon:"./pages/img/truck-stop.png"
			}
			
			$scope.map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
			$scope.directionsService = new google.maps.DirectionsService;
			$scope.deadMilesDirectionsService = new google.maps.DirectionsService;
			
			$scope.directionDisplay = [];
			$scope.labels = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];

			var previous = '';
			for( var i =0 ; i < arrayLength; i++ ) {
				if(mapRouteArray[i]['deleted'] != true){
					var dirRend = 'directionsDisplay'+i;
					$scope.dirRend = new google.maps.DirectionsRenderer({suppressMarkers: true,preserveViewport: true});
					$scope.dirRend.setMap($scope.map);

					//Draw Dead Mile Directions
					$scope.dirRendDeadMiles = new google.maps.DirectionsRenderer({suppressMarkers: true,preserveViewport: true, polylineOptions: {  strokeColor: "red" , geodesic: true, strokeOpacity:0.5, strokeWeight:6}});
					$scope.dirRendDeadMiles.setMap($scope.map);

					$scope.text = $scope.labels[i];//Generating google pin text
					$scope.origin  = mapRouteArray[i]['OriginCity']+', '+mapRouteArray[i]['OriginState']+', USA';
					$scope.destination = mapRouteArray[i]['DestinationCity']+', '+mapRouteArray[i]['DestinationState']+', USA';
					if(previous != ''){
						drawDeadMiles( previous, $scope.origin, $scope.dirRendDeadMiles,'DM');
					}
					makeRoute( $scope.origin, $scope.destination, $scope.dirRend,$scope.text);
					previous = $scope.destination;
					
				}
			}

			function drawDeadMiles(origin, destination,renderer,text) {
				$scope.deadMilesDirectionsService.route({
						origin: origin,
						destination: destination,
						travelMode: 'DRIVING'
					}, function(response, status) {
						if (status === 'OK') {
							renderer.setDirections(response);
							google.maps.event.trigger($scope.map, 'resize');
						} else if (status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {    
				            setTimeout(function() {
				                drawDeadMiles(origin, destination,renderer,text);
				                google.maps.event.trigger($scope.map, 'resize');
				            }, 200);

						}else{
							console.log('Directions request failed due to ' + status);
						}
					});
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
						var ebounds = {lat: parseFloat(leg.end_location.lat()), lng: parseFloat(leg.end_location.lng())};
						makeMarker( leg.end_location, pointers.red, destination);
					}else if (status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {    
			            setTimeout(function() {
			                makeRoute(origin, destination,renderer,text) ;
			            }, 200);

					} else {
						console.log('Directions request failed due to ' + status);
					}
				});
				google.maps.event.trigger($scope.map, 'resize');
			}
		
			function makeMarker( position, icon, title ) {
				marker = new google.maps.Marker({
					position: position,
					icon: icon,
					title: title,
					map: $scope.map,
				});
				google.maps.event.trigger($scope.map, 'resize');
				var center = new google.maps.LatLng(37.09024,-95.712891);
				$scope.map.panTo(center);
            }
				
	   return true;
	}

	/**
	 * Loading dynamic content to table
	 */ 
	function drawDynamicTableContent( loadsArray, actionNo, order ,rowsDelete) {
		if ( rowsDelete == 'deleteRows' ) {
			$scope.loadsData = [];
		} 
		
		$scope.loadsData = loadsArray;
		var k = 0;
		var j = 0;
		return true;
	}
	
	$scope.removeItenaryLoad = function(truckstopId, $event, index) {
		angular.element("#confirm-delete").modal('show');
		angular.element("#confirm-delete").data("item",truckstopId);
		$scope.setDeleteIndex = index;
	}

	$scope.confirmDelete = function(confirm){
		if(confirm == 'yes'){
			var truckstopId =  angular.element("#confirm-delete").data("item");
			var x = $("#data").find("tr[data-uinfo='"+truckstopId+"']");
			$scope.loadsData.splice($scope.setDeleteIndex,1);
		} else {
			angular.element("#confirm-delete").removeData("item");
		}
		angular.element("#confirm-delete").modal('hide');
	}
	
	$scope.removeItenaryLoadSide = function(truckstopId, $event, pickupdate, originDistance,divIndex,valuesArray) {
		angular.element(".confirm-btn-section .common-item, .doc-item").hide();
		angular.element(".confirm-btn-section .chain-item").show();
		angular.element("#confirm-delete").modal('show');
		angular.element("#confirm-delete").data("truckstopId",truckstopId);
		angular.element("#confirm-delete").data("pickupdate",pickupdate);
		angular.element("#confirm-delete").data("divIndex",divIndex);
		angular.element("#confirm-delete").data("originDistance",originDistance);

		$scope.chainDeleteEvent = $event;
		$scope.chainValuesArray = valuesArray;
	}

	$scope.confirmDeleteChainItem = function(confirm) {
		if(confirm == 'yes'){
			var truckstopId = angular.element("#confirm-delete").data("truckstopId");
			var pickupdate = angular.element("#confirm-delete").data("pickupdate");
			var divIndex = angular.element("#confirm-delete").data("divIndex");
			var originDistance = angular.element("#confirm-delete").data("originDistance");
			var valuesArray = $scope.chainValuesArray;

			t = $('#data').DataTable();
			var x = $("#data").find("tr[data-uinfo='"+truckstopId+"']");
			t.row(x).remove().draw();
			t.row().draw();
			angular.element($scope.chainDeleteEvent.currentTarget).parent().parent().addClass('trashme');
			if(divIndex == 0){
				$scope.startOverChain();
			}
			if ( $scope.newRowsArray.length > 0 ) {
				var result = removeItenaryArray( truckstopId, pickupdate, originDistance, $scope.newRowsArray );
				showGoogleMapRoutes($scope.newRowsArray);
				$scope.renderHOSGantt($scope.HOS, $scope.newRowsArray);

				$scope.callDynamicFn = true;
				/*dataFactory.httpRequest(URL+'/iterationloads/removeFromChain/','Post',{} ,{ valueArray : valuesArray , deletedRowIndex : divIndex, ID:truckstopId, driverID: $scope.vehicleIdRepeat}).then(function(data) {
				});
*/			}

			$scope.deletedRowIndex = divIndex;
			if ( $scope.newRowsArray.length == 0 && $scope.callDynamicFn == true) {
				$scope.firstStartOrigin = '';
				$scope.firstStartPickup = '';
				$scope.showRouteOnMap = false;
				$scope.showGantt = false;	
				drawDynamicTableContent( $scope.saveLoadsData, 4, 'orderDesc' , 'deleteRows')
				$scope.callDynamicFn = false;
			}
			angular.element(".common-item").show();
			angular.element(".chain-item").hide();
			angular.element("#confirm-delete").modal('hide');
		}else{
			angular.element("#confirm-delete").removeData("truckstopId");
			angular.element("#confirm-delete").removeData("pickupdate");
			angular.element("#confirm-delete").removeData("divIndex");
			angular.element("#confirm-delete").removeData("originDistance");
			$scope.chainDeleteEvent = '';
			$scope.chainValuesArray = '';

			angular.element("#confirm-delete").modal('hide');
			angular.element(".common-item").show();
			angular.element(".chain-item").hide();
		}
	}
	
	function checkArrayAlreadyExist( id, pickupdate, deadmilesDist, newArray ) {
		var x;
		for( x in newArray ) {
			//if (newArray[x].ID == id && newArray[x].PickUpDate.toLowerCase() == pickupdate.toLowerCase() && newArray[x].OriginDistance == deadmilesDist) {
			if (newArray[x].ID == id && newArray[x].deleted != true) {
				return false;
			}
		}
		return true;
	}
	
	function removeItenaryArray( id,  pickupdate, deadmilesDist, newArray ) {
		var x, loadsInChain = 0;
		$scope.totalMiles = 0, $scope.totalDeadMiles = 0, $scope.totalWorkingHours = 0, $scope.totalProfitPercent = 0, totalCost = 0, totalInvoicedAmount = 0, totalProfitPercent = 0;
		for( x in newArray ) {
			if (newArray[x].ID == id ){//&& newArray[x].PickUpDate == pickupdate && newArray[x].OriginDistance == deadmilesDist) {
				//newArray.splice(x, 1);
				newArray[x].deleted = true;
				//$addscope.chainElement--;
			}

			if(newArray[x].deleted !== true && newArray[x].TotalCost != undefined){
				$scope.totalMiles += parseInt(newArray[x].Miles);
				$scope.totalDeadMiles += parseInt(newArray[x].deadmiles);
				$scope.totalWorkingHours = $scope.toTime($scope.fromTime($scope.totalWorkingHours)  +  $scope.fromTime(newArray[x].totalDrivingHour) + $scope.fromTime($scope.loadUnloadTime));
				totalCost += parseFloat(newArray[x].TotalCost);
				totalInvoicedAmount += parseFloat(newArray[x].Payment);
				var totalProfit = parseFloat( totalInvoicedAmount - parseFloat(totalCost).toFixed(2) );
				var pftPercent = $scope.getProfitPercent(parseFloat(totalProfit).toFixed(2) , parseFloat(totalInvoicedAmount));
				totalProfitPercent = parseFloat(pftPercent);
				$scope.totalProfitPercent = totalProfitPercent;
				loadsInChain++;
			}
		}

		if(loadsInChain == 0){
			$scope.resetChainCalculations();
		}

		return newArray;
	}
	
	$scope.changeDailyHour = function(hoursLimit) {
		if ( hoursLimit != '' ) {
			dataFactory.httpRequest(URL+'/iterationloads/getIterationLoadNextDate/'+$scope.vehicleIdRepeat+'/'+hoursLimit,'Post',{} ,{ valueArray: $rootScope.iterationPopData, previousDate : $scope.previousDate, skipSaveSession:'yes' }).then(function(data) {
				$rootScope.iterationPopData = data;
				$scope.dailyWorkingHoursLimit = hoursLimit;
			});
		}
	}
	
	$scope.fetchNewIterationLoad = function() {
		$scope.newIterationButtonShow = true;
		dataFactory.httpRequest(URL+'/iterationloads/getIterationLoad/'+$scope.vehicleIdRepeat,'Post',{} ,{loadInfo:$rootScope.iterationPopData,driverType:$scope.driverType}).then(function(data) {
				if ( data.rows.length == 0 ) {
					$scope.loadsData = [];
					$scope.perPageNumber = 9;
					$scope.noRecordFoundMessage = "No loads Found for "+data.currentPickupDate;
					$scope.showNoRecordFoundMsg = true;
				}
				else {
					$scope.newRows = [];
					$scope.newRows = data.rows;
					drawDynamicTableContent( $scope.newRows, 4, 'noOrder', 'deleteRows' );
					$scope.showNoRecordFoundMsg = false;
				}
			$scope.newIterationButtonShow = false;
			$('#iterationNextLoadModal').modal('hide');		
			
			$scope.originCitySearch = data.originCitySearch;
			$scope.originStateSearch = data.originStateSearch;
			$scope.originDateSearch = data.originDateSearch;
			$rootScope.deadMilesOriginLocation = data.deadMilesOriginLocation;
			$scope.multiDestinationStateSearch = data.multiDestinationStateSearch;
			
			$scope.tableTitle = [];
			$scope.tableTitle.push(data.table_title);
			$scope.loadsIdArray = data.loadsIdArray;
		});
	}
   	
   	/**
   	 * On Change Driver Iteration load
   	 * 
   	 */
   	  
   	$scope.changeDriverLoads = function(driverValue) {
		if ( driverValue != '' && driverValue != 0 && driverValue != undefined && driverValue != 'all' ) {
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/iterationloads/getChangeDriverChains','POST',{},{driverInfo: driverValue,driverType: $scope.driverType}).then(function(data) {
				$scope.loadsData = [];
				$scope.tableTitle = [];
				$scope.loadsData = data.loadsData.rows;
				$scope.tableTitle.push(data.loadsData.table_title);
				$scope.vehicleIdRepeat = data.loadsData.vehicleIdRepeat;
				$rootScope.search_label = driverValue;
				$scope.unFinishedChain = data.loadsData.chainWithDriver;
				$scope.renderDriverChain();
				
				$scope.originCitySearch = data.loadsData.originCitySearch;
				$scope.originStateSearch = data.loadsData.originStateSearch;
				$scope.originDateSearch = data.loadsData.originDateSearch;
				$rootScope.deadMilesOriginLocation = data.loadsData.deadMilesOriginLocation;
				$scope.loadsIdArray = data.loadsData.loadsIdArray;
				$scope.autoFetchLoads = false;
			});	
		}		
	}

	/**
   	 * Search loads from custom location 
   	 * 
   	 */
   	  
   	$scope.customLocationSearch = function() {
		if ( $scope.vehicleIdRepeat != '' && $scope.vehicleIdRepeat != 0 && $scope.vehicleIdRepeat != undefined && $scope.vehicleIdRepeat != 'all' ) {
			$scope.autoFetchLoads = true;
			dataFactory.httpRequest(URL+'/iterationloads/customLocationSearch','POST',{},{vehicleId: $scope.vehicleIdRepeat, args:$rootScope.askCustom,driverType:$scope.driverType}).then(function(data) {
				angular.element("#askForCustomSearch").modal('hide');
				$scope.loadsData = [];
				$scope.tableTitle = [];
				$scope.loadsData = data.loadsData.rows;
				if($scope.loadsData.length <= 0){
					$scope.showNoRecordFoundMsg = false;
				}
				$scope.tableTitle.push(data.loadsData.table_title);
				$scope.vehicleIdRepeat = data.loadsData.vehicleIdRepeat;
				$scope.originCitySearch = data.loadsData.originCitySearch;
				$scope.originStateSearch = data.loadsData.originStateSearch;
				$scope.originDateSearch = data.loadsData.originDateSearch;
				$rootScope.deadMilesOriginLocation = data.loadsData.deadMilesOriginLocation;
				$scope.loadsIdArray = data.loadsData.loadsIdArray;
				$scope.autoFetchLoads = false;

			});	
		}		
	}
	
	/*************Fetching load Details start**********************/
	
	/**Clicking on load detail changes url withour reload state*/
	$scope.clickMatchLoadDetail = function(truckstopId,loadId, deadmile,calPayment,totalCost,orignPickDate,planPage) {
		if ( loadId == '' && loadId == undefined ) 
			loadId = '';
		encodedUrl = btoa(truckstopId+'-'+loadId+'-'+deadmile+'-'+calPayment+'-'+totalCost+'-'+orignPickDate+'-'+planPage+'-'+$scope.driverType);
		$state.go('search.popup', {staticId:2,encodedurl:encodedUrl}, {notify: false,reloadOnSearch: false});
	}
	
	$scope.hideLoadDetailPopup = function() {
		$rootScope.firstTimeClick = true;
		if ( $rootScope.changedState != '' && $rootScope.changedState != undefined ) {
			$state.go($rootScope.changedState, {type:false}, {notify: false,reload: false});
		} else {
			$state.go('plan', {type:false}, {notify: false,reload: false});
		}		
	}
	
	/*Changing url on outer click of popup*/
	$(document).off('click','#edit-fetched-load').on("click",'#edit-fetched-load', function(event){
		var $trigger1 = $(".popup-container-wid1");
		if($trigger1 !== event.target && !$trigger1.has(event.target).length){
			if ( $rootScope.changedState != '' && $rootScope.changedState != undefined ) {
				$state.go($rootScope.changedState, {type:false}, {notify: false,reload: false});
			} else {
				$state.go('plan', {type:false}, {notify: false,reload: false});
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
		
	$rootScope.iterationpopcheck = 0;
	$rootScope.iterationpopcheck_searchFrom = 0;				// search from popup checkbox
	$rootScope.multistateSearchFromCheck = 0;				// searchFrom popup on plan page showing default textbox for origin search
	$rootScope.iterationcheckmultistate = function(searchParm) {
		if ( searchParm == 'searchFrom' ) {
			if ( $rootScope.iterationpopcheck_searchFrom == 0 ) {				// checking search From page popup checked or not on plan page
				$rootScope.iterationpopcheck_searchFrom = 1;
				$scope.iterationShowMultiState('searchFrom');	
				$rootScope.multistateSearchFromCheck = 1;		// searchFrom popup on plan page showing text area for origin search
			} else {
				$rootScope.iterationpopcheck_searchFrom = 0;
				$rootScope.multistateSearchFromCheck = 0;		// searchFrom popup on plan page showing default textbox for origin search
				$rootScope.askCustom.multiOrigins = '';			// empty multiorigins on searchFrom popup
			}
		} else {
			if($rootScope.iterationmultistateCheck == 0) {
				$rootScope.iterationmultistateCheck = 1;
				$rootScope.iterationpopcheck = $rootScope.iterationmultistateCheck;
			} else {
				$rootScope.iterationmultistateCheck = 0;
				$rootScope.iterationpopcheck = $rootScope.iterationmultistateCheck;	
				$rootScope.iterationPopData.multiDestinations = '';
			}
		}
		
	}
	
	$scope.iterationShowMultiState = function( searchParm ) {
		if ( searchParm == 'searchFrom' ) {								// check if passed argument is search from for the search from popup on plan page
			var popupChecked = $rootScope.iterationpopcheck_searchFrom;
		} else {
			var popupChecked = $rootScope.iterationmultistateCheck;
		}
		
		if (popupChecked) {
			$rootScope.allCountries = [{ 'name' : 'USA','key' : 'USA'},{ 'name' : 'Canada','key' : 'CAN'}];
			$rootScope.sel_country = 'USA';
			var modalElem = $('#multistate');
			
			dataFactory.httpRequest(URL+'/states/fetch_states_areas/'+$rootScope.sel_country).then(function(data) {
				$scope.showMultiStatePopup = true;
				$rootScope.selectDataFor = searchParm;
				$rootScope.areas = data.areas;
				$rootScope.regions = data.regions;
				$rootScope.country = data.country;
				$rootScope.cDisplay = data.country == "CAN" ? "Canada" : data.country ;
				modalElem.children('.modal-dialog').addClass('modal-lg');
				$('#multistate').modal('show');
			});
		}
	}
	
	$scope.multiDataSelected = function(){
		$scope.multiData = true;
	}
	
	$scope.resetChainCalculations = function(){
		$scope.totalMiles = 0;
		$scope.totalDeadMiles = 0;
		$scope.totalWorkingHours = 0;
		$scope.totalProfitPercent = 0;
	}
	
	$scope.updateDashboard = function(direction){
		if(direction == "prev"){
			$scope.options.fromDate = moment($scope.options.fromDate).subtract(1,  'days');
			var bufferedDate = $scope.options.fromDate;
			$scope.options.toDate = moment(bufferedDate).add(23.59,'hours');
			var response = Sample.fetchHoursOfService($scope.HOS,$scope.options.fromDate,$scope.newRowsArray,$scope.driverType);
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
			var response = Sample.fetchHoursOfService($scope.HOS,$scope.options.fromDate,$scope.newRowsArray,$scope.driverType);
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
                'model.name': ' {{getHeader()}}',
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
        
        	timeFramesNonWorkingMode: 'visible',
            columnMagnet: '15 minutes',
            timeFramesMagnet: true,
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

        /*$scope.renderGantt = function(newData) {
        	if (typeof newData !== 'undefined' && newData.length > 0) {
				var date = newData[0].PickUpDate ;
				if( date.toLowerCase() == 'daily') {
					finalDate = newData[0].previousDate;
				}else{
					dateArray = date.split('/');
					year = '20'+dateArray[2];
					finalDate = dateArray[0]+'/'+dateArray[1]+'/'+year;
				}
			    var startDate = new Date(finalDate);
	        	$scope.options.fromDate = moment(startDate);
	        	$scope.displayHOSDate = moment($scope.options.fromDate).format("MMMM Do YYYY");
	        	var bufferedDate = $scope.options.fromDate;
				$scope.options.toDate = moment(bufferedDate).add(23.59,  'hours');
	        	$scope.data = Sample.fetchHoursOfService(newData);
			}
		}*/

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
				var startDate = new Date(finalDate);
				$scope.options.fromDate = moment(startDate);
	        	$scope.displayHOSDate = moment($scope.options.fromDate).format("MMMM Do YYYY");
	        	var bufferedDate = $scope.options.fromDate;
				$scope.options.toDate = moment(bufferedDate).add(23.59,  'hours');
				var response = Sample.fetchHoursOfService(hos,$scope.options.fromDate,data,$scope.driverType);
				
	        	$scope.data = response.vlog;
	        	$scope.onDuty = response.totals.onDuty;
	        	$scope.offDuty = response.totals.offDuty;
	        	$scope.SB = response.totals.SB;
	        	$scope.driving = response.totals.driving;
	        	$scope.thours = response.totals.thours;
			}
        }
		$scope.fromTime = function(time) {
		    if(time > 0){
		        time = parseFloat(time).toFixed(2);
		        var timeArray = time.toString().split('.');
		        var hours = parseInt(timeArray[0]);
		        var minutes = parseInt(timeArray[1]);
		        return (hours * 60) + minutes;    
		    }else
		    return 0;
		}

		$scope.toTime = function(number) {
		    var hours = Math.floor(number / 60);
		    var minutes = number % 60;
		    return hours + "." + (minutes <= 9 ? "0" : "") + minutes;
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

	$scope.changeClass = function() {
		if($scope.iClass === false){
			$scope.iClass = true;
		} else {
			$scope.iClass = false;
		}
	}

	$scope.askForCustomSearch = function(){
		angular.element("#askForCustomSearch").modal('show');
		$rootScope.askCustom = {};
		$rootScope.askCustom.searchFrom = "";
		$rootScope.iterationpopcheck_searchFrom = 0;
		$rootScope.multistateSearchFromCheck = 0;
	}
	
	$scope.askCustomCitiesSuggestions = function(q,from) {
		if ( q != undefined && q.length > 1 ) {
			dataFactory.httpRequest(URL+'/truckstop/searchCity','POST',{},{city: q}).then(function(data){
				if ( data.result.length > 0 ) {
					$scope.haveAskCities = true;
					$rootScope.askSuggestedCities = data.result;
				}else{
					$scope.haveAskCities = false;
				} 					
			});
		} else {
			$scope.haveAskCities = false;
		}
	}
	
	$scope.selectAskSuggestedCity = function(city, state_code, country,from){
		if(from == "fromChain"){
			$rootScope.iterationPopData.singleDestination = city + "," + state_code + "," + country;
		}else{
			$rootScope.askCustom.searchFrom = city + "," + state_code + "," + country;	
		}
		
		$scope.haveAskCities = false;
	}	
	$scope.hideAskCustomSuggestionList = function(){
		$scope.haveAskCities = false;
	}

	$scope.toggleRow = function($event,index){
		angular.element("#hblock"+index).slideToggle();
		angular.element($event.target).toggleClass("minus-1");
	}

	$scope.renderDriverChain = function() {
		$scope.newRowsArray=[];
		$scope.firstStartOrigin = '';
		$scope.firstStartPickup = '';
		$scope.showRouteOnMap = false;
		$scope.showGantt = false;
		$scope.totalMiles = 0;
		$scope.totalDeadMiles = 0;
		$scope.totalWorkingHours = 0;
		$scope.totalProfitPercent = 0;
		$scope.HOS.totalDays = 0;
		if ($scope.unFinishedChain.length > 0  ) {
			$scope.showStartOver = true;
			var totalMilesSum = totalDeadMilesSum =  totalWorkingHours =  totalProfitPercent = 0;
			var chain = [];
			$scope.chainElement = $scope.unFinishedChain[0].length;
	    	angular.forEach($scope.unFinishedChain[0], function(value, key) {

	    		idata = value.encodedJobRecord;
	    		varr = value.valuesArray;
    			value.valuesArray.ID				=	idata.ID;
				value.valuesArray.OriginCity		=	idata.OriginCity;
				value.valuesArray.OriginState		=	idata.OriginState;
				value.valuesArray.DestinationCity	=	idata.DestinationCity;
				value.valuesArray.DestinationState	=	idata.DestinationState;
				value.valuesArray.PickUpDate		=	idata.PickupDate.toLowerCase() === "daily" ? varr.pickDate :  idata.PickupDate;
				value.valuesArray.previousDate		=	varr.previousDate;
				value.valuesArray.driverName		=	varr.driver_name;
				value.valuesArray.nextPickupDate1	=	varr.nextPickupDate1;
				value.valuesArray.miles				=	varr.Miles;
				value.valuesArray.dmEstTime			=	varr.dmEstTime;
				value.valuesArray.deleted			=	value.deleted;
				value.valuesArray.hoursRemainingNextDay		=	varr.hoursRemainingNextDay;
				value.valuesArray.skippedWeekdays		=	varr.skippedWeekdays;
				if(!value.valuesArray.deleted){
					$scope.previousDate = $filter('date')(new Date(varr.previousDate), 'MM/dd/yyyy');
					totalMilesSum += parseInt(varr.Miles);
					totalDeadMilesSum += parseInt(varr.deadmiles);
					totalWorkingHours = $scope.toTime($scope.fromTime(totalWorkingHours)  +  $scope.fromTime(varr.totalDrivingHour) + $scope.fromTime($scope.loadUnloadTime));
				}
				
				chain.push(value.valuesArray);

				

				//-------------------- Hours of Service ------------------------
				$scope.HOS.totalDays  		+=  varr.totalWorkingDays;
				if(key == 0){
	    			$scope.HOS.startDate 	= value.valuesArray.PickUpDate;
	    		}
    			$scope.HOS.endDate 		= value.valuesArray.nextPickupDate1;
    			$scope.HOS.lastDayHours	= varr.hoursRemainingNextDay
				//-------------------- Hours of Service ------------------------

				if(varr.TotalCost != undefined  && varr.deleted != true){
					totalCost += parseFloat(varr.TotalCost);
					totalInvoicedAmount += parseFloat(varr.Payment);
					var totalProfit = parseFloat( totalInvoicedAmount - parseFloat(totalCost).toFixed(2) );
					var pftPercent =$scope.getProfitPercent(parseFloat(totalProfit).toFixed(2) , parseFloat(totalInvoicedAmount));
					totalProfitPercent = parseFloat(pftPercent);
				}



			});
			angular.element(document).ready(function () {
			    $scope.newRowsArray=chain;
				$scope.renderHOSGantt($scope.HOS, $scope.newRowsArray);
				$scope.showGantt = true;
				$scope.showRouteOnMap = true;
				$scope.totalMiles = totalMilesSum;
				$scope.totalDeadMiles = totalDeadMilesSum;
				$scope.totalWorkingHours = totalWorkingHours;
				$scope.totalProfitPercent = totalProfitPercent;
				setTimeout(function(){
		          if (typeof google === 'object' && typeof google.maps === 'object'){
		            showGoogleMapRoutes($scope.newRowsArray);
		          }
		        }, 3500);
			});
		}else{
			$scope.showStartOver = false;
		}
	}
	
	if(getIterationLoadData.loadsData !==false) {
		if ($scope.unFinishedChain != undefined && $scope.unFinishedChain.length > 0  ) {
			$scope.perPageNumber = 9;
			$scope.renderDriverChain();
		} else {
			$scope.perPageNumber = 25;
		}
	}
	
	/**
	*  Request after every 30 seconds
	 */ 
	 
	$rootScope.fetchPlanLoadsAfterEvery = function() {
		$scope.formSearch = {};
		$scope.formSearch.origin_City = $scope.originCitySearch;
		$scope.formSearch.origin_State = $scope.originStateSearch;
		$scope.formSearch.multiDestinations = $scope.multiDestinationStateSearch ;
		$scope.formSearch.pickup_date = $scope.originDateSearch;
		
		var searchStatus = 'planSearch';
		dataFactory.httpRequest('truckstop/get_load_data_repeat','POST',{},{loadsArray: $scope.loadsIdArray, vehicleIDRepeat : $scope.vehicleIdRepeat, formPost: $scope.formSearch, searchStatus : searchStatus}).then(function(data){
			$scope.loadsIdArray = data.loadsIdArray;
			$scope.newRows = data.rows;
			
			drawDynamicTableContent( $scope.newRows, 4, 'noOrder', 'nodeletePlan' );
		});
	}
}]);

 

