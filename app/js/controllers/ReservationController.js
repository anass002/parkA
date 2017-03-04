angular.module('MetronicApp').controller('ReservationController', function($rootScope, $scope, $http,settings, FileUploader ,jwtHelper) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();

        if(window.innerWidth < 992){
            $(".page-sidebar").removeClass("in");
        }
    });
    console.log("Ctrl Reservation");
    $scope.data = {};    
    $scope.data.hideFormReservation = false;
    $scope.data.hideListReservation = true;
    $scope.data.hideDivInfosCar = false;
    $scope.data.newResrAdded = false;
    $scope.data.errorAddNewResr = false;

    var tokenPayload = jwtHelper.decodeToken(window.localStorage['authToken']);
    $scope.data.access = true;
    if(tokenPayload.type == 'user' && !tokenPayload.droits.reservations){
        $scope.data.access = false;
    }

    getReservations();
    getCars();



    $scope.AddNewReservation = function(){
        $scope.data.hideListReservation = false;
        $scope.data.hideFormReservation = true;
        $scope.data.newResrAdded = false;
        $scope.data.errorAddNewResr = false;
         $scope.data.reservation = {};   
         $scope.data.reservation.files = {};
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

    //Upload


        var uploader = $scope.uploader = new FileUploader({
            url: '../serv/upload.php'
        });

        // FILTERS

        uploader.filters.push({
            name: 'imageFilter',
            fn: function(item /*{File|FileLikeObject}*/, options) {
                var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
            }
        });

        // CALLBACKS

        uploader.onWhenAddingFileFailed = function(item /*{File|FileLikeObject}*/, filter, options) {
            //console.info('onWhenAddingFileFailed', item, filter, options);
        };
        uploader.onAfterAddingFile = function(fileItem) {
           // console.info('onAfterAddingFile', fileItem);
        };
        uploader.onAfterAddingAll = function(addedFileItems) {
           // console.info('onAfterAddingAll', addedFileItems);
        };
        uploader.onBeforeUploadItem = function(item) {
           // console.info('onBeforeUploadItem', item);
        };
        uploader.onProgressItem = function(fileItem, progress) {
           // console.info('onProgressItem', fileItem, progress);
        };
        uploader.onProgressAll = function(progress) {
           // console.info('onProgressAll', progress);
        };
        uploader.onSuccessItem = function(fileItem, response, status, headers) {
           // console.info('onSuccessItem', fileItem, response, status, headers);
           if(angular.isDefined(fileItem.file.name)){
                $scope.data.reservation.files[fileItem.file.name] = "uploads/"+fileItem.file.name;
           }
        };
        uploader.onErrorItem = function(fileItem, response, status, headers) {
           // console.info('onErrorItem', fileItem, response, status, headers);
        };
        uploader.onCancelItem = function(fileItem, response, status, headers) {
           // console.info('onCancelItem', fileItem, response, status, headers);
        };
        uploader.onCompleteItem = function(fileItem, response, status, headers) {
           // console.info('onCompleteItem', fileItem, response, status, headers);
           //console.log(fileItem);
           
        };
        uploader.onCompleteAll = function() {
           // console.info('onCompleteAll');
        };

    
});