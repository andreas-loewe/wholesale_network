<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\communication;

require_once 'Mail.php';
require_once 'Mail/mime.php';

/**
 * Description of EmailService
 *
 * @author jrc
 */
class EmailService {

    static $singleton = null;
    protected $suppression_on;

    public function __construct() {
        $this->suppression_on = false;
    }

    public function setSuppression($state) {
        $this->suppression_on = $state;
    }

    public static function sendMessage($recipient, $messageSubject, $messageText) {
        $email = self::create();
        $email->sendAMessage($recipient, $messageSubject, $messageText);
    }

    public function sendAMessage($recipient, $messageSubject, $textBody, $htmlBody = null) {
        if ($this->suppression_on == true)
            return;

        $settings = new \system\Settings();

        \date_default_timezone_set("America/Los_Angeles");
        $formattedMessage = $this->formatMessageText($textBody);
        $additional_headers[] = ["From: info@phoenixhomesltd.com"];
        $additional_headers[] = "Return-Path: info@phoenixhomesltd.com";
        //\mail($recipient, $messageSubject, $messageText, \implode("\r\n", $additional_headers));

        $params = [];
        foreach (["host", "port", "auth", "username", "password", "localhost", "debug", "persist"] as $paramName) {
            $params[$paramName] = $settings->read($paramName, "SMTP");
        }
        $hdrs = [];
        $hdrs['From'] = $settings->read("username", "SMTP");
        $hdrs['To'] = $recipient;
        $hdrs['Subject'] = 'Test message';

        $crlf = "\r\n";

        $mime = new \Mail_mime(array('eol' => $crlf));

        $mime->setTXTBody($textBody);
        if( $htmlBody !== null ) $mime->setHTMLBody($htmlBody);

        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);

        $mail = & \Mail::factory('smtp', $params);
        $result = $mail->send($recipient, $hdrs, $body);
        if (\PEAR::isError($result)) {
            /* @var $result \Pear_Error */
            $rslt = $result->getMessage();
            var_dump($rslt);
            $result = false;
        }
        return $result;
    }

    protected function formatMessageText($messageText) {
        $temp1 = \str_replace("\r\n", "||==||", $messageText);
        $temp2 = \str_replace("\n", "||==||", $temp1);
        $newText = \str_replace("||==||", "\r\n", $temp2);
        $formatted = \wordwrap($newText, 70, "\r\n");
        return $formatted;
    }

    /**
     * Returns a singleton of the email service. Set connection details to SMTP
     * server in settings.ini
     * @return \business\communication\EmailService
     */
    public static function create() {
        if (self::$singleton === null)
            self::$singleton = new \business\communication\EmailService ();
        return self::$singleton;
    }

}
