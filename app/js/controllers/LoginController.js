angular.module('MetronicApp').controller('LoginController', function($rootScope, $scope, $window) {
    /*$scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initComponents(); // init core components
        //App.initAjax();
    });

    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = false;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;*/

    $scope.connect = function(){
    	//$state.go('dashboard');
    	$window.location.href = './#/dashboard.html';
    };
});