<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace business\behavior\dealTypes;

/**
 * Description of ContractAssignment
 *
 * @author jrc
 */
class ContractAssignment extends \business\behavior\dealTypes\DealType {
    /**
     * @param \model\Offer $offer
     * @return \business\behavior\dealTypes\ContractAssignment
     */
    static public function applyToOffer(\model\Offer $offer) {
        return parent::_setDealType($offer, __CLASS__);
    }

    public function applyDataFromWebForm($propertyData) {
        /* @var $propertyData \model\DirtyProperty */
        $assignData = $propertyData->assignment_detail;
        $offer = $this->getOffer();
        $assignFee = $assignData->fee;
        $contractPrice = $assignData->contract_price;
        $closeDate = new \DateTime( $assignData->closing_date );
        $companyName = $assignData->escrow->name;
        $companyWebsite = $assignData->escrow->url;
        $offer->setAssignmentFee( $assignFee );
        $offer->setContractPrice( $contractPrice );
        $offer->setEscrowCompanyName( $companyName );
        $offer->setEscrowCompanyWebsite( $companyWebsite );
        $offer->setClosingDate( $closeDate );
    }

    public function getUpfrontCostToBuyer() {
        $offer = $this->getOffer();
        $totalCost = $offer->getAssignmentFee() + $offer->getContractPrice();
        return $totalCost;
    }

    public function setCloseDate( \DateTime $closingDate) {
        $offer = $this->getOffer();
        $offer->setClosingDate($closingDate);
    }
    
    public function getClosingDate(){
        $offer = $this->getOffer();
        return $offer->getClosingDate();
    }

}
