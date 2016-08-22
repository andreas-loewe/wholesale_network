<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business;

/**
 * Description of DealManager
 *
 * @author jrc
 */
class DealManager {
    public function addOffer(\model\DirtyProperty $property) {
        /*
         * Create Offer
         */
        $offer = $this->constructOffer( $property );
        
        /*
         * Store Offer
         */
        \business\states\offer\Unconfirmed::setStateOnOffer($offer);
        \business\storage\OfferStorage::store( $offer );
        
        /*
         * Perform initial cycles on Offer
         */
        $cycler = \business\OfferCycler::create();
        $cycler->cycleStateActions( [ $offer ] );
        \business\storage\OfferStorage::store( $offer );
    }
    
    public function cycleAllOffersThroughStateActions(){
        $offerList = \business\storage\OfferStorage::getIterator();
        $cycler = \business\OfferCycler::create();
        $cycler->cycleStateActions( $offerList );
    }

    /**
     * @param \model\DirtyProperty $property
     * @return \model\Offer
     */
    public function constructOffer(\model\DirtyProperty $property) {
        $converter = new \business\behavior\data_conversion\DirtyProperty();
        $offer = $converter->makeOffer($property);
        return $offer;
    }
}
