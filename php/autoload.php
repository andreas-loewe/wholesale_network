<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
\date_default_timezone_set("America/Los_Angeles");
//echo "\nAutoload set.";
if (!defined("AUTOLOADER_1")) {
    define("AUTOLOADER_1", 1);
    
    $curDir = \getcwd();
    if( preg_match( '/^(.*\/php)\/.+$/', $curDir, $matches)){
        \chdir($matches[1]);
    //    echo "\nDirectory changed.";
    }
    $curDir = \getcwd();
    //echo "\nCurrent directory: $curDir\n";

    $path = \get_include_path();
    $pathParts = \explode(\PATH_SEPARATOR, $path);
    if (!in_array("./php", $pathParts)) {
        $pathParts[] = "php";
        $pathParts[] = "..";
        $pathParts[] = "php/pear";
        $pathParts[] = "pear";
    }
    $newPath = \implode(\PATH_SEPARATOR, $pathParts);
    \set_include_path($newPath);
    
    \spl_autoload_extensions(\implode(",",[
        ".class.php",
        ".interface.php",
        ".trait.php"
    ]));
    \spl_autoload_register( "spl_autoload" );
    
    $filePath = '../lib/braintree-php-3.15.0/Braintree.php';
    $path = \realpath($filePath );
    if( $path === false ){
        $path = realpath( '../' . $filePath );
    }
    require_once $path;
}
