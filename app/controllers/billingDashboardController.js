app.controller('billingDashboardController', ["dataFactory","$scope",  "$rootScope","$cookies", "billingStats", "$state", '$timeout',function(dataFactory,$scope,$rootScope ,  $cookies,  billingStats , $state, $timeout){
    var vm = this;
	vm.sentTodayCount        = billingStats.sentToTriumphToday;
	vm.expectedBilling       = billingStats.expectedBilling;
    vm.thisWeek              = billingStats.thisWeek;
    vm.lastWeek              = billingStats.lastWeek;
    vm.recentTransactions    = billingStats.recentTransactions ;
    vm.expectedBillingToday  = billingStats.expectedBillingToday ;
    var pieChartSeries = [
                {name:'Delivered',y:billingStats.pieChart.delivered },
                {name:'Waiting Paperwork',y:billingStats.pieChart.waitingPaperwork },
            ];

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
                showInLegend: true,
                events: {
                    click: function (event) {
                        if(event.point.name.toLowerCase() == "delivered"){
                            vm.goForDetails(event.point.name.toLowerCase(),'loads');
                        }else{
                            vm.goForDetails('waiting-paperwork','billings');
                        }
                    }
                }
            }
        },
        exporting: { enabled: false },
    }

    vm.JobStatusPieConfig.series =  [{ name:"Jobs",  data: pieChartSeries }];

    vm.goForDetails = function (type, page, entity){
        console.log("dddd");
        var args = "";
        var keyParam = "all";
        if(angular.isObject(entity)){
            var d = new Date(entity.date);
            d.setDate(d.getDate()-1);
            args = "filterType="+type+"&userType=all&requestFrom=billings&deliveryDate="+moment(d).format("YYYY-MM-DD"); 

        }else if(type == "last_week_sale" || type == "this_week_sale" || type =="sent_today_expected"){
            args = "filterType="+type+"&userType=all&requestFrom=billings";
        }else{
            args = "filterType="+type+"&userType=all&requestFrom=billings"; 
            //$state.go(page, { 'key': keyParam, q:args, type:true }, { reload: true } );    
        }
        console.log(args);
        $state.go(page, { 'key': keyParam, q:args, type:true }, { reload: true } );
    }

	vm.refreshTest = function(portlet,src) {
        // Timeout to simulate AJAX response delay
        dataFactory.httpRequest(URL+'/billings/getSpecificStat/','POST',{},{type:src}).then(function(data){
            $timeout(function() {
                switch(src){
                    case "sent_today"             : vm.sentTodayCount        = data.sentToTriumphToday    ; 
                                                    vm.expectedBillingToday  = data.expectedBillingToday  ; break; 
                    case "expected_billing"       : vm.expectedBilling       = data.expectedBilling       ; break;
                    case "last_week_sale"         : vm.lastWeek              = data                       ; break;
                    case "week_till_today_sale"   : vm.thisWeek              = data                       ; break;
                    case "recent_transactions"    : vm.recentTransactions    = data.recentTransactions    ; break;
                }
                $(portlet).portlet({ refresh: false });
            }, 500);
        });
    }



    

}]);
