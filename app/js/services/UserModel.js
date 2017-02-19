angular.module('MetronicApp').factory('UserModel', function() {
    
    var user = {};
    //user.infos = {};

    user.getInfo = function(token){
        return token;
    };

    return user;

});