<?php

class Engine_IncomingData {

    /**
     * @var Engine_Submission
     */
    private $engine;

    /**
     * @var Engine_Data
     */
    private $data;

    /**
     * @var Engine_Job
     */
    private $job;

    /**
     * @var Engine_Validator
     */
    private $validator;

    /**
     * @var Engine_IncomingLogger
     */
    private $logger;

    /**
     * @var Engine_Flags
     */
    public $flags;

    private $campaignFields = array();

    private $errors = array();


    public function __construct(Engine_Submission $engine, Engine_Data $data, Engine_Job $job) {
        $this->engine    = $engine;
        $this->flags     = $engine->flags;
        $this->data      = $data;
        $this->job       = $job;

        $this->validator = new Engine_Validator($this->data, $this->job, $this->flags);
        $this->logger    = new Engine_IncomingLogger($this->engine, $this->data, $this->job);
    }
    //--------------------------------------------------------------------------


    /**
     * @param array $levelLimitsAssoc array like('subscriber' => 20, 'power_user' => 100)
     * @param array $userLevels array with user capabilities, like ('subscriber', 'administrator');
     *
     * @return int Result is -1 if user has no rights at all, 0 for unlimited and
     * actual number of submissions user can process if everything works out
     */
    public static function getMaxDeliveriesForUserLevel(array $levelLimitsAssoc, array $userLevels) {
        return Engine_Utilities::getMaxDeliveriesForUserLevel($levelLimitsAssoc, $userLevels);
    }
    //--------------------------------------------------------------------------


    public function validateSubmissionAndWriteProfileData(array $dataSource) {
        $this->readAndValidateSubmissionData($dataSource);
        $this->checkTooManySubmissionsForUserLevel();
        
        $this->logger->logRequestAndWriteProfileData($this->errors);
    }
    //--------------------------------------------------------------------------
    
    
    public function insertRepostQueueAndResponseToUser() {
        // detect Ping process of Ping / Post campaign
        $pingProcessFlag = $this->job->campaign->isPingAndPostCampaign() && (!array_key_exists(Model_CampaignField::FIELD_TYPE_LEAD_ID, $this->data->inputData) ||
                    empty($this->data->inputData[Model_CampaignField::FIELD_TYPE_LEAD_ID]));
        
        if ($this->data->incomingId) {
            $this->addShadowCampaignsToRepostQueueIfNoErrors($pingProcessFlag);
        }

        $this->sendResponseOrRedirectIfQueuedDelivery($pingProcessFlag);
        $this->showErrorMessageIfSubmissionIsInvalid();
    }
    //--------------------------------------------------------------------------
    

    private function readAndValidateSubmissionData(array $dataSource) {
        $this->initializeFieldList();

        $this->data->inputData = array();  
        $this->processMandatorySetting($dataSource);
        $this->addDummyEmailIfEmailEmpty($dataSource);
        
        // validation cache for Ping / Post
        $this->campaignFields = $this->fieldValidationCache($dataSource, $this->campaignFields);

        $this->errors = $this->validator->readAndValidateFields($dataSource, $this->campaignFields);
        if (!$this->errors && $this->isServerIpAddress()) {
            $this->tryToRestoreIpFromProfiles();
        }

        $this->removeDummyEmail();
        
        $this->checkDuplicate();           
        $this->engine->report(Engine::ACTION_GATHERED, $this->errors);
    }
    //--------------------------------------------------------------------------
    
    
    private function removeDummyEmail() {
        $emailFieldType = Model_CampaignField::FIELD_TYPE_EMAIL;
        if ($this->data->inputData[$emailFieldType] === Model_CampaignField::DUMMY_EMAIL) {
            $this->data->inputData[$emailFieldType] = '';
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function addDummyEmailIfEmailEmpty(&$dataSource) {
        $emailFieldName      = $this->job->campaign->getEmailFieldName();
        $emailFieldMandatory = $this->job->campaign->getEmailFieldMandatory();
        if (empty($dataSource[$emailFieldName]) && empty($emailFieldMandatory)) {
            $dataSource[$emailFieldName] = Model_CampaignField::DUMMY_EMAIL;
            
            // disable validate for dummy email
            foreach ($this->campaignFields as &$field) {
                if ($field['name'] === $emailFieldName) {
                    $field['validate'] = '0';
                    break;
                }
            }
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function fieldValidationCache($dataSource, $campaignFields) {
        $emailFieldName  = $this->job->campaign->getEmailFieldName();
        $leadIdFieldName = $this->getLeadIdFieldName();

        if (!empty($dataSource[$leadIdFieldName])) {
            $oldIncRow = $this->data->logIncoming->get($dataSource[$leadIdFieldName]);
            $oldSubmissionsAssoc = $this->data->logIncoming->listSubmissionValuesGroup(array($dataSource[$leadIdFieldName]));

            if (!empty($oldIncRow)) {
                // throw error if Ping process failed
                if ((isset($oldIncRow['is_success']) && !$oldIncRow['is_success']) || strpos('FAIL', $oldIncRow['system_response']) !== false) {
                    throw new EExplainableError('' . $oldIncRow['system_response']);
                }
                
                // validation cache for email
                if (isset($dataSource[$emailFieldName]) && $dataSource[$emailFieldName] === $oldIncRow['email']) {
                    foreach ($campaignFields as &$field) {
                        if ($field['name'] === $emailFieldName) {
                            $field['validate'] = '0';
                            break;
                        }
                    }
                }
                
                // validation cache for other fields
                if (!empty($oldSubmissionsAssoc[$dataSource[$leadIdFieldName]])) {
                    foreach ($oldSubmissionsAssoc[$dataSource[$leadIdFieldName]] as $ftId => $oldValue) {
                        foreach ($campaignFields as &$field) {
                            if ($field['field_type'] == $ftId && isset($dataSource[$field['name']]) && $dataSource[$field['name']] == $oldValue) {
                                $field['validate'] = '0';
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        return $campaignFields;
    }
    //--------------------------------------------------------------------------
    
    
    private function processMandatorySetting($dataSource) {
        if ($this->job->campaign->isPingAndPostCampaign()) {
            
            $leadIdFieldName = $this->getLeadIdFieldName();
            if (!empty($leadIdFieldName) && !empty($dataSource[$leadIdFieldName])) {
                // post process
                foreach ($this->campaignFields as $index => $field) {
                    $postMandatory =  ($field['is_mandatory'] == Model_CampaignField::MANDATORY_BOTH || 
                                       $field['is_mandatory'] == Model_CampaignField::MANDATORY_POST);
                    $this->campaignFields[$index]['is_mandatory'] = $postMandatory ? '1' : '0';
                }
            } else {
                //ping process
                foreach ($this->campaignFields as $index => $field) {
                    $pingMandatory =  ($field['is_mandatory'] == Model_CampaignField::MANDATORY_BOTH || 
                                       $field['is_mandatory'] == Model_CampaignField::MANDATORY_PING);
                    $this->campaignFields[$index]['is_mandatory'] = $pingMandatory ? '1' : '0';
                }
            }
            
        } else {
            foreach ($this->campaignFields as $index => $field) {
                $this->campaignFields[$index]['is_mandatory'] = empty($field['is_mandatory']) ? '0' : '1';
            }
        }
    }
    //--------------------------------------------------------------------------
    
    
    private function getLeadIdFieldName() {
        foreach ($this->campaignFields as $field) {
            if ($field['field_type'] == Model_CampaignField::FIELD_TYPE_LEAD_ID) {
                return $field['name'];
            }
        }
        return '';
    }
    //--------------------------------------------------------------------------
    

    private function isServerIpAddress() {
        return in_array($_SERVER['REMOTE_ADDR'], Config::$serverIPAddresses);
    }
    //--------------------------------------------------------------------------


    private function tryToRestoreIpFromProfiles() {
        $email = $this->data->inputData[Model_CampaignField::FIELD_TYPE_EMAIL];
        $inferred = $this->data->modelProfile->getProfileInferredDataForEmail($email);
        if (!empty($inferred['ip_address'])) {
            $_SERVER['REMOTE_ADDR'] = $inferred['ip_address'];
        }
    }
    //--------------------------------------------------------------------------


    private function initializeFieldList() {
        $this->readCampaignFields();

        foreach ($this->campaignFields as $campaignField) {
            $fieldTypeId = $campaignField['field_type'];
            $this->data->usedFieldTypes[$fieldTypeId] = $this->data->fieldTypes[$fieldTypeId];
        }
    }
    //--------------------------------------------------------------------------


    private function readCampaignFields() {
        $sourceCampaignId     = $this->job->campaign->getSourceCampaignId() ? $this->job->campaign->getSourceCampaignId() : $this->job->campaign->getId();
        $this->campaignFields = $this->data->modelCampaignFields->listAllForCampaign($sourceCampaignId);

        $this->overrideValidationsIfShadow();

        // Prepend settings for email field
        array_unshift($this->campaignFields, array(
            'name'         => $this->job->campaign->getEmailFieldName(),
            'description'  => $this->job->campaign->getEmailFieldDescription(),
            'field_type'   => Model_CampaignField::FIELD_TYPE_EMAIL,
            'validate'     =>  $this->job->campaign->getEmailFieldVerify(),
            'is_mandatory' => $this->job->campaign->getEmailFieldMandatory())
        );
    }
    //--------------------------------------------------------------------------


    private function overrideValidationsIfShadow() {
        if ($this->job->campaign->getSourceCampaignId()) {
            $validationOverrides = $this->data->modelCampaignFields->listValidationOverrides($this->job->campaign->getId());
            foreach ($validationOverrides as $sourceCampaignFieldId => $validate) {
                if (isset($this->campaignFields[$sourceCampaignFieldId])) {
                    $this->campaignFields[$sourceCampaignFieldId]['validate'] = $validate ? 2 : $this->campaignFields[$sourceCampaignFieldId]['validate'];
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    protected function checkDuplicate($timeoutSeconds = 3600) {
        if (!$this->flags->checkForDuplicates) {
            return;
        }

        $submissionIDsWithSameEmail = $this->findSubmissionsWithSameEmail($timeoutSeconds);

        if ($submissionIDsWithSameEmail) {
            if (count($this->data->inputData) === 1 && isset($this->data->inputData[Model_CampaignField::FIELD_TYPE_EMAIL])) {
                $submissionId = SingletonRegistry::getModelLogIncomingDuplication()->getRealIncomingId($submissionIDsWithSameEmail[0]);
                $this->sendReplyFromPreviousSubmission($submissionId);
            } else {
                $oldSubmissionsAssoc = SingletonRegistry::getModelLogIncomingDuplication()->listSubmissionValuesGroup($submissionIDsWithSameEmail);

                foreach ($oldSubmissionsAssoc as $id => $oldData) {
                    if ($this->isSubmissionDataSame($oldData)) {
                        if ($this->job->campaign->isAPIResponse()) {
                            throw new EDoneException('FAIL|Duplicate Submission');
                        } else {
                            $this->engine->report(Engine::ACTION_INCOMING_DUPLICATE, $oldSubmissionsAssoc);
                            $submissionId = SingletonRegistry::getModelLogIncomingDuplication()->getRealIncomingId($id);
                            $this->sendReplyFromPreviousSubmission($submissionId);
                        }
                    }
                }
            }
        }
    }
    //--------------------------------------------------------------------------


    private function findSubmissionsWithSameEmail($timeoutSeconds) {
        $email = $this->data->inputData[Model_CampaignField::FIELD_TYPE_EMAIL];

        $submissionIDsWithSameEmail = SingletonRegistry::getModelLogIncomingDuplication()->listRecent($this->job->campaign->getId(), $email, empty($this->errors), $timeoutSeconds);
        return $submissionIDsWithSameEmail;
    }
    //--------------------------------------------------------------------------


    private function isSubmissionDataSame(array $oldData) {
        foreach ($this->data->inputData as $ftId => $value) {
            if ($ftId == Model_CampaignField::FIELD_TYPE_EMAIL) {
                continue;
            }
            if (!isset($oldData[$ftId])) {
                return false;
            }
            if ($oldData[$ftId] != $this->data->inputData[$ftId]) {
                return false;
            }
        }

        return true;
    }
    //--------------------------------------------------------------------------


    public function sendReplyFromPreviousSubmission($submissionId) {
        $oldIncRow = $this->data->logIncoming->get($submissionId);
        if ($this->job->campaign->isAPIResponse()) {
            throw new EDoneException('' . $oldIncRow['system_response']);
        } elseif (isset($oldIncRow['is_success']) && $oldIncRow['is_success']) {
            throw new ERedirectException('' . $oldIncRow['redirected_to']);
        } elseif (isset($oldIncRow['system_response']) && $oldIncRow['system_response']) {
            throw new EExplainableError('' . $oldIncRow['system_response']);
        } else {
            // if the previous lead's response is not ready for reply, send a default response
            throw new ERedirectException('' . $this->job->campaign->getFailureUrl());
        }
    }
    //--------------------------------------------------------------------------


    protected function checkTooManySubmissionsForUserLevel() {

        if (isset($this->flags->test_MaxSubmissions) && is_int($this->flags->test_MaxSubmissions)) {
            $maxSubmissionsForUser = $this->flags->test_MaxSubmissions;
        } else {
            /* @var $mppl Model_ConfigPostsPerLevel */
            $mppl = SingletonRegistry::getSingleInstance('Model_ConfigPostsPerLevel');
            $maxSubmissionsForUser = Engine_Utilities::getMaxDeliveriesForUserLevel($mppl->listLevelsAssoc(), $this->data->ownerUserCaps);
        }

        $this->sendToFailureUrlIfUserHasNoRightsAtAll($maxSubmissionsForUser);
        $this->sendToFailureUrlIfUserHasTooManySubmissions($maxSubmissionsForUser);
    }
    //--------------------------------------------------------------------------


    private function sendToFailureUrlIfUserHasNoRightsAtAll($maxSubmissionsForUser) {
        if ($maxSubmissionsForUser == -1) {
            $dummy = array();
            $this->engine->report(ENGINE::ACTION_TOO_MANY, $dummy);
            $this->engine->redirectToCampaignFailureUrlProcessed();
        }
    }
    //--------------------------------------------------------------------------


    private function sendToFailureUrlIfUserHasTooManySubmissions($maxSubmissionsForUser) {
        $subaccountModel = SingletonRegistry::getSingleInstance('Model_SubAccount');
        
        if ($maxSubmissionsForUser) {
            $userId = (int)$this->job->campaign->getUserId();
            $subAccountInfo = $subaccountModel->checkIfUserIsSubAccount($userId);
            if (!empty($subAccountInfo)) {
                $userId = (int) $subAccountInfo['user_id'];
            }

            $userSubmissionCount = $this->data->logIncoming->getTotalSuccessfulCount($userId);
            if ($userSubmissionCount > $maxSubmissionsForUser) {

                $reportData = array($userSubmissionCount, $maxSubmissionsForUser);
                $this->engine->report(Engine::ACTION_TOO_MANY, $reportData);

                $this->engine->redirectToCampaignFailureUrlProcessed();
            }
        }
    }
    //--------------------------------------------------------------------------


    private function addShadowCampaignsToRepostQueueIfNoErrors($pingProcessFlag) {
        if (!$this->errors && !$pingProcessFlag) {
            $this->addSubmissionToRepostQueueIfShouldRepostOrQueuedDelivery();
            $this->addParasiteShadowsToRepostQueue();
        }
    }
    //--------------------------------------------------------------------------


    private function addSubmissionToRepostQueueIfShouldRepostOrQueuedDelivery() {
        $campaign = $this->job->campaign;
        
        if ($campaign->isQueuedDelivery() || $campaign->isShouldRepost()) {
            $delayHours  = $campaign->getRepostHours();
            $repostCount = $campaign->isShouldRepost() ? $campaign->getRepostMax() : 0;

            $this->data->logIncoming->insertIntoRepostQueue($this->data->incomingId, $delayHours, $repostCount, $campaign->getId(), $campaign->getUserId(), $campaign->isQueuedDelivery());
        }
    }
    //--------------------------------------------------------------------------


    private function addParasiteShadowsToRepostQueue() {
        $parasiteShadowCampaigns = SingletonRegistry::getModelCampaign()->listShadowsOfCampaign($this->job->campaign->getId(), true);

        foreach ($parasiteShadowCampaigns as $campaignRow) {
            $campaign  = new Campaign($campaignRow);

            if ($this->validator->validationOverridesByShadowCampaignPassed($campaign, $this->campaignFields)) {
                $this->addOneParasiteShadowsToRepostQueue($campaign);
            }
        }
    }
    //--------------------------------------------------------------------------


    private function addOneParasiteShadowsToRepostQueue(Campaign $campaign) {
        if ($campaign->isShouldRepost()) {
            $delay     = $campaign->getRepostHours();
            $repostMax = $campaign->getRepostMax();
        } else {
            $delay     = 0;
            $repostMax = 0;
        }

        $this->data->logIncoming->insertIntoRepostQueue($this->data->incomingId, $delay, $repostMax, $campaign->getId(), $campaign->getUserId(), true, $campaign->getShadowDelayHours());
    }
    //--------------------------------------------------------------------------


    private function sendResponseOrRedirectIfQueuedDelivery($pingProcessFlag) {
        $campaign = $this->job->campaign;
        
        if ($campaign->isQueuedDelivery() && !$this->job->skimming && !$pingProcessFlag) {
            if ($campaign->isAPIResponse()) {
                // This will be caught by Controller_Api and sent as page response.
                throw new EDoneException(empty($this->errors) ? 'SUCCESS' : 'FAIL|' . implode(", ", $this->errors));
            } else {
                // get campaign's success/fail redirect URL and redirect
                $successUrl = $campaign->getSuccessUrl();
                $failureUrl = $campaign->getFailureUrl();
                $redirectUrl = (empty($this->errors)) ? $successUrl : $failureUrl;
                
                header('x-lw-url: ' . $redirectUrl); 
                header("Location: $redirectUrl");
                exit();
            }
        }
    }
    //--------------------------------------------------------------------------


    private function showErrorMessageIfSubmissionIsInvalid() {
        if ($this->errors) {
            if ($this->job->campaign->isAPIResponse()) {
                throw new EDoneException('FAIL|' . implode(", ", $this->errors));
            } else {
                throw new EExplainableError("\n" . implode("<br>", $this->errors) .
                "<br><br>Please click the 'Back' button on your browser to correct these errors and try again.");
            }
        }
    }
    //--------------------------------------------------------------------------
}
