'use strict';

/* Controllers */

angular.module('app')

.controller('SearchCtrl', ['$scope','dataFactory','$rootScope', function($scope,dataFactory,$rootScope) {
	$rootScope.searchResult = [];
	$rootScope.search = {};
    $scope.liveSearch = function() {
		
		if ( $rootScope.search.query.length > 0 ) {
			$rootScope.shownewSearch = false;
			$rootScope.toggleSearchTitle = $rootScope.languageCommonVariables.triggerLoadSearch;
		} 
        dataFactory.httpRequest(URL+'/states/search_links/','POST',{},{ searchResult: $rootScope.search.query}).then(function(data) {
			if ( data.result.length > 0 ) {
				$rootScope.searchResults = data.result;
			} else {
				$rootScope.searchResults = [];
			}
		});
    }
}]);
