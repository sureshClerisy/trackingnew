
app.controller('AdminController', function($scope,$interval, $document,$timeout, $http,$rootScope, $localStorage,$sce,$location, $window, $cookies, $state, $stateParams,dataFactory){
    $scope.fuelType = [{ 'val' : 'Diesel','key' : 'diesel'},{ 'val' : 'Petrol','key' : 'petrol'},{ 'val' : 'Gas','key' : 'gas'}];
    $scope.pools = [];
        $scope.printloadchart = "";
    $scope.todayReport = {};
    $scope.todayReport.totals = {};
    $rootScope.showHeader = true;
    $rootScope.showBackground = false;
    $scope.sparkline_pie_data = [3,4,3];
    $scope.skipClick = true;
    $rootScope.saveTypeLoad = "dashboard";
    $scope.city_name = "";
    $scope.country_name = "";
    $scope.today_insight = "";
    $scope.otherIcon = true;
    $rootScope.todayInsightActive ='booked';

    $scope.todayProgress = false;
    $scope.tSortableWidgets = 11;
    
    Highcharts.setOptions({
        lang: {
            thousandsSep:","
        }
    });


    if( $cookies.getObject('_gDateRangeDashboard') ){
        $scope.dateRangeSelector = $cookies.getObject('_gDateRangeDashboard');
    }else{
        $scope.dateRangeSelector = {startDate: moment().startOf('month').format('YYYY-MM-DD'), endDate: moment().format('YYYY-MM-DD')};    
        $cookies.putObject('_gDateRangeDashboard', $scope.dateRangeSelector);  
    }
   
    var globalDash = this;

    
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

                if ( $scope.dateRangeSelector.startDate != null && $scope.dateRangeSelector != undefined && Object.keys($scope.dateRangeSelector).length > 0 ) {
                    $scope.dateRangeSelector.startDate = $scope.dateRangeSelector.startDate.format('YYYY-MM-DD');
                    $scope.dateRangeSelector.endDate = $scope.dateRangeSelector.endDate.format('YYYY-MM-DD');
                    $cookies.putObject('_gDateRangeDashboard', $scope.dateRangeSelector);  
                } else {
                    $scope.dateRangeSelector = {startDate: null, endDate: null};
                }
                $scope.getWeatherInfo();
                topFiveCustomer();
            },
            'cancel.daterangepicker': function(ev, picker) {  
                $scope.dateRangeSelector = {startDate: moment().startOf('month').format('YYYY-MM-DD'), endDate: moment().format('YYYY-MM-DD')};    
                $cookies.putObject('_gDateRangeDashboard', $scope.dateRangeSelector);  
                $scope.getWeatherInfo();
                topFiveCustomer();
            }
        },
    };


    $scope.refreshTest = function(portlet,src) {
        topFiveCustomer();
        $timeout(function() {
            $(portlet).portlet({ refresh: false });
        }, 1000);
    }


   $scope.showRecordsWithFilter = function(type,page, disType, username, dispatcherId, secondDriverId,extra){
        if(type != undefined && type == 'idleloads'){
                disType = (extra.team_driver_id == 0 || extra.team_driver_id == "" ) ? 'driver' : 'team';
            args = "filterType=idle&userType="+disType+"&driverId="+extra.driver_id+"&secondDriverId="+extra.team_driver_id+"&cilckedEntity="+extra.driverName;
            $state.go(page, { 'key': 'drivers', q:args, type:true }, { reload: true } );
        }else if ( type != undefined && type == 'broker' ) {
            var brokerId = page;
            args = "userType="+type+"&userToken="+brokerId+"&dispatcherId="+dispatcherId+"&driverId="+username+"&secondDriverId="+secondDriverId+"&startDate="+$scope.dateRangeSelector.startDate+"&endDate="+$scope.dateRangeSelector.endDate;
            $state.go('loads', { 'key': 'broker', q:args, type:true }, { reload: true } );
        } else {
           if( $cookies.getObject('_globalDropdown') ){
                var tempBuffer = $cookies.getObject('_globalDropdown');
                var args = ""; var keyParam = "all";

                if(tempBuffer.label == "_iall" || tempBuffer.label == "" || tempBuffer.label == "all" ){
                    if ( type == 'reports') {
                        args = "filterType="+type+"&userType="+disType+"&userToken="+dispatcherId;
                         keyParam = username;
                    } else {
                        args = "filterType="+type+"&userType=all";
                         keyParam = "all";
                    }
                   
                } else if (tempBuffer.label == "_idispatcher"){
                    if ( type == 'reports') {
                        keyParam = username.toLowerCase().replace(/[\s]/g, '');
                        if ( secondDriverId != undefined && secondDriverId  != '' && secondDriverId != 0 )
                            args = "filterType="+type+"&userType=team&userToken="+dispatcherId+"&secondDriverId="+secondDriverId;
                        else
                            args = "filterType="+type+"&userType="+disType+"&userToken="+dispatcherId;
                    }
                    else {
                        keyParam = tempBuffer.username.toLowerCase().replace(/[\s]/g, '');
                        args = "filterType="+type+"&userType=dispatcher&userToken="+tempBuffer.dispId;
                    }

                }else  if(tempBuffer.vid !="" && tempBuffer.vid != undefined ){
                    keyParam = tempBuffer.driverName.toLowerCase().replace(/[\s+]/g, '');
                    if(tempBuffer.label == "_team"){
                        args = "filterType="+type+"&userType=team&userToken="+tempBuffer.id+"&secondDriverId="+tempBuffer.team_driver_id+"&dispId="+tempBuffer.dispId;
                    }else{
                        args = "filterType="+type+"&userType=driver&userToken="+tempBuffer.id+"&dispId="+tempBuffer.dispId;
                    }
                }else{
                    args = "filterType="+type+"&userType=all";keyParam = "all";
                }

            if( type == "withoutTruck"  || type == "trucksWithoutDriver" || type == "trucksReporting"){
                keyParam = type;    
                //args += "&fromDate="+extra ;
            }else if(keyParam != "" && ( extra == "" || extra == undefined )  ){
                args += "&startDate="+$scope.dateRangeSelector.startDate+"&endDate="+$scope.dateRangeSelector.endDate;
            }else if(extra != undefined){
                args += "&fromDate="+extra.date ;
                keyParam = "drivers";    
                if(extra.type == 'idle'){
                    keyParam = extra.type;  
                }
            }
            $state.go(page, { 'key': keyParam, q:args, type:true }, { reload: true } );
        }
    }
}

    //------------------------- Stacked Bar Chart -------------------
    
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
            stacking: 'normal',
            cursor: 'pointer',
            events: {
                click: function (event) {
                    if(event.point.clickType == "sendToDetail"){
                        $scope.showRecordsWithFilter('reports', 'loads', event.point.urlColumn, event.point.username,event.point.dispatcherId, event.point.second_driver_id);
                    } else {
                        $scope.clickMatchLoadDetail(0, event.point.jobId, '', '', '', '', 0 , '');
                    }
                  }
                }
            }
        },
        series: $scope.chartSeries,
        title: { text: '' },
        exporting: { enabled: false },
        tooltip: {
            shared : false,             
            headerFormat: '<b>{point.x}</b><br/>',
            pointFormat: '<b>{point.y}</b>',
            valueDecimals: 2,
            valuePrefix: '$',
        },

         //colors: ['#90ED7D','#5C5C61','#95CEFF',  '#f45b5b']
    }

    /**
    * Top five customers highchart
    */

// ["five","four","three","two","one"]
    $scope.topCustomersConfig = {
        chart: { type: 'line' },
        xAxis:{
            categories: '',
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Values'
            }
        },
        plotOptions: {
            series: {
                stacking: 'normal',
                cursor: 'pointer',
                events: {
                    click: function (event) {
                       $scope.showRecordsWithFilter('broker', event.point.brokerId,'', event.point.driverId, event.point.dispatcherId, event.point.second_driver_id);
                    }
                }
           }
        },
        series: $scope.chartSeries,
        title: { text: '' },
        exporting: { enabled: false },
        tooltip: {
            headerFormat: '{point.x}',
            pointFormat: '<b>{point.y}</b>',
            valueDecimals: 2,
            valuePrefix: '$',
            style: {fontSize: '17px'},
        },
    }

    /**
    * Top five customers records
    */
    function topFiveCustomer(){
        var tempBuffer = $cookies.getObject('_globalDropdown');
        if ( tempBuffer != undefined ) {
            if ( tempBuffer.label == '' || tempBuffer.label == 'all' || tempBuffer.label == '_iall' )  
                data = {'startDate':$scope.dateRangeSelector.startDate, 'endDate':$scope.dateRangeSelector.endDate};
            else if ( tempBuffer.label == "_idispatcher" )
                data = {'startDate':$scope.dateRangeSelector.startDate, 'endDate':$scope.dateRangeSelector.endDate, "dispatcherId" : tempBuffer.dispId};
            else
               data = {'startDate':$scope.dateRangeSelector.startDate, 'endDate':$scope.dateRangeSelector.endDate,"driverId":tempBuffer.id, "dispatcherId" : tempBuffer.dispId, 'secondDriverId' : tempBuffer.team_driver_id};  
        } else {
            data = {'startDate':$scope.dateRangeSelector.startDate, 'endDate':$scope.dateRangeSelector.endDate};
        }

        dataFactory.httpRequest(URL+'/dashboard/topFiveCustomer','POST',{},data).then(function(data){
            globalDash.showTopCustomers = true;
            if( data.paymentAmount != undefined && data.paymentAmount.length > 0 )
                globalDash.showTopCustomers = true;
            else
                globalDash.showTopCustomers = false;

            $scope.comp_name = [];
            $scope.barColors = data.colors;
            $scope.topCustomersConfig.xAxis = {categories : data.xAxis };
            $scope.comp_name = data.cName;   
            $scope.valueIds = data.valueIds;  
            $scope.topCustomersConfig.series   = [
                {"name": "TopCustomers",  "data" : data.paymentAmount, type: "column", id: 's55'}
            ];

            globalDash.activeDriversWTruck    = data.totalDrivers;
            globalDash.activeDriversDate      = data.todayDate;
            globalDash.trucksNotReporting     = data.trucksNotReporting;
            globalDash.vehiclesWithoutDriver   = data.vehiclesWithoutDriver;
            
        });         

    }
    //------------------------- multiple line graph -----------------

    $scope.configActiveVsIdle = {
        chart: {
            type: 'areaspline',

        },

        title: {
                text: ''
        },
        exporting: { enabled: false },

        tooltip: {
            shared: true,
        },
        credits: {
            enabled: false
        },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.5
            },
            series: {
                cursor:"pointer",
                events: {
                    click: function (event) {
                        if(event.point.type == "idle"){
                            $scope.showRecordsWithFilter(event.point.type,"driversInsights", "driver", "", "", "", event.point);
                        }else{
                            $scope.showRecordsWithFilter(event.point.type,"loads", "driver", "", "", "", event.point);
                        }
                        
                    }
                }
            }
        },
    }

    //------- Dashboar drag drop options----------------
    
    $scope.sortableOptions = {
    connectWith: ".sortable .row .col-md-6",
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
            
            var left = angular.element("#placeholder_left").sortable('toArray');
            var right = angular.element("#placeholder_right").sortable('toArray');
            var data ={'left':left,'right':right} ;
            dataFactory.httpRequest('dashboard/updateWidgets/','POST',{},data).then(function(data) {
            });
        },
    create: function (event, ui) {
            
        },

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
        
        alert($scope.search_vehicle);

        $cookies.remove("_globalDropdown");
        $cookies.putObject('_globalDropdown', item);    
        if(item.vid == "" ) {
            $scope.driversOnDashboard = [];
            $scope.selDrivers = [];
            $scope.showDrivers = false;
            $scope.vtype = '_iall';
            $scope.skipClick = true;
            $scope.getWeatherInfo();
            
        } else if(item.label == '_idispatcher') {
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
        } else {
            $scope.skipClick = false;
            if(item.label == '_team'){
                $scope.vtype = '_iteam';
            }else{
                $scope.vtype = '_idriver';
            }
            $scope.driversOnDashboard = [];
            $scope.selDrivers = [];
            $scope.driversOnDashboard.push(item);     
            $scope.selDrivers.push(item.vid);     
            $scope.showDrivers = true;
            $scope.getWeatherInfo();
        }
        topFiveCustomer();
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
            $cookies.remove("_globalDropdown");
            $cookies.putObject('_globalDropdown', $scope.search_vehicle1.selected);
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
        topFiveCustomer();
       
    }
    

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

        $scope.tSortableWidgetsLeft  = '1,2,3,4,5';
        $scope.tSortableWidgetsRight = '6,7,8,9,10,11';
    } else {
        $scope.tSortableWidgetsLeft  = data.widgetsOrder.left;
        $scope.tSortableWidgetsRight = data.widgetsOrder.right;
    }
    
    $scope.visibility   = data.widgetVisibility;
    $scope.user_role    = data.user_role;
    var orderArray      = $scope.tSortableWidgetsLeft.split(',');
    var listArray       = $('.widget_div .wpanel');
    
    for (var i = 0; i < orderArray.length; i++) {
        
        $('#placeholder_left').append(listArray[orderArray[i]-1]);
    }

    var orderArray1 = $scope.tSortableWidgetsRight.split(',');
    for (var i = 0; i < orderArray1.length; i++) {

        $('#placeholder_right').append(listArray[orderArray1[i]-1]);
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
        }else{
            $scope.search_vehicle1 = {};
            $scope.search_vehicle1.selected = $rootScope.driverBeingSelected;
            if($rootScope.driverBeingSelected.team_driver_id != 0 && $rootScope.driverBeingSelected.team_driver_id != ""){
                $scope.vtype = '_team';    
            }else{
                $scope.vtype = "_idriver";
            }
            $rootScope.ofFilter.did= $rootScope.driverBeingSelected.dispId;
            $rootScope.ofFilter.vid= $rootScope.driverBeingSelected.vid;
            $rootScope.ofFilter.driverId= $rootScope.driverBeingSelected.driverId;
            $rootScope.ofFilter.team_driver_id = $rootScope.driverBeingSelected.team_driver_id;
            $rootScope.ofFilter.vtype = $scope.vtype;
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
        $rootScope.ofFilter.endDate = $scope.dateRangeSelector.endDate;
        topFiveCustomer();

        dataFactory.httpRequest(URL+'/dashboard/index/'+ofDriver,'POST',{},$rootScope.ofFilter).then(function(data){
            $('a[href="#todayPickup"]').tab('show');
             $scope.printloadchart  = data.loadsChart;
            $scope.uInitial         = data.selectedDriver.avtarText;
            $scope.color            = data.selectedDriver.color;

            $rootScope.latitude     = data.latitude;
            $rootScope.longitude    = data.longitude;
            $rootScope.curAdd       = data.address;
            $scope.PrintchartStack  = data.chartStack.trecords;
            $scope.city_name        = data.weatherNotFound.name;
            $scope.country_name     = data.weatherNotFound.country;
            $scope.update_weather(data.latitude , data.longitude , data.address);

            $scope.CurVehicleId = data.vehicleID;

            $scope.vehicleList    = data.vehicleList;
            $scope.wdriver        = data.vehicleLabel;
            $scope.summary        = data.loadsChart.summary;
            $scope.todayReport    = data.todayReport;
            $scope.todayReport.totals    = data.totals;
            if($scope.user_role  == 2){
                $scope.vehicleList[0].driverName = $rootScope.languageArray.allDrivers;  //r288    
            }else{$scope.did
                $scope.vehicleList[0].driverName = $rootScope.languageArray.allGroups;  //r288    
            }
            
            $scope.liveTrucks = data.vehicleLocation.allVehicles;
            
            $scope.search_vehicle1 = {};
            /********** for getting selected driver label - r288****/
            if(ofDriver == '' || ofDriver == undefined){

                if( $cookies.getObject('_globalDropdown') ){
                    $scope.search_vehicle1.selected = $cookies.getObject('_globalDropdown');
                }else{
                    $scope.search_vehicle1.selected = {id: data.id,driverName:$rootScope.languageArray.allGroups, profile_image:'',label:'',username:data.username, latitude:'',longitude:'',vid:data.vehicleID,vehicle_address:data.vehicle_address,state:data.state, city:data.city};
                    $cookies.remove("_globalDropdown");
                    $cookies.putObject('_globalDropdown', $scope.search_vehicle1.selected);    
                }
                
            }else{    
                if($rootScope.driverBeingSelected != undefined && $rootScope.driverBeingSelected != ""){

                    $scope.driversOnDashboard.push( $rootScope.driverBeingSelected );
                    console.log($scope.driversOnDashboard);
                    $scope.selDrivers.push($rootScope.driverBeingSelected.vid);    
                    $scope.search_vehicle1.selected = $rootScope.driverBeingSelected;
                    if($rootScope.driverBeingSelected.team_driver_id != 0 || $rootScope.driverBeingSelected != ""){
                        $scope.vtype = '_team';    
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
                }

                $scope.showDrivers = true;
                $rootScope.ofDriver = '';
            }

            // -------------- Stacked Chart Update ------------------

            $rootScope.loadPerformance = data.chartStack.trecords;
            if (Object.keys($rootScope.loadPerformance).length  > 0 ) {
                $scope.haveRecords = true;            
            } else {
                $scope.haveRecords = false;
            }

            $scope.totalAllArray = data.chartStack.totals;
            $scope.typeOfData = data.chartStack.type;    


            $scope.configActiveVsIdle.xAxis  =  { categories: data.driversIdleVsActive.xaxis };
            $scope.configActiveVsIdle.series = [
                { name: 'Active', data: data.driversIdleVsActive.active }, 
                { name: 'Idle',   data: data.driversIdleVsActive.idle }
            ];

            switch($scope.typeOfData){
                case "_iall"        : $scope.fColumn = $scope.languageArray.dispatcher; $scope.skipClick = true;break;
                case "_idispatcher" : $scope.fColumn = $scope.languageArray.driver; $scope.skipClick = true;break;
                case "_iteam"       :
                case "_idriver"     : $scope.fColumn = $scope.languageArray.loadno; $scope.skipClick = false;break;
                default             : $scope.fColumn = $scope.languageArray.dispatcher;$scope.skipClick = true;break;
            }
            $scope.chartConfig.xAxis    = { categories: data.chartStack.xaxis };
            if ( $scope.typeOfData == '_iteam' || $scope.typeOfData == '_idriver') {
                $scope.chartConfig.series   = [
                    {"name": "Profit",    "data" : data.chartStack.profitAmount, type: "column", id: 's5', color : '#90ED7D'},
                    {"name": "Charges",   "data" : data.chartStack.charges,      type: "column", id: 's4', color : '#5C5C61'},
                ];
            } else {
                $scope.chartConfig.series   = [
                    {"name": "Profit",    "data" : data.chartStack.profitAmount, type: "column", id: 's5', color : '#90ED7D'},
                    {"name": "Charges",   "data" : data.chartStack.charges,      type: "column", id: 's4', color : '#5C5C61'},
                    {"name": "Goal", "data" : data.chartStack.goalsAchievement, type: 'spline', id: 's6', color : '#95CEFF'},
                ];
            }
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
    


$scope.toggleIcon = function($event,type){
    $scope.otherIcon = !$scope.otherIcon;  
    angular.element($event.currentTarget).closest(".wpanel").removeAttr('style');
    if( angular.element($event.currentTarget).closest(".wpanel").hasClass("panel-maximized")){ //code when minimized
        //angular.element("body").css("overflow","auto");
        angular.element("body").removeAttr("style");
        if(type=="today-insights"){
            angular.element($event.currentTarget).closest(".wpanel").find(".table-scroll-custom.today-insights").removeAttr('style');
        }else if(type == "leaderboard"){
            angular.element($event.currentTarget).closest(".wpanel").find(".table-scroll-custom.max-leaderboard").removeAttr('style');
        }
    }else{  //code when maximized 
        if(!angular.element($event.currentTarget).closest(".wpanel").hasClass("ui-sortable-helper")){
            angular.element("body").css("overflow","hidden");
        }
        if(type=="today-insights"){
            var setHeight = $window.innerHeight - 180;
            angular.element($event.currentTarget).closest(".wpanel").find(".scroll-wrapper.table-scroll-custom.today-insights.table-mainass.sroll-vertical").css("max-height",setHeight);
        }else if(type == "leaderboard"){
            var setHeight = $window.innerHeight - 550;
            angular.element($event.currentTarget).closest(".wpanel").find(".scroll-wrapper.table-scroll-custom.max-leaderboard.today-insights.sroll-vertical").css("max-height",setHeight);
        }
    }
}

$scope.print_xl =function(){
    var ext_head    = "";
    var ext_foot    = "";
    var if_driver   = 1;
    var tot_var     = 'Total';
    var total_row   = '<tr  style="text-transform: uppercase;font-size:12px;font-weight: bold;">';
    
    if ($scope.typeOfData == '_idriver' || $scope.typeOfData == '_iteam') {
        if_driver = 0;
    }

    if (typeof $scope.PrintchartStack[0].pickDate !== 'undefined') {
        ext_head = '<th style="padding:15px 7px;color:#363636; ">LOAD</th><th style="padding:15px 7px;color:#363636; ">PICKUP DATE</th><th style="padding:15px 7px;color:#363636; ">DELIVERY DATE</th>';
        total_row = total_row + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+tot_var+'</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td>';
        tot_var = "";
    }
    var cont = '<table cellpadding="0" cellspacing="0" style="width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:30px 0px 0px;"><thead><tr style="background:#f1f1f1;  text-transform: uppercase;font-size:12px; text-align:left;">' + ext_head;
    
    if ($scope.typeOfData === "_idispatcher") {
        cont = cont + '<th style="padding:15px 7px;color:#363636; ">Driver</th>';
        total_row = total_row + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; " >'+tot_var+'</td>';
    } else {
        cont = cont + '<th style="padding:15px 7px;color:#363636; ">DISPATCHER</th>';
        total_row = total_row + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+tot_var+'</td>';
    }

    if (if_driver != 0) {
        cont = cont + '<th style="padding:15px 7px;color:#363636; ">$-Goals</th><th style="padding:15px 7px;color:#363636; ">Mile-Goals</th>';
    }

    cont = cont + '<th style="padding:15px 7px;color:#363636; ">MILES</th><th style="padding:15px 7px;color:#363636; ">DEAD MILES</th><th style="padding:15px 7px;color:#363636; ">INVOICED</th><th style="padding:15px 7px;color:#363636; ">CHARGES</th><th style="padding:15px 7px;color:#363636; ">PROFIT</th><th style="padding:15px 7px;color:#363636; ">PROFIT %</th></tr></thead></tbody>';
    var innerContents = "";
    var $goals = $goalsProfit = mileGoals = mileGoalsProfit = miles = deadMiles = booked = charged = profit = profitPercent = 0;

    if (typeof $scope.PrintchartStack[0].pickDate !== 'undefined') {
        angular.forEach($scope.PrintchartStack, function(value, key) {
            miles       = miles + parseFloat(value.miles);
            deadMiles   = deadMiles + parseFloat(value.deadmiles);
            booked      = booked + parseFloat(value.invoice);
            charged     = charged + parseFloat(value.charges);
            profit      = profit + parseFloat(value.profit);
            profitPercent = profitPercent + parseFloat(value.ppercent);

            innerContents = innerContents + '<tr style="  text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.loadid + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.pickDate + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.DeliveryDate + '</td>';
            if ($scope.typeOfData === "_idispatcher") {
                innerContents = innerContents + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.username + '</td>';
            } else {

                var disname = "";
                if (if_driver != 0)
                    disname = value.fcolumn;
                else
                    disname = value.dispatcher;

                innerContents = innerContents + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + disname + '</td>';
            }
            var absval = "";
            if (value.plusMinusFinancialGoal > 0)
                absval = " $" + Math.abs(value.plusMinusFinancialGoal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
            else
                absval = "-$" + Math.abs(value.plusMinusFinancialGoal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
            var absvalmg = "";
            if (value.plusMinusMilesGoal > 0)
                absvalmg = " $" + Math.abs(value.plusMinusMilesGoal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
            else
                absvalmg = "-$" + Math.abs(value.plusMinusMilesGoal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
            if (if_driver != 0) {
                $goals = $goals + parseFloat(value.financialGoal);
                $goalsProfit = $goalsProfit + parseFloat(value.plusMinusFinancialGoal);
                mileGoals = mileGoals + parseFloat(value.milesGoal);
                mileGoalsProfit = mileGoalsProfit + parseFloat(value.plusMinusMilesGoal);
                innerContents = innerContents + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + value.financialGoal.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '<span style="font-size:10px;"> ' + absval + '</span></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.milesGoal.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '<span style="font-size:10px;"> ' + absvalmg + '<span></td>';
            }
            if (typeof value.ppercent == 'undefined') {
                value.ppercent = 0;
            }
            innerContents = innerContents + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.miles.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.deadmiles.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + value.invoice.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + value.charges.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + value.profit.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.ppercent + '%</td></<tr>';
        });
    } else {
        
        angular.forEach($scope.PrintchartStack, function(value, key) {
            miles       = miles + parseFloat(value.miles);
            deadMiles   = deadMiles + parseFloat(value.deadmiles);
            booked      = booked + parseFloat(value.invoice);
            charged     = charged + parseFloat(value.charges);
            profit      = profit + parseFloat(value.profit);
            profitPercent = profitPercent + parseFloat(value.ppercent);

            innerContents = innerContents + '<tr style="  text-transform: uppercase;font-size:12px; ">';
            if ($scope.typeOfData === "_idispatcher") {
                innerContents = innerContents + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.username + '</td>';
            } else {

                var disname = "";
                if (if_driver != 0)
                    disname = value.fcolumn;
                else
                    disname = value.dispatcher;

                innerContents = innerContents + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + disname + '</td>';
            }
            var absval = "";
            if (value.plusMinusFinancialGoal > 0)
                absval = " $" + Math.abs(value.plusMinusFinancialGoal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
            else
                absval = "-$" + Math.abs(value.plusMinusFinancialGoal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
            var absvalmg = "";
            if (value.plusMinusMilesGoal > 0)
                absvalmg = " $" + Math.abs(value.plusMinusMilesGoal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
            else
                absvalmg = "-$" + Math.abs(value.plusMinusMilesGoal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");

            if (if_driver != 0) {
                $goals = $goals + parseFloat(value.financialGoal);
                $goalsProfit = $goalsProfit + parseFloat(value.plusMinusFinancialGoal);
                mileGoals = mileGoals + parseFloat(value.milesGoal);
                mileGoalsProfit = mileGoalsProfit + parseFloat(value.plusMinusMilesGoal);
                innerContents = innerContents + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + value.financialGoal.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '<span style="font-size:10px;"> ' + absval + '</span></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.milesGoal.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '<span style="font-size:10px;"> ' + absvalmg + '<span></td>';
            }

            if (typeof value.ppercent == 'undefined') {
                value.ppercent = 0;
            }
            
            innerContents = innerContents + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.miles.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.deadmiles.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + value.invoice.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + value.charges.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + value.profit.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + parseFloat(value.ppercent) + '%</td></<tr>';

        });

    }

    if (if_driver != 0) {
        total_row = total_row + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + $goals.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '<span style="font-size:10px;">' + $goalsProfit.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</span></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + mileGoals.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '<span style="font-size:10px;">' + mileGoalsProfit.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</span></td>';
    }

    profitPercent = booked - charged;
    profitPercent = (profitPercent / booked) * 100;
    
    if (isNaN(profitPercent)) {
        profitPercent = 0;
    }
    
    var total_row_all = total_row + '<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + miles.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + deadMiles.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + booked.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + charged.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + profit.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + profitPercent.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + ' %</td></tr>';
    cont = cont + innerContents + total_row_all + "</tbody></table>";
    $scope.print_function(cont, 'Loads Tracking');  
}




$scope.print_todayInsights = function(){
    var out = [];
    var html = "";
    var sterm = '';
    if ($scope.selDrivers.length > 0) {
        sterm = $scope.selDrivers.join();
    }
    data = {
        'did': $scope.did,
        'vid': sterm,
        "vtype": $scope.vtype
    };

    dataFactory.httpRequest(URL + '/dashboard/getTodayReport/booked', 'POST', {}, data).then(function(data) {
        if (data.todayReport.length != 0) {
            console.log(data);
            html = html + "</br><table cellpadding='0' cellspacing='0' style='width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:30px 0px 15px;'><tr><td><h3 style='font-size:18px;margin:0px; padding:0px;'>Booked</h3></td></tr></table>";
            html = html + '<table cellpadding="0" cellspacing="0" style="width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:30px 0px 0px;"><thead>';
            html = html + '<tr style="background:#f1f1f1;  text-transform: uppercase;font-size:12px; text-align:left;">';
            html = html + '<th style="padding:15px 7px;color:#363636; ">Driver</th><th style="padding:15px 7px;color:#363636; ">truck</th><th style="padding:15px 7px;color:#363636; ">Dispatcher</th><th style="padding:15px 7px;color:#363636; ">Pickup</th><th style="padding:15px 7px;color:#363636; ">Delivery</th><th style="padding:15px 7px;color:#363636; ">Origin</th><th style="padding:15px 7px;color:#363636; ">ST</th><th style="padding:15px 7px;color:#363636; ">Destination</th><th style="padding:15px 7px;color:#363636; ">ST</th><th style="padding:15px 7px;color:#363636; ">Payment</th><th style="padding:15px 7px;color:#363636; ">RPM</th><th style="padding:15px 7px;color:#363636; ">Miles</th><th style="padding:15px 7px;color:#363636; ">Dead Miles</th><th style="padding:15px 7px;color:#363636; "><span style="min-width:250px; float:left;">Company Name</span></th></tr></thead><tbody>';
            var rPayment = rRPM = rMileage = rDeadMile = 0;
            angular.forEach(data.todayReport, function(value, key) {
                html = html + '<tr style=" text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.driverName + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.truckName + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.dispatcher + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.PickupDate + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.DeliveryDate + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.OriginCity + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.OriginState + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.DestinationCity + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.DestinationState + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + parseFloat(value.PaymentAmount).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + parseFloat(parseInt(value.RPM * 100)) / 100 + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + parseInt(value.Mileage).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + parseInt(value.deadmiles).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><span style="min-width:250px; float:left;">' + value.companyName + '</span></td><tr>';
                rPayment = rPayment + parseFloat(value.PaymentAmount);
                rMileage = rMileage + parseFloat(value.Mileage);
                rDeadMile = rDeadMile + parseFloat(value.deadmiles);
            });
            rRPM = rPayment / rMileage;
            html = html + '<tr style=" text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>Total</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>$' + rPayment.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>$' + rRPM.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>' + rMileage.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>' + rDeadMile.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><span style="min-width:250px; float:left;"></span></td><tr></tbody></table>';
        }
    });
    dataFactory.httpRequest(URL + '/dashboard/getTodayReport/inprogress', 'POST', {}, data).then(function(data) {
        if (data.todayReport.length != 0) {
            html = html + "</br></br><table cellpadding='0' cellspacing='0' style='width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:30px 0px 15px;'><tr><td><h3 style='font-size:18px;margin:0px; padding:0px;'>In Progress</h3></td></tr></table>";
            html = html + '<table cellpadding="0" cellspacing="0" style="width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:30px 0px 0px;"><thead>';
            html = html + '<tr style="background:#f1f1f1;  text-transform: uppercase;font-size:12px; text-align:left;">';
            html = html + '<th style="padding:15px 7px;color:#363636; ">Driver</th><th style="padding:15px 7px;color:#363636; ">truck</th><th style="padding:15px 7px;color:#363636; ">Dispatcher</th><th style="padding:15px 7px;color:#363636; ">Pickup</th><th style="padding:15px 7px;color:#363636; ">Delivery</th><th style="padding:15px 7px;color:#363636; ">Origin</th><th style="padding:15px 7px;color:#363636; ">ST</th><th style="padding:15px 7px;color:#363636; ">Destination</th><th style="padding:15px 7px;color:#363636; ">ST</th><th style="padding:15px 7px;color:#363636; ">Payment</th><th style="padding:15px 7px;color:#363636; ">RPM</th><th style="padding:15px 7px;color:#363636; ">Miles</th><th style="padding:15px 7px;color:#363636; ">Dead Miles</th><th style="padding:15px 7px;color:#363636; "><span style="min-width:250px; float:left;">Company Name</span></th></tr></thead><tbody>';
            var rPayment = rRPM = rMileage = rDeadMile = 0;
            angular.forEach(data.todayReport, function(value, key) {
                html = html + '<tr style="text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.driverName + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.truckName + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.dispatcher + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.PickupDate + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.DeliveryDate + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.OriginCity + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.OriginState + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.DestinationCity + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.DestinationState + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + parseFloat(value.PaymentAmount).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + parseFloat(parseInt(value.RPM * 100)) / 100 + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + parseInt(value.Mileage).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + parseInt(value.deadmiles).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><span style="min-width:250px; float:left;">' + value.companyName + '</span></td><tr>';
                rPayment = rPayment + parseFloat(value.PaymentAmount);
                rMileage = rMileage + parseFloat(value.Mileage);
                rDeadMile = rDeadMile + parseFloat(value.deadmiles);
            });
            rRPM = rPayment / rMileage;
            html = html + '<tr style=" text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>Total</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>$' + rPayment.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>$' + rRPM.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>' + rMileage.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>' + rDeadMile.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><span style="min-width:250px; float:left;"></span></td><tr></tbody></table>';
        }
    });
    dataFactory.httpRequest(URL + '/dashboard/getTodayReport/delivery', 'POST', {}, data).then(function(data) {
        if (data.todayReport.length != 0) {
            html = html + "</br></br><table cellpadding='0' cellspacing='0' style='width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:30px 0px 15px;'><tr><td><h3 style='font-size:18px;margin:0px; padding:0px;'>Deliverey</h3></td></tr></table>";
            html = html + '<table cellpadding="0" cellspacing="0" style="width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:30px 0px 0px;"><thead>';
            html = html + '<tr style="background:#f1f1f1;  text-transform: uppercase;font-size:12px; text-align:left;">';
            html = html + '<th style="padding:15px 7px;color:#363636; ">Driver</th><th style="padding:15px 7px;color:#363636; ">truck</th><th style="padding:15px 7px;color:#363636; ">Dispatcher</th><th style="padding:15px 7px;color:#363636; ">Pickup</th><th style="padding:15px 7px;color:#363636; ">Delivery</th><th style="padding:15px 7px;color:#363636; ">Origin</th><th style="padding:15px 7px;color:#363636; ">ST</th><th style="padding:15px 7px;color:#363636; ">Destination</th><th style="padding:15px 7px;color:#363636; ">ST</th><th style="padding:15px 7px;color:#363636; ">Payment</th><th style="padding:15px 7px;color:#363636; ">RPM</th><th style="padding:15px 7px;color:#363636; ">Miles</th><th style="padding:15px 7px;color:#363636; ">Dead Miles</th><th style="padding:15px 7px;color:#363636; "><span style="min-width:250px; float:left;">Company Name</span></th></tr></thead><tbody>';
            var rPayment = rRPM = rMileage = rDeadMile = 0;
            angular.forEach(data.todayReport, function(value, key) {
                html = html + '<tr style="text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.driverName + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.truckName + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.dispatcher + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.PickupDate + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.DeliveryDate + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.OriginCity + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.OriginState + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.DestinationCity + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + value.DestinationState + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + parseFloat(value.PaymentAmount).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">$' + parseFloat(parseInt(value.RPM * 100)) / 100 + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + parseInt(value.Mileage).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + parseInt(value.deadmiles).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><span style="min-width:250px; float:left;">' + value.companyName + '</span></td><tr>';
                rPayment = rPayment + parseFloat(value.PaymentAmount);
                rMileage = rMileage + parseFloat(value.Mileage);
                rDeadMile = rDeadMile + parseFloat(value.deadmiles);
            });
            rRPM = rPayment / rMileage;
            html = html + '<tr style=" text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>Total</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>$' + rPayment.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>$' + rRPM.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>' + rMileage.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><b>' + rDeadMile.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") + '</b></td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "><span style="min-width:250px; float:left;"></span></td><tr></tbody></table>';
        }
    });
    dataFactory.httpRequest(URL + '/dashboard/getTodayReport/idle', 'POST', {}, data).then(function(data) {

        if (data.todayReport.length != 0) {
            console.log(data);
            html = html + "</br></br><table cellpadding='0' cellspacing='0' style='width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:30px 0px 15px;'><tr><td><h3 style='font-size:18px;margin:0px; padding:0px;'>Idle</h3></td></tr></table>";
            html = html + '<table cellpadding="0" cellspacing="0" style="width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:30px 0px 0px;"><thead>';
            html = html + '<tr style="background:#f1f1f1;  text-transform: uppercase;font-size:12px; text-align:left;">';
            html = html + '<th style="padding:15px 7px;color:#363636; ">Driver</th><th style="padding:15px 7px;color:#363636; ">truck</th><th style="padding:15px 7px;color:#363636; ">Dispatcher</th></tr></thead><tbody>';
            angular.forEach(data.todayReport, function(value, key) {
                var driver = value.driverName;
                var truckName = value.truckName;
                var dispatcher = value.dispatcher;
               if(!driver)
                driver = 'NA';
               if(!truckName)
                truckName ='NA';
               if(!dispatcher)
                dispatcher = 'NA';
                html = html + '<tr style="text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + driver + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + truckName + '</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">' + dispatcher + '</td><tr>';

            });
            html = html + "</tbody></table>";
        }
    });
    $interval(function() {
        if (html != "") {

            $scope.print_function(html, 'Today Insights');
        }
    }, 1000, 1);  

  
}

$scope.print_weather = function(){
 var html = '<table cellpadding="0" cellspacing="0" style="width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:30px 0px 0px;"><thead>';
  html = html +'<tr style="background:#f1f1f1;  text-transform: uppercase;font-size:12px; text-align:left;">';
  html = html +'<th style="padding:15px 7px;color:#363636; ">Day</th><th style="padding:15px 7px;color:#363636; ">Date</th><th style="padding:15px 7px;color:#363636; ">Temperature</th><th style="padding:15px 7px;color:#363636; ">Wind</th><th style="padding:15px 7px;color:#363636; ">Humidity</th><th style="padding:15px 7px;color:#363636; ">Min. Temperature</th><th style="padding:15px 7px;color:#363636; ">Max. Temperature</th><th style="padding:15px 7px;color:#363636; ">Feels Like</th><tr></thead>';
  html = html +'<tbody><tr style="  text-transform: uppercase;font-size:12px; ">';
  html = html +'<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.currentWeather.today+'</td><td style="text-transform:none;padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.currentWeather.date.replace(' ,' ,',')+'</td><td  style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.currentWeather.current_temperature+' °F</td><td  style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.currentWeather.wind+' m/h</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.currentWeather.humidity+' %</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.currentWeather.main.temp_min+' °F</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.currentWeather.main.temp_max+' °F</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.currentWeather.weather_description+'</td><tr>';
  html = html +'<tr  style="  text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[0].today+'</td><td style="text-transform:none; ;padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[0].date.replace(' ,' ,',')+'</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[0].current_temperature+' °F</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[0].wind+' m/h</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[0].humidity+' %</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[0].temp.min+' °F</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[1].temp.max+' °F</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[0].weather_description+'</td><tr>';
  html = html +'<tr style="  text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[1].today+'</td><td style="text-transform:none;padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[1].date.replace(' ,' ,',')+'</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "> '+$scope.dailyForecast.list[1].current_temperature+' °F</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[1].wind+' m/h</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; "> '+$scope.dailyForecast.list[1].humidity+' %</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[1].temp.min+' °F</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[1].temp.max+' °F</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.dailyForecast.list[1].weather_description+' </td><tr></tbody>';
  html = html +"</table>";
  $scope.print_function(html , 'Weather Report');
  }

$scope.print_function = function(body , title){

    var html = '<table cellpadding="0" cellspacing="0" style="width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:40px 0px 34px;border-bottom:1px solid #b6b6b6;">';
    html = html +'<tr>';
    html = html +'<td style="width:118px;"><img src="assets/img/print_logo.png"></td>';
    html = html +'<td style="text-align:center;width:758px;"><h1 style="font-size:30px; color:#1f1f1f;padding:0px; margin:0px;">'+title+'</h1></td>';
    html = html +'<td style="width:300px; float:right;"></td>';
    html = html +'</tr>';
    html = html +'</table>';
    var mywindow = window.open('', '_blank');
    mywindow.document.write('<html><head><title></title>');
    mywindow.document.write('</head><style>thead{ display:table-row-group; } @media print { tr {page-break-inside: avoid;} </style><body >');
    mywindow.document.write(html);
    mywindow.document.write(body);

    mywindow.document.write('</body></html>');
    mywindow.document.close(); 
    mywindow.focus(); 
    mywindow.onload = function(){
        mywindow.print();
    }
    //mywindow.close(); 
    //return true; 
}

$scope.load_status = function(){


  //var data = $scope.printloadchart;

   var html = '<table cellpadding="0" cellspacing="0" style="width:1170px; margin:0px auto;padding:0px;font-family:arial;padding:30px 0px 0px;">';
  // html = html + '<thead><tr style="background:#f1f1f1;  text-transform: uppercase;font-size:12px; text-align:left;"><th style="padding:15px 7px;color:#363636; ">Assigned</th><th style="padding:15px 7px;color:#363636; ">Booked</th><th style="padding:15px 7px;color:#363636; ">Delivered</th><th style="padding:15px 7px;color:#363636; ">Inprogress</th><th style="padding:15px 7px;color:#363636; ">No-Loads</th><th style="padding:15px 7px;color:#363636; ">INVOICES</th><th style="padding:15px 7px;color:#363636; ">PAYMENT ON COLLECTION</th><th style="padding:15px 7px;color:#363636; ">Waiting Paperwork</th></thead>';
   // html = html +'<tbody><tr style="  text-transform: uppercase;font-size:12px; ">';
   // html = html +'<td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.assigned+'</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.booked+'</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.delivered+'</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.inprogress+'</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.noLoads+'</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.summary.invoiceCount+'</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.summary.sentForPaymentCount+'</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.summary.waitingPaperworkCount+'</td><tr></tbody>';

  
   html  = html + '<tr style="  text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">Assigned</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.assigned+'</td></tr>';
   html  = html + '<tr style="  text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">Booked</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.booked+'</td></tr>';
   html  = html + '<tr style="  text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">Delivered</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.delivered+'</td></tr>';
   html  = html + '<tr style="  text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">Inprogress</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.inprogress+'</td></tr>';
   html  = html + '<tr style="  text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">No-Loads</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.noLoads+'</td></tr>';
   html  = html + '<tr style="  text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">INVOICES</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.summary.invoiceCount+'</td></tr>';
   html  = html + '<tr style="  text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">PAYMENT ON COLLECTION</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.summary.sentForPaymentCount+'</td></tr>';
   html  = html + '<tr style="  text-transform: uppercase;font-size:12px; "><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">Waiting Paperwork</td><td style="padding:15px 7px;color:#363636 ;border-bottom: 1px solid #dedede; ">'+$scope.printloadchart.summary.waitingPaperworkCount+'</td></tr>';
   
   $scope.print_function(html , 'Load Status');   
          
            
}


$scope.refresh_loadStatus = function(){
angular.element('.load_portlet-progress').css('display', 'block');
       var sterm = '';
        if( $scope.selDrivers.length > 0)
            sterm = $scope.selDrivers.join();
        else
            sterm = '';

         var data = {'did':$scope.did, 'vid':sterm,"vtype":$scope.vtype,"startDate":$scope.dateRangeSelector.startDate, "endDate" : $scope.dateRangeSelector.endDate};
            dataFactory.httpRequest(URL+'/dashboard/index','POST',{},data).then(function(data){
                $scope.printloadchart = data.loadsChart;
               
            $scope.summary        = data.loadsChart.summary;
            var chartData = [];
           chartData.push({name:'Delivered',y:data.loadsChart.delivered });
           chartData.push({name:'Booked',y:data.loadsChart.booked });
           chartData.push({name:'In-progress',y:data.loadsChart.inprogress });
           chartData.push({name:'No-loads',y:data.loadsChart.noLoads });
           $scope.drawMatrix("delivery_matrix",chartData,'Loads');
             angular.element('.load_portlet-progress').css('display', 'none');
            });


}

$localStorage.mapheight = 0;
$scope.test = function(){
    $scope.otherIcon = !$scope.otherIcon;
     var map_height = "90%";
     var zoomin = 5;
     if($localStorage.mapheight == 0) {
        zoomin = 6;
        map_height = "90%";
        $localStorage.mapheight = 1;
    } else {
        zoomin =5;
        $localStorage.mapheight = 0;
    }

    $interval(function() {
      $scope.renderGoogleMap(zoomin, map_height);
    },500 , 1);
}

$scope.render_graphs = function(){
    $interval(function() {
             $scope.otherIcon = !$scope.otherIcon;
       $scope.render_graph();
    },500 , 1);
}


$scope.best_safety = function(){
    $interval(function(){
            $scope.otherIcon = !$scope.otherIcon;

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
    }, 500 , 1);

}

$scope.update_weather = function($lat , $lon , $add){

   angular.element('.myweather1_portlet-progress').css('display', 'block');
    
    var sterm = '';
    if( $scope.selDrivers.length > 0) {sterm = $scope.selDrivers.join();}
    var data = {'latitude':$lat , 'longitude':$lon , 'address':$add,'did':$scope.did, 'vid':sterm,"vtype":$scope.vtype};
    angular.element('.myweather1_portlet-progress').css('display', 'block');
    var data = {'latitude':$lat , 'longitude':$lon , 'address':$add};
    dataFactory.httpRequest(URL+'/dashboard/weather_updates','POST',{},data).then(function(dataa){
        angular.element('.myweather1_portlet-progress').css('display', 'none');
        $scope.autoFetchLoads = false;
        $scope.currentWeather = dataa.currentWeather;
        //$scope.currentWeather.weather_class = "cloudy";
        $scope.dailyForecast = dataa.dailyForecast;
        if($scope.currentWeather.weather_class != undefined){
            $("#current_weather_icon").html('<canvas id='+dataa.currentWeather.weather_class+' height="64" width="64" ></canvas>');
            var my_icon = dataa.currentWeather.weather_class.toUpperCase();
            my_icon = my_icon.split('-').join('_');
            var icons = new Skycons({"color": "black"});
            icons.set(dataa.currentWeather.weather_class , Skycons[my_icon]);
            icons.play();
        }
        if(dataa.weatherNotFound.status){
            $scope.weatherStatus = true; 
            $scope.currentWeather.name =  $scope.city_name;
            $scope.currentWeather.country = $scope.country_name;
        }
        if(true){
            $scope.currentWeather.name =  $scope.city_name;
            $scope.currentWeather.country = $scope.country_name;
            $scope.weatherShow = true;
        }
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

$rootScope.getTodayReport = function(reporttype){
    var sterm = '';
    if( $scope.selDrivers.length > 0) {sterm = $scope.selDrivers.join();}
    data = {'did':$scope.did, 'vid':sterm,"vtype":$scope.vtype};
    angular.element(".today-progress").show();
    $rootScope.todayInsightActive = reporttype;
    switch(reporttype){
        case "booked"     : $('a[href="#todayPickup"]').tab('show'); break;
        case "inprogress" : $('a[href="#todayInprogress"]').tab('show'); break;
        case "delivery"   : $('a[href="#todayDelivery"]').tab('show'); break;
        case "idle"       : $('a[href="#todayIdle"]').tab('show'); break;
        default           : $('a[href="#todayPickup"]').tab('show'); reporttype='booked' ;
    }

    dataFactory.httpRequest(URL+'/dashboard/getTodayReport/'+reporttype,'POST',{},data).then(function(data){
        angular.element(".today-progress").hide();
        if(data.success){
            $scope.todayReport = data.todayReport;    
            $scope.todayReport.totals = data.totals;    
        }else{
            $scope.todayReport = [];
        }
       // $scope.today_insight = data;
    });
}

$rootScope.exportTodayReport = function(){
    
    reporttype = ($rootScope.todayInsightActive === 'undefined' )?'booked':$rootScope.todayInsightActive;
    data = {'export':1};

    dataFactory.httpRequest(URL+'/dashboard/getTodayReport/'+reporttype,'POST',{},data).then(function(data){
            
            var url = URL+'/assets/ExportExcel/'+data.fileName;
            var downloadContainer   = angular.element('<div data-tap-disabled="true"><a></a></div>');
            var downloadLink        = angular.element(downloadContainer.children()[0]);
            downloadLink.attr('href', url);
            downloadLink.attr('download', data.filename);
            angular.element('body').append(downloadContainer);
            $timeout(function () {
              downloadLink[0].click();
              downloadLink.remove();
            }, null);

    });
}

$rootScope.exportLeaderBoard = function(item,model,type){
    
    alert(model);
    
    alert(type);

    $rootScope.ofFilter.startDate = $scope.dateRangeSelector.startDate;
    $rootScope.ofFilter.endDate = $scope.dateRangeSelector.endDate;

    /*reporttype = ($rootScope.todayInsightActive === 'undefined' )?'booked':$rootScope.todayInsightActive;
    data = {'export':1};

    dataFactory.httpRequest(URL+'/dashboard/getTodayReport/'+reporttype,'POST',{},data).then(function(data){
            
            var url = URL+'/assets/ExportExcel/'+data.fileName;
            var downloadContainer   = angular.element('<div data-tap-disabled="true"><a></a></div>');
            var downloadLink        = angular.element(downloadContainer.children()[0]);
            downloadLink.attr('href', url);
            downloadLink.attr('download', data.filename);
            angular.element('body').append(downloadContainer);
            $timeout(function () {
              downloadLink[0].click();
              downloadLink.remove();
            }, null);

    });*/
}



$scope.getWeatherInfo = function(){
    var sterm = '';
    $scope.weatherStatus = false;
    if(!$scope.driverName){ $scope.autoFetchLoads = true; }
    if( $scope.selDrivers.length > 0) { sterm = $scope.selDrivers.join(); } else{ sterm = '' ; }

    data = {'did':$scope.did, 'vid':sterm,"vtype":$scope.vtype,"startDate":$scope.dateRangeSelector.startDate, "endDate" : $scope.dateRangeSelector.endDate};
        dataFactory.httpRequest(URL+'/dashboard/index','POST',{},data).then(function(data){
            $('a[href="#todayPickup"]').tab('show');
            $scope.uInitial=data.selectedDriver.avtarText;
            $scope.color=data.selectedDriver.color;
            $scope.autoFetchLoads = false;
            $scope.currentWeather = data.currentWeather;
            $scope.dailyForecast = data.dailyForecast;
            $scope.vehicleList = data.vehicleList;
            $scope.wdriver = data.vehicleLabel;
            $scope.summary = data.loadsChart.summary;

            $scope.todayReport = data.todayReport;
            $scope.city_name = data.weatherNotFound.name;
            $scope.country_name = data.weatherNotFound.country;
            $scope.update_weather(data.latitude , data.longitude , data.address);
            $scope.todayReport.totals = data.totals;    

            if($scope.driverName){
                $scope.vehicleList[0].driverName = "All Drivers";  
                $scope.driverName = false;
            }
            $scope.liveTrucks = data.vehicleLocation.allVehicles;
            
            $scope.configActiveVsIdle.xAxis  =  { categories: data.driversIdleVsActive.xaxis };
                $scope.configActiveVsIdle.series = [
                    { name: 'Active', data: data.driversIdleVsActive.active }, 
                    { name: 'Idle',   data: data.driversIdleVsActive.idle }
                ];
              //-------------- Stacked Chart Update ------------------
              $rootScope.loadPerformance = data.chartStack.trecords;
              $scope.PrintchartStack = data.chartStack.trecords;

            if (Object.keys($rootScope.loadPerformance).length  > 0 ) {
                $scope.haveRecords = true;
            } else {
                $scope.haveRecords = false;
            }
            $scope.totalAllArray = data.chartStack.totals;
            $scope.typeOfData = data.chartStack.type;                 
            switch($scope.typeOfData){
                case "_all"         : $scope.fColumn = $scope.languageArray.dispatcher;break;
                case "_idispatcher" : $scope.fColumn = $scope.languageArray.driver;break;
                case "_iteam"       :
                case "_idriver"     : $scope.fColumn = $scope.languageArray.loadno;break;
                default             : $scope.fColumn = $scope.languageArray.dispatcher;break;
            }

              
            $scope.chartConfig.xAxis    = { categories: data.chartStack.xaxis };
            if ( $scope.typeOfData == '_iteam' || $scope.typeOfData == '_idriver') {
                $scope.chartConfig.series   = [
                {"name": "Profit",    "data" : data.chartStack.profitAmount, type: "column", id: 's5', color : '#90ED7D'},
                {"name": "Charges",   "data" : data.chartStack.charges,      type: "column", id: 's4', color : '#5C5C61'},
                ];
            } else {
                $scope.chartConfig.series   = [
                     {"name": "Profit",    "data" : data.chartStack.profitAmount, type: "column", id: 's5', color : '#90ED7D'},
                     {"name": "Charges",   "data" : data.chartStack.charges,      type: "column", id: 's4', color : '#5C5C61'},
                     {"name": "Goal", "data" : data.chartStack.goalsAchievement, type: 'spline', id: 's6', color : '#95CEFF'},
                ];
            }
          
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

    dataFactory.httpRequest(URL+'/dashboard/getRssFeeds').then(function(data){
        $scope.rssfeeds = data.feeds;
    });

   /* dataFactory.httpRequest(URL+'/dashboard/fetchActiveDriversWithoutTruck').then(function(data){
        globalDash.activeDriversWTruck = data.totalDrivers;
        globalDash.activeDriversDate   = data.todayDate; 
    });
*/
$rootScope.updateDashboard = function(){
  //  console.log($scope.dateRangeSelector);
    var sterm = '';
    if(!$scope.driverName){ $scope.autoFetchLoads = true; }
    if( $scope.selDrivers.length > 0){ sterm = $scope.selDrivers.join(); } else{ sterm = ''; }
    $rootScope.getTodayReport($rootScope.todayInsightActive);
    data = {'did':$scope.did, 'vid':sterm,"vtype":$scope.vtype,"startDate":$scope.dateRangeSelector.startDate, "endDate" : $scope.dateRangeSelector.endDate};
    dataFactory.httpRequest(URL+'/dashboard/updateDashboardOnLoadEdit','POST',{},data).then(function(data){
        $scope.autoFetchLoads = false;
        $scope.printloadchart = loadsChart;
        $scope.summary = data.loadsChart.summary;
        $rootScope.loadPerformance = data.chartStack.trecords;
       if (Object.keys($rootScope.loadPerformance).length  > 0 ) {
            $scope.haveRecords = true;
        }else{
            $scope.haveRecords = false;
        }
        $scope.totalAllArray = data.chartStack.totals;
          // -------------- Stacked Chart Update ------------------

          $scope.chartConfig.xAxis    = { categories: data.chartStack.xaxis };
             if ( $scope.typeOfData == '_iteam' || $scope.typeOfData == '_idriver') {
                $scope.chartConfig.series   = [
                    {"name": "Profit",    "data" : data.chartStack.profitAmount, type: "column", id: 's5', color : '#90ED7D'},
                    {"name": "Charges",   "data" : data.chartStack.charges,      type: "column", id: 's4', color : '#5C5C61'},
                ];
            } else {
                $scope.chartConfig.series   = [
                    {"name": "Profit",    "data" : data.chartStack.profitAmount, type: "column", id: 's5', color : '#90ED7D'},
                    {"name": "Charges",   "data" : data.chartStack.charges,      type: "column", id: 's4', color : '#5C5C61'},
                     {"name": "Goal", "data" : data.chartStack.goalsAchievement, type: 'spline', id: 's6', color : '#95CEFF'},
                ];
            }

          // $scope.chartConfig.series   = [
          // {"name": "Charges",  "data" : data.chartStack.charges,  type: "column", id: 's4'},
          // {"name": "Invoiced", "data" : data.chartStack.invoiced, type: "column", id: 's3'},
          // {"name": "Goal", "data" : data.chartStack.goalsAchievement, type: 'spline', id: 's6'},
          // {"name": "Profit",  "data" : data.chartStack.profitAmount, id: 's5'}
          // ];
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
    $scope.renderGoogleMap = function(param, map_height){

        if ( map_height == undefined || map_height == '' )
            map_height = '90%';

        var zoomoptn = 5;
        if(param != undefined && param != '')
            zoomoptn = param;

       var mapOptions = {
            zoom: zoomoptn,
            center: {lat: 37.09024, lng: -95.712891},
            scrollwheel: false, 
            scaleControl: false, 
            icon:"./pages/img/truck-stop.png"
        }    
        $scope.map = new google.maps.Map(document.getElementById('dash-map'), mapOptions);

        document.getElementById('dash-map').style.height = map_height;  
        var markers=[];
        $scope.directionsService = new google.maps.DirectionsService;
        var infowindow = new google.maps.InfoWindow();
        $scope.directionDisplay = [];
        $scope.labels = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        var i=0;
        var bounds = new google.maps.LatLngBounds();
        angular.forEach($scope.liveTrucks, function(value, key) {
           
            var position = {lat: parseFloat(value.latitude), lng: parseFloat(value.longitude)};
            var icon = "./pages/img/"+value.heading.toLowerCase()+"_live.png";
            var timestamp = "Live telemetry from tracker as of ("+value.timestamp+")";
            var msgClass = "truck-live";
            if(value.mintues_ago > 2){
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
            exporting:false,
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
    //top 5 removed
    

    
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
