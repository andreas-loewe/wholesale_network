<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\states\offer;

/**
 * Description of Expired
 *
 * @author jrc
 */
class Expired extends \business\states\offer\OfferState{
    public static function setStateOnOffer(\model\Offer $offer) {
        $initialState = $offer->getState();
        $emailCount = $offer->getEmailCount();
        $offer->resetEmailCount();
        
        switch( $initialState ){
            case 'business\states\offer\Unconfirmed':
            case 'business\states\offer\Confirmed':
            case 'business\states\offer\Unknown_Future':
                $offer->numberOfEmailsToBuyers += $emailCount;
                break;
            default:
                $offer->numberOfEmailsToSeller += $emailCount;
                break;
        }
        $offer->setState(__CLASS__);
        //this takes the offer out of the loop for cycling offers.
        $offer->setActiveState(false);
        
    }

    public function advanceState($useTime = null) {
        //there is no advancing from here
    }

    public function performStateAction($useTime = null) {
        //there is no action here
    }

}
