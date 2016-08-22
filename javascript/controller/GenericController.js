/* 
 * The MIT License
 *
 * Copyright 2016 jrc.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


angular.module('WholesaleApp').controller('GenericController', 
[
    '$routeParams', 
    '$scope',
    '$http',
    '$location',
    function($routeParams, $scope, $http, $location){
        var scope = $scope;
        var location = $location;
        $scope.errorText = null;
        $scope.errorNumber = null;
        for( var varname in $routeParams ){
            var value = $routeParams[varname];
            if( varname.indexOf("_") !== -1 ){
                var parts = varname.split("_",2);
                varname = parts[0];
                var type = parts[1];
                switch( type ){
                    case "date":
                        value = new Date( value );
                        break;
                }
            }
            $scope[varname] = value;
        }
        if( typeof $scope.tokenId != "undefined" ){
            var tokenId = $scope.tokenId;
            passTokenToServerForProcessing( tokenId );
        }
        function passTokenToServerForProcessing( tokenId ){
            $http.post('php/process_token.php',{tokenId:tokenId}).then(
                    handleSuccess,
                    processCommunicationError
                    );
        }
        function handleSuccess( xhttpData ){
            var serverResponse = xhttpData.data;
            scope.errorText = serverResponse.error;
            scope.errorNumber = serverResponse.errorno;
            if( typeof serverResponse.data != "undefined" && typeof serverResponse.data.loadUrlPath != "undefined" ){
                var newPath = xhttpData.data.data.loadUrlPath;
                location.path( newPath );
            }
        }
        function processCommunicationError( xhttpData ){
            switch( xhttpData.status ){
                case 404:
                    alert( "The token processing page is unreachable. Please try again.");
                    break;
                default:
                    alert( "An unknown error is found, please start the debugger console.");
                    console.log( xhttpData );
                    debugger;
            }
        }
    }
])