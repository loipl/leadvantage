<?php

/**
 * @desc Engine class for processing incoming submissions
 */
class Engine_Submission extends Engine {
    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var Engine_IncomingData
     */
    protected $incomingData;


    public function processIncomingFormSubmission(array $campaign) {
        $this->initializeVarsForSubmissionProcessing($campaign);
        
        $this->failSubmissionIfUserHasNoRole();
        $this->failSubmissionIfCampaignNotActive();
        
        $this->failSubmissionIfOverApiCapLimit();
        $this->checkIfCampaignShouldBeSkimmed();

        $this->readIncomingDataAndDeliverToPartners();
    }
    //--------------------------------------------------------------------------


    private function initializeVarsForSubmissionProcessing(array $campaign) {
        $this->startingNewJob(Engine_Job::KIND_SUBMISSION);
        $this->report(Engine::ACTION_INCOMING, $campaign);
        $this->assignCampaign($campaign);
        $this->job->markAsRepost = false;
    }
    //--------------------------------------------------------------------------


    private function failSubmissionIfUserHasNoRole() {
        require_once("wp-load.php");
        
        $userId = $this->job->campaign->getUserId();
        $user = get_userdata($userId);

        if (empty($user->roles)) {
            if ($this->job->campaign->isAPIResponse()) {
                throw new EDoneException('FAIL|Inactive User');
            } else {
                App::getFrontController()->redirectToUrl($this->job->campaign->getFailureUrl());
            }
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function failSubmissionIfCampaignNotActive() {
        if (!$this->job->campaign->isActive()) {
             if ($this->job->campaign->isAPIResponse()) {
                throw new EDoneException('FAIL|Inactive Campaign');
            } else {
                App::getFrontController()->redirectToUrl($this->job->campaign->getFailureUrl());
            } 
        }
    }
    //--------------------------------------------------------------------------


    protected function failSubmissionIfOverApiCapLimit() {
        $campaign = $this->job->campaign;
        if ($campaign->hasValidOOBCap()) {
            $submissionCount = $this->data->logIncoming->campaignIncomingCount($campaign->getId(), $campaign->getOOBCapType());
            if ($submissionCount >= $campaign->getOOBCapValue()) {
                if ($campaign->isAPIResponse()) {
                    throw new EDoneException('FAIL|Cap Unavailable');
                } else {
                    throw new ERedirectException($campaign->getFailureUrl());
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    protected function checkIfCampaignShouldBeSkimmed() {
        $this->readUserRightsForCurrentCampaign();
        if ($this->flags->neverSkim) {
            return;
        }

        $shadowCampaign = $this->findShadowCampaignForSkimming();
        if ($shadowCampaign) {
            $this->switchToShadowSkimCampaign($shadowCampaign);
            $this->readUserRightsForCurrentCampaign();
        }
    }
    //--------------------------------------------------------------------------


    private function readUserRightsForCurrentCampaign() {
        $this->data->ownerUserCaps = $this->getOwnerUserCaps($this->job->campaign->getUserId());
        if (empty($this->data->ownerUserCaps)) {
            // If there are no capabilities for this user we assume he does not exist in wordpress
            throw new EError404();
        }
        $this->data->cvFTypes = $this->data->modelUser->canValidateFieldTypes($this->data->ownerUserCaps);
    }
    //--------------------------------------------------------------------------


    public function findShadowCampaignForSkimming() {
        $shadowSettings = $this->data->model->listShadowsOfCampaign($this->job->campaign->getId(), false, true);
        if ($shadowSettings) {
            // If there are multiple shadow campaigns, pick a random one
            $randomRow = $shadowSettings[rand(0, sizeof($shadowSettings) - 1)];

            $shadowCampaign = $this->data->model->get((int)$randomRow['id']);
            if (!$shadowCampaign) {
                // Sanity check, this should never happen
                return array();
            }

            if ($this->flags->alwaysSkim) {
                return $shadowCampaign;
            }

            $secondMod100 = time() % 100;
            if ($secondMod100 < $shadowCampaign['skim_percentage']) {
                return $shadowCampaign;
            }
        }
        return array();
    }
    //--------------------------------------------------------------------------


    public function switchToShadowSkimCampaign(array $shadowCampaign) {
        // Unset OOB flag if we're skimming
        $shadowCampaign['delivery_type'] = strval(Model_Campaign::DELIVERY_TYPE_LIVE_KEY);
        $this->assignCampaign($shadowCampaign);

        $this->job->skimming = true;
    }
    //--------------------------------------------------------------------------


    private function readIncomingDataAndDeliverToPartners() {
        try  {
                     
            $this->readIncomingData();

            $this->deliverIncomingSubmissionToPartners();
            
        } catch (EFWException $e) {
            if ($e instanceof ERedirectException) {
                $this->removeSubmissionFromQueueIfPartnerListExhausted();
            }
            $this->setSystemResponseFromException($e);
            throw $e;
        }
    }
    //--------------------------------------------------------------------------


    public function readIncomingData() {
        if ($this->flags->testIncomingData === false) {
            $submissionData = (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'POST')) ? $_POST : $_GET;
        } else {
            $submissionData = $this->flags->testIncomingData;
        }

        $this->incomingData = new Engine_IncomingData($this, $this->data, $this->job);

        $this->incomingData->validateSubmissionAndWriteProfileData($submissionData);
        
        $this->checkCampaignFilters();
        
        $this->incomingData->insertRepostQueueAndResponseToUser();
    }
    //--------------------------------------------------------------------------

    
    private function checkCampaignFilters() {
        $this->job->campaignFiltersOK = true;
        
        $campaignFilterModel = SingletonRegistry::getModelCampaignFilter();
        $filters = $campaignFilterModel->listActiveFiltersForCampaign($this->job->campaign->getId());

        $filters = Engine_Delivery::processSameFieldTypeFilter($filters, true);

        if ($filters && is_array($filters)) {
            foreach ($filters as $rows) {
                // $rows can be 1 or 2 dimensional array
                if (!is_array($rows['0'])) {
                    $rows = array($rows);
                }
                
                $checkMatching = true;
                
                foreach ($rows as $row) {
                    $ftypeID    = (int)$row['field_type_id'];
                    $sysField   = $row['system_field'];
                    $matchType  = (int)$row['match_type'];
                    $matchValue = $row['match_value'];

                    $dummy = false;
                    $value = isset($this->data->inputData[$ftypeID]) ? $this->data->inputData[$ftypeID] : $this->deliveryEngine->valueOfSystemField($row['system_field'], $dummy);
                    if ( ! Engine_Utilities::checkMatching($matchType, $value, $matchValue)) {
                        $checkMatching = false;
                        break;
                    }
                }

                if ($checkMatching) {
                    $action = $rows['0']['filter_action'];
                    switch ($action) {
                        case Model_PartnerFilter::FILTER_ACTION_DONT_DELIVER:
                            $this->job->campaignFiltersOK = false;
                            $this->job->message = 'Campaign Rules';
                            
                            // check which rule is not satisfied
                            if (!empty($ftypeID)) {
                                $fieldTypeModel = new Model_FieldType();
                                $row = $fieldTypeModel->get($ftypeID);
                                isset($row['name']) ? $this->job->message .= ' - ' . $row['name'] : ""; 
                            } elseif (!empty($sysField)) {
                                $this->job->message .= ' - ' . $sysField;
                            }
                            break;
                        default:
                            // other actions, might be implemented later
                            break;
                    }
                }
                
                if ($this->job->campaignFiltersOK === false) {
                    // log to reporting
                    $this->logDeliveryIfMatchCampaignRule();
                    
                    // response to user
                    $this->failSubmissionIfMatchCampaignRule();
                }
            }
        }
    }
    //--------------------------------------------------------------------------

    
    private function logDeliveryIfMatchCampaignRule() {
        $isSuccess = '0';
        $this->data->logIncoming->setIsSuccessStatus($this->data->incomingId, $isSuccess, $this->job->message);

        $submissionRow = $this->data->logIncoming->get($this->data->incomingId);

        if ($submissionRow) {
            $this->data->logIncoming->insertFailedSubmissionIntoReportingTable($submissionRow, $this->job->campaign->getUserId(), false);
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function failSubmissionIfMatchCampaignRule() {
        $campaign = $this->job->campaign;
        
        if ($campaign->isAPIResponse()) {
            throw new EDoneException('FAIL|'.$this->job->message);
        } else {
            throw new ERedirectException($campaign->getFailureUrl());
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function deliverIncomingSubmissionToPartners() {
        $this->initializeDeliveryJobAndPartnerLists();
        
        if ($this->job->campaign->isPingPostCampaign()) {
            // Ping + Post campaign
            $this->deliveryEngine->pingToAllDeliveryPartnersBeforeDelivery();
        } elseif ($this->job->campaign->isPingAndPostCampaign()) {
            // Ping / Post campaign
            if (!array_key_exists(Model_CampaignField::FIELD_TYPE_LEAD_ID, $this->data->inputData) ||
                    empty($this->data->inputData[Model_CampaignField::FIELD_TYPE_LEAD_ID])) {
                // Ping process
                $this->deliveryEngine->pingToPartnersAndReturnResult();
            } else {
                // Post process
                $this->deliveryEngine->postToPartnersAfterGetDataFromPing();
            }
        } else {
            // normal campaigns
            $this->deliverToAllDeliveryPartners();
        }
        
        $this->logFailedSubmissionIfNoPartnersAccepted();
        
        $this->sendFinalResponseToUser();
        
    }
    //--------------------------------------------------------------------------


    private function setSystemResponseFromException(EFWException $e) {
        if ($e instanceof ERedirectException) {
            if ($this->data->incomingId) {
                $this->data->logIncoming->setSystemResponse($this->data->incomingId, 'User redirected to <a href="' . $e->getMessage() . '">' . $e->getMessage() . '</a>', $e->getMessage());
            }
        } elseif ($e instanceof EExplainableError) {
            if ($this->data->incomingId) {
                $this->data->logIncoming->setSystemResponse($this->data->incomingId, $e->getMessage());
            }
        } elseif ($e instanceof EDoneException) {
            if ($this->data->incomingId) {
                $this->data->logIncoming->setSystemResponse($this->data->incomingId, $e->getMessage());
            }
        }
    }
    //--------------------------------------------------------------------------


    private function initializeDeliveryJobAndPartnerLists() {
        $this->job->startingSubmissionDelivery();

        $this->job->deliverySettings    = $this->data->model->listDeliverySettings($this->job->campaign->getId());
        $this->job->partnersAssoc       = $this->listPartnersForInitialDelivery();
        $this->job->partnerFiltersAssoc = $this->data->modelPartnerFilter->listAssocForPartnerGroup(array_keys($this->job->partnersAssoc), true);
        $this->job->partnersFieldsAssoc = $this->data->modelPartnerField->listAssocForPartnerGroup(array_keys($this->job->partnersAssoc));

        $this->job->maxDeliveries = max($this->job->campaign->getLeadsPer(), 1);
        
        $maxExecutionTime = max($this->job->campaign->getCampaignTimeout(), Campaign::CAMPAIGN_TIMEOUT_DEFAULT) + 30;
        set_time_limit($maxExecutionTime);
    }
    //--------------------------------------------------------------------------


    public function listPartnersForInitialDelivery() {
        $this->job->partnerIDsForDelivery = array();

        foreach ($this->job->deliverySettings as $row) {
            $this->job->partnerIDsForDelivery[] = (int)$row['partner_id'];
        }
        sort($this->job->partnerIDsForDelivery);

        $partnersList = array();
        $partner = new Partner();
        foreach ($this->data->modelPartner->listGroup($this->job->partnerIDsForDelivery, 'id', true) as $id => $row) {
            $partner->row = $row;
            $checkCap = $this->isPartnerWithinCaps($partner);

            if ($checkCap === true) {
                $partnersList[$id] = $row;
            } else {
                $this->deliveryEngine->logCapExceededDelivery($row, $checkCap);
            }
        }
        
        if ($partnersList) {
            /* @var $modelPartnerSettings Model_PartnerSettings */
            $modelPartnerSettings = SingletonRegistry::getSingleInstance('Model_PartnerSettings');
            $partnersList = $modelPartnerSettings->attachSettingsForPartnerList($partnersList);
        }

        return $partnersList;
    }
    //--------------------------------------------------------------------------


    private function deliverToAllDeliveryPartners() {
        $deliverySettings = $this->job->deliverySettings;
        
        // check if round-robin mode is enable
        if ($this->job->campaign->isRoundRobin()) {
            $roundRobinLastIndex = $this->job->campaign->getRoundRobinLastIndex();
            $deliverySettings = $this->getRoundRobinDeliverySettingAndUpdateIndex($deliverySettings, $roundRobinLastIndex);
        }
        
        foreach ($deliverySettings as $delivery) {
            
            $campaignTimeout = intval($this->job->campaign->getCampaignTimeout());
            if ($campaignTimeout > 0 && $campaignTimeout <= time() - $this->job->startTime) {
                break;
            }
            
            if ($this->job->startNewDelivery($delivery)) {
                $this->deliverToCurrentPartner();
            }
        }
    }
    //--------------------------------------------------------------------------

    
    public function deliverToCurrentPartner() {
        $partner = $this->job->currentPartner;
        $partner->setDeliveryAddr($this->processUrlWithCampaignFields($partner->getDeliveryAddr()));
        
        if ($this->deliveryEngine->deliverToCurrentPartner()) {
            $partnerSuccessUrl = $this->getPartnerSuccessUrl($partner->getSuccessUrl());
            $this->job->deliveryAccepted($partnerSuccessUrl);
            
            if (!$this->job->currentPartner->getWrappedCampaignId()) {
                $this->job->atleastOneRealPartnerAccepted = true;
                
                $this->data->successUrl = $partnerSuccessUrl;
                $this->data->success = true;

                if ($this->data->successUrlAppend != '') {
                    $this->data->successUrlAppend .= "&";
                }

                $this->data->successUrlAppend .= urlencode('success_url[]=' . $partnerSuccessUrl);
            }
        }
        
        // check if exist API mode campaign wrapper partner
        if ($this->job->currentPartner->getWrappedCampaignId()) {
            $wrappedCampaignId = $this->job->currentPartner->getWrappedCampaignId();

            if (SingletonRegistry::getModelCampaign()->checkCampaignIsQueuedDelivery($wrappedCampaignId)) {
                $this->job->existOOBCampaignInDeliveryList = true;
            }
        }
    }
    //--------------------------------------------------------------------------


    private function logFailedSubmissionIfNoPartnersAccepted() {
        if (!$this->job->deliveryTried) {
            $submissionRow = $this->data->logIncoming->get($this->data->incomingId);
            if ($submissionRow) {
                $this->data->logIncoming->insertFailedSubmissionIntoReportingTable($submissionRow, $this->job->campaign->getUserId(), false);
            }
        }
        if (!$this->job->deliverySettings) {
            $this->data->logIncoming->insertFailedSubmissionNoPartners($this->data->incomingId);
        }
    }
    //--------------------------------------------------------------------------


    private function sendFinalResponseToUser() {
        if ($this->job->campaign->isAPIResponse()) {
            throw new EDoneException(($this->job->atleastOneRealPartnerAccepted) ? $this->getFinalSuccessResponse() : 'FAIL|No partner accepted');
        } else {
            $finalUrl = $this->getFinalUrl();
            header('x-lw-url: ' . $finalUrl);
            throw new ERedirectException($finalUrl);
        }
    }
    //--------------------------------------------------------------------------


    private function getFinalSuccessResponse() {
        $response = 'SUCCESS';
        if (!empty($this->data->successUrl)) {
            $response .= '|' . $this->data->successUrl;
        }
        
        return $response;
    }
    //--------------------------------------------------------------------------
    
    
    private function getFinalUrl() {
        if ($this->job->campaign->isMaster()) {
            if ($this->job->existOOBCampaignInDeliveryList && ! $this->job->atleastOneRealPartnerAccepted) {
                $this->data->success = true;
            }
            
            if ($this->data->success) {
                if ($this->job->campaign->isAppendMultipleSuccessUrls()) {
                    $finalUrl  = $this->job->campaign->getSuccessUrl();
                    $finalUrl  = $this->processUrlWithCampaignFields($finalUrl);
                    if (strpos($finalUrl,'?')) {
                        $finalUrl .= "&";
                    } else {
                        $finalUrl .= "?";
                    }
                    $finalUrl .= $this->data->successUrlAppend;

                    return trim($finalUrl);
                } else {
                    if (!empty($this->data->successUrl)) {
                        $finalUrl = $this->processUrlWithCampaignFieldsAndPartnerTokens($this->data->successUrl);

                        return trim($finalUrl);
                    } else {
                        $finalUrl  = $this->job->campaign->getSuccessUrl();
                        $finalUrl  = $this->processUrlWithCampaignFields($finalUrl);

                        return trim($finalUrl);
                    }
                }
            } else {
                $finalUrl = $this->processUrlWithCampaignFields($this->job->campaign->getFailureUrl());

                return trim($finalUrl);
            }
        }

        if ($this->job->successURLs && $this->job->isSuccess) {
            $finalUrl = $this->appendSuccessUrls();
        } else {
            $finalUrl = $this->job->finalSuccessUrl ? $this->job->finalSuccessUrl : '';
            if (!$finalUrl) {
                $finalUrl = $this->processUrlWithCampaignFields($this->job->isSuccess ? $this->job->campaign->getSuccessUrl() : $this->job->campaign->getFailureUrl());
                if (!$finalUrl) {
                    $finalUrl = $this->job->isSuccess ? $this->job->campaign->getSuccessUrl() : $this->job->campaign->getFailureUrl();
                }
            }
        }
        header('x-lw-success: ' . $this->job->isSuccess);
        return trim($finalUrl);
    }
    //--------------------------------------------------------------------------


    private function appendSuccessUrls() {
        $urls = array();
        foreach (array_unique($this->job->successURLs) as $oneSuccessUrl) {
            if ($oneSuccessUrl) {
                // All success urls have already been pushed through $this->processUrlWithCampaignFields
                // at the time when their partner was "current", so there's no need to do it again here.
                $urls[] = $oneSuccessUrl;
            }
        }

        $successUrl = $this->processUrlWithCampaignFields($this->job->campaign->getSuccessUrl());
        $successUrl .= (strpos($successUrl, '?') === false) ? '?' : '&';
        $add = false;
        foreach ($urls as $url) {
            if ($add) {
                $successUrl .= '&';
            } else {
                $add = true;
            }
            $successUrl .= 'success_url[]=' . urlencode($url);
        }
        return $successUrl;
    }
    //--------------------------------------------------------------------------


    protected function removeSubmissionFromQueueIfPartnerListExhausted() {
        $partnersWhoAcceptedOrSkipped = $this->data->logDelivery->listPartnersWhoAcceptedOrSkipped($this->data->incomingId, $this->job->campaign->getId());

        $deliveryProcessedInFull = !array_diff($this->job->partnerIDsForDelivery, $partnersWhoAcceptedOrSkipped);

        if ($deliveryProcessedInFull) {
            $this->data->logIncoming->deleteSubmissionFromRepostQueue($this->data->incomingId, $this->job->campaign->getId());
        }
    }
    //--------------------------------------------------------------------------
    
    
    public function setInputDataAndCheckCampaignFiltersForCSV($campaign, $inputData) {
        $this->assignCampaign($campaign);
        $this->data->inputData = $inputData;
        
        try {
            $this->checkCampaignFilters();
        } catch (Exception $ex) {
            return array(
                'status' => false,
                'message' => $this->job->message
            );
        }
        return array (
            'status' => $this->job->campaignFiltersOK,
            'message' => $this->job->message
        );
    }
}
