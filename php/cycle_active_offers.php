<?php

/* 
 * The MIT License
 *
 * Copyright 2016 jrc.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

\chdir( __DIR__ );

require_once 'autoload.php';
\set_time_limit(120);

$tempFile = "~tmpMarker_cycler.txt";

$dateTime = new DateTime( "now" );

$timeLimitToRun = 30;

if(\file_exists($tempFile)){
    $previous_time = \unserialize(\file_get_contents($tempFile));
    $diff = $dateTime->sub( $previous_time, true );
    /* @var $diff \DateInterval */
    $minutes = $diff->m + $diff->h * 60;
    if( $minutes < $timeLimitToRun * 1.5 ){
        //if another file is running. abort.
        return;
    }
    //unless that file is too old to indicate a currently running process.
}

\file_put_contents($tempFile, \serialize($dateTime));

$cycler = new \business\OfferCycler();
$offerData = \business\storage\OfferStorage::create();

/*
 * Cycle for at most 30 minutes
 */
$cycler->cycleStateActions($offerData, null, 30);

\unlink($tempFile);