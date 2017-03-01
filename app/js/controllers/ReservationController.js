angular.module('MetronicApp').controller('ReservationController', function($rootScope, $scope, $http,settings) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    console.log("Ctrl Reservation");
    $scope.data = {};
    $scope.data.hideFormReservation = false;
    $scope.data.hideListReservation = true;
    $scope.data.hideDivInfosCar = false;
    $scope.data.newResrAdded = false;
    $scope.data.errorAddNewResr = false;

    getReservations();
    getCars();



    $scope.AddNewReservation = function(){
        $scope.data.hideListReservation = false;
        $scope.data.hideFormReservation = true;
        $scope.data.newResrAdded = false;
        $scope.data.errorAddNewResr = false;
        $scope.data.reservation = {};
        $scope.data.errorForm = {};
    }


    function getReservations(){
        $http.post('../serv/ws/reservations.ws.php' , {action:'getAllReservations'}).then(
            function(response){
                console.log(response.data.data);
                $scope.data.reservations = response.data.data;
            },
            function(error){
                console.log(error);
            }
        )
    };

    $scope.editreservation = function(reservation){
        $scope.data.newResrAdded = false;
        $scope.data.errorAddNewResr = false;
        $scope.data.hideListReservation = false;
        $scope.data.hideFormReservation = true;
        $scope.data.reservation = reservation;
        $scope.data.errorForm = {};
    }

    $scope.deletereservation = function(reservation){
        if(window.confirm("Etes vous sur de vouloir supprimer cette réservation ?")){
            $http.post('../serv/ws/reservations.ws.php' , {action:'deleteReservation' , id : reservation.id}).then(
                function(response){
                    if(response.data.error === true){
                        alert(response.data.data);
                        return false;
                    }

                    getReservations();
                },
                function(error){
                    console.log(error);
                }
            )
        }

    }

    $scope.saveReservation = function(reservation){
        if(!checkForm(reservation)){
            return false;
        }
        $http.post('../serv/ws/reservations.ws.php' , {action:'saveReservation' , reservation : JSON.stringify(reservation)}).then(
            function(response){
                if(response.data.error === true){
                    $scope.data.errorAddNewResr = true;
                    $scope.closeReservation();
                    return false;
                }
                $scope.data.newResrAdded = true;
                $scope.closeReservation();
                getReservations();
            },
            function(error){
                console.log(error);
            }
        )
    }

    $scope.closeReservation = function(){
        $scope.data.hideFormReservation = false;
        $scope.data.hideListReservation = true;
    }

    $scope.getInfosCar = function(car){
        $scope.data.car = car;
        $scope.data.hideFormReservation = false;
        $scope.data.hideListReservation = false;
        $scope.data.hideDivInfosCar = true;

    }

    $scope.closeInfosCar = function(){
        $scope.data.hideFormReservation = false;
        $scope.data.hideListReservation = true;
        $scope.data.hideDivInfosCar = false;
        $scope.data.newResrAdded = false;
        $scope.data.errorAddNewResr = false;   
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

    function checkForm(reservation){
        if(!angular.isDefined(reservation.dreservation) || reservation.dreservation == ''){
            $scope.data.errorForm.dreservation = true;
            return false;
        }else{
            $scope.data.errorForm.dreservation = false;
        }

        if(!angular.isDefined(reservation.rate) || reservation.rate == ''){
            $scope.data.errorForm.rate = true;
            return false;
        }else{
            $scope.data.errorForm.rate = false;
        }

        if(!angular.isDefined(reservation.carid) || reservation.carid == ''){
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