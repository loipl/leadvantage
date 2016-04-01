<?php

class Partner {
    public $row = array();


    public function __construct(array $row = array()) {
        $this->row = $row;
    }
    //--------------------------------------------------------------------------


    public function getId() {
        if (!empty($this->row['id'])) {
            return (int)$this->row['id'];
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------


    public function isShouldRetry() {
        return !empty($this->row['should_retry']);
    }
    //--------------------------------------------------------------------------


    public function getWrappedCampaignId() {
        return (int)$this->row['wrapped_campaign_id'];
    }
    //--------------------------------------------------------------------------


    public function isParseResponse() {
        return !empty($this->row['parse_response']);
    }
    //--------------------------------------------------------------------------


    public function getDelimiter() {
        return $this->row['delimiter'];
    }
    //--------------------------------------------------------------------------


    public function getSuccessKeyword() {
        return trim($this->row['success_keyword']);
    }
    //--------------------------------------------------------------------------


    public function getFailureKeyword() {
        return trim($this->row['failure_keyword']);
    }
    //--------------------------------------------------------------------------


    public function getUsernameFailedKeyword() {
        return trim($this->row['username_failed_keyword']);
    }
    //--------------------------------------------------------------------------


    public function getSuccessUrl() {
        return trim($this->row['success_url']);
    }
    //--------------------------------------------------------------------------


    public function setSuccessUrl($value) {
        return $this->row['success_url'] = trim($value);
    }
    //--------------------------------------------------------------------------


    public function getCreatedAt() {
        return $this->row['created_at'];
    }
    //--------------------------------------------------------------------------


    public function getDeliveryAddr() {
        return trim($this->row['delivery_addr']);
    }
    //--------------------------------------------------------------------------


    public function setDeliveryAddr($value) {
        return $this->row['delivery_addr'] = trim($value);
    }
    //--------------------------------------------------------------------------


    public function getDeliveryCap() {
        return (int)$this->row['delivery_cap'];
    }
    //--------------------------------------------------------------------------


    public function getPostingType() {
        return (int)$this->row['posting_type'];
    }
    //--------------------------------------------------------------------------


    public function getTemplate() {
        return $this->row['template'];
    }
    //--------------------------------------------------------------------------


    public function getDeliveryCapType() {
        return (int)$this->row['delivery_ctype'];
    }
    //--------------------------------------------------------------------------


    public function getPricePerLead() {
        return $this->row['price_per_lead'];
    }
    //--------------------------------------------------------------------------
    
    public function getTransactionId() {
        return $this->row['auction_transaction_id'];
    }
    //--------------------------------------------------------------------------
    
    public function getResponseType() {
        return isset($this->row['response_type']) ? $this->row['response_type'] : '0';
    }
    //--------------------------------------------------------------------------
    
    public function getCurlTimeout() {
        return $this->row['curl_timeout'];
    }
    //--------------------------------------------------------------------------
    
    
    public function getPartnerTimezone() {
        if (!empty($this->row['partner_timezone'])) {
            return $this->row['partner_timezone'];
        } else {
            return null;
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function getXmlFieldName() {
        return $this->row['xml_field_name'];
    }
    //--------------------------------------------------------------------------
    
    
    public function getTransactionIdFieldName() {
        return $this->row['transaction_id_field_name'];
    }
    //--------------------------------------------------------------------------
    
    
    public function FF_Encrypt_Ticket($uname='',$fname='',$lname='',$password='',$email='',$zipcode='',$city='',$state='',$country='',$sex='',$birthdate='',$newpassword='',$subpid='',$title='',$profile='',$looking_for_desc='',$looking_for_person='',$ip_address='',$language='') {

        $in = 'ENC:'.implode('|',array($uname,$fname,$lname,$password,$email,$zipcode,$city,$state,$country,$sex,$birthdate,$newpassword,$subpid,$title,$profile,$looking_for_desc,$looking_for_person,$ip_address,$language));

        $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*:?_+-=|\'";.,/ ';
        $key     = isset($this->row['settings'][Model_PartnerSettings::SETTING_AFF_API_KEY]) ? $this->row['settings'][Model_PartnerSettings::SETTING_AFF_API_KEY] : '';
        if (!$key) {
            return '';
        }

        $pos = 0;
        $out = '';
        while ($pos < strlen($in)) {
            $out .= substr($key, strpos($letters, substr($in, $pos, 1)), 1);
            $pos++;
        }

        $out = base64_encode($out);
        return $out;
    }
    //--------------------------------------------------------------------------
}
