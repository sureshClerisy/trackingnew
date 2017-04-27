'use strict';

/**
 * @ngdoc service
 * @name app.shipperService
 * @description
 * # shipperService
 * Service in the app.
 */
angular.module('app')
    .service('shipperService', function ($http, EnvironmentConfig, $httpParamSerializerJQLike) {
        function getDataComplete(response) {
            return response.data;
        }

        function getDataFailed(error) {
            return error.data;
        }

        /*
         * fetch shippers list 
         */
        function fetchShippersList(shipperData) {
             return $http({
                    url: EnvironmentConfig.api + 'shippers/index',
                    method: 'POST',
                    data: $httpParamSerializerJQLike(shipperData)
                })
                .then(getDataComplete)
                .catch(getDataFailed);
        }

        /*
         * delete shipper
         */
        function deleteShipper(shipperId) {
            return $http({
                    url: EnvironmentConfig.api + 'shippers/delete/'+shipperId,
                    method: 'POST',
                    // data: $httpParamSerializerJQLike(data)
                })
                .then(getDataComplete)
                .catch(getDataFailed);
        }

        /*
         * add new shipper
         */
        function addShipperData(shipperData, srcpage) {
            return $http({
                url: EnvironmentConfig.api + 'shippers/addShipper',
                method: 'POST',
                data: $httpParamSerializerJQLike({data : shipperData, srcPage : srcpage })
            })
            .then(getDataComplete)
            .catch(getDataFailed);
        }

        /**
        * fetch list of states
        */

        function fetchStatesList() {
            return $http({
                url: EnvironmentConfig.api + 'shippers/skipAcl_fetchUsStates',
                method: 'POST',
            })
            .then(getDataComplete)
            .catch(getDataFailed);
        }

        /**
        * changing shipper status
        */

        function changeShipperStatus(shipperId, status) {
             return $http({
                url: EnvironmentConfig.api + 'shippers/changeStatus/'+shipperId+'/'+status,
                method: 'GET',
            })
            .then(getDataComplete)
            .catch(getDataFailed);
        }

        return {
            fetchShippersList: fetchShippersList,
            deleteShipper   : deleteShipper,
            addShipperData  : addShipperData,
            fetchStatesList  : fetchStatesList,
            changeShipperStatus: changeShipperStatus
        };
    });
