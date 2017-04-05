app.controller('billingDashboardController', ["dataFactory","$scope","$rootScope","$cookies", "billingStats",'$timeout',function(dataFactory,$scope,$rootScope ,  $cookies,  billingStats , $timeout){
    var vm = this;
	vm.sentTodayCount  = billingStats.sentToTriumphToday;
	vm.expectedBilling = billingStats.expectedBilling;

	$scope.refreshTest = function(portlet,src) {
        console.log("Refreshing...");
        // Timeout to simulate AJAX response delay
        dataFactory.httpRequest(URL+'/billings/getSpecificStat/','POST',{},{type:src}).then(function(data){
            $timeout(function() {
                switch(src){
                    case "sent_today"       : vm.sentTodayCount  = data.sentToTriumphToday; break;
                    case "expected_billing" : vm.expectedBilling = data.expectedBilling   ; break;
                }
                $(portlet).portlet({ refresh: false });
            }, 500);
        });
    }
}]);
