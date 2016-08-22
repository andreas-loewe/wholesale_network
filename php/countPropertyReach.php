<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'autoload.php';

/*
$buyerList = \BuyerList::load();
$subList = $buyerList->extractListForProperty( $property );
*/

\utilities\PostConfiguration::run();

$property = \model\DirtyProperty::construct( $_POST['property'] );
$buyersList = \model\user\BuyerList::load();

$interestedBuyers = $buyersList->getSubset( $property );

$listSize = count( $interestedBuyers );
$value = \model\user\BuyerList::sum( $buyersList );


$data = new \stdClass();
$data->count = max( $listSize, 8 );
$data->value = max( $value, 480000 );
$data->fee = \min( \business\FeeSchedule::getFeeByCount( $listSize ), \business\FeeSchedule::getFeeByValue( $value ));

echo \json_encode($data);