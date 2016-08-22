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

namespace business\behavior\data_storage\offer_messages;

/**
 * Description of OfferMessages
 *
 * @author jrc
 */
class ArrayStorage implements \interfaces\OfferMessageStorageBehavior{

    private $offer;

    public function __construct(\model\Offer $offer) {
        $this->offer = $offer;
        $messageData = $offer->getOfferMessages();
        if( !\is_array($messageData) ){
            $messageData = ['recipients'=>[],'respondants'=>[]];
            $offer->setOfferMessages($messageData);
        }
    }

    /**
     * @param \model\Offer $offer
     * @return int
     */
    public function countRespondingRecipients() {
        $offer = $this->offer;
        $messages = $offer->getOfferMessages();
        $count = count( $messages['respondants'] );
        return $count;
    }

    /**
     * @param \model\Offer $offer
     * @return int
     */
    public function countUniqueMessageRecipients() {
        $offer = $this->offer;
        $messages = $offer->getOfferMessages();
        $count = count( $messages['recipients'] );
        return $count;
    }

    /**
     * @param \model\Offer $offer
     * @return \DateTime|null
     */
    public function getLastMessageTime(\model\user\User $aUser) {
        $time = null;
        $offer = $this->offer;
        $messages = $offer->getOfferMessages();
        $id = $aUser->getId();
        $timeString = isset( $messages['recipients'][$id] ) ? $messages['recipients'][$id] : null;
        if( $timeString != null ){
            $time = new \DateTime( $timeString );
        }
        return $time;
    }

    public function saveMessageInstance(\model\user\User $aUser, $useTime = null) {
        $offer = $this->offer;
        $offerId = $offer->getId();
        $timeString = $this->getTimeString( $useTime );
        $messages = $offer->getOfferMessages();
        if( !isset( $messages ) ) $messages = ['recipients'=>[],'respondants'=>[]];
        $messages['recipients'][$aUser->getId()] = $timeString;
        $offer->setOfferMessages($messages);
    }

    protected function getTimeString($useTime) {
        $time = ( $useTime instanceof \DateTime ? $useTime : new \DateTime("now") );
        $timeString = $time->format("r");
        return $timeString;
    }

    public function recordMessageResponse($recipientEmail, $useTime = null) {
        $offer = $this->offer;
        $timeString = $this->getTimeString($useTime);
        $messageData = $offer->getOfferMessages();
        $messageData[ 'respondants' ][ $recipientEmail ] = $timeString;
        $offer->setOfferMessages($messageData);
    }

}
