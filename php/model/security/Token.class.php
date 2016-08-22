<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace model\security;

/**
 * Description of Token
 *
 * @author jrc
 */
class Token implements \interfaces\Storable{

    const METHOD = "AES-256-CBC";
    const IV = "ar23n1e2i3ksa21e";

    public $securityToken;
    public $storedData;
    private $password;
    private $expires;

    public function __construct( $securityToken, $storedData, $password ) {
        $this->securityToken = $securityToken;
        $this->storedData = $storedData;
        $this->password = $password;
    }
    
    public function getData(){
        if( $this->self_verify() ){
            return $this->storedData;
        }else{
            throw new \Exception( "Secruity token does not verify.");
        }
    }
    
    protected function self_verify(){
        $password = $this->password;
        $method = self::METHOD;
        $data = \serialize($this->storedData);
        
        $signature = \openssl_encrypt($data, $method, $password, 0, self::IV );
        $secureTokenId = md5( $signature );
        $doesVerify = ($secureTokenId == $this->securityToken);
        return $doesVerify;
    }

    public static function create( $minutesToLive , $tokenDataArray) {
        \date_default_timezone_set("America/Los_Angeles");
        $expire = new \DateTime( "now" );
        $expire->add( new \DateInterval( "PT" . abs( $minutesToLive ) . "M" ) );
        $tokenDataArray['expire'] = $expire->format("r");
        
        $password = md5( \microtime(true) );
        $method = self::METHOD;
        $data = \serialize($tokenDataArray);
        $signature = \openssl_encrypt($data, $method, $password, 0, self::IV);
        $securityToken = md5( $signature );
        
        $token = new \model\security\Token( $securityToken, $tokenDataArray, $password );
        $token->setExpiration( $expire );
        return $token;
    }

    public function getUrl() {
        $tokenRoot = \system\Settings::read( "token_url" );
        $tokenString = $this->securityToken;
        return "{$tokenRoot}/{$tokenString}";
    }

    public function getId() {
        return $this->securityToken;
    }
    
    /**
     * @return \DateTime
     */
    public function getExpireDate(){
        return $this->expires;
    }

    protected function setExpiration($expire) {
        $this->expires = $expire;
    }

}
