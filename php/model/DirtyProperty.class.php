<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace model;

/**
 * Description of Property
 *
 * @author jrc
 */
class DirtyProperty extends \stdClass {
    public $seller_email;
    public $sqft;
    public $beds;
    public $baths;
    public $deal_type;
    public $atn;
    public $street;
    public $pricing;
    public $videoURLs;
    public $assignment_detail;
    public $double_close_detail;
    public $about_seller;
    public $description;
    public $testing;
    public $seller;

    public function __construct() {
        $this->sqft = null;
        $this->beds = null;
        $this->baths = new \stdClass();
        $this->baths->full = null;
        $this->baths->half = null;
        $this->baths->three_quarter = null;
        $this->deal_type = null;
        $this->atn = null;
        $this->street = null;
        $this->pricing = new \stdClass();
        $this->pricing->as_is = null;
        $this->pricing->arv = null;
        $this->pricing->repairs = null;
        $this->videoURLs = null;
        $this->assignment_detail = new \stdClass();
        $this->assignment_detail->fee = null;
        $this->assignment_detail->contract_price = null;
        $this->assignment_detail->closing_date = null;
        $this->assignment_detail->escrow = new \stdClass();
        $this->assignment_detail->escrow->name = null;
        $this->assignment_detail->escrow->url = null;
        $this->double_close_detail = new \stdClass();
        $this->double_close_detail->desired_price = null;
        $this->double_close_detail->alternative_action = null;
        $this->double_close_detail->bottom_price = null;
        $this->about_seller = null;
        $this->description = null;
        $this->seller_email = null;
    }

    public static function construct($keyedArray) {
        $property = new \model\DirtyProperty();
        $vars = \get_object_vars( $property );
        $keys = \array_keys($vars);
        foreach( $keyedArray as $key => $value ){
            if(\in_array($key, $keys) ){
                if( $property->$key === null ){
                    $property->$key = $value;
                }else if( $property->$key instanceof \stdClass ){
                    $property->$key = self::convertArrayToStdClass( $value );
                }
            }
        }
        return $property;
    }

    protected static function convertArrayToStdClass($array) {
        $stdClass = new \stdClass();
        foreach( $array as $key => $value ){
            if( \is_array( $value ) ){
                $stdClass->$key = self::convertArrayToStdClass($value);
            }else{
                $stdClass->$key = $value;
            }
        }
        return $stdClass;
    }

}
