angular.module('MetronicApp').controller('MissionController', function($rootScope, $scope, $http,settings,jwtHelper) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();

        if(window.innerWidth < 992){
            $(".page-sidebar").removeClass("in");
        }
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    console.log("Ctrl Missions");
    $scope.data = {};
    $scope.data.hideFormMissions = false;
    $scope.data.hideListMissions = true;
    $scope.data.hideDivInfosCar = false;
    $scope.data.newMissionAdded = false;
    $scope.data.errorAddNewMission = false;
    var tokenPayload = jwtHelper.decodeToken(window.localStorage['authToken']);
    $scope.data.access = true;
    if(tokenPayload.type == 'user' && !tokenPayload.droits.missions){
        $scope.data.access = false;
    }

    getMissions();
    getCars();



    $scope.AddNewMission = function(){
        $scope.data.hideListMissions = false;
        $scope.data.hideFormMissions = true;
        $scope.data.newMissionAdded = false;
        $scope.data.errorAddNewMission = false;
        $scope.data.mission = {};
        $scope.data.errorForm = {};

        window.scrollTo(0,document.body.scrollHeight);
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
        $scope.data.newMissionAdded = false;
        $scope.data.errorAddNewMission = false;
        $scope.data.hideListMissions = false;
        $scope.data.hideFormMissions = true;
        $scope.data.mission = mission;
        $scope.data.errorForm = {};
    }

    $scope.deleteMission = function(mission){
        $scope.data.newMissionAdded = false;
        $scope.data.errorAddNewMission = false;
        if(window.confirm("Etes vous sur de vouloir supprimer cette missions")){
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
    }

    $scope.saveMission = function(mission){
        
        if(!checkForm(mission)){
            return false;
        }
        $http.post('../serv/ws/missions.ws.php' , {action:'saveMission' , mission : JSON.stringify(mission)}).then(
            function(response){
                if(response.data.error === true){
                    $scope.data.errorAddNewMission = true;
                    $scope.closeMission();
                    return false;
                }
                $scope.data.newMissionAdded = true;
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
        $scope.data.newMissionAdded = false;
        $scope.data.errorAddNewMission = false;
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

    function checkForm(mission){
        if(!angular.isDefined(mission.departure) || mission.departure == ''){
            $scope.data.errorForm.departure = true;
            return false;
        }else{
            $scope.data.errorForm.departure = false;
        }

        if(!angular.isDefined(mission.ddeparture) || mission.ddeparture == ''){
            $scope.data.errorForm.ddeparture = true;
            return false;
        }else{
            $scope.data.errorForm.ddeparture = false;
        }

        if(!angular.isDefined(mission.destination) || mission.destination == ''){
            $scope.data.errorForm.destination = true;
            return false;
        }else{
            $scope.data.errorForm.destination = false;
        }

        if(!angular.isDefined(mission.rate) || mission.rate == ''){
            $scope.data.errorForm.rate = true;
            return false;
        }else{
            $scope.data.errorForm.rate = false;
        }

        if(!angular.isDefined(mission.carid) || mission.carid == ''){
            $scope.data.errorForm.carid = true;
            return false;
        }else{
            $scope.data.errorForm.carid = false;
        }

        return true;
    }

    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;

    
});