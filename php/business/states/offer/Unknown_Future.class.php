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
class Unknown_Future extends \business\states\offer\OfferState {

    public static function setStateOnOffer(\model\Offer $offer) {
        $initialState = $offer->getState();
        $emailCount = $offer->getEmailCount();
        $offer->resetEmailCount();

        switch ($initialState) {
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
    }

    public function advanceState($useTime = null) {
        //there is no advancing from here
        $offer = $this->getOffer();
        $emails = $offer->getEmailCount();
        $limit = \system\Settings::read("email_limit_for_unkown_state", "State Transition Settings");
        if ($emails >= $limit) {
            \business\states\offer\Expired::setStateOnOffer($offer);
        }
        return false;
    }

    public function performStateAction($useTime = null) {
        //there is no action here
    }

    public function respondToEvent($eventMessage, \model\user\User $user = null) {
        $response = false;
        switch ($eventMessage) {
            case "sellers_choice_is_close":
                \business\states\offer\Expired::setStateOnOffer($this);
                $response = true;
                break;
            case "sellers_choice_is_change_type":
                break;
        }
        if( !$response ){
            $response = parent::respondToEvent($eventMessage, $user);
        }
        return $response;
    }

}
