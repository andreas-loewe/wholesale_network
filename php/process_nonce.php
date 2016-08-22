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


/**
 * This file is called after the buyer has indicated that payment should be processed for
 * a pending offer.
 */
require_once 'autoload.php';

$response = new \stdClass();
$response->error = null;
$response->errorno = null;
$response->data = null;

$offerId = null;
$nonce = null;

$in = \file_get_contents('php://input');
$inputs = \json_decode($in);

try {
    $nonce = null;
    $offerId = null;
    $amount = null;
    $type = null;
    $details = null;
    if (!isset($inputs->offerId)) {
        throw new \Exception("Offer Id is not provided.", 0x01);
    } else {
        $offerId = $inputs->offerId;
    }
    if (!isset($inputs->nonce)) {
        throw new \Exception("Nonce is not provided.", 0x02);
    } else {
        $nonce = $inputs->nonce;
    }
    if (!isset($inputs->type)) {
        throw new \Exception("Type is not provided.", 0x03);
    } else {
        $type = $inputs->type;
    }
    if (!isset($inputs->details)) {
        throw new \Exception("Details are not provided.", 0x04);
    } else {
        $details = $inputs->details;
    }
    if (!isset($inputs->amount_to_pay)) {
        throw new \Exception("Amount_to_pay is not set.", 0x05);
    } else {
        $amount = $inputs->amount_to_pay;
    }

    $response->data = $inputs;
    $offerData = \business\storage\OfferStorage::create();
    if (!\preg_match('/fake_.*_offer/', $offerId)) {

        $offer = $offerData->getById($offerId);
        if ($offer === null) {
            throw new \Exception("OfferId ($offerId) fails to link to a stored offer record.", 0x05);
        }
        /* @var $offer \model\Offer */
        $email = $offer->getSellerEmail();
    } else {
        $email = "jared(test_1234)@phoenixhomesltd.com";
    }

    $date = new \DateTime("now");
    $ts = $date->format("YmdHis");
    $invoiceId = $offerId . "_" . $ts;
    $bt = new \braintree\Braintree();

    $response->data = new \stdClass();
    $response->data->chargeSuccess = false;
    $response->data->remainingBalance = $amount;
    $transaction = $bt->makeSale($invoiceId, $email, $amount, $nonce, $error, $errorMessage);
    if ($transaction) {
        /* @var $transaction \Braintree\Transaction */
        switch ($transaction->status) {
            case 'approved':
            case 'submitted_for_settlement':
                if ($offer) {
                    $offer->addPayment($amount, $transaction->id);
                    $response->data->remainingBalance = \max( $offer->getPrice() - $offer->getMoneyPaid(), 0 );
                }
                $response->data->chargeSuccess = true;
                break;
            default:
                throw new \Exception("Payment not approved. Payment auth returned with the message: " . $transaction->status, 0x0A);
                break;
        }
    } else {
        if ($errorMessage) {
            throw new \Exception("Payment processing error occured: $errorMessage. No charge has been made to your account. Please try again later.", 0x0B);
        } else {
            throw new \Exception("Unkown error. No charge has been made to your account.", 0x0C);
        }
    }
} catch (\Exception $e) {
    $response->error = $e->getMessage();
    $response->errorno = $e->getCode();
    $response->input = $inputs;
}
echo \json_encode($response);

