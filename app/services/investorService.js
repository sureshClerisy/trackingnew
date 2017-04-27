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
                    url   : EnvironmentConfig.api + 'investors/skipAcl_fetchVehiclesList',
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
                    url   : EnvironmentConfig.api + 'investors/skipAcl_getPortletsData',
                    method: 'POST',
                    data  : $httpParamSerializerJQLike(filters)
                })
                .then(getDataComplete)
                .catch(getDataFailed);
        }


        /*****************************
         *   Refresh portlets data   *
         *****************************/
        function refreshPortlets(portlet, params) {
             return $http({
                    url   : EnvironmentConfig.api + 'investors/getSpecificPortletData/'+portlet,
                    method: 'POST',
                    data  : $httpParamSerializerJQLike(params)
                })
                .then(getDataComplete)
                .catch(getDataFailed);
        }


        /****************************
         *       Get RSS Feeds      *
         ****************************/
        function getRssFeeds() {
             return $http({
                    url   : EnvironmentConfig.api + 'investors/skipAcl_getRssFeeds/',
                    method: 'POST',
                    //data  : $httpParamSerializerJQLike(params)
                })
                .then(getDataComplete)
                .catch(getDataFailed);
        }


        /****************************
         *     Get vehicles jobs    *
         ****************************/
        function getVehiclesJobs() {
             return $http({
                    url   : EnvironmentConfig.api + 'investors/getVehiclesJobs/',
                    method: 'POST',
                    //data  : $httpParamSerializerJQLike(params)
                })
                .then(getDataComplete)
                .catch(getDataFailed);
        }



       

        return {
            fetchVehiclesList : fetchVehiclesList,
            getPortletsData   : getPortletsData,
            refreshPortlets   : refreshPortlets,
            getRssFeeds       : getRssFeeds,
            getVehiclesJobs   : getVehiclesJobs
        };
    });
