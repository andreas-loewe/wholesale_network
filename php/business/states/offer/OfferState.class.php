<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\states\offer;

/**
 * Description of OfferState
 *
 * @author jrc
 */
abstract class OfferState implements \interfaces\StateDriven, \interfaces\EventHandler {

    /* @var \model\Offer */
    private $offer;

    public function __construct( \model\Offer $offer ){
        $this->offer = $offer;
    }
    
    /**
     * @return \model\Offer
     */
    protected function getOffer() {
        return $this->offer;
    }
    
    public static function loadStateInOffer( \model\Offer $offer ){
        if( $offer->getState() === null ){
            \business\states\offer\Unconfirmed::setStateOnOffer( $offer );
        }
        $class = $offer->getState();
        $stateObj = new $class( $offer );
        return $stateObj;
    }
    
    abstract public static function setStateOnOffer( \model\Offer $offer );
    
    public function confirmSellerEmail(){
        //do nothing in the default state.
    }

    public function respondToEvent($eventMessage, \model\user\User $user = null ) {
        //default action is do nothing.
        $this->saveObjectIfChanged();
        return false;
    }
    
    public function saveObjectIfChanged(){
        $offer = $this->getOffer();
        if( $offer->hasChanged() ){
            $dataStorage = \business\storage\OfferStorage::create();
            $dataStorage->store($offer);
        }
    }
}
