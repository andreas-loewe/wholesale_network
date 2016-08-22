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


angular.module("WholesaleApp").config(function ($routeProvider, $locationProvider) {
    $routeProvider
            .when('/tokenExpired/:expireDate_date', {
                templateUrl: 'templates/pages/expired_token.html',
                controller: 'GenericController'
            })
            .when('/seller/registration', {
                templateUrl: 'templates/pages/buyer.html',
                controller: 'BuyerRegistration'
            })
                    
            .when('/seller/registration/error/:errorText', {
                templateUrl: 'templates/pages/buyer.html',
                controller: 'BuyerRegistration'
            })
            .when('/offer/:offerId', {})
            .when('/offer/:offerId/paymentSuccessful', {
                templateUrl: 'templates/pages/payment_complete.html',
                controller: 'GenericController'
            })
            .when('/user/:userId_base64', {redirectTo: '/user/:userId_base64/propertyFilters'})
            .when('/user/:userId_base64/propertyFilters', {})
            .when('/token_parser/:tokenId', {
                templateUrl: 'templates/pages/blank.html',
                controller: 'GenericController'
            })
            .when('/offer/:offerId/payment', {
                templateUrl: 'templates/pages/payment.html',
                controller: 'PaymentController',
                controllerAs: 'controle'
            })

    // configure html5 to get links working on jsfiddle
    //$locationProvider.html5Mode(true);
});