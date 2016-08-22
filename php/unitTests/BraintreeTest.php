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
require_once '../autoload.php';

/**
 * Description of BraintreeTest
 *
 * @author jrc
 */
class BraintreeTest extends \PHPUnit_Framework_TestCase {

    static $count;

    static public function setUpBeforeClass() {
        self::$count = 0;
    }

    public function testLoad() {
        $bt = new \braintree\Braintree();
        $tok = $bt->getClientToken();
        $this->assertNotNull($tok, "The client id is not null.");
    }

    public function testCustomer() {
        $bt = new \braintree\Braintree();
        $customer = $bt->createCustomer("jaredclemence(test_10)@gmail.com");
        //$this->assertEquals('60796268', $customer->id, "The customer id has not changed since last run.");
        $this->assertTrue($customer instanceof \Braintree\Customer);
    }

    /**
     * @dataProvider nonceProvider
     */
    public function testGoodSale($nonce, $trasactionObjectExpected) {
        $bt = new \braintree\Braintree();
        //$bt->setPaymentNonce($nonce);
        $i = self::$count;
        self::$count++;
        $email = "j_test_$i@phoenixhomesltd.com";
        $invoiceId = "order" . rand(1000, 2000);
        $transaction = $bt->makeSale($invoiceId, $email, 3.00, $nonce, $error, $errorMessage);
        if ($trasactionObjectExpected) {
            $this->assertEquals('Braintree\Transaction', \get_class($transaction), "makeSale should return a Transaction.");
            $this->assertEquals('Approved', $transaction->processorResponseText, "The processor response text is 'Approved'");
        } else {
            $this->assertNull($transaction, "Payment method is not accepted in account settings. If this error is triggered, verify settings or decide to accept payment.");
        }
    }
    
    /**
     * @dataProvider nonceProvider
     */
    public function testDeclinedSale($nonce, $trasactionObjectExpected) {
        $bt = new \braintree\Braintree();
        //$bt->setPaymentNonce($nonce);
        $i = self::$count;
        self::$count++;
        $email = "j_test_$i@phoenixhomesltd.com";
        $invoiceId = "order" . rand(1000, 2000);
        $transaction = $bt->makeSale($invoiceId, $email, 2000, $nonce, $error, $errorMessage);
        if ($trasactionObjectExpected) {
            $status = $transaction->status;
            $this->assertEquals( 'processor_declined', $status, "The test is designed to force a declined transaction. Status does not match the processor_declined status." );
            $this->assertTrue( $transaction instanceof \Braintree\Transaction, "A transaction object is returned even when a transaction is rejected.");
        } else {
            $this->assertNull($transaction, "Payment method is not accepted in account settings. If this error is triggered, verify settings or decide to accept payment.");
        }
    }
    
    /**
     * @dataProvider nonceProvider
     */
    public function testGatewayDeclined($nonce, $trasactionObjectExpected) {
        $bt = new \braintree\Braintree();
        //$bt->setPaymentNonce($nonce);
        $i = self::$count;
        self::$count++;
        $email = "j_test_$i@phoenixhomesltd.com";
        $invoiceId = "order" . rand(1000, 2000);
        $transaction = $bt->makeSale($invoiceId, $email, 5001, $nonce, $error, $errorMessage);
        if ($trasactionObjectExpected) {
            $status = $transaction->status;
            $this->assertEquals( 'gateway_rejected', $status, "The test is designed to force a declined transaction. Status does not match the processor_declined status." );
            $this->assertTrue( $transaction instanceof \Braintree\Transaction, "A transaction object is returned even when a transaction is rejected.");
        } else {
            $this->assertNull($transaction, "Payment method is not accepted in account settings. If this error is triggered, verify settings or decide to accept payment.");
        }
    }

    public function nonceProvider() {
        return [
            'A valid nonce that can be used to create a transaction' =>
            ['fake-valid-nonce', true],
            'A valid nonce containing no billing address information' => ['fake-valid-no-billing-address-nonce', true],
            'A nonce representing a valid Visa card request' => ['fake-valid-visa-nonce', true],
            'A nonce representing a valid American Express card request' => ['fake-valid-amex-nonce', true],
            'A nonce representing a valid Mastercard request' => ['fake-valid-mastercard-nonce', true],
            'A nonce representing a valid Discover card request' => ['fake-valid-discover-nonce', true],
            'A nonce representing a valid JCB card request' => ['fake-valid-jcb-nonce', true],
            'A nonce representing a valid Maestro card request' => ['fake-valid-maestro-nonce', false],
            'A nonce representing a valid Diners Club card request' => ['fake-valid-dinersclub-nonce', true],
            'A nonce representing a valid prepaid card request' => ['fake-valid-prepaid-nonce', true],
            'A nonce representing a valid commercial card request' => ['fake-valid-commercial-nonce', true],
            'A nonce representing a valid Durbin regulated card request' => ['fake-valid-durbin-regulated-nonce', true],
            'A nonce representing a valid healthcare card request' => ['fake-valid-healthcare-nonce', true],
            'A nonce representing a valid debit card request' => ['fake-valid-debit-nonce', true],
            'A nonce representing a valid payroll card request' => ['fake-valid-payroll-nonce', true],
            'A nonce representing a request for a valid card with no indicators' =>
            ['fake-valid-no-indicators-nonce', true],
            'A nonce representing a request for a valid card with unknown indicators' =>
            ['fake-valid-unknown-indicators-nonce', true],
            'A nonce representing a request for a valid card issued in the USA' =>
            ['fake-valid-country-of-issuance-usa-nonce', true],
            'A nonce representing a request for a valid card issued in Canada' =>
            ['fake-valid-country-of-issuance-cad-nonce', true],
            "A nonce representing a request for a valid card with the message 'Network Only' from the issuing bank" =>
            ['fake-valid-issuing-bank-network-only-nonce', true],
            'A nonce representing an Android Pay request' =>
            ['fake-android-pay-nonce', true],
            'A nonce representing an Android Pay Visa request' =>
            ['fake-android-pay-visa-nonce', true],
            'A nonce representing an Android Pay Mastercard request' =>
            ['fake-android-pay-mastercard-nonce', true],
            'A nonce representing an Android Pay American Express request' =>
            ['fake-android-pay-amex-nonce', true],
            'A nonce representing an Android Pay Discover request' =>
            ['fake-android-pay-discover-nonce', true],
            'A nonce representing an Apple Pay request for an American Express card number' =>
            ['fake-apple-pay-amex-nonce', false],
            'A nonce representing an Apple Pay request for an Visa card number' =>
            ['fake-apple-pay-visa-nonce', false],
            'A nonce representing an Apple Pay request for an MasterCard card number' =>
            ['fake-apple-pay-mastercard-nonce', false],
            'A nonce representing a Coinbase account' => ['fake-coinbase-nonce', false],
            'A nonce representing an unvaulted PayPal account that a user has '
            . 'authorized for the one time payment flow. You can use any email '
            . 'address and password you like when testing PayPal in Sandbox, '
            . 'but there are some restrictions to consider if doing end-to-end '
            . 'testing.' =>
            ['fake-paypal-one-time-nonce', true],
            'A nonce representing an unvaulted PayPal account that a user has '
            . 'authorized for the future payment flow. You can use any email '
            . 'address and password you like when testing PayPal in Sandbox, '
            . 'but there are some restrictions to consider if doing end-to-end '
            . 'testing.' =>
            ['fake-paypal-future-nonce', true],
            'A nonce representing a request for a Visa card that was declined '
            . 'by the processor' =>
            ['fake-processor-declined-visa-nonce', true],
            'A nonce representing a request for a Mastercard that was declined '
            . 'by the processor' =>
            ['fake-processor-declined-mastercard-nonce', true],
            'A nonce representing a request for a American Express card that was'
            . ' declined by the processor' =>
            ['fake-processor-declined-amex-nonce', true],
            'A nonce representing a request for a Discover card that was '
            . 'declined by the processor' =>
            ['fake-processor-declined-discover-nonce', true],
            'A nonce representing a request for a JCB card that was declined by '
            . 'the processor' =>
            ['fake-processor-failure-jcb-nonce', true],
            'A nonce representing a Luhn-invalid card' =>
            ['fake-luhn-invalid-nonce', false],
            'A nonce that has already been consumed' =>
            ['fake-consumed-nonce', false],
            'A fraudulent nonce' => ['fake-gateway-rejected-fraud-nonce', true]
        ];
    }

    public static function tearDownAfterClass() {
        $collection = \Braintree_Customer::search([
                    \Braintree_CustomerSearch::email()->contains('test')
        ]);
        $bt = new \braintree\Braintree();
        foreach ($collection as $customer) {
            $bt->deleteCustomer($customer);
        }
    }

}
