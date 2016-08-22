<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business;

/**
 * Description of OfferCycler
 *
 * @author jrc
 */
class OfferCycler {
    /* @var \business\OfferCycler */
    private static $singleton;
    
    /**
     * @return \business\OfferCycler
     */
    public static function create() {
        if( self::$singleton === null ){
            self::$singleton = new \business\OfferCycler();
        }
        return self::$singleton;
    }
    
    /**
     * 
     * @param array $array array of offers
     */
    public function cycleStateActions( $array, $useTime = null, $maxMinutes = 30 ){
        $skipQuitLimit = ( $useTime !== null );
        $profiler = \utilities\SystemProfiler::create();
        $profiler->enterSection("OfferCycler::cycleStateActions");
        $queue = new \SplQueue();
        foreach( $array as $item ){
            if( $item instanceof \model\Offer ) $queue->push( $item );
        }
        if( $useTime === null ){
            $useTime = new \DateTime("now");
        }
        \set_time_limit(count( $queue ) * 0.1 );
        $quitTime = new \DateTime("now");
        $quitTime->add( "PT" . $maxMinutes . "M" );
        while( count( $queue ) > 0 && ( $skipQuitLimit == true || \microtime() < $quitTime->getTimestamp() )) {
            $offer = $queue->pop();
            $startState = $offer->hasChanged();
            /* @var $offer \model\Offer */
            $offer->advanceState( $useTime );
            $offerChange = $offer->hasChanged();
            $loopAgain = $offer->performStateAction( $useTime );
            $actionChange = $offer->hasChanged();
            $changeStr = $offer->hasChanged() ? "TRUE" : "FALSE";
            if( $loopAgain === true || $offer->hasChanged() ){
                $queue->push( $offer );
            }
            if( $offer->hasChanged() ){
                $this->storeOffer( $offer );
            }
        }
        unset( $queue );
        $profiler->leaveSection("OfferCycler::cycleStateActions");
        \set_time_limit(30);
    }

    public function storeOffer($offer) {
        $store = \business\storage\OfferStorage::create();
        /* @var $store \business\storage\OfferStorage */
        $store->store($offer);
        $store = null;
    }

}
