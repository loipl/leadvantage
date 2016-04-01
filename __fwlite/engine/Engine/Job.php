<?php

/**
 * @desc Current state of the job that Engine class is working on.
 */
class Engine_Job {

    const KIND_UNINITIALIZED = 'Uninitialized';
    const KIND_SUBMISSION    = 'Submission';
    const KIND_REPOSTING     = 'Reposting';

    private $kind = self::KIND_UNINITIALIZED;

    /**
     * @desc How many deliveries have partners accepted
     */
    public $successfulDeliveriesCount = 0;


    public $deliverySettings = array();

    public $currentDelivery = array();

    public $deliveryTried = false;


    public $maxDeliveries = 0;

    public $startTime = false;


    public $isSuccess = false;

    public $successURLs = array();

    public $finalSuccessUrl = '';


    public $partnersAssoc = array();

    public $partnersFieldsAssoc = array();

    /**
     * @desc assoc array (partner_id => array(data from partner_filters_table for that partner))
    */
    public $partnerFiltersAssoc = array();

    
    /**
     * @desc Flag that says whether campaignfilters accept current delivery. It will be turned off
     * if there is a match for rule that forbids delivery
     * @var boolean 
     */
    public $campaignFiltersOK = false;

    /**
     * @desc This will hold partner response split by string that's set in campaign settings,
     * in case Partner response option is turned on
     */
    public $partnerResponseParsed = array();


    public $partnersAlreadyDeliveredTo = array();

    /**
     * @desc holds IDs of partners from delivery settings, not filtered.
     */
    public $partnerIDsForDelivery = array();


    /**
     * @desc Set to true if we're skimming a campaign
     */
    public $skimming = false;

    /**
     * @desc If we should mark the delivery in DB as repost
     */
    public $markAsRepost = false;

    /**
     * @desc Counter how many deliveries to partner we processed when reposting
     */
    public $repostCount = 0;
    
    /**
     * @desc determine if at least one real partner (not campaign wrapped partner) accepted the lead. 
     * This is set if a real partner accepted the lead.
     * Used to check whether there is an accepted real partner in delivery setting lists
     */
    public $atleastOneRealPartnerAccepted = false;
    
    /**
     * @desc check if an OOB campaign exists in delivery setting list. 
     * This is set if an campaign wrapper parter is in API mode (is_oob = 1)
     */
    public $existOOBCampaignInDeliveryList = false;

    public $lastPartnerWasSkipped = false;

    public $allSkipped = false;


    /**
     * @var Partner
    */
    public $currentPartner;

    /**
     * @var Campaign
     */
    public $campaign;
    
    /**
     * store responses of partners in Ping process
     */
    public $pingPartnerResponses = array();
    
    
     /**
     * message about the delivery, eg: skip reason
     * @var String 
     */
    public $message = '';

    
    public function __construct($kind = self::KIND_UNINITIALIZED) {
        $this->setKind($kind);
        $this->currentPartner = new Partner();
        $this->campaign       = new Campaign();
    }
    //--------------------------------------------------------------------------


    public function reset() {
        $this->successfulDeliveriesCount = 0;

        $this->deliverySettings = array();
        $this->currentDelivery  = array();
        $this->deliveryTried    = false;

        $this->maxDeliveries    = 0;
        $this->startTime        = time();

        $this->isSuccess        = false;
        $this->successURLs      = array();
        $this->finalSuccessUrl  = '';

        $this->partnersAssoc         = array();
        $this->partnerFiltersAssoc   = array();
        $this->partnersFieldsAssoc   = array();

        $this->partnerResponseParsed = array();

        $this->partnersAlreadyDeliveredTo = array();
        $this->partnerIDsForDelivery      = array();

        $this->lastPartnerWasSkipped      = false;
        $this->allSkipped                 = false;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Kind of this job - reposting, processing submission or none yet
     */
    public function getKind() {
        return $this->kind;
    }
    //--------------------------------------------------------------------------

    /**
     * @desc Kind of this job - reposting, processing submission or none yet
     */
    public function setKind($kind) {
        if (in_array($kind, array(self::KIND_UNINITIALIZED, self::KIND_SUBMISSION, self::KIND_REPOSTING))) {
            $this->kind = $kind;
        } else {
            throw new EServerError("Invalid job kind '$kind'");
        }
    }
    //--------------------------------------------------------------------------


    public function isReposting() {
        return $this->kind == self::KIND_REPOSTING;
    }
    //--------------------------------------------------------------------------


    public function processingTimedOut() {
        return (time() - $this->startTime) > Engine_Settings::$settings->processingTimeout;
    }
    //--------------------------------------------------------------------------


    public function isCurrentDeliveryAlwaysOn() {
        return !empty($this->currentDelivery['always_send']);
    }
    //--------------------------------------------------------------------------


    public function startingSubmissionDelivery() {
        $this->reset();
    }
    //--------------------------------------------------------------------------


    public function startNewDelivery(array $delivery) {
        $this->currentDelivery       = $delivery;
        $this->partnerResponseParsed = array();
        $this->message               = '';

        $partnerId = (int)$delivery['partner_id'];
        if (!empty($this->partnersAssoc[$partnerId])) {
            $this->currentPartner->row = $this->partnersAssoc[$partnerId];
            if ($this->canAllowCurrentDelivery()) {
                $this->deliveryTried = true;
                return true;
            }
        }
        return false;
    }
    //--------------------------------------------------------------------------


    public function canAllowCurrentDelivery() {
        $canAllow = $this->isCurrentDeliveryAlwaysOn() || ($this->successfulDeliveriesCount < $this->maxDeliveries);
        if (!$canAllow) {
            return false;
        }
        return !in_array($this->currentPartner->getId(), $this->partnersAlreadyDeliveredTo);
    }
    //--------------------------------------------------------------------------


    public function getPartnerFieldsForCurrentPartner() {
        $id = $this->currentPartner->getId();
        return isset($this->partnersFieldsAssoc[$id]) ? $this->partnersFieldsAssoc[$id] : array();
    }
    //--------------------------------------------------------------------------


    public function deliveryAccepted($successUrl) {
        $this->isSuccess = true;

        if ($successUrl) {
            if ($this->campaign->isAppendMultipleSuccessUrls()) {
                $this->successURLs[] = $successUrl;
            }
            $this->finalSuccessUrl = $successUrl;
        }

        if ($this->shouldCountDelivery()) {
            $this->successfulDeliveriesCount++;
        }
    }
    //--------------------------------------------------------------------------


    public function updateStateAfterProcessingRepost() {
        if ($this->isSuccess) {
            $this->allSkipped = $this->allSkipped && $this->lastPartnerWasSkipped;

            $shouldOmitAlwaysOnDeliveryFromCount = $this->isCurrentDeliveryAlwaysOn() && !$this->campaign->isCapalp();
            if (! $shouldOmitAlwaysOnDeliveryFromCount) {
                $this->successfulDeliveriesCount++;
            }
        }
    }
    //--------------------------------------------------------------------------


    public function shouldCountDelivery() {
        $shouldSkipCounting = $this->isCurrentDeliveryAlwaysOn() && empty($this->campaign->row['capalp']);
        return !$shouldSkipCounting;
    }
    //--------------------------------------------------------------------------


    public function startingRepostDelivery() {
        $this->reset();
        $this->allSkipped = true;
    }
    //--------------------------------------------------------------------------


    public function canDeliverToSomeone() {
        return !empty($this->partnersAssoc) && !empty($this->deliverySettings);
    }
    //--------------------------------------------------------------------------
}

