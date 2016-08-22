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

namespace utilities;

require_once 'Benchmark/Profiler.php';
require_once 'Benchmark/Timer.php';

/**
 * Description of SystemProfiler
 *
 * @author jrc
 */
class SystemProfiler extends \Benchmark_Profiler{
    static private $singleton = null;
    /**
     * 
     * @return \utilities\SystemProfiler
     */
    static public function create(){
        if( self::$singleton === null ){
            self::$singleton = new \utilities\SystemProfiler(true);
        }
        return self::$singleton;
    }
    
    static public function is_set(){
        return ( self::$singleton !== null );
    }

    public function reset() {
        self::$singleton = null;
    }

}
