angular.module('MetronicApp').controller('PapiersController', function($rootScope, $scope, $http,settings) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();
    });
    console.log("CTRL PAPIERS");
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    $scope.data = {};
    $scope.data.hideDivTablePapiers = true;	
	$scope.data.hideDivFormPapiers = false;

	getPapers();
	getCars();

	$scope.addNewPaper = function(){
		$scope.data.hideDivTablePapiers = false;	
		$scope.data.hideDivFormPapiers = true;
		$scope.data.paper = {};
	}

	$scope.editPaper = function(paper){
		console.log(paper);
		$scope.data.hideDivTablePapiers = false;	
		$scope.data.hideDivFormPapiers = true;
		$scope.data.paper = paper;
	}
	$scope.deletePaper = function(paper){
		console.log(paper);

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

	$scope.savePaper = function(paper){
		console.log(paper);

		$http.post('../serv/ws/papers.ws.php' , {action:'savePaper' , paper : JSON.stringify(paper)}).then(
			function(response){
				if(response.data.error === true){
					alert(response.data.data);
					return false;
				}
				$scope.closePaper();
				getPapers();
			}
		)
	}
	$scope.closePaper = function(){
		$scope.data.hideDivTablePapiers = true;	
		$scope.data.hideDivFormPapiers = false;
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

    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;
});     