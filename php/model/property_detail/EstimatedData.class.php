<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace model\property_detail;

/**
 * Description of EstimatedData
 *
 * @author jrc
 */
class EstimatedData {

    public $as_is_price;
    public $arv_price;
    public $repairs_price;

    public function __construct( $as_is_price, $arv_price, $repairs_price ) {
        $this->as_is_price = $as_is_price;
        $this->arv_price = $arv_price;
        $this->repairs_price = $repairs_price;
    }
    
    public function __get( $name ){
        return $this->{$name};
    }
    
    public function __set( $name, $value ){
        $this->{$name} = $value;
    }
}
