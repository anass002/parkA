angular.module('MetronicApp').controller('AchatsController', function($rootScope, $scope, $http,settings,jwtHelper,$window) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();

        if(window.innerWidth < 992){
            $(".page-sidebar").removeClass("in");
        }
    });
    console.log("CTRL ACHATS");
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    $scope.data = {};
    $scope.data.hideDivTableAchats = true;	
	$scope.data.hideDivFormAchats = false;
	$scope.data.hideDivInfosCar = false;
	$scope.data.newAchatAdded = false;
	$scope.data.errorAddNewAchat = false;
	getAchats();
	getCars();

	var tokenPayload = jwtHelper.decodeToken(window.localStorage['authToken']);
    $scope.data.access = true;
    if(tokenPayload.type == 'user' && !tokenPayload.droits.cars){
        $scope.data.access = false;
    }

	
	$scope.addNewAchat = function(achat){
		$scope.data.hideDivTableAchats = false;	
		$scope.data.hideDivFormAchats = true;
		$scope.data.newAchatAdded = false;
		$scope.data.errorAddNewAchat = false;
		$scope.data.achat = {};
		$scope.data.errorForm = {};
	}

	$scope.editAchat = function(achat){
		$scope.data.hideDivTableAchats = false;	
		$scope.data.hideDivFormAchats = true;
		$scope.data.newAchatAdded = false;
		$scope.data.errorAddNewAchat = false;
		$scope.data.achat = achat;
		$scope.data.errorForm = {};
	}

	$scope.deleteAchat = function(achat){
		if(window.confirm("Etes vous sur de vouloir supprimer cette achat ?")){
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
	}


	$scope.saveAchat = function(achat){
		if(!checkForm(achat)){
			return false;
		}

		$http.post('../serv/ws/achats.ws.php' , {action:'savePurshase' , achat : JSON.stringify(achat)}).then(
			function(response){
				if(response.data.error === true){
					$scope.data.errorAddNewAchat = true;
					$scope.closeAchat();
					return false;
				}
				$scope.data.newAchatAdded = true;
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
        $scope.data.errorAddNewAchat = false;
        $scope.data.newAchatAdded = false;
    }

    $scope.closeInfosCar = function(){
        $scope.data.hideDivTableAchats = true;	
		$scope.data.hideDivFormAchats = false;
        $scope.data.hideDivInfosCar = false;   
    }

    $scope.exporterExcel = function(type){
        console.log("EXporter Excel");

        $http.post('../serv/ws/achats.ws.php' , {action:'exportExcel'}).then(
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
        $http.post('../serv/ws/achats.ws.php' , {action:'exportPDF'}).then(
            function(response){
                console.log(response.data.data);

                $window.open('download/'+response.data.data, '_blank');
            },
            function(error){
                console.log(error);
            }
        )   
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

    function checkForm(achat){
    	if(!angular.isDefined(achat.name) || achat.name == ''){
            $scope.data.errorForm.name = true;
            return false;
        }else{
            $scope.data.errorForm.name = false;
        }

        if(!angular.isDefined(achat.nfacture) || achat.nfacture == ''){
            $scope.data.errorForm.nfacture = true;
            return false;
        }else{
            $scope.data.errorForm.nfacture = false;
        }

        if(!angular.isDefined(achat.nbl) || achat.nbl == ''){
            $scope.data.errorForm.nbl = true;
            return false;
        }else{
            $scope.data.errorForm.nbl = false;
        }

        if(!angular.isDefined(achat.price) || achat.price == ''){
            $scope.data.errorForm.price = true;
            $scope.data.errorForm.text = "Veuillez saisir le prix d'achat  !";
            return false;
        }else{
            var isNumber = /^[0-9.]+$/.test(achat.price);
            if(!isNumber){
                $scope.data.errorForm.price = true;
                $scope.data.errorForm.text = "Le Prix doit etre un nombre !";
                return false;
            }else{
                $scope.data.errorForm.price = false;
            }
        }

        if(!angular.isDefined(achat.carid) || achat.carid == ''){
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