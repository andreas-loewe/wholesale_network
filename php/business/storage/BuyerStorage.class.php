<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\storage;

/**
 * Description of BuyerStorage
 *
 * @author jrc
 */
class BuyerStorage extends \business\storage\Storage {

    protected static $singleton;

    public function deleteById($id) {
        $profiler = \utilities\SystemProfiler::create();
        $profiler->enterSection("BuyerStorage::deleteById()");
        $obj = $this->getById($id);
        $this->query("START TRANSACTION");
        $success = $this->addRecordToHistoricalTable($obj);
        if ($success) {
            $deleteSQL = <<<SQL
DELETE FROM CurrentBuyersList WHERE email='$id';
SQL;
            $success = $this->query($deleteSQL);
        }
        switch ($success) {
            case true:
                $this->query("COMMIT");
                break;
            case false:
                $this->query("ROLLBACK");
                break;
        }
        $profiler->leaveSection("BuyerStorage::deleteById()");
        return $success;
    }

    public function getAllIds() {
        $profiler = \utilities\SystemProfiler::create();
        $profiler->enterSection("BuyerStorage::getAllIds()");
        //search database tables for all email addresses. Return email addresses.
        $sql = <<<SQL
                SELECT email FROM CurrentBuyersList;
SQL;
        if( !( $this instanceof \business\storage\BuyerStorage) ){
            $trace = \debug_backtrace(false, 3);
            var_dump( $trace ); die();
        }
        $result = $this->query($sql);
        $id_array = [];
        if( $result instanceof \mysqli_result ){
            while( $row = $result->fetch_assoc() ){
                $id = $row['email'];
                $id_array[] = $id;
            }
        }
        $profiler->leaveSection("BuyerStorage::getAllIds()");
        return $id_array;
    }

    /**
     * @param type $id
     * @return \model\user\Buyer
     */
    public function getById($id) {
        $profiler = \utilities\SystemProfiler::create();
        $profiler->enterSection("BuyerStorage::getById()");
        $selectStatement = <<<SQL
SELECT serializedData FROM CurrentBuyersList WHERE email='$id';       
SQL;
        $result = $this->query($selectStatement);
        $obj = null;
        if ($result instanceof \mysqli_result && $result->num_rows == 1) {
            /* @var $result \mysqli_result */
            $row = $result->fetch_array();
            $data = $row[0];
            $obj = \unserialize($data);
        }
        $profiler->leaveSection("BuyerStorage::getById()");
        return $obj;
    }

    /**
     * @return string
     */
    public function getTableDefinition() {
        $createTable = <<<SQL
CREATE TABLE IF NOT EXISTS CurrentBuyersList (
    email VARCHAR( 230 ) NOT NULL ,
    startDate DATE NOT NULL ,
    serializedData LONGBLOB NOT NULL ,
    PRIMARY KEY (  email )
);
SQL;
        return $createTable;
    }

    public function store($object) {
        /* @var $object \model\user\Buyer */
        $profiler = \utilities\SystemProfiler::create();
        $profiler->enterSection("BuyerStorage::store()");
        $email = $object->getId();
        $data = \addslashes(\serialize($object));
        $startDate = $object->getCreationDate();
        $startDate = $startDate->format("Y-m-d");

        $replace = <<<SQL
REPLACE INTO CurrentBuyersList ( email, startDate, serializedData ) VALUES ( '$email', '$startDate', '$data' );
SQL;
        
        $result = $this->query($replace);
        $profiler->leaveSection("BuyerStorage::store()");
        return $result;
    }

    /**
     * @return \business\storage\BuyerStorage
     */
    public static function create() {
        if (self::$singleton === null) {
            self::$singleton = new \business\storage\BuyerStorage();
        }
        return self::$singleton;
    }

    public function getBuyersForOffer(\model\Offer $offer) {
        $profiler = \utilities\SystemProfiler::create();
        $profiler->enterSection("BuyerStorage::getBuyersForOffer()");
        $interestedIds = [];
        foreach ($this as $buyer) {
            /* @var $buyer \model\user\Buyer */
            if ($buyer->isInterestedInOffer($offer)) {
                $interestedIds[] = $buyer->getId();
            }
        }
        $iterator = new \business\storage\StorageIterator($this, $interestedIds);
        $profiler->leaveSection("BuyerStorage::getBuyersForOffer()");
        return $iterator;
    }

    /**
     * @param \model\user\Buyer $obj
     * @param \DateTime|null $useTime
     * @return boolean
     */
    protected function addRecordToHistoricalTable(\model\user\Buyer $obj, $useTime = null) {
        if ($obj->isTest() === false) {
            /*
        $createTable2 = <<<SQL
CREATE TABLE IF NOT EXISTS BuyerHistoricalRecord (
    id    VARCHAR( 255 ) NOT NULL ,
    email VARCHAR( 230 ) NOT NULL ,
    startDate DATE NOT NULL ,
    endDate DATE NOT NULL ,
    serializedData LONGBLOB NOT NULL ,
    PRIMARY KEY (  id )
);
SQL;
            $email = $obj->getId();
            $start = $obj->getCreationDate();
            $start = $start->format("Y-m-d");
            $end = ( $useTime === null ? new \DateTime("now") : $useTime );
            $end = $end->format("Y-m-d");
            $id = $email . "_" . \str_replace("-", "_", $end);
            $data = \addslashes(\serialize($obj));

            $insert = <<<REPLACE
INSERT INTO BuyerHistoricalRecord ( id, email, startDate, endDate, serializedData ) VALUES ( '$id', '$email', '$start', '$end', '$data' ); 
REPLACE;
            return $this->query($insert);
             * 
             */
        } else {
            return true;
        }
    }

}
