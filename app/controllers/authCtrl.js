//~ URL = 'http://192.168.1.178/trackingnew';
URL = window.location.protocol+'//'+window.location.host+'/trackingnew';
app.controller('authCtrl', function (dataFactory,$scope, PubNub, $rootScope, $location, $http , $cookies, $state, $localStorage) {
    $scope.login = {};
    $scope.signup = {};
    $scope.logmsg = $rootScope.logoutmessage;
    
    $scope.closeLoginError = function(){
		$rootScope.logoutmessage = false;
	}
    $scope.closeInvalidLogin = function(){
		$scope.invalidUsermsg = false;
		$scope.logmsg = false;
	}
    if($rootScope.loggedInUser == true)
	{
		$rootScope.loggedInUser = true;
		$rootScope.showBackground = false;
		$rootScope.loggedUserFirstName = $cookies.get('loggedUserFirstNameCookie');
		$rootScope.loggedUserRoleId = $cookies.get('loggedUserRoleId');
		
		$rootScope.LastName = $cookies.get('LastName');
		$rootScope.color 	= $cookies.get('color');

		$location.path('dashboard');
		
	} else {
		$rootScope.showHeader = false;
		$rootScope.showBackground = true;
		$rootScope.loggedInUser = false;
		$rootScope.loggedFirstUserName = '';
		
	}
    $scope.doLogin = function(){
		$scope.invalidUsermsg = false;
		dataFactory.httpRequest(URL + '/login/index','POST',{},$scope.login).then(function(data) {
			if ( data.success == true ) {
				
				$scope.showLogin=false;
				if(data.loggedUser_id != undefined)
				{	
					$rootScope.profileImage = data.profile_img.profile_image;
					$rootScope.loggedInUser = true;
					$rootScope.activeUser = data.loggedUser_id;
					$rootScope.loggedUserFirstName 	= data.loggedUser_fname;
					$rootScope.LastName 	= data.LastName;
					$rootScope.color 		= data.color;
					
					$cookies.put('LastName', $rootScope.LastName);
					$cookies.put('color', $rootScope.color);					
					$cookies.put('loggedUserFirstNameCookie', data.loggedUser_fname);
					$cookies.put('loggedUserId', data.loggedUser_id);
					$cookies.put('loggedUserRoleId', data.loggedUserRole_id);
					$cookies.put('profileImage', data.profile_img.profile_image);
					$cookies.put('userIsLoggedIn', 1);
					$rootScope.activeUser = data.loggedUser_id;
					
					if ( data.loggedUserRole_id == 8 ) {
						$rootScope.organisationsList = data.organisations;
						$rootScope.organisationSelected = data.selectedOrgName;
						$rootScope.globalSelectedOrganisationId = data.selectedId;
						$rootScope.showOrganisationsDropdown = true;
					} else {
						$rootScope.organisationSelected = '';
						$rootScope.globalSelectedOrganisationId = '';
						$rootScope.showOrganisationsDropdown = false;
					}		

				} else {
					$rootScope.loggedInUser = false;
				}

				$rootScope.showHeader = true;
				$rootScope.showBackground = false;

				if ( data.loggedUserRole_id == 7 ) {
					$location.path('investor');
					$rootScope.showInvestorSidebar = true;
				}
				else {
					$location.path('dashboard');
					$rootScope.showInvestorSidebar = false;
				}

			} else {
				$scope.showLogin=true;
				$scope.Message = $rootScope.languageCommonVariables.InvalidUsernamePassword;
				$scope.invalidUsermsg = true;
				$location.path('signup');
			}
		});
	}
    
});

app.controller('authLogoutCtrl', function (dataFactory,$scope, $rootScope, $location, $http , $cookies, $state,getLogoutUserData) {
	
	if (getLogoutUserData.success == true) {
		$cookies.remove("admin_email");
		$cookies.remove("admin_uid");
		$cookies.remove('loggedUserFirstNameCookie');	
		$cookies.remove('LastName');	
		$cookies.remove('color');	
		$cookies.remove('loggedUserRoleId');	
		$cookies.remove('userIsLoggedIn');	
		$rootScope.loggedInUser = false;		
		$location.path('login');
	}
	
});
    //initially set those objects to null to avoid undefined error
