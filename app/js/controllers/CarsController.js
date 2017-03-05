angular.module('MetronicApp').controller('CarsController', function($rootScope, $scope, $http,settings,jwtHelper,$window) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();

        if(window.innerWidth < 992){
            $(".page-sidebar").removeClass("in");
        }
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    console.log("Ctrl CARS");
    $scope.data = {};
    $scope.data.hideDivCars = true;
    $scope.data.hideInfosCar = false;
    $scope.data.hideModeInfosCars = false;
    $scope.data.newcarAdded = false;
    $scope.data.errorAddNewCar = false;
    
    var tokenPayload = jwtHelper.decodeToken(window.localStorage['authToken']);
    $scope.data.access = true;
    if(tokenPayload.type == 'user' && !tokenPayload.droits.cars){
        $scope.data.access = false;
    }

    getCars();
    getCategories();
    getBrands();

    $scope.addNewCar = function(){
        $scope.data.hideDivCars = false;
        $scope.data.hideInfosCar = true;
        $scope.data.newcarAdded = false;
        $scope.data.errorAddNewCar = false;
        $scope.data.car = {};
        $scope.data.errorForm = {};
        $scope.data.models = {};
        $scope.data.car.carcode = $scope.data.cars.length;
    }

    $scope.editCar = function(car){
        $scope.data.hideDivCars = false;
        $scope.data.hideInfosCar = true;
        $scope.data.newcarAdded = false;
        $scope.data.errorAddNewCar = false;
        $scope.data.car = car;
        $scope.data.errorForm = {};
        $scope.getNameCars(car.brand);

    }
    $scope.deleteCar = function(car){
        $scope.data.newcarAdded = false;
        $scope.data.errorAddNewCar = false;
        if(window.confirm("Etes Vous de vouloir Supprimer cette Voiture ? ")){
            $http.post('../serv/ws/cars.ws.php' , {action:'deleteCars' , id : car.id}).then(
                function(response){
                    console.log(response.data);
                    if(response.data.error === true){
                        alert(response.data.data);
                        return false;
                    }
                    getCars();
                },
                function(error){
                    console.log(error);
                }
            )
        }
    }

    $scope.saveCar = function(car){
        if(!checkForm(car)){
            return false;
        }

        $http.post('../serv/ws/cars.ws.php' , {action:'saveCar' , car : JSON.stringify(car)}).then(
            function(response){
                console.log(response.data);
                if(response.data.error === true){
                    $scope.data.errorAddNewCar = true;
                    $scope.closeCar();
                    return false;
                }
                $scope.closeCar();
                $scope.data.newcarAdded = true;
                getCars();
            },
            function(error){
                console.log(error);
            }
        )

    }
    $scope.closeCar = function(){
        $scope.data.hideDivCars = true;
        $scope.data.hideInfosCar = false;
    }

    $scope.getInfosCar = function(car){
        $scope.data.newcarAdded = false;
        $scope.data.errorAddNewCar = false;
        $scope.data.car = car;

        $http.post('../serv/ws/cars.ws.php' , {action:'getAllInfosCar' , id : car.id}).then(
            function(response){
                $scope.data.hideModeInfosCars = true;
                $scope.data.hideDivCars = false;
                $scope.data.hideInfosCar = false;

                console.log(response.data.data);
                $scope.data.carInfos = response.data.data;
            },
            function(error){
                console.log(error);
            }
        )
    }

    $scope.closeMoreInfos = function(){
        $scope.data.hideModeInfosCars = false;
        $scope.data.hideDivCars = true;
        $scope.data.hideInfosCar = false;
    }

    $scope.getNameCars = function(brand){
        console.log(brand);
        angular.forEach($scope.data.brands , function(object){
            if(brand == object.title){
                console.log(object.models);
                $scope.data.models = object.models;
            }
        })
    }

    $scope.exporterExcel = function(type){
        console.log("EXporter Excel");

        $http.post('../serv/ws/cars.ws.php' , {action:'exportExcel'}).then(
            function(response){
                console.log(response.data.data);

                $window.open('download/'+response.data.data, '_blank');
            },
            function(error){
                console.log(error);
            }
        )
    }

    $scope.exporterPDF = function(){
        $http.post('../serv/ws/cars.ws.php' , {action:'exportPDF'}).then(
            function(response){
                console.log(response.data.data);

                $window.open('download/'+response.data.data, '_blank');
            },
            function(error){
                console.log(error);
            }
        )   
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
    }

    function getBrands(){
        $http.get('../assets/brand.json').success(function(data) {
           console.log(data);
           $scope.data.brands = data;
        });
    }

    function getCategories(){
        $http.post('../serv/ws/categories.ws.php' , {action:'getAllCategories'}).then(
            function(response){
                console.log(response.data.data);
                $scope.data.categories = response.data.data;
            },
            function(error){
                console.log(error);
            }
        )
    }

    function checkForm(car){
        if(!angular.isDefined(car.carcode) || car.carcode == ''){
            $scope.data.errorForm.carcode = true;
            return false;
        }else{
            $scope.data.errorForm.carcode = false;
        }

        if(!angular.isDefined(car.brand) || car.brand == ''){
            $scope.data.errorForm.brand = true;
            return false;
        }else{
            $scope.data.errorForm.brand = false;
        }

        if(!angular.isDefined(car.name) || car.name == ''){
            $scope.data.errorForm.name = true;
            return false;
        }else{
            $scope.data.errorForm.name = false;
        }

        if(!angular.isDefined(car.greycard) || car.greycard == ''){
            $scope.data.errorForm.greycard = true;
            return false;
        }else{
            $scope.data.errorForm.greycard = false;
        }

        if(!angular.isDefined(car.km) || car.km == ''){
            $scope.data.errorForm.km = true;
            return false;
        }else{
            $scope.data.errorForm.km = false;
        }

        if(!angular.isDefined(car.registrationnumber) || car.registrationnumber == ''){
            $scope.data.errorForm.registrationnumber = true;
            return false;
        }else{
            $scope.data.errorForm.registrationnumber = false;
        }

        if(!angular.isDefined(car.catid) || car.catid == ''){
            $scope.data.errorForm.catid = true;
            return false;
        }else{
            $scope.data.errorForm.catid = false;
        }

        return true;
    }

    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;

    
});