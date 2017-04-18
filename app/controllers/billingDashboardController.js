app.controller('billingDashboardController', ["dataFactory","$scope",  "$rootScope","$cookies", "billingStats", "$state", '$timeout',function(dataFactory,$scope,$rootScope ,  $cookies,  billingStats , $state, $timeout){
    var vm = this;
	vm.sentTodayCount        = billingStats.sentToTriumphToday;
	//vm.expectedBilling       = billingStats.expectedBilling;
    vm.thisWeek              = billingStats.thisWeek;
    vm.lastWeek              = billingStats.lastWeek;
    vm.recentTransactions    = billingStats.recentTransactions ;
    vm.expectedBillingToday  = billingStats.expectedBillingToday ;
    vm.hasPieChartData       = billingStats.pieChart.hasValue;
    var pieChartSeries = [
                {name:'Delivered',         y:billingStats.pieChart.delivered        , color: "#6D5CAE" },
                {name:'Booked',            y:billingStats.pieChart.booked           , color: "#34D6C7"},
                {name:'Inprogress',        y:billingStats.pieChart.inprogress       , color: "#077ED0"},
                {name:'Waiting Paperwork', y:billingStats.pieChart.waitingPaperwork , color: "#626262" },
            ];
    vm.saleCaption = "Last Week Sales";

    Highcharts.setOptions({
        lang: {
            thousandsSep:","
        }
    });    

    vm.JobStatusPieConfig = {
        chart: { 
                type: 'pie', 
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false, 
                margin: [0, 0, 0, 0],
                spacingLeft: 0,
                spacingRight: 0
            }, 

        title: {
            text:""
        } ,
        plotOptions: {
          pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: false
                },
                size:'80%',
                showInLegend: true,
                events: {
                    click: function (event) {
                        if(event.point.name.toLowerCase() == "waiting paperwork"){
                            vm.goForDetails('waiting-paperwork','billings');
                        }else{
                            vm.goForDetails(event.point.name.toLowerCase(),'loads');
                        }
                    }
                }
            }
        },
        exporting: { enabled: false },
    }

    vm.JobStatusPieConfig.series =  [{ name:"Jobs",  data: pieChartSeries }];

    vm.dateRangeSelector = {startDate:null, endDate: null};
    if( $cookies.getObject('_gDateRangeBillDash') ){
        vm.dateRangeSelector = $cookies.getObject('_gDateRangeBillDash');
    }
    
    vm.opts = {
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

                if ( vm.dateRangeSelector.startDate != null && vm.dateRangeSelector != undefined && Object.keys(vm.dateRangeSelector).length > 0 ) {
                    vm.dateRangeSelector.startDate = vm.dateRangeSelector.startDate.format('YYYY-MM-DD');
                    vm.dateRangeSelector.endDate = vm.dateRangeSelector.endDate.format('YYYY-MM-DD');
                    $cookies.putObject('_gDateRangeBillDash', vm.dateRangeSelector);  
                    vm.updateBillingStats();
                } else {
                    vm.dateRangeSelector = {startDate: null, endDate: null};
                }
            },
            'cancel.daterangepicker': function(ev, picker) {  
                vm.updateBillingStats("clear");
                vm.dateRangeSelector = {startDate: null, endDate: null};    
                $cookies.putObject('_gDateRangeBillDash', vm.dateRangeSelector);  
            }
        },
    };


    $scope.showPaymentSidebarLiSelected = 'billingDashboard';
    $rootScope.readyToSendPaymentCount  = (billingStats.sentPaymentData != undefined ) ? billingStats.sentPaymentData.loadsForPaymentCount : 0;
    $rootScope.factoredPaymentCount     = (billingStats.sentPaymentData != undefined ) ? billingStats.sentPaymentData.factoredPaymentCount : 0;
    $rootScope.sentPaymentCount         = (billingStats.sentPaymentData != undefined ) ? billingStats.sentPaymentData.sentPaymentCount : 0;


    vm.goForDetails = function (type, page, entity){
        var args = "";
        var keyParam = "all";
        if(angular.isObject(entity)){
            args = "filterType="+type+"&userType=all&requestFrom=billings&dateFrom="+entity.fromDate+"&dateTo="+entity.toDate; 
        }else if(type == "last_week_sale" || type == "this_week_sale" || type =="sent_today_expected"){
            args = "filterType="+type+"&userType=all&requestFrom=billings";
        }else{
            args = "filterType="+type+"&userType=all&requestFrom=billings"; 
            //$state.go(page, { 'key': keyParam, q:args, type:true }, { reload: true } );    
        }
        if(type == "waiting-paperwork" || type =="inprogress" || type == "delivered" || type == "booked"){
            if(vm.dateRangeSelector.startDate != null && vm.dateRangeSelector.endDate != null){
                args += "&dateFrom="+vm.dateRangeSelector.startDate +"&dateTo="+vm.dateRangeSelector.endDate;     
            }
        }

        $state.go(page, { 'key': keyParam, q:args, type:true }, { reload: true } );
    }

	vm.refreshTest = function(portlet,src) {
        // Timeout to simulate AJAX response delay
        dataFactory.httpRequest(URL+'/billings/getSpecificStat/','POST',{},{type:src,dates:vm.dateRangeSelector}).then(function(data){
            $timeout(function() {
                switch(src){
                    case "sent_today"             : vm.sentTodayCount        = data.sentToTriumphToday    ; 
                                                    vm.expectedBillingToday  = data.expectedBillingToday  ; break; 
                    //case "expected_billing"       : vm.expectedBilling       = data.expectedBilling       ; break;
                    case "last_week_sale"         : vm.lastWeek              = data                       ; break;
                    case "week_till_today_sale"   : vm.thisWeek              = data                       ; break;
                    case "recent_transactions"    : vm.recentTransactions    = data.recentTransactions    ; break;
                    case "job_status"             : pieChartSeries = [
                                                                        {name:'Delivered',         y:data.delivered        , color: "#6D5CAE" },
                                                                        {name:'Booked'   ,         y:data.booked           , color: "#077ED0"},
                                                                        {name:'Inprogress',        y:data.inprogress       , color: "#077ED0"},
                                                                        {name:'Waiting Paperwork', y:data.waitingPaperwork , color: "#626262" },
                                                                    ];
                                                    vm.hasPieChartData           =  data.hasValue;
                                                    vm.JobStatusPieConfig.series =  [{ name:"Jobs",  data: pieChartSeries }];  break;
                }
                $(portlet).portlet({ refresh: false });
            }, 500);
        });
    }


    vm.updateBillingStats = function(key) {
        var method = "updateBillingStats";
        if(key == "clear"){
            method = "billingStats";
        }

        dataFactory.httpRequest(URL+'/billings/'+ method +'/','POST',{},vm.dateRangeSelector).then(function(data){
            if(key == "clear"){
                vm.saleCaption = "Last Week Sales";
                vm.sentTodayCount        = data.sentToTriumphToday;
                //vm.expectedBilling       = data.expectedBilling;
                vm.thisWeek              = data.thisWeek;
                vm.expectedBillingToday  = data.expectedBillingToday ;
            }else{
                vm.saleCaption = "Sales";
            }

            vm.lastWeek             = data.lastWeek;
            vm.recentTransactions   = data.recentTransactions ;
            vm.hasPieChartData      =  data.pieChart.hasValue;
            pieChartSeries = [
                        {name:'Delivered',        y:data.pieChart.delivered        , color: "#6D5CAE" },
                        {name:'Booked',           y:data.pieChart.booked           , color: "#077ED0"},
                        {name:'Inprogress',       y:data.pieChart.inprogress       , color: "#077ED0"},
                        {name:'Waiting Paperwork',y:data.pieChart.waitingPaperwork , color: "#626262" },
                    ];
            vm.JobStatusPieConfig.series =  [{ name:"Jobs",  data: pieChartSeries }]; 
        });
    }

    vm.exportTransmissions = function(){
        dataFactory.httpRequest(URL+'/billings/billingStats/','POST',{},{export:1}).then(function(data){
            $rootScope.donwloadExcelFile(data.fileName);
        });
    }
}]);
