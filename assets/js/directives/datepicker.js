/* ============================================================
 * Directive: datepicker
 * AngularJS directive for datepicker jQuery plugin
 * ============================================================ */
 
 
angular.module('app')
	.directive("datepicker", function () {
	  return {
		restrict: "A",
		require: "ngModel",
		 scope: {
      	date: '=ngModelCtrl'
		},
	    replace:false,
		link: function (scope, elem, attrs, ngModelCtrl) {
		  var updateModel = function (dateText) {
			scope.$apply(function () {
				
				ngModelCtrl.$setViewValue(dateText);
		 
			});
			};
		  
		  elem.datepicker({
			format: "yyyy-mm-dd",
			
			onSelect: function (dateText) {
				updateModel(dateText);
			    scope.date = dateText;
			    scope.$apply();
			}
		  });
		}
	}
});
	angular.module('app')
	.directive("datepicker1", function () {
	  return {
		restrict: "A",
		require: "ngModel",
		 scope: {
      	date: '=ngModelCtrl'
		},
	    replace:false,
		link: function (scope, elem, attrs, ngModelCtrl) {
		  var updateModel = function (dateText) {
			scope.$apply(function () {
				
				ngModelCtrl.$setViewValue(dateText);
		 
			});
			};
		  
		  elem.datepicker({
			format: "yyyy-mm-dd",
			
			onSelect: function (dateText) {
				updateModel(dateText);
			    scope.date = dateText;
			    scope.$apply();
			}
		  });
		}
	}
});
