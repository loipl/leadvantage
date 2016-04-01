<?php

/**
 * @desc Wrapper for data from campaigns table, with some useful functions. Checks on
 * the campaign should be done with these utility functions and not directly on array.
 */
class Campaign {
    
    const CAMPAIGN_TIMEOUT_DEFAULT = 60;

    public $row = array();


    public function __construct(array $row = array()) {
        $this->row = $row;
    }
    //--------------------------------------------------------------------------


    public function getId() {
        return (int)$this->row['id'];
    }
    //--------------------------------------------------------------------------


    public function getUserId() {
        return (int)$this->row['user_id'];
    }
    //--------------------------------------------------------------------------


    public function isActive() {
        return !empty($this->row['is_active']);
    }
    //--------------------------------------------------------------------------


    public function isMaster() {
        return !empty($this->row['is_master']);
    }
    //--------------------------------------------------------------------------


    public function isAppendMultipleSuccessUrls() {
        return !empty($this->row['append_succ_url']);
    }
    //--------------------------------------------------------------------------


    /**
     * <b>C</b>ount <b>A</b>lways=yes <b>P</b>artners <b>A</b>gainst <b>L</b>eads <b>P</b>er
     * @return boolean
     */
    public function isCapalp() {
        return !empty($this->row['capalp']);
    }
    //--------------------------------------------------------------------------


    public function isEmailFieldVerificationOn() {
        return !empty($this->row['email_field_verify']);
    }
    //--------------------------------------------------------------------------


    public function isShouldRepost() {
        return !empty($this->row['should_repost']);
    }
    //--------------------------------------------------------------------------


    public function getSourceCampaignId() {
        return (int)$this->row['shadow_of'];
    }
    //--------------------------------------------------------------------------


    public function getFailureUrl() {
        return $this->row['failure_url'];
    }
    //--------------------------------------------------------------------------


    public function getSuccessUrl() {
        return $this->row['success_url'];
    }
    //--------------------------------------------------------------------------


    public function getLeadsPer() {
        return (int)$this->row['leads_per'];
    }
    //--------------------------------------------------------------------------


    public function getRepostHours() {
        return (int)$this->row['repost_hours'];
    }
    //--------------------------------------------------------------------------


    public function getRepostMax() {
        return (int)$this->row['repost_max'];
    }
    //--------------------------------------------------------------------------


    public function getOOBCapType() {
        return (int)$this->row['oob_cap_type'];
    }
    //--------------------------------------------------------------------------


    public function getOOBCapValue() {
        return (int)$this->row['oob_cap_value'];
    }
    //--------------------------------------------------------------------------


    public function getPartnerRetries() {
        return (int)$this->row['partner_retries'];
    }
    //--------------------------------------------------------------------------


    public function getShadowDelayHours() {
        return (int)$this->row['shadow_delay_hours'];
    }
    //--------------------------------------------------------------------------


    public function getEmailFieldName() {
        return !empty($this->row['email_field_name']) ? $this->row['email_field_name'] : 'email';
    }
    //--------------------------------------------------------------------------


    public function getEmailFieldMandatory() {
        return $this->row['email_field_mandatory'];
    }
    //--------------------------------------------------------------------------

    
    public function getEmailFieldDescription() {
        return $this->row['email_field_description'];
    }
    //--------------------------------------------------------------------------


    public function getEmailFieldVerify() {
        return (int)$this->row['email_field_verify'];
    }
    //--------------------------------------------------------------------------

    
    public function getRoundRobin() {
        return (int)$this->row['round_robin'];
    }
    //--------------------------------------------------------------------------
    
    
    public function getRoundRobinLastIndex() {
        return $this->row['round_robin_last_index'];
    }
    //--------------------------------------------------------------------------
    
    
    public function getDeliveryType() {
        return intval($this->row['delivery_type']);
    }
    //--------------------------------------------------------------------------
    
    
    public function getResponseType() {
        return intval($this->row['response_type']);
    }
    //--------------------------------------------------------------------------
    
    
    public function getPingPostStatus() {
        return intval($this->row['is_pingpost']);
    }
    //--------------------------------------------------------------------------
    
    
    public function getPingAndPostStatus() {
        return intval($this->row['is_ping_and_post']);
    }
    //--------------------------------------------------------------------------
    
    
    public function getAuctionStatus() {
        return intval($this->row['auction']);
    }
    //--------------------------------------------------------------------------
    
    
    public function getBidResponseType() {
        return intval($this->row['bid_response_type']);
    }
    //--------------------------------------------------------------------------

    
    public function getBidResponseValue() {
        return floatval($this->row['bid_response_value']);
    }
    //--------------------------------------------------------------------------

    
    public function getCampaignTimeout() {
        $timeout = intval($this->row['campaign_timeout']);
        return ($timeout >= self::CAMPAIGN_TIMEOUT_DEFAULT) ? $timeout : self::CAMPAIGN_TIMEOUT_DEFAULT;
    }
    //--------------------------------------------------------------------------
    
    
    public function isPingPostCampaign() {
        $pingPostStatus = $this->getPingPostStatus();
        return !empty($pingPostStatus);
    }
    //--------------------------------------------------------------------------
    
    
    public function isPingAndPostCampaign() {
        $pingAndPostStatus = $this->getPingAndPostStatus();
        return !empty($pingAndPostStatus);
    }
    //--------------------------------------------------------------------------
    
    
    public function isAuction() {
        $auction = $this->getAuctionStatus();
        return !empty($auction);
    }
    //--------------------------------------------------------------------------
    
    
    public function isRoundRobin() {
        $roundRobin = $this->getRoundRobin();
        return !empty($roundRobin);
    }
    //--------------------------------------------------------------------------
    
    
    public function isQueuedDelivery() {
        $campaignDeliveryType = $this->getDeliveryType();
        
        if (isset(Model_Campaign::$deliveryTypes[$campaignDeliveryType])) {
            return Model_Campaign::$deliveryTypes[$campaignDeliveryType] === Model_Campaign::DELIVERY_TYPE_QUEUED;
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function isAPIResponse() {
        $campaignResponseType = $this->getResponseType();
        
        if (isset(Model_Campaign::$responseTypes[$campaignResponseType])) {
            return Model_Campaign::$responseTypes[$campaignResponseType] === Model_Campaign::RESPONSE_TYPE_API;
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function hasValidOOBCap() {
        return
            !empty($this->row['is_oob_capped']) &&
            !empty($this->row['oob_cap_value']) &&
            Model_Partner::isValidCapType($this->row['oob_cap_type']);
    }
    //--------------------------------------------------------------------------


    public function sanityCheckForParasiteShadowCampaign() {
        if ($this->row['shadow_of'] && ($this->row['skim_percentage'] == 0) && $this->row['delivery_type'] != strval(Model_Campaign::DELIVERY_TYPE_QUEUED_KEY)) {
            $this->row['delivery_type'] = strval(Model_Campaign::DELIVERY_TYPE_QUEUED_KEY);
        }
    }
    //--------------------------------------------------------------------------
    
    
    /**
     * 
     * @param int $bidPrice
     * @param Campaign $campaign
     * @return int
     */
    public function processPriceUsingBidResponse($bidPrice, $campaign) {
        if (!is_numeric($bidPrice)) {
            return 0;
        }
        
        $bidResponseType  = $campaign->getBidResponseType();
        $bidResponseValue = $campaign->getBidResponseValue();
        $result = 0;
        
        switch ($bidResponseType) {
            case Model_Campaign::BID_RESPONSE_PERCENT:
                $result = $bidPrice * (1 - $bidResponseValue/100);
                break;
            case Model_Campaign::BID_RESPONSE_FLAT_RATE:
                $result = $bidPrice - $bidResponseValue;
                break;
            case Model_Campaign::BID_RESPONSE_FIXED:
                $result = $bidResponseValue;
                break;
        }
        
        if ($result >= 0) {
            return floatval($result);
        }
        return 0;
    }
    //--------------------------------------------------------------------------
}
