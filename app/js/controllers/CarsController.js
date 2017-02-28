angular.module('MetronicApp').controller('CarsController', function($rootScope, $scope, $http,settings) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    console.log("Ctrl CARS");
    $scope.data = {};
    $scope.data.hideDivCars = true;
    $scope.data.hideInfosCar = false;
    $scope.data.hideModeInfosCars = false;

    getCars();
    getCategories();
    getBrands();

    $scope.addNewCar = function(){
        $scope.data.hideDivCars = false;
        $scope.data.hideInfosCar = true;
        $scope.data.car = {};
    }

    $scope.editCar = function(car){
        console.log(car);
        $scope.data.hideDivCars = false;
        $scope.data.hideInfosCar = true;
        $scope.data.car = car;

    }
    $scope.deleteCar = function(car){
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

    $scope.saveCar = function(car){
        console.log(car);
        $http.post('../serv/ws/cars.ws.php' , {action:'saveCar' , car : JSON.stringify(car)}).then(
            function(response){
                console.log(response.data);
                if(response.data.error === true){
                    alert(response.data.data);
                    return false;
                }
                $scope.closeCar();
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
        console.log(car);
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

    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;

    
});