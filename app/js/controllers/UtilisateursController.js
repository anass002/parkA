angular.module('MetronicApp').controller('UtilisateursController', function($rootScope, $scope, $http, $timeout, UserModel, jwtHelper) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    $scope.data = {};
    $scope.data.hideDivTableUser = true;
    $scope.data.hideDivInfosUser = false;
    $scope.data.userAdded = false;
    $scope.data.error = false;

    getUsers();
    

    $scope.editUser = function(user){
        console.log(user);
        $scope.data.hideDivInfosUser = true;
        $scope.data.hideDivTableUser = false;
        $scope.data.user = user;
    }
    $scope.deleteUser = function(user){
        console.log(user);
        $http.post('../serv/ws/users.ws.php' ,{action:'deleteUser' , id : user.id}).then(
            function(response){
                if(response.data.error === true){
                    alert("Error On Add User");
                    return false;
                }
                getUsers();
            },
            function(error){
                console.log(error);
            }
        )
    }

    $scope.AddNewUser = function(){
        $scope.data.hideDivInfosUser = true;
        $scope.data.hideDivTableUser = false;
        $scope.data.user = {};
    }

    $scope.closeNewUser = function(){
        $scope.data.hideDivInfosUser = false;
        $scope.data.hideDivTableUser = true;
    }

    $scope.saveUser = function(user){
        console.log(user);

        $http.post('../serv/ws/users.ws.php' , {action:'AddNewUser' , user:JSON.stringify(user)}).then(
            function(response){
                if(response.data.error === true){
                    alert("Error On Add User");
                    return false;
                }
                $scope.closeNewUser();
                $scope.data.userAdded = true;
                getUsers();

            },
            function(error){
                console.log(error);
                $scope.data.error = true;
            }
        )
    }

    function getUsers(){
        $http.post('../serv/ws/users.ws.php' , {action:'getAllUsers'}).then(
            function(response){
                $scope.data.users = response.data.data;
            },
            function(error){
                console.log(err);
            }
        )
    }




    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;

    
});