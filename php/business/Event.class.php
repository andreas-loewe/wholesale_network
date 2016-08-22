<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business;

/**
 * Description of Event
 *
 * @author jrc
 */
class Event {

    private $message;
    private $object;

    public function __construct( $message, \interfaces\EventHandler $object ){
        $this->message = $message;
        $this->object = $object;
        
        $object->respondToEvent($message);
    }
    
    public function __destruct() {
        unset( $this->object );
        unset( $this->message );
    }
}
