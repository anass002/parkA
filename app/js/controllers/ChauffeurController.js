angular.module('MetronicApp').controller('ChauffeurController', function($rootScope, $scope, $http,settings, FileUploader , jwtHelper,$window) {
    $scope.$on('$viewContentLoaded', function() {   
        // initialize core components
        App.initAjax();

        if(window.innerWidth < 992){
            $(".page-sidebar").removeClass("in");
        }
    });
    $http.defaults.headers.common.Authorization = 'Bearer ' + window.localStorage['authToken'] ;
    console.log("Drivers CTRL");
    $scope.data = {};
    $scope.data.hideDivTableDrivers = true;	
    $scope.data.hideDivFormDrivers = false;
	$scope.data.hideDivInfosCar = false;
    $scope.data.newdriverAdded = false;
    $scope.data.errorAddNewdriver = false;
    var tokenPayload = jwtHelper.decodeToken(window.localStorage['authToken']);
    $scope.data.access = true;
    if(tokenPayload.type == 'user' && !tokenPayload.droits.drivers){
        $scope.data.access = false;
    }

    getDrivers();
    getCars();

    $scope.addNewDriver = function(){
    	$scope.data.hideDivTableDrivers = false;	
		$scope.data.hideDivFormDrivers = true;
        $scope.data.newdriverAdded = false;
        $scope.data.errorAddNewdriver = false;
		$scope.data.driver = {};        
       $scope.data.driver.files = {};
        $scope.data.errorForm = {};
        getCars();
    }

    $scope.editDriver = function(driver){
    	$scope.data.hideDivTableDrivers = false;	
		$scope.data.hideDivFormDrivers = true;
		$scope.data.newdriverAdded = false;
        $scope.data.errorAddNewdriver = false;
        $scope.data.driver = driver;
        $scope.data.errorForm = {};
        $scope.data.cars.push(driver.dependencies.car);
        $scope.uploader.queue = [];

    }
	$scope.deleteDriver = function(driver){
		$scope.data.newdriverAdded = false;
        $scope.data.errorAddNewdriver = false;
        if(window.confirm("Etes-vous sur de vouloir supprimer ce chauffeur ?")){
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
	}

	$scope.saveDriver = function(driver){
        if(!checkForm(driver)){
            return false;
        }
        $http.post('../serv/ws/drivers.ws.php' , {action:'saveDriver' , driver : JSON.stringify(driver)}).then(
            function(response){
                if(response.data.error === true){
                    $scope.data.errorAddNewdriver = true;
                    $scope.closeDriver();
                    return false;
                }
                $scope.data.newdriverAdded = true;
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

    $scope.getInfosCar = function(car){
        $scope.data.car = car;
        $scope.data.hideDivTableDrivers = false;    
        $scope.data.hideDivFormDrivers = false;
        $scope.data.hideDivInfosCar = true;
        $scope.data.newdriverAdded = false;
        $scope.data.errorAddNewdriver = false;
    }

    $scope.closeInfosCar = function(){
        $scope.data.hideDivTableDrivers = true;    
        $scope.data.hideDivFormDrivers = false;
        $scope.data.hideDivInfosCar = false;   
    }

    $scope.exporterExcel = function(type){
        console.log("EXporter Excel");

        $http.post('../serv/ws/drivers.ws.php' , {action:'exportExcel'}).then(
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
        $http.post('../serv/ws/drivers.ws.php' , {action:'exportPDF'}).then(
            function(response){
                console.log(response.data.data);

                $window.open('download/'+response.data.data, '_blank');
            },
            function(error){
                console.log(error);
            }
        )   
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
        $http.post('../serv/ws/cars.ws.php',{action:'getNotAssignedCars'}).then(
            function(response){
                console.log(response.data.data);
                $scope.data.cars = response.data.data;
            },
            function(error){
                console.log(error);
            }
        )
    }

    function checkForm(driver){
        if(!angular.isDefined(driver.lastname) || driver.lastname == ''){
            $scope.data.errorForm.lastname = true;
            return false;
        }else{
            $scope.data.errorForm.lastname = false;
        }

        if(!angular.isDefined(driver.firstname) || driver.firstname == ''){
            $scope.data.errorForm.firstname = true;
            return false;
        }else{
            $scope.data.errorForm.firstname = false;
        }

        if(!angular.isDefined(driver.tel) || driver.tel == ''){
            $scope.data.errorForm.tel = true;
            $scope.data.errorForm.text = "Veuillez saisir le téléphone du Chauffeur !";
            return false;
        }else{
            var reg = /^06[0-9]{8}$/.test(driver.tel);

            if(!reg){
                $scope.data.errorForm.tel = true;
                $scope.data.errorForm.text = "Veuillez saisir un format de téléphone valide comme 06XXXXXXXX !";
                return false;  
            }else{
                $scope.data.errorForm.tel = false;
            }
        }

        if(!angular.isDefined(driver.email) || driver.email == ''){
            $scope.data.errorForm.email = true;
            $scope.data.errorForm.text = "Veuillez saisir l'email du Chauffeur ! ";
            return false;
        }else{
            if(!validateEmail(driver.email)){
                $scope.data.errorForm.email = true;
                $scope.data.errorForm.text = "Veuillez saisir un format de mail Valide Comme : XXXX@XXX.XXX !";
                return false;
            }else{
                $scope.data.errorForm.email = false;
            }
        }

        if(!angular.isDefined(driver.driverlicense) || driver.driverlicense == ''){
            $scope.data.errorForm.driverlicense = true;
            return false;
        }else{
            $scope.data.errorForm.driverlicense = false;
        }

        if(!angular.isDefined(driver.cin) || driver.cin == ''){
            $scope.data.errorForm.cin = true;
            return false;
        }else{
            $scope.data.errorForm.cin = false;
        }

        if(!angular.isDefined(driver.carid) || driver.carid == ''){
            $scope.data.errorForm.carid = true;
            return false;
        }else{
            $scope.data.errorForm.carid = false;
        }

        return true;
    }

    function validateEmail(email) {
      var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(email);
    }

    // set sidebar closed and body solid layout mode
    $rootScope.settings.layout.pageContentWhite = true;
    $rootScope.settings.layout.pageBodySolid = false;
    $rootScope.settings.layout.pageSidebarClosed = false;

    $scope.deteteAttahcement = function(key) {
        if(angular.isDefined($scope.data.driver) && angular.isDefined($scope.data.driver.files)){
            delete $scope.data.driver.files[key];
        }
    }



    //Upload


        var uploader = $scope.uploader = new FileUploader({
            url: '../serv/upload.php'
        });

        // FILTERS

        uploader.filters.push({
            name: 'imageFilter',
            fn: function(item /*{File|FileLikeObject}*/, options) {
                var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
            }
        });

        // CALLBACKS

        uploader.onWhenAddingFileFailed = function(item /*{File|FileLikeObject}*/, filter, options) {
            //console.info('onWhenAddingFileFailed', item, filter, options);
        };
        uploader.onAfterAddingFile = function(fileItem) {
           // console.info('onAfterAddingFile', fileItem);
        };
        uploader.onAfterAddingAll = function(addedFileItems) {
           // console.info('onAfterAddingAll', addedFileItems);
        };
        uploader.onBeforeUploadItem = function(item) {
           // console.info('onBeforeUploadItem', item);
        };
        uploader.onProgressItem = function(fileItem, progress) {
           // console.info('onProgressItem', fileItem, progress);
        };
        uploader.onProgressAll = function(progress) {
           // console.info('onProgressAll', progress);
        };
        uploader.onSuccessItem = function(fileItem, response, status, headers) {
           // console.info('onSuccessItem', fileItem, response, status, headers);
           if(angular.isDefined(fileItem.file.name)){
                $scope.data.driver.files[fileItem.file.name] = "uploads/"+fileItem.file.name;
           }
        };
        uploader.onErrorItem = function(fileItem, response, status, headers) {
           // console.info('onErrorItem', fileItem, response, status, headers);
        };
        uploader.onCancelItem = function(fileItem, response, status, headers) {
           // console.info('onCancelItem', fileItem, response, status, headers);
        };
        uploader.onCompleteItem = function(fileItem, response, status, headers) {
           // console.info('onCompleteItem', fileItem, response, status, headers);
           //console.log(fileItem);
           
        };
        uploader.onCompleteAll = function() {
           // console.info('onCompleteAll');
        };

        //console.info('uploader', uploader);

    
});    