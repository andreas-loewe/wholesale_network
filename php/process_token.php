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
$instring = \file_get_contents('php://input');
if( count( $_POST ) == 0 && $instring != "" ){
    $obj = \json_decode($instring);
    foreach( $obj as $attr => $value ){
        $_POST[$attr] = $value;
    }
}

require_once 'autoload.php';

$response = new \stdClass();
$response->error = null;
$response->errorno = null;
$response->data = new \stdClass();
try{
    \extract( $_POST );
    if( !isset( $tokenId ) ){
        throw new \Exception("'tokenId' is a required parameter.", 0x01);
    }
    $dataStorage = new \business\storage\TokenStorage();
    $token = $dataStorage->getById($tokenId);
    if( !\is_object($token)){
        throw new \Exception("'tokenId' does not link to a valid object.", 0x02);
    }
    /* @var $token \model\security\Token */
    $expireDate = $token->getExpireDate();
    $now = new \DateTime("now");
    if( $now > $expireDate ){
        $response->data->expiredOn = $expireDate->format("Y-m-d H:i:s");
        throw new \Exception("Token expired on {$response->data->expiredOn}.", 0x03);
    }
    $tokenData = $token->getData();
    \extract( $tokenData );
    if( $tokenData === null ){
        throw new \Exception( "TokenData is null.", 0x04);
    }
    $offer = null;
    $user = null;
    if( isset( $offerId ) ){
        $dataStorage = \business\storage\OfferStorage::create();
        $offer = $dataStorage->getById($offerId);
        if( !( $offer instanceof \model\Offer ) ){
            throw new \Exception("OfferId links to a bad offer.", 0x05);
        }
    }
    if( isset( $buyerId ) ){
        $dataStorage = \business\storage\BuyerStorage::create();
        $user = $dataStorage->getById($buyerId);
        if( !($user instanceof \model\user\Buyer ) ){
            throw new \Exception("BuyerId links to a bad buyer object.", 0x06);
        }
    }
    if( isset( $signal ) ){
        switch( $signal ){
            case 'cash_buyer_responds_to_offer_email':
            case 'confirm_seller_email':
            case 'load_payment_page':
                if( !isset( $successUrl ) ){
                    throw new \Exception("Server-side error: 'successUrl' is not included in token data packet.", 0x07);
                }
                //offer related items
                if( !isset( $offer ) ){
                    throw new \Exception("Server-side error: 'offerId' must be set in the token data packet.", 0x08);
                }
                $success = $offer->respondToEvent($signal, $user);
                if( !$success ){
                    throw new \Exception("Token signal no longer has an offer state that will process it.", 0x09);
                }
                $response->data->loadUrlPath = $successUrl;
                break;
            case 'update_property_filters':
                //user related item
                $response->data->loadUrlPath = ( "/user/" . \base64_encode($user->getId()) );
                break;
            case 'remove_from_buyer_list':
                //user related items
                $dataStorage = \business\storage\BuyerStorage::create();
                $dataStorage->deleteObj($user);
                $response->data->loadUrlPath = "/user/" . \base64_encode($user->getId()) . "/goodbye";
                break;
        }
    }
} catch (\Exception $ex) {
    $response->error = $ex->getMessage();
    $response->errorno = $ex->getCode();
}
echo \json_encode($response);