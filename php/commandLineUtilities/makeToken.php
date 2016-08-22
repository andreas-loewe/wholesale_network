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

$fourteenDays = new \DateTime("now");
$fourteenDays->add( new \DateInterval("P14D"));

$propertyDetail = <<<JSON
{"property":{"testing":true,"seller_email":"jaredclemence(test_0)@gmail.com","sqft":1690,"beds":2,"baths":{"full":2,"half":0,"three_quarter":0},"deal_type":"assignment","atn":"382-261-06-00-6","street":"3208 Crest Drive; Bakersfield, CA 93306","pricing":{"as_is":200000,"arv":220000,"repairs":10000},"videoURLs":"","assignment_detail":{"fee":5000,"contract_price":190000,"closing_date":"1981-12-06T08:00:00.000Z","escrow":{"name":"Ticor Title","url":"http://ticortitle.com"}},"double_close_detail":{"desired_price":0,"alternative_action":"","bottom_price":0},"about_seller":"I have had five wholesale deals that successfully closed in the last year. Three have been sold through this website. I am proud to provide bids instead of estimates for all repairs on all my deals.","description":"3 Contractor bids available for detailed repairs. Total repairs cost $10k. Estimated ARV is $220,000. Must sell fast."}}
JSON;

$propertyData = \json_decode($propertyDetail);
$property = $propertyData->property;
$property = \model\DirtyProperty::construct($property);
$property->assignment_detail->closing_date = $fourteenDays->format("c");

$manager = new \business\DealManager();
$offer = $manager->constructOffer($property);
     
$command = $argv[0];
$message = $argv[1];

if( isset( $argv[2] ) ){
    switch( $argv[2] ){
        case 1:
            \business\states\offer\Unconfirmed::setStateOnOffer($offer);
            break;
        case 2:
            $offer->setConfirmationTime(new \DateTime("now"));
            \business\states\offer\Confirmed::setStateOnOffer($offer);
            break;
        case 3:
            \business\states\offer\Paid::setStateOnOffer($offer);
            break;
        case 4:
            \business\states\offer\Advertized::setStateOnOffer($offer);
            break;
    }
}

$offer->setPrice(3);
$offerData = \business\storage\OfferStorage::create();
$offerData->store( $offer );

$buyer = new \model\user\Buyer();
$buyer->setEmail("jaredclemence(test_2)@gmail.com");
$buyerData = \business\storage\BuyerStorage::create();
$buyerData->store($buyer);

$offerId = $offer->getId();

$token = \model\security\Token::create(48*60, [
    "signal"=>$message,
    "offerId"=>$offerId,
    "successUrl"=>"test.html",
    "buyerId"=>$buyer->getId()
]);
$tokenData = \business\storage\TokenStorage::create();
$tokenData->store( $token );

$tokenId = $token->getId();
$tokenUrl = $token->getUrl();

var_dump(
        [
            'id' =>$tokenId,
            'url'=>$tokenUrl
        ]
        );