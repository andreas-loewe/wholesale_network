<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\behavior\dealTypes;

/**
 * Description of DealType
 *
 * @author jrc
 */
abstract class DealType implements \interfaces\DealType {
    /* @var \model\Offer */
    private $offer;
    
    public function __construct( \model\Offer $offer ) {
        $this->setOffer( $offer );
    }
    
    public static function getDealType(\model\Offer $offer) {
        $offerClass = self::_getDealType( $offer );
        $dealType = new $offerClass( $offer );
        return $dealType;
    }
    
    public function getTotalCostToBuyer() {
        $offer = $this->getOffer();
        $aquisition = $this->getUpfrontCostToBuyer();
        $repair = $offer->getRepairCosts();
        return $repair + $aquisition;
    }
    
    static protected function _getDealType( \model\Offer $offer ){
        return $offer->getDealTypeClass();
    }
    
    static protected function _setDealType( \model\Offer $offer, $class ){
        $offer->setDealTypeClass( $class );
        return new $class( $offer );
    }

    protected function setOffer(\model\Offer $offer) {
        $this->offer = $offer;
    }
    
    /**
     * @return \model\Offer
     */
    protected function getOffer(){ return $this->offer; }

}
