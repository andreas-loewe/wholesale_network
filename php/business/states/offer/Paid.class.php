<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\states\offer;

/**
 * Description of Paid
 *
 * @author jrc
 */
class Paid extends \business\states\offer\OfferState {

    public static function setStateOnOffer(\model\Offer $offer) {
        $emails = $offer->getEmailCount();
        $offer->numberOfEmailsToSeller += $emails;
        $offer->resetEmailCount();
        $offer->setState(__CLASS__);
    }

    public function advanceState($useTime = null) {
        //advance condition 1 => 100% response rate
        //advance condition 2 => 5 days pass
        $offer = $this->getOffer();
        $recipientCount = $offer->countUniqueMessageRecipients();
        $respondingCount = $offer->countRespondingRecipients();
        $responsePercentage = 0;
        if ($recipientCount > 0)
            $responsePercentage = $respondingCount / $recipientCount;
        $responsePercentage = \floor($responsePercentage * 100);
        $creationDate = $offer->getCreationDate();
        $curTime = ( $useTime === null ? new \DateTime("now") : $useTime );
        $diff = $curTime->diff($creationDate, true);
        $daysCount = $diff->days;

        $field = "State Transition Settings";
        $maxDuration = \system\Settings::read("advertising_length_in_days", $field);
        $responseRateTrigger = \system\Settings::read("response_rate_to_satisfy_obligation", $field);

        $dealType = \interfaces\DealType::getDealType($offer);
        $dealExpired = false;
        if( $dealType instanceof \business\behavior\dealTypes\ContractAssignment && $dealType->getClosingDate() < $curTime ){
            $dealExpired = true;
        }
        if ($daysCount >= $maxDuration || $responsePercentage >= $responseRateTrigger || $dealExpired ) {
            \business\states\offer\Advertized::setStateOnOffer($offer);
        }
        return \FALSE;
    }

    public function performStateAction($useTime = null) {
        //load all interested buyers
        //cycle through buyers
        //     get last email time for buyers
        //     if email sent 24 or more hours ago, send a new email
        //          update last email time
        //          record email in database
        $offer = $this->getOffer();
        $buyerStorage = \business\storage\BuyerStorage::create();
        $buyersList = $buyerStorage->getBuyersForOffer($offer);
        $emailSystem = \business\communication\EmailService::create();

        $i = 0;
        foreach ($buyersList as $buyer) {
            /* @var $buyer \model\user\Buyer */
            $emailAddress = $buyer->getEmail();

            $lastEmail = $offer->getLastMessageTime($buyer);
            $hours = 99;
            if ($lastEmail instanceof \DateTime) {
                $now = new \DateTime("now");
                $interval = $now->diff($lastEmail, true);
                $hours = $interval->h + $interval->days * 24;
            }
            if ($hours > 24) {
                $messageToBuyer = $this->constructMessageToBuyer($buyer, $htmlBody, $textBody);
                $street = $offer->getStreetAddress();
                $subject = "Wholesale Offer: {$street}";
                $messageIsSentSuccessfully = $emailSystem->sendAMessage($emailAddress, $subject, $textBody, $htmlBody);
                if ($messageIsSentSuccessfully || $buyer->isTest()){
                    $offer->saveMessageInstance($buyer, $useTime);
                }
            }
        }
        return false;
    }

    public function constructMessageToBuyer(\model\user\Buyer $buyer, &$html, &$textBody) {
        $offer = $this->getOffer();
        $root = \system\Settings::read("domainRoot");
        $offerId = $offer->getId();
        $tokenData = [
            'offerId' => $offerId,
            'buyerId' => $buyer->getEmail(),
            'successUrl' => $root . "offers/$offerId",
            'signal' => 'cash_buyer_responds_to_offer_email'
        ];
        $minutesToLive = 24 * 60; //60 minutes per hour for 24 hours
        $token = \model\security\Token::create($minutesToLive, $tokenData);

        $minutesToLive = 7 * 24 * 60;
        $change = \model\security\Token::create($minutesToLive, [ 'buyerId' => $buyer->getEmail(), 'signal' => 'update_property_filters', 'successUrl' => $root . 'account/settings']);
        $remove = \model\security\Token::create($minutesToLive, [ 'buyerId' => $buyer->getEmail(), 'signal' => 'remove_from_buyer_list', 'successUrl' => $root . 'account/remove']);

        $changeSettingsUrl = $change->getUrl();
        $removeFromListUrl = $remove->getUrl();
        $secureUrl = $token->getUrl();
        $tokenStore = \business\storage\TokenStorage::create();
        $tokenStore->store($token);
        $tokenStore->store($change);
        $tokenStore->store($remove);
        $textBody = <<<TEXT
Dear Wholesale Buyer:

A wholesaler provided us a deal that fits your property parameters. See the deal by clicking this link. It will only work for the next 24 hours:</p>

$secureUrl

Kind Regards,
The Wholesale Team
                
Change or Update Property Filters: $changeSettingsUrl
Unsubscribe from All Money Making Oportunities: $removeFromListUrl
              
TEXT;
        $html = <<<HTML
                <body>
                <p>Dear Wholesale Buyer:</p>
                <p>A wholesaler provided us a deal that fits your property parameters. See the deal by clicking this link. It will only work for the next 24 hours:</p>
                <p><a target="_blank" href="$secureUrl">$secureUrl</a></p>
                <p>Kind Regards,</p>
                <p>The Wholesale Team</p>
                <br/>
                <br/>
                <br/>
                <br/>
                <br/>
                <br/>
                <br/>
                <br/>
                <br/>
                <br/>
                <br/>
                <br/>
                <br/>
                <br/>
                <p>Other links (good for 1 week)</p>
                <ul>
                <li><a target="_blank" href="$changeSettingsUrl">Reduce emails and change preferences</a></li>
                <li><a target="_blank" href="$removeFromListUrl">Give up on your real estate dreams (unsubscribe)</a></li>
                </ul>
                </body>
HTML;
    }

}
