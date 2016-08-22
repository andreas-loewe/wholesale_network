<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace model;

/**
 * Description of Offer
 *
 * @author jrc
 */
class Offer implements \interfaces\EventHandler, \interfaces\StateDriven, \interfaces\Storable, \interfaces\OfferMessageStorageBehavior {
    /* @var string */

    public $id;
    
    /* @var boolean */
    public $is_active;

    /* @var \DateTime */
    public $creationTime;

    /* @var \DateTime */
    public $confirmationTime;

    /* @var string Name of state class */
    public $state;

    /* @var string */
    public $seller_email;

    /* @var \model\FactData */
    public $facts;

    /* @var array Copy of $_SERVER at offer creation */
    public $requestFrom;

    /* @var \model\EstimatedData */
    public $estimatedDetails;

    /* @var \model\ColorfulDetails */
    public $color;

    /* @var \DateTime */
    public $lastEmailTimestamp;

    /* @var \isTest */
    public $testing;

    /* @var int */
    public $numberOfEmails;

    /* @var int */
    public $numberOfEmailsToSeller;

    /* @var int */
    public $numberOfEmailsToBuyers;

    /* @var bool */
    public $changesMade;

    /* @var float */
    private $priceForService;

    /* @var float */
    private $moneyPaid;

    /* @var string */
    private $dealTypeClassName;

    /* @var float */
    private $assignment_fee;

    /* @var float */
    private $contract_price;

    /* @var string */
    private $escrow_co_name;

    /* @var string */
    private $escrow_co_www;

    /* @var \DateTime */
    private $closing_date;

    /* @var \DateTime */
    private $completion_date;

    /* @var mixed */
    private $offerMessages;

    /* @var \interfaces\OfferMessageStorageBehavior */
    private $offerMessageStorageBehaviorClass;

    /* @var \DateTime */
    private $advertising_end_date;

    /* @var \DateTime */
    private $last_transition_date;
    
    /* @var array */
    private $braintree_payment_transactions;
    
    /* @var array */
    private $date_service_paid;

    public function __construct() {
        $this->confirmationTime = null;
        $this->seller_email = "";
        $this->priceForService = null;
        $this->facts = new \model\property_detail\FactData(null, null, null, null, null, null, null);
        $this->color = new \model\property_detail\ColorfulDetails(null, null, null);
        $this->estimatedDetails = new \model\property_detail\EstimatedData(null, null, null);
        $this->testing = true;
        $this->numberOfEmails = 0;
        $this->numberOfEmailsToBuyers = 0;
        $this->numberOfEmailsToSeller = 0;
        $this->changesMade = false;
        $this->numberOfEmailsToSeller = 0;
        $this->moneyPaid = 0;
        $this->dealTypeClassName = null;
        $this->assignment_fee = null;
        $this->contract_price = null;
        $this->escrow_co_name = null;
        $this->escrow_co_www = null;
        $this->completion_date = null;
        $this->offerMessages = null;
        $this->advertising_end_date = null;
        $this->is_active = true;
        $this->offerMessageStorageBehaviorClass = '\business\behavior\data_storage\offer_messages\ArrayStorage';

        //allow the behavior to prep object
        $className = $this->offerMessageStorageBehaviorClass;
        new $className($this);
    }

    public function __wakeup() {
        //just in case we fail to clear the changes when we save to database, we 
        //will clear it now.
        $this->clearChangesFlag();
        $this->is_active = (bool)$this->is_active;
    }
    
    public function setActiveState( $bool ){
        $this->is_active = $bool;
        $this->markChangeMade();
    }

    public function markEmailSent($useTime = null) {
        if (!\is_object($useTime)) {
            var_dump($useTime);
        }
        $now = ( $useTime === null ? new \DateTime("now") : clone $useTime );
        $this->setLastEmailTimestamp($now);
        $this->numberOfEmails += 1;
        $this->markChangeMade();
    }

    public function resetEmailCount() {
        $this->numberOfEmails = 0;
    }

    public function getEmailCount() {
        return $this->numberOfEmails;
    }

    public function advanceState($useTime = null) {
        $stateObj = \business\states\offer\OfferState::loadStateInOffer($this);
        $stateObj->advanceState($useTime);
    }

    public function performStateAction($useTime = null) {
        $state = \business\states\offer\OfferState::loadStateInOffer($this);
        /* @var $state \business\states\offer\OfferState */
        $state->performStateAction($useTime);
    }

    public function respondToEvent($eventMessage, \model\user\User $user = null) {
        $response = false;
        $stateObject = \business\states\offer\OfferState::loadStateInOffer($this);
        /* @var $stateObject \business\states\offer\OfferState */
        $response = $stateObject->respondToEvent($eventMessage, $user);
        $stateObject->saveObjectIfChanged();
        return $response;
    }

    public function getState() {
        return $this->state;
    }

    public function setConfirmationTime(\DateTime $time) {
        $this->confirmationTime = $time;
        $this->markChangeMade();
    }

    public function setState($newStateClassName) {
        $this->state = $newStateClassName;
        $this->setLastTransitionDate();
        $this->markChangeMade();
    }

    /**
     * @return \DateTime
     */
    public function getLastEmailTimestamp() {
        return $this->lastEmailTimestamp;
    }

    public function setLastEmailTimestamp(\DateTime $now) {
        $this->lastEmailTimestamp = $now;
        $this->markChangeMade();
    }

    public function getSellerEmail() {
        return $this->seller_email;
    }

    public function getId() {
        return $this->id;
    }

    public function __clone() {
        $this->facts = clone $this->facts;
        $this->color = clone $this->color;
        $this->estimatedDetails = clone $this->estimatedDetails;
    }

    public function isSameAs(\model\Offer $offer2) {
        $comaparisons = [
            $offer2->id == $this->id,
            $this->stringTime($offer2->confirmationTime) == $this->stringTime($this->confirmationTime),
            $this->stringTime($offer2->creationTime) == $this->stringTime($this->creationTime),
            $offer2->estimatedDetails->as_is_price == $this->estimatedDetails->as_is_price,
            $offer2->estimatedDetails->arv_price == $this->estimatedDetails->arv_price,
            $offer2->estimatedDetails->repairs_price == $this->estimatedDetails->repairs_price,
            $offer2->facts->atn == $this->facts->atn,
            $offer2->facts->beds == $this->facts->beds,
            $offer2->facts->full_baths == $this->facts->full_baths,
            $offer2->facts->half_baths == $this->facts->half_baths,
            $offer2->facts->three_qt_baths == $this->facts->three_qt_baths,
            $offer2->facts->sqft == $this->facts->sqft,
            $offer2->facts->street_address == $this->facts->street_address,
            $this->stringTime($offer2->lastEmailTimestamp) == $this->stringTime($this->lastEmailTimestamp),
            $offer2->getSellerEmail() == $this->getSellerEmail(),
            $offer2->state == $this->state,
            $offer2->testing == $this->testing
        ];
        //add view comparison
        //add requestView comparison
        $isSame = true;
        foreach ($comaparisons as $testResult) {
            $isSame = $isSame && $testResult;
        }
        return $isSame;
    }

    protected function stringTime($time) {
        $string = ($time->confirmationTime ? $time->confirmationTime->format("r") : null );
        return $string;
    }

    public function markChangeMade() {
        $this->changesMade = true;
    }

    public function clearChangesFlag() {
        $this->changesMade = false;
    }

    public function hasChanged() {
        return $this->changesMade;
    }

    public function getPrice() {
        return $this->priceForService;
    }

    public function setPrice($price) {
        $this->priceForService = $price;
        $this->markChangeMade();
    }

    public function setSellerEmail($email) {
        $this->seller_email = $email;
    }

    public function getMoneyPaid() {
        return $this->moneyPaid;
    }

    public function setDealTypeClass($class) {
        $this->dealTypeClassName = $class;
    }

    public function getDealTypeClass() {
        return $this->dealTypeClassName;
    }

    public function getContractPrice() {
        return $this->contract_price;
    }

    public function getAssignmentFee() {
        return $this->assignment_fee;
    }

    public function setAssignmentFee($fee) {
        $this->assignment_fee = $fee;
    }

    public function setContractPrice($price) {
        $this->contract_price = $price;
    }

    public function setEscrowCompanyName($companyName) {
        $this->escrow_co_name = $companyName;
    }

    public function setEscrowCompanyWebsite($companyWebsite) {
        $this->escrow_co_www = $companyWebsite;
    }

    public function setClosingDate(\DateTime $closeDate) {
        $this->closing_date = $closeDate;
    }

    public function setSellersPrivateThoughts($bestAlternate) {
        $this->private_thoughts = $bestAlternate;
    }

    public function setLowestPrice($lowestPrice) {
        $this->lowest_price = $lowestPrice;
    }

    public function getRepairCosts() {
        return $this->estimatedDetails->repairs_price;
    }

    public function setCreationDate(\DateTime $date) {
        $this->creationTime = $date;
    }

    public function getCreationDate() {
        return $this->creationTime;
    }

    public function getCompletionDate() {
        return $this->completion_date;
    }

    public function setCompletionDate(\DateTime $date) {
        $this->completion_date = $date;
    }

    public function addPayment($amount, $transaction_id, \DateTime $useDate = null ) {
        $this->addBraintreeTransactionId( $transaction_id );
        $this->moneyPaid += $amount;
        if( $this->moneyPaid >= $this->priceForService ){
            $this->date_service_paid = ( $useDate instanceof \DateTime ? $useDate : new \DateTime("now") );
        }
        $this->markChangeMade();
    }

    public function getClosingDate() {
        return $this->closing_date;
    }

    public function getOfferMessages() {
        return $this->offerMessages;
    }

    /**
     * @param mixed $data
     */
    public function setOfferMessages($data) {
        $this->offerMessages = $data;
        $this->markChangeMade();
    }

    public function countRespondingRecipients() {
        $class = $this->offerMessageStorageBehaviorClass;
        $behavior = new $class($this);
        /* @var $behavior \interfaces\OfferMessageStorageBehavior */
        $count = $behavior->countRespondingRecipients();
        $behavior = null;
        return $count;
    }

    public function countUniqueMessageRecipients() {
        $className = $this->offerMessageStorageBehaviorClass;
        $behavior = new $className($this);
        /* @var $behavior \interfaces\OfferMessageStorageBehavior */
        $count = $behavior->countUniqueMessageRecipients();
        $behavior = null;
        return $count;
    }

    /**
     * @param \model\user\User $user
     * @return \DateTime
     */
    public function getLastMessageTime(user\User $user) {
        $class = $this->offerMessageStorageBehaviorClass;
        $behavior = new $class($this);
        /* @var $behavior \interfaces\OfferMessageStorageBehavior */
        $time = $behavior->getLastMessageTime($user);
        $behavior = null;
        return $time;
    }

    public function recordMessageResponse($recipientEmail, $useTime = null) {
        $class = $this->offerMessageStorageBehaviorClass;
        $behavior = new $class($this);
        /* @var $behavior \interfaces\OfferMessageStorageBehavior */
        $result = $behavior->recordMessageResponse($recipientEmail, $useTime);
        $behavior = null;
        return $result;
    }

    public function saveMessageInstance(user\User $user, $useTime = null) {
        $class = $this->offerMessageStorageBehaviorClass;
        $behavior = new $class($this);
        /* @var $behavior \interfaces\OfferMessageStorageBehavior */
        $result = $behavior->saveMessageInstance($user, $useTime);
        $this->markEmailSent($useTime);
        $behavior = null;
        return $result;
    }

    public function getStreetAddress() {
        return $this->facts->street_address;
    }

    public function setAdvertisingEndDate($useDate = null) {
        $now = ( $useDate instanceof \DateTime ? $useDate : new \DateTime("now") );
        $this->advertising_end_date = $now;
    }

    public function getAdvertisingEndDate() {
        return $this->advertising_end_date;
    }

    public function setLastTransitionDate($useDate = null) {
        $now = ( $useDate instanceof \DateTime ? $useDate : new \DateTime("now") );
        $this->last_transition_date = $now;
    }

    public function getLastTransitionDate() {
        return $this->last_transition_date;
    }

    public function addBraintreeTransactionId($transaction_id) {
        if( !is_array( $this->braintree_payment_transactions ) ){
            $this->braintree_payment_transactions = [];
        }
        $this->braintree_payment_transactions[] = $transaction_id;
        $this->markChangeMade();
    }

    public function getObjectVars() {
        return \get_object_vars($this);
    }

}
