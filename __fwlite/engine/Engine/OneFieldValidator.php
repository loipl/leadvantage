<?php

class Engine_OneFieldValidator {

    /**
     * @var Engine_Validator
     */
    private $validator;

    /**
     * @var Engine_Data
     */
    private $data;

    /**
     * @var Engine_Flags
     */
    public $flags;

    private $errors = array();

    private $dataSource = array();

    private $fieldName;
    private $fieldTypeId;
    private $isEmpty;
    private $isMandatory;
    private $value;

    private $email;
    private $profileData = false;


    public function __construct(Engine_Validator $validator, Engine_Data $data, Engine_Flags $flags, array $dataSource, array & $errors, $email) {
        $this->validator  = $validator;
        $this->data       = $data;
        $this->flags      = $flags;
        $this->dataSource = $dataSource;
        $this->errors     = & $errors;
        $this->email      = $email;
    }
    //--------------------------------------------------------------------------


    public function readAndValidate(array $campaignField) {
        $this->initVarsForNewValidation($campaignField);
        $this->getNormalizedValue();

        if ($this->isContentValid($campaignField)) {
            $this->checkIfShouldValidateViaApi($campaignField);
        }

        return $this->errors;
    }
    //--------------------------------------------------------------------------


    private function initVarsForNewValidation(array $campaignField) {
        $this->value       = '';
        $this->fieldName   = $campaignField['name'];
        $this->fieldTypeId = $campaignField['field_type'];
        $this->isMandatory = $campaignField['is_mandatory'] == '1';
        $this->isEmpty     = !isset($this->dataSource[$this->fieldName]) || !is_string($this->dataSource[$this->fieldName]) || $this->dataSource[$this->fieldName] == '';
    }
    //--------------------------------------------------------------------------


    private function isContentValid(array $campaignField) {
        $errors  = $this->errors;
        $isValid = $this->isSubmittedContentValid($campaignField);

        if (!$isValid && $this->flags->useProfilesForMissingData && ($this->fieldTypeId != Model_CampaignField::FIELD_TYPE_EMAIL)) {
            $isValid = $this->tryToReadFromProfiles($campaignField);
            if ($isValid) {
                $this->errors = $errors;
            }
        }

        return $isValid;
    }
    //--------------------------------------------------------------------------


    private function isSubmittedContentValid(array $campaignField) {
        if ($this->isMandatory && $this->isEmpty) {
            $this->createErrorMessageForMissingField($campaignField);
            return false;
        }

        if (!$this->isEmpty) {
            $errorCount = sizeof($this->errors);
            $this->validateFieldType();
            if (sizeof($this->errors) > $errorCount) {
                // New errors were added to this->errors, content is invalid
                return false;
            }
        }

        return true;
    }
    //--------------------------------------------------------------------------


    private function tryToReadFromProfiles(array $campaignField) {
        if ($this->profileData === false) {
            /* @var $modelProfile Model_Profile */
            $modelProfile = SingletonRegistry::getSingleInstance('Model_Profile');
            $this->profileData = $modelProfile->getProfileDataForEmail($this->email);
        }
        if (isset($this->profileData[$this->fieldTypeId])) {
            $this->data->inputData[$this->fieldTypeId] = $this->profileData[$this->fieldTypeId];

            return true;
        }
        return false;
    }
    //--------------------------------------------------------------------------


    private function checkIfShouldValidateViaApi($campaignField) {
        $somethingToVerify     = $this->isMandatory || !$this->isEmpty;
        $adminForcedValidation = $campaignField['validate'] == 2;
        $validationRequested   = !empty($campaignField['validate']);
        $userCanVerifyField    = in_array($this->fieldTypeId, $this->data->cvFTypes);

        if ($somethingToVerify && ($adminForcedValidation || ($validationRequested  && $userCanVerifyField))) {
            $this->validator->markForApiValidation($this->fieldName, $this->fieldTypeId, $this->value);
        }
    }
    //--------------------------------------------------------------------------


    private function getNormalizedValue() {
        $isValidFieldValue = isset($this->dataSource[$this->fieldName]) && is_string($this->dataSource[$this->fieldName]);
        $this->value = $isValidFieldValue ? trim($this->dataSource[$this->fieldName]) : '';

        if ($this->fieldTypeId == Model_CampaignField::FIELD_TYPE_IP_ADDRESS) {
            // Turn numeric IP into dotted
            $hasMoreThanThreeDigits = preg_match('/^[\\d]{4,}$/', $this->value);
            if ($hasMoreThanThreeDigits) {
                $this->value = long2ip($this->value);
            }
        }

        if (!empty($this->data->fieldTypesFull[$this->fieldTypeId]['force_uppercase'])) {
            $this->value = strtoupper($this->value);
        }

        $this->data->inputData[$this->fieldTypeId] = $this->value;
    }
    //--------------------------------------------------------------------------


    private function createErrorMessageForMissingField(array $campaignField) {
        $prettyName = trim($campaignField['description']) ? trim($campaignField['description']) : $this->data->fieldTypes[$this->fieldTypeId];
        $this->errors[]= "$prettyName is a mandatory field";
    }
    //--------------------------------------------------------------------------


    private function validateFieldType() {
        if (function_exists('mb_check_encoding') && !mb_check_encoding($this->value, 'UTF-8')) {
            $this->errors[] = 'Please use UTF-8 encoding';
        }
        $fieldType = isset($this->data->fieldTypesFull[$this->fieldTypeId]) ? $this->data->fieldTypesFull[$this->fieldTypeId] : array();
        if ($fieldType) {
            switch ($fieldType['validation_type']) {
                case Model_FieldType::VALIDATION_EMAIL:
                    $this->validateEmail();
                    break;

                case Model_FieldType::VALIDATION_URL:
                    $this->validateUrl();
                    break;

                case Model_FieldType::VALIDATION_DATE:
                    $this->validateDate();
                    break;

                case Model_FieldType::VALIDATION_REGEX:
                    $this->validateRegex($fieldType['validation_data']);
                    break;
            }
        }
    }
    //--------------------------------------------------------------------------


    private function validateEmail() {
        if(!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "$this->value is not a valid email";
        }
    }
    //--------------------------------------------------------------------------


    private function validateUrl() {
        if ((stripos($this->value, 'http%3a%2f%2f') === 0) || (stripos($this->value, 'https%3a%2f%2f') === 0)) {
            $this->value = urldecode($this->value);
        }
        
        if(!filter_var($this->value, FILTER_VALIDATE_URL)) {
            if ($this->isMandatory) {
                $this->errors[] = "$this->value is not a valid URL";
            } else {
                unset($this->data->inputData[$this->fieldTypeId]);
            }
        }
    }
    //--------------------------------------------------------------------------


    private function validateDate() {
        if (strtotime($this->value) === false) {
            if ($this->isMandatory) {
                $this->errors[] = "Invalid date: $this->value";
            } else {
                unset($this->data->inputData[$this->fieldTypeId]);
            }
        }
    }
    //--------------------------------------------------------------------------


    private function validateRegex($regex) {
        if ($regex && !preg_match($regex, $this->value)) {
            if ($this->isMandatory) {
                $this->errors[] = "'$this->value' is not a valid $this->fieldName";
            } else {
                unset($this->data->inputData[$this->fieldTypeId]);
            }
        }
    }
    //--------------------------------------------------------------------------

}
