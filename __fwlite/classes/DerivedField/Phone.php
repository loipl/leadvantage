<?php

class DerivedField_Phone extends DerivedField_Base {

    protected static $cellPhoneTokens = array(
        ':cell_phone:',
        ':cell_phone_area_code:',
        ':cell_phone_prefix:',
        ':cell_phone_suffix:'
    );
    
    protected static $phoneTokens = array(
        ':phone:',
        ':phone_area_code:',
        ':phone_prefix:',
        ':phone_suffix:'
    );
    
    protected static $workPhoneTokens = array(
        ':work_phone:',
        ':work_phone_area_code:',
        ':work_phone_prefix:',
        ':work_phone_suffix:'
    );

    public function __construct() {
        $this->name = 'phone';

        $this->visibilityMap = array(
            'cell_phone' => self::$cellPhoneTokens,
            'cell_phone_area_code' => array(
                ':cell_phone:'
            ),
            'cell_phone_prefix' => array(
                ':cell_phone:'
            ),
            'cell_phone_suffix' => array(
                ':cell_phone:'
            ),
            'phone' => self::$phoneTokens,
            'phone_area_code' => array(
                ':phone:'
            ),
            'phone_prefix' => array(
                ':phone:'
            ),
            'phone_suffix' => array(
                ':phone:'
            ),
            'work_phone' => self::$workPhoneTokens,
            'work_phone_area_code' => array(
                ':work_phone:'
            ),
            'work_phone_prefix' => array(
                ':work_phone:'
            ),
            'work_phone_suffix' => array(
                ':work_phone:'
            )
        );
    }
    //--------------------------------------------------------------------------


    public function listTokens() {
        return array_merge(self::$cellPhoneTokens, self::$phoneTokens, self::$workPhoneTokens);
    }
    //--------------------------------------------------------------------------


    public function listOverridableTokens() {
       return array_merge(self::$cellPhoneTokens, self::$phoneTokens, self::$workPhoneTokens);
    }
    //--------------------------------------------------------------------------
    
    public function valueOfCellPhoneAreaCode($token, $fieldTypeData) {
        if ($token != 'cell_phone_area_code') {
            return '';
        }
        
        // get cell_phone_area_code directly from user input
        $cellPhoneAreaCodeFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_AREA_CODE;
        if (!empty($fieldTypeData[$cellPhoneAreaCodeFTId])) {
            return $fieldTypeData[$cellPhoneAreaCodeFTId];
        } 
        
        // get cell_phone_area_code from cell_phone
        $cellPhoneFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE;
        if (!empty($fieldTypeData[$cellPhoneFTId])) {
            return $this->getPhoneAreaCode($fieldTypeData[$cellPhoneFTId]);
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfPhoneAreaCode($token, $fieldTypeData) {
        if ($token != 'phone_area_code') {
            return '';
        }
        
        // get phone_area_code directly from user input
        $phoneAreaCodeFTId = Model_CampaignField::FIELD_TYPE_PHONE_AREA_CODE;
        if (!empty($fieldTypeData[$phoneAreaCodeFTId])) {
            return $fieldTypeData[$phoneAreaCodeFTId];
        } 
        
        // get phone_area_code from cell_phone_area_code
        $cellPhoneAreaCodeFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_AREA_CODE;
        if (!empty($fieldTypeData[$cellPhoneAreaCodeFTId])) {
            return $fieldTypeData[$cellPhoneAreaCodeFTId];
        } 
        
        // get phone_area_code from phone
        $phoneFTId = Model_CampaignField::FIELD_TYPE_PHONE;
        if (!empty($fieldTypeData[$phoneFTId])) {
            return $this->getPhoneAreaCode($fieldTypeData[$phoneFTId]);
        };
        
        // get phone_area_code from cell_phone
        $cellPhoneFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE;
        if (!empty($fieldTypeData[$cellPhoneFTId])) {
            return $this->getPhoneAreaCode($fieldTypeData[$cellPhoneFTId]);
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfWorkPhoneAreaCode($token, $fieldTypeData) {
        if ($token != 'work_phone_area_code') {
            return '';
        }
        
        // get work_phone_area_code directly from user input
        $workPhoneAreaCodeFTId = Model_CampaignField::FIELD_TYPE_WORK_PHONE_AREA_CODE;
        if (!empty($fieldTypeData[$workPhoneAreaCodeFTId])) {
            return $fieldTypeData[$workPhoneAreaCodeFTId];
        } 
        
        // get work_phone_area_code from work_phone
        $workPhoneFTId = Model_CampaignField::FIELD_TYPE_WORK_PHONE;
        if (!empty($fieldTypeData[$workPhoneFTId])) {
            return $this->getPhoneAreaCode($fieldTypeData[$workPhoneFTId]);
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfCellPhonePrefix($token, $fieldTypeData) {
        if ($token != 'cell_phone_prefix') {
            return '';
        }
        
        // get cell_phone_prefix directly from user input
        $cellPhonePrefixFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_PREFIX;
        if (!empty($fieldTypeData[$cellPhonePrefixFTId])) {
            return $fieldTypeData[$cellPhonePrefixFTId];
        } 
        
        // get cell_phone_prefix from cell_phone
        $cellPhoneFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE;
        if (!empty($fieldTypeData[$cellPhoneFTId])) {
            return $this->getPhonePrefix($fieldTypeData[$cellPhoneFTId]);
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfPhonePrefix($token, $fieldTypeData) {
        if ($token != 'phone_prefix') {
            return '';
        }
        
        // get phone_prefix directly from user input
        $phonePrefixFTId = Model_CampaignField::FIELD_TYPE_PHONE_PREFIX;
        if (!empty($fieldTypeData[$phonePrefixFTId])) {
            return $fieldTypeData[$phonePrefixFTId];
        } 
        
        // get phone_prefix directly from cell_phone_prefix
        $cellPhonePrefixFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_PREFIX;
        if (!empty($fieldTypeData[$cellPhonePrefixFTId])) {
            return $fieldTypeData[$cellPhonePrefixFTId];
        } 
        
        // get phone_prefix from phone
        $phoneFTId = Model_CampaignField::FIELD_TYPE_PHONE;
        if (!empty($fieldTypeData[$phoneFTId])) {
            return $this->getPhonePrefix($fieldTypeData[$phoneFTId]);
        };
        
        // get phone_prefix from cell_phone
        $cellPhoneFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE;
        if (!empty($fieldTypeData[$cellPhoneFTId])) {
            return $this->getPhonePrefix($fieldTypeData[$cellPhoneFTId]);
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfWorkPhonePrefix($token, $fieldTypeData) {
        if ($token != 'work_phone_prefix') {
            return '';
        }
        
        // get work_phone_prefix directly from user input
        $workPhonePrefixFTId = Model_CampaignField::FIELD_TYPE_WORK_PHONE_PREFIX;
        if (!empty($fieldTypeData[$workPhonePrefixFTId])) {
            return $fieldTypeData[$workPhonePrefixFTId];
        } 
        
        // get work_phone_prefix from work_phone
        $workPhoneFTId = Model_CampaignField::FIELD_TYPE_WORK_PHONE;
        if (!empty($fieldTypeData[$workPhoneFTId])) {
            return $this->getPhonePrefix($fieldTypeData[$workPhoneFTId]);
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfCellPhoneSuffix($token, $fieldTypeData) {
        if ($token != 'cell_phone_suffix') {
            return '';
        }
        
        // get cell_phone_suffix directly from user input
        $cellPhoneSuffixFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_SUFFIX;
        if (!empty($fieldTypeData[$cellPhoneSuffixFTId])) {
            return $fieldTypeData[$cellPhoneSuffixFTId];
        } 
        
        // get cell_phone_suffix from cell_phone
        $cellPhoneFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE;
        if (!empty($fieldTypeData[$cellPhoneFTId])) {
            return $this->getPhoneSuffix($fieldTypeData[$cellPhoneFTId]);
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfPhoneSuffix($token, $fieldTypeData) {
        if ($token != 'phone_suffix') {
            return '';
        }
        
        // get phone_suffix directly from user input
        $phoneSuffixFTId = Model_CampaignField::FIELD_TYPE_PHONE_SUFFIX;
        if (!empty($fieldTypeData[$phoneSuffixFTId])) {
            return $fieldTypeData[$phoneSuffixFTId];
        } 
        
        // get phone_suffix from cell_phone_suffix
        $cellPhoneSuffixFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_SUFFIX;
        if (!empty($fieldTypeData[$cellPhoneSuffixFTId])) {
            return $fieldTypeData[$cellPhoneSuffixFTId];
        }
        
        // get phone_suffix from phone
        $phoneFTId = Model_CampaignField::FIELD_TYPE_PHONE;
        if (!empty($fieldTypeData[$phoneFTId])) {
            return $this->getPhoneSuffix($fieldTypeData[$phoneFTId]);
        };
        
        // get cell_phone_prefix from cell_phone
        $cellPhoneFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE;
        if (!empty($fieldTypeData[$cellPhoneFTId])) {
            return $this->getPhoneSuffix($fieldTypeData[$cellPhoneFTId]);
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfWorkPhoneSuffix($token, $fieldTypeData) {
        if ($token != 'work_phone_suffix') {
            return '';
        }
        
        // get work_phone_suffix directly from user input
        $workPhoneSuffixFTId = Model_CampaignField::FIELD_TYPE_WORK_PHONE_SUFFIX;
        if (!empty($fieldTypeData[$workPhoneSuffixFTId])) {
            return $fieldTypeData[$workPhoneSuffixFTId];
        } 
        
        // get work_phone_suffix from work_phone
        $workPhoneFTId = Model_CampaignField::FIELD_TYPE_WORK_PHONE;
        if (!empty($fieldTypeData[$workPhoneFTId])) {
            return $this->getPhoneSuffix($fieldTypeData[$workPhoneFTId]);
        };
        
        return '';
    }
    // -------------------------------------------------------------------------
    
    public function valueOfCellPhone($token, array $fieldTypeData) {
        if ($token != 'cell_phone') {
            return '';
        }
        
        // get cell_phone directly from user input
        $cellPhoneFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE;;
        if (!empty($fieldTypeData[$cellPhoneFTId])) {
            return $fieldTypeData[$cellPhoneFTId];
        };
        
        // get Cell_Phone_Area_Code
        $cellPhoneAreaCodeFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_AREA_CODE;
        if (!empty($fieldTypeData[$cellPhoneAreaCodeFTId])) {
            $cellPhoneAreaCode = $fieldTypeData[$cellPhoneAreaCodeFTId];
        } else {
            $cellPhoneAreaCode = '';
        }
        
        // get Cell_Phone_Prefix
        $cellPhonePrefixFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_PREFIX;
        if (!empty($fieldTypeData[$cellPhonePrefixFTId])) {
            $cellPhonePrefix = $fieldTypeData[$cellPhonePrefixFTId];
        } else {
            $cellPhonePrefix = '';
        }
        
         // get Cell_Phone_Suffix
        $cellPhoneSuffixFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_SUFFIX;
        if (!empty($fieldTypeData[$cellPhoneSuffixFTId])) {
            $cellPhoneSuffix = $fieldTypeData[$cellPhoneSuffixFTId];
        } else {
            $cellPhoneSuffix = '';
        }
                     
        if (! empty($cellPhoneAreaCode) && ! empty($cellPhonePrefix) && ! empty($cellPhoneSuffix)) {
            return $cellPhoneAreaCode. '-'. $cellPhonePrefix . '-' . $cellPhoneSuffix;
        } else {
            return '';
        }
    }
    // -------------------------------------------------------------------------
    
    public function valueOfPhone($token, array $fieldTypeData) {
        if ($token != 'phone') {
            return '';
        }
        
        // get phone directly from user input
        $phoneFTId = Model_CampaignField::FIELD_TYPE_PHONE;;
        if (!empty($fieldTypeData[$phoneFTId])) {
            return $fieldTypeData[$phoneFTId];
        };
        
        // get phone from cell_phone
        $cellPhoneFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE;;
        if (!empty($fieldTypeData[$cellPhoneFTId])) {
            return $fieldTypeData[$cellPhoneFTId];
        };
        
        // get Phone_Area_Code
        $phoneAreaCodeFTId = Model_CampaignField::FIELD_TYPE_PHONE_AREA_CODE;
        $cellPhoneAreaCodeFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_AREA_CODE;
        if (!empty($fieldTypeData[$phoneAreaCodeFTId])) {
            $phoneAreaCode = $fieldTypeData[$phoneAreaCodeFTId];
        } elseif (!empty($fieldTypeData[$cellPhoneAreaCodeFTId])) {
            $phoneAreaCode = $fieldTypeData[$cellPhoneAreaCodeFTId];
        } else {
            $phoneAreaCode = '';
        }
        
        // get Phone_Prefix
        $phonePrefixFTId = Model_CampaignField::FIELD_TYPE_PHONE_PREFIX;
        $cellPhonePrefixFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_PREFIX;
        if (!empty($fieldTypeData[$phonePrefixFTId])) {
            $phonePrefix = $fieldTypeData[$phonePrefixFTId];
        } elseif (!empty($fieldTypeData[$cellPhonePrefixFTId])) {
            $phonePrefix = $fieldTypeData[$cellPhonePrefixFTId];
        } else {
            $phonePrefix = '';
        }
        
         // get Phone_Suffix
        $phoneSuffixFTId = Model_CampaignField::FIELD_TYPE_PHONE_SUFFIX;
        $cellPhoneSuffixFTId = Model_CampaignField::FIELD_TYPE_CELL_PHONE_SUFFIX;
        if (!empty($fieldTypeData[$phoneSuffixFTId])) {
            $phoneSuffix = $fieldTypeData[$phoneSuffixFTId];
        } elseif (!empty($fieldTypeData[$cellPhoneSuffixFTId])) {
            $phoneSuffix = $fieldTypeData[$cellPhoneSuffixFTId];
        } else {
            $phoneSuffix = '';
        }
                     
        if (! empty($phoneAreaCode) && ! empty($phonePrefix) && ! empty($phoneSuffix)) {
            return $phoneAreaCode. '-'. $phonePrefix . '-' . $phoneSuffix;
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------
    
    public function valueOfWorkPhone($token, array $fieldTypeData) {
        if ($token != 'work_phone') {
            return '';
        }
        
        // get work_phone directly from user input
        $workPhoneFTId = Model_CampaignField::FIELD_TYPE_WORK_PHONE;;
        if (!empty($fieldTypeData[$workPhoneFTId])) {
            return $fieldTypeData[$workPhoneFTId];
        };
        
        // get Work_Phone_Area_Code
        $workPhoneAreaCodeFTId = Model_CampaignField::FIELD_TYPE_WORK_PHONE_AREA_CODE;
        if (!empty($fieldTypeData[$workPhoneAreaCodeFTId])) {
            $workPhoneAreaCode = $fieldTypeData[$workPhoneAreaCodeFTId];
        } else {
            $workPhoneAreaCode = '';
        }
        
        // get Work_Phone_Prefix
        $workPhonePrefixFTId = Model_CampaignField::FIELD_TYPE_WORK_PHONE_PREFIX;
        if (!empty($fieldTypeData[$workPhonePrefixFTId])) {
            $workPhonePrefix = $fieldTypeData[$workPhonePrefixFTId];
        } else {
            $workPhonePrefix = '';
        }
        
         // get Work_Phone_Suffix
        $workPhoneSuffixFTId = Model_CampaignField::FIELD_TYPE_WORK_PHONE_SUFFIX;
        if (!empty($fieldTypeData[$workPhoneSuffixFTId])) {
            $workPhoneSuffix = $fieldTypeData[$workPhoneSuffixFTId];
        } else {
            $workPhoneSuffix = '';
        }
                     
        if (! empty($workPhoneAreaCode) && ! empty($workPhonePrefix) && ! empty($workPhoneSuffix)) {
            return $workPhoneAreaCode. '-'. $workPhonePrefix . '-' . $workPhoneSuffix;
        } else {
            return '';
        }
    }
    //--------------------------------------------------------------------------
    
    public function getPhoneAreaCode($phone) {
        $formatedPhone = trim(preg_replace('/[-()+ .]+/', ' ', $phone));
        $parts = explode(' ', $formatedPhone);
        $areaCode = '';
        if (count($parts) >= 4) {
            // phone number in format: countrycode - areacode - prefix - suffix
            $areaCode = $parts[1];
        } else if (count($parts) === 3) {
            // phone number in format: areacode - prefix - suffix
            $areaCode = $parts[0];
        } else {
            // area code missing
            return "";
        }
        
        // get the last 3 digits in case of two long area code
        if (strlen($areaCode) > 3) {
            $areaCode = substr($areaCode, -3);
        }
        return $areaCode;
    }
    // -------------------------------------------------------------------------
    
    public function getPhonePrefix($phone) {
        $formatedPhone = trim(preg_replace('/[-()+ .]+/', ' ', $phone));
        $parts = explode(' ', $formatedPhone);
        if (count($parts) >= 4) {
            // phone number in format: countrycode - areacode - prefix - suffix
            return $parts[2];
        } else if (count($parts) === 3) {
            // phone number in format: areacode - prefix - suffix
            return $parts[1];
        } else if (count($parts) === 2){
            // phone number in format: prefix - suffix
            return $parts[0];
        } else {
            return "";
        }
    }
    // -------------------------------------------------------------------------
    
    public function getPhoneSuffix($phone) {
        $formatedPhone = trim(preg_replace('/[-()+ .]+/', ' ', $phone));
        $parts = explode(' ', $formatedPhone);
        
        // return the last part.
        $numParts = count($parts);
        if ($numParts >= 1) {
            return $parts[$numParts - 1];
        } else {
            return "";
        }
    }
    // -------------------------------------------------------------------------
    
    public function tryToOverrideValue($token, array $fieldTypeData, & $value) {
        $token = self::normalizeTokenName($token);
        switch($token) {
            case 'cell_phone_area_code':           
                $s = $this->valueOfCellPhoneAreaCode($token, $fieldTypeData);
                break;
            case 'cell_phone_prefix':           
                $s = $this->valueOfCellPhonePrefix($token, $fieldTypeData);
                break;
            case 'cell_phone_suffix':           
                $s = $this->valueOfCellPhoneSuffix($token, $fieldTypeData);
                break;
            case 'cell_phone':           
                $s = $this->valueOfCellPhone($token, $fieldTypeData);
                break;
            case 'phone_area_code':           
                $s = $this->valueOfPhoneAreaCode($token, $fieldTypeData);
                break;
            case 'phone_prefix':           
                $s = $this->valueOfPhonePrefix($token, $fieldTypeData);
                break;
            case 'phone_suffix':           
                $s = $this->valueOfPhoneSuffix($token, $fieldTypeData);
                break;
            case 'phone':           
                $s = $this->valueOfPhone($token, $fieldTypeData);
                break;
            case 'work_phone_area_code':           
                $s = $this->valueOfWorkPhoneAreaCode($token, $fieldTypeData);
                break;
            case 'work_phone_prefix':           
                $s = $this->valueOfWorkPhonePrefix($token, $fieldTypeData);
                break;
            case 'work_phone_suffix':           
                $s = $this->valueOfWorkPhoneSuffix($token, $fieldTypeData);
                break;
            case 'work_phone':           
                $s = $this->valueOfWorkPhone($token, $fieldTypeData);
                break;
            default:
                $s = '';
                break;
        }
        
        if ($s) {
            $value = $s;
            return true;
        }
        return false;
    }
    //--------------------------------------------------------------------------
}
