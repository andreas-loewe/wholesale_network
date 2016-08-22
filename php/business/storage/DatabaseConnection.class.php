<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\storage;

/**
 * Description of DatabaseConnection
 *
 * @author jrc
 */
class DatabaseConnection extends \mysqli{
    static protected $connection;
    
    public function __construct() {
        $host = \system\Settings::read("host", "Database");
        $user = \system\Settings::read("user", "Database");
        $pass = \system\Settings::read("pass", "Database");
        //GoDaddy databases have the user name == database name
        $db = $user;
        parent::__construct($host, $user, $pass, $db);
    }
    
    /**
     * 
     * @return \business\storage\DatabaseConnection
     */
    static public function create(){
        if( self::$connection === null || !isset( self::$connection ) ){
            self::$connection = new \business\storage\DatabaseConnection();
        }
        return self::$connection;
    }
}
