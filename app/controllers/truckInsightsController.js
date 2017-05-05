app.controller('truckInsightsController', ["dataFactory","$scope","$rootScope", "$state","$location",  "trucksListing",'$q',function(dataFactory,$scope,$rootScope ,$state, $location ,  trucksListing ,$q){
	if($rootScope.loggedInUser == false)
		$location.path('login');

    var vmInsights = this;
	
	vmInsights.trucks = trucksListing.trucksList;
    if(trucksListing.total != undefined){
        vmInsights.total = trucksListing.total;
    }else{
        vmInsights.total = trucksListing.trucksList.length;    
    }
	
	vmInsights.filterArgs = (trucksListing.filterArgs != undefined ) ? trucksListing.filterArgs : []; 
	if(vmInsights.total > 0){
		vmInsights.haveRecords = true;	
	}else{
		vmInsights.haveRecords = false;
	}
	
    vmInsights.truckInsights = function(search){
        
        dataFactory.httpRequest(URL+'/Loads/skipAcl_getTruckInsightsRecords/','Post',{} ,{ pageNo:'', itemsPerPage:vmInsights.itemsPerPage,searchQuery: search, sortColumn:'', sortType:'',filterArgs:vmInsights.filterArgs,'export':1 }).then(function(data){
            $rootScope.donwloadExcelFile(data.fileName);
        });
    }

    //-------------- Pagination functions ------------------------- 



    vmInsights.goToTrucks = function(type,page, extra){
    	var args ="";
        $state.go("editTruck", { 'id': extra.vehicleId, type:true }, { reload: true } );
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

    vmInsights.loadNextPage = function(pageNumber,search,sortColumn,sortType){
    	vmInsights.autoFetchLoads = true;
        dataFactory.httpRequest(URL+'/Loads/skipAcl_getTruckInsightsRecords/','Post',{} ,{ pageNo:pageNumber, itemsPerPage:vmInsights.itemsPerPage,searchQuery: search, sortColumn:sortColumn, sortType:sortType,filterArgs:vmInsights.filterArgs }).then(function(data){
        	vmInsights.autoFetchLoads = false;
        	vmInsights.trucks = data.data;

        	if(Object.keys(vmInsights.trucks).length > 0){
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
	//-------------- Pagination functions -------------------------
}]);