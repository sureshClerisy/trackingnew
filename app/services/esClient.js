'use strict';

/**
 * @ngdoc service
 * @name app.esClient
 * @description
 * # esClient
 * Service in the app.
 */
angular.module('app').service('esClient', function (esFactory) {
  return esFactory({
    host: '34.201.145.12:9200',
    apiVersion: '5.0',
    //log: 'trace'
  });
});
    
