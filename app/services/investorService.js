'use strict';

/**
 * @ngdoc service
 * @name app.investorService
 * @description
 * # investorService
 * Service in the app.
 */
angular.module('app')
    .service('investorService', function ($http, EnvironmentConfig, $httpParamSerializerJQLike) {
        function getDataComplete(response) {
            return response.data;
        }

        function getDataFailed(error) {
            return error.data;
        }

        /**
         * fetch vehile listing
         **/
        function fetchVehiclesList() {
             return $http({
                    url: EnvironmentConfig.api + 'investors/fetchVehiclesList',
                    method: 'POST',
                    //data: $httpParamSerializerJQLike(shipperData)
                })
                .then(getDataComplete)
                .catch(getDataFailed);
        }

        /**************************
         *   fetch portlets data  *
         **************************/
        function getPortletsData(filters) {
             return $http({
                    url: EnvironmentConfig.api + 'investors/getPortletsData',
                    method: 'POST',
                    data: $httpParamSerializerJQLike(filters)
                })
                .then(getDataComplete)
                .catch(getDataFailed);
        }

       

        return {
            fetchVehiclesList : fetchVehiclesList,
            getPortletsData   : getPortletsData
        };
    });
