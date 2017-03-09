app.controller('autoLoadsController', ["dataFactory","$scope","$http","$rootScope", "$state","$location","$cookies","$stateParams", "$localStorage", "getIterationLoadData","$compile","$filter","$log","ganttUtils",'GanttObjectModel', 'Sample', 'ganttMouseOffset', 'ganttDebounce', 'moment','$q','$window',function(dataFactory,$scope,$http ,$rootScope ,$state, $location ,  $cookies, $stateParams, $localStorage, getIterationLoadData , $compile,$filter,$log,utils, ObjectModel, Sample, mouseOffset, debounce, moment,$q,$window){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');

	$('#headerFixed').addClass('headerFixed');  
	$scope.editLoads = true;
	$scope.matchingTrucks = false;
	$scope.showMaps = false;
	$scope.showhighlighted = 'loadDetail';
	$scope.iClass = false;
	$scope.save_cancel_div = false;
	$scope.save_edit_div = true;
	$scope.showFormClass = true;
	$scope.showPlusMinus = true;
	
	$scope.callDynamicFn = false;
	$scope.Message = '';
	
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
	$scope.loadsData = [];
	$scope.saveLoadsData = [];
	$scope.saveForNoLoadsData = [];
	$scope.tableTitle = [];

	$scope.loadsData = getIterationLoadData.loadsData.rows;
	$scope.saveLoadsData = getIterationLoadData.loadsData.rows;
	$scope.saveForNoLoadsData = getIterationLoadData.loadsData.rows;
	$scope.tableTitle.push(getIterationLoadData.loadsData.table_title);
		
	$scope.vehicleIdRepeat = getIterationLoadData.loadsData.vehicleIdRepeat;
	$scope.labelArray = getIterationLoadData.loadsData.labelArray;
	$scope.search_label = $scope.vehicleIdRepeat.toString();
	
	$scope.deletedRowIndex = '';
	$scope.initialOrder = [[ 19, "desc" ]];
	$scope.noRecordFoundMessage = "No data available";
	$scope.dtOptions = {
			"scrollCollapse": false,
			"fixedHeader": {
				"header": false,
				"headerOffset": $(".header").height()
			},
			"language": {
				"lengthMenu": "_MENU_ ",
				"zeroRecords": $scope.noRecordFoundMessage,
				"info": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries",
				"infoEmpty": "No records available",
				"infoFiltered": "(filtered from _MAX_ total records)"
			},
			"aaSorting": [],
			"order": $scope.initialOrder,
			"iDisplayLength": 10,
			"rowReorder": {
				"selector": 'td:nth-child(2)'
			},
			
			"bLengthChange":false,
			"responsive": true,
			"columnDefs": [ {
				"targets": 21,
				"orderable": false,
				"responsivePriority":1
			} ]
		};

	$scope.newRowsArray = [];
	$scope.newDriversArray = [];
	$scope.iterationLeftBar = [];
	$scope.iterationDelete = [];
	$scope.firstStartOrigin = '';
	$scope.firstStartPickup = '';
	$scope.showFirstLi = false;
	$scope.previousDate = $filter('date')(new Date(), 'yyyy-MM-dd');
	$scope.multiData = false;
	
	$scope.chainElement = 0;
	$scope.totalMiles = 0;
	var totalMilesSum = 0;
	var totalDeadMilesSum = 0;
	var totalWorkingHours = 0;
	var totalProfitPercent = 0;
	var totalCost = 0;
	var totalPayment = 0;
	
	$scope.getNextDateForIt = 1;
	
	$scope.autoFetchLoads = false;

	$scope.getRepitionData = function( valuesArray, driverName, firstRequest) {
		$rootScope.iterationmultistateCheck = 0;
		$rootScope.iterationpopcheck = $rootScope.iterationmultistateCheck;	
		$scope.dailyWorkingHoursLimit = 8;
		$scope.iterationPopData = {};
		
		if ( firstRequest == 'firstRequest' || firstRequest == 'secondRequest' || firstRequest == 'delRequest' ) {
			$scope.autoFetchLoads = true;
		}
		
		dataFactory.httpRequest(URL+'/iterationloads/getIterationLoadNextDate/'+$scope.vehicleIdRepeat,'Post',{} ,{ valueArray : valuesArray , DriverName : driverName , previousDate : $scope.previousDate, skipSaveSession:'yes'}).then(function(data) {
			$scope.iterationPopData = data;
		
			if($scope.multiData === true)
			{
				$scope.iterationPopData.multiDestinations = $scope.checkArray;
			}
			valuesArray.nextPickupDate1 = $filter('date')(new Date(data.nextPickupDate1), 'MM/dd/yyyy');
			valuesArray.driverName = data.driver_name;
			valuesArray.previousDate = $filter('date')(new Date($scope.previousDate), 'MM/dd/yyyy');
			
			valuesArray.workingHour = data.compWorkingHours;
			
			if ( $scope.newRowsArray.length == 0 ) {
				$scope.previousDate = data.previousDate;
				$scope.newRowsArray.push(valuesArray);
				totalMilesSum += parseInt(valuesArray.Miles);
				totalDeadMilesSum += parseInt(valuesArray.deadmiles);
				totalWorkingHours += parseInt(valuesArray.workingHour);
				
				if(valuesArray.TotalCost != undefined ){
					totalCost += parseFloat(valuesArray.TotalCost);
					totalPayment += parseFloat(valuesArray.Payment);
					var totalProfit = parseFloat( totalPayment - totalCost );
					var pftPercent = ((parseFloat(totalProfit) / parseFloat(totalCost)) * 100).toFixed(2);
					totalProfitPercent = parseFloat(pftPercent);
				}			
				$scope.iterationLeftBar.push(data);
							
				$scope.firstStartOrigin = valuesArray.OriginCity+', '+valuesArray.OriginState+', USA';
				$scope.firstStartPickup = data.originFirstDate;
			
				$scope.showFirstLi = true;
				//~ showGoogleMapRoutes($scope.newRowsArray);

				//~ $scope.renderGantt($scope.newRowsArray);
			} else {
				$scope.tableIndex = valuesArray['ID'];
				var res = checkArrayAlreadyExist( valuesArray.ID, valuesArray.PickUpDate, valuesArray.OriginDistance, $scope.newRowsArray);
				if ( res == true ) {
					if ( $scope.deletedRowIndex != '' ) {
						$scope.newRowsArray.splice($scope.chainElement,0,valuesArray);
					} else {
						$scope.newRowsArray.push(valuesArray);
					}
					
					showGoogleMapRoutes($scope.newRowsArray);
					$scope.previousDate = data.previousDate;
					$scope.renderGantt($scope.newRowsArray);
					
					totalMilesSum += parseInt(valuesArray.Miles);
					totalDeadMilesSum += parseInt(valuesArray.deadmiles);
					totalWorkingHours += parseInt(valuesArray.workingHour);
					if(valuesArray.TotalCost != undefined ){
						totalCost += parseFloat(valuesArray.TotalCost);
						totalPayment += parseFloat(valuesArray.Payment);
						var totalProfit = parseFloat( totalPayment -totalCost );
						var pftPercent = ((parseFloat(totalProfit) / parseFloat(totalCost)) * 100).toFixed(2);
						totalProfitPercent = parseFloat(pftPercent);
					}
				}
			}
			
			$scope.deletedRowIndex = '';
			$scope.totalMiles = totalMilesSum;
			$scope.totalDeadMiles = totalDeadMilesSum;
			$scope.totalWorkingHours = totalWorkingHours;
			$scope.totalProfitPercent = totalProfitPercent;
						
			$scope.showGantt = true;
			$scope.newDriversArray.push(driverName);
			$scope.showRouteOnMap = true;
			
			if ( firstRequest == 'firstRequest' || firstRequest == 'secondRequest' ) {
				angular.element(document).ready(function () {
					if ( firstRequest == 'firstRequest' ) {
						$scope.firstLoadDate = $scope.iterationPopData.originFirstDate;
					}
					
					$scope.nextLoadDate = $scope.iterationPopData.nextPickupDate1;
					
					$scope.fetchNewIterationLoad();
				});
			} else if ( firstRequest == 'delRequest' ) {
				$scope.nextLoadDate = $scope.iterationPopData.nextPickupDate1;
				$scope.fetchNewIterationLoad('delRequest');
			}
			else {
				$('#iterationNextLoadModal').modal('show');
			}
		});
	}
	
	/**
	 * calling load automatically for first record
	 */ 
		
		$scope.saveIndexFirstSearched = 0;
	$scope.saveIndexNextSearched = 0;
	//~ $(window).load(function () {
		//~ if ( $scope.saveLoadsData.length > 1 ) {
				//~ $scope.getRepitionData($scope.loadsData[$scope.saveIndexFirstSearched],$scope.tableTitle[0],'firstRequest');
			//~ }
	//~ });
	

	angular.element(document).ready(function () {
		setTimeout(function(){
			if ( $scope.saveLoadsData.length > 1 ) {
				$scope.getRepitionData($scope.loadsData[$scope.saveIndexFirstSearched],$scope.tableTitle[0],'firstRequest');
			}
		}, 2000);
	});
	
	$scope.callStateReloadSecondTime = function() {
		//~ $state.reload();
		var secondIndex = parseInt($scope.saveIndexFirstSearched + 1);
		angular.element(document).ready(function () {
			setTimeout(function(){
				$scope.getRepitionData($scope.saveLoadsData[secondIndex],$scope.tableTitle[0],'firstRequest');
			}, 500);
		});
		$scope.saveIndexFirstSearched = secondIndex;
	}
	
	function showGoogleMapRoutes(mapRouteArray) {
		var elementOffset = $('#data').height(),
        dh = $(window).height(),
        mh = dh - elementOffset ;
        if(mh >= 450 )
        	$("#map_canvas").height(mh);
       	else
       		$("#map_canvas").height(dh-300);
        
		arrayLength = mapRouteArray.length;
			
			var mapOptions = {
				zoom: 4,
				center: {lat: 37.09024, lng: -95.712891},
				scrollwheel: false, 
				scaleControl: false, 
				icon:"./pages/img/truck-stop.png"
			}
			
			$scope.map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
			$scope.directionsService = new google.maps.DirectionsService;
			
			$scope.directionDisplay = [];
			$scope.labels = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];

			for( var i =0 ; i < arrayLength; i++ ) {

				var dirRend = 'directionsDisplay'+i;
				$scope.dirRend = new google.maps.DirectionsRenderer({suppressMarkers: true,preserveViewport: true});
				$scope.dirRend.setMap($scope.map);

				$scope.text = $scope.labels[i];//Generating google pin text
				 

				$scope.origin  = mapRouteArray[i]['OriginCity']+', '+mapRouteArray[i]['OriginState']+', USA';
				$scope.destination = mapRouteArray[i]['DestinationCity']+', '+mapRouteArray[i]['DestinationState']+', USA';
	
				//~ makeRoute( $scope.origin, $scope.destination, $scope.dirRend);
				makeRoute( $scope.origin, $scope.destination, $scope.dirRend,$scope.text);

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
							makeMarker( leg.start_location, 'http://www.googlemapsmarkers.com/v1/'+text+'/224C16/FFFFFF/224C16', origin);
  							makeMarker( leg.end_location, 'http://www.googlemapsmarkers.com/v1/'+text+'/A04646/FFFFFF/A04646', destination);

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

	
	function drawDynamicTableContent( loadsArray, actionNo, order ,rowsDelete) {
			table = $('#data').DataTable();
			
			if ( rowsDelete == 'deleteRows' ) {
				table.rows().every( function () {
					table.row().remove().draw();
				});
			} 
			
			$scope.initialOrder = [[ 19, "desc" ]];
			
		var k = 0;
		var j = 0;
			angular.forEach(loadsArray, function(value, key) {
				$scope.newDriver = $scope.newDriversArray[0];
				var deadmiles = value['deadmiles'];
				if(!angular.isNumber(parseFloat(deadmiles))){
					deadmiles = 0;
				}
				
					$scope.noLoad = '';
				if ( actionNo == 4 ) {
					var newVal = 'newValues'+k;
					$scope[newVal] = value;
								
					actionsValue = '<a title="Load Detail" class="icon-action search-il" ng-click="editSaveLoad('+value['ID']+',noLoad,'+deadmiles+','+value['Payment']+')" ><i class="fa fa-search"></i></a> <a title="Special Information" class="icon-action edit-il" ng-click="fetchSpecialNote('+value['ID']+')" ><i class="fa fa-info"></i></a> <a title="Not Interested" class="icon-action del-il" ng-click="removeItenaryLoad('+value['ID']+',$event)" ><i class="fa fa-trash"></i></a> <a title="Add To Iternary" class="icon-action iteration" ng-click="getRepitionData(newValues'+k+',newDriver, $event)" ><img src="./pages/img/map-p.png" ></a>';
				} else {
					var newVal = 'newValue'+j;
					$scope[newVal] = value;
				
					actionsValue = '<a title="Special Information" class="icon-action edit-il" ng-click="fetchSpecialNote('+value['ID']+')" ><i class="fa fa-info"></i></a><a title="Load Detail" class="icon-action search-il" ng-click="editSaveLoad('+value['ID']+',noLoad,'+deadmiles+','+value['Payment']+')" ><i class="fa fa-search"></i></a>  <a title="Add To Iternary" class="icon-action iteration" ng-click="getRepitionData(newValue'+j+',newDriver, $event)" ><img src="./pages/img/map-p.png" ></a>';
				}
				
				    newRow = table.row.add( [
					value['Bond'],
					value['PointOfContactPhone'],
					value['Equipment'],
					value['Age'],
					value['LoadType'],
					value['PickUpDate'],
					value['OriginCity'],
					value['OriginState'],
					value['OriginDistance'],											
					value['DestinationCity'],
					value['DestinationState'],
					//value['DestinationDistance'],
					"$"+parseFloat(value['Payment']).toFixed(2).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,"),	
					"$"+parseFloat(value['RPM']).toFixed(2).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,"),	
					value['Miles'],
					value['Length'],
					value['Weight'],
					//value['ExperienceFactor'],
					value['CompanyName'],
					value['FuelCost'],
					 value['deadmiles'],
			        "$"+parseFloat(value['profitAmount']).toFixed(2).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,"),
			        value['percent'],
			       
			        actionsValue,
				] );
			   		
			   	if ( actionNo == 3 ) {
					table.row(newRow).nodes().to$().addClass("itenary-added-row");
				}
				
				table.row(newRow).nodes().to$().attr("data-uinfo", value['ID']);

				
				//table.row(newRow).column(11).nodes().to$().addClass('text-center');
				
				if ( parseInt(value.highlight) == 1 ) {
					table.row(newRow).nodes().to$().addClass("estimate-payment");
				}
				table.row(newRow).column(7).nodes().to$().addClass('state-capital');
				table.row(newRow).column(10).nodes().to$().addClass('state-capital');
				table.row(newRow).column(21).nodes().to$().addClass('text-center');
				var compile = $compile((newRow).nodes().to$())($scope);
			    table.row(compile).draw();
			    k++;
			    j++;
			});
			
			return true;
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
	
	$scope.removeItenaryLoadSide = function(truckstopId, $event, pickupdate, originDistance,divIndex,valuesArray) {
		t = $('#data').DataTable();
		
		var x = $("#data").find("tr[data-uinfo='"+truckstopId+"']");
		t.row(x).remove().draw();
		t.row().draw();
		
		var newdivIndex = parseInt(divIndex - 1);
	
		if ( $scope.newRowsArray.length > 0 ) {
			var result = removeItenaryArray( truckstopId, pickupdate, originDistance, $scope.newRowsArray );
			$scope.callDynamicFn = true;
			
			if ( divIndex != 0 ) {
				$scope.againRequestArray = $scope.newRowsArray[newdivIndex];
				var arrayLength = parseInt($scope.newRowsArray.length);
				var k = 0;
				for ( var i = parseInt(divIndex); i < arrayLength; i++ ) {
					var n = i;
					n = parseInt(n - k);
					var result = removeItenaryArray( $scope.newRowsArray[n].ID, $scope.newRowsArray[n].PickUpDate, $scope.newRowsArray[n].OriginDistance, $scope.newRowsArray );
					k++;
				}
				$scope.getRepitionData($scope.againRequestArray,$scope.tableTitle[0],'delRequest');
			} else if ( divIndex == 0 ) {
				$scope.newRowsArray = [];
				$scope.callStateReloadSecondTime();
				$scope.callDynamicFn = false;
			}
			
			showGoogleMapRoutes($scope.newRowsArray);
			$scope.renderGantt($scope.newRowsArray);
			
			
		}

		$scope.deletedRowIndex = divIndex;
		if ( $scope.newRowsArray.length == 0 && $scope.callDynamicFn == true) {
			$scope.firstStartOrigin = '';
			$scope.firstStartPickup = '';
			$scope.showFirstLi = false;
			$scope.showRouteOnMap = false;
			$scope.showGantt = false;	
			drawDynamicTableContent( $scope.saveLoadsData, 4, 'orderDesc' , 'deleteRows')
			$scope.callDynamicFn = false;
		}
	}
	
	function checkArrayAlreadyExist( id, pickupdate, deadmilesDist, newArray ) {
		var x;
		for( x in newArray ) {
			if (newArray[x].ID == id && newArray[x].PickUpDate.toLowerCase() == pickupdate.toLowerCase() && newArray[x].OriginDistance == deadmilesDist) {
				return false;
			}
		}
		return true;
	}
	
	function removeItenaryArray( id,  pickupdate, deadmilesDist, newArray ) {
		var x;
		for( x in newArray ) {
			if (newArray[x].ID == id && newArray[x].PickUpDate == pickupdate && newArray[x].OriginDistance == deadmilesDist) {
				totalMilesSum -= parseInt(newArray[x].Miles);
				totalDeadMilesSum -= parseInt(newArray[x].deadmiles);
				totalWorkingHours -= parseInt(newArray[x].workingHour);
					
				totalCost -= parseFloat(newArray[x].TotalCost);
				totalPayment -= parseFloat(newArray[x].Payment);
				var totalProfit = parseFloat( totalPayment - totalCost );
				var pftPercent = ((parseFloat(totalProfit) / parseFloat(totalCost)) * 100).toFixed(2);
			
				totalProfitPercent = parseFloat(pftPercent);
				
				$scope.totalMiles = totalMilesSum;
				$scope.totalDeadMiles = totalDeadMilesSum;
				$scope.totalWorkingHours = totalWorkingHours;
				$scope.totalProfitPercent = totalProfitPercent;
				newArray.splice(x, 1);
				$scope.chainElement--;
			}
		}
		return newArray;
	}
	
	$scope.changeDailyHour = function(hoursLimit) {
		if ( hoursLimit != '' ) {
			dataFactory.httpRequest(URL+'/iterationloads/getIterationLoadNextDate/'+$scope.vehicleIdRepeat+'/'+hoursLimit,'Post',{} ,{ valueArray: $scope.iterationPopData, previousDate : $scope.previousDate, skipSaveSession:'yes' }).then(function(data) {
				$scope.iterationPopData = data;
			
				$scope.dailyWorkingHoursLimit = hoursLimit;
			});
		}
	}
	
	$scope.fetchNewIterationLoad = function(firstRequest) {
		$scope.newIterationButtonShow = true;
		
		dataFactory.httpRequest(URL+'/autoloads/getIterationLoad/'+$scope.vehicleIdRepeat,'Post',{} ,$scope.iterationPopData).then(function(data) {
			
				if ( data.rows.length == 0 ) {
						table = $('#data').DataTable();
						table.rows().every( function () {
							table.row().remove().draw();
						});
					$scope.noRecordFoundMessage = "No Record Found for "+data.currentPickupDate;
					var newNextIndex = parseInt($scope.saveIndexNextSearched + 1);
					
					$scope.removeRowIndex = $scope.newRowsArray.length;
											
					if ( $scope.saveIndexNextSearched < 3 ) {
						setTimeout(function(){
							$scope.getRepitionData($scope.saveForNoLoadsData[newNextIndex],$scope.tableTitle[0],'secondRequest');
						},1000);
						$scope.saveIndexNextSearched = newNextIndex;
						
						//~ if ( $scope.removeRowIndex > 1 )
						$scope.newRowsArray.splice(parseInt($scope.removeRowIndex - 1), 1);  
					} else {
						$scope.autoFetchLoads = false;
					}
				} else {
					$scope.newRows = [];
					$scope.newRows = data.rows;
					$scope.saveForNoLoadsData = data.rows;
					drawDynamicTableContent( $scope.newRows, 4, 'noOrder', 'deleteRows' );
					
					$scope.saveIndexNextSearched = 0;
					var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
					var firstDate = new Date($scope.firstLoadDate);
					var secondDate = new Date($scope.nextLoadDate);

					var diffDays = Math.round(Math.abs((firstDate.getTime() - secondDate.getTime())/(oneDay)));
					
					if ( firstRequest != 'delRequest' ) {
						if ( diffDays < 21 ) {
							if ( $scope.newRows.length > 0 ) {
								setTimeout(function(){
									$scope.getRepitionData($scope.newRows[$scope.saveIndexNextSearched],$scope.tableTitle[0],'secondRequest');
								},1000);
							}
						}
					}
					$scope.autoFetchLoads = false;
					console.log($scope.firstLoadDate);
					console.log($scope.nextLoadDate);
					console.log(diffDays);
				}
			$scope.newIterationButtonShow = false;
			$('#iterationNextLoadModal').modal('hide');		
		});
			
	}
	
	$scope.refreshDatepicker = function($event){
	   	angular.element($event.currentTarget).keypress();
	   	angular.element($event.currentTarget).keyup();
   	}
   	
   	/**
   	 * On Change Driver Iteration load
   	 * 
   	 */
   	  
   	$scope.changeDriverLoads = function(driverValue) {
		if ( driverValue != '' && driverValue != 0 && driverValue != undefined && driverValue != 'all' ) {
			$scope.newChangeDriverLoads = true;
			dataFactory.httpRequest('iterationloads/getChangeDriverChains','POST',{},{driverInfo: driverValue}).then(function(data) {
				$scope.loadsData = [];
				$scope.tableTitle = [];
				$scope.loadsData = data.loadsData.rows;
				$scope.tableTitle.push(data.loadsData.table_title);
				$scope.vehicleIdRepeat = data.loadsData.vehicleIdRepeat;
				$scope.search_label = driverValue;
				
				$scope.newChangeDriverLoads = false;
			});	
		}		
	}
	
	/*************Fetching load Details start**********************/
	
	$scope.firstTimeClick = true;
	$scope.showPaymentCal = false;
	
	$scope.editSaveLoad = function(truckstopId,loadId,deadmile,calPayment) {
	
		$scope.loadmsg = false;
		$scope.editLoads = true;
		$scope.matchingTrucks = false;
		$scope.showMaps = false;
		$scope.showhighlighted = 'loadDetail';
		
		$scope.save_cancel_div = false;
		$scope.save_edit_div = true;
		$scope.showFormClass = true;
		
		$scope.disableInputs();
		
		if ( $scope.firstTimeClick == true ) {
			dataFactory.httpRequest(URL+'/truckstop/matchLoadDetail/'+truckstopId+'/'+loadId).then(function(data) {
				var modalElem = $('#edit-fetched-load');
				$('#edit-fetched-load').modal('show')
				modalElem.children('.modal-dialog').addClass('modal-lg');
				
				$scope.editSavedLoad = {};
				$scope.editSavedDist = {};
				$scope.editSavedLoad = data.encodedJobRecord;
				$scope.deadmileSave = deadmile;
				if ( $scope.editSavedLoad.PaymentAmount == 0 || $scope.editSavedLoad.PaymentAmount == '' || $scope.editSavedLoad.PaymentAmount == undefined ) {
					$scope.showPaymentCal = true;
					$scope.editSavedLoad.overall_total_rate_mile = parseFloat(calPayment / $scope.editSavedLoad.timer_distance).toFixed(2);
				} else {
					$scope.showPaymentCal = false;
				}
				
				$scope.calPaymentSaved = calPayment;
				$scope.editSavedLoad.PaymentAmount = calPayment;
				$scope.editSavedDist = data.distance;
				$scope.primaryLoadId = data.primaryLoadId;
				$scope.statesData = data.states_data;
				
				$scope.showhighlighted = 'loadDetail';
				$scope.Message = '';
				$scope.editLoads = true;
				$scope.matchingTrucks = false;
				$scope.showMaps = false;
				
				$scope.fetch_triumph_request($scope.editSavedLoad.MCNumber,$scope.editSavedLoad.DOTNumber);
			
				if ( loadId == '' || loadId == undefined ) {
					$scope.firstTimeClick = false;
				}
			});
		} 
	}
	
	$scope.disableInputs  = function() {
		$(".enable-disable-inputs").find("input,textarea,select").attr("disabled", true);
	}
	
	$scope.hideLoadDetailPopup = function() {
		$scope.firstTimeClick = true;
	}
	
	$scope.fetchSpecialNote = function ( truckstopId ) {
		
		$scope.SpecialInfo = '';
		
		dataFactory.httpRequest('truckstop/fetch_truckstop_special_note/'+truckstopId).then(function(data) {
			
			if ( data.specialInfo.trim() != '' ) {
				var specialInfo =  data.specialInfo;
			} else {
				var specialInfo = "No information available."; 
			}
			$scope.SpecialInfo = specialInfo;
			$('#specialInfoModal').modal('show')
       	});

	}

	$scope.saveEditLoad = function(editSavedLoad) {
	
		dataFactory.httpRequest(URL+'/jobs/edit_live/'+$scope.primaryLoadId,'POST',{},{jobRecords: $scope.editSavedLoad, jobPrimary: $scope.primaryLoadId, jobDistance: $scope.editSavedDist}).then(function(data) {
			$scope.Message = 'The load details has been saved successfully.';
			$scope.loadmsg = true;
			$scope.save_cancel_div = false;
			$scope.save_edit_div = true;
			$scope.showFormClass = true;
			$scope.primaryLoadId  = data.id;
			
			$scope.firstTimeClick = true;
		});
	}
	
	$scope.changeTruckDetailFields = function() {
		$scope.showTruckDetailEdit = true;
		$scope.showTruckEdit = true;
	}
	
	$scope.changeTruckDetailStatus = function() {
		$scope.showTruckDetailEdit = false;
		$scope.showTruckEdit = false;
	}
	
	$scope.fetchMatchingTruck = function( truckstopId,loadId ) {
		
		$scope.showMatchingTrucks = true;
		$scope.showTruckDetailEdit = false;
		$scope.showTruckEdit = false;
		
		if ( loadId == '' || loadId == undefined) {
			var url_segment = 'fetch_matched_trucks_live';
			var ldID = 'L';
			var thirdPa = 1;
		} else {
			var url_segment = 'fetch_matched_trucks';
			var ldID = loadId;
			var thirdPa = 0;
		}
	
		dataFactory.httpRequest(URL+'/truckstop/'+url_segment+'/'+truckstopId+'/'+ldID+'/1'+'/'+$scope.vehicleIdRepeat,'POST',{},{jobRecords: $scope.editSavedLoad, jobPrimary: $scope.primaryLoadId, jobDistance: $scope.editSavedDist, saveDeadMile : $scope.deadmileSave, savedCalPayment: $scope.calPaymentSaved }).then(function(data) {
			$scope.showMatchingTrucks = false;
			$scope.matchedTruckData = {};
		
			$scope.matchedTruckData = data.vehicles_Available;
			$scope.matchedTruckJobData = data.jobRecord;
						
			if ( data.jobRecord.payment_amount != '' && data.jobRecord.payment_amount != 0 && data.jobRecord.payment_amount != undefined) {
				$scope.showNotCalculatedRecords = false;
				$scope.showPaymentCal = false;
			} else if ( data.jobRecord.PaymentAmount == '' || parseInt(data.jobRecord.PaymentAmount) == 0 || data.jobRecord.PaymentAmount == undefined ) {
				$scope.showNotCalculatedRecords = false;
			}  else {
				$scope.showNotCalculatedRecords = false;
			}
						
			$scope.showhighlighted = 'matchTruck';
			$scope.Message = '';
			$scope.editLoads = false;
			$scope.showMaps = false;
			$scope.matchingTrucks = true;
			//~ $scope.editSavedDist = data.distance;
			//~ $scope.primaryLoadId = data.primaryLoadId;
		
			$scope.$broadcast("dataloaded");
		});
		
	}
	
	$scope.changeEditStatus = function() {
		$scope.save_cancel_div = true;
		$scope.save_edit_div = false;
		$scope.showFormClass = false;
	}
	
	$scope.changeSaveStatus = function() {
	
		$scope.save_cancel_div = false;
		$scope.save_edit_div = true;
		$scope.showFormClass = true;
	}
	
	$scope.showhideMatchDetail = function() {
		$scope.showPlusMinus = false;
	}
	
	$scope.fetch_triumph_request = function( mcNumber, usDot ) {
		$scope.showSearchButtonText = true;
				
		dataFactory.httpRequest(URL+'/triumph/index/'+mcNumber+'/'+usDot).then(function(data) {
			if ( data.length == 0 ) {
				mc_status = 'Not Available';
			} else {
				if ( data.creditResultTypeId.name == 'Credit Request Approved' ) {
					mc_status = 'Approved';
				} else {
					mc_status = 'Not Approved';
				}
				
				if ( data.companyName != null && data.phone != null ) {
					$scope.editSavedLoad.TruckCompanyName = data.companyName;
					$scope.editSavedLoad.TruckCompanyPhone = data.phone;
					$scope.editSavedLoad.postingAddress = data.city + ',' + data.state;
				}
			}
			$scope.editSavedLoad.brokerStatus = mc_status;
			$scope.showSearchButtonText = false;
		});
		
	}
	
	$scope.showRelatedMap = function( truckstopId, loadId ) {
		$scope.showhighlighted = 'showMap';
		$scope.editLoads = false;
		$scope.showMaps = true;
		$scope.matchingTrucks = false;

        $scope.origin  =$scope.editSavedLoad.OriginCity +',' + $scope.editSavedLoad.OriginState;
        $scope.destination = $scope.editSavedLoad.DestinationCity +',' + $scope.editSavedLoad.DestinationState;
        var panel = document.getElementById('panel');
  		panel.innerHTML = '';
		
		var mapOptions = {
			zoom: 13,
			scrollwheel: false,
			scaleControl: false,
			center: new google.maps.LatLng(40.7482333, -73.8681295),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		
		$scope.mapStop = new google.maps.Map(document.getElementById('map'), mapOptions);
		$scope.mapFuelStop = new google.maps.Map(document.getElementById('Fuelmap'), mapOptions);
	    $scope.infowindowStop = new google.maps.InfoWindow();
		$scope.directionsServ = new google.maps.DirectionsService();
	   	    
	    $scope.directionsTruckstop = new google.maps.DirectionsRenderer({
			map: $scope.mapStop,
	        routeIndex: 0,
	        panel:panel
		});
		
		$scope.directionsFuelstop = new google.maps.DirectionsRenderer({
			map: $scope.mapFuelStop,
	        routeIndex: 0,
	    });
	  
		var requestStop = {
			origin: $scope.origin,
			destination: $scope.destination,
			travelMode: google.maps.DirectionsTravelMode.DRIVING,
		};
		
		$scope.directionsServ.route(requestStop, function(result, status) {
          	if (status == google.maps.DirectionsStatus.OK) {
	            $scope.directionsTruckstop.setDirections(result);
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
	            
	            dataFactory.httpRequest('truckstop/get_nearby_tstops','POST',{},{coords: coords,"radius":5.0}).then(function(data) {
					$scope.truckStops = data;
					angular.forEach($scope.truckStops, function(tstop){
	                   $scope.createMarker(tstop); 
	               }) 
				});         
	/***************Code For Fuel Stops start********************************/
		
	            $scope.directionsFuelstop.setDirections(result);
	       			latArray = [];
					lngArray = [];
					var j = 0;
					for (var x = 0; x < currentRoute.length; x++) {
						if( x % 5 == 0 && x != 0) {
							latArray[j] = currentRoute[x].lat(); //Returns the latitude
							lngArray[j] = currentRoute[x].lng(); //Returns the longitude
							j++;
						}
					}

					latLength = latArray.length;
					
					for( var i = 0; i < latLength; i++) {
						(function(i){
							setTimeout(function(){
								paymont = {lat: latArray[i], lng: lngArray[i]};
						
								var service = new google.maps.places.PlacesService($scope.mapFuelStop);
									
									service.nearbySearch({
										location: paymont,
										radius: 1000,
										type: ['gas_station']
									}, callback);
							}, 500 * i);
						}(i));
					}   
				}
	        
    	});
    	
    	function callback(results, status) {
			if (status === google.maps.places.PlacesServiceStatus.OK) {
				for (var i = 0; i < results.length; i++) {
					createFuelMarker(results[i]);
				}
			} 
		}
		
		function createFuelMarker(place) {
			var placeLoc = place.geometry.location;
			var marker = new google.maps.Marker({
				map: $scope.mapFuelStop,
				position: place.geometry.location,
				icon:"./pages/img/fuelpump.png"
			});

			google.maps.event.addListener(marker, 'click', function() {
				$scope.infowindowStop.setContent(place.name);
				$scope.infowindowStop.open(map, this);
			});
		}
    /***************Code For Fuel Stops ends********************************/
    
    	window.setTimeout(function(){
            google.maps.event.trigger($scope.mapFuelStop, 'resize');
            google.maps.event.trigger($scope.mapStop, 'resize');
        },100);
	}
	
	
	$scope.createMarker = function(tstop) {
		$scope.marker = new google.maps.Marker({
	      map: $scope.mapStop,
	      position: {lat: parseFloat(tstop.latitude), lng: parseFloat(tstop.longitude)},
	      icon:"./pages/img/truck-stop.png"
	    });
	google.maps.event.addListener($scope.infowindowStop, 'domready', function() {
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

	    google.maps.event.addListener($scope.marker, 'click', function() {
	    $scope.infowindowStop.setContent("<div id='iw-container'><h2 class='iw-title'>"+tstop.name+"</h2>\
	                                      <div class='col-lg-8' style ='padding:0px'>\
	                                      <p>"+tstop.address +", "+ tstop.city+ ", "+ tstop.state + " "+ tstop.zip  +"</p>\
	                                      <p><span>Phone: "+tstop.phone+ "</span> Fax: "+tstop.fax+"</p>\
	                                      </div>\
	                                      <div class='col-lg-4'>\
											  <div class='diesel-price-popup'>\
												<div class='col-lg-4'>\
												<div class='image-div'>\
													<img src='pages/img/gas-pump.png'>\
													</div>\
												</div>\
												<div class='col-lg-8'>\
													<p><strong>2.60</strong><br>\
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
	    $scope.infowindowStop.open($scope.mapStop, this);
	    });
		$scope.infowindowStop.setContent("<div id='iw-container'><h2 class='iw-title'>"+tstop.name+"</h2>\
	                                      <div class='col-lg-8' style ='padding:0px'>\
	                                      <p>"+tstop.address +", "+ tstop.city+ ", "+ tstop.state + " "+ tstop.zip  +"</p>\
	                                      <p><span>Phone: "+tstop.phone+ "</span> Fax: "+tstop.fax+"</p>\
	                                      </div>\
	                                      <div class='col-lg-4'>\
											  <div class='diesel-price-popup'>\
												<div class='col-lg-4'>\
												<div class='image-div'>\
													<img src='pages/img/gas-pump.png'>\
													</div>\
												</div>\
												<div class='col-lg-8'>\
													<p><strong>2.60</strong><br>\
													per gallon</p>\
												</div>\
											  </div>\
	                                      </div>\
	                                      <div class='tool-detail-cover'>\
		                                      	<div class='boxes box1'>\
			                                      <div class='image'><img src='assets/images/Resturent.png'></div>\
				                                      <div class='content-section'>\
					                                      <h2>"+tstop.tRestaurants+"</h2>\
					                                      <p>Restauranttt(s)</p>\
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
	    $scope.infowindowStop.open(map,$scope.marker);
	}
	
	$scope.showDetailInfoTruckStops = function(storeId) {
	
		dataFactory.httpRequest('iterationloads/getTruckstopDetail/'+storeId).then(function(data) {
			
		});
	}
	
	$scope.showHidePanel = function(){
		$("#paneldiv").slideToggle();
		$("#map").parent(".map-inner").toggleClass("open");
	}
	
	$scope.$on('ngRepeatFinished', function(ngRepeatFinishedEvent) {
	    make_fields_editable();
	});

	/***********Load Details ends******************/
		
	$rootScope.iterationpopcheck = 0;
	$rootScope.iterationcheckmultistate = function()
	{
		if($rootScope.iterationmultistateCheck == 0)
		{
			$rootScope.iterationmultistateCheck = 1;
			$rootScope.iterationpopcheck = $rootScope.iterationmultistateCheck;
		}
		else
		{
			$rootScope.iterationmultistateCheck = 0;
			$rootScope.iterationpopcheck = $rootScope.iterationmultistateCheck;	
		}
	}
	
	$scope.iterationShowMultiState = function() {
		if ($rootScope.iterationmultistateCheck) {
			var modalElem = $('#multistate');
            $('#multistate').modal('show')
            modalElem.children('.modal-dialog').addClass('modal-lg');
        }
	}
	
	$scope.Items = [{ Name: "CT" }, {  Name: "MA" }, {  Name: "ME" }, {  Name: "NH" }, {  Name: "RI" }, { Name: "VT" }];
	$scope.Items1 = [{ Name: "DE" }, {  Name: "NJ" }, {  Name: "NY" }, {  Name: "PA" }];
	$scope.Items2 = [{ Name: "DC" }, {  Name: "MD" }, {  Name: "NC" }, {  Name: "SC" }, {  Name: "VA" }, { Name: "WV" }];
	$scope.Items3 = [{ Name: "AL" }, {  Name: "FL" }, {  Name: "GA" }, {  Name: "MS" }, {  Name: "TN" }];
	$scope.Items4 = [{ Name: "IN" }, {  Name: "KY" }, {  Name: "MI" }, {  Name: "OH" }];
	$scope.Items5 = [{ Name: "IA" }, {  Name: "MN" }, {  Name: "MT" }, {  Name: "ND" } ,{  Name: "SD" }, {  Name: "WI" }];
	$scope.Items6 = [{ Name: "IL" }, {  Name: "KS" }, {  Name: "MO" }, {  Name: "NE" }];
	$scope.Items7 = [{ Name: "AR" }, {  Name: "LA" }, {  Name: "OK" }, {  Name: "TX" }];
	$scope.Items8 = [{ Name: "AZ" }, {  Name: "CO" }, {  Name: "ID" }, {  Name: "NM" } ,{  Name: "NV" }, {  Name: "UT" }, {  Name: "WY" }];
	$scope.Items9 = [{ Name: "CA" }, {  Name: "OR" }, {  Name: "WA" }];
	$scope.Items10 = [{ Name: "AK" }];
		
	$scope.checkArray = [];
	$scope.checkAllAreas0 = function (checkSel0) {
		if (checkSel0 == true) {
            $scope.selectedAll = true;
            angular.forEach($scope.Items, function (item) {
				item.Selected = $scope.selectedAll;
				$scope.checkArray.push(item.Name);
			});
        } else {
            $scope.selectedAll = false;
            angular.forEach($scope.Items, function (item) {
				item.Selected = $scope.selectedAll;
				$scope.checkArray.pop(item.Name);
			});
        }
       
	}
	
	$scope.checkAllAreas1 = function (checkSel1) {
	    if (checkSel1 == true) {
            $scope.selectedAll1 = true;
            angular.forEach($scope.Items1, function (item) {
				item.Selected = $scope.selectedAll1;
				$scope.checkArray.push(item.Name);
			});
        } else {
            $scope.selectedAll1 = false;
            angular.forEach($scope.Items1, function (item) {
				item.Selected = $scope.selectedAll1;
				$scope.checkArray.pop(item.Name);
			});
        }
    }
		
	$scope.checkAllAreas2 = function (checkSel2) {
	    if (checkSel2 == true) {
            $scope.selectedAll2 = true;
            angular.forEach($scope.Items2, function (item) {
				item.Selected = $scope.selectedAll2;
				$scope.checkArray.push(item.Name);
			});
        } else {
            $scope.selectedAll2 = false;
            angular.forEach($scope.Items2, function (item) {
				item.Selected = $scope.selectedAll2;
				$scope.checkArray.pop(item.Name);
			});
        }
    }
		
	$scope.checkAllAreas3 = function (checkSel3) {
	    if (checkSel3 == true) {
            $scope.selectedAll3 = true;
            angular.forEach($scope.Items3, function (item) {
				item.Selected = $scope.selectedAll3;
				$scope.checkArray.push(item.Name);
			});
        } else {
            $scope.selectedAll3 = false;
            angular.forEach($scope.Items3, function (item) {
				item.Selected = $scope.selectedAll3;
				$scope.checkArray.pop(item.Name);
			});
        }
    }
	
	$scope.checkAllAreas4 = function (checkSel4) {
	    if (checkSel4 == true) {
            $scope.selectedAll4 = true;
            angular.forEach($scope.Items4, function (item) {
				item.Selected = $scope.selectedAll4;
				$scope.checkArray.push(item.Name);
			});
        } else {
            $scope.selectedAll4 = false;
            angular.forEach($scope.Items4, function (item) {
				item.Selected = $scope.selectedAll4;
				$scope.checkArray.pop(item.Name);
			});
        }
    }
	
	$scope.checkAllAreas5 = function (checkSel5) {
	    if (checkSel5 == true) {
            $scope.selectedAll = true;
            angular.forEach($scope.Items5, function (item) {
				item.Selected = $scope.selectedAll;
				$scope.checkArray.push(item.Name);
			});
        } else {
            $scope.selectedAll = false;
            angular.forEach($scope.Items5, function (item) {
				item.Selected = $scope.selectedAll;
				$scope.checkArray.pop(item.Name);
			});
        }
    }
	
	$scope.checkAllAreas6 = function (checkSel6) {
	    if (checkSel6 == true) {
            $scope.selectedAll6 = true;
            angular.forEach($scope.Items6, function (item) {
				item.Selected = $scope.selectedAll6;
				$scope.checkArray.push(item.Name);
			});
        } else {
            $scope.selectedAll6 = false;
            angular.forEach($scope.Items6, function (item) {
				item.Selected = $scope.selectedAll6;
				$scope.checkArray.pop(item.Name);
			});
        }
    }
	
	$scope.checkAllAreas7 = function (checkSel7) {
	    if (checkSel7 == true) {
            $scope.selectedAll7 = true;
            angular.forEach($scope.Items7, function (item) {
				item.Selected = $scope.selectedAll7;
				$scope.checkArray.push(item.Name);
			});
        } else {
            $scope.selectedAll7 = false;
            angular.forEach($scope.Items7, function (item) {
				item.Selected = $scope.selectedAll7;
				$scope.checkArray.pop(item.Name);
			});
        }
    }
	
	$scope.checkAllAreas8 = function (checkSel8) {
	    if ($checkSel8 == true) {
            $scope.selectedAll8 = true;
            angular.forEach($scope.Items8, function (item) {
				item.Selected = $scope.selectedAll8;
				$scope.checkArray.push(item.Name);
			});
        } else {
            $scope.selectedAll8 = false;
            angular.forEach($scope.Items8, function (item) {
				item.Selected = $scope.selectedAll8;
				$scope.checkArray.pop(item.Name);
			});
        }
    }
	
	$scope.checkAllAreas9 = function (checkSel9) {
	    if (checkSel9 == true) {
            $scope.selectedAll9 = true;
            angular.forEach($scope.Items9, function (item) {
				item.Selected = $scope.selectedAll9;
				$scope.checkArray.push(item.Name);
			});
        } else {
            $scope.selectedAll9 = false;
            angular.forEach($scope.Items9, function (item) {
				item.Selected = $scope.selectedAll9
				$scope.checkArray.pop(item.Name);
			});
        }
    }
	
	$scope.checkAllAreas10 = function (checkSel10) {
	    if (checkSel10 == true) {
            $scope.selectedAll10 = true;
            angular.forEach($scope.Items10, function (item) {
				item.Selected = $scope.selectedAll10;
				$scope.checkArray.push(item.Name);
			});
        } else {
            $scope.selectedAl10 = false;
            angular.forEach($scope.Items10, function (item) {
				item.Selected = $scope.selectedAll10;
				$scope.checkArray.pop(item.Name);
			});
        }
    }
	
	$scope.getCheckboxValue = function (item_name) {
	    if ( $scope.checkArray.indexOf(item_name) !== -1 ) {
			$scope.checkArray.pop(item_name);
		} else {
			$scope.checkArray.push(item_name);
		}
      
	}
	
	$scope.regionsclick = function(checkboxvalue, regionsvalue) {
		
		$scope.northeastenRegionArray = [ "CT", "MA", "ME", "NH" , "RI" ,"VT", "DE", "NJ", "NY", "PA" , "DC" ,"MD" ];
		$scope.midWestRegionArray = [ "IN", "KY", "MI", "OH" , "IA" ,"MN", "WI", "IL" ];
		$scope.plainsRegionArray = [ "MT", "ND", "SD", "KS" , "NE" ,"AR", "OK", "TX", "CO", "NM" , "WY"];
		$scope.southernRegionArray = [ "NC", "SC", "VA", "WV" , "AL" ,"FL", "GA", "MS", "TN", "LA"  ];
		$scope.westernRegionArray = [ "AZ", "ID", "NV", "UT" , "CA" ,"OR", "WA" ];
		
		if ( regionsvalue == 'north' ) {
			$scope.regionsNewArray = $scope.northeastenRegionArray;
		} else if ( regionsvalue == 'midwest' ) {
			$scope.regionsNewArray = $scope.midWestRegionArray;
		} else if ( regionsvalue == 'plains' ) {
			$scope.regionsNewArray = $scope.plainsRegionArray;
		} else if ( regionsvalue == 'southern' ) {
			$scope.regionsNewArray = $scope.southernRegionArray;
		} else if ( regionsvalue == 'western' ) {
			$scope.regionsNewArray = $scope.westernRegionArray;
		}
		
		if ( checkboxvalue == true ) {
			angular.forEach($scope.regionsNewArray, function (item) {
				if ( $scope.checkArray.indexOf(item) !== -1 ) {
					$scope.checkArray.pop(item);
				} else {
					$scope.checkArray.push(item);
				}
			});
		} else {
			angular.forEach($scope.regionsNewArray, function (item) {
				$scope.checkArray.pop(item);
			});
		}
	}
	
	$scope.getAllCheckedBoxes = function() {
		$scope.iterationPopData.multiDestinations = $scope.checkArray;
		$(".fill-textBox").val($scope.checkArray);
		$("#multistate").modal("hide");
	}
	
	$scope.multiDataSelected = function(){
		$scope.multiData = true;
	}

	//------------------------------------- Gantt Chart Options ----------------------------
	$scope.options = {
            mode: 'custom',
            scale: 'day',
            sortMode: undefined,
            sideMode: 'Table',
            daily: false,
            maxHeight: true,
            width: true,
            zoom: 1,
            columns: ['model.name'],
            treeTableColumns: [],
            columnsHeaders: {'model.name' : 'Driver Name'},
            columnsClasses: {'model.name' : 'gantt-column-name', 'from': 'gantt-column-from', 'to': 'gantt-column-to'},
            headers:true,
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
                'model.name': '<i class="fa fa-truck"></i> {{getHeader()}}',
                
            },
            headersFormats: {
		        day: 'ddd', 
		    },
            autoExpand: 'both',
            taskOutOfRange: 'truncate',
            fromDate: moment(null),
            toDate: undefined,
            rowContent: '<i class="fa fa-align-justify"></i> {{row.model.name}}',
            taskContent : '<i class="fa fa-tasks"></i> {{task.model.name}}',
            allowSideResizing: false,
            labelsEnabled: true,
            currentDate: 'line',
            currentDateValue: new Date(),
            draw: false,
            readOnly: true,
            groupDisplayMode: 'group',
            filterTask: '',
            filterRow: '',
           timeFrames: {
                'weekend': {
                    working: false
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

        $scope.renderGantt = function(newData) {
			//console.log(newData);	
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
	        	startDate.setDate(startDate.getDate() + 30);
	        	$scope.options.toDate = moment(startDate);
	        	$scope.data = Sample.getIterativeData(newData);
			}

			$scope.data = Sample.getIterativeData(newData);

        }

        $scope.handleTaskIconClick = function(taskModel) {
            alert('Icon from ' + taskModel.name + ' task has been clicked.');
        };

        $scope.handleRowIconClick = function(rowModel) {
            alert('Icon from ' + rowModel.name + ' row has been clicked.');
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
    }
    ]);
app.directive('timepicker', function() {
    return {
        restrict: 'A',
        link: function(scope, elem, attrs) {
            $(elem).timepicker().on('show.timepicker', function(e) {
                var widget = $('.bootstrap-timepicker-widget');
                widget.find('.glyphicon-chevron-up').removeClass().addClass('pg-arrow_maximize');
                widget.find('.glyphicon-chevron-down').removeClass().addClass('pg-arrow_minimize');
            });
        }
    }
});
