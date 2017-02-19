angular.module('MetronicApp').controller('DashboardController', function($rootScope, $scope, $http, $timeout, UserModel, jwtHelper) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();
    });

    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;

    var a = UserModel.getInfo(window.localStorage['authToken'] );

    var tokenPayload = jwtHelper.decodeToken(window.localStorage['authToken']);
                console.log(tokenPayload);
});