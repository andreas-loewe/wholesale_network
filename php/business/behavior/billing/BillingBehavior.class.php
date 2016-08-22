<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\behavior\billing;

/**
 * Description of BillingBehavior
 *
 * @author jrc
 */
abstract class BillingBehavior implements \interfaces\BillingBehavior {
    private $user;
    
    public function __construct( \model\user\User $user ) {
        $this->setUser($user);
    }
    
    protected function setUser( \model\user\User $user ){
        $this->user = $user;
    }
    
    protected function getUser(){
        return $this->user;
    }
    
    public static function getBillingBehaviorForUser(\model\user\User $user) {
        $className = $user->getBillingBehavior();
        return new $className( $user );
    }

    protected static function __setBehaviorForUser(\model\user\User $user, $className) {
        $user->setBillingBehavior($className);
    }
}
