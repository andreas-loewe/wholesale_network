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
interface StateDriven {
    public function advanceState( $useTime = null );
    public function performStateAction( $useTime = null );
}
