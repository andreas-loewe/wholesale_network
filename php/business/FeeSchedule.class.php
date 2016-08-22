<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business;

/**
 * Description of FeeSchedule
 *
 * @author jrc
 */
class FeeSchedule {
    const FLAT_FEE = 3;

    /**
     * 
     * @param int $sizeOfList
     * @return float
     */
    public static function getFeeByValue($sizeOfList) {
        $fee = self::FLAT_FEE;
        if( $sizeOfList < 20 ){
            $fee = 0;
        }
        return $fee;
    }

    public static function getFeeByCount($valueOfList) {
        $fee = self::FLAT_FEE;
        if( $valueOfList < 200000 ){
            $fee = 0;
        }
        return $fee;
    }
}
