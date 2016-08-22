<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\storage;

/**
 * Description of Storage
 *
 * @author jrc
 */
abstract class Storage implements \interfaces\Storage, \Countable {

    static protected $tempStorage = "";
    private $tempData;

    public function getIterator() {
        $storage = $this->create();
        return new \business\storage\StorageIterator( $storage );
    }
    
    abstract public function getAllIds();
    abstract public static function create();
    abstract public function deleteById( $id );
    abstract public function getTableDefinition();
    
    public function deleteObj( \interfaces\Storable $obj ){
        $id = $obj->getId();
        return $this->deleteById($id);
    }
    
    
    public function query( $string ){
        $connection = \business\storage\DatabaseConnection::create();
        /* @var $connection \business\storage\DatabaseConnection */
        $result = $connection->query($string);
        if( $result === false ){
            $this->setTempData( $string );
            $func = function( \business\storage\Storage $storage ){
                $string = $storage->getTempData();
                $result = $storage->query( $string );
                $storage->setTempData(null);
                return $result;
            };
            $result = self::detectError( $func );
        }
        return $result;
    }

    protected function detectError( callable $func) {
        $conn = \business\storage\DatabaseConnection::create();
        $errno = $conn->errno;
        $error = $conn->error;
        switch( $errno ){
            case 1146:
                //table does not exist
                $createTable = $this->getTableDefinition();
                $result = $conn->query($createTable);
                if( $result === true ){
                    //run callable function if successful
                    return $func( $this );
                }else{
                    var_dump( $result );
                    var_dump( $conn->error );
                    throw new \Exception( "Unable to create table with string:\n$createTable");
                }
                break;
            default:
                $file = __FILE__;
                $line = __LINE__;
                var_dump( ["Error on {$file} and {$line}", $errno, $error] ); die();
        
        }
    }

    public function getTempData() {
        return $this->tempData;
    }

    public function setTempData($data) {
        $this->tempData = $data;
    }

    public function count() {
        $allIds = $this->getAllIds();
        return count( $allIds );
    }

}
