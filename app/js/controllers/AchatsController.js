angular.module('MetronicApp').controller('AchatsController', function($rootScope, $scope, $http,settings) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();
    });
    console.log("CTRL ACHATS");
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    $scope.data = {};
    $scope.data.hideDivTableAchats = true;	
	$scope.data.hideDivFormAchats = false;
	$scope.data.hideDivInfosCar = false;
	getAchats();
	getCars();

	
	$scope.addNewAchat = function(achat){
		$scope.data.hideDivTableAchats = false;	
		$scope.data.hideDivFormAchats = true;
		$scope.data.achat = {};
	}

	$scope.editAchat = function(achat){
		$scope.data.hideDivTableAchats = false;	
		$scope.data.hideDivFormAchats = true;
		$scope.data.achat = achat;
	}

	$scope.deleteAchat = function(achat){
		console.log(achat);
		$http.post('../serv/ws/achats.ws.php' , {action:'deletePurshase' , id : achat.id}).then(
			function(response){
				if(response.data.error === true){
					alert(response.data.data);
					return false;
				}

				getAchats();
			}
		)
	}


	$scope.saveAchat = function(achat){
		console.log(achat);

		$http.post('../serv/ws/achats.ws.php' , {action:'savePurshase' , achat : JSON.stringify(achat)}).then(
			function(response){
				if(response.data.error === true){
					alert(response.data.data);
					return false;
				}
				$scope.closeAchat();
				getAchats();
			},
			function(error){
				console.log(error);
			}
		)
	}

	$scope.closeAchat = function(){
		$scope.data.hideDivTableAchats = true;	
		$scope.data.hideDivFormAchats = false;
	}

	$scope.getInfosCar = function(car){
        $scope.data.car = car;
        $scope.data.hideDivTableAchats = false;	
		$scope.data.hideDivFormAchats = false;
        $scope.data.hideDivInfosCar = true;
    }

    $scope.closeInfosCar = function(){
        $scope.data.hideDivTableAchats = true;	
		$scope.data.hideDivFormAchats = false;
        $scope.data.hideDivInfosCar = false;   
    }

	function getAchats(){
		$http.post('../serv/ws/achats.ws.php' , {action:'getAllPurshases'}).then(
			function(response){
				console.log(response.data.data);
				$scope.data.achats = response.data.data;
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