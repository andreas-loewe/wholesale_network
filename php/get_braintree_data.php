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

require_once 'autoload.php';

$response = new \stdClass();
$response->error = null;
$response->errorno = null;
$response->data = null;

$offerId = null;

//get offer id from $_POST or php://input
if( $_POST['offerId'] ){
    $offerId = $_POST['offerId'];
}else{
    $in = \file_get_contents('php://input');
    $dataIn = \json_decode($in);
    if( isset( $dataIn->offerId ) ){
        $offerId = $dataIn->offerId;
    }
}
$response->offerId = $offerId;
try{
    if( $offerId === null ) throw new \Exception("Bad offerId. OfferId is not priveded.", 0x01);
    $bt = new \braintree\Braintree();
    $clientToken = $bt->getClientToken();
    
    //if offer id is 'fake_X_offer', return fake result
    $price = null;
    if( preg_match( '/fake_(\d+(\.\d+)?)_offer/', $offerId, $matches ) ){
        $price = $matches[1];
    }else{
        //load offer and get price
        $offerData = \business\storage\OfferStorage::create();
        $offer = $offerData->getById($offerId);
        /* @var $offer \model\Offer */
        if( $offer === null ){
            throw new \Exception("Bad offerId. Offer not found.", 0x02);
        }
        $tempState = new \business\states\offer\Confirmed($offer);
        $price = $tempState->determineBalanceOwed($offer);
        $price = \max( $price, 0 );
    }
    
    $response->data = new \stdClass();
    $response->data->price = $price;
    $response->data->clientAuth = $clientToken;
    
}catch( \Exception $e ){
    $response->error = $e->getMessage();
    $response->errorno = $e->getCode();
}
echo \json_encode($response);
