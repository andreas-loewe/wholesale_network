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


angular.module('WholesaleApp').controller('PaymentController',
        [
            '$routeParams',
            '$scope',
            '$http',
            '$location',
            function ($routeParams, $scope, $http, $location) {
                this.offerId = $routeParams.offerId;
                this.clientAuth = null;
                this.price = null;
                this.threeDSecure = null;
                var http = $http;
                this.paypal = null;
                var location = $location;
                var scope = $scope;
                this.sendPayloadToServer = function(payload) {
                    //we may choose to allow partial payments in the future, so we should send 
                    //an amount to pay to the server.
                    payload['amount_to_pay'] = scope.price;
                    payload['offerId'] = controller.offerId;
                    var promise = $http.post('php/process_nonce.php', payload);
                    promise.then(
                            function Success(xhttpdata) {
                                var serverResponce = xhttpdata.data.data;
                                var errorMessage = xhttpdata.data.error;
                                var errorNumber = xhttpdata.data.errorno;
                                scope.errorMessage = null;
                                scope.errorNumber = null;
                                if (errorMessage) {
                                    scope.errorMessage = errorMessage;
                                    scope.errorNumber = errorNumber;
                                }
                                if (serverResponce) {
                                    if( serverResponce.chargeSuccess && serverResponce.remainingBalance <= 0 ){
                                        location.path( '/offer/' + controller.offerId + "/paymentSuccessful" );
                                    }else{
                                        scope.price = serverResponce.remainingBalance;
                                    }
                                }
                            },
                            function Error(xhttpdata) {
                                //handle error
                                console.error(xhttpdata);
                            }
                    );
                }
                localBrainTree.credit_card.setPayloadFunction(this.sendPayloadToServer);
                var controller = this;
                $http.post('php/get_braintree_data.php', {offerId: this.offerId}).then(
                        function (xhttpData) {
                            //success
                            var responseObj = xhttpData.data;
                            if (responseObj.error) {
                                console.error("Error(" + responseObj.errorno + "): " + responseObj.error);
                            }
                            if (responseObj.data) {
                                var price = null;
                                var clientAuth = null;
                                if (responseObj.data.price) {
                                    price = responseObj.data.price;
                                }
                                if (responseObj.data.clientAuth) {
                                    clientAuth = responseObj.data.clientAuth;
                                }
                                if (price != null && clientAuth != null) {
                                    controller.setPriceAndAuth(price, clientAuth);
                                }
                            }

                        },
                        function (xhttpData) {
                            //error
                        }
                );
                this.setPriceAndAuth = function (price, clientAuth) {
                    scope.price = price;
                    if( price <= 0 ){
                        location.path('/offer/' + controller.offerId + '/paymentSuccessful' );
                        return;
                    }
                    localBrainTree.credit_card.initialize(clientAuth);
                    initializePayPalObject(clientAuth, "paypal_button", "paypal_form");
                    initialize3dSecureObject(clientAuth);
                }
                function initializePayPalObject(auth, buttonId, paypalFormId) {
                    var paypalButton = document.getElementById(buttonId);
                    var paypalForm = document.getElementById(paypalFormId);

                    braintree.client.create({
                        // Replace this with your own authorization.
                        authorization: auth
                    }, function (clientErr, clientInstance) {

                        if (clientErr) {
                            console.error(clientErr);
                            return;
                        }

                        braintree.paypal.create({client: clientInstance}, function (paypalErr, paypalInstance) {

                            if (paypalErr) {
                                console.error(paypalErr);
                                return;
                            }
                            controller.paypal = paypalInstance;
                            initializePayPalButtonById(buttonId);
                        });
                        paypalButton.removeAttribute("disabled");
                    }, false);
                }
                function initializePayPalButtonById(buttonId) {
                    var button = document.getElementById(buttonId);
                    var paypalInstance = controller.paypal;
                    button.addEventListener('click', function () {
                        // Disable the button so that we don't attempt to open multiple popups.
                        button.setAttribute('disabled', 'disabled');
                        // Because PayPal tokenization opens a popup, this must be called
                        // as a result of a user action, such as a button click.
                        paypalInstance.tokenize({
                            flow: 'vault' // Required
                                    // Any other tokenization options
                        }, function (tokenizeErr, payload) {
                            button.removeAttribute('disabled');
                            if (tokenizeErr) {
                                // Handle tokenization errors or premature flow closure
                                debugger;
                                switch (tokenizeErr.code) {
                                    case 'PAYPAL_POPUP_CLOSED':
                                        console.error('Customer closed PayPal popup.');
                                        break;
                                    case 'PAYPAL_ACCOUNT_TOKENIZATION_FAILED':
                                        console.error('PayPal tokenization failed. See details:', tokenizeErr.details);
                                        break;
                                    case 'PAYPAL_FLOW_FAILED':
                                        console.error('Unable to initialize PayPal flow. Are your options correct?', tokenizeErr.details);
                                        break;
                                    default:
                                        console.error('Error!', tokenizeErr);
                                }
                            } else {
                                controller.sendPayloadToServer(payload);
                                // Submit payload.nonce to your server
                            }
                        });
                    });
                }

                function initialize3dSecureObject(clientAuth) {
                    braintree.client.create({authorization: clientAuth}, function (err, client) {
                        if (err) {
                            console.error(err);
                            return;
                        }
                        if( typeof braintree.threeDSecure == "undefined" ){
                            return;
                        }
                        braintree.threeDSecure.create({
                            client: client
                        }, function(err,threeDSecure){
                            if( err ){
                                console.error( err );
                                return;
                            }
                            controller.threeDSecure = threeDSecure;
                        });
                    });
                }

                function verifyCard(nonce, amount) {
                    var my3DSContainer;
                    var threeDSecure = controller.threeDSecure;
                    if (threeDSecure === null)
                        return false;
                    threeDSecure.verifyCard({
                        nonce: nonce,
                        amount: amount,
                        addFrame: function (err, iframe) {
                            // Set up your UI and add the iframe.
                            my3DSContainer = document.createElement('div');
                            my3DSContainer.appendChild(iframe);
                            document.body.appendChild(my3DSContainer);
                        },
                        removeFrame: function () {
                            // Remove UI that you added in addFrame.
                            document.body.removeChild(my3DSContainer);
                        }
                    }, function (err, payload) {
                        if (err) {
                            console.error(err);
                            return;
                        }

                        if (payload.liabilityShifted) {
                            // Liablity has shifted
                            submitNonceToServer(payload.nonce);
                        } else if (payload.liabilityShiftPossible) {
                            // Liablity may still be shifted
                            // Decide if you want to submit the nonce
                        } else {
                            // Liablity has not shifted and will not shift
                            // Decide if you want to submit the nonce
                        }
                    });
                    return true;
                }

            }
        ]);