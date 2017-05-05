'use strict';

/**
 * @ngdoc service
 * @name app.rolesservice
 * @description
 * # rolesservice
 * Service in the app.
 */
angular.module('app')
    .service('rolesService', function ($http, EnvironmentConfig) {
    // AngularJS will instantiate a singleton by calling "new" on this function
    function getDataComplete(response) {
        return response.data;
    }

    function getDataFailed(error) {
        return error.data;
    }

    /**
    * get roles listing for mangement
    */

    function manageRoles(roleId) {
      return $http({
                url: EnvironmentConfig.api + 'roles/manage_roles/'+roleId,
                method: 'GET',
            })
            .then(getDataComplete)
            .catch(getDataFailed);
     }
   
   /**
   * changing permissions of roles
   */

   function changeManyPermissions(value, actions,roleId) {
        return $http({
                url: EnvironmentConfig.api + 'roles/skipAcl_change_permission/parent/'+roleId+'/'+value,
                method: 'POST',
                data : actions,
            })
            .then(getDataComplete)
            .catch(getDataFailed);
   }

   /**
   * changing permissions of roles
   */

   function changePermission(value, roleId, postData) {
        return $http({
                url: EnvironmentConfig.api + 'roles/skipAcl_change_permission/child/'+roleId+'/'+value,
                method: 'POST',
                data: postData
            })
            .then(getDataComplete)
            .catch(getDataFailed);
    }

    return {
        manageRoles: manageRoles,
        changeManyPermissions: changeManyPermissions,
        changePermission : changePermission
       
     };
  });
