<?php

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

namespace braintree;

/**
 * Description of Braintree
 *
 * @author jrc
 */
class Braintree {

    private $payment_nonce;

    public function __construct() {
        $this->payment_nonce = null;
        $this->initialize_environment();
    }

    public function getClientToken() {

        $clientToken = \Braintree_ClientToken::generate();
        return $clientToken;
    }

    private function initialize_environment() {
        $environment = \system\Settings::read("environment", "Braintree");
        $merchId = \system\Settings::read("merchant_id", "Braintree");
        $publicKey = \system\Settings::read("public_key", "Braintree");
        $privateKey = \system\Settings::read("private_key", "Braintree");
        \Braintree_Configuration::environment($environment);
        \Braintree_Configuration::merchantId($merchId);
        \Braintree_Configuration::publicKey($publicKey);
        \Braintree_Configuration::privateKey($privateKey);
    }

    public function setPaymentNonce($nonce) {
        $this->payment_nonce = $nonce;
    }

    /**
     * 
     * @param \Braintree\Result\Successful $amount
     */
    public function createTransaction($amount) {
        $result = \Braintree_Transaction::sale([
                    'amount' => $amount,
                    'paymentMethodNonce' => $this->payment_nonce,
                    'options' => [
                        'submitForSettlement' => True
                    ]
        ]);

        /* @var $result Braintree\Result\Successful */
        return $result;
    }

    public function makeSale($invoiceId, $email, $amount, $nonce, &$error, &$errorMessage) {
        $customer = $this->createCustomer($email);
        $customerId = $customer->id;
        $result = \Braintree_Transaction::sale([
                    'customerId' => $customerId,
                    'amount' => $amount,
                    'paymentMethodNonce' => $nonce,
                    'orderId' => $invoiceId,
                    'options' => [
                        'submitForSettlement' => True,
                        'storeInVault' => True
                    ]
        ]);
        $transaction = null;
        $errorMessage = isset($result->message) ? $result->message : null;
        if ($result instanceof \Braintree\Result\Error) {
            foreach ($result->errors->deepAll() AS $errorObj) {
                $error = $errorObj;
            }
        }
        if( $result->transaction instanceof \Braintree\Transaction ){
            $transaction = $result->transaction;
        }
        return $transaction;
    }

    public function createCustomer($email, $first = null, $last = null) {
        $collection = \Braintree_Customer::search([
                    \Braintree_CustomerSearch::email()->is($email)
        ]);
        /* @var $collection \Braintree\ResourceCollection */
        /* @var $customer \Braintree\Customer */
        $customer = null;
        foreach ($collection as $aCustomer) {
            /* @var $aCustomer \Braintree\Customer */
            if ($customer == null) {
                $customer = $aCustomer;
            } else {
                $deleteCustomer = null;
                if ($aCustomer->id < $customer->id) {
                    $deleteCustomer = $customer;
                    $customer = $aCustomer;
                }
                if ($deleteCustomer !== null) {
                    $this->deleteCustomer($deleteCustomer);
                }
            }
        }
        if ($customer == null) {
            $customerData = [];
            if ($email) {
                $customerData['email'] = $email;
            }
            if ($first) {
                $customerData['first'] = $first;
            }
            if ($last) {
                $customerData['last'] = $last;
            }

            $result = \Braintree_Customer::create($customerData);
            if ($result->success) {
                $customer = $result->customer;
            }
        }
        return $customer;
    }

    public function deleteCustomer(\Braintree\Customer $customer) {
        $result = \Braintree_Customer::delete($customer->id);
        return $result->success;
    }

}
