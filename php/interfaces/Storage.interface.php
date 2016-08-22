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
interface Storage extends \IteratorAggregate {
    public static function create();
    public function getById( $id );
    public function store( $object );
    public function deleteObj( \interfaces\Storable $obj );
    public function deleteById( $id );
    public function getAllIds();
}
