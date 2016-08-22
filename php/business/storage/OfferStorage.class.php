<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\storage;

/**
 * Description of OfferStorage
 *
 * @author jrc
 */
class OfferStorage extends \business\storage\Storage {
    static private $singleton;
    
    /**
     * Reconstitutes offer from identifier
     * @param string $id
     * @return \model\Offer 
     */
    public function getById($id) {
        $sql = "SELECT serializedObject FROM OfferTable WHERE id='$id'";
        $response = parent::query($sql);
        /* @var $response \mysqli_result */
        $obj = null;
        if( $response->num_rows == 1){
            $row = $response->fetch_row();
            $data = $row[0];
            $obj = \unserialize($data);
            
        }
        return $obj;
    }

    public function store($object) {
        /* @var $object \model\Offer */
        $id = $object->getId();
        $state = $object->getState();
        $object->clearChangesFlag();
        $serial = \addslashes( \serialize($object) );
        
        $creationDate = $object->getCreationDate();
        $creationDate = $creationDate->format( "Y-m-d" );
        
        $formattedCompletionDate = $object->getCompletionDate();
        if( $formattedCompletionDate ){
            $formattedCompletionDate = "'" . $formattedCompletionDate->format("Y-m-d") . "'";
        }else{
            $formattedCompletionDate = "NULL";
        }
        
        $sellerEmail = $object->getSellerEmail();
        $price = $object->getPrice();
        $activeInt = $object->is_active ? 1 : 0;
        if( $price == 0 ) $price = "0";
        
        $sql = "REPLACE INTO OfferTable ( id, is_active, state, creation_date, completion_date, seller_email, price_charged, serializedObject ) VALUES ( '$id', $activeInt, '$state', '$creationDate', $formattedCompletionDate, '$sellerEmail', $price,'$serial' );";
        $response = parent::query( $sql );
        return $response;
    }

    /**
     * 
     * @return \business\storage\OfferStorage
     */
    public static function create() {
        if( !isset( self::$singleton ) ){
            self::$singleton = new \business\storage\OfferStorage();
        }
        return self::$singleton;
    }

    public function getAllIds() {
        $sql = "SELECT id FROM OfferTable WHERE is_active=1  ORDER BY last_update ASC";
        $result = parent::query($sql);
        /* @var $result \mysqli_result */
        $rows = $result->fetch_all();
        $ids = [];
        foreach( $rows as $row ){
            $ids[] = $row[0];
        }
        return $ids;
    }

    public function getTableDefinition() {
        $definition = <<<SQL
CREATE TABLE  `phDataWholsale`.`OfferTable` (
     `id` CHAR( 30 ) NOT NULL ,
     `is_active` INT(1) NOT NULL DEFAULT 1,
     `state` VARCHAR( 255 ) NOT NULL ,
     `creation_date` DATE NOT NULL,
     `completion_date` DATE NULL,
     `seller_email` VARCHAR(255) NOT NULL,
     `price_charged` DECIMAL(5,2) NULL,
     `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `serializedObject` LONGBLOB NOT NULL ,
      PRIMARY KEY (  `id` )
) ENGINE = MYISAM;
SQL;
        return $definition;
    }

    public function deleteById($id) {
        $sql = "DELETE FROM OfferTable WHERE id='$id'";
        return parent::query($sql);
    }

}
