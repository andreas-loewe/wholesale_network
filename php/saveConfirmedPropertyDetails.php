<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'autoload.php';

\utilities\PostConfiguration::run();
$property = \model\DirtyProperty::construct($_POST['property']);

$newDealManager = new \business\DealManager();
$newDealManager->add( $property, \business\DealStates::UNCONFIRMED ,$emailPacket );

\business\EmailSystem::distribute( $emailPacket );

$response = new \stdClass();
$response->nextUrl = "thank_you_seller.html";
return \json_encode($response->nextUrl);