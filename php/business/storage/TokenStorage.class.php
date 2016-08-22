<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\storage;

/**
 * Description of TokenStorage
 *
 * @author jrc
 */
class TokenStorage extends \business\storage\Storage implements \interfaces\Storage{

    static $singleton = null;

    /**
     * Accesses database and retrieve token by security token identifier. Reconstruct 
     * token object and return.
     * @param string $id
     * @return \business\security\Token
     */
    public function getById($securityToken) {
        $select = "SELECT serializedData FROM tokens WHERE tokenId='$securityToken'";
        $obj = null;
        $result = $this->query($select);
        if( $result instanceof \mysqli_result ){
            if( $result->num_rows == 1 ){
                $row  = $result->fetch_assoc();
                $data = $row['serializedData'];
                $obj = \unserialize( $data );
            }
        }
        return $obj;
    }

    public function store($token) {
        /* @var $token \model\security\Token */
        $id = $token->getId();
        $expires = $token->getExpireDate();
        $expireTimeStamp = $expires->format( "Y-m-d H:i:s" );
        $data = \addslashes( \serialize($token) );
        $replace = "REPLACE INTO tokens ( tokenId, expires, serializedData ) VALUES ( '$id', '$expireTimeStamp', '$data' );";
        return $this->query($replace);
    }

    /**
     * @return \business\storage\TokenStorage
     */
    public static function create() {
        if( self::$singleton === null ){
            self::$singleton = new \business\storage\TokenStorage();
            self::$singleton->deleteLongExpiredTokens();
        }
        return self::$singleton;
    }

    public function getAllIds() {
        $now = new \DateTime("now");
        $timestamp = $now->format( "Y-m-d H:i:s" );
        $select = "SELECT tokenId FROM tokens WHERE expires > '$timestamp';";
        $result = $this->query($select);
        $ids = [];
        if( $result instanceof \mysqli_result ){
            while( $row = $result->fetch_array() ){
                $ids[] = $row[0];
            }
        }
        return $ids;
    }

    /**
     * Tokens cannot be deleted by this storage object except by another method that 
     * deletes expired tokens after a long period passes.
     * @param string $id
     * @return false Fails every time... no delete action is performed.
     */
    public function deleteById($id) {
        return false;
    }
    
    protected function deleteLongExpiredTokens(){
        $limit = new \DateTime("now");
        $numberOfDays = \system\Settings::read("delete_tokens_after_X_days", "Security");
        $interval = new \DateInterval("P{$numberOfDays}D");
        $limit->sub( $interval );
        $limitString = $limit->format( "Y-m-d H:i:s" );
        $expireDate = "DELETE FROM tokens WHERE expires < '$limitString';";
        return $this->query($expireDate);
    }

    public function getTableDefinition() {
        $createSql = <<<SQL
CREATE TABLE  `phDataWholsale`.`tokens` (
     `tokenId` VARCHAR( 255 ) NOT NULL ,
     `expires` DATETIME NOT NULL ,
     `serializedData` LONGBLOB NOT NULL
) ENGINE = MYISAM;
SQL;
        return $createSql;
    }

}
