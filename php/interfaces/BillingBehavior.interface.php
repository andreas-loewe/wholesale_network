<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace interfaces;

/**
 * @author jrc
 */
interface BillingBehavior extends \interfaces\UserBehavior {
    public static function getBillingBehaviorForUser( \model\user\User $user );
    public function getPriceToSellOffer( \model\Offer $offer );
}
