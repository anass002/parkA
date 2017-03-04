angular.module('MetronicApp').controller('UtilisateursController', function($rootScope, $scope, $http, $timeout, UserModel, jwtHelper) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();

        if(window.innerWidth < 992){
            $(".page-sidebar").removeClass("in");
        }
    });

    $scope.data = {};
    var tokenPayload = jwtHelper.decodeToken(window.localStorage['authToken']);
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    
    $scope.data.hideDivTableUser = true;
    $scope.data.hideDivInfosUser = false;
    $scope.data.userAdded = false;
    $scope.data.error = false;
    $scope.data.access = true;


    if(tokenPayload.type == 'user' && !tokenPayload.droits.user){
        $scope.data.access = false;
    }



    getUsers();
    

    $scope.editUser = function(user){
        console.log(user);
        $scope.data.hideDivInfosUser = true;
        $scope.data.hideDivTableUser = false;
        $scope.data.user = user;
        $scope.data.errorForm = {};
    }
    $scope.deleteUser = function(user){
        console.log(user);
        if(window.confirm("Etes vous sur de vouloir supprimer cette utilisateur ?")){
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
    }

    $scope.AddNewUser = function(){
        $scope.data.hideDivInfosUser = true;
        $scope.data.hideDivTableUser = false;
        $scope.data.user = {};
        $scope.data.errorForm = {};
    }

    $scope.closeNewUser = function(){
        $scope.data.hideDivInfosUser = false;
        $scope.data.hideDivTableUser = true;
    }

    $scope.saveUser = function(user){

        if(!checkForm(user)){
            return false;
        }

        $http.post('../serv/ws/users.ws.php' , {action:'AddNewUser' , user:JSON.stringify(user)}).then(
            function(response){
                if(response.data.error === true){
                    alert(response.data.data);
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


    function checkForm (user){
        if(!angular.isDefined(user.lastname) || user.lastname == ''){
            $scope.data.errorForm.lastname = true;
            return false;
        }else{
            $scope.data.errorForm.lastname = false;
        }

        if(!angular.isDefined(user.firstname) || user.firstname == ''){
            $scope.data.errorForm.firstname = true;
            return false;
        }else{
            $scope.data.errorForm.firstname = false;
        }

        if(!angular.isDefined(user.email) || user.email == ''){
            $scope.data.errorForm.email = true;
            return false;
        }else{
            $scope.data.errorForm.email = false;
        }

        if(!angular.isDefined(user.login) || user.login == ''){
            $scope.data.errorForm.login = true;
            return false;
        }else{
            $scope.data.errorForm.login = false;
        }

        if(!angular.isDefined(user.password) || user.password == ''){
            $scope.data.errorForm.password = true;
            return false;
        }else{
            $scope.data.errorForm.password = false;
        }


        if(!angular.isDefined(user.type) || user.type == ''){
            $scope.data.errorForm.type = true;
            return false;
        }else{
            $scope.data.errorForm.type = false;
        }

        return true;
    }




    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;

    
});