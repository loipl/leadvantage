<?php

/**
 * @desc Engine class for processing reposts
 */
class Engine_Repost extends Engine {

    /**
     * @desc Whether to remove partners from delivery list if we're running a shadow
     * campaign and partners are present in source campaign
     */
    public $removePartnersFromSourceCampaign = true;

    /**
     * @desc This will have the ID of only partner that we should deliver to.
     */
    public $catchingUpPartnerId = false;

    public $catchupJobId = false;

    public $catchupJobIsRateLimited = false;

    public $activityCallback = false;


    public function processRepost(array $incRow, array $campaign, $batchUploadLeadFlag = null) {
        $this->initializeForRepost($incRow, $campaign);
        $this->restoreValuesFromOriginalSubmission($incRow);
        $this->setUserRightsForOwnerOfActiveCampaign();
        $this->setFlagIfFirstRepostOfOOBCampaign();
        
        $this->executeRepostAndCheckIfDeliveryAlreadyDone($batchUploadLeadFlag);

        return $this->job->repostCount;
    }
    //--------------------------------------------------------------------------


    private function initializeForRepost(array $incRow, array $campaign) {
        $this->startingNewJob(Engine_Job::KIND_REPOSTING);
        $this->job->startingRepostDelivery();
        $this->assignCampaign($campaign);

        $this->job->repostCount  = 0;
        $this->postponeCatchupEntry = false;
    }
    //--------------------------------------------------------------------------


    private function restoreValuesFromOriginalSubmission(array $incRow) {
        $_SERVER['REMOTE_ADDR']   = $incRow['remote_ip'];
        $this->data->incRow       = $incRow;
        $this->data->incomingId   = (int)$incRow['id'];

        $this->data->inputData    = $this->data->logIncoming->listSubmissionValues($this->data->incomingId);

        // Mail address is kept in log_incoming table, separately from other
        // field types and will not be present in data returned by listSubmissionValues()
        // so it has to be added separately
        $this->data->inputData[Model_CampaignField::FIELD_TYPE_EMAIL] = $incRow['email'];

        $this->data->incomingStatus    = $incRow['is_success'];
        $this->data->incomingTimestamp = $incRow['request_time'];
    }
    //--------------------------------------------------------------------------


    private function setUserRightsForOwnerOfActiveCampaign() {
        $this->data->ownerUserCaps = $this->getOwnerUserCaps($this->job->campaign->getUserId());
        $this->data->cvFTypes      = $this->data->modelUser->canValidateFieldTypes($this->data->ownerUserCaps);
    }
    //--------------------------------------------------------------------------


    private function setFlagIfFirstRepostOfOOBCampaign() {
        // We do not mark first processing of an OOB submission as repost
        $countExistingDeliveries  = $this->data->logDelivery->countDeliveriesForIncomingIdAndCampaignId($this->data->incomingId, $this->job->campaign->getId());
        $this->job->markAsRepost = ($countExistingDeliveries != 0);
    }
    //--------------------------------------------------------------------------


    private function executeRepostAndCheckIfDeliveryAlreadyDone($batchUploadLeadFlag) {
        try {

            $this->initPartnersListForRepost(null, $batchUploadLeadFlag);
            
            if ($this->job->canDeliverToSomeone()) {
                $this->readAdditionalDataForPartnersListForRepost();
                
                $this->removePartnerAcceptedLeadOrTriedMoreThanCampaignAllow();
                
                if ($this->job->campaign->isPingPostCampaign()) {
                    $this->deliveryEngine->pingToAllDeliveryPartnersBeforeDelivery();
                } elseif ($this->job->campaign->isPingAndPostCampaign()) {
                    $this->deliveryEngine->postToPartnersAfterGetDataFromPing();
                } else {
                    $this->repostToPartners();
                }
                
                // $this->job->markAsRepost is false only for first repost, so condition is if all partners were skipped
                // (because the filters stopped delivery) and this is the initial repost of OOB campaign
                if ($this->job->allSkipped && !$this->job->markAsRepost && $this->job->campaign->isQueuedDelivery()) {
                    $this->data->logIncoming->increaseSnubCountInIncomingSummaryTable($this->job->campaign->getId(), $this->data->incRow['request_time']);
                }
            }
            
            if ($batchUploadLeadFlag) {
                // unlock partner after finishing delivery & log
                $partnerIds = $this->job->partnerIDsForDelivery;
                $this->unlockPartners($partnerIds);
            }
                
            $this->postponeOrLogRepost();

            // Finally
            $this->removeSubmissionFromQueueIfPartnerListExhausted();
        } catch (Exception $e) {

            // Finally
            $this->removeSubmissionFromQueueIfPartnerListExhausted();
            throw $e;
        }
    }
    //--------------------------------------------------------------------------


    public function initPartnersListForRepost($deliverySettings = null, $batchUploadLeadFlag = null) {
        if ($this->catchingUpPartnerId) {
            $this->job->deliverySettings = array(array('partner_id' => $this->catchingUpPartnerId, 'order_nr' => 1, 'always_send' => 1));
        } else {
            $this->job->deliverySettings = $this->data->model->listDeliverySettings($this->job->campaign->getId());
        }

        if (!empty($deliverySettings)) {
            $this->job->deliverySettings = $deliverySettings;
        }
        
        $this->job->partnerIDsForDelivery = array();
        foreach ($this->job->deliverySettings as $row) {
            $this->job->partnerIDsForDelivery[] = (int)$row['partner_id'];
        }

        $this->job->partnersAssoc = $this->figureOutPartnersToDeliverToForRepost();
     
        if ($batchUploadLeadFlag) {   
            // check if partner has been locked, wait until partner unlocked
            $lockSuccess = false;
            while ($lockSuccess === false) {
                while ($this->checkIfAtLeastOnePartnerHadBeenLocked()) {
                    usleep(mt_rand(100000, 1000000));
                }

                $lockSuccess = true;
                foreach ($this->job->partnersAssoc as $row) {

                    // lock partner if it matches conditions: has Cap, >= 80% * cap value
                    if ($this->lockPartnerIfMatchesConditions($row) === false) {
                        $lockSuccess = false;
                        break;
                    }
                }
            }
            
            $this->job->partnersAssoc = $this->figureOutPartnersToDeliverToForRepost();
        }
    }
    //--------------------------------------------------------------------------


    /**
     * check if partner has been locked, wait until partner unlocked
     */
    public function checkIfAtLeastOnePartnerHadBeenLocked() {
        $partnerIds = $this->job->partnerIDsForDelivery;
        if (empty($partnerIds)) {
            return false;
        }
        
        $modelPartner = SingletonRegistry::getModelPartner();
        $count = $modelPartner->countLockedPartner($partnerIds);
        return ($count > 0);
        
    }
    //--------------------------------------------------------------------------
   
    
    /*
     * lock partner if it matches conditions: has Cap, >= 80% * cap value
     */
    public function lockPartnerIfMatchesConditions($row) {
        $partner = new Partner();
        $partner->row = $row;
        
        $mpc = SingletonRegistry::getModelPartnerCap();
        $modelPartner = SingletonRegistry::getModelPartner();
        
        if ($row['ping_post_type'] == Model_Partner::PING_POST_TYPE_PING) {
            $caps = $mpc->listAllForPartner($row['post_partner_id']);
        } else {
            $caps = $mpc->listAllForPartner($partner->getId());
        }

        
        if (empty($caps)) {
            return true;
        }
        
        foreach ($caps as $cap) {
            $cap['capTypes'] = $mpc->getIntervals();
            
            switch ($cap['cap_type']) {
                case $mpc::CAP_TYPE_DELIVERY:
                    $alreadyDelivered = $this->data->logDelivery->getDeliveriesToPartner($cap, $partner->getPartnerTimezone());
                    if ($alreadyDelivered === false) {
                        break;
                    }
                    
                    $lockThreshold = intval($cap['cap_value']) - $modelPartner::NUMBER_OF_THREAD;
                    
                    if (intval($alreadyDelivered) >= $lockThreshold) {
                        return $modelPartner->lockPartner($partner->getId());
                    }
                    break;
                    
                case $mpc::CAP_TYPE_BUDGET:
                    $alreadyBudget = $this->data->logDelivery->getBudgetToPartner($cap, $partner->getPartnerTimezone());
                    if ($alreadyBudget === false) {
                        break;
                    }
                    
                    $lockThreshold = floatval($modelPartner::LOCK_THRESHOLD * $cap['cap_value']);
                    
                    if (floatval($alreadyBudget) >= $lockThreshold) {
                        return $modelPartner->lockPartner($partner->getId());
                    }
                    break;
                    
                default:
                    break;
            }
            
        }
        
        return true;
    }
    //--------------------------------------------------------------------------
    
    /*
     * unlock partner after finishing delivery & log
     */
    public function unlockPartners($partnerIds) {
        $modelPartner = SingletonRegistry::getModelPartner();
        
        return $modelPartner->unlockPartners($partnerIds);
    }
    //--------------------------------------------------------------------------
    
    
    public function readAdditionalDataForPartnersListForRepost() {
        /* @var $modelPartnerSettings Model_PartnerSettings */
        $modelPartnerSettings = SingletonRegistry::getSingleInstance('Model_PartnerSettings');
        $this->job->partnersAssoc = $modelPartnerSettings->attachSettingsForPartnerList($this->job->partnersAssoc);
        $this->job->partnersFieldsAssoc = $this->data->modelPartnerField->listAssocForPartnerGroup(array_keys($this->job->partnersAssoc));

        $this->job->maxDeliveries = max((int)$this->job->campaign->getLeadsPer(), 1);


        // Getting list of partners who accepted, not also who skipped this lead
        // because partner rules may have changed since last delivery attempt
        $deliveredToIDs = $this->data->logDelivery->listPartnersDeliveredTo($this->data->incomingId, $this->job->campaign->getId(), true);
        $this->job->partnersAlreadyDeliveredTo = $deliveredToIDs;

        $this->job->partnerFiltersAssoc = $this->data->modelPartnerFilter->listAssocForPartnerGroup(array_keys($this->job->partnersAssoc), true);
    }
    //--------------------------------------------------------------------------


    private function repostToPartners() {
        $deliverySettings = $this->job->deliverySettings;

        // check if round-robin mode is enable
        if ($this->job->campaign->isRoundRobin()) {
            $roundRobinLastIndex = $this->job->campaign->getRoundRobinLastIndex();
            $deliverySettings = $this->getRoundRobinDeliverySettingAndUpdateIndex($deliverySettings, $roundRobinLastIndex);
        }

        foreach ($deliverySettings as $delivery) {

            if ($this->job->startNewDelivery($delivery)) {
                $this->repostToCurrentPartner();
            }
        }
    }
    //--------------------------------------------------------------------------


    private function postponeOrLogRepost() {
        if ($this->postponeCatchupEntry) {
            $this->data->logIncomingRepost->postponeRepostQueueEntry($this->data->incomingId, $this->job->campaign->getId(),
                            $this->postponeCatchupEntry, $this->catchupJobIsRateLimited ? $this->catchupJobId : 0);
            $this->job->repostCount = 1;
        } else {
            $repostData = array('incoming_id' => $this->data->incomingId, 'campaign_id' => $this->job->campaign->getId(), 'job_id' => $this->catchupJobId);
            $this->data->logIncomingRepost->log($repostData);
        }
    }
    //--------------------------------------------------------------------------


    public function repostToCurrentPartner() {
        $deliveryAddressProcessed = $this->processUrlWithCampaignFields($this->job->currentPartner->getDeliveryAddr());
        $this->job->currentPartner->setDeliveryAddr($deliveryAddressProcessed);

        $this->job->isSuccess = $this->deliveryEngine->deliverToCurrentPartner();
        $this->job->updateStateAfterProcessingRepost();
    }
    //--------------------------------------------------------------------------


    private function figureOutPartnersToDeliverToForRepost() {
        $partnersList = $this->listEligiblePartnersForRepost();

        if (!$partnersList) {
            return array();
        }

        if ($this->shouldRemovePartnersFromSourceCampaign()) {
            $partnerIDs = $this->partnerIdsUsedBySourceCampaign();
            if ($partnerIDs) {
                $forbiddenDomains = $this->domainsUsedByPartnersList($partnerIDs);
                $partnersList     = $this->removePartnersThatDeliverToDomains($partnersList, $forbiddenDomains);
            }
        }
        $this->report(Engine::ACTION_DELIVER_REP_LIST, $partnersList);
        return $partnersList;
    }
    //--------------------------------------------------------------------------


    private function listEligiblePartnersForRepost() {
        $this->data->attemptsPerPartnerAssoc = $this->data->logDelivery->countOfDeliveryAttemptsGroup($this->data->incomingId, $this->job->campaign->getId(), $this->job->partnerIDsForDelivery);
        if ($this->catchingUpPartnerId) {
            return $this->createPartnerListWithJustCachupPartnerInIt();
        } else {
            return $this->listRepostPartnersForFullDelivery();
        }
    }
    //--------------------------------------------------------------------------


    private function createPartnerListWithJustCachupPartnerInIt() {
        if ($this->retriedPartnerTooManyTimesForThisSubmission($this->catchingUpPartnerId)) {
            return array();
        }

        $partnerRow = $this->data->modelPartner->get($this->catchingUpPartnerId);
        if (!$partnerRow) {
            return array();
        }

        $partner = new Partner($partnerRow);
        $checkCap = $this->isPartnerWithinCaps($partner);
        
        if ($checkCap === true) {
            return array($this->catchingUpPartnerId => $partnerRow);
        } else {
            $mpc = SingletonRegistry::getModelPartnerCap();
            $intervals = $mpc->getIntervals();
            $capType = $checkCap['interval'];
            $this->setPostponeFlagForCapType($intervals[$capType]);
            $this->deliveryEngine->logCapExceededDelivery($partnerRow, $checkCap);
            return array();
        }
    }
    //--------------------------------------------------------------------------


    private function setPostponeFlagForCapType($deliveryCapType) {
        $capType = isset(Model_Partner::$deliveryTypes[$deliveryCapType]) ? Model_Partner::$deliveryTypes[$deliveryCapType] : 0;

        switch ($capType) {
            case Model_Partner::DELIVERY_CTYPE_HOUR:
            case Model_Partner::DELIVERY_CTYPE_DAY:
                $this->postponeCatchupEntry = 1;
                break;
            case Model_Partner::DELIVERY_CTYPE_WEEK:
            case Model_Partner::DELIVERY_CTYPE_MONTH:
            case Model_Partner::DELIVERY_CTYPE_EVER:
                $this->postponeCatchupEntry = 24;
                break;
            default:
                // Default is here in case we add more delivery cap types later
                $this->postponeCatchupEntry = 24;
        }
    }
    //--------------------------------------------------------------------------


    private function retriedPartnerTooManyTimesForThisSubmission($partnerId) {
        $attemptsForThisSubmission = isset($this->data->attemptsPerPartnerAssoc[$partnerId]) ? (int)$this->data->attemptsPerPartnerAssoc[$partnerId] : -1;
        return $attemptsForThisSubmission > $this->job->campaign->getPartnerRetries();
    }
    //--------------------------------------------------------------------------


    private function shouldRemovePartnersFromSourceCampaign() {
        return $this->removePartnersFromSourceCampaign && $this->job->campaign->getSourceCampaignId() && !$this->job->skimming && !$this->catchingUpPartnerId;
    }
    //--------------------------------------------------------------------------


    private function domainsUsedByPartnersList($partnerIDs) {
        $partners = $this->data->modelPartner->listGroup($partnerIDs);
        $domains = array();
        foreach ($partners as $partnerRow) {
            $domains[] = Engine_Utilities::toFQDN(parse_url($partnerRow['delivery_addr'], PHP_URL_HOST));
        }
        return array_unique($domains);
    }
    //--------------------------------------------------------------------------


    private function removePartnersThatDeliverToDomains(array $partnersList, array $domains) {
        foreach ($partnersList as $k => $partner) {
            $fqdn = Engine_Utilities::toFQDN(parse_url($partner['delivery_addr'], PHP_URL_HOST));
            if (in_array($fqdn, $domains)) {
                unset($partnersList[$k]);
            }
        }
        return $partnersList;
    }
    //--------------------------------------------------------------------------


    private function partnerIdsUsedBySourceCampaign() {
        $sourceCampaignId = $this->job->campaign->getSourceCampaignId();

        $submissionStillInRepostQueue = $this->data->logIncoming->countRepostQueues($this->data->incomingId, $sourceCampaignId);
        if ($submissionStillInRepostQueue) {
            $allPartnerIDs = array();
            foreach ($this->data->model->listDeliverySettings($sourceCampaignId) as $row) {
                $allPartnerIDs[] = (int)$row['partner_id'];
            }

            return $allPartnerIDs;
        } else {
            $usedUpPartnerIDs = $this->data->logDelivery->listUsedUpPartnerIds($this->data->incomingId, $sourceCampaignId);
            return $usedUpPartnerIDs;
        }
    }
    //--------------------------------------------------------------------------


    private function listRepostPartnersForFullDelivery() {
        $partnersList = array();
        $partner  = new Partner();

        foreach ($this->data->modelPartner->listGroup($this->job->partnerIDsForDelivery, 'id', true) as $partnerId => $row) {
            $partner->row = $row;

            if (($partner->getCreatedAt() > $this->data->incomingTimestamp)  || $this->retriedPartnerTooManyTimesForThisSubmission($partnerId)) {
                continue;
            }

            $checkCap = $this->isPartnerWithinCaps($partner);
            
            if ($checkCap === true) {
                $partnersList[$partnerId] = $row;
            } else {
                $this->deliveryEngine->logCapExceededDelivery($row, $checkCap);
            }
        }
        return $partnersList;
    }
    //--------------------------------------------------------------------------


    public function catchupEntryProcessedButNotPostponed() {
        return $this->catchupJobId && !$this->postponeCatchupEntry && !$this->job->lastPartnerWasSkipped;
    }
    //--------------------------------------------------------------------------


    public function getLastPartnerWasSkipped() {
        return $this->job->lastPartnerWasSkipped;
    }
    //--------------------------------------------------------------------------


    protected function removeSubmissionFromQueueIfPartnerListExhausted() {
        $partnersWhoAcceptedOrSkipped = $this->data->logDelivery->listPartnersWhoAcceptedOrSkipped($this->data->incomingId, $this->job->campaign->getId());

        $deliveryProcessedInFull = !array_diff($this->job->partnerIDsForDelivery, $partnersWhoAcceptedOrSkipped);

        if (!$this->catchingUpPartnerId && $deliveryProcessedInFull) {
            $this->data->logIncoming->deleteSubmissionFromRepostQueue($this->data->incomingId, $this->job->campaign->getId());
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function removePartnerAcceptedLeadOrTriedMoreThanCampaignAllow() {
        // remove partner which accepted the lead or tried more than campaign allow
        foreach ($this->job->deliverySettings as $index => $partner) {
            $partnerId = $partner['partner_id'];

            if (in_array($partnerId, $this->job->partnersAlreadyDeliveredTo)) {
                unset($this->job->deliverySettings[$index]);
            }

            if (
                    isset($this->data->attemptsPerPartnerAssoc[$partnerId]) && 
                    $this->data->attemptsPerPartnerAssoc[$partnerId] >= $this->job->campaign->getPartnerRetries()
                ) {
                unset($this->job->deliverySettings[$index]);
            }
        }
    }
    //--------------------------------------------------------------------------

}
