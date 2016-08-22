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


angular.module('WholesaleApp').controller('BuyerRegistration',
        [
            '$http', '$scope', '$timeout', '$routeParams',
            function ($http, $scope, $timeout, $routeParams) {
                var scope = $scope;
                scope.errorText = typeof $routeParams.errorText == "undefined" ? null : $routeParams.errorText;
                scope.cash = null;
                scope.email = "";
                scope.recaptchaComplete = false;
                scope.currentPage = window.location.href;
                scope.submitted = false;
                scope.submit = function(){
                    scope.submitted = true;
                    $( "form" )[0].submit();
                }
                function displayRecaptcha(){
                    var element = document.getElementById( "recaptchaDiv" );
                    if( typeof grecaptcha != "object" || element == null ){
                        $timeout( displayRecaptcha, 100, false );
                    }else{
                        var options = {
                            sitekey: "6LczFygTAAAAAE7zfiPPc2Dv11gDpht29pXNFoRh",
                            callback: registerResponse,
                            "expired-callback": registerExpiration
                        };
                        grecaptcha.render("recaptchaDiv", options);
                    }
                }
                function registerResponse(){
                    scope.recaptchaComplete = true;
                    scope.$apply();
                }
                function registerExpiration(){
                    scope.recaptchaComplete = false;
                    scope.$apply();
                }
                displayRecaptcha();
                $scope.email = "";
                $scope.cash_limit = "";
                $scope.recaptcha_response = "";
                $scope.show_recaptcha = false;
            }
        ]
        );