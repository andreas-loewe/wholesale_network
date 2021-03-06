<?php

namespace business\communication;
require_once '../autoload.php';
/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-08-12 at 02:56:03.
 */
class EmailServiceTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var EmailService
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @covers business\communication\EmailService::sendMessage
     * @todo   Implement testSendMessage().
     */
    public function testSendMessage() {
        // Remove the following lines when you implement this test.]
        $service = \business\communication\EmailService::create();
        $service->sendAMessage( "jaredclemence@gmail.com", "Test Mail", "Message is short. Message is long." );
    }

    /**
     * @covers business\communication\EmailService::sendAMessage
     * @todo   Implement testSendAMessage().
     */
    public function testSendAMessage() {
        // Remove the following lines when you implement this test.
    }

    /**
     * @covers business\communication\EmailService::create
     * @todo   Implement testCreate().
     */
    public function testCreate() {
        // Remove the following lines when you implement this test.
    }

}
