<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\behavior\billing;

/**
 * Description of MonthlySubscriber
 *
 * @author jrc
 */
class MonthlySubscriber extends \business\behavior\billing\FreeAccountHolder {
    /* @var \model\User */
    protected $user;
    
    public static function getBillingBehaviorForUser(\model\user\User $user) {
        $className = $user->getBillingBehavior();
        return new $className( $user );
    }

    public static function setForUser(\model\user\User $user) {
        $user->setBillingBehavior( __CLASS__ );
    }

    public function getPriceToSellOffer(\model\Offer $offer) {
        $defaultPrice = parent::getPriceToSellOffer($offer);
        if( $defaultPrice > 0 ){
            //count offers made since begin of billing period that would not have been free
            $numOfPaidOffersMadeSincePeriodStart = $this->countNonFreeOffersThisMonth( $offer->getSellerEmail() );
            if( $numOfPaidOffersMadeSincePeriodStart < $accountLimit ){
                $price = 0;
            }
        }
    }

}
