<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace model\user;

/**
 * Description of Buyer
 *
 * @author jrc
 */
class Buyer extends \model\user\User implements \interfaces\Storable{

    private $creation_date;
    
    public function __construct() {
        parent::__construct();
        $this->creation_date = new \DateTime("now");
    }

    public function getVerifiedFunds(){
        return 0;
    }
    
    public function isInterestedInOffer( \model\Offer $offer ){
        //load behavior
        //use behavior do determine interested (true) and not interested (false)
        //return interest (true/false) to caller
        return true;
    }

    public function getCreationDate() {
        return $this->creation_date;
    }
    
    public function setCreationDate( \DateTime $time ){
        $this->creation_date = $time;
    }

    public function isSameAs(\model\user\Buyer $buyerCopy) {
        return parent::isSameAs( $buyerCopy ) && ($this->creation_date->format("Y-m-d") ==$buyerCopy->creation_date->format("Y-m-d"));
    }

}
