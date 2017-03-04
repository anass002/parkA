angular.module('MetronicApp').controller('PapiersController', function($rootScope, $scope, $http,settings,jwtHelper) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();

        if(window.innerWidth < 992){
            $(".page-sidebar").removeClass("in");
        }
    });
    console.log("CTRL PAPIERS");
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    $scope.data = {};
    $scope.data.hideDivTablePapiers = true;	
	$scope.data.hideDivFormPapiers = false;
	$scope.data.hideDivInfosCar = false;
	$scope.data.newPaperAdded = false;
	$scope.data.errorAddNewPaper = false;
	var tokenPayload = jwtHelper.decodeToken(window.localStorage['authToken']);
    $scope.data.access = true;
    if(tokenPayload.type == 'user' && !tokenPayload.droits.papers){
        $scope.data.access = false;
    }

	getPapers();
	getCars();

	$scope.addNewPaper = function(){
		$scope.data.hideDivTablePapiers = false;	
		$scope.data.hideDivFormPapiers = true;
		$scope.data.paper = {};
		$scope.data.newPaperAdded = false;
		$scope.data.errorAddNewPaper = false;
		$scope.data.errorForm = {};
	}

	$scope.editPaper = function(paper){
		console.log(paper);
		$scope.data.hideDivTablePapiers = false;	
		$scope.data.hideDivFormPapiers = true;
		$scope.data.paper = paper;
		$scope.data.newPaperAdded = false;
		$scope.data.errorAddNewPaper = false;
		$scope.data.errorForm = {};
	}
	$scope.deletePaper = function(paper){
		if(window.confirm("Etes vous sur de vouloir supprimer ce papier ?")){
			$http.post('../serv/ws/papers.ws.php' , {action:'deletePaper' , id : paper.id}).then(
				function(response){
					if(response.data.error === true){
						alert(response.data.data);
						return false;
					}

					getPapers();
				}
			)
		}
	}

	$scope.savePaper = function(paper){
		if(!checkForm(paper)){
			return false;
		}

		$http.post('../serv/ws/papers.ws.php' , {action:'savePaper' , paper : JSON.stringify(paper)}).then(
			function(response){
				if(response.data.error === true){
					$scope.data.errorAddNewPaper = true;
					$scope.closePaper();
					return false;
				}
				$scope.data.newPaperAdded = true;
				$scope.closePaper();
				getPapers();
			}
		)
	}
	$scope.closePaper = function(){
		$scope.data.hideDivTablePapiers = true;	
		$scope.data.hideDivFormPapiers = false;
	}

	$scope.getInfosCar = function(car){
        $scope.data.car = car;
        $scope.data.hideDivTablePapiers = false;	
		$scope.data.hideDivFormPapiers = false;
		$scope.data.newPaperAdded = false;
		$scope.data.errorAddNewPaper = false;
    }

    $scope.closeInfosCar = function(){
        $scope.data.hideDivTablePapiers = true;	
		$scope.data.hideDivFormPapiers = false;
        $scope.data.hideDivInfosCar = false;   
    }

	function getPapers(){
		$http.post('../serv/ws/papers.ws.php' , {action : 'getAllPapers'}).then(
			function(response){
				console.log(response.data.data);
				$scope.data.papers = response.data.data;
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

    function checkForm(paper){
    	if(!angular.isDefined(paper.name) || paper.name == ''){
            $scope.data.errorForm.name = true;
            return false;
        }else{
            $scope.data.errorForm.name = false;
        }

        if(!angular.isDefined(paper.dbegin) || paper.dbegin == ''){
            $scope.data.errorForm.dbegin = true;
            return false;
        }else{
            $scope.data.errorForm.dbegin = false;
        }

        if(!angular.isDefined(paper.dend) || paper.dend == ''){
            $scope.data.errorForm.dend = true;
            return false;
        }else{
            $scope.data.errorForm.dend = false;
        }

        if(!angular.isDefined(paper.carid) || paper.carid == ''){
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