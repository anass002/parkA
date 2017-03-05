angular.module('MetronicApp').controller('CategorieController', function($rootScope, $scope,$http,settings,jwtHelper) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();

        if(window.innerWidth < 992){
            $(".page-sidebar").removeClass("in");
        }
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;    
    console.log("CTRL categories");
    $scope.data = {};
    $scope.data.hideDivCategories = true;
    $scope.data.hideFormCategories = false;
    var tokenPayload = jwtHelper.decodeToken(window.localStorage['authToken']);
    $scope.data.access = true;
    if(tokenPayload.type == 'user' && !tokenPayload.droits.categories){
        $scope.data.access = false;
    }


    getCategories();

    $scope.editCategorie = function(categorie){
    	$scope.data.hideDivCategories = false;
    	$scope.data.hideFormCategories = true;
    	$scope.data.categorie = categorie;
    }
	$scope.deleteCategorie = function(categorie){
		console.log(categorie);

		$http.post('../serv/ws/categories.ws.php' , {action:'deleteCategorie' , id : categorie.id}).then(
			function(response){
				if(response.data.error === true){
					alert(response.data.data);
					return false;
				}
				getCategories();
			},
			function(error){
				console.log(error);
			}
		)
	}

    $scope.AddNewCategorie = function(){
    	$scope.data.hideDivCategories = false;
    	$scope.data.hideFormCategories = true;
    	$scope.data.categorie = {};
    }

    $scope.saveCategorie = function(categorie){
    	console.log(categorie);

    	$http.post('../serv/ws/categories.ws.php' , {action:'saveCategorie' , categorie : JSON.stringify(categorie)}).then(
    		function(response){
    			if(response.data.error === true){
    				alert(response.data.data);
    				return false
    			}

    			$scope.closeCategories();
    			getCategories();
    		},
    		function(error){
    			console.log(error);
    		}
    	)

    }
	$scope.closeCategories = function(){
		$scope.data.hideDivCategories = true;
    	$scope.data.hideFormCategories = false;
	}

    $scope.exporterExcel = function(type){
        console.log("EXporter Excel");

        $http.post('../serv/ws/categories.ws.php' , {action:'exportExcel'}).then(
            function(response){
                console.log(response.data.data);

                window.location.href = 'download/'+response.data.data;
            },
            function(error){
                console.log(error);
            }
        )
    }

    $scope.exporterPDF = function(){
        $http.post('../serv/ws/categories.ws.php' , {action:'exportPDF'}).then(
            function(response){
                console.log(response.data.data);

                window.location.href = 'download/'+response.data.data;
            },
            function(error){
                console.log(error);
            }
        )   
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

        // set default layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;	
});