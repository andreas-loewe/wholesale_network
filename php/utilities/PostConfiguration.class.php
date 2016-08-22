<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace utilities;

/**
 * Description of PostConfiguration
 *
 * @author jrc
 */
class PostConfiguration {
    public static function run() {
        $input = \file_get_contents('php://input');
        if( count( $_POST ) == 0 && strlen( $input ) > 0 ){
            $obj = \json_decode($input);
            $_POST = \get_object_vars($obj);
        }
    }
}
