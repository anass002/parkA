angular.module('MetronicApp').controller('DashboardController', function($rootScope, $scope, $http, $timeout, UserModel, jwtHelper) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    $scope.data = {};
    $scope.data.hideDivTableUser = true;
    $scope.data.hideDivInfosUser = false;

    $http.post('../serv/ws/users.ws.php' , {action:'getAllUsers'}).then(
        function(response){
            $scope.data.users = response.data.data;
            console.log(response);
        },
        function(error){
            console.log(err);
        }
    )

    $scope.editUser = function(user){
        console.log(user);
        $scope.data.hideDivInfosUser = true;
        $scope.data.hideDivTableUser = false;
        $scope.data.user = user;
    }
    $scope.deleteUser = function(user){
        console.log(user);
    }




    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;

    var tokenPayload = jwtHelper.decodeToken(window.localStorage['authToken']);
        console.log(tokenPayload);
});