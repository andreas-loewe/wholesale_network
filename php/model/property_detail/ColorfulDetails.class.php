<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace model\property_detail;

/**
 * Description of ColorfulDetails
 *
 * @author jrc
 */
class ColorfulDetails {

    private $video_links;
    private $about_seller;
    private $property_description;

    public function __construct( $videoURLs, $about_seller, $description ) {
        $this->video_links = $videoURLs;
        $this->about_seller = $about_seller;
        $this->property_description = $description;
    }
    
    public function __set($name, $value) {
        $this->{$name} = $value;
    }
    
    public function __get($name) {
        return $this->$name;
    }
}
