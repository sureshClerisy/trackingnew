'use strict';

/**
 * @ngdoc function
 * @name ForgotPassword
 * @description
 * # ForgotPassword
 * Controller of the app
 */
app.controller('forgotPassword', function (forgotService,$state, $rootScope) {
    var frg = this;
    frg.errorMessage = '';
    $rootScope.showBackground = true;

    frg.hideMessage = function() {
        frg.errorMessage = '';
    }

    frg.setForgotPassword = function () {
        forgotService.forgotPassword(frg.email)
        .then(function (response) {
            if (response.status === 'success') {
                $state.go('login', { type: 'success', 'message': $rootScope.languageCommonVariables.EmailedPassword });
            } else {
                frg.errorMessage = $rootScope.languageCommonVariables.EmailNotRegistered ; 
            }
        });
    };
});
