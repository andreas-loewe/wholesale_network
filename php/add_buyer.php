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

require_once 'autoload.php';

\extract($_POST);
try {
    if (!isset($email))
        throw new \Exception("Email is not set");
    if (!isset($cash))
        throw new \Exception("Cash is not set");
    if (!isset($_POST["g-recaptcha-response"]))
        throw new \Exception("Recaptcha is not set");
    if( !isset($page_url) )
        throw new \Exception("Page Url is not set.");

    $recaptcha = $_POST["g-recaptcha-response"];
    $request_from = $_SERVER['REMOTE_ADDR'];

    $requestData = $_SERVER;

    $url = \system\Settings::read("endpoint", "Recaptcha");
    $curl = \curl_init($url);
    $data = [
        "secret" => \system\Settings::read("secret_key", "Recaptcha"),
        "response" => $recaptcha
    ];
    \curl_setopt($curl, \CURLOPT_CUSTOMREQUEST, "POST");
    \curl_setopt($curl, \CURLOPT_RETURNTRANSFER, true);
    \curl_setopt($curl, \CURLOPT_POSTFIELDS, $data);
    $response = \curl_exec($curl);

    $responseObj = \json_decode($response);
    if ($responseObj->success !== true) {
        throw new \Exception("Recaptcha failed validation check.");
    }
    
    $token = \model\security\Token::create( 60*24, [
        "email"=>$email,
        "cash" => $cash,
        "original_request" => $request_from,
        "signal" => "verify_and_build_buyer"
    ] );
    $tokenData = \business\storage\TokenStorage::create();
    $tokenData->store($token);
    $tokenUrl = $token->getUrl();
    
    $recipient = $email;
    $messageSubject = "Verify Email to Start Receiving Wholesale Deals";
    $textBody = <<<TEXT
            Thank you. You are just one step away from joining the largest network of real estate buyers. Unlike other sites,
            membership here is free of charge. All we ask is that after you verify your email you check the offers that come to your 
            email inbox and make a "buy" or "pass" decision./n/n
            Click this link (or use copy and paste) to verify your email: $tokenUrl
TEXT;
    $htmlBody = <<<HTML
    <html>
            <head>
            </head>
            <body>
            <h1>Verify Your Email Address</h1>
            <h2>Thank you for joining our Wholesale Buyers Network</h2>
                <p>You are one step away!</p>
                <p>You have asked to be a part of the largest network of real 
                    estate buyers. Unlike other sites, our membership is free of charge. 
            All we ask is that you look at the deals that come your way and make a 
            "buy" or "pass" decission.</p>
                <p>To start receiving deals, you must confirm your email by clicking this link:</p>
                <a href="$tokenUrl">Finish registration and verify your email</a>
            </body>
    </html>
HTML;
    
    $emailService = \business\communication\EmailService::create();
    $emailService->sendAMessage($recipient, $messageSubject, $textBody, $htmlBody);
    
    $url = null;
    if( \preg_match('/[^.]+\//',$_POST['page_url'],$matches) ){
        $url = $matches[0] . "seller_check_email.html";
    }
    if( $url === null ){
        throw new \Exception( "Success Url fails to load. Please try again later.");
    }
} catch (\Exception $e) {
    $url = $_POST['page_url'];
    if( preg_match( '/\/error\/.+/', $url, $matches ) ){
        $url = str_replace( $matches[0], "", $url );
    }
    $errorMessage = $e->getMessage();
    
    $url .= "/error/" . \urlencode($errorMessage);
    
}
\header("Location: $url");