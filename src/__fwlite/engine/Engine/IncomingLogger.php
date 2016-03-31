<?php

class Engine_IncomingLogger {

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


    public function __construct(Engine_Submission $engine, Engine_Data $data, Engine_Job $job) {
        $this->engine    = $engine;
        $this->data      = $data;
        $this->job       = $job;
    }
    //--------------------------------------------------------------------------


    public function logRequestAndWriteProfileData($errors) {
        $this->errors = $errors;
        $this->setIncomingStatus();
        
        // insert data into log_incoming_duplication & log_incoming_values_duplication tables
        $submissionData = $this->getSubmissionData();
        if (!empty($submissionData)) {
            $duplicatedSubmissionId = $this->insertSubmissionToLogDuplication($submissionData);
            $this->logSubmissionDuplicationFieldTypeValues($duplicatedSubmissionId);
        }
        
        // Has to be done in this order since we write lead_id to profiles_inferred,
        // and lead_id is id from log_incoming which is only avaliable after logging
        // the request
        $submissionData = $this->getSubmissionData(true);
        if (!empty($submissionData)) {
            DB::$db->query("START TRANSACTION");
            $this->logIncomingRequest($submissionData, $duplicatedSubmissionId);
            DB::$db->query("COMMIT");
        }
        
        $this->writeProfileDataIfNoErrors();

        return $this->data->incomingId;
    }
    //--------------------------------------------------------------------------


    private function setIncomingStatus() {
        $this->data->incomingStatus = empty($this->errors) ? 1 : 0;
    }
    //--------------------------------------------------------------------------


    private function logIncomingRequest($submissionData, $duplicatedSubmissionId) {
        $this->insertSubmissionToLog($submissionData);

        if ($this->data->incomingId) {
            $this->updateDuplicationIncomingId($duplicatedSubmissionId, $this->data->incomingId);
            $this->logSubmissionFieldTypeValues();
            $this->updateSummaryAndReportingTables();
        }
    }
    //--------------------------------------------------------------------------


    private function getSubmissionData($flag = null) {
        $email = isset($this->data->inputData[Model_CampaignField::FIELD_TYPE_EMAIL]) ? $this->data->inputData[Model_CampaignField::FIELD_TYPE_EMAIL] : '';
        
        
        $submissionData = array(
            'campaign_id'  => $this->job->campaign->getId(),
            'email'        => $email,
            'user_agent'   => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'http_referer' => isset($_SERVER['HTTP_REFERER'])    ? $_SERVER['HTTP_REFERER']    : '',
            'remote_ip'    => isset($_SERVER['REMOTE_ADDR'])     ? $_SERVER['REMOTE_ADDR']     : '',
            'is_success'   => $this->errors ? '0' : '1',
            'user_id'      => $this->job->campaign->getUserId()
        );
        
        if (isset($flag)) {
            $fullName = Engine_Utilities::getFullNameValue($this->data->inputData);
            $phone = isset($this->data->inputData[Model_CampaignField::FIELD_TYPE_PHONE]) ? $this->data->inputData[Model_CampaignField::FIELD_TYPE_PHONE] : '';
            $campaign = isset($this->data->inputData[Model_CampaignField::FIELD_TYPE_CAMPAIGN]) ? $this->data->inputData[Model_CampaignField::FIELD_TYPE_CAMPAIGN] : '';
            $fullAddress = isset($this->data->inputData[Model_CampaignField::FIELD_TYPE_ADDRESS_1]) ? $this->data->inputData[Model_CampaignField::FIELD_TYPE_ADDRESS_1] : '';
            
            if (isset($this->data->inputData[Model_CampaignField::FIELD_TYPE_CITY])) {
                $fullAddress .= ', ' . $this->data->inputData[Model_CampaignField::FIELD_TYPE_CITY];
            }
            
            if (isset($this->data->inputData[Model_CampaignField::FIELD_TYPE_STATE_CODE])) {
                $fullAddress .= ', ' . $this->data->inputData[Model_CampaignField::FIELD_TYPE_STATE_CODE];
            }
            
            $fullAddress = ltrim($fullAddress, ', ');
            
            $submissionData['full_name']    = $fullName;
            $submissionData['full_address'] = $fullAddress;
            $submissionData['phone']        = $phone;
            $submissionData['campaign']     = $campaign;
        }
        
        return $submissionData;
    }
    //--------------------------------------------------------------------------
    
    
    private function insertSubmissionToLog($submissionData) {
        $this->data->incomingId = $this->data->logIncoming->insert($submissionData);
    }
    //--------------------------------------------------------------------------
    
    
    private function insertSubmissionToLogDuplication($submissionData) {
        return SingletonRegistry::getModelLogIncomingDuplication()->insert($submissionData);
    }
    //--------------------------------------------------------------------------
    

    private function updateDuplicationIncomingId($id, $incomingId) {
        return SingletonRegistry::getModelLogIncomingDuplication()->updateDuplicationIncomingId($id, $incomingId);
    }
    //--------------------------------------------------------------------------
    
    
    private function writeProfileDataIfNoErrors() {
        if (!$this->errors) {
            $this->data->modelProfile->writeProfileData((int)$this->job->campaign->getUserId(), $this->data->inputData, $this->data->profileId);

            $inferrableTokens = $this->data->modelCampaignFields->listSystemFields();
            $inferredValues   = array();

            foreach ($inferrableTokens as $token) {
                $dummy = false;
                $value = $this->engine->valueOfSystemField($token, $dummy);
                if ($value != '') {
                    $inferredValues[$token] = $value;
                }
            }

            if ($inferredValues) {
                $this->data->modelProfile->writeProfileInferredData((int)$this->job->campaign->getUserId(), $this->data->inputData, $inferredValues, $this->data->profileId);
            }
        }
    }
    //--------------------------------------------------------------------------


    private function logSubmissionFieldTypeValues() {
        $copyOfInputData = $this->data->inputData;
        unset($copyOfInputData[Model_CampaignField::FIELD_TYPE_EMAIL]);

        $this->data->logIncoming->insertSubmissionValues($this->data->incomingId, $copyOfInputData);
    }
    //--------------------------------------------------------------------------
    
    
    private function logSubmissionDuplicationFieldTypeValues($duplicatedSubmissionId) {
        $copyOfInputData = $this->data->inputData;
        unset($copyOfInputData[Model_CampaignField::FIELD_TYPE_EMAIL]);

        SingletonRegistry::getModelLogIncomingDuplication()->insertSubmissionValues($duplicatedSubmissionId, $copyOfInputData);
    }
    //--------------------------------------------------------------------------


    private function updateSummaryAndReportingTables() {
        $submission = $this->data->logIncoming->get($this->data->incomingId);
        $this->data->incomingTimestamp = $submission['request_time'];
        $this->data->logIncoming->logSubmissionToSummaryTable($this->job->campaign->getId(), $this->data->incomingTimestamp, empty($this->errors));

        if ($this->errors) {
            $this->data->logIncoming->insertFailedSubmissionIntoReportingTable($submission, $this->job->campaign->getUserId(), true);
        }
    }
    //--------------------------------------------------------------------------
}
