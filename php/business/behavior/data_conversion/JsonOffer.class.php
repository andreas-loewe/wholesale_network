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
 * Description of JsonOffer
 *
 * @author jrc
 */
class JsonOffer implements \interfaces\OfferConverter {

    public function makeOffer($dataSource) {
        /* @var $dataSource \model\Offer */
        $vars = $dataSource->getObjectVars();
        $stdClass = new \stdClass();
        $prohibitedFields = ['state','changesMade', 'dealTypeClassName', 'offerMessages', 'offerMessageStorageBehaviorClass', 'braintree_payment_transactions'];
        foreach ($vars as $key => $value) {
            if( \in_array( $key, $prohibitedFields ) ) continue;
            if ($value instanceof \DateTime) {
                $vars->{$key} = $value->format("r");
            } else if (\is_bool($value)) {
                $vars->{$key} = ( $value ? "true" : "false" );
            } else if (\is_string($value) || \is_numeric($value)) {
                $stdClass->{$key} = $value;
            }
        }
        $stdClass->class = \get_class($dataSource);
        return $stdClass;
    }

}
