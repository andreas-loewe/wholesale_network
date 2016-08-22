<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\behavior\billing;

/**
 * Description of FreeAccountHolder
 *
 * @author jrc
 */
class FreeAccountHolder extends \business\behavior\billing\BillingBehavior {

    public static function setForUser(\model\user\User $user) {
        parent::__setBehaviorForUser($user, __CLASS__);
    }

    public function getPriceToSellOffer(\model\Offer $offer) {
        $price = $offer->getPrice();
        if ($price === null) {
            //if price not yet set
            $settings = new \system\Settings();
            $price = $settings->read("default_offer_price", "Pricing", "");
            $minInterest = $settings->read("min_buyers_count", "Pricing");
            $minCashAvailableMultiplier = $settings->read("min_cash_available_multiplier", "Pricing");
            //if number of sellers is less than 20 people, no charge
            $numSellersInterested = $this->determineInterest($offer, $buyersList);
            if ($numSellersInterested < $minInterest) {
                $price = 0;
            }
            $cashAvailable = $this->countCashAvailable($buyersList);
            //if number of dollars available is less than 3 * the purchase price, no charge

            if ($cashAvailable < $minCashAvailableMultiplier * 1) {
                $price = 0;
            }
            //else return default price
            $offer->setPrice( $price );
        }
        return $price;
    }

    protected function determineInterest($offer) {
        return 0;
    }

}
