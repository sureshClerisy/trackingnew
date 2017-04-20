app.controller('investorController', function(dataFactory, $scope,  $http , $rootScope ,vehicles, portlets, investorService, $sce, $state, $stateParams, $timeout, $cookies){
	if($rootScope.loggedInUser == false)
		$state.go('login');
	var inv = this; 
	inv.vehicle = {};
    inv.selectedVehicle = "all";
	inv.vehicleList = vehicles.list;
    inv.rssFeeds    = vehicles.rssFeeds;
	inv.liveTrucks  = portlets.mapList;
    inv.thisWeek    = portlets.thisWeek;
    inv.lastWeek    = portlets.lastWeek;
    inv.vehiclesJobs= portlets.vehiclesJobs;
    inv.vehiclesWithoutDriver = portlets.vehiclesWithoutDriver;
	inv.vehicle.selected= {id:"all",vehicleName:"All Vehicles"};
    inv.saleCaption = "Last Week Sales";
    inv.dateRangeSelector = {startDate:null, endDate: null};

    $rootScope.dataTableOpts(10,14);



    if( $cookies.getObject('_gDateRangeInvDash') ){
        inv.dateRangeSelector = $cookies.getObject('_gDateRangeInvDash');
        inv.saleCaption = "Sales";
    }
    
    inv.opts = {
        opens:'left',
        autoUpdateInput: false,
        locale: {
            applyClass: 'btn-green',
            applyLabel: "Apply",
            fromLabel: "From",
            format: "YYYY-MM-DD",
            toLabel: "To",
            cancelLabel: 'Clear',
            direction:'rtl',
        },
        eventHandlers: {
            'apply.daterangepicker': function(ev, picker) {  
                if ( inv.dateRangeSelector.startDate != null && inv.dateRangeSelector != undefined && Object.keys(inv.dateRangeSelector).length > 0 ) {
                    inv.dateRangeSelector.startDate   = inv.dateRangeSelector.startDate.format('YYYY-MM-DD');
                    inv.dateRangeSelector.endDate     = inv.dateRangeSelector.endDate.format('YYYY-MM-DD');
                    $cookies.putObject('_gDateRangeInvDash', inv.dateRangeSelector);  
                    inv.updateInvestorDashboard();
                } else {
                    inv.dateRangeSelector = {startDate: null, endDate: null};
                }
            },
            'cancel.daterangepicker': function(ev, picker) { 
                inv.dateRangeSelector = {startDate: null, endDate: null};    
                $cookies.putObject('_gDateRangeInvDash', inv.dateRangeSelector);  
                inv.updateInvestorDashboard("clear");
            }
        },
    };


    inv.refreshPortlets = function(portlet,name) {
        var params = {name: name, dates:inv.dateRangeSelector};
        investorService.refreshPortlets(name, params).then(function (response) {
            $timeout(function() {
                switch( name ){
                    case 'last_week_sale'          : inv.lastWeek     = response.lastWeek; break;
                    case 'this_week_till_today'    : inv.thisWeek     = response.thisWeek; break;
                    case 'trucks_location'         : inv.liveTrucks   = response.mapList ; break;  
                    case 'rss_feeds'               : inv.rssFeeds     = response.rssFeeds; break;  
                    case 'vehicles_jobs'           : inv.vehiclesJobs = response.vehiclesJobs; break;  
                    case 'vechiles_without_driver' : inv.vehiclesWithoutDriver = response.vehiclesWithoutDriver; break;
                }
                $(portlet).portlet({ refresh: false });
            }, 500);
        });
    }


    inv.updateInvestorDashboard = function(key){
        var data = {};
        if(key != "clear"){
            data = {vehicle_id: inv.selectedVehicle};
        }
        
        investorService.getPortletsData(data).then(function (response) {
            if(key == "clear"){
                inv.saleCaption = "Last Week Sales";
            }else{
                inv.saleCaption = "Sales";
            }
            
            inv.liveTrucks  = response.mapList;
            inv.thisWeek    = response.thisWeek;
            inv.lastWeek    = response.lastWeek;
            inv.vehiclesJobs= response.vehiclesJobs;
            inv.vehiclesWithoutDriver = response.vehiclesWithoutDriver;
        });
    }


    inv.goForDetails = function (type, page, entity){
        var args = "";
        var keyParam = "all";
        if(angular.isObject(entity)){
            //args = "filterType="+type+"&userType=all&requestFrom=billings&dateFrom="+entity.fromDate+"&dateTo="+entity.toDate; 
        }else if(type == "last_week_sale" || type == "this_week_sale"){
            args = "filterType="+type+"&userType=all&requestFrom=investor";
        }else{
            args = "filterType="+type+"&userType=all&requestFrom=investor"; 
            //$state.go(page, { 'key': keyParam, q:args, type:true }, { reload: true } );    
        }

        if(type == "last_week_sale"){
            if(inv.dateRangeSelector.startDate != null && inv.dateRangeSelector.endDate != null){
                args += "&dateFrom="+inv.dateRangeSelector.startDate +"&dateTo="+inv.dateRangeSelector.endDate;     
            }
        }


        $state.go(page, { 'key': keyParam, q:args, type:true }, { reload: true } );
    }


    inv.vehicleChangeCallBack = function(item, model){
        var data = {vehicle_id: item.id};
        inv.selectedVehicle = item.id;
        investorService.getPortletsData(data).
            then(function (response) {
                inv.liveTrucks  = response.mapList;
                inv.thisWeek    = response.thisWeek;
                inv.lastWeek    = response.lastWeek;
                inv.vehiclesJobs= response.vehiclesJobs;
                inv.vehiclesWithoutDriver = response.vehiclesWithoutDriver;
                inv.renderMap();
        });
    }

	inv.renderMap = function(param, map_height){
        if ( map_height == undefined || map_height == '' ){ map_height = '90%'; }
        var zoomoptn = 5;
        if(param != undefined && param != ''){ zoomoptn = param; }

       	var mapOptions = {
            zoom: zoomoptn,
            center: {lat: 37.09024, lng: -95.712891},
            scrollwheel: false, 
            scaleControl: false, 
            icon:"./pages/img/truck-stop.png"
        } 

        inv.map = new google.maps.Map(document.getElementById('live-trucks-map'), mapOptions);
        angular.element('#live-trucks-map').height( map_height ) ;  
        var markers = [];
        
        inv.directionsService = new google.maps.DirectionsService;
        var infowindow        = new google.maps.InfoWindow();
        inv.directionDisplay  = [];
        var i=0;
        var bounds = new google.maps.LatLngBounds();
        angular.forEach(inv.liveTrucks, function(value, key) {
            var position = { lat: parseFloat( value.latitude ), lng: parseFloat( value.longitude ) };
            var icon = "./pages/img/"+value.heading.toLowerCase()+"_live.png";
            var timestamp = "Live telemetry from tracker as of ("+value.timestamp+")";
            var msgClass = "truck-live";

            if(value.mintues_ago > 2){
                icon = "./pages/img/"+value.heading.toLowerCase()+"_stale.png";
                timestamp = "Last known location telemetry stopped ("+value.timestamp+")";
                msgClass = "truck-stale";
            }

            marker = new google.maps.Marker( { position: position, icon: icon } );
            markers.push(marker);
            google.maps.event.addListener(marker, 'click', function() {
                infowindow.setContent('<div class="info-container">\
                    <p class="'+msgClass+'">'+ timestamp+'</p>\
                    <p><b>Driver : </b>'+ value.driverName+'</p>\
                    <p><b>Truck Name : </b> '+value.label+ '</p>\
                    <p><b>Address : </b> '+value.vehicle_address+', '+ value.city+', '+value.state+'</p>\
                    </div>');
                infowindow.open(inv.map, this);
            });
            bounds.extend(position);
        });

	    var mcOptions = { imagePath: "https://cdn.rawgit.com/googlemaps/js-marker-clusterer/gh-pages/images/m" };
	    var mc = new MarkerClusterer( inv.map,markers,mcOptions );     
	    google.maps.event.addListener( infowindow, 'domready', function() {
	        var iwOuter = $('.gm-style-iw').parent().addClass('live-trucks');
	    });
    }

    /**Clicking on load detail changes url withour reload state*/
    inv.clickMatchLoadDetail = function(truckstopId,loadId, deadmile,calPayment,totalCost,orignPickDate,vehicleID, index) {
        if ( loadId == '' && loadId == undefined ) 
            loadId = '';
        $rootScope.globalListingIndex = index;          // set index to update the particular record from list
        encodedUrl = btoa(truckstopId+'-'+loadId+'-'+deadmile+'-'+calPayment+'-'+totalCost+'-'+orignPickDate+'-'+vehicleID);
        $state.go('search.popup', {staticId:2,encodedurl:encodedUrl}, {notify: false,reloadOnSearch: false});
    } 

    inv.hideLoadDetailPopup = function() {
        $rootScope.firstTimeClick = true;
        if ( $rootScope.statesArr[0] != '' && $rootScope.statesArr[0] != undefined ) {
            $state.go($rootScope.statesArr[0], {}, {notify: false,reload: false});
        } else {
            $state.go('investor', {}, {notify: false,reload: false});
        }
    }
    
    /*Changing url on outer click of popup*/
    $(document).off('click','#edit-fetched-load').on("click",'#edit-fetched-load', function(event){
        var $trigger1 = $(".popup-container-wid1");
        if($trigger1 !== event.target && !$trigger1.has(event.target).length){
            if ( $rootScope.statesArr[0] != '' && $rootScope.statesArr[0] != undefined ) {
                $state.go($rootScope.statesArr[0], {}, {notify: false,reload: false});
            } else {
                $state.go('investor', {}, {notify: false,reload: false});
            }
        }
    });  


    inv.renderMap();
});