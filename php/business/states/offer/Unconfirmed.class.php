<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\states\offer;

/**
 * Description of Unconfirmed
 *
 * @author jrc
 */
class Unconfirmed extends \business\states\offer\OfferState {

    public function confirmSellerEmail() {
        parent::confirmSellerEmail();
        $offer = $this->getOffer();
        $time = new \DateTime("now", new \DateTimeZone("America/Los_Angeles"));
        $offer->setConfirmationTime($time);
    }

    public static function setStateOnOffer(\model\Offer $offer) {
        $offer->setCreationDate(new \DateTime("now"));
        $offer->setState(__CLASS__);
    }

    public function advanceState($useTime = null) {
        $changeMade = false;
        $offer = $this->getOffer();
        //if number of emails sent > 3 advance to expired
        $emailCount = $offer->getEmailCount();
        if ($offer->numberOfEmails >= 3) {
            \business\states\offer\Expired::setStateOnOffer($offer);
            $changeMade = true;
        }
        if ($offer->confirmationTime !== null) {
            //if confirmation date is set, advance to confirmed
            \business\states\offer\Confirmed::setStateOnOffer($offer);
            $changeMade = true;
        }
        $state = $offer->getState();
        return $changeMade;
    }

    public function performStateAction($useTime = null) {
        \date_default_timezone_set("America/Los_Angeles");
        if ($useTime === null) {
            $now = new \DateTime("now");
        } else {
            $now = clone $useTime;
        }
        $offer = $this->getOffer();
        //check time of last email
        $lastEmailToSeller = $offer->getLastEmailTimestamp();

        $hours = 99;
        if ($lastEmailToSeller instanceof \DateTime) {
            ////check amount of time passed
            $diff = $now->diff($lastEmailToSeller, true);
            $hours = $diff->d * 24 + $diff->h;
        }
        $runThroughCyclerAgain = false;
        if ($hours >= 24) {
            //if > 24 hours:
            //1. send email 2. increment counter 3. set response to true
            $this->createMessageText($offer, $messageText, $htmlText);
            $messageSubject = $this->createMessageSubject($offer);
            $recipient = $this->createMessageRecipient($offer);

            $emailer = \business\communication\EmailService::create();
            $emailer->sendAMessage($recipient, $messageSubject, $messageText, $htmlText);
            $offer->markEmailSent($now);
            $runThroughCyclerAgain = true;
        }
        //return response
        return $runThroughCyclerAgain;
    }

    public function createMessageText(\model\Offer $offer, &$textBody, &$htmlBody =null) {
        $offerId = $offer->getId();
        $tokenData = [
            "offerId" => $offerId,
            "signal" => "confirm_seller_email",
            "successUrl" => "/offer/$offerId/payment"
        ];

        $token = \model\security\Token::create($expires = 30, $tokenData);
        $secureUrl = $token->getUrl();
        $textBody = <<<MESSAGE
Please confirm your email account address. This step is critical to the 
wholesaling process. We do not have user accounts, so each user is identified 
by email communication.
        
By clicking the following link, you are confirming that you have access to this
email account and also that you have a property or contract that you wish to sell.
(Note: If the following link is not clickable, please copy and paste it into your 
internet browser. )
        
$secureUrl        
        
If this email seems strange to you, or if you do not have any property to sell, 
please ignore this message. Our system will stop attempting to confirm this email 
after a period of three days passes.
                
Kind regards,

Wholesale System
MESSAGE;
        $htmlBody = <<<MESSAGE
<p>Please confirm your email account address. This step is critical to the 
wholesaling process. We do not have user accounts, so each user is identified 
by email communication.</p>
        
<p>By clicking the following link, you are confirming that you have access to this
email account and also that you have a property or contract that you wish to sell.
(Note: If the following link is not clickable, please copy and paste it into your 
internet browser. )</p>
        
<a href='$secureUrl' target="_blank">$secureUrl</a>       
        
<p>If this email seems strange to you, or if you do not have any property to sell, 
please ignore this message. Our system will stop attempting to confirm this email 
after a period of three days passes.</p>
                
<p>Kind regards,</p>
<p>Wholesale System</p>
MESSAGE;
        
        return $textBody;
    }

    public function createMessageSubject(\model\Offer $offer) {
        return "Please Confirm Your Email Address";
    }

    public function createMessageRecipient(\model\Offer $offer) {
        return $offer->getSellerEmail();
    }
    
    public function respondToEvent($eventMessage, \model\user\User $user = null) {
        $response = false;
        switch( $eventMessage ){
            case 'secure_link_clicked':
                $this->confirmSellerEmail();
                $response = true;
                break;
        }
        if( !$response ){
            $response = parent::respondToEvent($eventMessage, $user);
        }
        return $response;
    }

}
