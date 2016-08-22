<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace model\user;
/**
 * Description of BuyerList
 *
 * @author jrc
 */
class BuyerList extends \business\storage\StorageIterator {
    /**
     * Standard interface for loading a new BuyerList
     * 
     * @return \BuyerList
     */
    public static function load( $ids = null ) {
        $storage = \business\storage\BuyerStorage::create();
        $list = new \model\user\BuyerList($storage, $ids);
        return $list;
    }

    /**
     * Ask each buyer if the buyer is interested in the property. Return a buyer list 
     * containing only those buyers that answer yes.
     * 
     * @param \Property $property
     * @return \BuyerList
     */
    public function getSubset( $propertyOffer ) {
        $ids = [];
        foreach( $this as $buyer ){
            /* @bar $buyer \model\user\Buyer */
            $buyerId = $buyer->getId();
            $wishes = $buyer->getWishes();
            if( $wishes->like( $propertyOffer ) ){
                $ids[] = $buyerId;
            }
            unset( $buyer );
        }
        return self::load( $ids );
    }

    /**
     * Iterate through all buyers in list and add verified value.
     * 
     * @param \model\user\BuyerList $buyersList
     */
    public static function sum(\model\user\BuyerList $buyersList) {
        $sum = 0;
        foreach( $buyersList as $buyer ){
            /* @var $buyer \model\user\Buyer */
            $sum += $buyer->getVerifiedFunds();
        }
        return $sum;
    }

    /**
     * @return
     */
    public function getWishes() {
        
    }

}
