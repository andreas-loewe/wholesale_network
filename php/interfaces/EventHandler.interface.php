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
interface EventHandler {
    public function respondToEvent( $eventMessage, \model\user\User $user = null );
}
