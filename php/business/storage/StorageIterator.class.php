<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\storage;

/**
 * Description of StorageIterator
 *
 * @author jrc
 */
class StorageIterator implements \Iterator, \Countable{
    /* @var array */
    private $ids;
    
    /* @var int */
    private $currentIndex;
    
    /* @var \interfaces\Storage */
    private $dataStore;

    public function __construct( \interfaces\Storage $dataStore, $ids=null ){
        $this->dataStore = $dataStore;
        if( $ids === null ){
            $this->ids = $this->dataStore->getAllIds();
        }else{
            foreach( $ids as $id ){
                if( !\is_string($id) ){
                    $class = __CLASS__;
                    $file = __FILE__;
                    $line = __LINE__;
                    $message = "List of IDs in $class must be all strings. Look in $file at line $line for error.";
                    throw new \Exception( $message );
                }
            }
            $this->ids = $ids;
        }
    }
    
    public function current() {
        $currentId = $this->key();
        $object = $this->dataStore->getById($currentId);
        return $object;
    }

    public function key() {
        $currentIndex = $this->currentIndex;
        $currentId = $this->ids[ $currentIndex ];
        return $currentId;
    }

    public function next() {
        $this->currentIndex++;
    }

    public function rewind() {
        $this->currentIndex = 0;
    }

    public function valid() {
        return isset( $this->ids[ $this->currentIndex ] );
    }
    
    public static function create( \interfaces\Storage $dataStore, $ids = null ){
        return new \business\storage\StorageIterator( $dataStore, $ids );
    }

    public function count() {
        return count( $this->ids );
    }

}
