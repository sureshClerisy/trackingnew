app.controller('driversInsightsController', ["dataFactory","$scope","$rootScope", "$state","$location",  "driverListing",'$q',function(dataFactory,$scope,$rootScope ,$state, $location ,  driverListing ,$q){
	if($rootScope.loggedInUser == false)
		$location.path('login');

	var vmInsights = this;
	
	vmInsights.drivers = driverListing.driversList;
    if(driverListing.total != undefined){
        vmInsights.total = driverListing.total;
    }else{
        vmInsights.total = driverListing.driversList.length;    
    }
	
	vmInsights.filterArgs = (driverListing.filterArgs != undefined ) ? driverListing.filterArgs : []; 
	if(vmInsights.total > 0){
		vmInsights.haveRecords = true;	
	}else{
		vmInsights.haveRecords = false;
	}
	
    //-------------- Pagination functions ------------------------- 



    vmInsights.goToDriverLoads = function(type,page, extra){
    	var args ="";
        if(extra.recordType != undefined && extra.recordType == "withoutTruck"){
            $state.go("trucks", {type:true }, { reload: true } );
        }else if(extra.recordType != undefined && extra.recordType == "trucksReporting"){
            $state.go("editTruck", { 'id': extra.vehicleId, type:true }, { reload: true } );
        }else{
            args+= "filterType="+vmInsights.filterArgs.filterType +"&userType=driver&fromDate="+vmInsights.filterArgs.fromDate+"&driverId="+extra.driver_id+"&cilckedEntity="+extra.driverName  
            if(extra.team_driver_id !== 0 || extra.team_driver_id !== ""){
                args +="&secondDriverId="+extra.team_driver_id;
            }

            $state.go(page, { 'key': type, q:args, type:true }, { reload: true } );    
        }
    	
    }

	//Set data for pagiantion
	vmInsights.itemsPerPage     = 20;
	vmInsights.perPageOptions   = [10, 20, 50];
	vmInsights.currentPage      = 1;
	vmInsights.lastSortedColumn = '';
    vmInsights.lastSortType 	= '';
    vmInsights.loadItems = function(){
        vmInsights.loadNextPage((vmInsights.currentPage - 1),vmInsights.searchFilter,vmInsights.lastSortedColumn,vmInsights.lastSortType);
    };

    vmInsights.pageChanged = function(newPage){
        vmInsights.currentPage = newPage;
        vmInsights.loadItems();
    };

    vmInsights.exportInsights = function(search){
        dataFactory.httpRequest(URL+'/Loads/skipAcl_getDriversInsightsRecords/','Post',{} ,{ pageNo:'', itemsPerPage:vmInsights.itemsPerPage,searchQuery: search, sortColumn:'', sortType:'',filterArgs:vmInsights.filterArgs,'export':1}).then(function(data){
            $rootScope.donwloadExcelFile(data.fileName);
        });
    }

    vmInsights.loadNextPage = function(pageNumber,search,sortColumn,sortType){
    	vmInsights.autoFetchLoads = true;
        dataFactory.httpRequest(URL+'/Loads/skipAcl_getDriversInsightsRecords/','Post',{} ,{ pageNo:pageNumber, itemsPerPage:vmInsights.itemsPerPage,searchQuery: search, sortColumn:sortColumn, sortType:sortType,filterArgs:vmInsights.filterArgs }).then(function(data){
        	vmInsights.autoFetchLoads = false;
        	vmInsights.drivers = data.data;

        	if(Object.keys(vmInsights.drivers).length > 0){
				vmInsights.haveRecords = true;
			}else{
				vmInsights.haveRecords = false;
			}
            vmInsights.total = data.total;
        	return data;
		});
    };

    vmInsights.callSearchFilter = function(query){
    	vmInsights.loadNextPage((vmInsights.currentPage - 1), query, vmInsights.lastSortedColumn,vmInsights.lastSortType);
    };


    vmInsights.sortCustom = function(sortColumn,type) {
		type = type == "ASC" ? "DESC" : "ASC";
		vmInsights.lastSortedColumn = sortColumn;
    	vmInsights.lastSortType 	= type;
    	vmInsights.idSortType = ''; vmInsights.PointOfContactPhoneSortType = ''; vmInsights.equipment_optionsSortType = ''; vmInsights.LoadTypeSortType = ''; vmInsights.PickupDateSortType = ''; vmInsights.DeliveryDateSortType = ''; vmInsights.OriginCitySortType = ''; vmInsights.OriginStateSortType = ''; vmInsights.DestinationCitySortType = ''; vmInsights.DestinationStateSortType = ''; vmInsights.driverNameSortType = ''; vmInsights.invoiceNoSortType = ''; vmInsights.PaymentAmountSortType = ''; vmInsights.MileageSortType = '';vmInsights.deadmilesSortType = ''; vmInsights.LengthSortType = ''; vmInsights.LengthSortType = ''; vmInsights.WeightSortType = ''; vmInsights.companyNameSortType = ''; vmInsights.load_sourceSortType = ''; vmInsights.JobStatusSortType = ''; vmInsights.RpmSortType = '';

    	switch(sortColumn){
    		case 'id' 					: vmInsights.idSortType = type;  break;
    		case 'PointOfContactPhone'	: vmInsights.PointOfContactPhoneSortType = type; break;
    		case 'equipment_options'	: vmInsights.equipment_optionsSortType = type; break;
    		case 'LoadType'			 	: vmInsights.LoadTypeSortType = type; break;
    		case 'PickupDate'			: vmInsights.PickupDateSortType = type; break;
    		case 'DeliveryDate'			: vmInsights.DeliveryDateSortType = type; break;
    		case 'OriginCity'			: vmInsights.OriginCitySortType = type; break;
    		case 'OriginState'			: vmInsights.OriginStateSortType = type; break;
    		case 'DestinationCity'		: vmInsights.DestinationCitySortType = type; break;
    		case 'DestinationState'		: vmInsights.DestinationStateSortType = type; break;
    		case 'driverName'			: vmInsights.driverNameSortType = type; break;
    		case 'invoiceNo'			: vmInsights.invoiceNoSortType = type; break;
    		case 'PaymentAmount'		: vmInsights.PaymentAmountSortType = type; break;
    		case 'Mileage'			 	: vmInsights.MileageSortType = type; break;
    		case 'rpm'			 		: vmInsights.RpmSortType = type; break;
    		case 'deadmiles'			: vmInsights.deadmilesSortType = type; break;
    		case 'Length'			 	: vmInsights.LengthSortType = type; break;
    		case 'Weight'			 	: vmInsights.WeightSortType = type; break;
    		case 'TruckCompanyName'		: vmInsights.TruckCompanyNameSortType = type; break;
    		case 'load_source'			: vmInsights.load_sourceSortType = type; break;
    		case 'JobStatus'			: vmInsights.JobStatusSortType = type; break;
    	}

    	
		vmInsights.loadNextPage((vmInsights.currentPage - 1), vmInsights.searchFilter, sortColumn, type);
    }

    vmInsights.toggleRow = function($event,index){
		angular.element("#hblock"+index).slideToggle();
		angular.element($event.target).toggleClass("minus-1");
	}
	//-------------- Pagination functions -------------------------

}]);