<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace interfaces;

/**
 *
 * @author jrc
 */
interface DealType {
    static public function getDealType( \model\Offer $offer );
    static public function applyToOffer( \model\Offer $offer );
    public function getTotalCostToBuyer();
    public function getUpfrontCostToBuyer();
    public function applyDataFromWebForm( $propertyData );
}
