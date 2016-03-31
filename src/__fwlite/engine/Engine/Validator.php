<?php


class Engine_Validator {

    /**
     * @var Engine_Data
     */
    private $data;

    /**
     * @var Engine_Job
     */
    private $job;

    /**
     * @var Engine_Flags
     */
    public $flags;

    private $errors = array();

    private $validatedFields = array();

    private $toValidateViaApi      = array();
    private $toValidateViaApiFTIds = array();


    public function __construct(Engine_Data $data, Engine_Job $job, Engine_Flags $flags) {
        $this->data  = $data;
        $this->job   = $job;
        $this->flags = $flags;
    }
    //--------------------------------------------------------------------------


    public function readAndValidateFields(array $dataSource, array $campaignFields) {
        $this->resetArraysAndFlags();
        $emailFieldName = $this->job->campaign->getEmailFieldName();
        $email = isset($dataSource[$emailFieldName]) ? $dataSource[$emailFieldName] : '';
        $fieldValidator = new Engine_OneFieldValidator($this, $this->data, $this->flags, $dataSource, $this->errors, $email);

        foreach ($campaignFields as $field) {
            $fieldValidator->readAndValidate($field);
        }
        $this->runApiValidationOnAllRequestedFields();

        return $this->errors;
    }
    //--------------------------------------------------------------------------


    private function resetArraysAndFlags() {
        $this->errors = array();
        $this->toValidateViaApi      = array();
        $this->toValidateViaApiFTIds = array();
    }
    //--------------------------------------------------------------------------


    private function runApiValidationOnAllRequestedFields() {
        if ($this->toValidateViaApi || $this->toValidateViaApiFTIds) {
            $this->validateMultipleFieldsViaApi($this->toValidateViaApi, $this->toValidateViaApiFTIds);
        }
    }
    //--------------------------------------------------------------------------


    private function validateMultipleFieldsViaApi() {
        if ($this->isPhoneAndEmailValidation()) {

            // make sure first row has email field
            if ($this->toValidateViaApi[0][1] == Model_CampaignField::FIELD_TYPE_EMAIL) {
                $params = array($this->toValidateViaApi[0], $this->toValidateViaApi[1]);
            } else {
                $params = array($this->toValidateViaApi[1], $this->toValidateViaApi[0]);
            }

            $this->validateEmailAndPhoneViaApi($params[0][0], $params[0][2], $params[1][0], $params[1][2]);

            $this->validatedFields[Model_CampaignField::FIELD_TYPE_EMAIL] = 1;
            $this->validatedFields[Model_CampaignField::FIELD_TYPE_PHONE] = 1;
            return;
        }
        foreach ($this->toValidateViaApi as $row) {
            list($fieldName, $fieldTypeId, $value) = $row;
            $this->validateViaApi($fieldName, $fieldTypeId, $value);
            $this->validatedFields[$fieldTypeId] = 1;
        }
    }
    //--------------------------------------------------------------------------


    private function isPhoneAndEmailValidation() {
        return (sizeof($this->toValidateViaApiFTIds) == 2)
            && !empty($this->toValidateViaApiFTIds[Model_CampaignField::FIELD_TYPE_EMAIL])
            && !empty($this->toValidateViaApiFTIds[Model_CampaignField::FIELD_TYPE_PHONE]);
    }
    //--------------------------------------------------------------------------


    private function validateEmailAndPhoneViaApi($efname, $evalue, $pfname, $pvalue) {
        $emailError = '';
        $phoneError = '';
        $isValid = Validator_Registry::validatePhoneAndEmailWithBrite($evalue, $emailError, $pvalue, $phoneError);
        if (!$isValid) {
            if ($emailError) {
                $this->errors[] = str_ireplace('%s', $evalue, $emailError);
            }
            if ($phoneError) {
                $this->errors[] = str_ireplace('%s', $pvalue, $phoneError);
            }
        }
        $this->saveLastUsedValidators($isValid);
    }
    //--------------------------------------------------------------------------


    private function validateViaApi($fname, $ftype, $value) {
        $error = '';
        $isValid = Validator_Registry::validate($value, $ftype, $error);
        if (!$isValid) {
            if ($error) {
                $s = str_ireplace('%s', $value, $error);
            } else {
                $s = "Invalid value in '$fname'";
            }
            $this->errors[] = $s;
        }
        $this->saveLastUsedValidators($isValid);
    }
    //--------------------------------------------------------------------------


    private function saveLastUsedValidators($isValid) {
        $model = false;

        foreach (Validator_Registry::getTriedValidatorsOnLastRun() as $validatorName) {
            if (isset(Validator_Registry::$valNames[$validatorName])) {
                /* @var $model Model_ValidationLog */
                $model or $model = SingletonRegistry::getSingleInstance('Model_ValidationLog');
                $model->log($this->job->campaign->getId(), Validator_Registry::$valNames[$validatorName], $isValid);
            }
        }
    }
    //--------------------------------------------------------------------------


    public function markForApiValidation($fieldName, $fieldTypeId, $value) {
        $this->toValidateViaApi[] = array($fieldName, $fieldTypeId, $value);
        $this->toValidateViaApiFTIds[$fieldTypeId] = 1;
    }
    //--------------------------------------------------------------------------


    public function validationOverridesByShadowCampaignPassed(Campaign $campaign, array $campaignFields) {
        $this->resetArraysAndFlags();

        if ($campaign->isEmailFieldVerificationOn() && empty($this->validatedFields[Model_CampaignField::FIELD_TYPE_EMAIL])) {
            $this->markEmailForApiValidation();
        }
        $this->applyValidationOverrides($campaign, $campaignFields);
        $this->runApiValidationOnAllRequestedFields();

        return empty($this->errors);
    }
    //--------------------------------------------------------------------------


    private function markEmailForApiValidation() {
        $this->toValidateViaApi[] = array('email', Model_CampaignField::FIELD_TYPE_EMAIL, $this->data->inputData[Model_CampaignField::FIELD_TYPE_EMAIL]);
        $this->toValidateViaApiFTIds[Model_CampaignField::FIELD_TYPE_EMAIL] = 1;
    }
    //--------------------------------------------------------------------------


    private function applyValidationOverrides(Campaign $campaign, array $campaignFields) {
        $overrides = $this->data->modelCampaignFields->listValidationOverrides($campaign->getId());
        foreach ($overrides as $sourceFieldId => $shouldOverride) {
            if ($shouldOverride) {
                foreach ($campaignFields as $campaignField) {
                    $thisFieldId = (int)$campaignField['id'];
                    $fieldTypeId = (int)$campaignField['field_type'];

                    if (($thisFieldId == $sourceFieldId) && empty($this->validatedFields[$fieldTypeId])) {
                        $this->markForApiValidation($sourceFieldId, $fieldTypeId, $this->data->inputData[$fieldTypeId]);
                    }
                }
            }
        }
    }
    //--------------------------------------------------------------------------
}
