'use strict';

/**
 * @ngdoc service
 * @name app.forgotService
 * @description
 * # forgotService
 * Service in the app.
 */
angular.module('app')
    .service('forgotService', function (EnvironmentConfig, $http) {
        function getDataComplete(response) {
            return response.data;
        }

        function getDataFailed(error) {
            return error.data;
        }


        /*
         *  Forgot Process
         */
        function forgotPassword(email) {console.log(email);

            return $http({
                    url: EnvironmentConfig.api + 'login/forgotPassword',
                    method: 'POST',
                    data: { email : email }
                })
                .then(getDataComplete)
                .catch(getDataFailed);
        }

        /*
         *  Forgot Process
         */
        function resetPassword(password,token) {
            
            return $http({
                    url: EnvironmentConfig.api + 'login/resetPassword',
                    method: 'POST',
                    data: { token : token, password : password }
                })
                .then(getDataComplete)
                .catch(getDataFailed);
        }
        return {
            forgotPassword: forgotPassword,
            resetPassword: resetPassword
        };
    });
