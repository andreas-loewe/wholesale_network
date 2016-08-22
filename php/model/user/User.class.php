<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace model\user;

/**
 * Description of User
 *
 * @author jrc
 */
class User {
    public $email;
    private $billingBehaviorClassName;
    private $is_test;

    public function __construct() {
        $this->email = null;
        $this->billingBehaviorClassName = null;
        $this->is_test = false;
    }

    public function setBillingBehavior($className) {
        $this->billingBehaviorClassName = $className;
    }

    public function getBillingBehavior() {
        return $this->billingBehaviorClassName;
    }
    
    public function getId(){
        return $this->getEmail();
    }
    
    public function getEmail(){
        return $this->email;
    }
    
    public function setEmail( $address ){
        $this->email = $address;
    }
    
    public function setTestFlag( $bool ){
        $this->is_test = $bool;
    }
    
    public function isTest(){
        return $this->is_test;
    }
    
    public function isSameAs( \model\user\User $user ){
        return ($this->billingBehaviorClassName == $user->billingBehaviorClassName) && ($this->is_test == $user->is_test) && ($this->email == $user->email);
    }
    
    public function recordMessageAboutOffer( \model\Offer $offer, $useTime = null ){
        $behaviorClass = $this->offerMessageStorageBehaviorClass;
        $behavior = new $behaviorClass( $this );
        
    }

}
