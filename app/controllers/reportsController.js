app.controller('reportsController', function(dataFactory,$scope,$http ,$rootScope , $window, $location , $cookies, $localStorage, $filter, $compile, $timeout, initialData){
	
	if($rootScope.loggedInUser == false)
		$location.path('login');
	
	$scope.vehicles = initialData.vehicles;
	//All Dispatchers with their drivers
	$scope.vDriversList = initialData.vDriversList;


	$scope.formFilter = {};
	$scope.formFilter.customDate = $filter('date')(new Date(), 'yyyy-MM-dd');
	$scope.formFilter.reportType = "individual";
	$scope.selectedScope= {id: "",driverName:"All Groups", profile_image:"",label:"",username:"All Groups", latitude:"",longitude:"",vid:"",vehicle_address:"",state:"", city:""};

	$scope.reportListOpen 	= true;
	$scope.reportOpen 		= false;
	$scope.showCustomDate 	= false;
	$scope.noFilterYet 		= true;
	$scope.filteredResults 	= false;
	$scope.isQueued 		= false;
	$scope.noRecords 		= true;
	$scope.sortType     	= 'deviceID'; // set the default sort type
  	$scope.sortReverse  	= false;  // set the default sort order
	$scope.vStatus = [{key:'IDLE',label:'Idle'},{key:'SPEED',label:'Speeding'}, {key:'TRAVEL',label:'Travel'},{key:'IGOFF',label:'Engine Off'} ,{key:'PTO_OFF',label:'PTO OFF'} ,{key:'PTO_ON',label:'PTO ON'} ];
	
	$scope.reportList = {/*Administration:[
									{name:"state_mileage", title:"State Mileage Report", context:"Miles driven per state" },
									{name:"unauthorized_usage", title:"Unauthorized Usage Report", context:"Vehicle usage during non-working hours" },
									{name:"user_login", title:"User Login Report", context:"User login statistics" },
									{name:"work_order", title:"Work Order Report", context:"Work order detail report" }
								],*/
						Performance:[
									//{name:"alert_report", title:"Alert Report", context:"Record of events meriting notifications" },
									{name:"breadcrumb_detail", title:"Breadcrumb Detail Report", context:"Chronological list of events per Vehicle" },
									{name:"loads_performance", title:"Loads Tracking Report", context:"Report of loads performance" },
									//{name:"generate_idle", title:"Generate Idle Report", context:"Idle time calculations for generators" }
								]
						};




	$scope.toggleReportOpen = function(report){
		$scope.reportListOpen = $scope.reportListOpen ? false : true;
		$scope.reportOpen = $scope.reportOpen ? false : true;
		if(angular.isObject(report)){
			//For report breadcrumb_detail
			$scope.formFilter.vStatus = ''; $scope.formFilter.vehicles = '';

			//For report loads_performance
			$scope.formFilter.reportType = 'individual'; $scope.formFilter.scope = '';

			//Common to all reports
			$scope.reportTitle = report.title;
			$scope.reportContext = report.context;
			$scope.report = report;	
		}else{
			$scope.noFilterYet = true;
			$scope.filteredResults = false;
		}
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
            direction:'ltr'
        },
        eventHandlers: {
            'cancel.daterangepicker': function(ev, picker) {  
                $scope.dateRangeValue = {startDate: null, endDate: null};
                angular.element('#reportsDRPicker').data('daterangepicker').setStartDate(new Date());
                angular.element('#reportsDRPicker').data('daterangepicker').setEndDate(new Date());
            }
        },
    };

	$scope.optsReportsBreadcrum = {
        autoUpdateInput: false,
        locale: {
            applyClass: 'btn-green',
            applyLabel: "Apply",
            fromLabel: "From",
            format: "YYYY-MM-DD",
            toLabel: "To",
            cancelLabel: 'Clear',
            direction:'ltr'
        },
        eventHandlers: {
            'cancel.daterangepicker': function(ev, picker) {  
                $scope.dateRangeValueBreadcrum= {startDate: null, endDate: null};
                angular.element('#reportsBreadCrumDRPicker').data('daterangepicker').setStartDate(new Date());
                angular.element('#reportsBreadCrumDRPicker').data('daterangepicker').setEndDate(new Date());
            }
        },
    };
    

	$scope.toggleSelectStatus = function(action,reportName) {
		switch(reportName){
			case "breadcrumb_detail": $scope.formFilter.vStatus =  (action == 'all') ? $scope.vStatus : ''; break;
			//case "loads_performance": $scope.formFilter.vStatus =  (action == 'all') ? $scope.vStatus : ''; break;
		}
		
	}
	$scope.toggleSelectVehicles = function(action) {
		if (action == 'all') {
			$scope.formFilter.vehicles = $scope.vehicles;	
		}else{
			$scope.formFilter.vehicles = '';
		}
	}
	
	$scope.toggleCustomDate = function(args){
		var today =new Date();
		today.setDate(today.getDate() - 1);
		switch(args){
			case 'today' 	: $scope.showCustomDate = false; $scope.formFilter.customDate = $filter('date')(new Date(), 'yyyy-MM-dd'); break;
			case 'yesterday': $scope.showCustomDate = false; $scope.formFilter.customDate = $filter('date')(today, 'yyyy-MM-dd'); break;
			case 'custom' 	: $scope.showCustomDate = true; $scope.dateRangeValueBreadcrum = {startDate: moment().subtract(2, 'days'), endDate: moment()}; break;
		}
	}

	$scope.showLoadWOPagination = false;
	$scope.generateReport = function(report){

		if ( $scope.dateRangeValue != undefined && report.name == 'loads_performance' ) {
			$scope.formFilter.startDate  = $scope.dateRangeValue.startDate;
			$scope.formFilter.endDate    = $scope.dateRangeValue.endDate;
		} else if ( $scope.dateRangeValueBreadcrum != undefined && report.name == 'breadcrumb_detail' && $scope.showCustomDate == true ) {
			$scope.formFilter.startDate  = $scope.dateRangeValueBreadcrum.startDate;
			$scope.formFilter.endDate    = $scope.dateRangeValueBreadcrum.endDate;
			$scope.formFilter.customDate = '';
		} else {
			$scope.formFilter.startDate = '';
			$scope.formFilter.endDate 	= '';
		}

			$scope.isQueued = true; //Disable generate report btn
			dataFactory.httpRequest(URL+'/reports/irp_'+report.name,'POST',{},{args:$scope.formFilter}).then(function(data){
				$scope.isQueued = false; //Re-enable generate report btn
				
				if ( data.wPagination != undefined ) {
					
					$scope.showLoadWOPagination = true;
					$scope.loadsListing = data.wPagination.loads;
					$scope.loadsCount   = data.wPagination.total;
				} else {
					$scope.showLoadWOPagination = false;
					$scope.columnMappings 	= data.column_mappings;
					$scope.tCols 			= data.column_mappings.length;
					$scope.showTotals 		= false;					// showing total in case of performance only
					$scope.showSecondTd 	= false;				// hiding second td in total in case of all groups selectd
					
					if(data.result){
						if(report.name == "breadcrumb_detail"){
							$scope.records = data.result;
							
						} else if(report.name == "loads_performance") {
							$scope.lpRecords  =data.result;
							if ( data.totals != undefined && Object.keys(data.totals).length > 0 ) {
								if($scope.formFilter.scope != '' && $scope.formFilter.scope != 'all' ) 
									$scope.showSecondTd = true;
								else
									$scope.showSecondTd = false;

								$scope.overAllTotal = data.totals;
								$scope.showTotals = true;
							}							
						}

						$scope.noRecords = false;	
						if( data.result.length == 0 ) {
							$scope.noRecords = true;
						} 
						
					} else {
						$scope.records = [];	
						$scope.noRecords = true;
					}
				}
				
				$scope.noFilterYet = false;
				$scope.filteredResults = true;
				
			});
		//}else if(report.name == "loads_performance"){
		//	$scope.noFilterYet = false;
		//	$scope.filteredResults = true;
		//}

	}

	$scope.toggleRow = function($event,index){
		angular.element("#hblock"+index).slideToggle();
		angular.element($event.target).toggleClass("minus-1");
	}


	$scope.itemsPerPage     = 20;
	$scope.currentPage      = 1,
	$scope.lastSortedColumn = '';
    $scope.lastSortType 	= '';
    $scope.searchFilter 	= '';

	/**
	* pagination 
	*/

	$scope.pageChanged = function(newPage){

		$scope.action = '/reports/getReportRecords/';
		$scope.currentPage = newPage;
        $scope.loadNextPage(($scope.currentPage - 1),$scope.searchFilter,$scope.lastSortedColumn,$scope.lastSortType);
    };

    $scope.breadcrumbPageChanged = function(newPage){
    	
    	$scope.action = '/reports/getBreadcrumbReportRecords/';
		$scope.currentPage = newPage;
        $scope.loadNextPage(($scope.currentPage - 1),$scope.searchFilter,$scope.lastSortedColumn,$scope.lastSortType);
    };

    $scope.loadNextPage = function(pageNumber,search,sortColumn,sortType){
    	
    	$scope.autoFetchLoads = true;
        dataFactory.httpRequest(URL+$scope.action,'Post',{} ,{ pageNo:pageNumber, itemsPerPage:$scope.itemsPerPage,searchQuery: search, sortColumn:sortColumn, sortType:sortType,formValue : $scope.formFilter }).then(function(data){
        	$scope.autoFetchLoads 	= false;
           	$scope.loadsListing 	= data.wPagination.loads;
			$scope.loadsCount   	= data.wPagination.total;
        	if(Object.keys($scope.loadsListing).length <= 0){
				$scope.haveRecords = true;
			}else{
				$scope.haveRecords = false;
			}
        });
	};

	$scope.sortCustom = function(sortColumn,type) {
		type = type == "ASC" ? "DESC" : "ASC";
		$scope.lastSortedColumn = sortColumn;
    	$scope.lastSortType 	= type;
    	$scope.idSortType = ''; $scope.dispatcherSortType = ''; $scope.driverSortType = ''; $scope.brokerSortType = ''; $scope.paymentAmountSortType = ''; $scope.totalCostSortType = ''; $scope.overallTotalProfitSortType = ''; $scope.overallTotalProfitPercentSortType = ''; $scope.MileageSortType = ''; $scope.deadmilesSortType = ''; $scope.RpmSortType = ''; $scope.pickupDateSortType = ''; $scope.OriginCitySortType = ''; $scope.OriginStateSortType = '';$scope.DeliveryDateSortType = ''; $scope.DestinationCitySortType = ''; $scope.DestinationStateSortType = '';

    	switch(sortColumn){
    		case 'id' 							: $scope.idSortType 		= type;  break;
    		case 'dispatcher'					: $scope.dispatcherSortType = type; break;
    		case 'driver'						: $scope.driverSortType 	= type; break;
    		case 'broker'			 			: $scope.brokerSortType 	= type; break;
    		case 'PaymentAmount'				: $scope.paymentAmountSortType = type; break;
    		case 'totalCost'					: $scope.totalCostSortType 	= type; break;
    		case 'overallTotalProfit'			: $scope.overallTotalProfitSortType = type; break;
    		case 'overallTotalProfitPercent'	: $scope.overallTotalProfitPercentSortType = type; break;
    		case 'Mileage'						: $scope.MileageSortType 	= type; break;
    		case 'deadmiles'					: $scope.deadmilesSortType 	= type; break;
    		case 'PickupDate'					: $scope.pickupDateSortType = type; break;
    		case 'PaymentAmount'				: $scope.PaymentAmountSortType = type; break;
    		case 'OriginCity'		 			: $scope.OriginCitySortType = type; break;
    		case 'rpm'			 				: $scope.RpmSortType 		= type; break;
    		case 'OriginState'					: $scope.OriginStateSortType = type; break;
    		case 'DeliveryDate'		 			: $scope.DeliveryDateSortType = type; break;
    		case 'DestinationCity'	 			: $scope.DestinationCitySortType = type; break;
    		case 'DestinationState' 			: $scope.DestinationStateSortType = type; break;
    	}

    	$scope.loadNextPage(($scope.currentPage - 1), $scope.searchFilter, sortColumn, type);
    }

    $scope.callSearchFilter = function(query){
    	$scope.searchFilter = query;
    	$scope.loadNextPage(($scope.currentPage - 1), query, $scope.lastSortedColumn,$scope.lastSortType);
    };

	$scope.exportToPDF = function(report){
		//if(report.name == "breadcrumb_detail"){
			dataFactory.httpRequest(URL+'/reports/export_pdf_irp_'+report.name,'POST',{},{args:$scope.formFilter,report: report}).then(function(data){
				var url = URL+'/assets/uploads/reports/'+data.output;
                var a = document.createElement('a'),
				    ev = document.createEvent("MouseEvents");
				a.href = url;
				a.download = url.slice(url.lastIndexOf('/')+1);
				ev.initMouseEvent("click", true, false, self, 0, 0, 0, 0, 0,
				                  false, false, false, false, 0, null);
				a.dispatchEvent(ev);
				
			});
		//}	
	}

	$rootScope.exportToCSV = function(report){
		
			var csvString = [];
			dataFactory.httpRequest(URL+'/reports/export_csv_irp_'+report.name,'POST',{},{args:$scope.formFilter,report: report}).then(function(data){
				$scope.csvContent = data.column_mappings.join(", ");
				$scope.csvContent += "\n";
				var regex = new RegExp(',', 'g');
				var result = data.result;
				if(angular.isObject(result)){
					angular.forEach(result, function(value, key) {
						if(report.name == "breadcrumb_detail"){
							$scope.csvContent += value.deviceID + "," + value.label + "," + value.driverName + "," + value.GMTTime + "," + value.eventType;
							if(value.eventType.toLowerCase() == "moving"){
								$scope.csvContent += " " + value.hdirection + ":" + value.vehicleSpeed+"mph";
							}
							if($scope.formFilter.includeLatLong){
								$scope.csvContent += "," + value.latitude + "," + value.longitude;
							}
							$scope.csvContent += "," + value.location.replace(regex, ' ') + "," + value.vehicleSpeed + "," + value.odometer+"\n";
						}else{
							var colsVal = [];
							for (var property in value) {
							    if (value.hasOwnProperty(property)) {
							    	var col = value[property];
							    	if(col != null){
							    		col = col.replace(/[,*{}★★★★★\n]/g,'');	
							    	}
							    	
							        colsVal.push(col);
							    }
							    
							}
							$scope.csvContent += colsVal.join();
							$scope.csvContent += "\n";
						}
					});
				}
				var timestamp = Math.floor(Date.now() / 1000);
				var fileName = "";
				if($scope.formFilter.reportType == "individual"){
					fileName = "CSV_Load_Details_"+timestamp;
				}else if($scope.formFilter.reportType == "performance"){
					fileName = "CSV_Load_Performance_"+timestamp;
				}else{
					fileName = report.name+"_"+timestamp;
				}
				
				var downloadContainer = angular.element('<div data-tap-disabled="true"><a></a></div>');
				var downloadLink = angular.element(downloadContainer.children()[0]);
				downloadLink.attr('href', 'data:application/octet-stream;base64,'+btoa($scope.csvContent));
				downloadLink.attr('download', fileName+".csv");
				downloadLink.attr('target', '_blank');
				angular.element('body').append(downloadContainer);
				$timeout(function () {
				  downloadLink[0].click();
				  downloadLink.remove();
				}, null);
	        });
	    
		
    }



    $scope.exportToHTML = function(report,print){
	   	if ( angular.isObject($scope.formFilter.startDate)) {
    		$scope.formFilter.startDate = $scope.formFilter.startDate.format("YYYY-MM-DD");
			$scope.formFilter.endDate   = $scope.formFilter.endDate.format("YYYY-MM-DD");
    	} else {
    		$scope.formFilter.startDate = '';
			$scope.formFilter.endDate   = '';
    	}

    	var url = URL+'/reports/export_html_irp_'+report.name+"/?";
    	var queryArgs = $.param({args:$scope.formFilter,report: report});
    	url +=queryArgs; 
    	var winPrint = $window.open(url,'_blank');
    	if(print){
	    	winPrint.focus();
			winPrint.print();
		}
    }


    $scope.onSelectVehicleCallback = function (item, model){
       	$scope.selectedScope = item.vid;
       	if(item.vid == "" ){
            //$scope.driversOnDashboard = [];
            $scope.formFilter.selScope = [];
            $scope.formFilter.scope = "all";
            $scope.formFilter.driverId = [];
			$scope.formFilter.dispId = '';
        }else if(item.label == '_idispatcher'){
            $scope.formFilter.selScope = [];
            $scope.formFilter.scope = "dispatcher";
            $scope.formFilter.dispId = item.dispId;
            $scope.formFilter.driverId = '';
            $scope.vtype = '_idispatcher';
            angular.forEach($scope.vDriversList, function(value, key) {
                if(value.username == item.username && value.label != '_idispatcher'){
                    $scope.formFilter.selScope.push(value.vid); //Added vehicle ids
                }
            });
        }else{
        	if(item.label == '_team'){
        		$scope.formFilter.scope = "team";
        		$scope.vtype = '_team';	
        	}else{
        		$scope.formFilter.scope = "driver";	
        	}
    		
    		$scope.formFilter.driverId = item.id;
			$scope.formFilter.dispId = item.dispId;

        	
            /*if($scope.formFilter.selScope.indexOf(item.vid) === -1) {
                $scope.formFilter.selScope.push(item.vid);     
            }*/
            $scope.formFilter.selScope = [];     
            $scope.formFilter.selScope.push(item.vid);
        }
    }

    $scope.groupFind = function(item){
        if(item.username !== "")
            return 'Dispatcher: '+item.username;
        else
            return item.username;
    }


});