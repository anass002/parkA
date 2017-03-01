angular.module('MetronicApp').controller('DashboardController', function($rootScope, $scope, $http, $timeout, UserModel, jwtHelper) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    $scope.data = {};
    



    $http.post('../serv/ws/cars.ws.php' , {action: 'getAllCars'}).then(
        function(response){
            $scope.data.nbrTotalCars = response.data.data.length;
        },
        function(error){
            console.log(error);
        }
    )

    $http.post('../serv/ws/cars.ws.php' , {action: 'getNotAssignedCars'}).then(
        function(response){
            $scope.data.nbrTotalNotAssignedCars = response.data.data.length;
        },
        function(error){
            console.log(error);
        }
    )

    $http.post('../serv/ws/cars.ws.php' , {action: 'getAssignedCars'}).then(
        function(response){
            $scope.data.nbrTotalAssignedCars = response.data.data.length;
        },
        function(error){
            console.log(error);
        }
    )

    $http.post('../serv/ws/missions.ws.php' , {action: 'getAllMissions'}).then(
        function(response){
            $scope.data.nbrTotalMissions = response.data.data.length;
        },
        function(error){
            console.log(error);
        }
    )


    $http.post('../serv/ws/missions.ws.php' , {action: 'getNextMissions'}).then(
        function(response){
            $scope.data.nbrTotalNextMissions = response.data.data.length;
        },
        function(error){
            console.log(error);
        }
    )




    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;

    
});