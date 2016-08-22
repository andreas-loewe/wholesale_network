<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\states\offer;

/**
 * Description of Advertized
 *
 * @author jrc
 */
class Advertized extends \business\states\offer\OfferState {

    public static function setStateOnOffer(\model\Offer $offer) {
        $emails = $offer->getEmailCount();
        $offer->resetEmailCount();
        $offer->numberOfEmailsToBuyers += $emails;
        $offer->setAdvertisingEndDate();
        $offer->setState( __CLASS__ );
    }

    public function advanceState($useTime = null) {
        $offer = $this->getOffer();
        $date = $offer->getAdvertisingEndDate();
        $now = $useTime instanceof \DateTime ? $useTime : new \DateTime("now");
        $interval = $now->diff( $date );
        $days = $interval->days;
        $limit = \system\Settings::read("keep_open_X_days_after_conclusion", "State Transition Settings");
        if( $days > $limit ){
            \business\states\offer\Expired::setStateOnOffer($offer);
        }
        return false;
    }

    public function performStateAction($useTime = null) {
        /*
         * do nothing.... in this state, we wait to collect responses
         * we transition only after all responses are collected.
         * 
         * We also allow the seller to view the offer status.
         */
        return false;
    }

}
