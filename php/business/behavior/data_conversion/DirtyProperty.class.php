<?php

/*
 * The MIT License
 *
 * Copyright 2016 jrc.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace business\behavior\data_conversion;

/**
 * Description of DirtyProperty
 *
 * @author jrc
 */
class DirtyProperty implements \interfaces\OfferConverter {
    public function makeOffer($dataSource) {
        return $this->make_v1_Offer($dataSource);
    }

    public function make_v1_Offer( \model\DirtyProperty $property ){
        $offer = new \model\Offer();
        \business\states\offer\Unconfirmed::setStateOnOffer($offer);
        $offer->requestFrom = $_SERVER;
        $offer->id = $this->constructId();
        $offer->facts = $this->extractFacts( $property );
        $offer->color = $this->extractColorDetail( $property );
        $offer->estimatedDetails = $this->extractEstimatedData( $property );
        switch( $property->deal_type ){
            case 'assignment':
                $dealType = \business\behavior\dealTypes\ContractAssignment::applyToOffer($offer);
                break;
            case 'double-close':
            default:
                $dealType = \business\behavior\dealTypes\DoubleClose::applyToOffer($offer);
                break;
        }
        /* @var $dealType \interfaces\DealType */
        $dealType->applyDataFromWebForm($property);
        $offer->testing = $property->testing;
        return $offer;
    }
    
    protected function extractFacts(\model\DirtyProperty $property) {
        $sqft = $property->sqft;
        $beds = $property->beds;
        $full = $property->baths->full;
        $three_quarter = $property->baths->three_quarter;
        $half = $property->baths->half;
        $atn = $property->atn;
        $street = $property->street;
        $facts = new \model\property_detail\FactData($sqft, $beds, $full, $three_quarter, $half, $atn, $street);
        return $facts;
    }

    protected function extractColorDetail(\model\DirtyProperty $property) {
        $videoURLs = $property->videoURLs;
        $about_seller = $property->about_seller;
        $description = $property->description;
        $color = new \model\property_detail\ColorfulDetails($videoURLs, $about_seller, $description);
        return $color;
    }

    protected function extractEstimatedData(\model\DirtyProperty $property) {
        $as_is_price = $property->pricing->as_is;
        $arv_price = $property->pricing->arv;
        $repairs_price = $property->pricing->repairs;
        $estimated = new \model\property_detail\EstimatedData($as_is_price, $arv_price, $repairs_price);
        return $estimated;
    }

    protected function constructId() {
        $date = new \DateTime("now");
        $daycode = $date->format("Ymd_Hi");
        $randcode = rand(3000,3999);
        $id = "{$daycode}_r{$randcode}_v1";
        return $id;
    }
}
