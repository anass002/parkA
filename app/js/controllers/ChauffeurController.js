angular.module('MetronicApp').controller('ChauffeurController', function($rootScope, $scope, $http,settings) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    console.log("Drivers CTRL");
    $scope.data = {};
    $scope.data.hideDivTableDrivers = true;	
	$scope.data.hideDivFormDrivers = false;

    getDrivers();
    getCars();

    $scope.addNewDriver = function(){
    	$scope.data.hideDivTableDrivers = false;	
		$scope.data.hideDivFormDrivers = true;
		$scope.data.driver = {};
    }

    $scope.editDriver = function(driver){
    	console.log(driver);
    	$scope.data.hideDivTableDrivers = false;	
		$scope.data.hideDivFormDrivers = true;
		$scope.data.driver = driver;

    }
	$scope.deleteDriver = function(driver){
		console.log(driver);
        $http.post('../serv/ws/drivers.ws.php' , {action:'deleteDriver' , id : driver.id}).then(
            function(response){
                if(response.data.error === true){
                    alert(response.data.data);
                    return false;
                }

                getDrivers();
            },
            function(error){
                console.log(error);
            }
        )

	}

	$scope.saveDriver = function(driver){
		console.log(driver);
        $http.post('../serv/ws/drivers.ws.php' , {action:'saveDriver' , driver : JSON.stringify(driver)}).then(
            function(response){
                if(response.data.error === true){
                    alert(response.data.data);
                    return false;
                }
                $scope.closeDriver();
                getDrivers();
            },
            function(error){
                console.log(error);
            }
        )
	}

	$scope.closeDriver = function(){
		$scope.data.hideDivTableDrivers = true;	
		$scope.data.hideDivFormDrivers = false;
	}





    function getDrivers(){
    	$http.post('../serv/ws/drivers.ws.php' , {action:'getAllDrivers'}).then(
    		function(response){
    			console.log(response.data.data);
    			$scope.data.drivers = response.data.data;
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

    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;

    
});    