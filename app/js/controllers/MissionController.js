angular.module('MetronicApp').controller('MissionController', function($rootScope, $scope, $http,settings) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    console.log("Ctrl Missions");
    $scope.data = {};
    $scope.data.hideFormMissions = false;
    $scope.data.hideListMissions = true;
    $scope.data.hideDivInfosCar = false;

    getMissions();
    getCars();



    $scope.AddNewMission = function(){
        $scope.data.hideListMissions = false;
        $scope.data.hideFormMissions = true;
        $scope.data.mission = {};
    }


    function getMissions(){
        $http.post('../serv/ws/missions.ws.php' , {action:'getAllMissions'}).then(
            function(response){
                console.log(response.data.data);
                $scope.data.missions = response.data.data;
            },
            function(error){
                console.log(error);
            }
        )
    };

    $scope.editMission = function(mission){
        console.log(mission);
        $scope.data.hideListMissions = false;
        $scope.data.hideFormMissions = true;
        $scope.data.mission = mission;
    }

    $scope.deleteMission = function(mission){
        console.log(mission);
        $http.post('../serv/ws/missions.ws.php' , {action:'deleteMission' , id : mission.id}).then(
            function(response){
                if(response.data.error === true){
                    alert(response.data.data);
                    return false;
                }

                getMissions();
            },
            function(error){
                console.log(error);
            }
        )

    }

    $scope.saveMission = function(mission){
        console.log(mission);
        $http.post('../serv/ws/missions.ws.php' , {action:'saveMission' , mission : JSON.stringify(mission)}).then(
            function(response){
                if(response.data.error === true){
                    alert(response.data.data);
                    return false;
                }
                $scope.closeMission();
                getMissions();
            },
            function(error){
                console.log(error);
            }
        )
    }

    $scope.closeMission = function(){
        $scope.data.hideFormMissions = false;
        $scope.data.hideListMissions = true;
    }

    $scope.getInfosCar = function(car){
        $scope.data.car = car;
        $scope.data.hideFormMissions = false;
        $scope.data.hideListMissions = false;
        $scope.data.hideDivInfosCar = true;
    }

    $scope.closeInfosCar = function(){
        $scope.data.hideFormMissions = false;
        $scope.data.hideListMissions = true;
        $scope.data.hideDivInfosCar = false;   
    }

    function getCars(){
        $http.post('../serv/ws/cars.ws.php',{action:'getAllCars'}).then(
            function(response){
                console.log(response.data.data);
                $scope.data.cars = response.data.data;
            },
            function(error){
                console.log(error);
            }
        )
    };

    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;

    
});