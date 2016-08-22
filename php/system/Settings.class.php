<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace system;

/**
 * Description of Settings
 *
 * @author jrc
 */
class Settings {

    public static function read($parameterName, $field = "") {
        \file_put_contents("settings.ini", "", \FILE_APPEND);
        $path= \realpath("settings.ini");
        
        $content = \file_get_contents($path);
        $ini = \parse_ini_string($content, true );
        $value = null;
        
        if ($field == "") {
            $field = "General Settings";
        }
        
        if (!is_array($ini[$field]))
            $ini[$field] = [];
        
        if (!isset($ini[$field][$parameterName])) {
            $ini[$field][$parameterName] = "Not set";
            $changeMade = true;
        }
        
        $value = $ini[$field][$parameterName];
        
        if ($changeMade === true) {
            self::saveDataToFile($ini, $path);
        }
        
        switch( $value ){
            case 'true':
                $value = true;
                break;
            case 'false':
                $value = false;
                break;
        }
        return $value;
    }

    protected static function saveDataToFile($ini, $file_name) {
        $keys = array_keys( $ini );
        sort( $keys );
        $content = "";
        foreach( $keys as $key ){
            $data = $ini[$key];
            $content .= "[$key]\n";
            foreach( $data as $paramName=>$stringSetting ){
                if( preg_match('/[?{}|&~!\[()^\"]/', $stringSetting ) ){
                    $stringSetting = '"' . \addslashes($stringSetting) . '"';
                }
                $content .= "{$paramName} = {$stringSetting}\n";
            }
            $content .= "\n\n";
        }
        $data = \file_put_contents($file_name, $content);
        if( $data === false ){
            throw new \Exception("Unable to write settings file.");
        }
    }

}
