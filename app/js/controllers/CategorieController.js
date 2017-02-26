angular.module('MetronicApp').controller('CategorieController', function($rootScope, $scope,$http,settings) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;    
    console.log("CTRL categories");
    $scope.data = {};
    $scope.data.hideDivCategories = true;
    $scope.data.hideFormCategories = false;


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