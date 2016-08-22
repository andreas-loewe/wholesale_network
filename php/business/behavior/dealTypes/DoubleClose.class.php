<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\behavior\dealTypes;

/**
 * Description of DoubleClose
 *
 * @author jrc
 */
class DoubleClose extends \business\behavior\dealTypes\DealType {
    public function applyDataFromWebForm($propertyData) {
        /* @var $propertyData \model\DirtyProperty */
        $dataObj = $propertyData->double_close_detail;
        $offer = $this->getOffer();
        $price = $dataObj->desired_price;
        $bestAlternate = $dataObj->alternative_action;
        $lowestPrice = $dataObj->bottom_price;
        $offer->setContractPrice( $price );
        $offer->setSellersPrivateThoughts( $bestAlternate );
        $offer->setLowestPrice( $lowestPrice );
    }

    /**
     * 
     * @param \model\Offer $offer
     * @return \business\behavior\dealTypes\DoubleClose
     */
    static public function applyToOffer(\model\Offer $offer) {
        return parent::_setDealType($offer, __CLASS__);
    }

    public function getUpfrontCostToBuyer() {
        $offer = $this->getOffer();
        return $offer->getContractPrice();
    }

}
