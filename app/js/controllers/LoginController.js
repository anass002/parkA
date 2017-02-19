angular.module('MetronicApp').controller('LoginController', function($rootScope, $scope, $window,$http, UserModel) {
    /*$scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initComponents(); // init core components
        //App.initAjax();
    });

    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = false;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;*/
    $scope.data = {};
    $scope.connect = function(data){
        $http.post('../serv/ws/auth.ws.php' , {action:'signIn',login : data.login , password : data.password}).then(
            function(response){
                console.log(response.data.data);

                if(response.data.error === true){
                    alert(response.data.data);
                    return false;
                }

                window.localStorage['authToken'] = response.data.data;
                $window.location.href = './#/dashboard.html';

            },
            function(error){
                console.log(error);
            }
        )   	
    };
});