<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace model\meta;

/**
 * Description of ViewInstance
 *
 * @author jrc
 */
class ViewInstance {
    public function __construct( \model\user\User $account ) {
        $this->serverData = $_SERVER;
        $this->tokenData = $_GET;
        $this->user = $account;
    }
}
