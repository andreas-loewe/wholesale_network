<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\states\offer;

/**
 * Description of Confirmed
 *
 * @author jrc
 */
class Confirmed extends \business\states\offer\OfferState {
    public static function setStateOnOffer(\model\Offer $offer) {
        if( $offer->getState() !== __CLASS__ ){
            //this prevents the email count from being reset if the offer is already in this state.
            $offer->numberOfEmailsToSeller += $offer->getEmailCount();
            $offer->resetEmailCount();
            $offer->setState( __CLASS__ );
        }
    }

    public function advanceState( $useDate = null ) {
        $offer = $this->getOffer();
        
        $balance = $this->determineBalanceOwed( $offer );
        if( $balance <= 0 ){
            //advance when balance on account is zero
            \business\states\offer\Paid::setStateOnOffer($offer);
        }else{
            $dealType = \business\behavior\dealTypes\DealType::getDealType($offer);
            $now = ( $useDate === null ? new \DateTime("now") : $useDate );
            if( $dealType instanceof \business\behavior\dealTypes\ContractAssignment ){
                /* @var $dealType \business\behavior\dealTypes\ContractAssignment */
                $closeDate = $dealType->getClosingDate();
                if( $closeDate <= $now ){
                    \business\states\offer\Unknown_Future::setStateOnOffer($offer);
                    return;
                }
            }
            //advance when more than 3 days pass without payment
            $confirmedOn = $offer->confirmationTime;
            $timeSinceConfirmation = $now->diff( $confirmedOn, true );
            /* @var $timeSinceConfirmation \DateInterval */
            $days = $timeSinceConfirmation->days;
            if( $days > 3 ){
                \business\states\offer\Expired::setStateOnOffer($offer);
            }
        }
    }

    public function performStateAction( $useTime = null ) {
        $offer = $this->getOffer();
        $emailCount = $offer->getEmailCount();
        if( $emailCount == 0 ){
            //if email count is zero, send an email with link to payment page to seller
        }else{
            $now = ( $useTime === null ? new \DateTime("now") : $useTime );
            //if email count is not zero, send one email to seller every 24 hours
            $lastEmail = $offer->getLastEmailTimestamp();
            $timeSince = $lastEmail->diff( $now, true );
            /* @var $timeSince \DateInterval */
            $hours = $timeSince->days * 24 + $timeSince->h;
            if( $hours >= 24 ){
                $this->sendPaymentReminder( $offer );
            }
        }
    }

    protected function sendPaymentReminder( \model\Offer $offer) {
        $offerId = $offer->getId();
        $tokenData = [
            "offerId" => $offerId,
            "signal"     => "load_payment_page",
            "successUrl" => "/offer/$offerId/payment",
            "balanceOwed" => $this->determineBalanceOwed( $offer )
        ];
        
        $recipient = $offer->getSellerEmail();
        $header = "[Action Required] Wholesale Your Property: One last step";
        $message = "You are almost finished. There is one last step before we email your deal to our cash buyers. Please click on this link to finish: $paymentLink";
    }

    public function determineBalanceOwed( \model\Offer $offer) {
        $price = $offer->getPrice();
        $paid = $offer->getMoneyPaid();
        return ($price - $paid);
    }
    
    public function respondToEvent($eventMessage, \model\user\User $user = null) {
        $response = false;
        switch( $eventMessage ){
            case 'load_payment_page':
                $response = true;
                break;
        }
        if( !$response ){
            $response = parent::respondToEvent($eventMessage, $user);
        }
        return $response;
    }
}
