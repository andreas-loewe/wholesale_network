<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace model\property_detail;

/**
 * Description of FactData
 *
 * @author jrc
 */
class FactData {
    public $sqft;
    public $beds;
    public $full_baths;
    public $three_qt_baths;
    public $half_baths;
    public $atn;
    public $street_address;
    
    public function __construct(  $sqft, $beds, $full, $three_quarter, $half, $atn, $street ) {
        $this->sqft = $sqft;
        $this->beds = $beds;
        $this->full_baths = $full;
        $this->three_qt_baths = $three_quarter;
        $this->half_baths = $half;
        $this->atn = $atn;
        $this->street_address = $street;
    }
}
