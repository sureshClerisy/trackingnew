'use strict';

/**
 * @ngdoc function
 * @name ForgotPassword
 * @description
 * # ForgotPassword
 * Controller of the app
 */
 app.controller('resetPassword', function (forgotService,$state,getbase64Data,$rootScope) {

    var reset = this;
    reset.errorMessage = '';
    $rootScope.showBackground = true;

    reset.userID = getbase64Data.data;

    reset.hideMessage = function() {
        reset.errorMessage = '';
    }

    reset.resetPassword = function() {
        
        if(reset.password != reset.confPassword){
            reset.Message = 'Error! : Password does not matched.';
            reset.invalidUsermsg = true;
        }else{
            forgotService.resetPassword(reset.password,reset.userID)
            .then(function (response) {
                if (response.status === true) {
                    
                    $state.go('login', { type: 'success', 'message': 'New password has been generated successfully.'});
                } else {
                    reset.invalidUsermsg = true
                    reset.Message = 'Error! : Reset password link expired.'
                }
            });
        }
    }



    reset.setForgotPassword = function () {
        forgotService.forgotPassword(frg.email)
        .then(function (response) {
            if (response.status === 'success') {
                //$state.go('login', { type: 'success', 'message': 'New password has been generated and sent to your email address.'});
            } else {
                reset.errorMessage = 'Error! : This email address does not exist. Please try another one.'
            }
        });
    };
});
