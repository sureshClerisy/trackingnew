
app.controller('AdminController', function($scope,$http,$rootScope, $localStorage,$sce,$location, $window, $cookies, $state, $stateParams,dataFactory){
	$scope.fuelType = [{ 'val' : 'Diesel','key' : 'diesel'},{ 'val' : 'Petrol','key' : 'petrol'},{ 'val' : 'Gas','key' : 'gas'}];
	$scope.pools = [];
	$rootScope.showHeader = true;
	$rootScope.showBackground = false;
	$scope.sparkline_pie_data = [3,4,3];
    $scope.skipClick = true;
    $rootScope.saveTypeLoad = "dashboard";
   
    //------- Dashboar drag drop options----------------
    $scope.tSortableWidgets = 9;

    //------------------------- Stacked Bar Chart -------------------

    Highcharts.setOptions({
        lang: {
            thousandsSep:","
        }
    });

    if( $cookies.getObject('_gDateRange') ){
        $scope.dateRangeSelector = $cookies.getObject('_gDateRange');
    }else{
        $scope.dateRangeSelector = {startDate: moment().subtract(29, 'days'), endDate: moment()};    
        $cookies.putObject('_gDateRange', $scope.dateRangeSelector);  
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
            'apply.daterangepicker': function(ev, picker) {  
                if ( $scope.dateRangeSelector != undefined && Object.keys($scope.dateRangeSelector).length > 0 ) {
                    
                    $cookies.putObject('_gDateRange', $scope.dateRangeSelector);    
                    
                } else {
                    $scope.startDate = ''; $scope.endDate = '';
                }
                $scope.getWeatherInfo();
            },
            'cancel.daterangepicker': function(ev, picker) {  
                $scope.dateRangeSelector = {startDate: null, endDate: null};
                $cookies.putObject('_gDateRange', $scope.dateRangeSelector);    
                //change the selected date range of that picker
                angular.element('#drpicker').data('daterangepicker').setStartDate(new Date());
                angular.element('#drpicker').data('daterangepicker').setEndDate(new Date());
                $scope.getWeatherInfo();
            }
        },
    };


    $scope.showRecordsWithFilter = function(type,page){
        if( $cookies.getObject('_globalDropdown') ){
            var tempBuffer = $cookies.getObject('_globalDropdown');
            var sendToURL = URL+"/"+$state.href(page).slice(0, -1);
            var args = "";
            if(tempBuffer.label == "_iall" || tempBuffer.label == "" || tempBuffer.label == "all" ){
                sendToURL += "all/";
                args = "filterType="+type+"&userType=all";
                sendToURL +=  "?q="+encodeURIComponent(args);
                // $window.open(sendToURL, '_blank');
                $state.go(page, {'key':"all",q:args,type:true}, {reload: true});
            }else if(tempBuffer.label == "_idispatcher"){
                var keyParam = tempBuffer.username.toLowerCase().replace(/[\s]/g, '');
                args = "filterType="+type+"&userType=dispatcher&userToken="+tempBuffer.dispId;
                //sendToURL +=  "?q="+encodeURIComponent(args);
                //$window.open(sendToURL, '_blank');
                $state.go(page, {'key':keyParam,q:args,type:true}, {reload: true});
            }else  if(tempBuffer.vid !="" && tempBuffer.vid != undefined ){
                //sendToURL += tempBuffer.driverName.toLowerCase().replace(/[\s+]/g, '')+"/";
                var keyParam = tempBuffer.driverName.toLowerCase().replace(/[\s+]/g, '');
                if(tempBuffer.label == "_team"){
                    //$rootScope.vtype = "_iteam";
                    args = "filterType="+type+"&userType=team&userToken="+tempBuffer.id;
                }else{
                    args = "filterType="+type+"&userType=driver&userToken="+tempBuffer.id;
                }
                $state.go(page, {'key':keyParam,q:args,type:true}, {reload: true});
                //sendToURL +=  "?q="+encodeURIComponent(args);
                //$window.open(sendToURL, '_blank');
            }else{
                sendToURL += "all/";
                args = "filterType="+type+"&userType=all";
                sendToURL +=  "?q="+encodeURIComponent(args);
                //$window.open(sendToURL, '_blank');
                $state.go(page, {'key':"all",q:args,type:true}, {reload: true});
            }
        }
    }


    $scope.chartConfig = {
        chart: { type: 'line' },
        xAxis:{
            categories: $scope.performanceOf
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Values'
            }
        },
        plotOptions: {
          series: {
            stacking: 'normal'
          }
        },
        series: $scope.chartSeries,
        title: { text: '' },
        exporting: { enabled: false },
        tooltip: {
            headerFormat: '<b>{point.x}</b><br/>',
            pointFormat: '<b>{point.y}</b>',
            valueDecimals: 2,
            valuePrefix: '$',
        },

        //colors: ['#f45b5b', '#91e8e1']
   }
$scope.topBrokersXaxis = ["one","two","three","four","five"];
   $scope.chartConfigTopCustomers = {
        colors: ['rgba(7, 126, 208, 1)','rgba(109,92,174,1)','rgba(52, 214, 199, 1)','rgba(245,87,83,1)','rgba(109,92,174,1)'],
        chart: { type: 'column' },
        xAxis: {
            categories: $scope.topBrokersXaxis
        },
        yAxis: {
            min: 0,
            title: {
                text: ''
            }
        },
        plotOptions: {
          series: {
            stacking: 'normal'
          }
        },
        series: [{
            name: 'Brands',
            colorByPoint: true,
            data: [{
                name: 'Camas Transport',
                y: 84.33,
                drilldown: 'Camas Transport'
            }, {
                name: 'RPM Freight',
                y: 78.03,
                drilldown: 'RPM Freight'
            }, {
                name: 'VC Logistics',
                y: 76.38,
                drilldown: 'VC Logistics'
            }, {
                name: 'Matson Logistics',
                y: 65.77,
                drilldown: 'Matson Logistics'
            }, {
                name: 'Bennett',
                y: 58.91,
                drilldown: 'Bennett'
            }]
        }],
        title: { text: '' },
        exporting: { enabled: false },
        /*tooltip: {
            headerFormat: '<b>{point.x}</b><br/>',
            pointFormat: '<b>{point.y}</b>',
            valueDecimals: 2,
            valuePrefix: '$',
        },*/
        legend: {
            enabled: true
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '{point.y:.1f}%'
                }
            }
        },

        //colors: ['#f45b5b', '#91e8e1']
   }




    //------------------------- Stacked Bar Chart -------------------









    $scope.sortableOptions = {connectWith: ".sortable .row .col-md-6",
        handle: ".panel-heading",
        cancel: ".portlet-close",
        placeholder: "sortable-box-placeholder round-all",
        forcePlaceholderSize: true,
        tolerance: "pointer",
        forceHelperSize: true,
        revert: true,
        helper: "original",
        opacity: 0.8,
        iframeFix: false,
        stop: function(e,ui){
            /*var $list=ui.item.parent();
            var place = $list.attr('id');
            $scope.sortableOrder = $list.sortable('toArray');*/
            
            var left = angular.element("#placeholder_left").sortable('toArray');
            var right = angular.element("#placeholder_right").sortable('toArray');
            var data ={'left':left,'right':right} ;
            dataFactory.httpRequest('dashboard/updateWidgets/','POST',{},data).then(function(data) {
            });
        },
        create: function (event, ui) {
           
        }
    };

    //------- Dashboar drag drop options----------------




	$scope.sparkline_pie_options = {
		type: 'pie',
		width: 130,
		height: 130,
		sliceColors: ["rgba(7, 126, 208, 1)","rgba(109, 92, 174, 1)","rgba(52, 214, 199, 1)"]
	};
  $scope.sparkline_pie_data1 = [1,5,4];
	$scope.sparkline_pie_options1 = {
		type: 'pie',
		width: ($("#sparkline-pie").width())-50,
		height: ($("#sparkline-pie").height())-50,
		sliceColors: ["rgba(7, 126, 208, 1)","rgba(109, 92, 174, 1)","rgba(52, 214, 199, 1)"]

	};

	$scope.sparkline_line_data = [0, 10, 8, 20, 15, 10, 15, 5];
        $scope.sparkline_line_options = {
            type: 'line',
            width: '150',
            height: '150',
            chartRangeMax: 40,
            fillColor: $.Pages.getColor('danger', .3), // Get Pages contextual color
            lineColor: 'rgba(0,0,0,0)',
            highlightLineColor: 'rgba(0,0,0,.09)',
            highlightSpotColor: 'rgba(0,0,0,.21)',
    };
    

	//-------------------------weather dropdown---------------------------
	$scope.trustAsHtml = function(value) {
		return $sce.trustAsHtml(value);
    };

    

   
    /**Clicking on load detail changes url withour reload state*/
    $scope.clickMatchLoadDetail = function(truckstopId,loadId, deadmile,calPayment,totalCost,orignPickDate,vehicleID, index) {
        if ( loadId == '' && loadId == undefined ) 
            loadId = '';
        $rootScope.globalListingIndex = index;          // set index to update the particular record from list
        encodedUrl = btoa(truckstopId+'-'+loadId+'-'+deadmile+'-'+calPayment+'-'+totalCost+'-'+orignPickDate+'-'+vehicleID);
        $state.go('search.popup', {staticId:2,encodedurl:encodedUrl}, {notify: false,reloadOnSearch: false});
    } 

    $scope.hideLoadDetailPopup = function() {
        $rootScope.firstTimeClick = true;
        if ( $rootScope.statesArr[0] != '' && $rootScope.statesArr[0] != undefined ) {
            $state.go($rootScope.statesArr[0], {}, {notify: false,reload: false});
        } else {
            $state.go('assignedLoad', {}, {notify: false,reload: false});
        }
    }
    
    /*Changing url on outer click of popup*/
    $(document).off('click','#edit-fetched-load').on("click",'#edit-fetched-load', function(event){
        var $trigger1 = $(".popup-container-wid1");
        if($trigger1 !== event.target && !$trigger1.has(event.target).length){
            if ( $rootScope.statesArr[0] != '' && $rootScope.statesArr[0] != undefined ) {
                $state.go($rootScope.statesArr[0], {}, {notify: false,reload: false});
            } else {
                $state.go('assignedLoad', {}, {notify: false,reload: false});
            }
        }
    });  

    $scope.onSelectVehicleCallback = function (item, model){
        $scope.search_vehicle = item.vid;
        $cookies.remove("_globalDropdown");
        $cookies.putObject('_globalDropdown', item);    
        if(item.vid == "" ){
            $scope.driversOnDashboard = [];
            $scope.selDrivers = [];
            $scope.showDrivers = false;
            $scope.vtype = '_iall';
            $scope.skipClick = true;
            $scope.getWeatherInfo();
            
        }else if(item.label == '_idispatcher'){
            $scope.showDrivers = false;
            $scope.driversOnDashboard = [];
            $scope.selDrivers = [];
            $scope.vtype = '_idispatcher';
            $scope.did = item.dispId;
            $scope.skipClick = true;
            angular.forEach($scope.vehicleList, function(value, key) {
                if(value.username == item.username && value.label != '_idispatcher'){
                    //$scope.driversOnDashboard.push(value);
                    $scope.selDrivers.push(value.vid);
                }
            });
            $scope.getWeatherInfo();
        }else{
            $scope.skipClick = false;
            if(item.label == '_team'){
                $scope.vtype = '_iteam';
            }else{
                $scope.vtype = '_idriver';
            }
            /*if($scope.selDrivers.indexOf(item.vid) === -1) {
                $scope.driversOnDashboard.push(item);     
                $scope.selDrivers.push(item.vid);     
                $scope.showDrivers = true;
                $scope.getWeatherInfo();
            }*/
            $scope.driversOnDashboard = [];
            $scope.selDrivers = [];
            $scope.driversOnDashboard.push(item);     
            $scope.selDrivers.push(item.vid);     
            $scope.showDrivers = true;
            $scope.getWeatherInfo();
        }
	};

    $scope.groupFind = function(item){
        if(item.username !== "")
            return 'Dispatcher: '+item.username;
        else
            return item.username;
    }

    $scope.showDrivers = false;
    $scope.removeDashboardView = function(driver){
        var index = $scope.selDrivers.indexOf(driver.vid);
        if (index > -1) {
            $scope.selDrivers.splice(index, 1);
            var newBorn = [];
            newBorn = $scope.driversOnDashboard.filter(function(el) {
                return el.vid !== driver.vid;
            });
            $scope.driversOnDashboard = newBorn;

        }

        if($scope.driversOnDashboard.length <= 0 && $scope.user_role != 2){
            $scope.showDrivers = false;
            $scope.vtype = '_iall';
            $scope.search_vehicle1.selected= {id: "",driverName:$rootScope.languageArray.allGroups, profile_image:"",label:"",username:"All Groups", latitude:"",longitude:"",vid:"",vehicle_address:"",state:"", city:""};
            $scope.skipClick = true;
            
        }else if($scope.driversOnDashboard.length <= 0 && $scope.user_role == 2){
            $scope.showDrivers = false;
            $scope.vtype="_idispatcher";
            $scope.did= driver.dispId;
            $scope.search_vehicle1.selected= {dispId:driver.dispId, id: "",driverName:"Dispatcher : "+driver.username, profile_image:"",label:"",username:driver.username, latitude:"",longitude:"",vid:"",vehicle_address:"",state:"", city:""};
            $cookies.remove("_globalDropdown");
            $cookies.putObject('_globalDropdown', $scope.search_vehicle1.selected);
            $scope.skipClick = true;
            
        }

        $scope.getWeatherInfo();

        

    }
	//-------------------------weather dropdown---------------------------

    //------------------------- Get Weather Info ----------------------------
    $scope.search_vehicle = 'false';
    $scope.changeWeather = 'false';
    $scope.weatherStatus = false;
    $scope.weatherNotFound = true;
    $scope.weatherShow = false;
    $scope.driversOnDashboard = []; //Dashboard drivers multiview && aggregated
    $scope.selDrivers = []; //Dashboard drivers multiview && aggregated
    $scope.driverName = false;
    dataFactory.httpRequest(URL+'/dashboard/fetchWidgetsOrder').then(function(data){
		if(data.widgetsOrder.length == 0 ) {
			$scope.tSortableWidgetsLeft  = '1,2,3,4';
			$scope.tSortableWidgetsRight = '6,7,8,9,5';	
		} else {
			$scope.tSortableWidgetsLeft  = data.widgetsOrder.left;
			$scope.tSortableWidgetsRight = data.widgetsOrder.right;
		}
        $scope.user_role = data.user_role;

        var orderArray = $scope.tSortableWidgetsLeft.split(',');
        
        var listArray = $('.widget_div .wpanel');
        for (var i = 0; i < orderArray.length; i++) {
            $('#placeholder_left').append(listArray[orderArray[i]-1]);
        }

        var orderArray = $scope.tSortableWidgetsRight.split(',');
        for (var i = 0; i < orderArray.length; i++) {
            $('#placeholder_right').append(listArray[orderArray[i]-1]);
        } 
        
        if( ($rootScope.ofDriver == undefined || $rootScope.ofDriver == '') && data.selDrivers.length > 0){
            if( $cookies.getObject('_globalDropdown') ){
                var tempBuffer = $cookies.getObject('_globalDropdown');
                if(tempBuffer.vid !="" && tempBuffer.vid != undefined && tempBuffer.label != "_idispatcher"){
                    $rootScope.ofDriver = tempBuffer.vid;
                    $scope.skipClick = false;
                    if(tempBuffer.label == "_team"){
                        $rootScope.vtype = "_iteam";
                    }
                }else if (tempBuffer.label == "_idispatcher"){
                    $scope.skipClick = true;
                    $rootScope.ofFilter = {};
                    $rootScope.ofFilter.vtype="_idispatcher";
                    $rootScope.ofFilter.did= tempBuffer.dispId;
                }
                $rootScope.ofDriver = ($rootScope.ofDriver == undefined) ? '':$rootScope.ofDriver;
                $scope.dashboardData($rootScope.ofDriver);    
            }else{
                $scope.skipClick = true;
                $scope.selDrivers = data.selDrivers;
                $scope.vtype = '_idispatcher';
                $scope.search_vehicle1 = {};
                $scope.search_vehicle1.selected = data.selectedDriver;
                $cookies.remove("_globalDropdown");
                $cookies.putObject('_globalDropdown', data.selectedDriver);    
                $scope.driverName = true;
                $scope.getWeatherInfo();
            }
        }else {
            if($rootScope.ofDriver == "" || $rootScope.ofDriver == undefined){
                if( $cookies.getObject('_globalDropdown') ){
                    var tempBuffer = $cookies.getObject('_globalDropdown'); 
                    if(tempBuffer.vid !="" && tempBuffer.vid != undefined && tempBuffer.label != "_idispatcher" && tempBuffer.label != "_iall" && tempBuffer.label != ""){
                        $scope.skipClick = false;
                        $rootScope.ofDriver = tempBuffer.vid;
                        if(tempBuffer.label == "_team"){
                            $scope.vtype = "_iteam";
                        }
                    }else if (tempBuffer.label == "_idispatcher"){
                        $scope.skipClick = true;
                        $rootScope.ofFilter = {};
                        $rootScope.ofFilter.vtype="_idispatcher";
                        $rootScope.ofFilter.did= tempBuffer.dispId;
                    }
                }    
            }

            
            $rootScope.ofDriver = ($rootScope.ofDriver == undefined) ? '':$rootScope.ofDriver;
            $scope.dashboardData($rootScope.ofDriver);    
        }

        
        
    });
    $rootScope.ofFilter = {};
	$scope.changeLang = function(){
        if($scope.user_role != 2 && $scope.user_role != 4){
			$scope.vehicleList[0].driverName = $rootScope.languageArray.allGroups;  //r288
        }
	}
    $scope.dashboardData = function(ofDriver){
        $rootScope.ofFilter.startDate = $scope.dateRangeSelector.startDate;
        $rootScope.ofFilter.endDate = $scope.dateRangeSelector.endDate

        dataFactory.httpRequest(URL+'/dashboard/index/'+ofDriver,'POST',{},$rootScope.ofFilter).then(function(data){
            $scope.currentWeather = data.currentWeather;
            $scope.dailyForecast = data.dailyForecast;
            $scope.vehicleList = data.vehicleList;
            $scope.wdriver = data.vehicleLabel;
            $scope.summary = data.loadsChart.summary;
            if($scope.user_role == 2){
                $scope.vehicleList[0].driverName = $rootScope.languageArray.allDrivers;  //r288    
            }else{
                $scope.vehicleList[0].driverName = $rootScope.languageArray.allGroups;  //r288    
            }
            
            

            
            $scope.liveTrucks = data.vehicleLocation.allVehicles;
            
            $scope.search_vehicle1 = {};
            /********** for getting selected driver label - r288****/
            if(ofDriver == ''){
                
                if( $cookies.getObject('_globalDropdown') ){
                    $scope.search_vehicle1.selected = $cookies.getObject('_globalDropdown');
                }else{
                    $scope.search_vehicle1.selected = {id: data.id,driverName:$rootScope.languageArray.allGroups, profile_image:'',label:'',username:data.username, latitude:'',longitude:'',vid:data.vehicleID,vehicle_address:data.vehicle_address,state:data.state, city:data.city};
                    $cookies.remove("_globalDropdown");
                    $cookies.putObject('_globalDropdown', $scope.search_vehicle1.selected);    
                }
                
            }else{    
                $scope.search_vehicle1.selected = data.selectedDriver;
                $cookies.remove("_globalDropdown");
                if(data.selectedDriver.dtype !== undefined && data.selectedDriver.dtype.length > 0){
                    data.selectedDriver.label = "_team";
                    $scope.vtype = '_team';
                }
                $cookies.putObject('_globalDropdown', data.selectedDriver);    
                $scope.driversOnDashboard.push( $scope.search_vehicle1.selected );
                $scope.selDrivers = [];
                if(data.selectedDriver.label != "" && data.selectedDriver.label != "_idispatcher" && data.selectedDriver.label != "_iall"){
                    $scope.selDrivers.push(data.selectedDriver.vid);    
                }
                

                $scope.showDrivers = true;
                $rootScope.ofDriver = '';
            }

            // -------------- Stacked Chart Update ------------------
                $rootScope.loadPerformance = data.chartStack.trecords;
                if($rootScope.loadPerformance.length > 0 ){
                    $scope.haveRecords = true;
                }else{
                    $scope.haveRecords = false;
                }
                $scope.typeOfData = data.chartStack.type;                 
                switch($scope.typeOfData){
                    case "_iall"        : $scope.fColumn = $scope.languageArray.dispatcher; $scope.skipClick = true;break;
                    case "_idispatcher" : $scope.fColumn = $scope.languageArray.driver; $scope.skipClick = true;break;
                    case "_iteam"       :
                    case "_idriver"     : $scope.fColumn = $scope.languageArray.loadno; $scope.skipClick = false;break;
                    default             : $scope.fColumn = $scope.languageArray.dispatcher;$scope.skipClick = true;break;
                }
                $scope.chartConfig.xAxis    = { categories: data.chartStack.xaxis };
                $scope.chartConfig.series   = [
                                                {"name": "Charges",  "data" : data.chartStack.charges,  type: "column", id: 's4'},
                                                {"name": "Invoiced", "data" : data.chartStack.invoiced, type: "column", id: 's3'},
                                                {"name": "Profit Amount",  "data" : data.chartStack.profitAmount,id: 's5'}
                                            ];
            // -------------- Stacked Chart Update ------------------



            /********** for getting selected driver label - r288****/
            $scope.weatherNotFound = data.weatherNotFound.status;
            if($scope.weatherNotFound){
                $scope.weatherStatus = true;  
                $scope.currentWeather.name = data.weatherNotFound.name;
                $scope.currentWeather.country = data.weatherNotFound.country;
            }
            if(data.success == true){
    		   $scope.weatherShow = true;
    	    }
            var chartData = [];
            chartData.push({name:'Delivered',y:data.loadsChart.delivered });
            chartData.push({name:'Booked',y:data.loadsChart.booked });
            chartData.push({name:'In-progress',y:data.loadsChart.inprogress });
            chartData.push({name:'No-loads',y:data.loadsChart.noLoads });
            $scope.drawMatrix("delivery_matrix",chartData,'Loads');

            // ---------------------------------------
            
            // ---------------------------------------
            $scope.renderGoogleMap();
            //$scope.clusteredMap();
        });
    }

    $scope.clusteredMap = function(){
        var tiles = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors, Points &copy 2012 LINZ'
            }),
            latlng = L.latLng(37.09024, -95.712891);
            var map = L.map('dash-map', {center: latlng, zoom:4, layers: [tiles]});
            var markers = L.markerClusterGroup();
            angular.forEach($scope.liveTrucks, function(value, key) {
                var position = {lat: parseFloat(value.latitude), lng: parseFloat(value.longitude)};
                var marker = L.marker(new L.LatLng(position.lat, position.lng), { title: title });
                
                var icon = "./pages/img/truck-live.png";
                var msgClass = "truck-live";
                if(value.mintues_ago > 2){
                    icon = "./pages/img/truck-stale.png";
                    timestamp = "Last known location telemetry stopped ("+value.timestamp+")";
                    msgClass = "truck-stale";
                }
                var timestamp = "Live telemetry from tracker as of ("+value.timestamp+")";
                var title = '<div class="info-container">\
                                                <p class="'+msgClass+'">'+ timestamp+'</p>\
                                                <p><b>Driver : </b>'+ value.driverName+'</p>\
                                                <p><b>Truck Name : </b> '+value.label+ '</p>\
                                                <p><b>Address : </b> '+value.vehicle_address+', '+ value.city+', '+value.state+'</p>\
                                                </div>';

                
                marker.bindPopup(title);
                markers.addLayer(marker);

           });

        map.addLayer(markers);
    }

    $scope.getWeatherInfo = function(){
        var sterm = '';
		$scope.weatherStatus = false;
        //$scope.changeWeather = true;
        if(!$scope.driverName){
            $scope.autoFetchLoads = true;
        }
        //$scope.weatherShow = false;

        if( $scope.selDrivers.length > 0)
            sterm = $scope.selDrivers.join();
        else
            sterm = '';


        data = {'did':$scope.did, 'vid':sterm,"vtype":$scope.vtype,"startDate":$scope.dateRangeSelector.startDate, "endDate" : $scope.dateRangeSelector.endDate};
        //dataFactory.httpRequest(URL+'/dashboard/index/'+).then(function(data){

        dataFactory.httpRequest(URL+'/dashboard/index','POST',{},data).then(function(data){
            $scope.autoFetchLoads = false;
            $scope.currentWeather = data.currentWeather;
            $scope.dailyForecast = data.dailyForecast;
            $scope.vehicleList = data.vehicleList;
            $scope.wdriver = data.vehicleLabel;
            $scope.summary = data.loadsChart.summary;
            if($scope.driverName){
                $scope.vehicleList[0].driverName = "All Drivers";  
                $scope.driverName = false;
            }
            //$scope.loadOnTheRoad = data.loadOnTheRoad;
            $scope.liveTrucks = data.vehicleLocation.allVehicles;
          // -------------- Stacked Chart Update ------------------
            $rootScope.loadPerformance = data.chartStack.trecords;
            if($rootScope.loadPerformance.length > 0 ){
                $scope.haveRecords = true;
            }else{
                $scope.haveRecords = false;
            }

            $scope.typeOfData = data.chartStack.type;                 
            switch($scope.typeOfData){
                case "_all"         : $scope.fColumn = $scope.languageArray.dispatcher;break;
                case "_idispatcher" : $scope.fColumn = $scope.languageArray.driver;break;
                case "_iteam"       :
                case "_idriver"     : $scope.fColumn = $scope.languageArray.loadno;break;
                default             : $scope.fColumn = $scope.languageArray.dispatcher;break;
            }

            $scope.chartConfig.xAxis    = { categories: data.chartStack.xaxis };
            $scope.chartConfig.series   = [
                                            {"name": "Charges",  "data" : data.chartStack.charges,  type: "column", id: 's4'},
                                            {"name": "Invoiced", "data" : data.chartStack.invoiced, type: "column", id: 's3'},
                                            {"name": "Profit Amout",  "data" : data.chartStack.profitAmount, id: 's5'}
                                        ];
            // -------------- Stacked Chart Update ------------------


            $scope.search_vehicle = data.vehicleID;
            $scope.weatherNotFound = data.weatherNotFound.status;
            if($scope.weatherNotFound){
            	$scope.weatherStatus = true;
                $scope.currentWeather.name = data.weatherNotFound.name;
                $scope.currentWeather.country = data.weatherNotFound.country;
              }
            if(data.success==true){
               $scope.weatherShow = true;
            }   
            var chartData = [];
            chartData.push({name:'Delivered',y:data.loadsChart.delivered });
            chartData.push({name:'Booked',y:data.loadsChart.booked });
            chartData.push({name:'In-progress',y:data.loadsChart.inprogress });
            chartData.push({name:'No-loads',y:data.loadsChart.noLoads });
            $scope.drawMatrix("delivery_matrix",chartData,'Loads');
           $scope.renderGoogleMap();
        });
    }
 
    //------------------------- RSS Feeds ----------------------------



    dataFactory.httpRequest(URL+'/dashboard/getRssFeeds').then(function(data){
        $scope.rssfeeds = data.feeds;
    });




    $rootScope.updateDashboard = function(){
         var sterm = '';
        if(!$scope.driverName){
            $scope.autoFetchLoads = true;
        }
        if( $scope.selDrivers.length > 0){
            sterm = $scope.selDrivers.join();
        }
        else{
            sterm = '';
        }

        data = {'did':$scope.did, 'vid':sterm,"vtype":$scope.vtype,"startDate":$scope.dateRangeSelector.startDate, "endDate" : $scope.dateRangeSelector.endDate};
        dataFactory.httpRequest(URL+'/dashboard/updateDashboardOnLoadEdit','POST',{},data).then(function(data){
            $scope.autoFetchLoads = false;
            $scope.summary = data.loadsChart.summary;
            $rootScope.loadPerformance = data.chartStack.trecords;
            if($rootScope.loadPerformance.length > 0 ){
                $scope.haveRecords = true;
            }else{
                $scope.haveRecords = false;
            }
          // -------------- Stacked Chart Update ------------------
            
            $scope.chartConfig.xAxis    = { categories: data.chartStack.xaxis };
            $scope.chartConfig.series   = [
                                            {"name": "Charges",  "data" : data.chartStack.charges,  type: "column", id: 's4'},
                                            {"name": "Invoiced", "data" : data.chartStack.invoiced, type: "column", id: 's3'},
                                            {"name": "Profit Amonut",  "data" : data.chartStack.profitAmount, id: 's5'}
                                        ];
            // -------------- Stacked Chart Update ------------------
            var chartData = [];
            chartData.push({name:'Delivered',y:data.loadsChart.delivered });
            chartData.push({name:'Booked',y:data.loadsChart.booked });
            chartData.push({name:'In-progress',y:data.loadsChart.inprogress });
            chartData.push({name:'No-loads',y:data.loadsChart.noLoads });
            $scope.drawMatrix("delivery_matrix",chartData,'Loads');
        });
    }
 
    







    //--------------------- Map Custom Image Marker --------------------
    $scope.base_image = new Image();
    $scope.base_image2 = new Image();
    $scope.base_image.src = 'pages/img/green-point.png';
    $scope.base_image2.src = 'pages/img/red-point.png';

    $scope.getImageMarker = function(text,type,fmap){
        //Green Pointer
        var returnImg = {};
        var canvas = document.getElementById('viewport'),
        context = canvas.getContext('2d');
        canvas.height = $scope.base_image.height ;
        canvas.width = $scope.base_image.width ;
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.drawImage($scope.base_image,0,0);
        context.font = 'bold 14px Arial';
        context.fillStyle = '#359407';
        context.fillText(text,9,19);

        returnImg.green =  canvas.toDataURL();
        
        if(fmap == true)    {
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

    //--------------------- Google Map Dashboard--------------------------------
    $scope.renderGoogleMap = function(){
         var mapOptions = {
            zoom: 4,
            center: {lat: 37.09024, lng: -95.712891},
            scrollwheel: false, 
            scaleControl: false, 
            icon:"./pages/img/truck-stop.png"
        }    
        $scope.map = new google.maps.Map(document.getElementById('dash-map'), mapOptions);  
        var markers=[];
        $scope.directionsService = new google.maps.DirectionsService;
        var infowindow = new google.maps.InfoWindow();
        $scope.directionDisplay = [];
        $scope.labels = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        var i=0;
        var bounds = new google.maps.LatLngBounds();
        angular.forEach($scope.liveTrucks, function(value, key) {
            if(value.loadDetail != null){
                var origin  =value.loadDetail['OriginCity']+', '+value.loadDetail['OriginState']+', USA';
                var destination = value.loadDetail['DestinationCity']+', '+value.loadDetail['DestinationState']+', USA';

                if(value.loadDetail['OriginCity'] == "" && value.loadDetail['OriginState'] == ""){
                    origin = value.loadDetail["PickupAddress"];
                }
                if(value.loadDetail['DestinationCity'] == "" && value.loadDetail['DestinationState'] == ""){
                    destination = value.loadDetail["DestinationAddress"];
                }

                var dirRend = 'directionsDisplay'+key;
                $scope.dirRend = new google.maps.DirectionsRenderer({suppressMarkers: true,preserveViewport: true});
                $scope.dirRend.setMap($scope.map);
                $scope.text = $scope.labels[i++];//Generating google pin text
                makeRoute( origin, destination, $scope.dirRend,$scope.text);  
            }
            
            var position = {lat: parseFloat(value.latitude), lng: parseFloat(value.longitude)};
            //var icon = "./pages/img/truck-live.png";
            var icon = "./pages/img/"+value.heading.toLowerCase()+"_live.png";
            var timestamp = "Live telemetry from tracker as of ("+value.timestamp+")";
            var msgClass = "truck-live";
            if(value.mintues_ago > 2){
                //icon = "./pages/img/truck-stale.png";
                icon = "./pages/img/"+value.heading.toLowerCase()+"_stale.png";
                timestamp = "Last known location telemetry stopped ("+value.timestamp+")";
                msgClass = "truck-stale";
                
            }
            marker = new google.maps.Marker({
                position: position,
                //map: $scope.map,
                icon:icon
            });
            markers.push(marker);
            google.maps.event.addListener(marker, 'click', function() {
                infowindow.setContent('<div class="info-container">\
                                            <p class="'+msgClass+'">'+ timestamp+'</p>\
                                            <p><b>Driver : </b>'+ value.driverName+'</p>\
                                            <p><b>Truck Name : </b> '+value.label+ '</p>\
                                            <p><b>Address : </b> '+value.vehicle_address+', '+ value.city+', '+value.state+'</p>\
                                            </div>');
                infowindow.open($scope.map, this);
            });
            bounds.extend(position);

        });

        var mcOptions = {imagePath: "https://cdn.rawgit.com/googlemaps/js-marker-clusterer/gh-pages/images/m"};
        var mc = new MarkerClusterer($scope.map,markers,mcOptions);     
        google.maps.event.addListener(infowindow, 'domready', function() {
            var iwOuter = $('.gm-style-iw').parent().addClass('live-trucks');
        });


       /* google.maps.event.addListenerOnce($scope.map, 'bounds_changed', function(event) {
            this.setZoom($scope.map.getZoom()-1);
            if(this.getZoom() > 15) {
                this.setZoom(15);
            }
        });
        $scope.map.fitBounds(bounds);*/
    }
    //------------------------ RSS Feeds -------------------------------------

        function makeRoute(origin, destination,renderer,text) {
                        
            $scope.directionsService.route({
                    origin: origin,
                    destination: destination,
                    travelMode: 'DRIVING'
                }, function(response, status) {
                    if (status === 'OK') {
                        renderer.setDirections(response);
                        var leg = response.routes['0'].legs['0'];
                        var pointers = $scope.getImageMarker(text,'G',true);
                        
                        //makeMarker( leg.start_location, 'http://www.googlemapsmarkers.com/v1/'+text+'/224C16/FFFFFF/224C16', origin);
                        //makeMarker( leg.end_location, 'http://www.googlemapsmarkers.com/v1/'+text+'/A04646/FFFFFF/A04646', destination);
                        makeMarker( leg.start_location, pointers.green, origin);
                        makeMarker( leg.end_location, pointers.red, destination);

                    } else {
                        console.log('Directions request failed due to ' + status);
                    }
                });
        }

            function makeMarker( position, icon, title ) {
                //console.log(icon);
                new google.maps.Marker({
                    position: position,
                    icon: icon,
                    title: title,
                    map: $scope.map,
                
                });
            }

    
    $scope.drawMatrix = function(id,data,brand){
        $('#'+id).highcharts({
            colors: ['rgba(109,92,174,1)','rgba(52, 214, 199, 1)','rgba(7, 126, 208, 1)','rgba(98,98,98,1)'],
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: ''
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: false
                }
            },
            series: [{
                name: brand,
                colorByPoint: true,
                data: data
            }]
        });
    }

    
  $(function () {
    $('#assets_insight').highcharts({
		colors: ['rgba(52, 214, 199, 1)','rgba(7, 126, 208, 1)','rgba(98,98,98,1)'],
		
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: 0,
            plotShadow: false
        },
        title: {
            text: '',
            align: 'left',
            verticalAlign: 'left',
            y: 5
        },
        /*tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },*/
        plotOptions: {
            pie: {
                dataLabels: {
                    enabled: false,
                    distance: -50,
                    style: {
                        
                        color: 'black'
                    }
                },
                startAngle: -90,
                endAngle: 90,
                center: ['50%', '50%'],
               
            }
        },
        series: [{
            type: 'pie',
            name: 'Sevices',
            innerSize: '60%',
         
            
            data: [
                ['In', 80],
                ['Out of',13],
                ['Random', 7],
                
               
            ]
        }]
    });
    $('#driver_renewals').highcharts({
		colors: ['rgba(52, 214, 199, 1)','rgba(7, 126, 208, 1)','rgba(98,98,98,1)','rgba(109,92,174,1)'],
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: 0,
            plotShadow: false
        },
        title: {
            text: '',
            align: 'left',
            verticalAlign: 'left',
            y: 5
        },
        /*tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },*/
        plotOptions: {
            pie: {
				allowPointSelect: true,
				cursor: 'pointer',
                dataLabels: {
                    enabled: false,
                    distance: -50,
                    style: {
                        
                        color: 'black'
                    }
                },
                
                center: ['50%', '50%'],
               
            }
        },
        series: [{
            type: 'pie',
            name: 'Sevices',
            innerSize: '60%',
            data: [
                ['In Emergency', 7],
                ['High Priority',30],
                ['Opened', 40],
                ['Un Opened', 23],
                
               
            ]
        }]
    });
    $('#driver_msg').highcharts({
		colors: ['rgba(98,98,98,1)','rgba(52, 214, 199, 1)','rgba(109,92,174,1)','rgba(7, 126, 208, 1)'],
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: 0,
            plotShadow: false
        },
        title: {
            text: '',
            align: 'left',
            verticalAlign: 'left',
            y: 5
        },
        /*tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },*/
        plotOptions: {
            pie: {
				allowPointSelect: true,
				cursor: 'pointer',
                dataLabels: {
                    enabled: false,
                    distance: -50,
                    style: {
                        
                        color: 'black'
                    }
                },
                
                center: ['50%', '50%'],
               
            }
        },
        series: [{
            type: 'pie',
            name: 'Message',
            innerSize: '60%',
            data: [
                ['In Emergency', 7],
                ['High Priority',30],
                ['Opened', 40],
                ['Un Opened', 23],
                
               
            ]
        }]
    });
	// pie chart
	
    




	$('#delivery_matrix1').highcharts({
		colors: ['rgba(52, 214, 199, 1)','rgba(7, 126, 208, 1)','rgba(98,98,98,1)','rgba(109,92,174,1)'],
		chart: {
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false,
			type: 'pie'
		},
		title: {
			text: ''
		},
		//~ tooltip: {
			//~ pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		//~ },
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: false
				},
				showInLegend: false
			}
		},
		series: [{
			name: 'Brands',
			colorByPoint: true,
			data: [{
				name: 'Late Delivery',
				y: 56.33
			}, {
				name: 'On - Time',
				y: 24.03,
				sliced: true,
				selected: true
			}, {
				name: 'On The Road',
				y: 10.38
			}]
		}]
	});
    //bar-graph
	 $('#customers').highcharts({
		 colors: ['rgba(7, 126, 208, 1)','rgba(109,92,174,1)','rgba(52, 214, 199, 1)','rgba(245,87,83,1)','rgba(109,92,174,1)'],
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            type: ''
        },
        yAxis: {
            title: {
                text: ''
            }

        },
        legend: {
            enabled: false
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '{point.y:.1f}%'
                }
            }
        },

        //~ tooltip: {
            //~ headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
            //~ pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
        //~ },

        series: [{
            name: 'Brands',
            colorByPoint: true,
            data: [{
                name: 'Camas Transport',
                y: 84.33,
                drilldown: 'Camas Transport'
            }, {
                name: 'RPM Freight',
                y: 78.03,
                drilldown: 'RPM Freight'
            }, {
                name: 'VC Logistics',
                y: 76.38,
                drilldown: 'VC Logistics'
            }, {
                name: 'Matson Logistics',
                y: 65.77,
                drilldown: 'Matson Logistics'
            }, {
                name: 'Bennett',
                y: 58.91,
                drilldown: 'Bennett'
            }]
        }]
       
    });
    //multiple line graph
     $('#fleet_load').highcharts({
        chart: {
            type: 'areaspline'
        },
        title: {
            text: ''
        },
        legend: {
            layout: 'vertical',
            align: 'left',
            verticalAlign: 'top',
            x: 440,
            y: 0,
            floating: true,
            borderWidth: 1,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
        },
        xAxis: {
            categories: [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday'
            ],
            plotBands: [{ // visualize the weekend
                from: 4.5,
                to: 6.5,
                color: 'rgba(68, 170, 213, .2)'
            }]
        },
        yAxis: {
            title: {
                text: ''
            }
        },
        tooltip: {
            shared: true,
            valueSuffix: 'lbs'
        },
        credits: {
            enabled: false
        },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.5
            }
        },
        series: [{
            name: 'Capacity',
            data: [42000, 43000, 40000, 39500, 42000, 44500, 42000]
        }, {
            name: 'Actual',
            data: [48000, 48000, 48000, 48000, 48000, 48000, 48000]
        }]
    });
    //colored line chart
    $('#best_safety').highcharts({
		colors: ['rgba(245,87,83,1)','rgba(52, 214, 199, 1)','rgba(7, 126, 208, 1)'],
        chart: {
            type: 'area'
        },
        title: {
            text: ''
        },
        //~ xAxis: {
            //~ categories: ['Apples', 'Oranges', 'Pears', 'Grapes', 'Bananas']
        //~ },
        credits: {
            enabled: false
        },
        series: [{
            name: 'Failed',
            data: [5, 3, 4, 7, 2]
        }, {
            name: 'Passed',
            data: [2, -2, -3, 2, 1]
        }, {
            name: 'Issues',
            data: [3, 4, 4, -2, 5]
        }]
    });
    //bar graph with line
     /*$('#load_on_road').highcharts({
        chart: {
            zoomType: ''
        },
        title: {
            text: ''
            
        },
        subtitle: {
            text: ''
        },
        xAxis: [{
            categories: ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI',
                'SAT'],
            crosshair: true
        }],
        yAxis: [{ // Primary yAxis
            labels: {
                format: '',
                style: {
                    color: Highcharts.getOptions().colors[2]
                }
            },
            title: {
                text: '',
                style: {
                    color: Highcharts.getOptions().colors[2]
                }
            },
            opposite: true

        }, { // Secondary yAxis
            gridLineWidth: 0,
            title: {
                text: '',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            labels: {
                format: '',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            }

        }, { // Tertiary yAxis
            gridLineWidth: 0,
            title: {
                text: '',
                style: {
                    color: Highcharts.getOptions().colors[1]
                }
            },
            labels: {
                format: '',
                style: {
                    color: Highcharts.getOptions().colors[1]
                }
            },
            opposite: true
        }],
        tooltip: {
            shared: true
        },
        //~ legend: {
            //~ layout: 'vertical',
            //~ align: 'left',
            //~ x: 80,
            //~ verticalAlign: 'top',
            //~ y: 55,
            //~ floating: true,
            //~ backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
        //~ },
        series: [{
            name: 'Load 1',
            type: 'column',
            yAxis: 1,
            data: [168.9, 91.5, 186.4, 129.2, 144.0, 176.0, 135.6],
            tooltip: {
                valueSuffix: ''
            }

        }, {
            name: 'Load 2',
            type: 'spline',
            yAxis: 2,
            data: [606,726, 915.9, 715.5, 512.3, 809.5, 909.6],
            marker: {
                enabled: false
            },
            dashStyle: 'shortdot',
            tooltip: {
                valueSuffix: ''
            }

        }, {
            name: 'Load 3',
            type: 'spline',
            data: [15.0, 8.9, 13.5, 11.5, 6.2, 14.5, 19.2],
            tooltip: {
                valueSuffix: ''
            }
        }]
    });*/
});
  if($rootScope.loggedInUser == false) {
		$location.path('login');
	} else {	
		$rootScope.loggedInUser = true;
	}
	
});
app.directive('csSelect', function() {
        return {
            restrict: 'A',
            link: function(scope, el, attrs) {
                if (!window.SelectFx) return;

                var el = $(el).get(0);
                $(el).wrap('<div class="cs-wrapper"></div>');
                new SelectFx(el);

            }
        };
    });
app.controller('ItemController', function(dataFactory,$scope,$http ,$rootScope , $location , $cookies, $localStorage,DTOptionsBuilder){
	
	if($rootScope.loggedInUser == false)
		$location.path('logout');

  $scope.data = [];
  $scope.pageNumber = 1;
  $scope.libraryTemp = {};
  $scope.totalItemsTemp = {};

  $scope.totalItems = 0;
  $scope.pageChanged = function(newPage) {
	  getResultsPage(newPage);
  };
  
  $scope.sortType     = 'title'; // set the default sort type
  $scope.sortReverse  = false;  // set the default sort order
  $scope.searchFish   = '';     // set the default search/filter term

	//~ $scope.t1 = { predicate: 'age', reverse: true};
	//~ $scope.t2 = { predicate: 'age', reverse: true};
	//~ 
	//~ $scope.order = function(predicate, tableId) {
		//~ 
	  //~ $scope[tableId].reverse = ($scope[tableId].predicate === predicate) ? !$scope[tableId].reverse : false;
	  //~ $scope[tableId].predicate = predicate;
	//~ };
	
	$scope.dtOptions = {
			
		"destroy": true,
		fixedHeader: true,
		"scrollCollapse": true,
		"oLanguage": {
			"sLengthMenu": "_MENU_ ",
			"sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
		},
		"iDisplayLength": 50
	};
	

  getResultsPage(1);
  function getResultsPage(pageNumber) {
      if(! $.isEmptyObject($scope.libraryTemp)){
          dataFactory.httpRequest(URL+'/items?search='+$scope.searchText+'&page='+pageNumber).then(function(data) {
            $scope.data = data.data;
            $scope.totalItems = data.total;
            $scope.pageNumber = pageNumber;
          });
      }else{
	    dataFactory.httpRequest(URL+'/items?page='+pageNumber).then(function(data) {
		  $scope.data = data.data;
          $scope.totalItems = data.total;
          $scope.pageNumber = pageNumber;
        });
      }
  }

	
  $scope.searchDB = function(){
      if($scope.searchText.length >= 3){
          if($.isEmptyObject($scope.libraryTemp)){
              $scope.libraryTemp = $scope.data;
              $scope.totalItemsTemp = $scope.totalItems;
              $scope.data = {};
          }
          getResultsPage(1);
      }else{
		  if(! $.isEmptyObject($scope.libraryTemp)){
			  $scope.data = $scope.libraryTemp ;
              $scope.totalItems = $scope.totalItemsTemp;
              $scope.libraryTemp = {};
          }
      }
  }

  $scope.saveAdd = function(){
	 dataFactory.httpRequest('itemsCreate','POST',{},$scope.form).then(function(data) {
	  $scope.data.push(data);
      $(".modal").modal("hide");
    });
  }

  $scope.edit = function(id){
	  dataFactory.httpRequest('itemsEdit/'+id).then(function(data) {
    	$scope.form = data;
    });
  }

  $scope.saveEdit = function(){
    dataFactory.httpRequest('itemsUpdate/'+$scope.form.id,'PUT',{},$scope.form).then(function(data) {
      	$(".modal").modal("hide");
        $scope.data = apiModifyTable($scope.data,data.id,data);
    });
  }

  $scope.remove = function(item,index){
    var result = confirm("Are you sure delete this item?");
   	if (result) {
      dataFactory.httpRequest('itemsDelete/'+item.id,'DELETE').then(function(data) {
          $scope.data.splice(index,1);
      });
    }
  }
});


// Directive for reorganize all widgets
/*app.directive('afterRendering', ['$timeout', function (timer) {
    return {
      link: function (scope, elem, attrs, ctrl) {
        var hello = function () {
            var orderArray = angular.element("#left").text().split(',');
            //var orderArray = "2,3,4,5".split(',');
            console.log(orderArray);
            //var orderArray = [];
            var listArray = $('.widget_div .wpanel');
            for (var i = 0; i < orderArray.length; i++) {
                $('#placeholder_left').append(listArray[orderArray[i]-1]);
                console.log(listArray[orderArray[i]-1]);
            }

            var orderArray = angular.element("#right").text().split(',');
            //var orderArray = "6,1,7,8,9".split(',');
            console.log(orderArray);
            //var listArray = $('.widget_div .panel');
            for (var i = 0; i < orderArray.length; i++) {
                $('#placeholder_right').append(listArray[orderArray[i]-1]);
                console.log(listArray[orderArray[i]-1]);
            } 
        }
        timer(hello, 0);
      }
    }
  }]);*/



app.directive('hcChart', function () {
    return {
        restrict: 'E',
        template: '<div></div>',
        scope: {
            options: '='
        },
        link: function (scope, element) {
            Highcharts.chart(element[0], scope.options);
        }
    };
});
